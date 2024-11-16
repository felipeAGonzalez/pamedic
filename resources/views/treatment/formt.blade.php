@extends('layouts.app')

@section('content')
<h1>TransHemodiálisis</h1>
<h3 style="color: red;">{{ $patient->name .' '. $patient->last_name }}</h3>
<form  action="{{ route('treatment.fillTransHemo') }}" method="POST" class="row">
<div class="container">
    <div class="row">
    @csrf
        <div class="col-md-6">
                <input type="hidden" class="form-control" id="patient_id" name="patient_id" value="{{ $id ?? $transHemodialysis->patient_id}}">
            <div class="form-group">
                <label for="time">Hora</label>
                <input type="time" class="form-control" id="time" name="time">
            </div>
            <div class="form-group">
                <label for="arterial_pressure">Presión Arterial</label>
                <input type="text" class="form-control" id="arterial_pressure" name="arterial_pressure">
            </div>
            <div class="form-group">
                <label for="mean_pressure">Presión Media</label>
                <input type="text" class="form-control" id="mean_pressure" name="mean_pressure">
            </div>
            <div class="form-group">
                <label for="heart_rate">Frecuencia Cardíaco</label>
                <input type="text" class="form-control" id="heart_rate" name="heart_rate">
            </div>
            <div class="form-group">
                <label for="respiratory_rate">Frecuencia Respiratorio</label>
                <input type="text" class="form-control" id="respiratory_rate" name="respiratory_rate">
            </div>
            <div class="form-group">
                <label for="temperature">Temperatura</label>
                <input type="text" class="form-control" id="temperature" name="temperature">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="arterial_pressure_monitor">Monitor de Presión Arterial</label>
                <input type="text" class="form-control" id="arterial_pressure_monitor" name="arterial_pressure_monitor">
            </div>
            <div class="form-group">
                <label for="venous_pressure_monitor">Monitor de Presión Venosa</label>
                <input type="text" class="form-control" id="venous_pressure_monitor" name="venous_pressure_monitor">
            </div>
            <div class="form-group">
                <label for="transmembrane_pressure_monitor">Monitor de Presión Transmembrana</label>
                <input type="text" class="form-control" id="transmembrane_pressure_monitor" name="transmembrane_pressure_monitor">
            </div>
            <div class="form-group">
                <label for="blood_flow">Flujo Sanguíneo</label>
                <input type="text" class="form-control" id="blood_flow" name="blood_flow">
            </div>
            <div class="form-group">
                <label for="ultrafiltration">Ultrafiltración</label>
                <input type="text" class="form-control" id="ultrafiltration" name="ultrafiltration">
            </div>
            <div class="form-group">
                <label for="heparin">Heparina</label>
                <input type="text" class="form-control" id="heparin" name="heparin">
            </div>
            <div class="form-group">
                <label for="observations">Observaciones</label>
                <textarea class="form-control" id="observations" name="observations"></textarea>
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
