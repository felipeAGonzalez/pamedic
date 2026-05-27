@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Insumo</h2>

    <form method="POST" action="{{ route('supplies.update', $supply) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Material</label>
            <input type="text" name="material" value="{{ old('material', $supply->material) }}"
                   class="form-control @error('material') is-invalid @enderror" required>
            @error('material')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Existencias</label>
            <input type="number" name="existencias" value="{{ old('existencias', $supply->existencias) }}"
                   class="form-control @error('existencias') is-invalid @enderror" min="0" required>
            @error('existencias')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-success">Actualizar</button>
        <a href="{{ route('supplies.index') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
@endsection
