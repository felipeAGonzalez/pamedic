@props(['record', 'canEdit'])

@php
    $vascularAccessMark = match($record->vascular_access) {
        'fistula' => 'F',
        'catheter' => 'C',
        default => null,
    };
    $habitualMachineId = $record->habitualSchedule?->machine_id;
    $hasDailyChange = $habitualMachineId && (int) $habitualMachineId !== (int) $record->machine_id;
    $showMakeHabitual = $record->is_temporary || $hasDailyChange;
@endphp

<div class="schedule-patient-card border rounded bg-white p-1 mb-1"
     data-record-id="{{ $record->id }}"
     data-patient-id="{{ $record->patient_id }}"
     data-vascular-access="{{ $record->vascular_access }}"
     data-habitual-machine-id="{{ $habitualMachineId }}"
     data-temporary="{{ $record->is_temporary ? '1' : '0' }}"
     draggable="{{ $canEdit ? 'true' : 'false' }}">
    <div class="d-flex align-items-start justify-content-between gap-1">
        <div class="lh-sm">
            <strong>{{ $record->patient->expedient_number }}</strong><br>
            {{ trim($record->patient->name.' '.$record->patient->last_name.' '.$record->patient->last_name_two) }}@if($vascularAccessMark) <strong>({{ $vascularAccessMark }})</strong>@endif
            <span class="schedule-status-badges">
                <span class="badge bg-info text-dark schedule-temporary-badge {{ $record->is_temporary ? '' : 'd-none' }}">Temporal</span>
                <span class="badge bg-secondary schedule-daily-change-badge {{ $hasDailyChange ? '' : 'd-none' }}">Cambio del día</span>
            </span>
        </div>

        @if($canEdit)
            <div class="d-flex gap-1 schedule-card-actions">
                <form method="POST"
                      action="{{ route('schedule.makeHabitual', $record->id) }}"
                      class="schedule-habitual-form {{ $showMakeHabitual ? '' : 'd-none' }}"
                      data-confirm="¿Establecer esta máquina como ubicación habitual para este día de la semana?">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-primary btn-sm px-2 py-0"
                            title="Establecer como lugar habitual" aria-label="Establecer como lugar habitual">
                        <i class="bi bi-pin-angle-fill"></i>
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('schedule.destroy', $record->id) }}"
                      class="schedule-delete-form"
                      data-delete-type="absence"
                      data-confirm="¿Registrar que el paciente faltará únicamente este día?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-warning btn-sm px-2 py-0"
                            title="Ausencia de este día" aria-label="Ausencia de este día">✖</button>
                </form>

                <form method="POST"
                      action="{{ route('schedule.permanentDestroy', $record->id) }}"
                      class="schedule-delete-form"
                      data-delete-type="permanent"
                      data-confirm="¿Dar de baja definitivamente al paciente de todas sus programaciones actuales y futuras?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm px-2 py-0"
                            title="Baja definitiva" aria-label="Baja definitiva">
                        <i class="bi bi-person-x-fill"></i>
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
