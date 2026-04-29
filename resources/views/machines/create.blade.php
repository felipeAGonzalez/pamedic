@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Agregar máquina</h2>

    <form method="POST" action="{{ route('machines.store') }}">
        @csrf

        <div class="mb-3">
            <label>Número de máquina</label>
            <input type="number" name="machine_number" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Número de serie</label>
            <input type="text" name="serial_number" class="form-control" required>
        </div>

        <button class="btn btn-success">Guardar</button>
        <a href="{{ route('machines.index') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
@endsection
