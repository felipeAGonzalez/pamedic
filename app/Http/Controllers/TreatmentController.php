<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\NursePatient;
use App\Models\ActivePatient;
use App\Models\DialysisMonitoring;
use App\Models\DialysisPrescription;
use App\Models\TransHemodialysis;
use App\Models\PreHemodialysis;
use App\Models\PostHemoDialysis;
use App\Models\EvaluationRisk;
use App\Models\NurseEvaluation;
use App\Models\MedicationAdministration;
use App\Models\User;
use App\Models\Medicine;
use App\Models\Patient;

class TreatmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $nursePatients = NursePatient::where(['user_id' => Auth::user()->id,'history' => 0])->get();
        $patients = $nursePatients->map(function ($nursePatients) {
            return $nursePatients->active_patient->patient;
        });
        return view('treatment.index', compact('patients','user'));
    }
    public function create(Request $request,$id)
    {
        $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $id, 'history' =>  0])->orderBy('date_hour','DESC')->first();
        if ($dialysisMonitoring) {
            return view('treatment.form', compact('dialysisMonitoring'));
        }
        return view('treatment.form', compact('id'));
    }
    public function createPres(Request $request,$id)
    {
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($dialysisPrescription) {
            return view('treatment.formPres', compact('dialysisPrescription'));
        }
        return view('treatment.formPres', compact('id'));
    }
    public function createPreHemo(Request $request,$id)
    {
        $preHemodialysis = PreHemodialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($preHemodialysis) {
            return view('treatment.formPreH', compact('preHemodialysis'));
        }
        return view('treatment.formPreH', compact('id'));
    }
    public function createTransHemo(Request $request,$id)
    {
        $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $id, 'history' =>  0])->orderBy('date_hour','DESC')->first();
        if (!$dialysisMonitoring) {
            return redirect()->route('treatment.index')->with('error', 'Primero debe llenar el monitoreo de diálisis');
        }
        $hour = date('H:i', strtotime($dialysisMonitoring->date_hour));
        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('time','ASC')->get();
        if (($transHemodialysis->count() > 0)) {
                return view('treatment.formTransH', compact('transHemodialysis'));
            }
        for ($i=0; $i <13 ; $i++) {
                TransHemodialysis::create([
                    'patient_id' => $id,
                    'time' => date('H:i', strtotime($hour) + ($i * 15 * 60)),
                    'arterial_pressure' => 0,
                    'mean_pressure' => 0,
                    'heart_rate' => 0,
                    'respiratory_rate' => 0,
                    'temperature' => 0,
                    'arterial_pressure_monitor' => 0,
                    'venous_pressure_monitor' => 0,
                    'transmembrane_pressure_monitor' => 0,
                    'blood_flow' => 0,
                    'ultrafiltration' => 0,
                    'heparin' => 0,
                    'observations' => 0
                ]);
            }
            $transHemodialysis = TransHemodialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('time','ASC')->get();
            return view('treatment.formTransH', compact('transHemodialysis'));
        }

    public function createPostHemo(Request $request,$id)
    {
        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($postHemoDialysis) {
            return view('treatment.formPostH', compact('postHemoDialysis'));
        }
        return view('treatment.formPostH', compact('id'));
    }
    public function createEvaluation(Request $request,$id)
    {
        $fase = [0 => 'Pre-Hemodiálisis',
                 1 => 'Trans-Hemodiálisis',
                 2 => 'Post-Hemodiálisis'
                ];

        $evaluationRisk = EvaluationRisk::where(['patient_id' => $id, 'history' =>  0])->orderBy('hour','ASC')->get();
        if (($evaluationRisk->count() > 0)) {

            return view('treatment.formEvaluation', compact('evaluationRisk'));
            }
            $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $id, 'history' =>  0])->orderBy('date_hour','ASC')->first();
        if (!$dialysisMonitoring) {
            return redirect()->route('treatment.index')->with('error', 'Primero debe llenar el monitoreo de diálisis');
        }
        $hour = date('H:i', strtotime($dialysisMonitoring->date_hour));
        for ($i=0; $i < 3 ; $i++) {
            EvaluationRisk::create([
                	'patient_id' => $id,
                	'fase' => $fase[$i],
                    'hour' => date('H:i', strtotime($hour) + ($i * 60 * 60)),
                	'result' => '0',
                	'fall_risk_trans' => 'low'
                ]);
            }
            $evaluationRisk = EvaluationRisk::where(['patient_id' => $id, 'history' =>  0])->orderBy('hour','ASC')->get();
            return view('treatment.formEvaluation', compact('evaluationRisk'));
    }
    public function createEvaluationNurse(Request $request,$id)
    {
       $fase = [ 0 => 'Pre-Hemodiálisis',
                 1 => 'Trans-Hemodiálisis',
                 2 => 'Post-Hemodiálisis'
                ];

        $nurseValo = NurseEvaluation::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','ASC')->get();
        if (($nurseValo->count() > 0)) {
            return view('treatment.formNurseValo', compact('nurseValo'));
            }
        for ($i=0; $i < 3 ; $i++) {
            NurseEvaluation::create([
                	'patient_id' => $id,
                    'nurse_valuation' => '',
                	'fase' => $fase[$i],
                    'nurse_intervention' => '',
                ]);
            }
            $nurseValo = NurseEvaluation::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','ASC')->get();
            return view('treatment.formNurseValo', compact('nurseValo'));
    }
    public function createMedicineAdmin(Request $request,$id)
    {
        $medicineAdministration = MedicationAdministration::where(['patient_id' => $id, 'history' =>  0])
            ->whereDate('created_at', now())
            ->orderBy('created_at','DESC')
            ->get();
        $patient = Patient::where('id',$id)->first();
        $users = User::where('position','NURSE')->get();
        $medicines = Medicine::all();
        if ($medicineAdministration) {
            return view('treatment.formMedicineA', compact('id','medicineAdministration','users','medicines','patient'))->with('success', 'Medicamento asignado exitosamente');
        }
        return view('treatment.formMedicineA', compact('id','users','medicines','patient'));
    }
    public function fill(Request $request)
    {
        $validator = $request->validate([
            'date_hour' => 'required|date',
            'vascular_access' => 'required|in:fistula,catheter',
            'catheter_type' => 'in:tunneling,no_tunneling',
            'implantation' => 'required|in:femoral,yugular,subclavia,brazo,antebrazo',
            'needle_mesure' => 'integer',
            'machine_number' => 'required|integer',
            'session_number' => 'required|integer',
            'side' => 'required|in:right,left',
            'collocation_date' => 'required|date',
            'serology' => 'required|in:positivo,negativo',
            'serology_date' => 'required|date',
            'blood_type' => 'required|string',
            'allergy' => 'required|string',
            'diagnostic' => 'required|string',
        ]);

        $dialysisMonitoring = new DialysisMonitoring();
        $dialysisMonitoring->patient_id  = $request->input('patient_id');
        $dialysisMonitoring->date_hour = $request->input('date_hour');
        $dialysisMonitoring->machine_number = $request->input('machine_number');
        $dialysisMonitoring->session_number = $request->input('session_number');
        $dialysisMonitoring->vascular_access = $request->input('vascular_access');
        $dialysisMonitoring->catheter_type = $request->input('catheter_type');
        $dialysisMonitoring->implantation = $request->input('implantation');
        $dialysisMonitoring->needle_mesure = $request->input('needle_mesure');
        $dialysisMonitoring->side = $request->input('side');
        $dialysisMonitoring->collocation_date = $request->input('collocation_date');
        $dialysisMonitoring->serology = $request->input('serology');
        $dialysisMonitoring->serology_date = $request->input('serology_date');
        $dialysisMonitoring->blood_type = $request->input('blood_type');
        $dialysisMonitoring->allergy = $request->input('allergy');
        $dialysisMonitoring->diagnostic = $request->input('diagnostic');
        $dialysisMonitoring->save();

        return redirect()->route('treatment.index')->with('success', 'Monitoreo de diálisis guardado exitosamente');

    }
    public function fillPres(Request $request)
    {

       $validator = $request->validate([
            'type_dialyzer' => 'required|in:HF80S,F6ELISIO21H,F6ELISIO19H',
            'time' => 'required|numeric|min:10|max:180',
            'blood_flux' => 'required|string',
            'flux_dialyzer' => 'required|string',
            'heparin' => 'required|string',
            'schedule_ultrafilter' => 'required|string',
            'profile_ultrafilter' => 'required|string',
            'sodium_profile' => 'required|string',
            'machine_temperature' => 'required|string',
        ]);
        $dialysisPrescription = new DialysisPrescription();
        $dialysisPrescription->patient_id  = $request->input('patient_id');
        $dialysisPrescription->type_dialyzer = $request->input('type_dialyzer');
        $dialysisPrescription->time = $request->input('time');
        $dialysisPrescription->blood_flux = $request->input('blood_flux');
        $dialysisPrescription->flux_dialyzer = $request->input('flux_dialyzer');
        $dialysisPrescription->heparin = $request->input('heparin');
        $dialysisPrescription->schedule_ultrafilter = $request->input('schedule_ultrafilter');
        $dialysisPrescription->profile_ultrafilter = $request->input('profile_ultrafilter');
        $dialysisPrescription->sodium_profile = $request->input('sodium_profile');
        $dialysisPrescription->machine_temperature = $request->input('machine_temperature');
        $dialysisPrescription->save();

        return redirect()->route('treatment.index')->with('success', 'Prescripción de diálisis guardada exitosamente');
    }
    public function fillPreHemo(Request $request){
        $validator = $request->validate([
            'previous_initial_weight' => 'required|numeric',
            'previous_final_weight' => 'required|numeric',
            'previous_weight_gain' => 'required|numeric',
            'initial_weight' => 'required|numeric',
            'dry_weight' => 'required|numeric',
            'weight_gain' => 'required|numeric',
            'reuse_number' => 'required|numeric',
            'sitting_blood_pressure' => 'required|numeric',
            'standing_blood_pressure' => 'required|numeric',
            'body_temperature' => 'required|numeric',
            'heart_rate' => 'required|numeric',
            'respiratory_rate' => 'required|numeric',
            'oxygen_saturation' => 'required|numeric',
            'conductivity' => 'required|numeric',
            'destrostix' => 'required|numeric',
            'itchiness' => 'required|numeric',
            'pallor_skin' => 'required|numeric',
            'edema' => 'required|numeric',
            'vascular_access_conditions' => 'required|numeric',
            'fall_risk' => 'required|numeric',
            'observations' => 'required|string',
        ]);
        $preHemodialysis = new PreHemodialysis();
        $preHemodialysis->patient_id  = $request->input('patient_id');
        $preHemodialysis->previous_initial_weight = $request->input('previous_initial_weight');
        $preHemodialysis->previous_final_weight = $request->input('previous_final_weight');
        $preHemodialysis->previous_weight_gain = $request->input('previous_weight_gain');
        $preHemodialysis->initial_weight = $request->input('initial_weight');
        $preHemodialysis->dry_weight = $request->input('dry_weight');
        $preHemodialysis->weight_gain = $request->input('weight_gain');
        $preHemodialysis->reuse_number = $request->input('reuse_number');
        $preHemodialysis->sitting_blood_pressure = $request->input('sitting_blood_pressure');
        $preHemodialysis->standing_blood_pressure = $request->input('standing_blood_pressure');
        $preHemodialysis->body_temperature = $request->input('body_temperature');
        $preHemodialysis->heart_rate = $request->input('heart_rate');
        $preHemodialysis->respiratory_rate = $request->input('respiratory_rate');
        $preHemodialysis->oxygen_saturation = $request->input('oxygen_saturation');
        $preHemodialysis->conductivity = $request->input('conductivity');
        $preHemodialysis->destrostix = $request->input('destrostix');
        $preHemodialysis->itchiness = $request->input('itchiness');
        $preHemodialysis->pallor_skin = $request->input('pallor_skin');
        $preHemodialysis->edema = $request->input('edema');
        $preHemodialysis->vascular_access_conditions = $request->input('vascular_access_conditions');
        $preHemodialysis->fall_risk = $request->input('fall_risk');
        $preHemodialysis->observations = $request->input('observations');
        $preHemodialysis->save();
        return redirect()->route('treatment.index')->with('success', 'Datos de la pre-diálisis guardada exitosamente');

    }
    public function fillTransHemo(Request $request){

        $validator = $request->validate([
            'time.*' => 'required|date_format:H:i:s',
            'arterial_pressure.*' => 'required|string',
            'mean_pressure.*' => 'required|numeric',
            'heart_rate.*' => 'required|numeric',
            'respiratory_rate.*' => 'required|numeric',
            'temperature.*' => 'required|numeric',
            'arterial_pressure_monitor.*' => 'required|string',
            'venous_pressure_monitor.*' => 'required|numeric',
            'transmembrane_pressure_monitor.*' => 'required|numeric',
            'blood_flow.*' => 'required|numeric',
            'ultrafiltration.*' => 'required|numeric',
            'heparin.*' => 'required|numeric',
            'observations.*' => 'required|string',
        ]);
        foreach ($request->input('time') as $key => $value) {
            TransHemodialysis::updateOrCreate(
            ['patient_id' => $request->input('patient_id'),
             'time' => $value],
            [
                'arterial_pressure' => floatval($request->input('arterial_pressure')[$key]),
                'mean_pressure' => $request->input('mean_pressure')[$key],
                'heart_rate' => $request->input('heart_rate')[$key],
                'respiratory_rate' => $request->input('respiratory_rate')[$key],
                'temperature' => $request->input('temperature')[$key],
                'arterial_pressure_monitor' => $request->input('arterial_pressure_monitor')[$key],
                'venous_pressure_monitor' => $request->input('venous_pressure_monitor')[$key],
                'transmembrane_pressure_monitor' => $request->input('transmembrane_pressure_monitor')[$key],
                'blood_flow' => $request->input('blood_flow')[$key],
                'ultrafiltration' => $request->input('ultrafiltration')[$key],
                'heparin' => $request->input('heparin')[$key],
                'observations' => $request->input('observations')[$key],
            ]
            );
        }
        return redirect()->route('treatment.index')->with('success', 'Datos de guardados exitosamente');
    }
    public function fillPostHemo(Request $request){
        $validator = $request->validate([
            'final_ultrafiltration' => 'required|numeric',
            'treated_blood' => 'required|numeric',
            'ktv' => 'required|numeric',
            'patient_temperature' => 'required|numeric',
            'blood_pressure_stand' => 'required|numeric',
            'blood_pressure_sit' => 'required|numeric',
            'respiratory_rate' => 'required|numeric',
            'heart_rate' => 'required|numeric',
            'weight_out' => 'required|numeric',
            'fall_risk' => 'required|numeric',
        ]);

        $postHemoDialysis = new PostHemoDialysis();
        $postHemoDialysis->patient_id  = $request->input('patient_id');
        $postHemoDialysis->final_ultrafiltration = $request->input('final_ultrafiltration');
        $postHemoDialysis->treated_blood = $request->input('treated_blood');
        $postHemoDialysis->ktv = $request->input('ktv');
        $postHemoDialysis->patient_temperature = $request->input('patient_temperature');
        $postHemoDialysis->blood_pressure_stand = $request->input('blood_pressure_stand');
        $postHemoDialysis->blood_pressure_sit = $request->input('blood_pressure_sit');
        $postHemoDialysis->respiratory_rate = $request->input('respiratory_rate');
        $postHemoDialysis->heart_rate = $request->input('heart_rate');
        $postHemoDialysis->weight_out = $request->input('weight_out');
        $postHemoDialysis->fall_risk = $request->input('fall_risk');
        $postHemoDialysis->save();
        return redirect()->route('treatment.index')->with('success', 'Datos de guardados exitosamente');
    }
    public function fillEvaluation(Request $request){

        $validator = $request->validate([
            'fase.*' => 'required|string',
            'hour.*' => 'required|date_format:H:i:s',
            'result.*' => 'required|numeric',
            // 'fall_risk_trans.*' => 'required|string',
        ]);

        foreach ($request->input('hour') as $key => $value) {
            EvaluationRisk::updateOrCreate(
            ['patient_id' => $request->input('patient_id')[$key],
             'hour' => $value],
            [
            'fase' => $request->input('fase')[$key],
            'result' => $request->input('score')[$key],
            // 'fall_risk_trans' => $request->input('fall_risk_trans')[$key] ?? null,
            ]
            );
        }
        return redirect()->route('treatment.index')->with('success', 'Datos de guardados exitosamente');
    }

    public function fillNurseEvaluation(Request $request){
        $validator = $request->validate([
            'fase.*' => 'required|string',
            'nurse_valuation.*' => 'required|string',
            'nurse_intervention.*' => 'required|string',
        ]);
        foreach ($request->input('fase') as $key => $value) {
            NurseEvaluation::updateOrCreate(
            ['patient_id' => $request->input('patient_id')[$key],
             'fase' => $value],
            [
            'nurse_valuation' => $request->input('nurse_valuation')[$key],
            'nurse_intervention' => $request->input('nurse_intervention')[$key],
            ]
            );
        }
        return redirect()->route('treatment.index')->with('success', 'Datos de guardados exitosamente');
    }
    public function fillMedicineAdmin(Request $request)
    {
        $validator = $request->validate([
            'nurse_prepare_id' => 'required|integer',
            'nurse_admin_id' => 'required|integer',
            'medicine_id' => 'required|integer',
            'dilution' => 'required|string',
            'velocity' => 'required|string',
            'hour' => 'required|date_format:H:i',
            'due_date' => 'required|date',
        ]);
        $medicineAdministration = new MedicationAdministration();
        $medicineAdministration->patient_id  = $request->input('patient_id');
        $medicineAdministration->nurse_prepare_id = $request->input('nurse_prepare_id');
        $medicineAdministration->nurse_admin_id = $request->input('nurse_admin_id');
        $medicineAdministration->medicine_id = $request->input('medicine_id');
        $medicineAdministration->dilution = $request->input('dilution');
        $medicineAdministration->velocity = $request->input('velocity');
        $medicineAdministration->hour = $request->input('hour');
        $medicineAdministration->due_date = $request->input('due_date');
        $medicineAdministration->save();
        $patient = Patient::where('id',$medicineAdministration->patient_id)->first();
        $medicineAdministration = MedicationAdministration::where(['patient_id' => $request->input('patient_id'), 'history' =>  0])
            ->whereDate('created_at', now())
            ->orderBy('created_at','DESC')
            ->get();
        $users = User::where('position','NURSE')->get();
        $medicines = Medicine::all();
        return view('treatment.formMedicineA', compact('medicineAdministration','users','medicines','patient'))->with('success', 'Medicamento asignado exitosamente');
    }
    public function destroy($id)
    {
        $medicine = MedicationAdministration::findOrFail($id);
        $medicineAdministration =$medicine;
        $patient = Patient::where('id',$medicineAdministration->patient_id)->first();
        $users = User::where('position','NURSE')->get();
        $medicines = Medicine::all();
        $medicine->delete();
        $medicineAdministration = MedicationAdministration::where(['patient_id' => $patient->id, 'history' =>  0])
            ->whereDate('created_at', now())
            ->orderBy('created_at','DESC')
            ->get();
        return view('treatment.formMedicineA', compact('medicineAdministration','users','medicines','patient'))->with('success', 'Medicamento eliminado exitosamente');
    }
    public function finaliceTreatment(Request $request,$id)
    {
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if (!$dialysisPrescription) {
            $error = ValidationException::withMessages(['Error' => 'Primero debe llenar la prescripción de diálisis']);
            throw $error;
        }
        $dialysisPrescription->history = 1;
        $dialysisPrescription->save();
        $preHemodialysis = PreHemodialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if (!$preHemodialysis) {
            $error = ValidationException::withMessages(['Error' => 'Primero debe llenar la pre-diálisis']);
            throw $error;
        }
        $preHemodialysis->history = 1;
        $preHemodialysis->save();
        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('time','ASC')->get();
        foreach ($transHemodialysis as $trans) {
            $trans->history = 1;
            $trans->save();
        }
        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        $postHemoDialysis->history = 1;
        $postHemoDialysis->save();
        $evaluationRisk = EvaluationRisk::where(['patient_id' => $id, 'history' =>  0])->orderBy('hour','ASC')->get();
        foreach ($evaluationRisk as $eval) {
            $eval->history = 1;
            $eval->save();
        }
        $nurseValo = NurseEvaluation::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','ASC')->get();
        foreach ($nurseValo as $nurse) {
            $nurse->history = 1;
            $nurse->save();
        }
        $medicineAdministration = MedicationAdministration::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','ASC')->get();
        foreach ($medicineAdministration as $medicine) {
            $medicine->history = 1;
            $medicine->save();
        }
        $activePatient = ActivePatient::where(['patient_id' => $id, 'active' => 0, 'date' => date('Y-m-d')])->first();
        if (!$activePatient) {
            $error = ValidationException::withMessages(['Error' => 'Paciente no encontrado']);
            throw $error;
            }
        $nursePatients = NursePatient::where(['active_patient_id' => $activePatient->id,'history' => 0])->first();
        $nursePatients->history = 1;
        $nursePatients->save();
        return redirect()->route('treatment.index')->with('success', 'Tratamiento finalizado exitosamente');
    }

}
