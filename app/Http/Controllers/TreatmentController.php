<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NursePatient;
use App\Models\DialysisMonitoring;
use App\Models\DialysisPrescription;
use App\Models\TransHemodialysis;
use App\Models\PreHemodialysis;
use App\Models\PostHemoDialysis;
use App\Models\EvaluationRisk;

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
        $nursePatients = NursePatient::where('user_id', Auth::user()->id)->get();
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
        $transHemodialysis = PostHemoDialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($transHemodialysis) {
            return view('treatment.formPostH', compact('transHemodialysis'));
        }
        return view('treatment.formPostH', compact('id'));
    }
    public function createEvaluation(Request $request,$id)
    {
        $evaluationRisk = EvaluationRisk::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($evaluationRisk) {
            return view('treatment.formEvaluation', compact('evaluationRisk'));
        }
        return view('treatment.formEvaluation', compact('id'));
    }
    public function fill(Request $request)
    {
        $validator = $request->validate([
            'date_hour' => 'required|date',
            'vascular_access' => 'required|in:fistula,catheter',
            'catheter_type' => 'in:tunneling,no_tunneling',
            'implantation' => 'required|in:femoral,yugular,subclavia,brazo,antebrazo',
            'needle_mesure' => 'integer',
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
            'time.*' => 'required|date_format:H:i',
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
            ['patient_id' => $request->input('patient_id'), 'time' => $value],
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
            'fall_risk' => 'required|numeric',
            'fall_risk_date' => 'required|date',
            'fall_risk_time' => 'required|date_format:H:i',
            'fall_risk_nurse' => 'required|string',
            'fall_risk_nurse_time' => 'required|date_format:H:i',
            'fall_risk_nurse_date' => 'required|date',
            'fall_risk_nurse_observations' => 'required|string',
            'fall_risk_nurse_intervention' => 'required|string',
            'fall_risk_nurse_intervention_time' => 'required|date_format:H:i',
            'fall_risk_nurse_intervention_date' => 'required|date',
            'fall_risk_nurse_intervention_observations' => 'required|string',
            'fall_risk_nurse_intervention_result' => 'required|string',
            'fall_risk_nurse_intervention_result_time' => 'required|date_format:H:i',
            'fall_risk_nurse_intervention_result_date' => 'required|date',
            'fall_risk_nurse_intervention_result_observations' => 'required|string',
            'fall_risk_nurse_intervention_result_intervention' => 'required|string',
        ]);
    }
}
