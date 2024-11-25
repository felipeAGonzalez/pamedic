@extends('layouts.app')

@section('content')
    <h1>{{ isset($user) ? 'Editar Usuario' : 'Crear Usuario' }}</h1>
    <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST">
        @csrf
        @if(isset($user))
            @method('PUT')
        @endif
        <div class="col-md-6">
            <div class="form-group">
                <label for="name">Nombre:</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', isset($user) ? $user->name : '') }}">
            </div>

            <div class="form-group">
                <label for="last_name_one">Primer Apellido:</label>
                <input type="text" name="last_name_one" id="last_name_one" class="form-control" value="{{ old('last_name_one', isset($user) ? $user->last_name_one : '') }}">
            </div>

            <div class="form-group">
                <label for="last_name_two">Segundo Apellido:</label>
                <input type="text" name="last_name_two" id="last_name_two" class="form-control" value="{{ old('last_name_two', isset($user) ? $user->last_name_two : '') }}">
            </div>

            <div class="form-group">
                <label for="profesional_id">Cédula Profesional:</label>
                <input type="text" name="profesional_id" id="profesional_id" class="form-control" value="{{ old('profesional_id', isset($user) ? $user->profesional_id : '') }}">
            </div>

            <div class="form-group">
                <label for="position">Seleccione un cargo:</label>
                <select name="position" class="form-select" id="position">
                    <option value="">Seleccione una opción</option>
                    @foreach($position as $key => $value)
                        <option value="{{ $key }}" {{ isset($user) && $user->position == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', isset($user) ? $user->email : '') }}">
            </div>
            <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Actualizar' : 'Guardar' }}</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
        @if ($errors->any())
            <div class="alert2 alert2-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ __($error) }}<br></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>
@endsection
