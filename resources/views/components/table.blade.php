@props(['title','shiftData'])

@php
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$daysES = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];

// calculate max blocks (chunks of 15)
$maxBlocks = 0;
foreach ($shiftData as $dayData) {
    $maxBlocks = max($maxBlocks, $dayData->count());
}
@endphp

<h5 class="text-center bg-light p-2 mt-4">{{ $title }}</h5>

@for($block = 0; $block < $maxBlocks; $block++)
<div class="table-responsive mb-4">
    <table class="table table-bordered table-sm">

        <thead class="table-secondary text-center">
            <tr>
                <th width="40">#</th>
                @foreach($daysES as $dayName)
                    <th>{{ $dayName }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @for($i = 0; $i < 15; $i++)
            <tr>
                <td><strong>{{ $i + 1 }}</strong></td>

                @foreach($days as $day)
                    <td style="font-size:12px;">
                        @php
                            $record = $shiftData[$day][$block][$i] ?? null;
                        @endphp

                        @if($record)
                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <strong>{{ $record->patient->expedient_number }}</strong><br>
                                {{ $record->patient->name }}
                            </div>

                            <form method="POST"
                                  action="{{ route('schedule.destroy', $record->id) }}"
                                  onsubmit="return confirm('Remove patient from schedule?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm px-2 py-0">✖</button>
                            </form>

                        </div>
                        @endif
                    </td>
                @endforeach

            </tr>
            @endfor
        </tbody>

    </table>
</div>
@endfor
