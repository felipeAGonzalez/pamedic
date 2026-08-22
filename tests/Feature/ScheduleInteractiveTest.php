<?php

namespace Tests\Feature;

use App\Models\ActivePatient;
use App\Models\DialysisMonitoring;
use App\Models\DialysisPrescription;
use App\Models\Machine;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\ScheduleCloneBatch;
use App\Models\SchedulePatients;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleInteractiveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        Carbon::setTestNow('2026-08-20 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_patient_search_requires_two_characters_and_finds_name_or_expedient(): void
    {
        $user = $this->user('QUALITY');
        $this->patient('EXP-100', 'MARIA');

        $this->actingAs($user)
            ->getJson(route('schedule.patientSearch', ['search' => 'M']))
            ->assertUnprocessable();

        $this->actingAs($user)
            ->getJson(route('schedule.patientSearch', ['search' => 'MA']))
            ->assertOk()
            ->assertJsonPath('patients.0.expedient_number', 'EXP-100');

        $this->actingAs($user)
            ->getJson(route('schedule.patientSearch', ['search' => '100']))
            ->assertOk()
            ->assertJsonPath('patients.0.name', 'MARIA');
    }

    public function test_authorized_user_can_assign_patient_to_empty_cell(): void
    {
        $user = $this->user('QUALITY');
        $patient = $this->patient();
        $schedule = $this->schedule();
        $machine = $this->machine('1');

        $this->actingAs($user)->postJson(route('schedule.assign'), [
            'patient_id' => $patient->id,
            'schedules_id' => $schedule->id,
            'date' => '2026-08-20',
            'machine_id' => $machine->id,
        ])->assertCreated()
            ->assertJsonPath('message', 'Paciente agregado al horario correctamente.')
            ->assertJsonStructure(['html']);

        $created = SchedulePatients::where('patient_id', $patient->id)
            ->where('schedules_id', $schedule->id)
            ->whereDate('date', '2026-08-20')
            ->where('machine_id', $machine->id)
            ->where('continue_schedule', true)
            ->first();

        $this->assertNotNull($created);
        $this->assertDatabaseHas('active_patient', [
            'patient_id' => $patient->id,
            'date' => '2026-08-20',
            'active' => 1,
        ]);
    }

    public function test_occupied_cell_and_duplicate_patient_are_rejected(): void
    {
        $user = $this->user('QUALITY');
        $schedule = $this->schedule();
        $machineOne = $this->machine('1');
        $machineTwo = $this->machine('2');
        $first = $this->patient('EXP-001');
        $second = $this->patient('EXP-002');
        $this->scheduled($first, $schedule, $machineOne, '2026-08-20');

        $this->actingAs($user)->postJson(route('schedule.assign'), [
            'patient_id' => $second->id,
            'schedules_id' => $schedule->id,
            'date' => '2026-08-20',
            'machine_id' => $machineOne->id,
        ])->assertConflict();

        $this->actingAs($user)->postJson(route('schedule.assign'), [
            'patient_id' => $first->id,
            'schedules_id' => $schedule->id,
            'date' => '2026-08-20',
            'machine_id' => $machineTwo->id,
        ])->assertConflict();

        $this->assertSame(1, SchedulePatients::count());
    }

    public function test_drag_endpoint_swaps_two_occupied_machines_atomically(): void
    {
        $user = $this->user('QUALITY');
        $schedule = $this->schedule();
        $machineOne = $this->machine('1');
        $machineTwo = $this->machine('2');
        $first = $this->scheduled($this->patient('EXP-001'), $schedule, $machineOne, '2026-08-20');
        $second = $this->scheduled($this->patient('EXP-002'), $schedule, $machineTwo, '2026-08-20');

        $this->actingAs($user)
            ->patchJson(route('schedule.move', $first->id), ['machine_id' => $machineTwo->id])
            ->assertOk()
            ->assertJsonPath('swapped_record_id', $second->id);

        $this->assertSame((string) $machineTwo->id, $first->fresh()->machine_id);
        $this->assertSame((string) $machineOne->id, $second->fresh()->machine_id);
    }

    public function test_daily_absence_keeps_supply_calculation_and_is_cloned_next_week(): void
    {
        $user = $this->user('QUALITY');
        $schedule = $this->schedule();
        $machine = $this->machine('1');
        $patient = $this->patient();
        $record = $this->scheduled($patient, $schedule, $machine, '2026-08-17');

        DB::table('supply_orders')->insert([
            'period_start' => '2026-08-17',
            'period_end' => '2026-08-17',
            'generated_at' => now()->toDateTimeString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DialysisMonitoring::create([
            'patient_id' => $patient->id,
            'vascular_access' => 'fistula',
            'history' => 1,
        ]);
        DialysisPrescription::create([
            'patient_id' => $patient->id,
            'type_dialyzer' => 'F6ELISIO21H',
            'history' => 1,
        ]);
        $filter = Supply::create([
            'material' => 'Filtro',
            'type' => 'filter',
            'for_vascular_access' => 'no_apply',
            'existencias' => 0,
        ]);
        $fistulaSupply = Supply::create([
            'material' => 'Fístula',
            'type' => 'supply',
            'for_vascular_access' => 'fistula',
            'existencias' => 0,
        ]);

        $this->actingAs($user)
            ->delete(route('schedule.destroy', $record->id))
            ->assertRedirect();

        $trashed = SchedulePatients::withTrashed()->findOrFail($record->id);
        $this->assertNotNull($trashed->deleted_at);
        $this->assertTrue($trashed->continue_schedule);
        $this->assertDatabaseMissing('active_patient', [
            'patient_id' => $patient->id,
            'date' => '2026-08-17',
        ]);
        $this->assertSame(1, $filter->fresh()->existencias);
        $this->assertSame(1, $fistulaSupply->fresh()->existencias);

        $this->actingAs($user)->post(route('schedule.cloneWeek'), [
            'week' => 34,
            'year' => 2026,
        ])->assertRedirect();

        $cloned = SchedulePatients::where('patient_id', $patient->id)
            ->whereDate('date', '2026-08-24')
            ->where('continue_schedule', true)
            ->first();

        $this->assertNotNull($cloned);
        $batch = ScheduleCloneBatch::where('source_year', 2026)
            ->where('source_week', 34)
            ->where('status', 'active')
            ->firstOrFail();
        $this->assertSame($batch->id, $cloned->clone_batch_id);
        $clonedCount = SchedulePatients::whereBetween('date', ['2026-08-24', '2026-08-30'])->count();

        $this->actingAs($user)->post(route('schedule.cloneWeek'), [
            'week' => 34,
            'year' => 2026,
        ])->assertRedirect()
            ->assertSessionHas('error', 'Esta semana ya fue clonada');

        $this->assertSame($clonedCount, SchedulePatients::whereBetween('date', ['2026-08-24', '2026-08-30'])->count());
    }

    public function test_untouched_cloned_week_can_be_undone_without_counting_as_cancellation(): void
    {
        $user = $this->user('QUALITY');
        $patient = $this->patient();
        $this->scheduled($patient, $this->schedule(), $this->machine('1'), '2026-08-17');

        $this->actingAs($user)->post(route('schedule.cloneWeek'), [
            'week' => 34,
            'year' => 2026,
        ])->assertSessionHas('success', 'Semana clonada correctamente');

        $batch = ScheduleCloneBatch::where('status', 'active')->firstOrFail();

        $this->actingAs($user)
            ->delete(route('schedule.undoCloneWeek', $batch->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'Clonación deshecha correctamente.');

        $this->assertSame('undone', $batch->fresh()->status);
        $this->assertNotNull($batch->fresh()->undone_at);
        $this->assertSame($user->id, $batch->fresh()->undone_by);
        $this->assertSame(0, SchedulePatients::withTrashed()->where('clone_batch_id', $batch->id)->count());
        $this->assertDatabaseMissing('active_patient', [
            'patient_id' => $patient->id,
            'date' => '2026-08-24',
        ]);
        $this->assertTrue(SchedulePatients::where('patient_id', $patient->id)
            ->whereDate('date', '2026-08-17')
            ->exists());
    }

    public function test_cloned_week_cannot_be_undone_after_a_schedule_change(): void
    {
        $user = $this->user('QUALITY');
        $patient = $this->patient();
        $this->scheduled($patient, $this->schedule(), $this->machine('1'), '2026-08-17');

        $this->actingAs($user)->post(route('schedule.cloneWeek'), [
            'week' => 34,
            'year' => 2026,
        ]);

        $batch = ScheduleCloneBatch::where('status', 'active')->firstOrFail();
        SchedulePatients::where('clone_batch_id', $batch->id)->firstOrFail()->update([
            'machine_id' => $this->machine('2')->id,
        ]);

        $this->actingAs($user)
            ->delete(route('schedule.undoCloneWeek', $batch->id))
            ->assertSessionHas('error', 'No se puede deshacer porque la semana clonada ya tiene cambios.');

        $this->assertSame('active', $batch->fresh()->status);
        $this->assertSame(1, SchedulePatients::where('clone_batch_id', $batch->id)->count());
    }

    public function test_cloned_week_cannot_be_undone_when_it_has_a_supply_order(): void
    {
        $user = $this->user('QUALITY');
        $patient = $this->patient();
        $this->scheduled($patient, $this->schedule(), $this->machine('1'), '2026-08-17');

        $this->actingAs($user)->post(route('schedule.cloneWeek'), [
            'week' => 34,
            'year' => 2026,
        ]);

        $batch = ScheduleCloneBatch::where('status', 'active')->firstOrFail();
        DB::table('supply_orders')->insert([
            'period_start' => '2026-08-24',
            'period_end' => '2026-08-30',
            'generated_at' => now()->toDateTimeString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->delete(route('schedule.undoCloneWeek', $batch->id))
            ->assertSessionHas('error', 'No se puede deshacer porque ya existe un pedido de insumos para esa semana.');

        $this->assertSame('active', $batch->fresh()->status);
        $this->assertSame(1, SchedulePatients::where('clone_batch_id', $batch->id)->count());
    }

    public function test_permanent_removal_cancels_every_current_and_future_date_but_preserves_history(): void
    {
        $user = $this->user('QUALITY');
        $schedule = $this->schedule();
        $machine = $this->machine('1');
        $patient = $this->patient();
        $past = $this->scheduled($patient, $schedule, $machine, '2026-08-19');
        $today = $this->scheduled($patient, $schedule, $machine, '2026-08-20');
        $future = $this->scheduled($patient, $schedule, $machine, '2026-08-21');

        $this->actingAs($user)
            ->delete(route('schedule.permanentDestroy', $today->id))
            ->assertRedirect();

        $this->assertNull($past->fresh()->deleted_at);
        $this->assertFalse($past->fresh()->continue_schedule);

        foreach ([$today, $future] as $record) {
            $record = SchedulePatients::withTrashed()->findOrFail($record->id);
            $this->assertNotNull($record->deleted_at);
            $this->assertFalse($record->continue_schedule);
        }

        $this->assertDatabaseHas('active_patient', [
            'patient_id' => $patient->id,
            'date' => '2026-08-19',
        ]);
        $this->assertDatabaseMissing('active_patient', [
            'patient_id' => $patient->id,
            'date' => '2026-08-20',
        ]);
        $this->assertDatabaseMissing('active_patient', [
            'patient_id' => $patient->id,
            'date' => '2026-08-21',
        ]);
        $this->actingAs($user)->post(route('schedule.cloneWeek'), [
            'week' => 34,
            'year' => 2026,
        ])->assertRedirect();

        $this->assertSame(0, SchedulePatients::whereBetween('date', ['2026-08-24', '2026-08-30'])->count());
    }

    public function test_nurse_and_manager_remain_read_only(): void
    {
        $schedule = $this->schedule();
        $machine = $this->machine('1');
        $patient = $this->patient();
        $record = $this->scheduled($patient, $schedule, $machine, '2026-08-20');

        foreach (['NURSE', 'MANAGER'] as $position) {
            $user = $this->user($position);

            $this->actingAs($user)
                ->postJson(route('schedule.assign'), [
                    'patient_id' => $patient->id,
                    'schedules_id' => $schedule->id,
                    'date' => '2026-08-21',
                    'machine_id' => $machine->id,
                ])
                ->assertForbidden();

            $this->actingAs($user)
                ->patchJson(route('schedule.move', $record->id), ['machine_id' => $machine->id])
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('schedule.index', [2026, 34]))
                ->assertOk()
                ->assertDontSee('schedule-patient-modal')
                ->assertDontSee('Baja definitiva');
        }
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('enabled')->default(true);
            $table->boolean('need_change')->default(false);
            $table->timestamps();
        });

        Schema::create('patient', function (Blueprint $table) {
            $table->id();
            $table->string('expedient_number')->unique();
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('last_name_two')->nullable();
            $table->timestamps();
        });

        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->nullable();
            $table->string('machine_number');
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule');
            $table->string('schedule_type');
            $table->timestamps();
        });

        Schema::create('schedule_clone_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('source_year');
            $table->unsignedTinyInteger('source_week');
            $table->unsignedSmallInteger('target_year');
            $table->unsignedTinyInteger('target_week');
            $table->char('snapshot_hash', 64)->nullable();
            $table->unsignedInteger('records_count')->default(0);
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('cloned_by')->nullable();
            $table->unsignedBigInteger('undone_by')->nullable();
            $table->timestamp('undone_at')->nullable();
            $table->timestamps();
        });

        Schema::create('schedules_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('schedules_id');
            $table->date('date');
            $table->string('machine_id');
            $table->boolean('continue_schedule')->default(true);
            $table->unsignedBigInteger('clone_batch_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('active_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->date('date');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('nurse_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('active_patient_id');
            $table->date('date')->nullable();
            $table->timestamps();
        });

        Schema::create('supply_orders', function (Blueprint $table) {
            $table->id();
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamp('generated_at');
            $table->timestamps();
        });

        Schema::create('dialysis_monitoring', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('vascular_access')->nullable();
            $table->boolean('history')->default(false);
            $table->timestamps();
        });

        Schema::create('dialysis_prescription', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('type_dialyzer')->nullable();
            $table->boolean('history')->default(false);
            $table->timestamps();
        });

        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('material');
            $table->string('type');
            $table->string('for_vascular_access');
            $table->unsignedInteger('existencias')->default(0);
            $table->timestamps();
        });
    }

    private function user(string $position): User
    {
        return User::forceCreate([
            'name' => $position,
            'position' => $position,
            'email' => strtolower($position).uniqid().'@example.test',
            'password' => 'password',
            'enabled' => true,
            'need_change' => false,
        ]);
    }

    private function patient(?string $expedient = null, string $name = 'PACIENTE'): Patient
    {
        $expedient ??= 'EXP-'.uniqid();

        return Patient::create([
            'expedient_number' => $expedient,
            'name' => $name,
            'last_name' => 'PRUEBA',
            'last_name_two' => 'QA',
        ]);
    }

    private function schedule(): Schedule
    {
        return Schedule::create([
            'schedule' => '06:00:00',
            'schedule_type' => 'morning',
        ]);
    }

    private function machine(string $number): Machine
    {
        return Machine::create([
            'machine_number' => $number,
            'serial_number' => 'SERIAL-'.$number,
        ]);
    }

    private function scheduled(Patient $patient, Schedule $schedule, Machine $machine, string $date): SchedulePatients
    {
        $record = SchedulePatients::create([
            'patient_id' => $patient->id,
            'schedules_id' => $schedule->id,
            'date' => $date,
            'machine_id' => $machine->id,
            'continue_schedule' => true,
        ]);

        ActivePatient::create([
            'patient_id' => $patient->id,
            'date' => $date,
            'active' => true,
        ]);

        return $record;
    }
}
