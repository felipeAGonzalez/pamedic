@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">Máquinas de Hemodiálisis</h2>

    <a href="{{ route('machines.create') }}" class="btn btn-primary mb-3">
        Agregar Máquina
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Número de Máquina</th>
                <th>Número de Serie</th>
                <th width="150">Acciones</th>
            </tr>
        </thead>

        <tbody>
        @foreach($machines as $machine)
            <tr>
                <td>{{ $machine->id }}</td>
                <td>{{ $machine->machine_number }}</td>
                <td>{{ $machine->serial_number }}</td>
                <td>
                    <a href="{{ route('machines.edit',$machine) }}" class="btn btn-warning btn-sm">Editar</a>

                    <form action="{{ route('machines.destroy',$machine) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('¿Eliminar máquina?')" class="btn btn-danger btn-sm">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
@endsection
