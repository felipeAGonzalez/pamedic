@extends('layouts.app')

@section('content')
    <h1>Listado de Usuarios</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary">Nuevo Usuario</a>
    <form method="GET" action="{{ route('users.index') }}" class="row g-2 mt-2">
        <div class="col-md-6">
            <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Buscar usuario">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-secondary">Buscar</button>
            @if(request()->filled('search'))
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            @endif
        </div>
    </form>
    <div class="table-responsive">
    <table class="table mt-4">
         <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Cargo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            @if($user->id != 1)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ __('web.'.$user->position) }}</td>
                <td>
                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-info">Ver</a>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Editar</a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                        <form action="{{ route('password.reset', $user->id) }}" method="POST" style="display: inline">
                            @csrf
                            <button type="submit" class="btn btn-success">Reinicio de contraseña</button>
                        </form>
                    </td>
                </tr>
            @endif
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-end">
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-end">
            {!!$users->links()!!}
            </ul>
        </nav>
    </div>
</div>
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@if ($errors->any())
                <div class="alert2 alert2-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ __($error) }}<br></li>
                        @endforeach
                        </ul>
                    </div>
            @endif
@endsection
