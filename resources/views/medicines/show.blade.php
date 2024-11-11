@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>información del Medicamento</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4><strong>{{ $medicine->name }}</strong></h4>
                        <p><strong>Via de administración:</strong> {{ $medicine->route_administration }}</p>
                    </div>
                </div>
            </div>
        </div>
</div>
    <a href="{{ route('medicines.index') }}" class="btn btn-secondary mt-3">Volver</a>
@endsection
