@props(['titulo','turnoData'])

@php
$dias = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$nombresDias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];

$maxBloques = 0;
foreach ($turnoData as $dia) {
    $maxBloques = max($maxBloques, $dia->count());
}
@endphp

<h5 class="text-center bg-light p-2 mt-4">{{ $titulo }}</h5>

@for($bloque=0; $bloque < $maxBloques; $bloque++)
<div class="table-responsive mb-4">
    <table class="table table-bordered table-sm">

        <thead class="table-secondary text-center">
            <tr>
                <th width="40">#</th>
                @foreach($nombresDias as $d)
                    <th>{{ $d }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @for($i=0; $i<15; $i++)
            <tr>
                <td><strong>{{ $i+1 }}</strong></td>

                @foreach($dias as $dia)
                    <td style="font-size:12px;">
                        @php
                            $paciente = $turnoData[$dia][$bloque][$i] ?? null;
                        @endphp

                        @if($paciente)
                            <strong>{{ $paciente->expedient_number }}</strong><br>
                            {{ $paciente->name }}
                        @endif
                    </td>
                @endforeach

            </tr>
            @endfor
        </tbody>

    </table>
</div>
@endfor
