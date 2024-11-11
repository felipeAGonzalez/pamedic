@extends('layouts.app')

@section('content')
<form  action="{{ route('treatment.fillPostHemo') }}" method="POST" class="row">
<div class="container">
    <div class="row">
    @csrf
        <div class="col-md-6">
        <input type="hidden" class="form-control" id="patient_id" name="patient_id" value="{{ $id ?? $postHemoDialysis->patient_id}}">
            <div class="form-group">
                <label for="final_ultrafiltration">Ultrafiltración Final</label>
                <input type="text" class="form-control" id="final_ultrafiltration" name="final_ultrafiltration" value="{{ old('final_ultrafiltration', $postHemoDialysis->final_ultrafiltration ?? '') }}">
            </div>
            <div class="form-group">
                <label for="treated_blood">Sangre Tratada</label>
                <input type="text" class="form-control" id="treated_blood" name="treated_blood" value="{{ old('treated_blood', $postHemoDialysis->treated_blood ?? '') }}">
            </div>
            <div class="form-group">
                <label for="ktv">KTV</label>
                <input type="text" class="form-control" id="ktv" name="ktv" value="{{ old('ktv', $postHemoDialysis->ktv ?? '') }}">
            </div>
            <div class="form-group">
                <label for="patient_temperature">Temperatura del Paciente</label>
                <input type="text" class="form-control" id="patient_temperature" name="patient_temperature" value="{{ old('patient_temperature', $postHemoDialysis->patient_temperature ?? '') }}">
            </div>
            <div class="form-group">
                <label for="blood_pressure_stand">Presión Arterial (De Pie)</label>
                <input type="text" class="form-control" id="blood_pressure_stand" name="blood_pressure_stand" value="{{ old('blood_pressure_stand', $postHemoDialysis->blood_pressure_stand ?? '') }}">
            </div>
            <div class="form-group">
                <label for="blood_pressure_sit">Presión Arterial (Sentado)</label>
                <input type="text" class="form-control" id="blood_pressure_sit" name="blood_pressure_sit" value="{{ old('blood_pressure_sit', $postHemoDialysis->blood_pressure_sit ?? '') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="respiratory_rate">Frecuencia Respiratoria</label>
                <input type="text" class="form-control" id="respiratory_rate" name="respiratory_rate" value="{{ old('respiratory_rate', $postHemoDialysis->respiratory_rate ?? '') }}">
            </div>
            <div class="form-group">
                <label for="heart_rate">Frecuencia Cardíaca</label>
                <input type="text" class="form-control" id="heart_rate" name="heart_rate" value="{{ old('heart_rate', $postHemoDialysis->heart_rate ?? '') }}">
            </div>
            <div class="form-group">
                <label for="weight_out">Peso Saliente</label>
                <input type="text" class="form-control" id="weight_out" name="weight_out" value="{{ old('weight_out', $postHemoDialysis->weight_out ?? '') }}">
            </div>
            <div class="form-group">
                <label for="fall_risk">Riesgo de Caída</label>
                <input type="text" class="form-control" id="fall_risk" name="fall_risk" value="{{ old('fall_risk', $postHemoDialysis->fall_risk ?? '') }}">
            </div>
        </div>
    </div>
</div>
<div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">Continuar</button>
            <a href="{{ route('treatment.index') }}" class="btn btn-info">Volver</a>

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
@endsection
