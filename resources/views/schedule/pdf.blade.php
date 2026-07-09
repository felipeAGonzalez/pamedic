<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programación Semana {{ $week }}-{{ $year }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 6px;
            margin: 0;
            padding: 0;
        }
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 0px 1px; vertical-align: middle; word-wrap: break-word; }

        .header-title { font-size: 8px; font-weight: bold; text-align: center; margin: 0; }
        .header-sub   { font-size: 6px; text-align: center; margin: 1px 0; }

        .turno-header td {
            background-color: #4472C4;
            color: #ffffff;
            font-weight: bold;
            font-size: 7px;
            text-align: center;
            padding: 2px;
        }
        .dias-header td {
            background-color: #8db4e3;
            font-weight: bold;
            font-size: 6px;
            text-align: center;
            border: 1px solid #aaa;
        }
        .cols-header td {
            background-color: #D9E1F2;
            font-size: 5px;
            text-align: center;
            border: 1px solid #aaa;
        }
        .maq-col {
            background-color: #D9E1F2;
            font-weight: bold;
            text-align: center;
            width: 3%;
            border: 1px solid #aaa;
        }
        .row-alt { background-color: #f5f5f5; }
        .td-paciente { width: 9%; overflow: hidden; border: 1px solid #ccc; }
        .td-ec       { width: 3%; text-align: center; border: 1px solid #ccc; }
        .td-rc       { width: 2%; text-align: center; border: 1px solid #ccc; }
        .td-av       { width: 2%; text-align: center; border: 1px solid #ccc; }
        .leyenda td  { border: 1px solid #999; padding: 2px 4px; font-size: 6px; }
        .page-break  { page-break-before: always; }

        /* Colores de texto por derechohabiencia */
        .text-imss   { color: #FF0000; }
        .text-isapeg { color: #0070C0; }

        /* Fondo especial para fila de máquina 15 */
        .maq-15 { background-color: #BDD7EE !important; }

        /* Fondo amarillo para pacientes con filtro NIPRO */
        .bg-nipro { background-color: #FFC000; }
    </style>
</head>
<body>

{{-- ENCABEZADO --}}
<table style="margin-bottom: 4px;">
    <tr>
        <td style="width: 55px; vertical-align: middle;">
            <img src="{{ public_path('logos/pamedic.png') }}" width="45">
        </td>
        <td style="vertical-align: middle;">
            <p class="header-title">CORPORACIÓN PAMEDIC S.A DE C.V</p>
            <p class="header-sub">UNIDAD DE HEMODIÁLISIS — ANEXA AL CENTRO MÉDICO GUADALUPANO</p>
            <p class="header-sub" style="font-weight:bold;">PROGRAMACIÓN SEMANAL DE HEMODIÁLISIS</p>
            <p class="header-sub">{{ $weekLabel }}</p>
        </td>
    </tr>
</table>

{{-- TURNOS --}}
@foreach($turnLabels as $turnId => $turnLabel)

    @if($turnId == 3)
        <div style="page-break-before: always;"></div>
    @endif

    <table style="margin-bottom: 2px;">

        <tr class="turno-header">
            <td colspan="{{ 1 + count($days) * 4 }}">{{ $turnLabel }}</td>
        </tr>

        <tr class="dias-header">
            <td class="maq-col">Máq.</td>
            @foreach($days as $day)
                <td colspan="4" style="text-align:center;">{{ $day['name'] }} {{ $day['date'] }}</td>
            @endforeach
        </tr>

        <tr class="cols-header">
            <td class="maq-col"></td>
            @foreach($days as $day)
                <td class="td-ec">EC</td>
                <td class="td-paciente">Paciente</td>
                <td class="td-rc">RC</td>
                <td class="td-av">AV</td>
            @endforeach
        </tr>

        @foreach($machines as $i => $machine)
            @php $esMaq15 = ($machine->id == 15); @endphp
            <tr class="{{ $i % 2 == 0 ? '' : 'row-alt' }} {{ $esMaq15 ? 'maq-15' : '' }}">
                <td class="maq-col {{ $esMaq15 ? 'maq-15' : '' }}">{{ $machine->machine_number }}</td>
                @foreach($days as $day)
                    @php
                        $slot = collect($agenda[$turnId][$day['key']] ?? [])
                            ->first(fn($r) => $r->machine_id == $machine->id);
                    @endphp
                    @if($slot)
                        @php
                            $colorClass = match($slot->patient->insurance ?? 'NONE') {
                                'IMSS'   => 'text-imss',
                                'ISAPEG' => 'text-isapeg',
                                default  => '',
                            };
                            $niproClass = isset($niproPatientIds[$slot->patient_id]) ? 'bg-nipro' : '';
                        @endphp
                        <td class="td-ec {{ $colorClass }} {{ $niproClass }}">{{ substr($slot->patient->expedient_number ?? '', -4) }}</td>
                        <td class="td-paciente {{ $colorClass }} {{ $niproClass }}">
                            {{ trim(($slot->patient->last_name ?? '') . ' ' . ($slot->patient->last_name_two ?? '') . ' ' . ($slot->patient->name ?? '')) }}
                        </td>
                        <td class="td-rc {{ $colorClass }} {{ $niproClass }}"></td>
                        <td class="td-av {{ $colorClass }} {{ $niproClass }}"></td>
                    @else
                        <td class="td-ec"></td>
                        <td class="td-paciente"></td>
                        <td class="td-rc"></td>
                        <td class="td-av"></td>
                    @endif
                @endforeach
            </tr>
        @endforeach

    </table>
    <div style="margin-bottom: 2px;"></div>

@endforeach

{{-- CLAVES DE COLOR --}}
<table class="leyenda" style="margin-top: 6px; width: auto;">
    <tr>
        <td style="padding: 2px 8px;"><strong style="color: #0070C0;">&#9632; ISAPEG</strong></td>
        <td style="padding: 2px 8px;"><strong style="color: #FF0000;">&#9632; IMSS</strong></td>
        <td style="padding: 2px 8px; background-color: #FFC000;"><strong>&#9632; NIPRO</strong></td>
        <td style="padding: 2px 8px; background-color: #BDD7EE;"><strong>&#9632; Máquina 5008</strong></td>
    </tr>
</table>

</body>
</html>
