@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @if(isset($medicineAdministration) && !empty($medicineAdministration))
        <h3><strong>Medicina aplicada a paciente:</strong> {{ $patient->name.' '.$patient->last_name." ".$patient->last_name_two }}</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><strong>Medicamento</strong></th>
                        <th><strong>Dilución</strong></th>
                        <th><strong>Fecha de caducidad</strong></th>
                        <th><strong>Hora</strong></th>
                        <th><strong>Enfermero que Administró</strong></th>
                        <th><strong>Velocidad</strong></th>
                        <th><strong>Acción</strong></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medicineAdministration as $administration)
                    <tr>
                        <td>{{ $administration->medicine->name }}</td>
                        <td>{{ $administration['dilution'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($administration['due_date'])->format('m-Y') }}</td>
                        <td>{{ $administration['hour'] }}</td>
                        <td>{{ $administration->nurse_admin->name }}</td>
                        <td>{{ $administration['velocity'] }}</td>
                        <td>
                            <form action="{{ route('edit.destroy', $administration->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este medicamento?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
 <form  action="{{ route('edit.fillMedicineAdmin') }}" method="POST" class="row">
  @csrf
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Dilución</th>
                            <th>Fecha de caducidad</th>
                            <th>Hora</th>
                            <th>Enfermero que Preparó</th>
                            <th>Enfermero que Administró</th>
                            <th>Velocidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <input type="hidden" class="form-control" id="patient_id" name="patient_id" value="{{ $id ?? $patient->id }}">
                        <tr>
                            <td>
                                <select class="form-control" id="medicine_id" name="medicine_id">
                                    <option value="" selected disabled>Seleccione un medicamento</option>
                                    @foreach($medicines as $medicine)
                                    <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="form-control" id="dilution" name="dilution">
                                    <option value="" selected disabled>Seleccione un volumen</option>
                                    <option value="1ml">1ml</option>
                                    <option value="2ml">2ml</option>
                                    <option value="5ml">5ml</option>
                                    <option value="10ml">10ml</option>
                                    <option value="20ml">20ml</option>
                                    <option value="50ml">50ml</option>
                                </select>
                            </td>
                            <td><input type="month" class="form-control" id="due_date" name="due_date"></td>
                            <td><input type="time" class="form-control" id="hour" name="hour"></td>
                            <td>
                                <select class="form-control" id="nurse_prepare_id" name="nurse_prepare_id">
                                    <option value="" selected disabled>Seleccione un Enfermero</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="form-control" id="nurse_admin_id" name="nurse_admin_id">
                                    <option value="" selected disabled>Seleccione un Enfermero</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="form-control" id="velocity" name="velocity">
                                    <option value="" selected disabled>Seleccione un tiempo</option>
                                    <option value="bolo">Bolo</option>
                                    <option value="15min">15 min</option>
                                    <option value="30min">30 min</option>
                                    <option value="60min">60 min</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


    <div id="controlledMedicineSection">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Medicamento correcto</th>
                        <th>Dosis correcta</th>
                        <th>Dilución correcta</th>
                        <th>Hora Correcta</th>
                        <th>Verificación de caducidad</th>
                        <th>Registro de medicamentos</th>
                        <th>Educación del paciente</th>
                        <th>Identificación del medicamento</th>
                        <th>Enfermero</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="checkbox" name="correct_medication" value="1" {{ isset($doubleVerification['correct_medication']) && $doubleVerification['correct_medication'] == 1 ? 'checked' : '' }}>
                        </td>
                        <td>
                            <input type="checkbox" name="correct_dosage" value="1" {{ isset($doubleVerification['correct_dosage']) && $doubleVerification['correct_dosage'] == 1 ? 'checked' : '' }}>
                        </td>
                        <td>
                            <input type="checkbox" name="correct_dilution" value="1" {{ isset($doubleVerification['correct_dilution']) && $doubleVerification['correct_dilution'] == 1 ? 'checked' : '' }}>
                        </td>
                        <td>
                            <input type="checkbox" name="correct_time" value="1" {{ isset($doubleVerification['correct_time']) && $doubleVerification['correct_time'] == 1 ? 'checked' : '' }}>
                        </td>
                        <td>
                            <input type="checkbox" name="expiration_verification" value="1" {{ isset($doubleVerification['expiration_verification']) && $doubleVerification['expiration_verification'] == 1 ? 'checked' : '' }}>
                        </td>
                        <td>
                            <input type="checkbox" name="medication_record" value="1" {{ isset($doubleVerification['medication_record']) && $doubleVerification['medication_record'] == 1 ? 'checked' : '' }}>
                        </td>
                        <td>
                            <input type="checkbox" name="patient_education" value="1" {{ isset($doubleVerification['patient_education']) && $doubleVerification['patient_education'] == 1 ? 'checked' : '' }}>
                        </td>
                        <td>
                            <input type="checkbox" name="medication_identification" value="1" {{ isset($doubleVerification['medication_identification']) && $doubleVerification['medication_identification'] == 1 ? 'checked' : '' }}>
                        </td>
                        <td>
                            <select class="form-control" id="nurse_id" name="nurse_id">
                                <option value="" selected disabled>Seleccione un Enfermero</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ isset($doubleVerification['nurse_id']) && $doubleVerification['nurse_id'] == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    </div>
    <div class="col-md-6">
        @if (request()->routeIs('edit.createMedicineAdmin'))
            <button type="submit" class="btn btn-primary">Guardar</button>
        @endif
        <a href="{{ route('edit.index') }}" class="btn btn-info">Volver</a>
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
