@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Medicamentos para su administración</h1>
    <div class="row">
        <div class="col-md-6">
        <a href="{{ route('medicines.create') }}" class="btn btn-primary mb-3">Registrar Medicamentos</a>
        </div>
        </div>
    <div class="table-responsive">
        <table class="table mt-4">
         <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Vía de Administración</th>
                <th>Medicamento Controlado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medicines as $medicine)
            <tr>
                <td>{{ $medicine->name }}</td>
                <td>{{ $medicine->route_administration }}</td>
                <td>
                    @if($medicine->medicine_controlled == 1)
                        <i class="bi bi-check-circle-fill text-success"></i>
                    @else
                        <i class="bi bi-dash-circle text-danger"></i>
                    @endif
                </td>
                <td>
                    <a href="{{ route('medicines.edit', $medicine->id) }}" class="btn btn-primary">Edit</a>
                    <form action="{{ route('medicines.destroy', $medicine->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end">
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-end">
            {!!$medicines->links()!!}
            </ul>
        </nav>
    </div>
        </div>
    </div>
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@endsection
