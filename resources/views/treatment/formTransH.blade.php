@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('treatment.fillTransHemo') }}" method="POST" class="row">
            @csrf
            <table class="table table-responsive" style="width: 100%">
                <thead>
                    <tr>
                        <th>Tiempo</th>
                        <th>Presión Arterial</th>
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
                                    <input type="text" name="arterial_pressure[]" value="{{ $item->arterial_pressure }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="mean_pressure[]" value="{{ $item->mean_pressure }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="heart_rate[]" value="{{ $item->heart_rate }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="respiratory_rate[]" value="{{ $item->respiratory_rate }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="temperature[]" value="{{ $item->temperature }}" step="0.01" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="arterial_pressure_monitor[]" value="{{ $item->arterial_pressure_monitor }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="venous_pressure_monitor[]" value="{{ $item->venous_pressure_monitor }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="transmembrane_pressure_monitor[]" value="{{ $item->transmembrane_pressure_monitor }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="blood_flow[]" value="{{ $item->blood_flow }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="ultrafiltration[]" value="{{ $item->ultrafiltration }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" name="heparin[]" value="{{ $item->heparin }}" class="form-control" required>
                                </td>
                                <td>
                                    <input type="text" name="observations[]" value="{{ $item->observations }}" class="form-control" required>
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


@endsection
