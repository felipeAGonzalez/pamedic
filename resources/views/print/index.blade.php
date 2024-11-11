@extends('layouts.app')

@section('content')

<h1>Imprimir Hoja de Enfermería</h1>
<h3>Se muestran por defecto los pacientes atendidos el día de hoy</h3>
        <div class="row">
            <div class="col-md-6">
                <form action="{{ route('print.search') }}" method="GET" class="mb-3">
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
                    <th scope="col">Fecha de ingreso</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patients as $patient)
                    <tr>
                        <td>{{ $patient->expedient_number}}</td>
                        <td>
                            <div style="width: 200px; height: 200px; overflow: hidden;">
                                <img src="{{$patient->photo ? asset($patient->photo) : asset('default/no-photo-m.png')}}" alt="Foto Paciente" style="width: auto; height: auto; object-fit: contain;">
                            </div>
                        </td>
                        <td>{{ $patient->name . ' ' . $patient->last_name . ' ' . $patient->last_name_two }}</td>
                        <td>{{ $patient->gender}}</td>
                        <td>{{ $patient->birth_date->format('d-m-Y')}}</td>
                        <td>{{ $patient->date_entry->format('d-m-Y')}}</td>
                        <td>
                        <div >
                                    <a href="{{ route('print.printNurseExpedient', ['id' => $patient->id]) }}" class="btn btn-info">Imprimir Hoja</a>
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
@endsection
