@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">Cálculo de Insumos</h2>

    <a href="{{ route('supplies.index') }}" class="btn btn-secondary mb-3">Volver</a>

    <div class="row">
        <div class="col-md-6">
            <h5>Acceso Vascular</h5>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Tipo</th>
                        <th>Pacientes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vascularCounts as $type => $total)
                        <tr>
                            <td>{{ $type }}</td>
                            <td>{{ $total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <h5>Tipo de Dializador</h5>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Tipo</th>
                        <th>Pacientes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dialyzerCounts as $type => $total)
                        <tr>
                            <td>{{ $type }}</td>
                            <td>{{ $total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
