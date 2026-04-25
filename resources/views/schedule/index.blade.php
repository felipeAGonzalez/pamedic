@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <h4 class="text-center bg-warning p-2">
        SEMANA {{ $semanaNumero }} ({{ $rango }})
    </h4>

    <x-table titulo="PRIMER TURNO 6:00 - 9:30" :turnoData="$agenda[1]" />
    <x-table titulo="SEGUNDO TURNO 10:00 - 13:30" :turnoData="$agenda[2]" />
    <x-table titulo="TERCER TURNO 14:00 - 17:30" :turnoData="$agenda[3]" />
    <x-table titulo="CUARTO TURNO 17:30 - 21:00" :turnoData="$agenda[4]" />

</div>

@endsection
