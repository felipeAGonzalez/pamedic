@props(['title','shiftData','machines'])

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
<td class="p-0" style="font-size:12px;">
    @php
        $patients = collect($shiftData[$dia] ?? []);
        $record = $patients->firstWhere('machine_id', $machine->id);
    @endphp

    @if($record)
        <div class="d-flex align-items-start justify-content-between px-1 py-1">

            <div class="lh-sm">
                <strong>{{ $record->patient->expedient_number }}</strong><br>
                {{ $record->patient->name . ' '. $record->patient->last_name }}
            </div>

            @if(Auth::user()->position !== 'NURSE')
                <form method="POST"
                      action="{{ route('schedule.destroy',$record->id) }}"
                      onsubmit="return confirm('Remove patient from schedule?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm px-2 py-0">✖</button>
                </form>
            @endif

        </div>
    @endif
</td>
@endforeach

</tr>
@endforeach

    </tbody>
</table>
</div>
