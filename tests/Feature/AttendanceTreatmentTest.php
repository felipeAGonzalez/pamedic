<?php

namespace Tests\Feature;

use App\Models\ActivePatient;
use App\Models\Machine;
use App\Models\NursePatient;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\SchedulePatients;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceTreatmentTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_list_only_groups_active_patients_scheduled_today_and_puts_emergency_first(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('NURSE');
        $first = $this->schedule('06:00:00', 'morning');
        $emergency = $this->schedule('00:00:00', 'emergency');
        $emergencyPatient = $this->availablePatient($emergency);
        $normalPatient = $this->availablePatient($first);
        $inactive = $this->availablePatient($first, false);
        $tomorrow = $this->availablePatient($first, true, today()->addDay());

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertOk()->assertSeeInOrder(['Horario especial', $emergencyPatient->name, 'Primer turno', $normalPatient->name]);
        $response->assertDontSee($inactive->name)->assertDontSee($tomorrow->name);
    }

    public function test_emergency_is_omitted_when_it_has_no_available_patients(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('NURSE');
        $this->schedule('00:00:00', 'emergency');
        $this->availablePatient($this->schedule('06:00:00', 'morning'));

        $this->actingAs($user)->get('/attendance/list')->assertOk()->assertDontSee('Horario especial');
    }

    public function test_all_normal_turns_remain_visible_regardless_of_current_turn(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('NURSE');
        foreach (['06:00:00', '10:00:00', '14:00:00', '18:00:00'] as $index => $time) {
            $this->availablePatient($this->schedule($time, $index < 2 ? 'morning' : 'afternoon'));
        }

        $this->actingAs($user)->get('/attendance/list')->assertOk()->assertSee('Cuarto turno');
    }

    public function test_patients_are_shown_with_their_machine_in_numeric_machine_order(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('NURSE');
        $schedule = $this->schedule('10:00:00', 'morning');
        $machineTen = Machine::create(['machine_number' => '10', 'serial_number' => 'TEST-10']);
        $machineTwo = Machine::create(['machine_number' => '2', 'serial_number' => 'TEST-2']);
        $patientTen = $this->availablePatient($schedule, true, null, $machineTen->id);
        $patientTwo = $this->availablePatient($schedule, true, null, $machineTwo->id);

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertOk()
            ->assertSee('Máquina')
            ->assertSeeInOrder([$patientTwo->name, $patientTen->name]);
    }

    public function test_refresh_endpoint_returns_expected_json_and_excludes_assigned_patient(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('NURSE');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));
        $active = ActivePatient::where('patient_id', $patient->id)->firstOrFail();

        $this->actingAs($user)->getJson('/attendance/list/refresh')
            ->assertOk()
            ->assertJsonStructure(['html', 'current_turn_key', 'generated_at'])
            ->assertSee($patient->name);

        $this->postJson("/attendance/nurseAsigne/{$patient->id}", ['active_patient_id' => $active->id])
            ->assertCreated();
        $this->getJson('/attendance/list/refresh')->assertOk()->assertDontSee($patient->name);
    }

    public function test_authorized_user_assigns_exactly_once_and_deactivates_patient(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('MANAGER');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));
        $active = ActivePatient::where('patient_id', $patient->id)->firstOrFail();

        $this->actingAs($user)
            ->postJson("/attendance/nurseAsigne/{$patient->id}", ['active_patient_id' => $active->id])
            ->assertCreated();

        $this->assertDatabaseCount('nurse_patient', 1);
        $this->assertDatabaseHas('nurse_patient', ['active_patient_id' => $active->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('active_patient', ['id' => $active->id, 'active' => 0]);
    }

    public function test_second_assignment_returns_conflict_without_creating_another_assignment(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));
        $active = ActivePatient::where('patient_id', $patient->id)->firstOrFail();

        $this->actingAs($this->user('NURSE'))
            ->postJson("/attendance/nurseAsigne/{$patient->id}", ['active_patient_id' => $active->id])
            ->assertCreated();
        $this->actingAs($this->user('NURSE'))
            ->postJson("/attendance/nurseAsigne/{$patient->id}", ['active_patient_id' => $active->id])
            ->assertConflict()->assertJson(['message' => 'Este paciente acaba de ser asignado a otro enfermero.']);
        $this->assertDatabaseCount('nurse_patient', 1);
    }

    public function test_unauthorized_user_cannot_assign(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));
        $active = ActivePatient::where('patient_id', $patient->id)->firstOrFail();

        $this->actingAs($this->user('RECEPCIONIST'))
            ->postJson("/attendance/nurseAsigne/{$patient->id}", ['active_patient_id' => $active->id])
            ->assertForbidden();
        $this->assertDatabaseCount('nurse_patient', 0);
    }

    public function test_nurse_can_undo_own_assignment_when_no_clinical_data_exists(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('NURSE');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));
        $active = ActivePatient::where('patient_id', $patient->id)->firstOrFail();
        $active->update(['active' => 0]);
        $assignment = NursePatient::create([
            'active_patient_id' => $active->id,
            'user_id' => $user->id,
            'date' => today(),
        ]);

        $this->actingAs($user)->get(route('treatment.index'))
            ->assertOk()
            ->assertSee('Deshacer asignación');

        $this->actingAs($user)
            ->delete(route('treatment.assignment.undo', $active->id))
            ->assertRedirect(route('treatment.index'))
            ->assertSessionHas('success', 'Asignación deshecha correctamente.');

        $this->assertDatabaseMissing('nurse_patient', ['id' => $assignment->id]);
        $this->assertDatabaseHas('active_patient', ['id' => $active->id, 'active' => 1]);
    }

    public function test_assignment_cannot_be_undone_after_clinical_data_was_saved(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('NURSE');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));
        $active = ActivePatient::where('patient_id', $patient->id)->firstOrFail();
        $active->update(['active' => 0]);
        $assignment = NursePatient::create([
            'active_patient_id' => $active->id,
            'user_id' => $user->id,
            'date' => today(),
        ]);
        DB::table('double_verifications')->insert([
            'patient_id' => $patient->id,
            'history' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->get(route('treatment.index'))
            ->assertOk()
            ->assertDontSee('Deshacer asignación');

        $this->actingAs($user)
            ->delete(route('treatment.assignment.undo', $active->id))
            ->assertRedirect(route('treatment.index'))
            ->assertSessionHas('Error', 'No se puede deshacer la asignación porque el tratamiento ya tiene información clínica registrada.');

        $this->assertDatabaseHas('nurse_patient', ['id' => $assignment->id]);
        $this->assertDatabaseHas('active_patient', ['id' => $active->id, 'active' => 0]);
    }

    public function test_nurse_cannot_undo_another_nurses_assignment(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $owner = $this->user('NURSE');
        $otherNurse = $this->user('NURSE');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));
        $active = ActivePatient::where('patient_id', $patient->id)->firstOrFail();
        $active->update(['active' => 0]);
        $assignment = NursePatient::create([
            'active_patient_id' => $active->id,
            'user_id' => $owner->id,
            'date' => today(),
        ]);

        $this->actingAs($otherNurse)
            ->delete(route('treatment.assignment.undo', $active->id))
            ->assertForbidden();

        $this->assertDatabaseHas('nurse_patient', ['id' => $assignment->id]);
        $this->assertDatabaseHas('active_patient', ['id' => $active->id, 'active' => 0]);
    }

    public function test_schedule_patient_relationship_uses_schedules_id(): void
    {
        $schedule = $this->schedule('06:00:00', 'morning');
        $scheduledPatient = $this->availablePatient($schedule)->schedulePatients()->firstOrFail();

        $this->assertTrue($scheduledPatient->schedule->is($schedule));
        $this->assertTrue($schedule->schedulePatients->contains($scheduledPatient));
    }

    private function user(string $position): User
    {
        return User::forceCreate([
            'name' => 'Usuario',
            'last_name_one' => 'Prueba',
            'last_name_two' => 'Sistema',
            'profesional_id' => uniqid(),
            'position' => $position,
            'email' => uniqid().'@example.test', 'password' => 'password',
            'enabled' => true,
        ]);
    }

    private function schedule(string $time, string $type): Schedule
    {
        return Schedule::create(['schedule' => $time, 'schedule_type' => $type]);
    }

    private function availablePatient(Schedule $schedule, bool $active = true, ?Carbon $date = null, ?int $machineId = null): Patient
    {
        $date ??= today();
        $patient = Patient::create([
            'expedient_number' => uniqid('EXP'), 'name' => uniqid('Paciente'), 'last_name' => 'Prueba',
            'last_name_two' => 'Sistema', 'birth_date' => '1980-01-01', 'date_entry' => '2020-01-01', 'gender' => 'M',
        ]);
        SchedulePatients::create(['schedules_id' => $schedule->id, 'patient_id' => $patient->id, 'date' => $date, 'machine_id' => $machineId ?? '1']);
        ActivePatient::create(['patient_id' => $patient->id, 'date' => $date, 'active' => $active]);

        return $patient;
    }
}
