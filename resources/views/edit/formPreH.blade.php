@extends('layouts.app')

@section('content')
<h1>Pre-Hemodiálisis</h1>
<h3 style="color: red;">{{ $patient->name .' '. $patient->last_name }}</h3>
<form  action="{{ route('edit.fillPreHemo') }}" method="POST" class="row">
    <div class="row">
        @csrf
        <div class="col-md-6">
            <div class="form-group">
                <input type="hidden" class="form-control" id="patient_id" name="patient_id" value="{{ $id ?? $preHemodialysis->patient_id }}">
                <input type="hidden" name="created_at" value="{{ $id ?? $preHemodialysis->created_at}}">
            </div>
            <div class="form-group">
                <label for="initial_weight">Peso Inicial</label>
                <input type="text" class="form-control" id="initial_weight" name="initial_weight" value="{{ old('initial_weight', $preHemodialysis->initial_weight ?? '') }}">
            </div>
            @if(isset($preHemodialysis))
                <div class="form-group">
                    <label for="previous_initial_weight">Peso Inicial Anterior</label>
                    <input type="text" class="form-control" id="previous_initial_weight" name="previous_initial_weight" value="{{ old('previous_initial_weight', $preHemodialysis->previous_initial_weight ?? '') }}">
                </div>
                <div class="form-group">
                    <label for="previous_final_weight">Peso Final Anterior</label>
                    <input type="text" class="form-control" id="previous_final_weight" name="previous_final_weight" value="{{ old('previous_final_weight', $preHemodialysis->previous_final_weight ?? '') }}">
                </div>
                <div class="form-group">
                    <label for="previous_weight_gain">Peso Ganado Anterior</label>
                    <input type="text" class="form-control" id="previous_weight_gain" name="previous_weight_gain" value="{{ old('previous_weight_gain', $preHemodialysis->previous_weight_gain ?? '') }}">
                </div>
                <div class="form-group">
                    <label for="dry_weight">Peso Seco</label>
                    <input type="text" class="form-control" id="dry_weight" name="dry_weight" value="{{ old('dry_weight', $preHemodialysis->dry_weight ?? '') }}">
                </div>
                <div class="form-group">
                    <label for="weight_gain">Peso Ganado</label>
                    <input type="text" class="form-control" id="weight_gain" name="weight_gain" value="{{ old('weight_gain', $preHemodialysis->weight_gain ?? '') }}">
                </div>
                @endif

            @if ($noReuse != 1)
                <div class="form-group">
                    <label for="reuse_number">Número de Reutilización</label>
                    <input type="text" class="form-control" id="reuse_number" name="reuse_number" value="{{ old('reuse_number', $preHemodialysis->reuse_number ?? '') }}">
                </div>
            @endif
            <div class="form-group">
                <label for="sitting_blood_pressure">Presión Arterial Sentado</label>
                <input type="text" class="form-control" id="sitting_blood_pressure" name="sitting_blood_pressure" value="{{ old('sitting_blood_pressure', $preHemodialysis->sitting_blood_pressure ?? '') }}">
            </div>
            <div class="form-group">
                <label for="standing_blood_pressure">Presión Arterial de Pie</label>
                <input type="text" class="form-control" id="standing_blood_pressure" name="standing_blood_pressure" value="{{ old('standing_blood_pressure', $preHemodialysis->standing_blood_pressure ?? '') }}">
            </div>
            <div class="form-group">
                <label for="body_temperature">Temperatura Corporal</label>
                <input type="text" class="form-control" id="body_temperature" name="body_temperature" value="{{ old('body_temperature', $preHemodialysis->body_temperature ?? '') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="heart_rate">Frecuencia Cardíaca</label>
                <input type="text" class="form-control" id="heart_rate" name="heart_rate" value="{{ old('heart_rate', $preHemodialysis->heart_rate ?? '') }}">
            </div>
            <div class="form-group">
                <label for="respiratory_rate">Frecuencia Respiratoria</label>
                <input type="text" class="form-control" id="respiratory_rate" name="respiratory_rate" value="{{ old('respiratory_rate', $preHemodialysis->respiratory_rate ?? '') }}">
            </div>
            <div class="form-group">
                <label for="oxygen_saturation">Saturación de Oxígeno</label>
                <input type="text" class="form-control" id="oxygen_saturation" name="oxygen_saturation" value="{{ old('oxygen_saturation', $preHemodialysis->oxygen_saturation ?? '') }}">
            </div>
            <div class="form-group">
                <label for="conductivity">Conductividad</label>
                <input type="text" class="form-control" id="conductivity" name="conductivity" value="{{ old('conductivity', $preHemodialysis->conductivity ?? '') }}">
            </div>
            <div class="form-group">
                <label for="dextrostix">Dexstrostis</label>
                <input type="text" class="form-control" id="dextrostix" name="dextrostix" value="{{ old('dextrostix', $preHemodialysis->dextrostix ?? '') }}">
            </div>
            <div class="form-group">
                <label for="itchiness">Prurito</label>
                <select class="form-control" id="itchiness" name="itchiness">
                    <option value="low" {{ old('itchiness', $preHemodialysis->itchiness ?? '') == 'low' ? 'selected' : '' }}>Bajo</option>
                    <option value="medium" {{ old('itchiness', $preHemodialysis->itchiness ?? '') == 'medium' ? 'selected' : '' }}>Medio</option>
                    <option value="high" {{ old('itchiness', $preHemodialysis->itchiness ?? '') == 'high' ? 'selected' : '' }}>Alto</option>
                    <option value="N/A" {{ old('itchiness', $preHemodialysis->itchiness ?? '') == 'na' ? 'selected' : '' }}>N/A</option>
                    <option value="N/P" {{ old('itchiness', $preHemodialysis->itchiness ?? '') == 'np' ? 'selected' : '' }}>N/P</option>
                </select>
            </div>
            <div class="form-group">
                <label for="pallor_skin">Palidez de la Piel</label>
                <select class="form-control" id="pallor_skin" name="pallor_skin">
                    <option value="low" {{ old('pallor_skin', $preHemodialysis->pallor_skin ?? '') == 'low' ? 'selected' : '' }}>Bajo</option>
                    <option value="medium" {{ old('pallor_skin', $preHemodialysis->pallor_skin ?? '') == 'medium' ? 'selected' : '' }}>Medio</option>
                    <option value="high" {{ old('pallor_skin', $preHemodialysis->pallor_skin ?? '') == 'high' ? 'selected' : '' }}>Alto</option>
                    <option value="N/A" {{ old('pallor_skin', $preHemodialysis->pallor_skin ?? '') == 'na' ? 'selected' : '' }}>N/A</option>
                    <option value="N/P" {{ old('pallor_skin', $preHemodialysis->pallor_skin ?? '') == 'np' ? 'selected' : '' }}>N/P</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edema">Edema</label>
                <select class="form-control" id="edema" name="edema">
                    <option value="low" {{ old('edema', $preHemodialysis->edema ?? '') == 'low' ? 'selected' : '' }}>Bajo</option>
                    <option value="medium" {{ old('edema', $preHemodialysis->edema ?? '') == 'medium' ? 'selected' : '' }}>Medio</option>
                    <option value="high" {{ old('edema', $preHemodialysis->edema ?? '') == 'high' ? 'selected' : '' }}>Alto</option>
                    <option value="N/A" {{ old('edema', $preHemodialysis->edema ?? '') == 'na' ? 'selected' : '' }}>N/A</option>
                    <option value="N/P" {{ old('edema', $preHemodialysis->edema ?? '') == 'np' ? 'selected' : '' }}>N/P</option>
                </select>
            </div>
            <div class="form-group">
                <label for="vascular_access_conditions">Condiciones de Acceso Vascular</label>
                <input type="text" class="form-control" id="vascular_access_conditions" name="vascular_access_conditions" value="{{ old('vascular_access_conditions', $preHemodialysis->vascular_access_conditions ?? '') }}">
            </div>
            <div class="form-group">
                <label for="fall_risk">Riesgo de Caída</label>
                <select class="form-control" id="fall_risk" name="fall_risk">
                    <option value="low" {{ old('fall_risk', $preHemodialysis->fall_risk ?? '') == 'low' ? 'selected' : '' }}>Bajo</option>
                    <option value="medium" {{ old('fall_risk', $preHemodialysis->fall_risk ?? '') == 'medium' ? 'selected' : '' }}>Medio</option>
                    <option value="high" {{ old('fall_risk', $preHemodialysis->fall_risk ?? '') == 'high' ? 'selected' : '' }}>Alto</option>
                </select>
            </div>
            <div class="form-group">
                <label for="observations">Observaciones</label>
                <textarea class="form-control" id="observations" name="observations">{{ old('observations', $preHemodialysis->observations ?? '') }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary">Continuar</button>
                <a href="{{ route('edit.index') }}" class="btn btn-info">Volver</a>
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
