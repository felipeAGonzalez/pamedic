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
        $patients = $nursePatients->map(function ($nursePatients) {
            return $nursePatients->active_patient->patient;
        });
        return view('print.index', compact('patients'));
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
        return view('print.index', compact('patients'));

    }
    public function printNurseExpedient($id, $date = null){
        // $date = \Carbon\Carbon::parse($date)->toIso8601String() ?? now()->toIso8601String();
        $date = $date ?? date('Y-m-d');
        $patient = Patient::findOrFail($id);
        $dialysisMonitoring = DialysisMonitoring::where('patient_id', $id)->orderBy('created_at', 'desc')->first();
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $preHemodialysis = PreHemodialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $user = auth()->user();
        $evaluationRisk = EvaluationRisk::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        $nurseValo = NurseEvaluation::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        $medicineAdmin = MedicationAdministration::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        \Log::info($evaluationRisk->toArray());
        $pdf = Pdf::loadView('print.paper', compact('patient', 'dialysisMonitoring', 'dialysisPrescription', 'transHemodialysis', 'preHemodialysis', 'postHemoDialysis','user','evaluationRisk','nurseValo','medicineAdmin'));
        return $pdf->stream('expediente.pdf');
    }
}
