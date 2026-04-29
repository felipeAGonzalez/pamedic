<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\ActivePatient;
use App\Models\NursePatient;
use App\Models\Schedule;
use App\Models\SchedulePatients;
use App\Models\Machine;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateTimeZone;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $patients=[];
        return view('attendance.index',compact('patients'));
    }
    public function attendanceSchedule(Request $request)
    {
        $patients=[];
        return view('attendance.attendance_schedule',compact('patients'));
    }
 public function searchSchedule(Request $request){
        $search = $request->query('search');

        $patients = Patient::query();
        $schedules = Schedule::query();
        if ($search ?? false) {
            $patients = Patient::where('expedient_number','LIKE','%'.$search.'%')->orWhere('name','LIKE','%'.$search.'%')->orWhere('last_name','LIKE','%'.$search.'%')->orWhere('last_name_two','LIKE','%'.$search.'%');
        }
        $patients = $patients->get();

        if ($patients->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'Paciente no encontrado']);
            throw $error;
        }
        $schedules = $schedules->get();
        $machines = Machine::orderBy('id')->get();
        return view('attendance.attendance_schedule', compact('patients','schedules','machines'));

    }
    public function search(Request $request){
        $search = $request->query('search');

        $patients = Patient::query();
        $schedules = Schedule::query();
        if ($search ?? false) {
            $patients = Patient::where('expedient_number','LIKE','%'.$search.'%')->orWhere('name','LIKE','%'.$search.'%')->orWhere('last_name','LIKE','%'.$search.'%')->orWhere('last_name_two','LIKE','%'.$search.'%');
        }
        $patients = $patients->get();

        if ($patients->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'Paciente no encontrado']);
            throw $error;
        }
        $machines = Machine::orderBy('id')->get();
        $schedules = $schedules->get();
        return view('attendance.index', compact('patients','schedules','machines'));

    }

   public function register(Request $request, $id)
{
    $request->validate([
        'schedule_id' => 'nullable|exists:schedules,id'
    ]);
    $patient = Patient::findOrFail($id);

    $timezone = new DateTimeZone('America/Mexico_City');
    $date = new DateTime('now', $timezone);
    $today = $date->format('Y-m-d');
    $sessionDate = $request->date ?? $today;
    $scheduleId = $request->schedule_id
        ?? Schedule::where('schedule','EMERGENCY')->value('id');
    $alreadyScheduled = SchedulePatients::where('patient_id', $id)
        ->whereDate('date', $sessionDate)
        ->exists();

    if ($alreadyScheduled) {
        throw ValidationException::withMessages([
            'Error' => 'El paciente ya está programado en un turno este día'
        ]);
    }

    $existingTodayPatient = ActivePatient::where('patient_id',$id)
        ->where('date',$sessionDate)
        ->first();

    if ($existingTodayPatient) {
        $assignedPatient = NursePatient::where([
            'active_patient_id' => $existingTodayPatient->id,
            'history' => 0
        ])->first();
        if ($assignedPatient) {
            throw ValidationException::withMessages([
                'Error' => 'El paciente ya está en tratamiento'
            ]);
        }
        throw ValidationException::withMessages([
            'Error' => 'El paciente ya tiene asistencia registrada'
        ]);
    }
    try {
        SchedulePatients::create([
            'schedules_id' => $scheduleId,
            'patient_id' => $id,
            'date' => $sessionDate,
            'machine_id' => $request->machine_id
        ]);
        ActivePatient::create([
            'patient_id' => $id,
            'date' => $sessionDate,
            'active' => 1
        ]);

    } catch (\Illuminate\Database\QueryException $e) {

        if ($e->getCode() === '23000') {
            throw ValidationException::withMessages([
                'Error' => 'El paciente ya tiene asistencia registrada para esta fecha'
            ]);
        }

        throw $e;
}

    return redirect()->route('attendance.attendanceSchedule')
        ->with('message','Asistencia registrada correctamente');
}

    public function list(Request $request){
        $activePatients = ActivePatient::where('active',1)->where('date', date('Y-m-d'))->get();

        $patients = $activePatients->map(function ($activePatients) {
            return $activePatients->patient;
        });
        $nursePatients = NursePatient::where('date', date('Y-m-d'))->get();
        return view('attendance.treatment', compact('patients','nursePatients'));
    }

    public function asigne(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $activePatient = ActivePatient::where('patient_id', $id)
                ->where('active', 1)
                ->where('date', date('Y-m-d'))
                ->lockForUpdate()
                ->first();

            if (! $activePatient) {
                throw ValidationException::withMessages([
                    'error' => 'No se encontró el paciente activo'
                ]);
            }
            NursePatient::firstOrCreate(
                [
                    'active_patient_id' => $activePatient->id,
                    'date' => today(),
                ],
                [
                    'user_id' => $request->user()->id,
                ]
            );
            $activePatient->update(['active' => 0]);

        $activePatients = ActivePatient::where('active', 1)->get();
            $patients = $activePatients->map(function ($activePatients) {
                return $activePatients->patient;
            });
            return view('attendance.treatment', compact('patients'));
        });
    }
}
