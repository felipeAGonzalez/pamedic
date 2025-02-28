@extends('layouts.app')

@section('content')

<h1>Editar Nota de Enfermería</h1>
<div class="container">
        @if ($errors->any())
                <div class="alert2 alert2-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ __($error) }}<br></li>
                        @endforeach
                        </ul>
                    </div>
            @endif
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
        <div class="row">
            <div class="col-md-6">
                <form action="{{ route('edit.search') }}" method="GET" class="mb-3">
                    <div class="input-group mb-6">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o numero de expediente">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
        <table class="table mt-4">
         <thead class="table-dark">
                <tr>
                    <th scope="col">Número de expediente</th>
                    <th scope="col">Foto</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Género</th>
                    <th scope="col">Fecha de nacimiento</th>
                    <th scope="col">Fecha de ultima sesión</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activePatients as $activePatient)
                    <tr>
                        <td>{{ $activePatient->patient->expedient_number}}</td>
                        <td>
                            <div style="width: 200px; height: 200px; overflow: hidden;">
                                <img src="{{$activePatient->patient->photo ? asset($activePatient->patient->photo) : asset('default/no-photo-m.png')}}" alt="Foto Paciente" style="width: auto; height: auto; object-fit: contain;">
                            </div>
                        </td>
                        <td>{{ $activePatient->patient->name . ' ' . $activePatient->patient->last_name . ' ' . $activePatient->patient->last_name_two }}</td>
                        <td>{{ $activePatient->patient->gender}}</td>
                        <td>{{ $activePatient->patient->birth_date ? $activePatient->patient->birth_date->format('d-m-Y') : 'Sin fecha de nacimiento' }}</td>
                        <td>{{ $activePatient->date ? \Carbon\Carbon::parse($activePatient->date)->format('d-m-Y') : 'Sin fecha de entrada' }}</td>
                        <td>
                        <div >
                            <div >
                                    <a href="{{ route('edit.create', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-info">Pre-dialisis</a>
                                    <a href="{{ route('edit.createPres', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-primary">Prescripción</a>
                                    <a href="{{ route('edit.createPreHemo', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-success">Pre-Hemodiálisis</a>
                                    <a href="{{ route('edit.createTransHemo', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-danger">Trans-Hemodialisis</a>
                                    <a href="{{ route('edit.createEvaluation', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-secondary">Evaluación</a>
                                    <a href="{{ route('edit.createEvaluationNurse', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-secondary">Valoración de enfermería</a>
                                    <a href="{{ route('edit.createMedicineAdmin', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-info">Ministración de medicamentos</a>
                                    <a href="{{ route('edit.createPostHemo', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-warning">Post-Hemodialisis</a>
                                    <a href="{{ route('treatment.createOxygen', ['id' => $activePatient->id]) }}" class="btn btn-danger">Oxigeno Terapia</a>
                                </div>
                        </div>
                        </td>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-end">
        </div>

@endsection
