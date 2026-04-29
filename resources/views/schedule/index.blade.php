@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <h4 class="text-center bg-warning p-2">
        SEMANA {{ $weekNumber }} ({{ $range }})
    </h4>

    <x-table title="PRIMER TURNO 6:00 - 9:30" :shiftData="$agenda[1]" />
    <x-table title="SEGUNDO TURNO 10:00 - 13:30" :shiftData="$agenda[2]" />
    <x-table title="TERCER TURNO 14:00 - 17:30" :shiftData="$agenda[3]" />
    <x-table title="CUARTO TURNO 17:30 - 21:00" :shiftData="$agenda[4]" />

</div>

@endsection
