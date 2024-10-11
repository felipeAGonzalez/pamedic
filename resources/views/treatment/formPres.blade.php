@extends('layouts.app')

@section('content')
<h1>Prescripción de Hemodiálisis</h1>
<form  action="{{ route('treatment.fillPres') }}" method="POST" class="row">
    <div class="row">
        @csrf
        <input type="hidden" class="form-control" id="patient_id" name="patient_id" value="{{ $id ?? $dialysisPrescription->patient_id}}">
        <div class="col-md-6">
            <div class="form-group">
                <label for="type_dialyzer">Tipo de Dializador</label>
                <select class="form-control" id="type_dialyzer" name="type_dialyzer">
                    <option value="">Seleccionar</option>
                    <option value="HF80S">HF80S</option>
                    <option value="F6ELISIO21H">F6ELISIO21H</option>
                    <option value="F6ELISIO19H">F6ELISIO19H</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="time">Tiempo (Min)</label>
                <select class="form-control" id="time" name="time">
                    <option value="">Seleccionar</option>
                    <?php for ($i = 10; $i <= 180; $i += 10): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="blood_flux">Flujo Sanguíneo</label>
                <input type="text" class="form-control" id="blood_flux" name="blood_flux">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="flux_dialyzer">Flujo del Dializador</label>
                <input type="text" class="form-control" id="flux_dialyzer" name="flux_dialyzer">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="heparin">Heparina</label>
                <select class="form-control" id="heparin" name="heparin">
                    <option value="">Seleccionar</option>
                    <option value="bolo_inicial">Bolo Inicial</option>
                    <option value="dosis_unica">Dosis Única</option>
                    <option value="dosis_por_hora">Bolo(ui/h)</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="schedule_ultrafilter">Programar Ultrafiltración</label>
                <input type="text" class="form-control" id="schedule_ultrafilter" name="schedule_ultrafilter">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="profile_ultrafilter">Perfil de Ultrafiltración</label>
                <input type="text" class="form-control" id="profile_ultrafilter" name="profile_ultrafilter">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="sodium_profile">Perfil de Sodio</label>
                <input type="text" class="form-control" id="sodium_profile" name="sodium_profile">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="machine_temperature">Temperatura de la Máquina</label>
                <input type="text" class="form-control" id="machine_temperature" name="machine_temperature">
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
