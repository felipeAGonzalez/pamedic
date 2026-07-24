@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">Cálculo de Insumos</h2>

    <a href="{{ route('supplies.index') }}" class="btn btn-secondary mb-3">Volver</a>
    @if($period)
        <a href="{{ route('supplies.pdf', ['week' => $week, 'period' => $period, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
           class="btn btn-danger mb-3" target="_blank">PDF</a>
    @endif

    <form method="GET" action="{{ route('supplies.calculate') }}" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="form-label">Fecha inicio</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" required>
            </div>
            <div class="col-auto">
                <label class="form-label">Fecha fin</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" required>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Calcular</button>
            </div>
        </div>
    </form>

    @if($period)
        <p class="text-muted">Periodo: <strong>{{ $period }}</strong> &mdash; {{ $activePatients->count() }} paciente(s) activos</p>

        <div class="row mb-4">
            <div class="col-md-4">
                <h5>Pacientes por Turno</h5>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Turno</th>
                            <th>Pacientes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([1 => 'Turno 1', 2 => 'Turno 2', 3 => 'Turno 3', 4 => 'Turno 4'] as $id => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td>{{ $shiftCounts[$id] ?? 0 }}</td>
                            </tr>
                        @endforeach
                        <tr class="table-warning">
                            <td>Emergencias</td>
                            <td>{{ $shiftCounts[5] ?? 0 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

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
    @endif

</div>
@endsection
