@extends('layouts.app')

@section('content')
<h1>Pesos</h1>
<h3 style="color: red;">{{ $patient->name .' '. $patient->last_name }}</h3>
<form  action="{{ route('treatment.fillWeight') }}" method="POST" class="row">
    <div class="row">
        @csrf
        <div class="col-md-6">
            <div class="form-group">
            <input type="hidden" class="form-control" id="patient_id" name="patient_id" value="{{ $id ?? $patient->id }}">
            </div>
            <div class="form-group">
            <label for="initial_weight">Peso Inicial</label>
            <input type="text" class="form-control" id="initial_weight" name="initial_weight" value="{{ old('initial_weight', $preHemodialysis->initial_weight ?? '') }}" oninput="calculateWeights()">
            </div>
            <div class="form-group">
            <label for="previous_initial_weight">Peso Inicial Anterior</label>
            <input type="text" class="form-control" id="previous_initial_weight" name="previous_initial_weight" value="{{ old('previous_initial_weight', $prevPreHemodialysis->previous_initial_weight ?? $preHemodialysis->previous_initial_weight ?? '') }}" oninput="calculateWeights()">
            </div>
            <div class="form-group">
            <label for="previous_final_weight">Peso Final Anterior</label>
            <input type="text" class="form-control" id="previous_final_weight" name="previous_final_weight" value="{{ old('previous_final_weight', $prevPostHemoDialysis->weight_out ?? $preHemodialysis->previous_final_weight ?? '') }}" oninput="calculateWeights()">
            </div>
            <div class="form-group">
            <label for="previous_weight_gain">Peso Ganado Anterior</label>
            <input type="text" class="form-control" id="previous_weight_gain" name="previous_weight_gain" value="{{ old('previous_weight_gain', $preHemodialysis->previous_weight_gain ?? '') }}" readonly>
            </div>
            <div class="form-group">
            <label for="dry_weight">Peso Seco</label>
            <input type="text" class="form-control" id="dry_weight" name="dry_weight" value="{{ old('dry_weight', $prevPreHemodialysis->dry_weight ?? $preHemodialysis->dry_weight ?? '') }}" oninput="calculateWeights()">
            </div>
            <div class="form-group">
            <label for="weight_gain">Peso Ganado</label>
            <input type="text" class="form-control" id="weight_gain" name="weight_gain" value="{{ old('weight_gain', $preHemodialysis->weight_gain ?? '') }}" readonly>
            </div>
        </div>

        <script>
            function calculateWeights() {
            const initialWeight = parseFloat(document.getElementById('initial_weight').value) || 0;
            // const previousInitialWeight = parseFloat(document.getElementById('initial_weight').value) || 0;
            const previousFinalWeight = parseFloat(document.getElementById('previous_final_weight').value) || 0;
            const dryWeight = parseFloat(document.getElementById('dry_weight').value) || 0;

            // Calculate previous weight gain
            const previousWeightGain = initialWeight - previousFinalWeight;
            document.getElementById('previous_weight_gain').value = previousWeightGain.toFixed(2);

            // Calculate weight gain
            const weightGain = initialWeight - dryWeight;
            document.getElementById('weight_gain').value = weightGain.toFixed(2);
            }
        </script>
    </div>
    <div class="row">
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary">Continuar</button>
                <a href="{{ route('treatment.index') }}" class="btn btn-info">Volver</a>
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
