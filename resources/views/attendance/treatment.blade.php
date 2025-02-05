@extends('layouts.app')

@section('content')
<script>
    setInterval(function() {
        location.reload();
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
                        <td>{{ $patient->expedient_number}}</td>
                        <td>
                            <div style="width: 200px; height: 200px; overflow: hidden;">
                                <img src="{{$patient->photo ? asset($patient->photo) : asset('default/no-photo-m.png')}}" alt="Foto Paciente" style="width: auto; height: auto; object-fit: contain;">
                            </div>
                        </td>
                        <td>{{ $patient->name . ' ' . $patient->last_name . ' ' . $patient->last_name_two }}</td>
                        <td>{{ $patient->gender}}</td>
                        <td>{{ $patient->birth_date->format('d-m-Y')}}</td>
                        @if(auth()->user()->position == 'NURSE' || auth()->user()->position == 'MANAGER')
                        <td>
                                <a href="{{ route('attendance.asigne', $patient->id) }}" class="btn btn-success">Asignar Paciente</a>
                        </td>
                            @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-end">
        </div>
    </div>
        </div>
    </div>
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@endsection
