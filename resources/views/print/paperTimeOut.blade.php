<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{'Validación-'.substr($patient->expedient_number, -4)}}</title>
</head>

<body>
    <table style="width: 100%;  border-collapse: collapse;">
        <tr>
            <td style="width: 50px;">
                <img src="{{public_path('logos/pamedic.png')}}" width="70" style="margin-right: 10px;">
            </td>
            <td colspan=5>
                <h3 style="margin: 0; text-align: center;  padding: 10px;"><strong><pre>       Corporación Pamedic S.A de C.V Unidad de Hemodialisis        </pre></strong></h3>
            </td>
        </tr>
    </table>
    <table  border="1" style="width: 100%; font-size: 11px;">
    <thead>
        <tr class="text-center">
            <th colspan="6" style="background-color: #8db4e3;">DATOS PERSONALES DEL PACIENTE</th>
            <th colspan="9" style="background-color: #8db4e3;">PRESCRIPCIÓN</th>
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
            <td colspan="6" class="text-start">DATOS DEL ACCESO VASCULAR: <strong>{{__('web.'.$dialysisMonitoring['vascular_access'])}}</strong></td>
            <td colspan="3"> <strong>{{$dialysisPrescription->time}}</strong></td>
            <td colspan="3"> <strong>{{$dialysisPrescription->type_dialyzer}}</strong></td>
            <td colspan="3"> <strong>{{__('web.'.$dialysisPrescription->heparin)}}</strong></td>
        </tr>
    </tbody>
</table>
<br>
<table class="table" border="1" style="width: 100%; font-size: 10px;">
    <thead>
        <tr>
            <th colspan = 14  style="background-color: #8db4e3; width: 100%;">VERIFICACIÓN PRE-PROCEDIMIENTO</th>
        </tr>
        <tr>
            <th>Fecha de Sesión de Hemodiálisis</th>
            @foreach($verification as $item)
            <th>{{ \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y') }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>VERIFICACIÓN DE NOMBRE PACIENTE</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['patient_name'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE FECHA NACIMIENTO</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['date_of_birth'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE PROCEDIMIENTO PROGRAMADO</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['scheduled_procedure'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE IDENTIFICACIÓN PACIENTE</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['patient_id_badge'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE HOJA DE ENFERMERÍA IDENTIFICADA</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['nurse_sheet_identified'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE SEROLOGÍA HEP B/C (4 MESES)</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['hep_b_c_serology_m_4_months'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE SEROLOGÍA (6 MESES)</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['serology_m_6_months'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE PRUEBA MÁQUINA HD PASADA</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['hd_machine_test_passed'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE KIT POR ACCESO VASCULAR</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['kit_per_vascular_access'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE ALERGIAS</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['allergies'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE DIALIZADOR SEGÚN PRESCRIPCIÓN</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['dialyzer_per_prescription'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE ETIQUETA DIALIZADOR REPROCESADO</td>
            @foreach($verification as $item)
           <td style="text-align: center;">{{ $item['reprocessed_dialyzer_label'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE ACCESO VASCULAR</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['vascular_access'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <br>
        <tr>
            <th colspan = 14 style="background-color: #8db4e3; width: 100%;">TIME OUT/TIEMPO FUERA</th>
        </tr>
        <tr>
            <td>IDENTIFICACIÓN DEL PACIENTE</td>
            @foreach($timeOut as $item)
            <td style="text-align: center;">{{ $item['patient_identification'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>PROCEDIMIENTO PROGRAMADO</td>
            @foreach($timeOut as $item)
            <td style="text-align: center;">{{ $item['scheduled_procedure'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>PRESCRIPCIÓN DIALÍTICA</td>
            @foreach($timeOut as $item)
            <td style="text-align: center;">{{ $item['dialysis_prescription'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DEL DIALIZADOR</td>
            @foreach($timeOut as $item)
            <td style="text-align: center;">{{ $item['dialyzer_check'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN DE SANGRADO</td>
            @foreach($timeOut as $item)
            <td style="text-align: center;">{{ $item['bleeding_check'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <td>VERIFICACIÓN ACCESO VASCULAR</td>
            @foreach($timeOut as $item)
            <td style="text-align: center;">{{ $item['vascular_access_check'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
    </tbody>
</table>

</body>
</html>
