@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('schedule.cloneWeek') }}">
    @csrf
    <input type="hidden" name="week" value="{{ $week }}">
    <input type="hidden" name="year" value="{{ $year }}">
    <button class="btn btn-success">
        Clonar esta semana → siguiente
    </button>
</form>
<br>
<div class="d-flex justify-content-between align-items-center mb-3">

    <a href="{{ route('schedule.index', [$prevWeek->year, $prevWeek->weekOfYear]) }}"
       class="btn btn-outline-primary">
        ← Semana {{ $prevWeek->weekOfYear }}
    </a>

    <a href="{{ route('schedule.index', [$nextWeek->year, $nextWeek->weekOfYear]) }}"
       class="btn btn-outline-primary">
        Semana {{ $nextWeek->weekOfYear }} →
    </a>

</div>

<div class="container-fluid">

    <h4 class="text-center bg-warning p-2">
        SEMANA {{ $week }} ({{ $range }})
    </h4>

    <x-table title="PRIMER TURNO 6:00 - 9:30" :shiftData="$agenda[1]" :machines="$machines" />
    <x-table title="SEGUNDO TURNO 10:00 - 13:30" :shiftData="$agenda[2]" :machines="$machines" />
    <x-table title="TERCER TURNO 14:00 - 17:30" :shiftData="$agenda[3]" :machines="$machines" />
    <x-table title="CUARTO TURNO 17:30 - 21:00" :shiftData="$agenda[4]" :machines="$machines" />

</div>

@endsection
