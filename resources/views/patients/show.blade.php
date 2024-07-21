@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>información del Paciente</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <td><img src="{{$patient->photo ? asset($patient->photo):asset('default/no-photo-m.png')}}" alt="Imagen de Ejemplo" class="img-fluid"></td>
                    </div>
                    <div class="col-md-8">
                        <h4><strong>{{ $patient->name }} {{ $patient->last_name }} {{ $patient->last_name_two }}</strong></h4>
                        <p><strong>Fecha de Nacimiento:</strong> {{ $patient->birth_date->format('d/m/Y') }}</p>
                        <p><strong>Edad:</strong> {{ $patient->birth_date->age }}</p>
                        <p><strong>Fecha de Ingreso:</strong> {{ $patient->date_entry->format('d/m/Y') }}</p>
                        <p><strong>Fecha de Antigüedad:</strong> {{ $patient->date_entry->age }}</p>
                        <p><strong>Teléfono de contacto:</strong> {{ $patient->contact_phone_number }}</p>
                    </div>
                </div>
            </div>
        </div>
</div>
    <a href="{{ route('patients.index') }}" class="btn btn-secondary mt-3">Volver</a>
@endsection
