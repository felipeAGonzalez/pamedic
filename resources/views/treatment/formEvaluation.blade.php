@extends('layouts.app')

@section('content')
<form action="{{ route('treatment.fillEvaluation') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="patient_id">Patient ID</label>
        <input type="text" name="patient_id" id="patient_id" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="hour">Hour</label>
        <input type="text" name="hour" id="hour" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="result">Result</label>
        <input type="text" name="result" id="result" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="fall_risk_trans">Fall Risk Trans</label>
        <input type="text" name="fall_risk_trans" id="fall_risk_trans" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="nurse_valuation">nurse_valuation</label>
        <input type="text" name="nurse_valuation" id="nurse_valuation" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="fase">fase</label>
        <input type="text" name="fase" id="fase" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="nurse_intervention">nurse_intervention</label>
        <input type="text" name="nurse_intervention" id="nurse_intervention" class="form-control" required>
    </div>

    'nurse_valuation',
        'fase',
        'nurse_intervention',

    <button type="submit" class="btn btn-primary">Submit</button>
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
