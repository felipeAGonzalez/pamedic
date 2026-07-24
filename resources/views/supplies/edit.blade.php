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
            <label>Tipo</label>
            <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                <option value="">-- Seleccionar --</option>
                @foreach(\App\Models\Supply::TYPES as $value => $label)
                    <option value="{{ $value }}" {{ old('type', $supply->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Acceso Vascular</label>
            <select name="for_vascular_access" class="form-control @error('for_vascular_access') is-invalid @enderror" required>
                <option value="">-- Seleccionar --</option>
                <option value="catheter"  {{ old('for_vascular_access', $supply->for_vascular_access) == 'catheter'  ? 'selected' : '' }}>Catéter</option>
                <option value="fistula"   {{ old('for_vascular_access', $supply->for_vascular_access) == 'fistula'   ? 'selected' : '' }}>Fístula</option>
                <option value="both"      {{ old('for_vascular_access', $supply->for_vascular_access) == 'both'      ? 'selected' : '' }}>Ambos</option>
                <option value="no_apply"  {{ old('for_vascular_access', $supply->for_vascular_access) == 'no_apply'  ? 'selected' : '' }}>No Aplica</option>
            </select>
            @error('for_vascular_access')
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
