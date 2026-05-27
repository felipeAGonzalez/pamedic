@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">Insumos</h2>

    <a href="{{ route('supplies.create') }}" class="btn btn-primary mb-3">Agregar Insumo</a>
    <a href="{{ route('supplies.calculate') }}" class="btn btn-secondary mb-3">Calcular Insumos</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Material</th>
                <th>Existencias</th>
                <th width="150">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($supplies as $supply)
                <tr>
                    <td>{{ $supply->material }}</td>
                    <td>{{ $supply->existencias }}</td>
                    <td>
                        <a href="{{ route('supplies.edit', $supply) }}" class="btn btn-warning btn-sm">Editar</a>

                        <form action="{{ route('supplies.destroy', $supply) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('¿Eliminar insumo?')" class="btn btn-danger btn-sm">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No hay insumos registrados</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection
