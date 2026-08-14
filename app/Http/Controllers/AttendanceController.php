<?php

namespace App\Http\Controllers;

use App\Models\ActivePatient;
use App\Models\Machine;
use App\Models\NursePatient;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\SchedulePatients;
use DateTime;
use DateTimeZone;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $patients = [];

        return view('attendance.index', compact('patients'));
    }

    public function attendanceSchedule(Request $request)
    {
        $patients = [];

        return view('attendance.attendance_schedule', compact('patients'));
    }

    public function searchSchedule(Request $request)
    {
        $search = $request->query('search');

        $patients = Patient::query();
        $schedules = Schedule::query();
        if ($search ?? false) {
            $patients = Patient::where('expedient_number', 'LIKE', '%'.$search.'%')->orWhere('name', 'LIKE', '%'.$search.'%')->orWhere('last_name', 'LIKE', '%'.$search.'%')->orWhere('last_name_two', 'LIKE', '%'.$search.'%');
        }
        $patients = $patients->get();

        if ($patients->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'Paciente no encontrado']);
            throw $error;
        }
        $schedules = $schedules->get();
        $machines = Machine::orderBy('id')->get();

        return view('attendance.attendance_schedule', compact('patients', 'schedules', 'machines'));

    }

    public function search(Request $request)
    {
        $search = $request->query('search');

        $patients = Patient::query();
        $schedules = Schedule::query();
        if ($search ?? false) {
            $patients = Patient::where('expedient_number', 'LIKE', '%'.$search.'%')->orWhere('name', 'LIKE', '%'.$search.'%')->orWhere('last_name', 'LIKE', '%'.$search.'%')->orWhere('last_name_two', 'LIKE', '%'.$search.'%');
        }
        $patients = $patients->get();

        if ($patients->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'Paciente no encontrado']);
            throw $error;
        }
        $machines = Machine::orderBy('id')->get();
        $schedules = $schedules->get();

        return view('attendance.index', compact('patients', 'schedules', 'machines'));

    }

    public function register(Request $request, $id)
    {
        $request->validate([
            'schedule_id' => 'nullable|exists:schedules,id',
        ]);
        $patient = Patient::findOrFail($id);

        $timezone = new DateTimeZone('America/Mexico_City');
        $date = new DateTime('now', $timezone);
        $today = $date->format('Y-m-d');
        $sessionDate = $request->date ?? $today;
        $scheduleId = $request->schedule_id
            ?? Schedule::where('schedule', 'EMERGENCY')->value('id');
        $alreadyScheduled = SchedulePatients::where('patient_id', $id)
            ->whereDate('date', $sessionDate)
            ->exists();

        if ($alreadyScheduled) {
            throw ValidationException::withMessages([
                'Error' => 'El paciente ya está programado en un turno este día',
            ]);
        }

        $existingTodayPatient = ActivePatient::where('patient_id', $id)
            ->where('date', $sessionDate)
            ->first();

        if ($existingTodayPatient) {
            $assignedPatient = NursePatient::where([
                'active_patient_id' => $existingTodayPatient->id,
                'history' => 0,
            ])->first();
            if ($assignedPatient) {
                throw ValidationException::withMessages([
                    'Error' => 'El paciente ya está en tratamiento',
                ]);
            }
            throw ValidationException::withMessages([
                'Error' => 'El paciente ya tiene asistencia registrada',
            ]);
        }
        try {
            SchedulePatients::create([
                'schedules_id' => $scheduleId,
                'patient_id' => $id,
                'date' => $sessionDate,
                'machine_id' => $request->machine_id,
            ]);
            ActivePatient::create([
                'patient_id' => $id,
                'date' => $sessionDate,
                'active' => 1,
            ]);

        } catch (\Illuminate\Database\QueryException $e) {

            if ($e->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'Error' => 'El paciente ya tiene asistencia registrada para esta fecha',
                ]);
            }

            throw $e;
        }

        return redirect()->route('attendance.attendanceSchedule')
            ->with('message', 'Asistencia registrada correctamente');
    }

    public function list(Request $request)
    {
        return view('attendance.treatment', $this->availableTurnData());
    }

    public function refresh(Request $request)
    {
        $data = $this->availableTurnData();

        return response()->json([
            'html' => view('attendance.partials.treatment-accordions', $data)->render(),
            'current_turn_key' => $data['currentTurnKey'],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function asigne(Request $request, $id)
    {
        if (! in_array($request->user()->position, ['NURSE', 'MANAGER'], true)) {
            return $this->assignmentResponse($request, 'No tienes permiso para realizar esta acción.', 403);
        }

        try {
            $result = DB::transaction(function () use ($request, $id) {
                $activePatient = ActivePatient::where('patient_id', $id)
                    ->whereDate('date', today())
                    ->lockForUpdate()
                    ->first();

                if (! $activePatient) {
                    return ['message' => 'No se encontró un registro activo para este paciente.', 'status' => 404];
                }

                if (! $activePatient->active || NursePatient::where('active_patient_id', $activePatient->id)->exists()) {
                    return ['message' => 'Este paciente acaba de ser asignado a otro enfermero.', 'status' => 409];
                }

                NursePatient::create([
                    'active_patient_id' => $activePatient->id,
                    'date' => today(),
                    'user_id' => $request->user()->id,
                ]);
                $activePatient->update(['active' => 0]);

                return ['message' => 'Paciente asignado correctamente.', 'status' => 201];
            }, 3);
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                return $this->assignmentResponse($request, 'Este paciente acaba de ser asignado a otro enfermero.', 409);
            }
            Log::error('Error de base de datos al asignar paciente.', ['patient_id' => $id, 'user_id' => $request->user()->id, 'exception' => $exception]);

            return $this->assignmentResponse($request, 'No fue posible asignar al paciente. Inténtalo nuevamente.', 500);
        } catch (Throwable $exception) {
            Log::error('Error inesperado al asignar paciente.', ['patient_id' => $id, 'user_id' => $request->user()->id, 'exception' => $exception]);

            return $this->assignmentResponse($request, 'No fue posible asignar al paciente. Inténtalo nuevamente.', 500);
        }

        return $this->assignmentResponse($request, $result['message'], $result['status']);
    }

    private function assignmentResponse(Request $request, string $message, int $status)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->route('attendance.list')->with($status < 300 ? 'success' : 'error', $message);
    }

    private function availableTurnData(): array
    {
        $today = today()->toDateString();
        $schedules = Schedule::orderBy('schedule')->orderBy('id')->get();
        $scheduledPatients = SchedulePatients::query()
            ->whereDate('date', $today)
            ->whereHas('schedule')
            ->whereHas('patient.activePatients', fn ($query) => $query->whereDate('date', $today)->where('active', 1))
            ->with(['schedule', 'machine', 'patient.activePatients' => fn ($query) => $query->whereDate('date', $today)->where('active', 1)])
            ->get()
            ->sortBy(function (SchedulePatients $item) {
                $machine = $item->machine?->machine_number ?? $item->machine_id ?? '';
                $patient = $item->patient;

                return sprintf('%010s|%s|%s|%s|%010d', $machine, $patient->last_name, $patient->last_name_two, $patient->name, $patient->id);
            })->groupBy('schedules_id');

        $normal = $schedules->reject(fn (Schedule $schedule) => strtolower($schedule->schedule_type) === 'emergency')->values();
        $emergency = $schedules->filter(fn (Schedule $schedule) => strtolower($schedule->schedule_type) === 'emergency');
        $turns = collect();
        $emergencyPatients = $emergency->flatMap(fn (Schedule $schedule) => $scheduledPatients->get($schedule->id, collect()))->unique('patient_id')->values();

        if ($emergencyPatients->isNotEmpty()) {
            $schedule = $emergency->sortBy('schedule')->first();
            $turns->push(['key' => 'emergency', 'name' => 'Horario especial', 'start' => substr($schedule->schedule, 0, 5), 'patients' => $emergencyPatients]);
        }

        $names = ['Primer turno', 'Segundo turno', 'Tercer turno', 'Cuarto turno'];
        foreach ($normal as $index => $schedule) {
            $turns->push(['key' => 'schedule-'.$schedule->id, 'name' => $names[$index] ?? 'Turno '.($index + 1), 'start' => substr($schedule->schedule, 0, 5), 'patients' => $scheduledPatients->get($schedule->id, collect())->values()]);
        }

        $currentTurnKey = null;
        $currentTime = now()->format('H:i:s');
        foreach ($normal as $index => $schedule) {
            $nextStart = $normal->get($index + 1)?->schedule ?? '21:00:00';
            if ($currentTime >= $schedule->schedule && $currentTime < $nextStart && $currentTime < '21:00:00') {
                $currentTurnKey = 'schedule-'.$schedule->id;
                break;
            }
        }

        return compact('turns', 'currentTurnKey');
    }
}
