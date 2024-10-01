@extends('layouts.app')

@section('content')
<h1>Diálisis prescripción</h1>
<form  action="{{ route('treatment.fillPreHemo') }}" method="POST" class="row">
    <div class="row">
        @csrf
        <div class="col-md-6">
            <div class="form-group">
                <input type="hidden" class="form-control" id="patient_id" name="patient_id" value="{{ $id ?? $preHemodialysis->patient_id}}">
            </div>
            <div class="form-group">
                <label for="previous_initial_weight">Peso Inicial Anterior</label>
                <input type="text" class="form-control" id="previous_initial_weight" name="previous_initial_weight">
            </div>
            <div class="form-group">
                <label for="previous_final_weight">Peso Final Anterior</label>
                <input type="text" class="form-control" id="previous_final_weight" name="previous_final_weight">
            </div>
            <div class="form-group">
                <label for="previous_weight_gain">Ganancia de Peso Anterior</label>
                <input type="text" class="form-control" id="previous_weight_gain" name="previous_weight_gain">
            </div>
            <div class="form-group">
                <label for="initial_weight">Peso Inicial</label>
                <input type="text" class="form-control" id="initial_weight" name="initial_weight">
            </div>
            <div class="form-group">
                <label for="dry_weight">Peso Seco</label>
                <input type="text" class="form-control" id="dry_weight" name="dry_weight">
            </div>
            <div class="form-group">
                <label for="weight_gain">Ganancia de Peso</label>
                <input type="text" class="form-control" id="weight_gain" name="weight_gain">
            </div>
            <div class="form-group">
                <label for="reuse_number">Número de Reutilización</label>
                <input type="text" class="form-control" id="reuse_number" name="reuse_number">
            </div>
            <div class="form-group">
                <label for="sitting_blood_pressure">Presión Arterial Sentado</label>
                <input type="text" class="form-control" id="sitting_blood_pressure" name="sitting_blood_pressure">
            </div>
            <div class="form-group">
                <label for="standing_blood_pressure">Presión Arterial de Pie</label>
                <input type="text" class="form-control" id="standing_blood_pressure" name="standing_blood_pressure">
            </div>
            <div class="form-group">
                <label for="body_temperature">Temperatura Corporal</label>
                <input type="text" class="form-control" id="body_temperature" name="body_temperature">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="heart_rate">Ritmo Cardíaco</label>
                <input type="text" class="form-control" id="heart_rate" name="heart_rate">
            </div>
            <div class="form-group">
                <label for="respiratory_rate">Ritmo Respiratorio</label>
                <input type="text" class="form-control" id="respiratory_rate" name="respiratory_rate">
            </div>
            <div class="form-group">
                <label for="oxygen_saturation">Saturación de Oxígeno</label>
                <input type="text" class="form-control" id="oxygen_saturation" name="oxygen_saturation">
            </div>
            <div class="form-group">
                <label for="conductivity">Conductividad</label>
                <input type="text" class="form-control" id="conductivity" name="conductivity">
            </div>
            <div class="form-group">
                <label for="destrostix">Destrostix</label>
                <input type="text" class="form-control" id="destrostix" name="destrostix">
            </div>
            <div class="form-group">
                <label for="itchiness">Prurito</label>
                <input type="text" class="form-control" id="itchiness" name="itchiness">
            </div>
            <div class="form-group">
                <label for="pallor_skin">Palidez de la Piel</label>
                <input type="text" class="form-control" id="pallor_skin" name="pallor_skin">
            </div>
            <div class="form-group">
                <label for="edema">Edema</label>
                <input type="text" class="form-control" id="edema" name="edema">
            </div>
            <div class="form-group">
                <label for="vascular_access_conditions">Condiciones de Acceso Vascular</label>
                <input type="text" class="form-control" id="vascular_access_conditions" name="vascular_access_conditions">
            </div>
            <div class="form-group">
                <label for="fall_risk">Riesgo de Caída</label>
                <input type="text" class="form-control" id="fall_risk" name="fall_risk">
            </div>
            <div class="form-group">
                <label for="observations">Observaciones</label>
                <textarea class="form-control" id="observations" name="observations"></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary">Continuar</button>
        <a href="{{ route('patients.index') }}" class="btn btn-info">Volver</a>
            </div>
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
