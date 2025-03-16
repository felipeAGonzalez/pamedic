@extends('layouts.app')

@section('content')
<head>
    <title>Note Medic Form</title>
    <style>
        textarea {
            resize: none;
        }
    </style>
</head>
<body>
@if ($errors->any())
                <div class="alert2 alert2-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ __($error) }}<br></li>
                        @endforeach
                        </ul>
                    </div>
            @endif
    <div class="container mt-5">
        <form action="{{ route('noteMedic.store') }}" method="POST">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $id ?? $dialysisMonitoring->patient_id}}">
            <input type="hidden" name="date" value="{{ $dialysisMonitoring->date_hour ?? ''}}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="patient_name"><strong>Paciente: </strong>{{ $patient['last_name'] }} {{ $patient['last_name_two'] }} {{ $patient['name'] }}</label><br>
                        <label for="birth_date"><strong>Fecha de Nacimiento: </strong>{{ \Carbon\Carbon::parse($patient['birth_date'])->format('d/m/Y') }}</label><br>
                        <label for="date_entry"><strong>Fecha de Ingreso: </strong>{{ \Carbon\Carbon::parse($patient['date_entry'])->format('d/m/Y') }}</label><br>
                        <label for="gender"><strong>Género: </strong>{{ $patient['gender'] == 'M' ? 'Masculino' : 'Femenino' }}</label>
                        <br>
                        <br>
                        <label for="date"><strong>
                            <select name="note_type" class="form-control" style="display: inline-block; width: auto;">
                                <option value="input" {{ (isset($medicNote) && $medicNote->note_type == 'input') ? 'selected' : '' }}>Nota de Ingreso</option>
                                <option value="output" {{ (isset($medicNote) && $medicNote->note_type == 'output') ? 'selected' : '' }}>Nota de Egreso</option>
                                <option value="evolution" {{ (isset($medicNote) && $medicNote->note_type == 'evolution') ? 'selected' : '' }}>Nota de Evolución</option>
                            </select>
                            </strong> {{ \Carbon\Carbon::parse($dialysisMonitoring->date_hour)->addHours(3)->format('d/m/Y H:i') }}</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date"><strong>Edad:</strong> {{ \Carbon\Carbon::parse($patient['birth_date'])->age }}</label><br>
                        <label for="text"><strong>Servicio:</strong>Hemodialisis</label><br>
                        <label for="text"><strong>Numero de Expediente:</strong>{{ $patient['expedient_number'] }}</label><br>
                        <label for="text"><strong>alergias:</strong>{{ $dialysisMonitoring->allergy }}</label>

                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="date"><strong>VITALES A SU INGRESO:</strong> PESO FINAL ANTERIOR <strong>{{$preHemodialysis['previous_final_weight']}}</strong> KG, GANANCIA INTERDALICA: <strong>{{$preHemodialysis['initial_weight'] - $preHemodialysis['previous_final_weight']}}</strong> KG,
                        PESO INICIAL: <strong>{{$preHemodialysis['initial_weight']}}</strong> KG, PESO SECO: <strong>{{$preHemodialysis['dry_weight']}}</strong> KG A PESO SECO: <strong>{{$preHemodialysis['weight_gain']}}</strong> KG TA: <strong>{{$preHemodialysis['standing_blood_pressure']}}</strong> MMHG, TEMPERATURA: <strong>{{$preHemodialysis['body_temperature']}}</strong> °</label><br>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="date"><strong>VITALES A SU EGRESO:</strong> PESO DE SALIDA: <strong>{{$postHemoDialysis['weight_out']}}</strong> FLUJO SANGUINEO <strong>{{$dialysisPrescription['blood_flux']}}</strong> ML/MIN ULTRAFILTRACIÓN FINAL: <strong>{{$postHemoDialysis['final_ultrafiltration']}}</strong> ML,
                        SANGRE TRATADA: <strong>{{$postHemoDialysis['treated_blood']}}</strong> L, KTV: <strong>{{$postHemoDialysis['ktv']}}</strong>, TEMPERATURA: <strong>{{$postHemoDialysis['patient_temperature']}}</strong>, TA: <strong>{{$postHemoDialysis['blood_pressure_stand']}}</strong>, FR: <strong>{{$postHemoDialysis['respiratory_rate']}}</strong>, FC: <strong>{{$postHemoDialysis['heart_rate']}}</strong></label><br>
                </div>
            </div>
            <div class="form-group">
                <label for="time"><strong>Tiempo de sesión:</strong> {{ $totalTime }}</label>
            </div>
            <div class="form-group">
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
            </div>
            <div class="form-group">
                    <label for="medicines"><strong>Tipo de dializador: </strong>{{ $dialysisPrescription->type_dialyzer == 'F6ELISIO21H' || $dialysisPrescription->type_dialyzer == 'F6ELISIO19H' ? "Reúso" : "Desechable"}}</label>
            </div>
            <div class="form-group">
                <label for="patient">P:</label>
                <textarea class="form-control" id="patient" name="patient" rows="4">{{ $medicNote->patient ?? '' }}</textarea>
            </div>
            <div class="form-group">
                <label for="subjective">S:</label>
                <textarea class="form-control" id="subjective" name="subjective" rows="4">{{ $medicNote->subjective ?? '' }}</textarea>
            </div>
            <div class="form-group">
                <label for="objective">O:</label>
                <textarea class="form-control" id="objective" name="objective" rows="4">{{ $medicNote->objective ?? '' }}</textarea>
            </div>

            <div class="form-group">
                <label for="prognosis">P: Pronostico:</label>
                <textarea class="form-control" id="prognosis" name="prognosis" rows="4">{{ $medicNote->prognosis ?? '' }}</textarea>
            </div>
            <div class="form-group">
                <label for="plan">Plan:</label>
                <textarea class="form-control" id="plan" name="plan" rows="4">{{ $medicNote->plan ?? '' }}</textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" onclick="window.close();">Guardar</button>
                <a href="{{ route('print.medicNote') }}" class="btn btn-info" onclick="window.close();">Volver</a>

            </div>
        </form>
    </div>
</body>

@endsection
