@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Asistencia</h1>

        <div class="row">
            <div class="col-md-6">
                <form action="{{ route('attendance.search') }}" method="GET" class="mb-3">
                    <div class="input-group mb-6">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o numero de expediente">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>
            </div>
        </div>

        <table class="table">
             <thead class="table-dark">
                <tr>
                    <th>Foto</th>
                    <th>Numero de expediente</th>
                    <th>Nombre</th>
                    <th>Fecha de nacimiento</th>
                    <th>Genero</th>
                    @if($patients)
                        <th>Acci¨®n</th>
                    @endif
                    
                </tr>
            </thead>
            <tbody>
                @if(! $patients)
                <tr>
                    <td>
                        <img src="{{ asset('default/no-photo-m.png') }}" alt="Foto" class="figure-img img-fluid rounded" style="max-width: 100px;">
                    </td>
                    <td>{{ '' }}</td>
                    <td>{{ '' }}</td>
                    <td>{{ '' }}</td>
                    <td>{{ '' }}</td>
                </tr>
                @else
                @foreach($patients as $patient)
                    <tr>
                        <td>
                            <img src="{{ $patient ? ($patient->photo ? asset($patient->photo) : asset('default/no-photo-m.png')) : '' }}" alt="Foto" class="figure-img img-fluid rounded" style="max-width: 100px;">
                        </td>
                        <td>{{ $patient->expedient_number}}</td>
                        <td>{{ $patient->name . ' ' . $patient->last_name . ' ' . $patient->last_name_two}}</td>
                        <td>{{ date('d/m/Y', strtotime($patient->birth_date))}}</td>
                        <td>{{ $patient->gender}}</td>
                        <td>
                            <form action="{{ route('attendance.register', $patient->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary">Registrar Asistencia</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @endif
            </tbody>
            </tbody>
        </table>
            <div class="input-group">
                <div class="input-group-append">
                    <a href="{{ route('attendance.index') }}" class="btn btn-info">Limpiar</a>
                </div>
            </div>
    </div>
    @if(Session::has('success'))
    <div class="alert2 alert2-success">
        <ul>
            <li>{!! Session::get('success') !!}<br></li>
        </ul>
    </div>
    @endif
    @if(Session::has('message'))
    <div class="alert2 alert-success">
        <ul>
            <li style="color: green;">{!! Session::get('message') !!}<br></li>
        </ul>
    </div>
    @endif
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
