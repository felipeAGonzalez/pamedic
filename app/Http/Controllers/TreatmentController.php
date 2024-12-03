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
        $patient = Patient::where('id',$id)->first();
        $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $patient->id, 'history' =>  0])->first();
        if ($dialysisMonitoring) {
            return view('treatment.form', compact('dialysisMonitoring','patient'));
        }else{
            $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $patient->id, 'history' =>  1])->orderBy('date_hour','DESC')->first();
            if ($dialysisMonitoring) {
                $dialysisMonitoring = $dialysisMonitoring->replicate();
                $dialysisMonitoring->date_hour = date('Y-m-d H:i');
                $dialysisMonitoring->machine_number = "";
                $dialysisMonitoring->session_number = $dialysisMonitoring->session_number + 1;
                $dialysisMonitoring->history = 0;
                $dialysisMonitoring->save();
                return view('treatment.form', compact('dialysisMonitoring','patient'));
            }
            return view('treatment.form', compact('patient','id'));
        }
    }
    public function createPres(Request $request,$id)
    {
        $patient = Patient::where('id',$id)->first();
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($dialysisPrescription) {
            return view('treatment.formPres', compact('dialysisPrescription','patient'));
        }
        return view('treatment.formPres', compact('id','patient'));
    }
    public function createPreHemo(Request $request,$id)
    {
        $patient = Patient::where('id',$id)->first();
        $noReuse = 0;
        $preHemodialysis = PreHemodialysis::where(['patient_id' => $id, 'history' =>  0])->first();
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id, 'history' =>  0])->first();
        if (!$dialysisPrescription) {
            return redirect()->route('treatment.index')->with('error', 'Primero debe llenar la prescripción de diálisis');
        }
        if ($dialysisPrescription->type_dialyzer == 'F6ELISIO21H' || $dialysisPrescription->type_dialyzer == 'F6ELISIO19H') {
            $noReuse = 1;
        }
        if ($preHemodialysis) {
            return view('treatment.formPreH', compact('preHemodialysis','noReuse','patient'));
        }else{
            $preHemodialysis = PreHemodialysis::where(['patient_id' => $id, 'history' =>  1])->orderBy('id','DESC')->first();
            $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id, 'history' =>  1])->orderBy('created_at','DESC')->first();
            if ($preHemodialysis) {
                $newPreHemodialysis = $preHemodialysis->replicate();
                $newPreHemodialysis->previous_initial_weight = $preHemodialysis->initial_weight;
                $newPreHemodialysis->previous_final_weight = $postHemoDialysis->weight_out;
                $newPreHemodialysis->previous_weight_gain = (double) $preHemodialysis->initial_weight - (double) $postHemoDialysis->weight_out;
                //    $weight_gain = initial_weight - dry_weight
                $newPreHemodialysis->history = 0;
                $newPreHemodialysis->save();
                $preHemodialysis = $newPreHemodialysis;
                return view('treatment.formPreH', compact('preHemodialysis','noReuse','patient'));
            }
        }
        return view('treatment.formPreH', compact('id','noReuse','patient'));
    }
    public function createTransHemo(Request $request,$id)
    {
        $patient = Patient::where('id',$id)->first();
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if (!$dialysisPrescription) {
            return redirect()->route('treatment.index')->with('error', 'Primero debe llenar la prescripción de diálisis');
        }
        $scheduleUltrafilter = floor($dialysisPrescription->schedule_ultrafilter / 12);
        $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $id, 'history' =>  0])->orderBy('date_hour','DESC')->first();
        if (!$dialysisMonitoring) {
            return redirect()->route('treatment.index')->with('error', 'Primero debe llenar el monitoreo de diálisis');
        }
        $hour = date('H:i', strtotime($dialysisMonitoring->date_hour));
        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('time','ASC')->get();
        if (($transHemodialysis->count() > 0)) {
                return view('treatment.formTransH', compact('transHemodialysis','patient'));
            }
        for ($i=0; $i <13 ; $i++) {
                TransHemodialysis::create([
                    'patient_id' => $id,
                    'time' => date('H:i', strtotime($hour) + ($i * 15 * 60)),
                    'arterial_pressure' => 0,
                    'mean_pressure' => 0,
                    'heart_rate' => 0,
                    'respiratory_rate' => 0,
                    'temperature' => 35.5,
                    'arterial_pressure_monitor' => 0,
                    'venous_pressure_monitor' => 0,
                    'transmembrane_pressure_monitor' => 0,
                    'blood_flow' => 0,
                    'ultrafiltration' => $i * $scheduleUltrafilter,
                    'heparin' => 0,
                    'observations' => '',
                ]);
            }
            $transHemodialysis = TransHemodialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('time','ASC')->get();
            return view('treatment.formTransH', compact('transHemodialysis','patient'));
        }

    public function createPostHemo(Request $request,$id)
    {
        $patient = Patient::where('id',$id)->first();
        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($postHemoDialysis) {
            return view('treatment.formPostH', compact('postHemoDialysis','patient'));
        }
        return view('treatment.formPostH', compact('id','patient'));
    }
    public function createEvaluation(Request $request,$id)
    {
        $patient = Patient::where('id',$id)->first();
        $fase = [0 => 'Pre-Hemodiálisis',
                 1 => 'Trans-Hemodiálisis',
                 2 => 'Post-Hemodiálisis'
                ];

        $evaluationRisk = EvaluationRisk::where(['patient_id' => $id, 'history' =>  0])->orderBy('hour','ASC')->get();
        if (($evaluationRisk->count() > 0)) {

            return view('treatment.formEvaluation', compact('evaluationRisk','patient'));
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
            return view('treatment.formEvaluation', compact('evaluationRisk','patient'));
    }
    public function createEvaluationNurse(Request $request,$id)
    {
       $patient = Patient::where('id',$id)->first();
       $fase = [ 0 => 'Pre-Hemodiálisis',
                 1 => 'Trans-Hemodiálisis',
                 2 => 'Post-Hemodiálisis'
                ];

        $nurseValo = NurseEvaluation::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','ASC')->get();
        if (($nurseValo->count() > 0)) {
            return view('treatment.formNurseValo', compact('nurseValo','patient'));
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
            return view('treatment.formNurseValo', compact('nurseValo','patient'));
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
            'catheter_type' => 'nullable|in:tunneling,no_tunneling',
            'implantation' => 'required|in:femoral,yugular,subclavia,brazo,antebrazo',
            'needle_mesure' => 'nullable|integer',
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
        $dialysisMonitoring = DialysisMonitoring::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'history' => 0],
            [
                'date_hour' => $request->input('date_hour'),
                'machine_number' => $request->input('machine_number'),
                'session_number' => $request->input('session_number'),
                'vascular_access' => $request->input('vascular_access'),
                'catheter_type' => $request->input('catheter_type'),
                'implantation' => $request->input('implantation'),
                'needle_mesure' => $request->input('needle_mesure'),
                'side' => $request->input('side'),
                'collocation_date' => $request->input('collocation_date'),
                'serology' => $request->input('serology'),
                'serology_date' => $request->input('serology_date'),
                'blood_type' => $request->input('blood_type'),
                'allergy' => $request->input('allergy'),
                'diagnostic' => $request->input('diagnostic'),
            ]
        );
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
            'profile_ultrafilter' => 'nullable|string',
            'sodium_profile' => 'nullable|string',
            'machine_temperature' => 'required|string',
        ]);
        $dialysisPrescription = DialysisPrescription::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'history' => 0],
            [
            'type_dialyzer' => $request->input('type_dialyzer'),
            'time' => $request->input('time'),
            'blood_flux' => $request->input('blood_flux'),
            'flux_dialyzer' => $request->input('flux_dialyzer'),
            'heparin' => $request->input('heparin'),
            'schedule_ultrafilter' => $request->input('schedule_ultrafilter'),
            'profile_ultrafilter' => $request->input('profile_ultrafilter'),
            'sodium_profile' => $request->input('sodium_profile'),
            'machine_temperature' => $request->input('machine_temperature'),
            ]
        );

        return redirect()->route('treatment.index')->with('success', 'Prescripción de diálisis guardada exitosamente');
    }
    public function fillPreHemo(Request $request){
        $validator = $request->validate([
            'previous_initial_weight' => 'numeric',
            'previous_final_weight' => 'numeric',
            'previous_weight_gain' => 'numeric',
            'initial_weight' => 'required|numeric',
            'dry_weight' => 'numeric',
            'weight_gain' => 'numeric',
            'reuse_number' => 'required|numeric',
            'sitting_blood_pressure' => 'required|string',
            'standing_blood_pressure' => 'required|string',
            'body_temperature' => 'required|numeric',
            'heart_rate' => 'required|numeric',
            'respiratory_rate' => 'required|numeric',
            'oxygen_saturation' => 'required|numeric',
            'conductivity' => 'required|numeric',
            'destrostix' => '|numeric',
            'pallor_skin' => 'required|in:low,medium,high',
            'itchiness' => 'required|in:low,medium,high',
            'edema' => 'required|in:low,medium,high',
            'vascular_access_conditions' => 'required|string',
            'fall_risk' => 'required|in:low,medium,high',
            'observations' => 'required|string',
        ]);
        $preHemodialysis = PreHemodialysis::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'history' => 0],
            [
            'previous_initial_weight' => $request->input('previous_initial_weight', 0),
            'previous_final_weight' => $request->input('previous_final_weight',0),
            'previous_weight_gain' => $request->input('previous_weight_gain',0),
            'initial_weight' => $request->input('initial_weight'),
            'dry_weight' => $request->input('dry_weight',0),
            'weight_gain' => $request->input('weight_gain',0),
            'reuse_number' => $request->input('reuse_number'),
            'sitting_blood_pressure' => $request->input('sitting_blood_pressure'),
            'standing_blood_pressure' => $request->input('standing_blood_pressure'),
            'body_temperature' => $request->input('body_temperature'),
            'heart_rate' => $request->input('heart_rate'),
            'respiratory_rate' => $request->input('respiratory_rate'),
            'oxygen_saturation' => $request->input('oxygen_saturation'),
            'conductivity' => $request->input('conductivity'),
            'dextrostix' => $request->input('dextrostix',0),
            'itchiness' => $request->input('itchiness'),
            'pallor_skin' => $request->input('pallor_skin'),
            'edema' => $request->input('edema'),
            'vascular_access_conditions' => $request->input('vascular_access_conditions'),
            'fall_risk' => $request->input('fall_risk'),
            'observations' => $request->input('observations'),
            ]
        );
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
            'blood_pressure_stand' => 'required|string',
            'blood_pressure_sit' => 'required|string',
            'respiratory_rate' => 'required|numeric',
            'heart_rate' => 'required|numeric',
            'weight_out' => 'required|numeric',
            'fall_risk' => 'required|string',
        ]);

        $postHemoDialysis = PostHemoDialysis::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'history' => 0],
            [
            'final_ultrafiltration' => $request->input('final_ultrafiltration'),
            'treated_blood' => $request->input('treated_blood'),
            'ktv' => $request->input('ktv'),
            'patient_temperature' => $request->input('patient_temperature'),
            'blood_pressure_stand' => $request->input('blood_pressure_stand'),
            'blood_pressure_sit' => $request->input('blood_pressure_sit'),
            'respiratory_rate' => $request->input('respiratory_rate'),
            'heart_rate' => $request->input('heart_rate'),
            'weight_out' => $request->input('weight_out'),
            'fall_risk' => $request->input('fall_risk'),
            ]
        );
        return redirect()->route('treatment.index')->with('success', 'Datos de guardados exitosamente');
    }
    public function fillEvaluation(Request $request){
        $validator = $request->validate([
            'fase.*' => 'required|string',
            'hour.*' => 'required|date_format:H:i',
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
            'due_date' => 'required|date_format:Y-m',
        ]);
        $medicineAdministration = MedicationAdministration::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'medicine_id' => $request->input('medicine_id'), 'hour' => $request->input('hour')],
            [
            'nurse_prepare_id' => $request->input('nurse_prepare_id'),
            'nurse_admin_id' => $request->input('nurse_admin_id'),
            'dilution' => $request->input('dilution'),
            'velocity' => $request->input('velocity'),
            'due_date' => $request->input('due_date') . '-01',
            ]
        );
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
        $medicineAdministration = MedicationAdministration::findOrFail($id);
        $medicineAdministration->delete();
        $patient = Patient::where('id',$medicineAdministration->patient_id)->first();
        $users = User::where('position','NURSE')->get();
        $medicines = Medicine::all();
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
