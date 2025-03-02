<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$date.'-'.substr($patient->expedient_number, -4)}}</title>
</head>
<body>
<table style="width: 100%;  border-collapse: collapse;">
        <tr>
            <td style="width: 50px; ">
                <img src="{{public_path('logos/pamedic.png')}}" width="110" style="margin-right: 10px;">
            </td>
            <td colspan=5>
                <h3 style="margin: 0; text-align: center;  padding: 10px;"><strong>
                    <p>   CORPORACIÓN PAMEDIC S.A de C.V <br>
                                UNIDAD DE HEMODIÁLISIS <br>
                            ANEXA AL CENTRO MÉDICO GUADALUPANO     </pre></strong></h3>
            </td>
        </tr>
    </table>
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="width: 50%;">
                        <div class="form-group">
                            <label for="patient_name"><strong>Paciente: </strong>{{ $patient['last_name'] }} {{ $patient['last_name_two'] }} {{ $patient['name'] }}</label><br>
                            <label for="birth_date"><strong>Fecha de Nacimiento: </strong>{{ \Carbon\Carbon::parse($patient['birth_date'])->format('d/m/Y') }}</label><br>
                            <label for="date_entry"><strong>Fecha de Ingreso: </strong>{{ \Carbon\Carbon::parse($patient['date_entry'])->format('d/m/Y') }}</label><br>
                            <label for="gender"><strong>Género: </strong>{{ $patient['gender'] == 'M' ? 'Masculino' : 'Femenino' }}</label><br><br>
                            <label for="date"><strong>NOTA DE EGRESO: </strong>{{ $dialysisMonitoring->date_hour ? \Carbon\Carbon::parse($dialysisMonitoring->date_hour)->addHours(3)->format('d/m/Y H:i') : '' }}</label>
                        </div>
                    </td>
                    <td></td>
                    <td style="width: 50%;">
                        <div class="form-group">
                            <label for="date"><strong>Edad:</strong> {{ \Carbon\Carbon::parse($patient['birth_date'])->age }}</label><br>
                            <label for="text"><strong>Servicio:</strong>Hemodiálisis</label><br>
                            <label for="text"><strong>Numero de Expediente:</strong>{{ $patient['expedient_number'] }}</label><br>
                            <label for="text"><strong>Alergias:</strong>{{ $dialysisMonitoring->allergy }}</label>
                        </div>
                    </td>
                </tr>
            </table>
        <br>
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td><strong>P:</strong>{{ $medicNote->patient }}</td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td><strong>S:</strong>{{ $medicNote->subjective }}</td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td><strong>O:</strong>{{ $medicNote->objective }}</td>
                </tr>
                <tr><td>&nbsp;</td></tr>
            </table>
            <table>
                <tr>
                    <td style="font-size: 14px;"><strong style="font-size: 14px;">VITALES A SU INGRESO:</strong>
                        PESO FINAL ANTERIOR <strong>{{$preHemodialysis['previous_final_weight']}}</strong> KG,
                        GANANCIA INTERDALICA: <strong>{{$preHemodialysis['initial_weight'] - $preHemodialysis['previous_final_weight']}}</strong> KG,
                        PESO INICIAL: <strong>{{$preHemodialysis['initial_weight']}}</strong> KG,
                        PESO SECO: <strong>{{$preHemodialysis['dry_weight']}}</strong> KG
                        A PESO SECO: <strong>{{$preHemodialysis['weight_gain']}}</strong> KG
                        TA: <strong>{{$preHemodialysis['standing_blood_pressure']}}</strong> MMHG,
                        TEMPERATURA: <strong>{{$preHemodialysis['body_temperature']}}</strong> °
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 14px;"><strong style="font-size: 14px;">VITALES A SU EGRESO:</strong><
                        PESO DE SALIDA: <strong>{{$postHemoDialysis['weight_out']}}</strong>
                        FLUJO SANGUINEO <strong>{{$dialysisPrescription['blood_flux']}}</strong> ML/MIN
                        ULTRAFILTRACIÓN FINAL: <strong>{{$postHemoDialysis['final_ultrafiltration']}}</strong> ML,
                        SANGRE TRATADA: <strong>{{$postHemoDialysis['treated_blood']}}</strong> L,
                        KTV: <strong>{{$postHemoDialysis['ktv']}}</strong>,
                        TEMPERATURA: <strong>{{$postHemoDialysis['patient_temperature']}}</strong>,
                        TA: <strong>{{$postHemoDialysis['blood_pressure_stand']}}</strong>,
                        FR: <strong>{{$postHemoDialysis['respiratory_rate']}}</strong>,
                        FC: <strong>{{$postHemoDialysis['heart_rate']}}</strong>
                    </td>
                </tr>
            </table>
            <br>
            <label for="medicines">Medicamentos administrados:</label>
            @if($medicineAdministration->isEmpty())
                <p>SIN MEDICAMENTOS</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th><strong>Medicamento</strong></th>
                            <th><strong>Dilución</strong></th>
                            <th><strong>Fecha de caducidad</strong></th>
                            <th><strong>Hora</strong></th>
                            <th><strong>Velocidad</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medicineAdministration as $administration)
                        <tr>
                            <td>{{ $administration->medicine->name }}</td>
                            <td>{{ $administration['dilution'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($administration['due_date'])->format('m-Y') }}</td>
                            <td>{{ $administration['hour'] }}</td>
                            <td>{{ $administration['velocity'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <br>
            <table style="font-size: 14px;">
                <tr>
                    <td><strong>P: Pronostico:</strong>{{ $medicNote->prognosis }}</td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td><strong>Plan:</strong>{{ $medicNote->plan }}</td>
                </tr>
            </table>
            <br>
            <br>
            <br>
            <br>
            <table style="width: 100%; text-align: right;">
                <tr>
                    <td>
                        Dr.{{$user->name . ' ' . $user->last_name . ' ' . $user->last_name_two}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {{$user->position . ' ' . $user->profesional_id}}
                    </td>
                </tr>
            </table>

    </div>
</body>
