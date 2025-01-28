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
    <div class="container mt-5">
        <form action="{{ route('noteMedic.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="patient_name"><strong>Paciente: </strong>{{ $patient['last_name'] }} {{ $patient['last_name_two'] }} {{ $patient['name'] }}</label><br>
                        <label for="birth_date"><strong>Fecha de Nacimiento: </strong>{{ \Carbon\Carbon::parse($patient['birth_date'])->format('d/m/Y') }}</label><br>
                        <label for="date_entry"><strong>Fecha de Ingreso: </strong>{{ \Carbon\Carbon::parse($patient['date_entry'])->format('d/m/Y') }}</label><br>
                        <label for="gender"><strong>Género: </strong>{{ $patient['gender'] == 'M' ? 'Masculino' : 'Femenino' }}</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date"><strong>Edad:</strong> {{ \Carbon\Carbon::parse($patient['birth_date'])->age }}</label><br>
                        <label for="text"><strong>Servicio:</strong>Hemodialisis</label><br>
                        <label for="text"><strong>Numero de Expediente:</strong>{{ $patient['expedient_number'] }}</label><br>
                        <label for="text"><strong>alergias:</strong>{{ $dialysisMonitoring->allergy }}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="patient">P:</label>
                <textarea class="form-control" id="patient" name="patient" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="subjective">S:</label>
                <textarea class="form-control" id="subjective" name="subjective" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="objective">O:</label>
                <textarea class="form-control" id="objective" name="objective" rows="4"></textarea>
            </div>
            <div class="col-md-6">
                    <div class="form-group">
                        <label for="date">A VITALES A SU INGRESO: PESO FINAL ANTERIOR {{$preHemodialysis['previous_final_weight']}} KG, GANANCIA INTERDALICA, PESO INICIAL:KG, PESO SECO A PESO SECO KG TA MMHG, TEMPERATURA: °</label><br>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="text">Servicio:Hemodialisis</label><br>

                    </div>
                </div>
            <div class="form-group">
                <label for="prognostic">P: Pronostico:</label>
                <textarea class="form-control" id="prognostic" name="prognostic" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="plan">Plan:</label>
                <textarea class="form-control" id="plan" name="plan" rows="4"></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('print.medicNote') }}" class="btn btn-info">Volver</a>

            </div>
        </form>
    </div>
</body>
@endsection
