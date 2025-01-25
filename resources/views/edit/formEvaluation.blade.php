@extends('layouts.app')

@section('content')
<h1>Evaluación de Dolor</h1>
<h3 style="color: red;">{{ $patient->name .' '. $patient->last_name }}</h3>
<form action="{{ route('edit.fillEvaluation') }}" method="POST" class="row">
@csrf
@foreach ($evaluationRisk as $evaluation)
<div class="col-md-3  mx-auto">
        <h3>{{$evaluation['fase']}}</h3>
    <input type="hidden" name="fase[]" id="fase" class="form-control" value="{{$evaluation['fase']}}">

        <input type="hidden" name="patient_id[]" id="patient_id" class="form-control" value="{{ $evaluation['patient_id'] }}">

        <label for="hour">Hora</label>
        <input type="time" name="hour[]" id="hour" class="form-control" value="{{ \Carbon\Carbon::parse($evaluation['hour'])->format('H:i') }}" required>

        <label for="result">Resultado</label>
        <select name="score[]" id="score" class="form-control" required>
            @foreach(['0', '2', '4', '6', '8', '10'] as $score)
                <option value="{{ $score }}" {{ $evaluation['result'] == $score ? 'selected' : '' }}>{{ $score }}</option>
            @endforeach
        </select>
        <br>
</div>

@endforeach
<div class="col-md-6">
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('edit.index') }}" class="btn btn-info">Volver</a>
</div>
@if ($errors->any())
    <div class="alert2 alert2-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ __($error) }}<br></li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
