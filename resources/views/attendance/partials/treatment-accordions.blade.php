@if($turns->isEmpty())
    <div class="alert alert-info mb-0" role="status">No hay pacientes programados disponibles para tratamiento hoy.</div>
@else
    <div class="accordion" id="treatment-accordion">
        @foreach($turns as $turn)
            @php
                $collapseId = 'turn-'.Str::slug($turn['key']);
            @endphp
            <div class="accordion-item" data-turn-key="{{ $turn['key'] }}">
                <h2 class="accordion-header" id="heading-{{ $collapseId }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                        <span class="fw-semibold">{{ $turn['name'] }}</span>
                        <span class="ms-2 text-muted">{{ $turn['start'] }}</span>
                        <span class="badge bg-primary rounded-pill ms-auto me-3">{{ $turn['patients']->count() }} pacientes</span>
                    </button>
                </h2>
                <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $collapseId }}" data-bs-parent="#treatment-accordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr><th>Máquina</th><th>Expediente</th><th>Foto</th><th>Nombre</th><th>Género</th><th>Fecha de nacimiento</th>@if(in_array(auth()->user()->position, ['NURSE', 'MANAGER'], true))<th>Acciones</th>@endif</tr>
                                </thead>
                                <tbody>
                                @forelse($turn['patients'] as $scheduledPatient)
                                    @php
                                        $patient = $scheduledPatient->patient;
                                        $activePatient = $patient->activePatients->first();
                                        $machineNumber = $scheduledPatient->machine?->machine_number ?? $scheduledPatient->machine_id ?? 'Sin asignar';
                                    @endphp
                                    <tr data-active-patient-id="{{ $activePatient?->id }}" data-patient-id="{{ $patient->id }}">
                                        <td class="fw-bold fs-5">{{ $machineNumber }}</td>
                                        <td>{{ $patient->expedient_number }}</td>
                                        <td><img src="{{ $patient->photo ? asset($patient->photo) : asset('default/no-photo-m.png') }}" alt="Foto de {{ $patient->name }}" class="rounded object-fit-cover" width="96" height="96"></td>
                                        <td>{{ trim($patient->name.' '.$patient->last_name.' '.$patient->last_name_two) }}</td>
                                        <td>{{ $patient->gender }}</td>
                                        <td>{{ $patient->birth_date?->format('d-m-Y') ?? 'Sin fecha de nacimiento' }}</td>
                                        @if(in_array(auth()->user()->position, ['NURSE', 'MANAGER'], true))
                                            <td><form method="POST" action="{{ route('attendance.asigne', ['id' => $patient->id]) }}" data-assignment-form>@csrf<input type="hidden" name="active_patient_id" value="{{ $activePatient?->id }}"><button type="submit" class="btn btn-success" data-idle-text="Asignar máquina {{ $machineNumber }}">Asignar máquina {{ $machineNumber }}</button></form></td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ in_array(auth()->user()->position, ['NURSE', 'MANAGER'], true) ? 7 : 6 }}" class="text-center text-muted py-4">No hay pacientes disponibles en este turno.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
