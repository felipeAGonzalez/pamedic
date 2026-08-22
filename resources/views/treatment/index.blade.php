@extends('layouts.app')

@section('content')
    <div class="container">
        @if($user->position != 'NURSE' || $user->position != 'MANAGER')
        <h2>Pacientes en tratamiento actual</h2>
        @else
        <h2>Pacientes a tratamiento con {{$user->name .' '. __('web.'.$user->position)}} </h2>
        @endif
        @if (session('Error'))
                <div class="alert alert-danger">
                    {{ session('Error') }}
                </div>
            @endif
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
        <div class="table-responsive">
        <table class="table mt-4">
         <thead class="table-dark">
                <tr>
                    <th scope="col">Número de expediente</th>
                    <th scope="col">Foto</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Género</th>
                    @if($user->position === 'ROOT')
                        <th scope="col">Fecha</th>
                    @endif
                <th scope="col">{{ $user->position != 'NURSE' && $user->position != 'MANAGER' ? 'Asignación' : 'Tratamiento' }}</th>
                  @if(in_array($user->position, ['NURSE', 'MANAGER', 'ROOT']))
                <th scope="col">Acciones</th>
                    @endif

                </tr>
            </thead>
            <tbody>
                @foreach($patients as $patient)
                    <tr>
                        <td>{{ $patient->expedient_number}}</td>
                        <td>
                            <div style="width: 200px; height: 200px; overflow: hidden;">
                                <img src="{{$patient->photo ? asset($patient->photo) : asset('default/no-photo-m.png')}}" alt="Foto Paciente" style="width: auto; height: auto; object-fit: contain;">
                            </div>
                        </td>
                        <td>{{ $patient->name . ' ' . $patient->last_name . ' ' . $patient->last_name_two }}</td>
                        <td>{{ $patient->gender}}</td>
                        @if($user->position === 'ROOT')
                            <td>{{ $patient->activePatient->date }}</td>
                        @endif
                        <td>
                            @if($user->position != 'NURSE' && $user->position != 'MANAGER')
                            <label>{{ $patient->activePatient->nursePatient->user->name }}</label>
                            @if($user->position === 'ROOT')
                                <div class="modal fade" id="finalizeTreatment{{ $patient->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('treatment.finalize', ['id' => $patient->id]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Finalizar tratamiento anterior</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label for="start_date_{{ $patient->id }}" class="form-label">Fecha de inicio del tratamiento</label>
                                                    <input type="date" id="start_date_{{ $patient->id }}" name="start_date" class="form-control" required>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-success">Finalizar Tratamiento</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @else
                        <div >
                                    <a href="{{ route('treatment.createWeight', ['id' => $patient->id]) }}" class="btn btn-dark">Pesos</a>
                                    <a href="{{ route('treatment.create', ['id' => $patient->id]) }}" class="btn btn-info">Pre-dialisis</a>
                                    <a href="{{ route('treatment.createPres', ['id' => $patient->id]) }}" class="btn btn-primary">Prescripción</a>
                                    <a href="{{ route('treatment.createPreHemo', ['id' => $patient->id]) }}" class="btn btn-success">Pre-Hemodiálisis</a>
                                    <a href="{{ route('treatment.createTransHemo', ['id' => $patient->id]) }}" class="btn btn-danger">Trans-Hemodialisis</a>
                                    <a href="{{ route('treatment.createEvaluation', ['id' => $patient->id]) }}" class="btn btn-secondary">Evaluación</a>
                                    <a href="{{ route('treatment.createEvaluationNurse', ['id' => $patient->id]) }}" class="btn btn-secondary">Valoración de enfermería</a>
                                    <a href="{{ route('treatment.createMedicineAdmin', ['id' => $patient->id]) }}" class="btn btn-info">Ministración de medicamentos</a>
                                    <a href="{{ route('treatment.createTimeOut', ['id' => $patient->id]) }}" class="btn btn-primary">Verificación/Tiempo Fuera</a>
                                    <a href="{{ route('treatment.createPostHemo', ['id' => $patient->id]) }}" class="btn btn-warning">Post-Hemodialisis</a>
                                    <a href="{{ route('treatment.createOxygen', ['id' => $patient->id]) }}" class="btn btn-danger">Oxigeno Terapia</a>
                        </div>
                        @unless($patientsWithSavedClinicalData->contains($patient->id))
                            <form action="{{ route('treatment.assignment.undo', ['id' => $patient->activePatient->id]) }}" method="POST" class="mt-2" onsubmit="return confirm('¿Desea deshacer esta asignación?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Deshacer asignación</button>
                            </form>
                        @endunless
                        @endif
                        </td>
                        <td>
                            @if($user->position === 'ROOT')
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#finalizeTreatment{{ $patient->id }}">
                                    Finalizar Tratamiento
                                </button>
                            @elseif(in_array($user->position, ['NURSE', 'MANAGER']))
                                <form action="{{ route('treatment.finalize', ['id' => $patient->id]) }}" method="POST" onsubmit="return confirm('¿Desea finalizar este tratamiento?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">Finalizar Tratamiento</button>
                                </form>
                            @endif
                        @if(in_array($user->position, ['ROOT', 'DIRECTIVE', 'QUALITY']))
                                        <form action="{{ route('delete.treatment', ['id' => $patient->id]) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Eliminar tratamiento</button>
                                        </form>
                                    @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-end">
        </div>
    </div>
        </div>
    </div>

@endsection
