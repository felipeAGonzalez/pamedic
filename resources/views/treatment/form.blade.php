@extends('layouts.app')

@section('content')
<h1>Monitoreo Pre-Trans y Post Diálisis</h1>

<form action="{{ route('treatment.fill') }}" method="POST" class="row">
    @csrf
    <input type="hidden" name="patient_id" value="{{ $id ?? $dialysisMonitoring->patient_id}}">
    <div class="col-md-6">
        <div class="form-group">
            <label for="date_hour">Fecha y Hora:</label>
            <input type="datetime-local" name="date_hour" id="date_hour" class="form-control"  value="{{ $dialysisMonitoring->date_hour ?? '' }}">
        </div>

        <div class="form-group">
            <label for="vascular_access">Acceso Vascular:</label>
            <select name="vascular_access" id="vascular_access" class="form-control">
            <option value="">Seleccionar</option> <!-- Opción neutra -->
            <option value="fistula" {{ isset($dialysisMonitoring) && $dialysisMonitoring->vascular_access == 'fistula' ? 'selected' : '' }}>Fistula</option>
            <option value="catheter" {{ isset($dialysisMonitoring) && $dialysisMonitoring->vascular_access == 'catheter' ? 'selected' : '' }}>Catéter</option>
            </select>
        </div>

        <div class="form-group">
            <label for="catheter_type">Tipo de Catéter:</label>
            <select name="catheter_type" id="catheter_type" class="form-control">
                <option value="">Seleccionar</option> <!-- Opción neutra -->
                <option value="tunneling" {{isset($dialysisMonitoring) && $dialysisMonitoring->catheter_type == 'tunneling' ? 'selected' : '' }}>Tunelizado</option>
                <option value="no_tunneling"{{isset($dialysisMonitoring) && $dialysisMonitoring->catheter_type == 'no_tunneling' ? 'selected' : '' }}>No Tunelizado</option>
            </select>
        </div>

        <div class="form-group">
            <label for="implantation">Implantación:</label>
            <select name="implantation" id="implantation" class="form-control">
                <option value="">Seleccionar</option> <!-- Opción neutra -->
                <option value="femoral" {{ isset($dialysisMonitoring) && $dialysisMonitoring->implantation == 'femoral' ? 'selected' : '' }}>Femoral</option>
                <option value="yugular" {{ isset($dialysisMonitoring) && $dialysisMonitoring->implantation == 'yugular' ? 'selected' : '' }}>Yugular</option>
                <option value="subclavia" {{ isset($dialysisMonitoring) && $dialysisMonitoring->implantation == 'subclavia' ? 'selected' : '' }}>Subclavia</option>
                <option value="brazo" {{ isset($dialysisMonitoring) && $dialysisMonitoring->implantation == 'brazo' ? 'selected' : '' }}>Brazo</option>
                <option value="antebrazo" {{ isset($dialysisMonitoring) && $dialysisMonitoring->implantation == 'antebrazo' ? 'selected' : '' }}>Antebrazo</option>
            </select>
        </div>

        <div class="form-group">
            <label for="needle_mesure">Medida de Aguja:</label>
            <input type="text" name="needle_mesure" id="needle_mesure" class="form-control" value="{{ $dialysisMonitoring->needle_mesure ?? '' }}">
        </div>

        <div class="form-group">
            <label for="side">Lado:</label>
            <div class="form-check">
            <input type="radio" name="side" id="side_izquierda" value="left" class="form-check-input" {{ isset($dialysisMonitoring) && $dialysisMonitoring->side == 'left' ? 'checked' : '' }}>
            <label for="side_izquierda" class="form-check-label">Izquierda</label>
            </div>
            <div class="form-check">
            <input type="radio" name="side" id="side_derecha" value="right" class="form-check-input" {{ isset($dialysisMonitoring) && $dialysisMonitoring->side == 'right' ? 'checked' : '' }}>
            <label for="side_derecha" class="form-check-label">Derecha</label>
            </div>
        </div>
        </div>
    </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="collocation_date">Fecha de Colocación:</label>
            <input type="date" name="collocation_date" id="collocation_date" class="form-control" value="{{ $dialysisMonitoring->collocation_date ?? '' }}">
        </div>

        <div class="form-group">
            <label for="serology">Serología:</label>
            <div class="form-check">
            <input type="radio" name="serology" id="serology_positivo" value="positivo" class="form-check-input" {{ isset($dialysisMonitoring) && $dialysisMonitoring->serology == 'positivo' ? 'checked' : '' }}>
            <label for="serology_positivo" class="form-check-label">Positivo</label>
            </div>
            <div class="form-check">
            <input type="radio" name="serology" id="serology_negativo" value="negativo" class="form-check-input" {{ isset($dialysisMonitoring) && $dialysisMonitoring->serology == 'negativo' ? 'checked' : '' }}>
            <label for="serology_negativo" class="form-check-label">Negativo</label>
            </div>
        </div>

        <div class="form-group">
            <label for="serology_date">Fecha de Serología:</label>
            <input type="date" name="serology_date" id="serology_date" class="form-control" value="{{ isset($dialysisMonitoring) ? $dialysisMonitoring->serology_date : '' }}" >
        </div>

        <div class="form-group">
            <label for="blood_type">Tipo de Sangre:</label>
            <select name="blood_type" id="blood_type" class="form-control">
            <option value="">Seleccionar</option>
            <?php
            $bloodTypes = [
            ["group" => "A", "rh_factor" => "Positivo"],
            ["group" => "A", "rh_factor" => "Negativo"],
            ["group" => "B", "rh_factor" => "Positivo"],
            ["group" => "B", "rh_factor" => "Negativo"],
            ["group" => "AB", "rh_factor" => "Positivo"],
            ["group" => "AB", "rh_factor" => "Negativo"],
            ["group" => "O", "rh_factor" => "Positivo"],
            ["group" => "O", "rh_factor" => "Negativo"]
            ];
            foreach ($bloodTypes as $bloodType) {
            $selected = (isset($dialysisMonitoring) && $dialysisMonitoring->blood_type == $bloodType['group'] . " " . $bloodType['rh_factor']) ? 'selected' : '';
            echo "<option value='" . $bloodType['group'] ." ". $bloodType['rh_factor'] . "' $selected>" . $bloodType['group'] ." ". $bloodType['rh_factor'] . "</option>";
            }
            ?>
            </select>
        </div>

        <div class="form-group">
            <label for="allergy">Alergia:</label>
            <input type="text" name="allergy" id="allergy" class="form-control" value="{{ $dialysisMonitoring->allergy ?? '' }}">
        </div>

        <div class="form-group">
            <label for="diagnostic">Diagnóstico:</label>
            <input type="text" name="diagnostic" id="diagnostic" class="form-control" value="{{ $dialysisMonitoring->diagnostic ?? '' }}">
        </div>
        <button type="submit" class="btn btn-primary">Continuar y Guardar</button>
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
        </form>
@endsection
