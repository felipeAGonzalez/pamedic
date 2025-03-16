@extends('layouts.app')

@section('content')

<h1>Crear nota medica</h1>
<h3>Se muestran por defecto los pacientes atendidos el día de hoy</h3>
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
                <form action="{{ route('noteMedic.search') }}" method="GET" class="mb-3">
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
                    <th scope="col">Nota creada</th>
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
                        @php
                            $note = $activePatient->patient->medicNote;
                            if($note){
                                $note = $note->where('date', $activePatient->date)->first();
                            }
                        @endphp
                        <td>
                        <div >
                            <a href="{{ route('print.note', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-info" target="_blank">{{ $note ? 'Editar Nota' : 'Crear Nota' }}</a>
                        </div>
                        </td>
                        <td>
                            @if($note)
                                <a href="{{ route('print.noteMedicDate', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-success" target="_blank">
                                    <i class="fas fa-check"></i>
                                </a>
                            @else
                                <a href="#" class="btn btn-danger">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </td>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-end">
        </div>

@endsection
