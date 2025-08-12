@extends('layouts.app')

@section('content')

<h1>Imprimir Hoja de validación/Tiempo Fuera</h1>
<h3>Se muestran por defecto las sesiones del mes actual</h3>
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
                <form action="{{ route('print.search') }}" method="GET" class="mb-3">
                    <div class="input-group mt-12">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o numero de expediente">
                        <input type="date" name="dateInitial" class="form-control" placeholder="Fecha Inicial">
                        <input type="date" name="dateFinal" class="form-control" placeholder="Fecha Final">
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
                        <td>{{ \Carbon\Carbon::parse($activePatient->date)->format('d-m-Y')}}</td>
                        <td>
                        <div >
                            <a href="{{ route('print.printTimeOut', ['id' => $activePatient->patient->id, 'date' => $activePatient->date]) }}" class="btn btn-info" target="_blank">Imprimir Time Out</a>
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
