<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NursePatient;
use App\Models\Patient;
use App\Models\User;
use App\Models\Medicine;
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


class EditController extends Controller
{
    public function index()
    {
        $nursePatients = NursePatient::where(['date' => date('Y-m-d'),'history'=>1])->get();
        $activePatients = $nursePatients->map(function ($nursePatients) {
            return $nursePatients->active_patient;
        });
        return view('edit.index', compact('activePatients'));
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
        return view('edit.index', compact('activePatients'));
    }
    public function create(Request $request,$id,$date)
    {
        $patient = Patient::where('id',$id)->first();
        $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $patient->id, 'history' =>  1])->whereDate('created_at', $date)->first();
        return view('edit.form', compact('dialysisMonitoring','patient'));

    }
    public function createPres(Request $request,$id,$date)
    {
        $patient = Patient::where('id',$id)->first();
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id, 'history' =>  1])->whereDate('created_at', $date)->first();
        return view('edit.formPres', compact('dialysisPrescription','patient'));
    }
    public function createPreHemo(Request $request,$id,$date)
    {
        $patient = Patient::where('id',$id)->first();
        $noReuse = 0;
        $preHemodialysis = PreHemodialysis::where(['patient_id' => $id, 'history' =>  1])->whereDate('created_at', $date)->first();
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id, 'history' =>  1])->whereDate('created_at', $date)->first();

        if ($dialysisPrescription->type_dialyzer == 'F6ELISIO21H' || $dialysisPrescription->type_dialyzer == 'F6ELISIO19H') {
            $noReuse = 1;
        }
            return view('edit.formPreH', compact('preHemodialysis','noReuse','patient'));
    }
    public function createTransHemo(Request $request,$id,$date)
    {
        $patient = Patient::where('id',$id)->first();
        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id, 'history' =>  1])->whereDate('created_at', $date)->orderBy('time','ASC')->get();
        return view('edit.formTransH', compact('transHemodialysis','patient'));

        }

    public function createPostHemo(Request $request,$id,$date)
    {
        $patient = Patient::where('id',$id)->first();
        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id, 'history' =>  1])->whereDate('created_at', $date)->orderBy('id','DESC')->first();
        return view('edit.formPostH', compact('postHemoDialysis','patient'));

    }
    public function createEvaluation(Request $request,$id,$date)
    {
        $patient = Patient::where('id',$id)->first();

        $evaluationRisk = EvaluationRisk::where(['patient_id' => $id, 'history' =>  1])->whereDate('created_at', $date)->orderBy('hour','ASC')->get();
            return view('edit.formEvaluation', compact('evaluationRisk','patient'));
    }
    public function createEvaluationNurse(Request $request,$id,$date)
    {
        $patient = Patient::where('id',$id)->first();
        $nurseValo = NurseEvaluation::where(['patient_id' => $id, 'history' =>  1])->whereDate('created_at', $date)->orderBy('id','ASC')->get();
        return view('edit.formNurseValo', compact('nurseValo','patient'));
    }
    public function createOxygenTherapy(Request $request,$id)
    {
        $patient = Patient::where('id',$id)->first();
        $oxygenTherapy = OxygenTherapy::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($oxygenTherapy) {
            return view('treatment.formOx', compact('oxygenTherapy','patient'));
        }
        return view('treatment.formOx', compact('id','patient'));
    }
    public function createMedicineAdmin(Request $request,$id,$date)
    {
        $medicineAdministration = MedicationAdministration::where(['patient_id' => $id, 'history' =>  1])
            ->whereDate('created_at', $date)
            ->orderBy('created_at','DESC')
            ->get();
        $patient = Patient::where('id',$id)->first();
        $users = User::where('position','NURSE')->get();
        $medicines = Medicine::all();
            return view('edit.formMedicineA', compact('id','medicineAdministration','users','medicines','patient'))->with('success', 'Medicamento asignado exitosamente');
    }
    public function fill(Request $request)
    {
        $validator = $request->validate([
            'date_hour' => 'nullable|date',
            'vascular_access' => 'nullable|in:fistula,catheter',
            'catheter_type' => 'nullable|in:tunneling,no_tunneling',
            'implantation' => 'nullable|in:femoral,yugular,subclavia,brazo,antebrazo',
            'needle_mesure' => 'nullable|integer',
            'machine_number' => 'nullable|integer',
            'session_number' => 'nullable|integer',
            'side' => 'nullable|in:right,left',
            'collocation_date' => 'nullable|date',
            'serology' => 'nullable|in:positivo,negativo',
            'serology_date' => 'nullable|date',
            'blood_type' => 'nullable|string',
            'allergy' => 'nullable|string',
            'diagnostic' => 'nullable|string',
        ]);
        $dialysisMonitoring = DialysisMonitoring::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'history' => 1],
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
        return redirect()->route('edit.index')->with('success', 'Monitoreo de diálisis guardado exitosamente');

    }
    public function fillPres(Request $request)
    {

       $validator = $request->validate([
            'type_dialyzer' => 'nullable|in:HF80S,F6ELISIO21H,F6ELISIO19H',
            'time' => 'nullable|numeric|min:10|max:300',
            'blood_flux' => 'nullable|string',
            'flux_dialyzer' => 'nullable|string',
            'heparin' => 'nullable|string',
            'schedule_ultrafilter' => 'nullable|string',
            'profile_ultrafilter' => 'nullable|string',
            'sodium_profile' => 'nullable|string',
            'machine_temperature' => 'nullable|string',
        ]);
        $dialysisPrescription = DialysisPrescription::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'history' => 1],
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

        return redirect()->route('edit.index')->with('success', 'Prescripción de diálisis guardada exitosamente');
    }
    public function fillPreHemo(Request $request){
        $validator = $request->validate([
            'previous_initial_weight' => 'numeric',
            'previous_final_weight' => 'numeric',
            'previous_weight_gain' => 'numeric',
            'initial_weight' => 'nullable|numeric',
            'dry_weight' => 'numeric',
            'weight_gain' => 'numeric',
            'reuse_number' => 'nullable|numeric',
            'sitting_blood_pressure' => 'nullable|string',
            'standing_blood_pressure' => 'nullable|string',
            'body_temperature' => 'nullable|numeric',
            'heart_rate' => 'nullable|numeric',
            'respiratory_rate' => 'nullable|numeric',
            'oxygen_saturation' => 'nullable|numeric',
            'conductivity' => 'nullable|numeric',
            'destrostix' => '|numeric',
            'pallor_skin' => 'nullable|in:low,medium,high',
            'itchiness' => 'nullable|in:low,medium,high',
            'edema' => 'nullable|in:low,medium,high',
            'vascular_access_conditions' => 'nullable|string',
            'fall_risk' => 'nullable|in:low,medium,high',
            'observations' => 'nullable|string',
        ]);
        $preHemodialysis = PreHemodialysis::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'history' => 1],
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
        return redirect()->route('edit.index')->with('success', 'Datos de la pre-diálisis guardada exitosamente');

    }
    public function fillTransHemo(Request $request){

        $validator = $request->validate([
            'time.*' => 'nullable|date_format:H:i:s',
            'arterial_pressure.*' => 'nullable|string',
            'mean_pressure.*' => 'nullable|numeric',
            'heart_rate.*' => 'nullable|numeric',
            'respiratory_rate.*' => 'nullable|numeric',
            'temperature.*' => 'nullable|numeric',
            'arterial_pressure_monitor.*' => 'nullable|string',
            'venous_pressure_monitor.*' => 'nullable|numeric',
            'transmembrane_pressure_monitor.*' => 'nullable|numeric',
            'blood_flow.*' => 'nullable|numeric',
            'ultrafiltration.*' => 'nullable|numeric',
            'heparin.*' => 'nullable|numeric',
            'observations.*' => 'nullable|string',
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
        return redirect()->route('edit.index')->with('success', 'Datos de guardados exitosamente');
    }
    public function fillPostHemo(Request $request){
        $validator = $request->validate([
            'final_ultrafiltration' => 'nullable|numeric',
            'treated_blood' => 'nullable|numeric',
            'ktv' => 'nullable|numeric',
            'patient_temperature' => 'nullable|numeric',
            'blood_pressure_stand' => 'nullable|string',
            'blood_pressure_sit' => 'nullable|string',
            'respiratory_rate' => 'nullable|numeric',
            'heart_rate' => 'nullable|numeric',
            'weight_out' => 'nullable|numeric',
            'fall_risk' => 'nullable|string',
        ]);

        $postHemoDialysis = PostHemoDialysis::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'history' => 1],
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
        return redirect()->route('edit.index')->with('success', 'Datos de guardados exitosamente');
    }
    public function fillEvaluation(Request $request){
        $validator = $request->validate([
            'fase.*' => 'nullable|string',
            'hour.*' => 'nullable|date_format:H:i',
            'result.*' => 'nullable|numeric',
            // 'fall_risk_trans.*' => 'nullable|string',
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
        return redirect()->route('edit.index')->with('success', 'Datos de guardados exitosamente');
    }

    public function fillNurseEvaluation(Request $request){
        $validator = $request->validate([
            'fase.*' => 'nullable|string',
            'nurse_valuation.*' => 'nullable|string',
            'nurse_intervention.*' => 'nullable|string',
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
        return redirect()->route('edit.index')->with('success', 'Datos de guardados exitosamente');
    }
    public function fillOxygenTherapy(Request $request){
        $validator = $request->validate([
            'patient_id' => 'required|integer',
            'initial_oxygen_saturation' => 'nullable|numeric',
            'final_oxygen_saturation' => 'nullable|numeric',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'oxygen_flow' => 'nullable|numeric',
        ]);

        $oxygenTherapy = OxygenTherapy::updateOrCreate(
            ['patient_id' => $request->input('patient_id'), 'history' => 0],
            [
                'initial_oxygen_saturation' => $request->input('initial_oxygen_saturation'),
                'final_oxygen_saturation' => $request->input('final_oxygen_saturation'),
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'oxygen_flow' => $request->input('oxygen_flow'),
            ]
        );

        return redirect()->route('treatment.index')->with('success', 'Terapia de oxígeno guardada exitosamente');
    }
    public function fillMedicineAdmin(Request $request)
    {
        $validator = $request->validate([
            'nurse_prepare_id' => 'nullable|integer',
            'nurse_admin_id' => 'nullable|integer',
            'medicine_id' => 'nullable|integer',
            'dilution' => 'nullable|string',
            'velocity' => 'nullable|string',
            'hour' => 'nullable|date_format:H:i',
            'due_date' => 'nullable|date_format:Y-m',
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
        return view('edit.formMedicineA', compact('medicineAdministration','users','medicines','patient'))->with('success', 'Medicamento asignado exitosamente');
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
        return view('edit.formMedicineA', compact('medicineAdministration','users','medicines','patient'))->with('success', 'Medicamento eliminado exitosamente');
    }
     public function destroyTreatment(Request $request,$id)
    {
        $dialysisMonitoring = DialysisMonitoring::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($dialysisMonitoring) {
            $dialysisMonitoring->delete();
        }
        $dialysisPrescription = DialysisPrescription::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($dialysisPrescription) {
            $dialysisPrescription->delete();
        }

        $preHemodialysis = PreHemodialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($preHemodialysis) {
            $preHemodialysis->delete();
        }

        $transHemodialysis = TransHemodialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('time','ASC')->get();
        foreach ($transHemodialysis as $trans) {
            $trans->delete();
        }

        $postHemoDialysis = PostHemoDialysis::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','DESC')->first();
        if ($postHemoDialysis) {
            $postHemoDialysis->delete();
        }
        $evaluationRisk = EvaluationRisk::where(['patient_id' => $id, 'history' =>  0])->orderBy('hour','ASC')->get();
        foreach ($evaluationRisk as $eval) {
            $eval->delete();
        }

        $nurseValo = NurseEvaluation::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','ASC')->get();
        foreach ($nurseValo as $nurse) {
            $nurse->delete();
        }

        $medicineAdministration = MedicationAdministration::where(['patient_id' => $id, 'history' =>  0])->orderBy('id','ASC')->get();
        foreach ($medicineAdministration as $medicine) {
            $medicine->delete();
        }

        $activePatient = ActivePatient::where(['patient_id' => $id, 'active' => 0])->orderBy('date','DESC')->first();
        if ($activePatient) {
            $activePatient->delete();
            $nursePatients = NursePatient::where(['active_patient_id' => $activePatient->id,'history' => 0])->first();
            if ($nursePatients) {
                $nursePatients->delete();
            }
        }

        return redirect()->route('treatment.index')->with('success', 'Tratamiento eliminado exitosamente');
    }
}
