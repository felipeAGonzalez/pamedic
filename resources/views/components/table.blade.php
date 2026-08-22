@props(['title', 'shiftData', 'machines', 'dates', 'scheduleId', 'canEdit' => false])

@php
$dias = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$nombresDias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
@endphp

<h5 class="text-center bg-light p-2 mt-4">{{ $title }}</h5>

<div class="table-responsive mb-4">
<table class="table table-bordered table-sm">

    <thead class="table-secondary text-center">
        <tr>
            <th width="80">Máquina</th>
            @foreach($nombresDias as $d)
                <th>{{ $d }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>

@foreach($machines as $machine)
<tr>
    <td class="text-center">
        <strong>{{ $machine->machine_number }}</strong>
    </td>

    @foreach($dias as $dia)
        @php
            $records = collect($shiftData[$dia] ?? [])
                ->filter(fn ($record) => (int) $record->machine_id === (int) $machine->id)
                ->values();
        @endphp

        <td class="schedule-cell p-1"
            style="font-size:12px; min-width:165px; min-height:55px;"
            data-schedule-id="{{ $scheduleId }}"
            data-date="{{ $dates[$dia] }}"
            data-machine-id="{{ $machine->id }}"
            data-record-count="{{ $records->count() }}"
            @if($canEdit) tabindex="0" role="button" title="Agregar paciente o recibir un cambio de máquina" @endif>
            @foreach($records as $record)
                @include('schedule.partials.patient-card', ['record' => $record, 'canEdit' => $canEdit])
            @endforeach

            @if($records->isEmpty() && $canEdit)
                <div class="schedule-empty-placeholder text-muted text-center py-2">
                    <i class="bi bi-person-plus"></i> Agregar
                </div>
            @endif
        </td>
    @endforeach
</tr>
@endforeach

    </tbody>
</table>
</div>
