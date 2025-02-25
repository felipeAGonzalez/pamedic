<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$date.'-'.substr($patient->expedient_number, -4)}}</title>
</head>
<body>
    <table style="width: 100%;  border-collapse: collapse;">
        <tr>
            <td style="width: 50px; background-color: #8db4e3;">
                <img src="{{ public_path('logos/pamedic.png') }}" width="70" style="margin-right: 10px;">
            </td>
            <td colspan=5>
                <h3 style="margin: 0; text-align: center; background-color: #8db4e3; padding: 10px;"><strong><pre>       Hoja de Enfermería - Nota de Enfermería        </pre></strong></h3>
            </td>
        </tr>
    </table>
    <table border="1" style="width: 100%;">
        <tr>
            <td style="font-size: 12px;"><Strong>{{ $patient->name }}</Strong></td>
            <td style="font-size: 12px;"><Strong>{{ $patient->last_name }}</Strong></td>
            <td style="font-size: 12px;"><Strong>{{ $patient->last_name_two }}</Strong></td>
            <td style="font-size: 12px;"><Strong>{{ $patient->expedient_number }}</Strong></td>
            <td style="font-size: 12px;"><Strong>{{ $patient->gender}}</Strong></td>
            <td style="font-size: 12px;"><Strong>{{ $patient->birth_date ? $patient->birth_date->age : 'Sin fecha de nacimiento' }}</Strong></td>
        </tr>
        <tr>
            <td style="background-color: #e6e6e6; font-size: 12px; width: 24.66%;">Nombre(s) del Paciente</td>
            <td style="background-color: #e6e6e6; font-size: 12px; width: 20%;">Apellido Paterno</td>
            <td style="background-color: #e6e6e6; font-size: 12px; width: 20%;">Apellido Materno</td>
            <td style="background-color: #e6e6e6; font-size: 12px; width: 22.32%;">No. de Expediente</td>
            <td style="background-color: #e6e6e6; font-size: 12px; width: 7.5%;">Género</td>
            <td style="background-color: #e6e6e6; font-size: 12px; width: 5.5%;">Edad</td>
        </tr>
        <tr>
            <td colspan="6" style="background-color: #8db4e3; text-align: center; padding: 5px;">
            <h4 style="margin: 0;">MONITOREO PRE-TRANS Y POST DIÁLISIS</h4>
            </td>
        </tr>

    </table>
    <table border=1 style="width: 100%;">
    <tr>
        <td style="background-color: #e6e6e6; width: 14.3%; font-size: 12px;">FECHA</td>
        <td style="background-color: #e6e6e6; width: 14.3%; font-size: 12px;">HORA</td>
        <td style="background-color: #e6e6e6; width: 14.3%; font-size: 12px;">Nº SESIÓN</td>
        <td style="background-color: #e6e6e6; width: 14.3%; font-size: 12px;">Nº MAQUINA</td>
        <td style="background-color: #e6e6e6; width: 14.3%; font-size: 12px;">ACCESO VASCULAR</td>
        <td style="background-color: #e6e6e6; width: 14.3%; font-size: 12px;">No. AGUJA</td>
        <td style="background-color: #e6e6e6; width: 14.3%; font-size: 12px;">IMPLANTACIÓN</td>
        <td style="background-color: #e6e6e6; width: 14.3%; font-size: 12px;">FECHA DE COLOCACIÓN</td>
    </tr>
    <tr>
        <td>{{ date('d-m-y', strtotime($dialysisMonitoring['date_hour'])) }}</td>
        <td>{{ date('H:i', strtotime($dialysisMonitoring['date_hour'])) }}</td>
        <td>{{ $dialysisMonitoring['session_number'] }}</td>
        <td>{{ $dialysisMonitoring['machine_number'] }}</td>
        <td>{{ __('web.'.$dialysisMonitoring['vascular_access']) }}</td>
        @if($dialysisMonitoring['vascular_access'] == 'catheter')
            <td>{{ __('web.'.$dialysisMonitoring['catheter_type'])}}</td>
        @else
            <td>{{ $dialysisMonitoring['needle_mesure'] }}</td>
        @endif
        <td>{{ __('web.'.$dialysisMonitoring['implantation']) ." ". __('web.'.$dialysisMonitoring['side'])}}</td>
        <td>{{ date('d-m-y', strtotime($dialysisMonitoring['collocation_date'])) }}</td>
    </tr>
</table>
<table border=1 style="width: 100%;">
    <tr>
        <td style="background-color: #e6e6e6; width: 30%; font-size: 13px;">DIAGNOSTICO (CIE 10)</td>
        <td style="background-color: #e6e6e6; width: 20%; font-size: 13px;">ALERGIAS</td>
        <td style="background-color: #e6e6e6; width: 15%; font-size: 12px;">TIPO DE SANGRE</td>
        <td style="background-color: #e6e6e6; width: 35%; font-size: 13px;">SEROLOGÍA  <b>{{ $dialysisMonitoring['serology'] == 'positivo' ? 'POSITIVO' : 'NEGATIVO' }}</b></td>
    </tr>
    <tr>
        <td>{{ $dialysisMonitoring['diagnostic'] }}</td>
        <td>{{ $dialysisMonitoring['allergy'] }}</td>
        <td>{{ $dialysisMonitoring['blood_type'] }}</td>
        <td>FECHA: {{ date('Y-m-d', strtotime($dialysisMonitoring['serology_date'])) }}</td>
    </tr>
    <tr>
        <td colspan="6" style="background-color: #8db4e3; text-align: center; padding: 5px;">
            <h4 style="margin: 0;">PRESCRIPCIÓN DE HEMODIÁLISIS</h4>
        </td>
    </tr>
</table>
<table border=1 style="width: 100%;">
    <tr>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">BCM</td>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">TIPO DIALIZADOR</td>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">TIEMPO (MIN)</td>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">FLUJO SANGUÍNEO</td>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">FLUJO DIALIZANTE</td>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">HEPARINA (DOSIS)</td>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">ULTRAFILTRACIÓN PROGRAMADA</td>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">PERFIL DE ULTRAFILTRACIÓN</td>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">PERFIL DE SODIO</td>
        <td style="background-color: #e6e6e6; width: 11%; font-size: 10px;">TEMPERATURA MÁQUINA</td>
    </tr>
    <tr>
        <td>---</td>
        <td style='font-size: 12px'>{{ $dialysisPrescription['type_dialyzer'] }}</td>
        <td style='font-size: 12px'>{{ $dialysisPrescription['time'] }}</td>
        <td style='font-size: 12px'>{{ $dialysisPrescription['blood_flux'] }}</td>
        <td style='font-size: 12px'>{{ $dialysisPrescription['flux_dialyzer'] }}</td>
        <td style='font-size: 12px'>{{ __('web.'.$dialysisPrescription['heparin']) }}</td>
        <td style='font-size: 12px'>{{ $dialysisPrescription['schedule_ultrafilter'] }}</td>
        <td style='font-size: 12px'>{{ $dialysisPrescription['profile_ultrafilter'] }}</td>
        <td style='font-size: 12px'>{{ $dialysisPrescription['sodium_profile'] }}</td>
        <td style='font-size: 12px'>{{ $dialysisPrescription['machine_temperature'] }}</td>
    </tr>
    <td colspan="15" style="background-color: #8db4e3; text-align: center; padding: 5px;">
            <h4 style="margin: 0;">PRE-HEMODIÁLISIS</h4>
    </td>
</table>
<table border=1 style="width: 100%;">
    <tr>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">PESO INICIAL ANTERIOR</td>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">PESO FINAL ANTERIOR</td>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">PESO GANADO ANTERIOR</td>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">PESO INICIAL</td>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">PESO SECO</td>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">PESO GANADO</td>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">NÚMERO DE REÚSO DEL DIALIZADOR</td>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">TENSIÓN ARTERIAL</td>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">TEMPERATURA CORPORAL</td>
        <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">FRECUENCIA CARDIACA</td>
    </tr>
    <tr>
        <td style="font-size: 8px;">{{ $preHemodialysis['previous_initial_weight'] }}</td>
        <td style="font-size: 8px;">{{ $preHemodialysis['previous_final_weight'] }}</td>
        <td style="font-size: 8px;">{{ $preHemodialysis['previous_weight_gain'] }}</td>
        <td style="font-size: 8px;">{{ $preHemodialysis['initial_weight'] }}</td>
        <td style="font-size: 8px;">{{ $preHemodialysis['dry_weight'] }}</td>
        <td style="font-size: 8px;">{{ $preHemodialysis['weight_gain'] }}</td>
        <td style="font-size: 8px;">{{ $preHemodialysis['reuse_number'] }}</td>
        <td style="font-size: 7px;">Sentado: {{ $preHemodialysis['sitting_blood_pressure'] }}<br>De Pie: {{ $preHemodialysis['standing_blood_pressure'] }}</td>
        <td style="font-size: 8px;">{{ $preHemodialysis['body_temperature'] }}</td>
        <td style="font-size: 8px;">{{ $preHemodialysis['heart_rate'] }}</td>
    </tr>
</table>
<table border=1 style="width: 100%;">
<tr>
    <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">FRECUENCIA RESPIRATORIA</td>
    <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">SATURACIÓN DE OXÍGENO</td>
    <td style="background-color: #e6e6e6; width: 5.25%; font-size: 7px;">CONDUCTIVIDAD</td>
    <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">DESTROSTIS</td>
    <td style="background-color: #e6e6e6; width: 6.25%; font-size: 7px;">PRURITO</td>
    <td style="background-color: #e6e6e6; width: 20%; font-size: 9px;">PALIDEZ DE PIEL</td>
    <td style="background-color: #e6e6e6; width: 20%; font-size: 9px;">EDEMA</td>
    <td style="background-color: #e6e6e6; width: 20%; font-size: 9px;">CONDICIONES DEL ACCESO VASCULAR</td>
    <td style="background-color: #e6e6e6; width: 20%; font-size: 9px;">RIESGO DE CAÍDA</td>
    <td style="background-color: #e6e6e6; width: 20%; font-size: 9px;">OBSERVACIONES</td>
</tr>
<tr>
    <td style="font-size: 8px;">{{ $preHemodialysis['respiratory_rate'] }}</td>
    <td style="font-size: 8px;">{{ $preHemodialysis['oxygen_saturation'] }}</td>
    <td style="font-size: 8px;">{{ $preHemodialysis['conductivity'] }}</td>
    <td style="font-size: 8px;">{{ $preHemodialysis['destrostix'] }}</td>
    <td style="font-size: 8px;">{{ $preHemodialysis['itchiness'] }}</td>
    <td style="width: 20%; font-size: 12px;">{{ __('web.'.$preHemodialysis['pallor_skin']) }}</td>
    <td style="width: 20%; font-size: 12px;">{{ __('web.'.$preHemodialysis['edema']) }}</td>
    <td style="width: 20%; font-size: 12px;">{{ $preHemodialysis['vascular_access_conditions'] }}</td>
    <td style="width: 20%; font-size: 12px;">{{ __('web.'.$preHemodialysis['fall_risk']) }}</td>
    <td style="width: 20%; font-size: 12px;">{{ $preHemodialysis['observations'] }}</td>
</tr>
<td colspan="15" style="background-color: #8db4e3; text-align: center; padding: 5px;">
        <h4 style="margin: 0;">TRANS-HEMODIÁLISIS</h4>
</td>
</table>
<table class="table table-responsive" style="width: 100%">
    <thead>
        <tr>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Tiempo</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Presión Arterial</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Presión Media</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Frecuencia Cardíaco</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Frecuencia Respiratorio</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Temperatura</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Monitor de Presión Arterial</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Monitor de Presión Venosa</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Monitor de Presión Transmembrana</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Flujo Sanguíneo</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Ultrafiltración</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Heparina</th>
            <th style="background-color: #e6e6e6; width: 7.1%; font-size: 10px;">Observaciones</th>
        </tr>
    </thead>
    <tbody>
            @foreach ($transHemodialysis as $item)
                <tr>
                    <td style="font-size: 15px;">{{ date('H:i', strtotime($item->time)) ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->arterial_pressure ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->mean_pressure ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->heart_rate ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->respiratory_rate ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->temperature == '0.00' ? '-' : $item->temperature }}</td>
                    <td style="font-size: 15px;">{{ $item->arterial_pressure_monitor ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->venous_pressure_monitor ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->transmembrane_pressure_monitor ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->blood_flow ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->ultrafiltration ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->heparin ?: '-' }}</td>
                    <td style="font-size: 15px;">{{ $item->observations ?: '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <table>
            <td>
            <img src="{{ public_path('logos/painLevel.png') }}" width="370" height="175" style="margin-right: 00px;">
            </td>
            <td>
                <table border=1 style="width: 100%;">
                <td colspan="25" style="background-color: #8db4e3; text-align: center; padding: 0px;">
                    <h4 style="margin: 0;"><PRE>       EVALUACIÓN DE DOLOR       </PRE></h4>
                 </td>
                </table>
                <table  style="font-size: 9px;">
                    @foreach($evaluationRisk as $evaluationRisk)
                        <td>
                            <table border=1 style="font-size: 9px;">
                                <tr>
                                    <th style = "background-color: #e6e6e6;"colspan=2>{{$evaluationRisk['fase']}}</th>
                                </tr>
                                <tr>
                                    <th style="background-color: #e6e6e6; width: 17.5%;">HORA</th>
                                    <th style="background-color: #e6e6e6; width: 17.5%;">RESULTADO</th>
                                </tr>
                                <tr>
                                    <td style="width: 17.5%;">{{ date('H:i', strtotime($evaluationRisk['hour'])) }}</td>
                                    <td style="width: 17.5%; text-align: center;">{{$evaluationRisk['result']}}</td>
                                </tr>
                            </table>
                        </td>
                    @endforeach
                </table>
                <table border=1 style="width: 100%;">
                <td colspan="25" style="background-color: #8db4e3; text-align: center; padding: 0px;">
                    <h4 style="margin: 0;">       EVALUACIÓN DE RIESGO DE CAÍDAS       </h4>
                    </td>
                </table>
                <table border=1 style="width: 100%;">
                <tr>
                    <td style="background-color: #e6e6e6; width: 75%; font-size: 12px;">RIESGO DE CAÍDAS TRANS</td>
                    <td style="width:25%; font-size: 12px;">{{__('web.'.$evaluationRisk['fall_risk_trans'])}}</td>
                </tr>
                </table>
            </td>
        </table>

        <table border=1 style="width: 100%;">
            <tr>
                <td style="background-color: #8db4e3; width: 40%; text-align: center;">VALORACIÓN DE ENFERMERÍA</td>
                <td style="background-color: #8db4e3; width: 20%; text-align: center;">FASE</td>
                <td style="background-color: #8db4e3; width: 40%; text-align: center;">INTERVENCIÓN DE ENFERMERÍA</td>
            </tr>
            @foreach($nurseValo as $nurseValo)
            <tr>
                <td style="text-align: center;">{{ $nurseValo['nurse_valuation'] }}</td>
                <td style="text-align: center;">{{ $nurseValo['fase'] }}</td>
                <td style="text-align: center;">{{ $nurseValo['nurse_intervention'] }}</td>
            </tr>
            @endforeach
            <td colspan="6" style="background-color: #8db4e3; text-align: center; padding: 5px;">
            <h4 style="margin: 0;">MINISTRACIÓN DE MEDICAMENTOS</h4>
        </td>
    </table>
        <table border=1 style="width: 100%;">
            <tr>
            <td colspan=3 style="background-color: #e6e6e6; width: 100%; font-size: 18px;">Enfermero que prepara</td>
            <td colspan=3 style="background-color: #e6e6e6; width: 100%; font-size: 18px;">Enfermero que administra</td>
            </tr>
                <tr>
                <td colspan=3 style="font-size: 18px;">{{ !empty($medicineAdmin) && isset($medicineAdmin[0]) ? $medicineAdmin[0]->nurse_prepare->name : 'Sin medicamentos' }}</td>
                <td colspan=3 style="font-size: 18px;">{{ !empty($medicineAdmin) && isset($medicineAdmin[0]) ? $medicineAdmin[0]->nurse_admin->name : 'Sin medicamentos'}}</td>
                </tr>
            <tr>
                <td style="background-color: #e6e6e6; width: 14.28%; font-size: 18px;">Medicamento</td>
                <td style="background-color: #e6e6e6; width: 14.28%; font-size: 18px;">Via de administración</td>
                <td style="background-color: #e6e6e6; width: 14.28%; font-size: 18px;">Dilución</td>
                <td style="background-color: #e6e6e6; width: 14.28%; font-size: 18px;">Velocidad</td>
                <td style="background-color: #e6e6e6; width: 14.28%; font-size: 18px;">Hora</td>
                <td style="background-color: #e6e6e6; width: 14.28%; font-size: 18px;">Fecha de Vencimiento</td>
            </tr>
            <tr>

                @if(count($medicineAdmin)>0)
                @foreach($medicineAdmin as $medication)
                        <tr>
                            <td style="font-size: 18px;">{{ $medication->medicine->name }}</td>
                            <td style="font-size: 18px;">{{ $medication->medicine->route_administration }}</td>
                            <td style="font-size: 18px;">{{ $medication['dilution'] }}</td>
                            <td style="font-size: 18px;">{{ $medication['velocity'] }}</td>
                            <td style="font-size: 18px;">{{ date('H:i', strtotime($medication['hour'])) }}</td>
                            <td style="font-size: 18px;">{{ date('m-Y', $medication['dueDate']) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" style="font-size: 18px; text-align: center;">Sin medicamentos</td>
                    </tr>
                @endif
            </tr>
        </table>
    <table border=1 style="width: 100%;">
    <td colspan="15" style="background-color: #8db4e3; text-align: center; padding: 5px;">
            <h4 style="margin: 0;">POST-HEMODIÁLISIS</h4>
    </td>
        <tr>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">ULTRAFILTRACIÓN FINAL</td>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">SANGRE TRATADA</td>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">KTV</td>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">TEMPERATURA DEL PACIENTE</td>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">PRESIÓN ARTERIAL DE PIE</td>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">PRESIÓN ARTERIAL SENTADO</td>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">FRECUENCIA RESPIRATORIA</td>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">FRECUENCIA CARDIACA</td>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">PESO DE SALIDA</td>
            <td style="background-color: #e6e6e6; width: 10%; font-size: 10px;">RIESGO DE CAÍDA</td>
        </tr>
        <tr>
            <td style="width: 20%; font-size: 12px;">{{ $postHemoDialysis['final_ultrafiltration'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $postHemoDialysis['treated_blood'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $postHemoDialysis['ktv'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $postHemoDialysis['patient_temperature'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $postHemoDialysis['blood_pressure_stand'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $postHemoDialysis['blood_pressure_sit'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $postHemoDialysis['respiratory_rate'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $postHemoDialysis['heart_rate'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $postHemoDialysis['weight_out'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ __('web.'.$postHemoDialysis['fall_risk']) }}</td>
        </tr>
        <td colspan="15" style="background-color: #8db4e3; text-align: center; padding: 5px;">
                <h4 style="margin: 0;">Oxigenoterapia</h4>
        </td>
    </table>
    <table border=1 style="width: 100%;">
        <tr>
            <td style="background-color: #e6e6e6; width: 20%; font-size: 12px;">Saturación de Oxígeno Inicial</td>
            <td style="background-color: #e6e6e6; width: 20%; font-size: 12px;">Saturación de Oxígeno Final</td>
            <td style="background-color: #e6e6e6; width: 20%; font-size: 12px;">Hora de Inicio</td>
            <td style="background-color: #e6e6e6; width: 20%; font-size: 12px;">Hora de Fin</td>
            <td style="background-color: #e6e6e6; width: 20%; font-size: 12px;">Flujo de Oxígeno</td>
        </tr>
        <tr>
            @if(isset($oxygenTherapy))
                <td style="font-size: 12px;">{{ $oxygenTherapy['initial_oxygen_saturation'] ?? '-' }}</td>
                <td style="font-size: 12px;">{{ $oxygenTherapy['final_oxygen_saturation'] ?? '-' }}</td>
                <td style="font-size: 12px;">{{ $oxygenTherapy['start_time'] != '00:00:00' ? $oxygenTherapy['start_time'] : '-' }}</td>
                <td style="font-size: 12px;">{{ $oxygenTherapy['end_time'] != '00:00:00' ? $oxygenTherapy['end_time'] : '-' }}</td>
                <td style="font-size: 12px;">{{ $oxygenTherapy['oxygen_flow'] ?? '-' }}</td>
            @else
                <td style="font-size: 12px;">-</td>
                <td style="font-size: 12px;">-</td>
                <td style="font-size: 12px;">-</td>
                <td style="font-size: 12px;">-</td>
                <td style="font-size: 12px;">-</td>
            @endif
        </tr>
        <td colspan="15" style="background-color: #8db4e3; text-align: center; padding: 5px;">
                <h4 style="margin: 0;">ENFERMERO RESPONSABLE</h4>
        </td>
    </table>
    <table border=1 style="width: 100%;">
        <tr>
            <td style="width: 20%; font-size: 12px;">{{ $user['name'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $user['last_name_one'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $user['last_name_two'] }}</td>
            <td style="width: 20%; font-size: 12px;">{{ $user['profesional_id'] }}</td>
            <td style="width: 20%; font-size: 12px;"></td>
            <td style="width: 20%; font-size: 12px;">{{ date('H:i', strtotime($dialysisMonitoring['date_hour'])) }}</td>
            <td style="width: 20%; font-size: 12px;">{{ date('H', strtotime($dialysisMonitoring['date_hour'])) < 14 ? 'Matutino' : 'Vespertino' }}</td>
        </tr>
        <tr>
            <td style="background-color: #e6e6e6; width: 25%; font-size: 12px;">Nombre</td>
            <td style="background-color: #e6e6e6; width: 25%; font-size: 12px;">Apellido Paterno</td>
            <td style="background-color: #e6e6e6; width: 25%; font-size: 12px;">Apellido Materno</td>
            <td style="background-color: #e6e6e6; width: 25%; font-size: 12px;">Cedula Profesional</td>
            <td style="background-color: #e6e6e6; width: 25%; font-size: 12px;">Firma</td>
            <td style="background-color: #e6e6e6; width: 25%; font-size: 12px;">Hora</td>
            <td style="background-color: #e6e6e6; width: 25%; font-size: 12px;">Turno</td>
        </tr>
    </table>
</body>
</html>
