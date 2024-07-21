@extends('layouts.app')

@section('content')
<form action="{{ route('patients.photo', $patient->id)}}" enctype="multipart/form-data" method="POST">
    <div class="container">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Agregar Foto al Paciente</h2>
                </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <td><img src="{{$patient->photo ? asset($patient->photo):asset('default/no-photo-m.png')}}" alt="Imagen de Ejemplo" class="img-fluid"></td>
                    </div>
                    <div class="col-md-8">
                        <h4>{{ $patient->name }} {{ $patient->last_name }} {{ $patient->last_name_two }}</h4>
                        <p><strong>Email:</strong> {{ $patient->email }}</p>
                        <p><strong>Fecha de Nacimiento:</strong> {{ $patient->birth_date->format('d/m/Y') }}</p>
                        <p><strong>Edad:</strong> {{ $patient->birth_date->age }}</p>
                        <p><strong>Teléfono:</strong> {{ $patient->phone_number }}</p>
                        @csrf
                        @method('PATCH')
                        <label for="photo" class="form-label">Selecciona una imagen:</label>
                        <input type="file" class="form-control" id="photo" name="photo">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
            <div>
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('patients.index') }}" class="btn btn-info">Volver</a>
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
    </div>
@endsection
