<?php

namespace Tests\Feature;

use App\Models\ActivePatient;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\SchedulePatients;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

    public function test_refresh_endpoint_returns_expected_json_and_excludes_assigned_patient(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('NURSE');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));

        $this->actingAs($user)->getJson('/attendance/list/refresh')
            ->assertOk()->assertJsonStructure(['html', 'current_turn_key', 'generated_at'])->assertSee($patient->name);

        $this->postJson("/attendance/nurseAsigne/{$patient->id}")->assertCreated();
        $this->getJson('/attendance/list/refresh')->assertOk()->assertDontSee($patient->name);
    }

    public function test_authorized_user_assigns_exactly_once_and_deactivates_patient(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $user = $this->user('MANAGER');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));
        $active = ActivePatient::where('patient_id', $patient->id)->firstOrFail();

        $this->actingAs($user)->postJson("/attendance/nurseAsigne/{$patient->id}")->assertCreated();

        $this->assertDatabaseCount('nurse_patient', 1);
        $this->assertDatabaseHas('nurse_patient', ['active_patient_id' => $active->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('active_patient', ['id' => $active->id, 'active' => 0]);
    }

    public function test_second_assignment_returns_conflict_without_creating_another_assignment(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));

        $this->actingAs($this->user('NURSE'))->postJson("/attendance/nurseAsigne/{$patient->id}")->assertCreated();
        $this->actingAs($this->user('NURSE'))->postJson("/attendance/nurseAsigne/{$patient->id}")
            ->assertConflict()->assertJson(['message' => 'Este paciente acaba de ser asignado a otro enfermero.']);
        $this->assertDatabaseCount('nurse_patient', 1);
    }

    public function test_unauthorized_user_cannot_assign(): void
    {
        Carbon::setTestNow('2026-08-13 10:30:00');
        $patient = $this->availablePatient($this->schedule('10:00:00', 'morning'));

        $this->actingAs($this->user('RECEPCIONIST'))->postJson("/attendance/nurseAsigne/{$patient->id}")->assertForbidden();
        $this->assertDatabaseCount('nurse_patient', 0);
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
        return User::create(['name' => 'Usuario', 'position' => $position, 'email' => uniqid().'@example.test', 'password' => 'password']);
    }

    private function schedule(string $time, string $type): Schedule
    {
        return Schedule::create(['schedule' => $time, 'schedule_type' => $type]);
    }

    private function availablePatient(Schedule $schedule, bool $active = true, ?Carbon $date = null): Patient
    {
        $date ??= today();
        $patient = Patient::create([
            'expedient_number' => uniqid('EXP'), 'name' => uniqid('Paciente'), 'last_name' => 'Prueba',
            'last_name_two' => 'Sistema', 'birth_date' => '1980-01-01', 'date_entry' => '2020-01-01', 'gender' => 'M',
        ]);
        SchedulePatients::create(['schedules_id' => $schedule->id, 'patient_id' => $patient->id, 'date' => $date, 'machine_id' => '1']);
        ActivePatient::create(['patient_id' => $patient->id, 'date' => $date, 'active' => $active]);

        return $patient;
    }
}
