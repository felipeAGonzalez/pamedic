@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Máquina</h2>

    <form method="POST" action="{{ route('machines.update',$machine) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Número de Máquina</label>
            <input type="text" name="machine_number"
                   value="{{ $machine->machine_number }}"
                   class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Número de Serie</label>
            <input type="text" name="serial_number"
                   value="{{ $machine->serial_number }}"
                   class="form-control" required>
        </div>

        <button class="btn btn-success">Actualizar</button>
        <a href="{{ route('machines.index') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
@endsection
