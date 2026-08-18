@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">Insumos</h2>
    @if(in_array($user->position, ['QUALITY', 'MANAGER', 'ROOT']))
    <a href="{{ route('supplies.create') }}" class="btn btn-primary mb-3">Agregar Insumo</a>
    <a href="{{ route('supplies.calculate') }}" class="btn btn-secondary mb-3">Calcular Insumos</a>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Material</th>
                <th>Tipo</th>
                <th>Acceso Vascular</th>
                <th>Existencias</th>
                @if(in_array($user->position, ['QUALITY', 'MANAGER', 'ROOT']))
                <th width="150">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($supplies as $supply)
                <tr>
                    <td>{{ $supply->material }}</td>
                    <td>{{ $supply->type }}</td>
                    <td>
                        @if($supply->for_vascular_access == 'catheter') Catéter
                        @elseif($supply->for_vascular_access == 'fistula') Fístula
                        @elseif($supply->for_vascular_access == 'both') Ambos
                        @else No Aplica
                        @endif
                    </td>
                    <td>{{ $supply->existencias }}</td>
                    @if(in_array($user->position, ['QUALITY', 'MANAGER', 'ROOT']))
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
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No hay insumos registrados</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-end">
            {!! $supplies->links() !!}
        </ul>
    </nav>

</div>
@endsection
