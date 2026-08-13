@extends('layouts.app')

@section('content')
<script>
    setInterval(() => {
        window.location.href = "{{ route('attendance.list') }}";
    }, 3000);
</script>
<div class="container">
    <h2>Listado de Pacientes Para Tratamiento</h2>
    <div class="table-responsive">
        <table class="table mt-4">
            <thead class="table-dark">
                <tr>
                    <th scope="col">Número de expediente</th>
                    <th scope="col">Foto</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Género</th>
                    <th scope="col">Fecha de nacimiento</th>
                    @if(auth()->user()->position == 'NURSE' || auth()->user()->position == 'MANAGER')
                        <th scope="col">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($patients as $patient)
                    <tr>
                        <td>{{ $patient->expedient_number }}</td>

                        <td>
                            <div style="width: 200px; height: 200px; overflow: hidden;">
                                <img
                                    src="{{ $patient->photo ? asset($patient->photo) : asset('default/no-photo-m.png') }}"
                                    alt="Foto Paciente"
                                    style="width: auto; height: auto; object-fit: contain;">
                            </div>
                        </td>
                        <td>
                            {{ $patient->name . ' ' . $patient->last_name . ' ' . $patient->last_name_two }}
                        </td>
                        <td>{{ $patient->gender }}</td>
                        <td>
                            {{ $patient->birth_date ? $patient->birth_date->format('d-m-Y') : 'Sin fecha de nacimiento' }}
                        </td>
                        @if(auth()->user()->position == 'NURSE' || auth()->user()->position == 'MANAGER')
                            <td>
                                <form method="POST" action="{{ route('attendance.asigne' , ['id' => $patient->id]) }}">
                                    @csrf
                                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                                    <button
                                        type="submit"
                                        class="btn btn-success"
                                        onclick="this.disabled=true; this.form.submit();">
                                        Asignar Paciente
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@endsection
