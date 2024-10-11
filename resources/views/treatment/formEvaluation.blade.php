@extends('layouts.app')

@section('content')
<form action="{{ route('treatment.fillEvaluation') }}" method="POST">
    @csrf
    <h3>Pre-diálisis</h3>
        <input type="hidden" name="fase" id="fase" class="form-control" value="pre-dial">
    <div class="form-group">
        <input type="hidden" name="patient_id" id="patient_id" class="form-control" value="{{ $id ?? $evaluationRisk->patient_id}}">
    </div>

    <div class="form-group">
        <label for="hour">Hora</label>
        <input type="text" name="hour" id="hour" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="result">Resultado</label>
        <select name="score" id="score" class="form-control" required>
            @foreach(['0', '2', '4', '6', '8', '10'] as $score)
                <option value="{{ $score }}">{{ $score }}</option>
            @endforeach
        </select>
    </div>
    </div>
    <hr>
    <h3>Trans-diálisis</h3>
        <input type="hidden" name="fase" id="fase" class="form-control" value="pre-dial">
    <div class="form-group">
        <input type="hidden" name="patient_id" id="patient_id" class="form-control" value="{{ $id ?? $evaluationRisk->patient_id}}">
    </div>

    <div class="form-group">
        <label for="hour">Hora</label>
        <input type="text" name="hour" id="hour" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="result">Resultado</label>
        <select name="score" id="score" class="form-control" required>
            @foreach(['0', '2', '4', '6', '8', '10'] as $score)
                <option value="{{ $score }}">{{ $score }}</option>
            @endforeach
        </select>
    </div>
    </div>
    <hr>
    <h3>Post-diálisis</h3>
        <input type="hidden" name="fase" id="fase" class="form-control" value="pre-dial">
    <div class="form-group">
        <input type="hidden" name="patient_id" id="patient_id" class="form-control" value="{{ $id ?? $evaluationRisk->patient_id}}">
    </div>

    <div class="form-group">
        <label for="hour">Hora</label>
        <input type="text" name="hour" id="hour" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="result">Resultado</label>
        <select name="score" id="score" class="form-control" required>
            @foreach(['0', '2', '4', '6', '8', '10'] as $score)
                <option value="{{ $score }}">{{ $score }}</option>
            @endforeach
        </select>
    </div>
    </div>
    <div class="form-group">
        <label for="fall_risk_trans">Riesgo de Caída Trans</label>
        <select name="fall_risk_trans" id="fall_risk_trans" class="form-control" required>
            <option value="low">Bajo</option>
            <option value="medium">Medio</option>
            <option value="high">Alto</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
    <a href="{{ route('treatment.index') }}" class="btn btn-info">Volver</a>
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
