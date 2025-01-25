@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form action="{{ route('treatment.fillOxygen') }}" method="POST">
                @csrf
                <input type="hidden" class="form-control" id="patient_id" name="patient_id" value="{{ $id ?? $oxygenTherapy->patient_id }}">
                <div class="form-group">
                    <label for="initial_oxygen_saturation">Saturación de Oxígeno Inicial:</label>
                    <input type="number" step="0.01" name="initial_oxygen_saturation" id="initial_oxygen_saturation" class="form-control" value="{{ $oxygenTherapy->initial_oxygen_saturation ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label for="final_oxygen_saturation">Saturación de Oxígeno Final:</label>
                    <input type="number" step="0.01" name="final_oxygen_saturation" id="final_oxygen_saturation" class="form-control" value="{{ $oxygenTherapy->final_oxygen_saturation ?? '' }}" required onclick="if(this.value == 0) this.value=''">
                </div>
                <div class="form-group">
                    <label for="start_time">Hora de Inicio:</label>
                    <input type="time" name="start_time" id="start_time" class="form-control" value="{{ $oxygenTherapy->start_time ? \Carbon\Carbon::parse($oxygenTherapy->start_time)->format('H:i') : '' }}" required>
                </div>
                <div class="form-group">
                    <label for="end_time">Hora de Fin:</label>
                    <input type="time" name="end_time" id="end_time" class="form-control" value="{{ $oxygenTherapy->end_time ? \Carbon\Carbon::parse($oxygenTherapy->end_time)->format('H:i') : '' }}" required>
                </div>
                <div class="form-group">
                    <label for="oxygen_flow">Flujo de Oxígeno:</label>
                    <input type="number" step="0.01" name="oxygen_flow" id="oxygen_flow" class="form-control" value="{{ $oxygenTherapy->oxygen_flow ?? '' }}" required onclick="if(this.value == 0) this.value=''">
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
            <div class="mt-3">
                <a href="{{ route('treatment.index') }}" class="btn btn-info">Volver</a>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ __($error) }}<br></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
