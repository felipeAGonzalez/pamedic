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
use App\Models\MedicNote;
use App\Models\ActivePatient;
use App\Models\OxygenTherapy;
use App\Models\MedicationAdministration;
use Illuminate\Support\Facades\Auth;
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

    public function indexMedicNote(){
        $nursePatients = NursePatient::where(['date' => date('Y-m-d'),'history'=>1])->get();
        $activePatients = $nursePatients->map(function ($nursePatients) {
            return $nursePatients->active_patient;
        });

        return view('noteMedic.index', compact('activePatients'));
    }

    public function createMedicNote($id,$date = null){
        $date = $date ?? date('Y-m-d');
        $patient = Patient::findOrFail($id);
        $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $preHemodialysis = PreHemodialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        if ($transHemodialysis->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'No se han encontrado tratamientos finalizados']);
            throw $error;
        }
        $timeFirst = $transHemodialysis->first()->time;
        $transHemodialysisWithOutHash = $transHemodialysis->filter(function ($item) {
            return strpos($item->observations, '#') === false;
        });
        $timeLast = $transHemodialysisWithOutHash->last()->time;

        $totalTime = strtotime($timeLast) - strtotime($timeFirst);
        $hours = floor($totalTime / 3600);
        $minutes = floor(($totalTime % 3600) / 60);
        $seconds = $totalTime % 60;
        $totalTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        $medicNote = MedicNote::where(['patient_id' => $id , 'history' => 0])->whereDate('date',$date)->first();
        if (!$medicNote) {
            $medicNote = MedicNote::where(['patient_id' => $id , 'history' => 0])->orderby('date','desc')->first();
            if($medicNote){
                $medicNoteNew = $medicNote->replicate();
                $medicNoteNew->prognosis = '';
                $medicNoteNew->save();
                $medicNote = $medicNoteNew;
            }
        }
        $medicineAdministration = MedicationAdministration::where(['patient_id' => $id, 'history' =>  1])
            ->whereDate('created_at', $date)
            ->orderBy('created_at','DESC')
            ->get();
        return view('noteMedic.form', compact('patient','date','dialysisMonitoring','preHemodialysis','dialysisPrescription','postHemoDialysis','medicNote','medicineAdministration','totalTime'));
    }

    public function printMedicNote($id,$date = null){
        $date = $date ?? date('Y-m-d');
        $patient = Patient::findOrFail($id);
        $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $preHemodialysis = PreHemodialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        $timeFirst = $transHemodialysis->first()->time;
        $transHemodialysisWithOutHash = $transHemodialysis->filter(function ($item) {
            return strpos($item->observations, '#') === false;
        });
        $timeLast = $transHemodialysisWithOutHash->last()->time;

        $totalTime = strtotime($timeLast) - strtotime($timeFirst);
        $hours = floor($totalTime / 3600);
        $minutes = floor(($totalTime % 3600) / 60);
        $seconds = $totalTime % 60;
        $totalTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        $medicNote = MedicNote::where(['patient_id' => $id , 'history' => 0])->whereDate('date', $date)->first();
        $medicineAdministration = MedicationAdministration::where(['patient_id' => $id, 'history' =>  1])
            ->whereDate('created_at', $date)
            ->orderBy('created_at','DESC')
            ->get();
        $user = Auth::user();
        $pdf = Pdf::loadView('print.note', compact('date','patient', 'dialysisMonitoring', 'preHemodialysis', 'dialysisPrescription', 'postHemoDialysis', 'medicNote','user','medicineAdministration','totalTime'));
        return $pdf->stream('Nota Medica '.$date.'-'.substr($patient->expedient_number, -4) . '.pdf');
    }

    public function store(Request $request)
    {
        $validator = $request->validate([
            'patient_id' => 'required|exists:patient,id',
            'date' => 'required|date',
            'note_type' => 'required|string|max:1000',
            'patient' => 'required|string|max:1000',
            'subjective' => 'required|string|max:1000',
            'objective' => 'required|string|max:1000',
            'prognosis' => 'required|string|max:1000',
            'plan' => 'required|string|max:1000',
        ]);
        $validator['date'] = date('Y-m-d', strtotime($validator['date']));
        $medicNote = MedicNote::where(['patient_id' => $validator['patient_id'], 'date' => $validator['date'], 'history' => 0])->first();
        $medicNote = MedicNote::updateOrCreate(
            ['patient_id' => $validator['patient_id'], 'date' => $validator['date'], 'history' => 0],
            [
            'user_id' => $medicNote ? $medicNote->user_id : Auth::id(),
            'patient' => $validator['patient'],
            'note_type' => $validator['note_type'],
            'subjective' => $validator['subjective'],
            'objective' => $validator['objective'],
            'prognosis' => $validator['prognosis'],
            'plan' => $validator['plan'],
            ]
        );
        return redirect()->route('print.medicNote')->with('success', 'Nota medica guardada correctamente.');
    }
    public function searchMedicNote(Request $request){
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
        return view('noteMedic.index', compact('activePatients'));
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
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado los datos de pre hemodiálisis']);
            throw $error;
        }
        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        if ($transHemodialysis->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado los datos de trans hemodiálisis']);
            throw $error;
        }
        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        if ($postHemoDialysis == null) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado los datos de post hemodiálisis']);
            throw $error;
        }
        $activePatient = ActivePatient::where(['patient_id' => $id , 'date' => $date])->first();
        $user = NursePatient::where(['active_patient_id' => $activePatient->id , 'date' => $date])->first()->user;
        $evaluationRisk = EvaluationRisk::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        if ($evaluationRisk->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado la evaluación de caídas']);
            throw $error;
        }
        $oxygenTherapy = OxygenTherapy::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->first();
        $nurseValo = NurseEvaluation::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        if ($nurseValo->isEmpty()) {
            $error = ValidationException::withMessages(['Error' => 'No se ha encontrado la evaluación de enfermería']);
            throw $error;
        }
        $medicineAdmin = MedicationAdministration::where(['patient_id' => $id , 'history' => 1])->whereDate('created_at', $date)->get();
        $pdf = Pdf::loadView('print.paper', compact('date','patient', 'dialysisMonitoring', 'dialysisPrescription', 'transHemodialysis', 'preHemodialysis', 'postHemoDialysis','user','evaluationRisk','oxygenTherapy','nurseValo','medicineAdmin'));
        return $pdf->stream($date.'-'.substr($patient->expedient_number, -4) . '.pdf');
    }
}
