@extends('layouts.app')

@section('content')
<h1>Verificación</h1>

<table class="table table-bordered table-sm align-middle">
    <thead>
        <tr class="text-center">
            <th colspan="6">DATOS PERSONALES DEL PACIENTE</th>
            <th colspan="9">PRESCRIPCIÓN</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="6" class="text-start">NOMBRE DEL PACIENTE:  <strong>{{$patient->name.' '.$patient->last_name.' '.$patient->last_name_two}}</strong></td>
            <td colspan="3">UF (ml)</td>
            <td colspan="3">FLUJO DE BOMBA</td>
            <td colspan="3">FLUJO DE DIÁLISIS</td>
        </tr>
        <tr>
            <td colspan="6" class="text-start">FECHA DE NACIMIENTO:  <strong>{{$patient->birth_date->format('d/m/Y')}}</strong></td>
            <td colspan="3"><strong>{{$dialysisPrescription->schedule_ultrafilter}}</strong></td>
            <td colspan="3"><strong>{{$dialysisPrescription->blood_flux}}</strong></td>
            <td colspan="3"><strong>{{$dialysisPrescription->flux_dialyzer}}</strong></td>
        </tr>
        <tr>
            <td colspan="6" class="text-start">NÚMERO DE EXPEDIENTE: <strong>{{$patient->expedient_number}}</strong></td>
            <td colspan="3">138 mEq/L NA</td>
            <td colspan="3">2.5 mEq/L Ca</td>
            <td colspan="3">2 mEq/L K</td>
        </tr>
        <tr>
            <td colspan="6" class="text-start">TIPO SANGUÍNEO: <strong>{{$dialysisMonitoring->blood_type}}</strong></td>
            <td class="text-center" colspan="3">--</td>
            <td class="text-center" colspan="3">--</td>
            <td class="text-center" colspan="3">--</td>
        </tr>
        <tr>
            <td colspan="6" class="text-start">FECHA DE ÚLTIMOS RESULTADOS DE LABORATORIO: <strong>{{$dialysisMonitoring->serology_date}}</strong></td>
             <td colspan="3">TIEMPO</td>
            <td colspan="3">DIALIZADOR</td>
            <td colspan="3">HEPARINA</td>
        </tr>
        <tr>
            <td colspan="6" class="text-start">DATOS DEL ACCESO VASCULAR: <strong>{{$dialysisMonitoring->vascular_access}}</strong></td>
          <td colspan="3"> <strong>{{$dialysisPrescription->time}}</strong></td>
            <td colspan="3"> <strong>{{$dialysisPrescription->type_dialyzer}}</strong></td>
            <td colspan="3"> <strong>{{__('web.'.$dialysisPrescription->heparin)}}</strong></td>
        </tr>
    </tbody>
</table>



<form action="{{ route('treatment.fillTimeOut') }}" method="POST" class="row">
    @csrf
    <input type="hidden" name="patient_id" value="{{ $id ?? $timeOut->patient_id }}">
    <div class="col-md-6">
        <div class="table-responsive">
    <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Verificación pre - Procedimiento</th>
                    <th>¿Completado?</th>
                </tr>
            </thead>
        <tbody>
            <tr>
                <td>Verificación de nombre del paciente</td>
                <td><input type="checkbox" id="patient_name" name="patient_name" value="1" {{ isset($verification['patient_name']) && $verification['patient_name'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de fecha de nacimiento</td>
                <td><input type="checkbox" id="date_of_birth" name="date_of_birth" value="1" {{ isset($verification['date_of_birth']) && $verification['date_of_birth'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de procedimiento programado</td>
                <td><input type="checkbox" id="scheduled_procedure_2" name="scheduled_procedure_2" value="1" {{ isset($verification['scheduled_procedure']) && $verification['scheduled_procedure'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de gafete del paciente</td>
                <td><input type="checkbox" id="patient_id_badge" name="patient_id_badge" value="1" {{ isset($verification['patient_id_badge']) && $verification['patient_id_badge'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de hoja de enfermería identificada</td>
                <td><input type="checkbox" id="nurse_sheet_identified" name="nurse_sheet_identified" value="1" {{ isset($verification['nurse_sheet_identified']) && $verification['nurse_sheet_identified'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de serología hepatitis B y C menor a 4 meses</td>
                <td><input type="checkbox" id="hep_b_c_serology_m_4_months" name="hep_b_c_serology_m_4_months" value="1" {{ isset($verification['hep_b_c_serology_m_4_months']) && $verification['hep_b_c_serology_m_4_months'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de serología menor a 6 meses</td>
                <td><input type="checkbox" id="serology_m_6_months" name="serology_m_6_months" value="1" {{ isset($verification['serology_m_6_months']) && $verification['serology_m_6_months'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de máquina de hemodiálisis (pasó test)</td>
                <td><input type="checkbox" id="hd_machine_test_passed" name="hd_machine_test_passed" value="1" {{ isset($verification['hd_machine_test_passed']) && $verification['hd_machine_test_passed'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de kit de acuerdo al acceso vascular</td>
                <td><input type="checkbox" id="kit_per_vascular_access" name="kit_per_vascular_access" value="1" {{ isset($verification['kit_per_vascular_access']) && $verification['kit_per_vascular_access'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de alergias</td>
                <td><input type="checkbox" id="allergies" name="allergies" value="1" {{ isset($verification['allergies']) && $verification['allergies'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de dializador de acuerdo a la prescripción</td>
                <td><input type="checkbox" id="dialyzer_per_prescription" name="dialyzer_per_prescription" value="1" {{ isset($verification['dialyzer_per_prescription']) && $verification['dialyzer_per_prescription'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de etiqueta del dializador reprocesado</td>
                <td><input type="checkbox" id="reprocessed_dialyzer_label" name="reprocessed_dialyzer_label" value="1" {{ isset($verification['reprocessed_dialyzer_label']) && $verification['reprocessed_dialyzer_label'] == 1 ? 'checked' : '' }}></td>
            </tr>
            <tr>
                <td>Verificación de acceso vascular</td>
                <td><input type="checkbox" id="vascular_access" name="vascular_access" value="1" {{ isset($verification['vascular_access']) && $verification['vascular_access'] == 1 ? 'checked' : '' }}></td>
            </tr>
        </tbody>
    </table>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('treatment.index') }}" class="btn btn-info">Volver</a>
        </div>
    </div>

    <div class="col-md-6">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Time Out/Tiempo Fuera</th>
                        <th>¿Completado?</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Identificación del paciente</td>
                        <td><input type="checkbox" id="patient_id_check" name="patient_id_check" value="1" {{ isset($timeOut['patient_identification']) && $timeOut['patient_identification'] == 1 ? 'checked' : '' }}></td>
                    </tr>
                    <tr>
                        <td>Procedimiento programado</td>
                        <td><input type="checkbox" id="scheduled_procedure" name="scheduled_procedure" value="1" {{ isset($timeOut['scheduled_procedure']) && $timeOut['scheduled_procedure'] == 1 ? 'checked' : '' }}></td>
                    </tr>
                    <tr>
                        <td>Prescripción dialítica</td>
                        <td><input type="checkbox" id="dialysis_prescription" name="dialysis_prescription" value="1" {{ isset($timeOut['dialysis_prescription']) && $timeOut['dialysis_prescription'] == 1 ? 'checked' : '' }}></td>
                    </tr>
                    <tr>
                        <td>Verificación del dializador</td>
                        <td><input type="checkbox" id="dialyzer_check" name="dialyzer_check" value="1" {{ isset($timeOut['dialyzer_check']) && $timeOut['dialyzer_check'] == 1 ? 'checked' : '' }}></td>
                    </tr>
                    <tr>
                        <td>Verificación de sangrado</td>
                        <td><input type="checkbox" id="bleeding_check" name="bleeding_check" value="1" {{ isset($timeOut['bleeding_check']) && $timeOut['bleeding_check'] == 1 ? 'checked' : '' }}></td>
                    </tr>
                    <tr>
                        <td>Verificación acceso vascular</td>
                        <td><input type="checkbox" id="vascular_access_check" name="vascular_access_check" value="1" {{ isset($timeOut['vascular_access_check']) && $timeOut['vascular_access_check'] == 1 ? 'checked' : '' }}></td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>


</form>


        @if ($errors->any())
                <div class="alert2 alert2-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ __($error) }}<br></li>
                        @endforeach
                        </ul>
                    </div>
            @endif
        </form>
@endsection
