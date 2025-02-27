@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ isset($patient) ? 'Editar Paciente' : 'Dar de Alta Paciente' }}</h2>
        <form action="{{ isset($patient) ? route('patients.update', $patient->id) : route('patients.store') }}" enctype="multipart/form-data" method="POST">
            @csrf
            @if (isset($patient))
                @method('PUT')
            @endif
            <div class="container">
        <div class="row">
                <div class = "col-md-6">
                    @if (isset($patient))
                        <div class="form-group">
                            <label for="expedient_number">Número de Expediente:</label>
                            <input type="text" class="form-control" id="expedient_number" name="expedient_number" value="{{ $patient->expedient_number }}">
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ isset($patient) ? $patient->name : old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Primer Apellido:</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="{{ isset($patient) ? $patient->last_name : old('last_name') }}">
                    </div>
                    <div class="form-group">
                        <label for="last_name_two">Segundo Apellido:</label>
                        <input type="text" class="form-control" id="last_name_two" name="last_name_two" value="{{ isset($patient) ? $patient->last_name_two : old('last_name_two') }}">
                    </div>
                    <div class="form-group">
                        <label for="gender">Género:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="male" value="M" {{ isset($patient) && $patient->gender == 'male' ? 'checked' : '' }}>
                            <label class="form-check-label" for="male">
                                Masculino
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="female" value="F" {{ isset($patient) && $patient->gender == 'female' ? 'checked' : '' }}>
                            <label class="form-check-label" for="female">
                                Femenino
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="birth_date">Fecha de Nacimiento:</label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ isset($patient) && $patient->birth_date ? $patient->birth_date->format('Y-m-d') : old('birth_date') }}">
                    </div>
                    <div class="form-group">
                        <label for="height">Talla (en metros):</label>
                        <input type="number" step="0.01" class="form-control" id="height" name="height" value="{{ isset($patient) ? $patient->height : old('height') }}">
                    </div>
                </div>
                <div class="col-md-6">
                <div class="form-group">
                        <label for="contact_phone_number">Teléfono de contacto:</label>
                        <input type="tel" class="form-control" id="contact_phone_number" name="contact_phone_number" pattern="[0-9]{3}[0-9]{3}[0-9]{4}" value="{{ isset($patient) ? $patient->contact_phone_number : old('contact_phone_number') }}">
                    </div>
                    <div class="form-group">
                        <label for="date_entry">Fecha de Ingreso:</label>
                        <input type="date" class="form-control" id="date_entry" name="date_entry" value="{{ isset($patient) && $patient->date_entry ? $patient->date_entry->format('Y-m-d') : '' }}">
                    </div>
                    @if(isset($patient))
                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto Actual:</label><br>
                        <img src="{{$patient->photo ? asset($patient->photo):asset('default/no-photo-m.png')}}" alt="Foto actual">
                    </div>
                    @endif
                    <div class="mb-3">
                        <label for="photo" class="form-label">Selecciona una imagen:</label>
                        <input type="file" class="form-control" id="photo" name="photo">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ isset($patient) ? 'Actualizar' : 'Guardar' }}</button>
        <a href="{{ route('patients.index') }}" class="btn btn-info">Volver</a>
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
    </div>
@endsection
