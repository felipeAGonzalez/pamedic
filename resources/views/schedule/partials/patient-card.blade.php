@props(['record', 'canEdit'])

<div class="schedule-patient-card border rounded bg-white p-1 mb-1"
     data-record-id="{{ $record->id }}"
     draggable="{{ $canEdit ? 'true' : 'false' }}">
    <div class="d-flex align-items-start justify-content-between gap-1">
        <div class="lh-sm">
            <strong>{{ $record->patient->expedient_number }}</strong><br>
            {{ trim($record->patient->name.' '.$record->patient->last_name.' '.$record->patient->last_name_two) }}
        </div>

        @if($canEdit)
            <div class="d-flex gap-1 schedule-card-actions">
                <form method="POST"
                      action="{{ route('schedule.destroy', $record->id) }}"
                      onsubmit="return confirm('¿Registrar que el paciente faltará únicamente este día?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-warning btn-sm px-2 py-0"
                            title="Ausencia de este día" aria-label="Ausencia de este día">✖</button>
                </form>

                <form method="POST"
                      action="{{ route('schedule.permanentDestroy', $record->id) }}"
                      onsubmit="return confirm('¿Dar de baja definitivamente al paciente de todas sus programaciones actuales y futuras?')">
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
