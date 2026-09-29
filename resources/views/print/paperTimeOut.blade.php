<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{'Validación-'.substr($patient->expedient_number, -4)}}</title>
    <style>
        @page {
            margin: 9mm 10mm;
        }

        body {
            margin: 0;
            color: #000;
            font-family: DejaVu Sans, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            margin-bottom: 6px;
        }

        .header-logo {
            width: 75px;
            vertical-align: middle;
        }

        .header-logo img {
            width: 55px;
        }

        .header-content {
            text-align: center;
            vertical-align: middle;
        }

        .document-title {
            margin: 0 0 8px;
            font-size: 15px;
        }

        .document-subtitle {
            margin: 0;
            font-size: 12px;
        }

        .header-code {
            width: 145px;
            padding-left: 8px;
            vertical-align: top;
        }

        .code-box {
            font-size: 8px;
            text-align: center;
        }

        .code-box th {
            padding: 2px 4px;
            border: 1px solid #666;
            background-color: #8db4e3;
        }

        .code-box td {
            padding: 3px 4px;
            border: 1px solid #666;
            white-space: nowrap;
        }

        .patient-table {
            margin-bottom: 7px;
            table-layout: fixed;
            font-size: 9px;
        }

        .patient-table th,
        .patient-table td {
            padding: 2.5px 3px;
            line-height: 1.05;
            vertical-align: middle;
        }

        .check-table {
            table-layout: fixed;
            font-size: 8px;
        }

        .check-table th,
        .check-table td {
            padding: 2.4px 3px;
            line-height: 1.08;
            vertical-align: middle;
        }

        .check-table .description-column {
            width: 38%;
        }

        .section-title {
            background-color: #8db4e3;
            font-size: 8px;
            text-align: center;
        }

        .session-date {
            padding-right: 1px !important;
            padding-left: 1px !important;
            font-size: 6px;
            text-align: center;
            white-space: nowrap;
        }

        .answer {
            text-align: center;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{public_path('logos/pamedic.png')}}">
            </td>
            <td class="header-content">
                <h1 class="document-title">Corporación Pamedic S.A de C.V Unidad de Hemodiálisis</h1>
                <h2 class="document-subtitle">Pre procedimiento y tiempo fuera</h2>
            </td>
            <td class="header-code">
                <table class="code-box">
                    <tr>
                        <th>CLAVE</th>
                    </tr>
                    <tr>
                        <td>PM-DM-PROC-MED-02: FOR-11</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="patient-table" border="1">
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
<table class="check-table" border="1">
    <thead>
        <tr>
            <th colspan="{{ $verification->count() + 1 }}" class="section-title">VERIFICACIÓN PRE-PROCEDIMIENTO</th>
        </tr>
        <tr>
            <th class="description-column">Fecha de Sesión de Hemodiálisis</th>
            @foreach($verification as $item)
            <th class="session-date">{{ \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y') }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>VERIFICACIÓN DE NOMBRE PACIENTE</td>
            @foreach($verification as $item)
            <td class="answer">{{ $item['patient_name'] == 1 ? '/' : 'X' }}</td>
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
            <td>VERIFICACIÓN DE ACCESO VASCULAR (IDENTIFICACIÓN Y FUNCIONALIDAD)</td>
            @foreach($verification as $item)
            <td style="text-align: center;">{{ $item['vascular_access'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
        <tr>
            <th colspan="{{ $timeOut->count() + 1 }}" class="section-title">TIME OUT/TIEMPO FUERA</th>
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
            <td>VERIFICACIÓN ACCESO VASCULAR </td>
            @foreach($timeOut as $item)
            <td style="text-align: center;">{{ $item['vascular_access_check'] == 1 ? '/' : 'X' }}</td>
            @endforeach
        </tr>
    </tbody>
</table>

</body>
</html>
