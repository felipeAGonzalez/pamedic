<?php

namespace Tests\Feature;

use App\Models\ActivePatient;
use App\Models\NursePatient;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TreatmentFinalizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        Carbon::setTestNow('2026-08-18 02:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_normal_treatment_started_today_can_be_finalized(): void
    {
        $user = $this->user('NURSE');
        [$patient, $active, $assignment] = $this->treatment($user, '2026-08-18', 1);

        $this->actingAs($user)->patch(route('treatment.finalize', $patient->id))
            ->assertRedirect(route('treatment.index'))
            ->assertSessionHas('success', 'Tratamiento finalizado correctamente.');

        $this->assertDatabaseHas('active_patient', ['id' => $active->id, 'date' => '2026-08-18']);
        $this->assertDatabaseHas('nurse_patient', [
            'id' => $assignment->id,
            'history' => 1,
            'finalized_by' => $user->id,
            'finalized_at' => '2026-08-18 02:00:00',
            'exceptional_start_date' => null,
        ]);
    }

    public function test_treatment_without_trans_hemodialysis_cannot_be_finalized(): void
    {
        $user = $this->user('NURSE');
        [$patient, , $assignment] = $this->treatment($user, '2026-08-18', 1);

        DB::table('trans_hemodialysis')->where('patient_id', $patient->id)->delete();

        $this->actingAs($user)->patch(route('treatment.finalize', $patient->id))
            ->assertRedirect(route('treatment.index'))
            ->assertSessionHas('Error', 'Primero debe llenar la transhemodiálisis');

        $this->assertDatabaseHas('nurse_patient', [
            'id' => $assignment->id,
            'history' => 0,
        ]);
        $this->assertDatabaseHas('dialysis_monitoring', [
            'patient_id' => $patient->id,
            'history' => 0,
        ]);
    }

    public function test_emergency_started_yesterday_uses_its_original_start_date(): void
    {
        $user = $this->user('NURSE');
        [$patient, $active, $assignment] = $this->treatment($user, '2026-08-17', 5);

        $this->actingAs($user)->patch(route('treatment.finalize', $patient->id))
            ->assertRedirect(route('treatment.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('active_patient', ['id' => $active->id, 'date' => '2026-08-17']);
        $this->assertDatabaseHas('nurse_patient', [
            'id' => $assignment->id,
            'history' => 1,
            'exceptional_start_date' => null,
        ]);
    }

    public function test_non_root_cannot_finalize_previous_normal_treatment(): void
    {
        $user = $this->user('NURSE');
        [$patient, , $assignment] = $this->treatment($user, '2026-08-17', 1);

        $this->actingAs($user)->patch(route('treatment.finalize', $patient->id))->assertForbidden();

        $this->assertDatabaseHas('nurse_patient', ['id' => $assignment->id, 'history' => 0]);
        $this->assertDatabaseHas('dialysis_monitoring', ['patient_id' => $patient->id, 'history' => 0]);
    }

    public function test_non_root_cannot_send_a_selected_date_manually(): void
    {
        $user = $this->user('MANAGER');
        [$patient, , $assignment] = $this->treatment($user, '2026-08-17', 1);

        $this->actingAs($user)->patch(route('treatment.finalize', $patient->id), [
            'start_date' => '2026-08-17',
        ])->assertForbidden();

        $this->assertDatabaseHas('nurse_patient', ['id' => $assignment->id, 'history' => 0]);
    }

    public function test_root_can_finalize_previous_normal_treatment_with_exact_date(): void
    {
        $root = $this->user('ROOT');
        $assignedNurse = $this->user('NURSE');
        [$patient, $active, $assignment] = $this->treatment($assignedNurse, '2026-08-17', 1);

        $this->actingAs($root)->patch(route('treatment.finalize', $patient->id), [
            'start_date' => '2026-08-17',
        ])->assertRedirect(route('treatment.index'))
            ->assertSessionHas('success', 'Tratamiento finalizado correctamente.');

        $this->assertDatabaseHas('active_patient', ['id' => $active->id, 'date' => '2026-08-17']);
        $this->assertDatabaseHas('nurse_patient', [
            'id' => $assignment->id,
            'history' => 1,
            'finalized_by' => $root->id,
        ]);
        $this->assertSame('2026-08-17', NursePatient::findOrFail($assignment->id)->exceptional_start_date->toDateString());
    }

    public function test_root_cannot_finalize_with_a_date_without_active_treatment(): void
    {
        $root = $this->user('ROOT');
        $assignedNurse = $this->user('NURSE');
        [$patient, , $assignment] = $this->treatment($assignedNurse, '2026-08-17', 1);

        $this->actingAs($root)->patch(route('treatment.finalize', $patient->id), [
            'start_date' => '2026-08-16',
        ])->assertRedirect(route('treatment.index'))
            ->assertSessionHas('Error', 'La fecha seleccionada no corresponde a un tratamiento activo.');

        $this->assertDatabaseHas('nurse_patient', ['id' => $assignment->id, 'history' => 0]);
    }

    public function test_root_receives_clear_message_for_invalid_date_format(): void
    {
        $root = $this->user('ROOT');
        $assignedNurse = $this->user('NURSE');
        [$patient, , $assignment] = $this->treatment($assignedNurse, '2026-08-17', 1);

        $this->actingAs($root)->patch(route('treatment.finalize', $patient->id), [
            'start_date' => 'fecha-invalida',
        ])->assertRedirect(route('treatment.index'))
            ->assertSessionHas('Error', 'La fecha seleccionada es inválida.');

        $this->assertDatabaseHas('nurse_patient', ['id' => $assignment->id, 'history' => 0]);
    }

    public function test_second_finalization_is_rejected_without_changing_audit(): void
    {
        $user = $this->user('NURSE');
        [$patient, , $assignment] = $this->treatment($user, '2026-08-18', 1);

        $this->actingAs($user)->patch(route('treatment.finalize', $patient->id))->assertSessionHas('success');
        $finishedAt = NursePatient::findOrFail($assignment->id)->finalized_at;

        Carbon::setTestNow('2026-08-18 03:00:00');
        $this->actingAs($user)->patch(route('treatment.finalize', $patient->id))
            ->assertRedirect(route('treatment.index'))
            ->assertSessionHas('Error', 'El tratamiento ya fue finalizado.');

        $this->assertSame($finishedAt->toDateTimeString(), NursePatient::findOrFail($assignment->id)->finalized_at->toDateTimeString());
        $this->assertDatabaseCount('nurse_patient', 1);
    }

    public function test_missing_active_treatment_has_clear_message_and_get_cannot_finalize(): void
    {
        $user = $this->user('NURSE');
        $patient = $this->patient();

        $this->actingAs($user)->get('/treatment/finalize/'.$patient->id)->assertMethodNotAllowed();

        $this->actingAs($user)->patch(route('treatment.finalize', $patient->id))
            ->assertRedirect(route('treatment.index'))
            ->assertSessionHas('Error', 'Tratamiento activo no encontrado.');
    }

    public function test_root_cannot_use_another_patients_active_date(): void
    {
        $root = $this->user('ROOT');
        $assignedNurse = $this->user('NURSE');
        [$patient, , $assignment] = $this->treatment($assignedNurse, '2026-08-17', 1);
        [, , $otherAssignment] = $this->treatment($assignedNurse, '2026-08-16', 1);

        $this->actingAs($root)->patch(route('treatment.finalize', $patient->id), [
            'start_date' => '2026-08-16',
        ])->assertRedirect(route('treatment.index'))
            ->assertSessionHas('Error', 'La fecha seleccionada no corresponde a un tratamiento activo.');

        $this->assertDatabaseHas('nurse_patient', ['id' => $assignment->id, 'history' => 0]);
        $this->assertDatabaseHas('nurse_patient', ['id' => $otherAssignment->id, 'history' => 0]);
    }

    public function test_exceptional_date_selector_is_only_rendered_for_root(): void
    {
        $root = $this->user('ROOT');
        $nurse = $this->user('NURSE');
        $this->treatment($nurse, '2026-08-18', 1);

        $this->actingAs($root)->get(route('treatment.index'))
            ->assertOk()
            ->assertSee('Fecha de inicio del tratamiento')
            ->assertSee('Llenar con tratamiento anterior')
            ->assertSee('name="start_date"', false);

        $this->actingAs($nurse)->get(route('treatment.index'))
            ->assertOk()
            ->assertDontSee('Fecha de inicio del tratamiento')
            ->assertDontSee('Llenar con tratamiento anterior');
    }

    public function test_root_can_copy_previous_treatment_without_overwriting_current_data_or_copying_optional_sections(): void
    {
        $root = $this->user('ROOT');
        $nurse = $this->user('NURSE');
        $patient = $this->patient();

        $previousActive = ActivePatient::create([
            'patient_id' => $patient->id,
            'date' => '2026-08-10',
            'active' => 0,
        ]);
        $previousAssignment = NursePatient::create([
            'active_patient_id' => $previousActive->id,
            'user_id' => $nurse->id,
            'date' => '2026-08-10',
            'history' => 1,
            'finalized_by' => $root->id,
            'finalized_at' => '2026-08-10 10:00:00',
        ]);
        DB::table('nurse_patient')->where('id', $previousAssignment->id)->update([
            'created_at' => '2026-08-10 06:00:00',
            'updated_at' => '2026-08-10 10:00:00',
        ]);

        $historicalRows = [
            'dialysis_monitoring' => ['date_hour' => '2026-08-10 06:00:00', 'machine_number' => '2', 'session_number' => 8, 'vascular_access' => 'fistula'],
            'dialysis_prescription' => ['time' => '180', 'blood_flux' => '350'],
            'pre_hemodialysis' => ['initial_weight' => 70, 'dry_weight' => 65, 'reuse_number' => 4],
            'trans_hemodialysis' => ['time' => '06:00:00', 'heart_rate' => 75],
            'post_hemodialysis' => ['weight_out' => 66],
            'evaluation_risk' => ['fase' => 'Pre-Hemodiálisis', 'hour' => '06:00:00', 'result' => '4'],
            'nurse_valuation' => ['fase' => 'Pre-Hemodiálisis', 'nurse_valuation' => 'Valoración anterior', 'nurse_intervention' => 'Intervención anterior'],
            'time_out' => ['patient_identification' => true],
            'verification' => ['patient_name' => true],
            'medication_administration' => [],
            'oxygen_therapy' => [],
        ];
        foreach ($historicalRows as $table => $values) {
            DB::table($table)->insert(array_merge($values, [
                'patient_id' => $patient->id,
                'history' => 1,
                'created_at' => '2026-08-10 07:00:00',
                'updated_at' => '2026-08-10 10:00:00',
            ]));
        }

        $currentActive = ActivePatient::create([
            'patient_id' => $patient->id,
            'date' => '2026-08-18',
            'active' => 0,
        ]);
        NursePatient::create([
            'active_patient_id' => $currentActive->id,
            'user_id' => $nurse->id,
            'date' => '2026-08-18',
            'history' => 0,
        ]);
        DB::table('machines')->insert(['id' => 1, 'machine_number' => '7']);
        DB::table('schedules_patient')->insert([
            'schedules_id' => 1,
            'patient_id' => $patient->id,
            'date' => '2026-08-18',
            'machine_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('dialysis_monitoring')->insert([
            'patient_id' => $patient->id,
            'history' => 0,
            'date_hour' => '2026-08-18 02:00:00',
            'machine_number' => '7',
            'session_number' => 20,
            'vascular_access' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($root)
            ->post(route('treatment.copyPrevious', $currentActive))
            ->assertRedirect(route('treatment.index'))
            ->assertSessionHas('success', 'Tratamiento llenado con la información clínica del tratamiento anterior.');

        $this->assertDatabaseHas('dialysis_monitoring', [
            'patient_id' => $patient->id,
            'history' => 0,
            'date_hour' => '2026-08-18 02:00:00',
            'machine_number' => '7',
            'session_number' => 20,
            'vascular_access' => 'fistula',
        ]);
        $this->assertDatabaseHas('dialysis_prescription', [
            'patient_id' => $patient->id,
            'history' => 0,
            'blood_flux' => '350',
        ]);
        $this->assertDatabaseHas('pre_hemodialysis', [
            'patient_id' => $patient->id,
            'history' => 0,
            'reuse_number' => 5,
        ]);
        $this->assertDatabaseHas('trans_hemodialysis', [
            'patient_id' => $patient->id,
            'history' => 0,
            'time' => '02:00:00',
            'heart_rate' => 75,
        ]);
        $this->assertDatabaseHas('time_out', [
            'patient_id' => $patient->id,
            'history' => 0,
            'patient_identification' => true,
        ]);
        $this->assertDatabaseMissing('medication_administration', [
            'patient_id' => $patient->id,
            'history' => 0,
        ]);
        $this->assertDatabaseMissing('oxygen_therapy', [
            'patient_id' => $patient->id,
            'history' => 0,
        ]);
    }

    public function test_non_root_cannot_copy_previous_treatment(): void
    {
        $nurse = $this->user('NURSE');
        [, $active] = $this->treatment($nurse, '2026-08-18', 1);

        $this->actingAs($nurse)
            ->post(route('treatment.copyPrevious', $active))
            ->assertForbidden();
    }

    public function test_root_cannot_copy_treatment_older_than_thirty_days(): void
    {
        $root = $this->user('ROOT');
        $nurse = $this->user('NURSE');
        $patient = $this->patient();
        $previousActive = ActivePatient::create([
            'patient_id' => $patient->id,
            'date' => '2026-07-18',
            'active' => 0,
        ]);
        NursePatient::create([
            'active_patient_id' => $previousActive->id,
            'user_id' => $nurse->id,
            'date' => '2026-07-18',
            'history' => 1,
            'finalized_at' => '2026-07-18 10:00:00',
        ]);
        $currentActive = ActivePatient::create([
            'patient_id' => $patient->id,
            'date' => '2026-08-18',
            'active' => 0,
        ]);
        NursePatient::create([
            'active_patient_id' => $currentActive->id,
            'user_id' => $nurse->id,
            'date' => '2026-08-18',
            'history' => 0,
        ]);

        $this->actingAs($root)
            ->post(route('treatment.copyPrevious', $currentActive))
            ->assertRedirect(route('treatment.index'))
            ->assertSessionHas('Error', 'No existe un tratamiento anterior dentro de los últimos 30 días.');
    }

    private function treatment(User $assignedUser, string $date, int $scheduleId): array
    {
        $patient = $this->patient();
        $active = ActivePatient::create([
            'patient_id' => $patient->id,
            'date' => $date,
            'active' => 0,
        ]);
        $assignment = NursePatient::create([
            'active_patient_id' => $active->id,
            'user_id' => $assignedUser->id,
            'date' => $date,
            'history' => 0,
        ]);

        DB::table('schedules_patient')->insert([
            'schedules_id' => $scheduleId,
            'patient_id' => $patient->id,
            'date' => $date,
            'machine_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            'dialysis_monitoring',
            'dialysis_prescription',
            'pre_hemodialysis',
            'trans_hemodialysis',
            'post_hemodialysis',
            'evaluation_risk',
            'nurse_valuation',
            'time_out',
            'verification',
        ] as $table) {
            DB::table($table)->insert([
                'patient_id' => $patient->id,
                'history' => 0,
                'time' => '02:00:00',
                'hour' => '02:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$patient, $active, $assignment];
    }

    private function user(string $position): User
    {
        return User::forceCreate([
            'name' => 'Usuario',
            'position' => $position,
            'email' => uniqid().'@example.test',
            'password' => 'password',
            'enabled' => true,
        ]);
    }

    private function patient(): Patient
    {
        return Patient::create([
            'expedient_number' => uniqid('EXP'),
            'name' => 'Paciente',
            'last_name' => 'Prueba',
            'last_name_two' => 'Sistema',
            'birth_date' => '1980-01-01',
            'date_entry' => '2020-01-01',
            'gender' => 'M',
        ]);
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
            $table->timestamps();
        });
        Schema::create('patient', function (Blueprint $table) {
            $table->id();
            $table->string('expedient_number');
            $table->string('name');
            $table->string('last_name');
            $table->string('last_name_two')->nullable();
            $table->date('birth_date');
            $table->date('date_entry');
            $table->string('gender');
            $table->timestamps();
        });
        Schema::create('active_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->date('date');
            $table->boolean('active');
            $table->timestamps();
        });
        Schema::create('nurse_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('active_patient_id');
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->boolean('history')->default(false);
            $table->unsignedBigInteger('finalized_by')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->date('exceptional_start_date')->nullable();
            $table->timestamps();
        });
        Schema::create('schedules_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedules_id');
            $table->unsignedBigInteger('patient_id');
            $table->date('date');
            $table->unsignedBigInteger('machine_id');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('machine_number');
            $table->timestamps();
        });

        foreach ([
            'dialysis_monitoring',
            'dialysis_prescription',
            'pre_hemodialysis',
            'trans_hemodialysis',
            'post_hemodialysis',
            'evaluation_risk',
            'nurse_valuation',
            'time_out',
            'verification',
            'medication_administration',
            'oxygen_therapy',
            'double_verifications',
        ] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id');
                $table->boolean('history')->default(false);
                $table->string('time')->nullable();
                $table->string('hour')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('dialysis_monitoring', function (Blueprint $table) {
            $table->timestamp('date_hour')->nullable();
            $table->string('machine_number')->nullable();
            $table->integer('session_number')->nullable();
            $table->string('vascular_access')->nullable();
        });
        Schema::table('dialysis_prescription', function (Blueprint $table) {
            $table->string('blood_flux')->nullable();
        });
        Schema::table('pre_hemodialysis', function (Blueprint $table) {
            $table->decimal('previous_initial_weight')->nullable();
            $table->decimal('previous_final_weight')->nullable();
            $table->decimal('previous_weight_gain')->nullable();
            $table->decimal('initial_weight')->nullable();
            $table->decimal('dry_weight')->nullable();
            $table->integer('reuse_number')->nullable();
        });
        Schema::table('trans_hemodialysis', function (Blueprint $table) {
            $table->integer('heart_rate')->nullable();
        });
        Schema::table('post_hemodialysis', function (Blueprint $table) {
            $table->decimal('weight_out')->nullable();
        });
        Schema::table('evaluation_risk', function (Blueprint $table) {
            $table->string('fase')->nullable();
            $table->string('result')->nullable();
        });
        Schema::table('nurse_valuation', function (Blueprint $table) {
            $table->string('fase')->nullable();
            $table->text('nurse_valuation')->nullable();
            $table->text('nurse_intervention')->nullable();
        });
        Schema::table('time_out', function (Blueprint $table) {
            $table->boolean('patient_identification')->default(false);
        });
        Schema::table('verification', function (Blueprint $table) {
            $table->boolean('patient_name')->default(false);
        });
    }
}
