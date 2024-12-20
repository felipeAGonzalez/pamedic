<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NursePatient;
use App\Models\Patient;
use App\Models\DialysisMonitoring;
use App\Models\DialysisPrescription;
use App\Models\TransHemodialysis;
use App\Models\PreHemodialysis;
use App\Models\PostHemoDialysis;
use App\Models\EvaluationRisk;
use App\Models\NurseEvaluation;
use App\Models\ActivePatient;
use App\Models\MedicationAdministration;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;




class PrintController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $nursePatients = NursePatient::where(['date' => date('Y-m-d'),'history'=>1])->get();
        $activePatients = $nursePatients->map(function ($nursePatients) {
            return $nursePatients->active_patient;
        });
        return view('print.index', compact('activePatients'));
    }

    public function search(Request $request){
        $search = $request->query('search');
        $activePatients = [];
        if ($search ?? false) {
            $patients = Patient::where('expedient_number','LIKE','%'.$search.'%')->orWhere('name','LIKE','%'.$search.'%')->orWhere('last_name','LIKE','%'.$search.'%')->orWhere('last_name_two','LIKE','%'.$search.'%');
            $patients = $patients->get();
            if ($patients->isEmpty()) {
                $error = ValidationException::withMessages(['Error' => 'Paciente no encontrado']);
                throw $error;
            }
            $activePatients = ActivePatient::query();
            $activePatients = $activePatients->whereIn('patient_id', $patients->pluck('id'))->orderBy('date','desc')->get();
            if ($activePatients->isEmpty()) {
                $error = ValidationException::withMessages(['Error' => 'Paciente sin tratamientos']);
                throw $error;
            }
        }
        return view('print.index', compact('activePatients'));
    }

    public function printNurseExpedient($id,$date = null){
        $date = $date ?? date('Y-m-d');
        $patient = Patient::findOrFail($id);
        $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        if ($dialysisMonitoring == null) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado el monitoreo']);
            throw $error;
        }
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        if ($dialysisPrescription == null) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado la prescripción']);
            throw $error;
        }
        $preHemodialysis = PreHemodialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        if ($preHemodialysis == null) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado los datos de pre hemodialisis']);
            throw $error;
        }
        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        if ($transHemodialysis->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado los datos de trans hemodialisis']);
            throw $error;
        }
        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        if ($postHemoDialysis == null) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado los datos de post hemodialisis']);
            throw $error;
        }
        $user = auth()->user();
        $evaluationRisk = EvaluationRisk::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        if ($evaluationRisk->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado la evaluación de caidas']);
            throw $error;
        }
        $nurseValo = NurseEvaluation::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        if ($nurseValo->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado la evaluación de enfermería']);
            throw $error;
        }
        $medicineAdmin = MedicationAdministration::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        $pdf = Pdf::loadView('print.paper', compact('patient', 'dialysisMonitoring', 'dialysisPrescription', 'transHemodialysis', 'preHemodialysis', 'postHemoDialysis','user','evaluationRisk','nurseValo','medicineAdmin'));
        return $pdf->stream('expediente.pdf');
    }
}
