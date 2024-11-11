@extends('layouts.app')
@section('content')
<form action="{{ route('treatment.fillNurseEvaluation') }}" method="POST" class="row">
@csrf
@foreach ($nurseValo as $evaluation)
<div class="col-md-3  mx-auto">
        <h3>{{$evaluation['fase']}}</h3>
        <input type="hidden" name="fase[]" id="fase" class="form-control" value="{{$evaluation['fase']}}">

        <input type="hidden" name="patient_id[]" id="patient_id" class="form-control" value="{{ $evaluation['patient_id'] }}">

        <label for="nurse_valuation">Valoración de Enfermería</label>
        <textarea name="nurse_valuation[]" id="nurse_valuation" class="form-control" required>{{ $evaluation['nurse_valuation'] }}</textarea>

        <label for="nurse_intervention">Intervención de Enfermería</label>
        <textarea name="nurse_intervention[]" id="nurse_intervention" class="form-control" required>{{ $evaluation['nurse_intervention'] }}</textarea>
        <br>
</div>

@endforeach
<div class="col-md-6">
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('treatment.index') }}" class="btn btn-info">Volver</a>
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
