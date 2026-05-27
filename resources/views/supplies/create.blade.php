@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Agregar Insumo</h2>

    <form method="POST" action="{{ route('supplies.store') }}">
        @csrf

        <div class="mb-3">
            <label>Material</label>
            <input type="text" name="material" value="{{ old('material') }}"
                   class="form-control @error('material') is-invalid @enderror" required>
            @error('material')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Existencias</label>
            <input type="number" name="existencias" value="{{ old('existencias', 0) }}"
                   class="form-control @error('existencias') is-invalid @enderror" min="0" required>
            @error('existencias')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-success">Guardar</button>
        <a href="{{ route('supplies.index') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
@endsection
