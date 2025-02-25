@extends('layouts.app')

@section('content')
<h1>TransHemodiálisis</h1>
<h3 style="color: red;">{{ $patient->name .' '. $patient->last_name }}</h3>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('treatment.fillTransHemo') }}" method="POST" class="row">
            @csrf
            <table class="table table-responsive" style="width: 100%">
                <thead>
                    <tr>
                        <th>Tiempo</th>
                        <th>Presión Arterial (Sistolica)</th>
                        <th>Presión Arterial (Diastolica)</th>
                        <th>Presión Media</th>
                        <th>Frecuencia Cardíaco</th>
                        <th>Frecuencia Respiratorio</th>
                        <th>Temperatura</th>
                        <th>Monitor de Presión Arterial</th>
                        <th>Monitor de Presión Venosa</th>
                        <th>Monitor de Presión Transmembrana</th>
                        <th>Flujo Sanguíneo</th>
                        <th>Ultrafiltración</th>
                        <th>Heparina</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                        @foreach ($transHemodialysis as $item)
                            <tr>
                                <td style="display: none;">
                                    <input type="hidden" name="patient_id[]" value="{{ $item->patient_id }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="time" name="time[]" value="{{ $item->time }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="arterial_pressure_sistolica[]" value="{{ $item->arterial_pressure != '0' ? explode('/', $item->arterial_pressure)[0] : 0 }}" class="form-control" onclick="this.value=''">
                                </td>
                                <td>
                                    <input type="number" name="arterial_pressure_diastolica[]" value="{{ $item->arterial_pressure != '0' ? explode('/', $item->arterial_pressure)[1] : 0 }}" class="form-control" onclick="this.value=''">
                                </td>
                                <td>
                                    <input type="number" name="mean_pressure[]" value="{{ $item->mean_pressure}}" class="form-control" readonly style="width: 120px;">
                                </td>

                                <td>
                                    <select name="heart_rate[]" class="form-control" required>
                                        @for ($i = 40; $i <= 150; $i++)
                                            <option value="{{ $i }}" {{ $item->heart_rate == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td>
                                    <select name="respiratory_rate[]" class="form-control" required>
                                        @for ($i = 8; $i <= 30; $i++)
                                            <option value="{{ $i }}" {{ $item->respiratory_rate == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td>
                                    <select name="temperature[]" class="form-control" required>
                                        @for ($i = 35; $i <= 40; $i += 0.5)
                                            <option value="{{ $i }}" {{ $item->temperature == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="arterial_pressure_monitor[]" value="{{ $item->arterial_pressure_monitor }}" class="form-control" required onclick="this.value=''">
                                </td>
                                <td>
                                    <input type="number" name="venous_pressure_monitor[]" value="{{ $item->venous_pressure_monitor }}" class="form-control" required onclick="this.value=''">
                                </td>
                                <td>
                                    <input type="number" name="transmembrane_pressure_monitor[]" value="{{ $item->transmembrane_pressure_monitor }}" class="form-control" required onclick="this.value=''">
                                </td>
                                <td>
                                    <select name="blood_flow[]" class="form-control" required>
                                        @for ($i = 200; $i <= 400; $i += 50)
                                            <option value="{{ $i }}" {{ $item->blood_flow == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="ultrafiltration[]" value="{{ $item->ultrafiltration }}" class="form-control" required onclick="this.value=''">
                                </td>
                                <td>
                                    <input type="number" name="heparin[]" value="{{ $item->heparin }}" class="form-control" required onclick="this.value=''">
                                </td>
                                <td>
                                    <input type="text" name="observations[]" value="{{ $item->observations }}" class="form-control">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('treatment.index') }}" class="btn btn-info">Volver</a>
            @if ($errors->any())
                <div class="alert2 alert2-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ __($error) }}<br></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </form>
        </div>

<script>
    document.querySelectorAll('input[name="arterial_pressure_sistolica[]"], input[name="arterial_pressure_diastolica[]"]').forEach(function(element) {
        element.addEventListener('blur', function() {
            let row = this.closest('tr');
            let sistolica = row.querySelector('input[name="arterial_pressure_sistolica[]"]').value;
            let diastolica = row.querySelector('input[name="arterial_pressure_diastolica[]"]').value;
            if (sistolica && diastolica) {
                let meanPressure = (2 * parseFloat(sistolica) + parseFloat(diastolica)) / 3;
                row.querySelector('input[name="mean_pressure[]"]').value = Math.ceil(meanPressure);
            }
        });
    });
</script>
@endsection
