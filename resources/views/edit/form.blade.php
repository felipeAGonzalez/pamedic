@extends('layouts.app')

@section('content')
<h1>Monitoreo Pre-Trans y Post Diálisis </h1>
<h3 style="color: red;">{{ $patient->name .' '. $patient->last_name }}</h3>


<form action="{{ route('edit.fill') }}" method="POST" class="row">
    @csrf
    <input type="hidden" name="patient_id" value="{{ $id ?? $dialysisMonitoring->patient_id}}">
    <input type="hidden" name="created_at" value="{{ $id ?? $dialysisMonitoring->created_at}}">
    <div class="col-md-6">
        <div class="form-group">
            <label for="date_hour">Fecha y Hora:</label>
            <input type="datetime-local" name="date_hour" id="date_hour" class="form-control"  value="{{ $dialysisMonitoring->date_hour ?? '' }}">
        </div>
        <div class="form-group">
            <label for="machine_number">Número de Máquina:</label>
            <input type="text" name="machine_number" id="machine_number" class="form-control" value="{{ $dialysisMonitoring->machine_number ?? '' }}">
        </div>

        <div class="form-group">
            <label for="session_number">Número de Sesión:</label>
            <input type="text" name="session_number" id="session_number" class="form-control" value="{{ $dialysisMonitoring->session_number ?? '' }}">
        </div>

        <div class="form-group">
            <label for="vascular_access">Acceso Vascular:</label>
            <select name="vascular_access" id="vascular_access" class="form-control" onchange="toggleFields()">
            <option value="">Seleccionar</option> <!-- Opción neutra -->
            <option value="fistula" {{ isset($dialysisMonitoring) && $dialysisMonitoring->vascular_access == 'fistula' ? 'selected' : '' }}>Fistula</option>
            <option value="catheter" {{ isset($dialysisMonitoring) && $dialysisMonitoring->vascular_access == 'catheter' ? 'selected' : '' }}>Catéter</option>
            </select>
        </div>

        <div class="form-group" id="catheter_type_group" style="display: none;">
            <label for="catheter_type">Tipo de Catéter:</label>
            <select name="catheter_type" id="catheter_type" class="form-control">
            <option value="">Seleccionar</option> <!-- Opción neutra -->
            <option value="tunneling" {{isset($dialysisMonitoring) && $dialysisMonitoring->catheter_type == 'tunneling' ? 'selected' : '' }}>Tunelizado</option>
            <option value="no_tunneling"{{isset($dialysisMonitoring) && $dialysisMonitoring->catheter_type == 'no_tunneling' ? 'selected' : '' }}>No Tunelizado</option>
            </select>
        </div>

        <div class="form-group" id="needle_mesure_group" style="display: none;">
            <label for="needle_mesure">Medida de Aguja:</label>
            <select name="needle_mesure" id="needle_mesure" class="form-control">
                <option value="">Seleccionar</option>
                <option value="15" {{ isset($dialysisMonitoring) && $dialysisMonitoring->needle_mesure == '15' ? 'selected' : '' }}>15</option>
                <option value="16" {{ isset($dialysisMonitoring) && $dialysisMonitoring->needle_mesure == '16' ? 'selected' : '' }}>16</option>
                <option value="17" {{ isset($dialysisMonitoring) && $dialysisMonitoring->needle_mesure == '17' ? 'selected' : '' }}>17</option>
            </select>
        </div>

        <script>
            function toggleFields() {
            var vascularAccess = document.getElementById('vascular_access').value;
            var catheterTypeGroup = document.getElementById('catheter_type_group');
            var needleMesureGroup = document.getElementById('needle_mesure_group');

            if (vascularAccess === 'catheter') {
                catheterTypeGroup.style.display = 'block';
                needleMesureGroup.style.display = 'none';
            } else if (vascularAccess === 'fistula') {
                catheterTypeGroup.style.display = 'none';
                needleMesureGroup.style.display = 'block';
            } else {
                catheterTypeGroup.style.display = 'none';
                needleMesureGroup.style.display = 'none';
            }
            }

            document.addEventListener('DOMContentLoaded', function() {
            toggleFields();
            });
        </script>
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
    <select name="diagnostic[]" id="diagnostic" class="form-control" multiple>
        <option value="">Seleccionar</option>
        <option value="B15 - Hepatitis aguda tipo A" {{ isset($diagnostic) && in_array('B15 - Hepatitis aguda tipo A', $diagnostic) ? 'selected' : '' }}>B15 - Hepatitis aguda tipo A</option>
        <option value="B16 - Hepatitis aguda tipo B" {{ isset($diagnostic) && in_array('B16 - Hepatitis aguda tipo B', $diagnostic) ? 'selected' : '' }}>B16 - Hepatitis aguda tipo B</option>
        <option value="B18 - Hepatitis viral crónica" {{ isset($diagnostic) && in_array('B18 - Hepatitis viral crónica', $diagnostic) ? 'selected' : '' }}>B18 - Hepatitis viral crónica</option>
        <option value="B24 - Enfermedad por virus de la inmunodeficiencia humana [VIH], sin otra especificación" {{ isset($diagnostic) && in_array('B24 - Enfermedad por virus de la inmunodeficiencia humana [VIH], sin otra especificación', $diagnostic) ? 'selected' : '' }}>B24 - Enfermedad por virus de la inmunodeficiencia humana [VIH], sin otra especificación</option>
        <option value="E10 - Diabetes mellitus insulinodependiente" {{ isset($diagnostic) && in_array('E10 - Diabetes mellitus insulinodependiente', $diagnostic) ? 'selected' : '' }}>E10 - Diabetes mellitus insulinodependiente</option>
        <option value="E11 - Diabetes mellitus no insulinodependiente" {{ isset($diagnostic) && in_array('E11 - Diabetes mellitus no insulinodependiente', $diagnostic) ? 'selected' : '' }}>E11 - Diabetes mellitus no insulinodependiente</option>
        <option value="E12 - Diabetes mellitus asociada con desnutrición" {{ isset($diagnostic) && in_array('E12 - Diabetes mellitus asociada con desnutrición', $diagnostic) ? 'selected' : '' }}>E12 - Diabetes mellitus asociada con desnutrición</option>
        <option value="E13 - Otras diabetes mellitus especificadas" {{ isset($diagnostic) && in_array('E13 - Otras diabetes mellitus especificadas', $diagnostic) ? 'selected' : '' }}>E13 - Otras diabetes mellitus especificadas</option>
        <option value="E14 - Diabetes mellitus, no especificada" {{ isset($diagnostic) && in_array('E14 - Diabetes mellitus, no especificada', $diagnostic) ? 'selected' : '' }}>E14 - Diabetes mellitus, no especificada</option>
        <option value="E66 - Obesidad" {{ isset($diagnostic) && in_array('E66 - Obesidad', $diagnostic) ? 'selected' : '' }}>E66 - Obesidad</option>
        <option value="I10 - Hipertensión esencial (primaria)" {{ isset($diagnostic) && in_array('I10 - Hipertensión esencial (primaria)', $diagnostic) ? 'selected' : '' }}>I10 - Hipertensión esencial (primaria)</option>
        <option value="I11 - Enfermedad cardíaca hipertensiva" {{ isset($diagnostic) && in_array('I11 - Enfermedad cardíaca hipertensiva', $diagnostic) ? 'selected' : '' }}>I11 - Enfermedad cardíaca hipertensiva</option>
        <option value="I12 - Enfermedad renal hipertensiva" {{ isset($diagnostic) && in_array('I12 - Enfermedad renal hipertensiva', $diagnostic) ? 'selected' : '' }}>I12 - Enfermedad renal hipertensiva</option>
        <option value="I13 - Enfermedad cardiorrenal hipertensiva" {{ isset($diagnostic) && in_array('I13 - Enfermedad cardiorrenal hipertensiva', $diagnostic) ? 'selected' : '' }}>I13 - Enfermedad cardiorrenal hipertensiva</option>
        <option value="I15 - Hipertensión secundaria" {{ isset($diagnostic) && in_array('I15 - Hipertensión secundaria', $diagnostic) ? 'selected' : '' }}>I15 - Hipertensión secundaria</option>
        <option value="I95 - Hipotensión" {{ isset($diagnostic) && in_array('I95 - Hipotensión', $diagnostic) ? 'selected' : '' }}>I95 - Hipotensión</option>
        <option value="N17 - Insuficiencia renal aguda" {{ isset($diagnostic) && in_array('N17 - Insuficiencia renal aguda', $diagnostic) ? 'selected' : '' }}>N17 - Insuficiencia renal aguda</option>
        <option value="N18 - Insuficiencia renal crónica" {{ isset($diagnostic) && in_array('N18 - Insuficiencia renal crónica', $diagnostic) ? 'selected' : '' }}>N18 - Insuficiencia renal crónica</option>
        <option value="N19 - Insuficiencia renal no especificada" {{ isset($diagnostic) && in_array('N19 - Insuficiencia renal no especificada', $diagnostic) ? 'selected' : '' }}>N19 - Insuficiencia renal no especificada</option>
        <option value="Q60.3 - Hipoplasia renal, unilateral" {{ isset($diagnostic) && in_array('Q60.3 - Hipoplasia renal, unilateral', $diagnostic) ? 'selected' : '' }}>Q60.3 - Hipoplasia renal, unilateral</option>
        <option value="Q60.4 - Hipoplasia renal, bilateral" {{ isset($diagnostic) && in_array('Q60.4 - Hipoplasia renal, bilateral', $diagnostic) ? 'selected' : '' }}>Q60.4 - Hipoplasia renal, bilateral</option>
        <option value="Q60.5 - Hipoplasia renal, no especificada" {{ isset($diagnostic) && in_array('Q60.5 - Hipoplasia renal, no especificada', $diagnostic) ? 'selected' : '' }}>Q60.5 - Hipoplasia renal, no especificada</option>
    </select>
</div>
        <button type="submit" class="btn btn-primary">Continuar y Guardar</button>
        <a href="{{ route('edit.index') }}" class="btn btn-info">Volver</a>
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
