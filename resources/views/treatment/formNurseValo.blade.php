@extends('layouts.app')

@section('content')
<form action="{{ route('treatment.fillEvaluation') }}" method="POST">
<div class="form-group">
        <label for="nurse_valuation">Valoración de Enfermería</label>
        <input type="text" name="nurse_valuation" id="nurse_valuation" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="fase">Fase</label>
        <input type="text" name="fase" id="fase" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="nurse_intervention">Intervención de Enfermería</label>
        <input type="text" name="nurse_intervention" id="nurse_intervention" class="form-control" required>
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
