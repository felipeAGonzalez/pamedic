@extends('layouts.app')

@section('content')

@php($canEdit = !in_array(Auth::user()->position, ['NURSE', 'MANAGER']))

@if(Session::has('success'))
    <div class="alert2 alert2-success">
        <ul>
            <li>{!! Session::get('success') !!}<br></li>
        </ul>
    </div>
@endif
@if(Session::has('error'))
    <div class="alert2 alert2-danger">
        <ul>
            <li>{!! Session::get('error') !!}<br></li>
        </ul>
    </div>
@endif
@if($canEdit)
    <div id="schedule-feedback" class="alert d-none mb-3" role="status" aria-live="polite"></div>
@endif


@if($canEdit)
    <form method="POST" action="{{ route('schedule.cloneWeek') }}" class="d-inline-block">
        @csrf
        <input type="hidden" name="week" value="{{ $week }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <button class="btn btn-success">
            Clonar esta semana → siguiente
        </button>
    </form>

    @if($cloneBatch)
        <form method="POST"
              action="{{ route('schedule.undoCloneWeek', $cloneBatch->id) }}"
              class="d-inline-block ml-2"
              onsubmit="return confirm('¿Desea deshacer la clonación de la siguiente semana?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" @disabled(! $cloneUndo['can_undo'])>
                Deshacer clonación de la siguiente semana
            </button>
        </form>

        @if(! $cloneUndo['can_undo'])
            <div class="text-danger small mt-2">{{ $cloneUndo['reason'] }}</div>
        @endif
    @endif
    <br>
@endif
<div class="d-flex justify-content-between align-items-center mb-3">

    <a href="{{ route('schedule.index', [$prevWeek->year, $prevWeek->weekOfYear]) }}"
       class="btn btn-outline-primary">
        ← Semana {{ $prevWeek->weekOfYear }}
    </a>

    <a href="{{ route('schedule.pdf', [$year, $week]) }}"
       target="_blank"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer"></i> Imprimir PDF
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

    <x-table title="PRIMER TURNO 6:00 - 9:30" :shiftData="$agenda[1]" :machines="$machines" :dates="$dates" :scheduleId="1" :canEdit="$canEdit" />
    <x-table title="SEGUNDO TURNO 10:00 - 13:30" :shiftData="$agenda[2]" :machines="$machines" :dates="$dates" :scheduleId="2" :canEdit="$canEdit" />
    <x-table title="TERCER TURNO 14:00 - 17:30" :shiftData="$agenda[3]" :machines="$machines" :dates="$dates" :scheduleId="3" :canEdit="$canEdit" />
    <x-table title="CUARTO TURNO 17:30 - 21:00" :shiftData="$agenda[4]" :machines="$machines" :dates="$dates" :scheduleId="4" :canEdit="$canEdit" />

</div>

@include("schedule.partials.interactive")

@endsection
