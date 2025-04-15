<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\ActivePatient;
use App\Models\NursePatient;
use Illuminate\Validation\ValidationException;
use DateTime;
use DateTimeZone;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $patients=[];
        return view('attendance.index',compact('patients'));
    }

    public function search(Request $request){
        $search = $request->query('search');

        $patients = Patient::query();

        if ($search ?? false) {
            $patients = Patient::where('expedient_number','LIKE','%'.$search.'%')->orWhere('name','LIKE','%'.$search.'%')->orWhere('last_name','LIKE','%'.$search.'%')->orWhere('last_name_two','LIKE','%'.$search.'%');
        }
        $patients = $patients->get();

        if ($patients->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'Paciente no encontrado']);
            throw $error;
        }
        return view('attendance.index', compact('patients'));

    }

    public function register(Request $request, $id){
        $patient = Patient::find($id);
        $existingPatient = ActivePatient::where(['patient_id' => $id,'active' => 1])->where('date', date('Y-m-d'))->first();
        $existingTodayPatient = ActivePatient::where(['patient_id' => $id])->where('date', date('Y-m-d'))->first();
        $timezone = new DateTimeZone('America/Mexico_City');
        $date = new DateTime('now', $timezone);
        if($existingTodayPatient){
            $assignedPatient = NursePatient::where(['active_patient_id' => $existingTodayPatient->id,'history' => 0])->first();
            if($assignedPatient)
                throw ValidationException::withMessages(['Error' => 'El paciente ya esta en tratamiento']);
        }
        if (! $existingPatient && ! $existingTodayPatient) {
            $activePatient = new ActivePatient();
            $activePatient->patient_id = $id;
            $activePatient->date = $date->format('Y-m-d');
            $activePatient->active = 1;
            $activePatient->save();
            return redirect()->route('attendance.index')->with('message', 'Asistencia registrada correctamente');
        }
            throw ValidationException::withMessages(['Error' => 'El paciente ya tiene asistencia registrada']);;

        $patients=[];
        return view('attendance.index',compact('patients'));
    }

    public function list(Request $request){
        $activePatients = ActivePatient::where('active',1)->get();

        $patients = $activePatients->map(function ($activePatients) {
            return $activePatients->patient;
        });
        $nursePatients = NursePatient::where('date', date('Y-m-d'))->get();
        return view('attendance.treatment', compact('patients','nursePatients'));
    }

    public function asigne(Request $request,$id){
        $activePatient = ActivePatient::where('patient_id', $id)->where('active', 1)->first();
        if ($activePatient) {
            $nursePatients = NursePatient::create([
                'active_patient_id' => $activePatient->id,
                'user_id' => $request->user()->id,
                'date' => date('Y-m-d'),
            ]);
            $activePatient->active = 0;
            $activePatient->save();
        } else {
            $message = ValidationException::withMessages(['Error' => 'No se encontró el paciente']);
            throw $message;
        }
        $activePatients = ActivePatient::where('active', 1)->get();
        $patients = $activePatients->map(function ($activePatients) {
            return $activePatients->patient;
        });
        return view('attendance.treatment', compact('patients'));
    }
}
