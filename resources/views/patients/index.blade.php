@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Listado de Pacientes</h2>
        <div class="row">
        <div class="col-md-6">
        <a href="{{ route('patients.create') }}" class="btn btn-primary mb-3">Registrar Paciente</a>
                <form action="{{ route('patients.search') }}" method="GET" class="mb-3">
                    <div class="input-group mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por clave o por nombre">
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
                        <td><img src="{{$patient->photo ? asset($patient->photo):asset('default/no-photo-m.png')}}" alt="Foto Paciente"></td>
                        <td>{{ $patient->name . ' ' . $patient->last_name . ' ' . $patient->last_name_two }}</td>
                        <td>{{ $patient->gender}}</td>
                        <td>{{ $patient->birth_date->format('d-m-Y')}}</td>
                        <td>{{ $patient->date_entry->format('d-m-Y')}}</td>
                        <td>
                            <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-info">Ver</a>
                            <a href="{{ route('patients.show.photo', $patient->id) }}" class="btn btn-success">Agregar Foto</a>
                            @if (auth()->user()->position == 'ROOT' || auth()->user()->position == 'DIRECTIVE')
                            <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-primary">Editar</a>
                            <form action="{{ route('patients.destroy', $patient->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este Paciente?')">Eliminar</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-end">
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-end">
            {!!$patients->links()!!}
            </ul>
        </nav>
    </div>
        </div>
    </div>
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@endsection
