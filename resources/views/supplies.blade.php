<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Insumos - Semana {{ $week }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; }
    </style>
</head>
<body>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
        <tr>
            <td style="width: 25%; vertical-align: middle;">
                <img src="{{ public_path('logos/pamedic.png') }}" width="110">
            </td>
            <td style="width: 50%; text-align: center; vertical-align: middle; line-height: 1.6;">
                <strong>IMSS</strong><br>
                CORPORACIÓN PAMEDIC S.A DE C.V<br>
                UNIDAD DE HEMODIÁLISIS<br>
                ANEXA AL CENTRO MÉDICO GUADALUPANO
            </td>
            <td style="width: 25%; vertical-align: middle; line-height: 1.8;">
                Semana No.: {{ $week }}<br>
                Fecha: {{ $today instanceof \Carbon\Carbon ? $today->format('d/m/Y') : $today }}<br>
                @if($period)
                    Periodo: {{ $period }}
                @endif
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
        <tr>
            <td style="background-color: #C5E0B4; text-align: center; font-weight: bold; padding: 5px 0;">
                Insumos
            </td>
        </tr>
    </table>

    <table border="1" style="width: 100%; border-collapse: collapse; font-size: 11px;">
        <thead>
            <tr>
                <th style="background-color: #C5E0B4; width: 60%; text-align: center; padding: 4px;">MATERIAL</th>
                <th style="background-color: #C5E0B4; width: 20%; text-align: center; padding: 4px;">CANTIDAD SOLICITADA</th>
                <th style="background-color: #C5E0B4; width: 20%; text-align: center; padding: 4px;">CANTIDAD ENTREGADA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($supplies as $supply)
            <tr>
                <td style="padding: 3px 5px;">{{ $supply->material }}</td>
                <td style="text-align: center; padding: 3px;">{{ $supply->requested_quantity }}</td>
                <td style="text-align: center; padding: 3px;">{{ $supply->delivered_quantity ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
