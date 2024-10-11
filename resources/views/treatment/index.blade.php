@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Pacientes a tratamiento con {{$user->name}} </h2>
        <div class="table-responsive">
        <table class="table mt-4">
         <thead class="table-dark">
                <tr>
                    <th scope="col">Número de expediente</th>
                    <th scope="col">Foto</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Género</th>
                    <th scope="col">Fecha de nacimiento</th>
                    <th scope="col">Fecha de ingreso</th>
                    <th scope="col">Acciones</th>
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
                        <td>{{ $patient->date_entry->format('d-m-Y')}}</td>
                        <td>
                        <div >
                                    <a href="{{ route('treatment.create', ['id' => $patient->id]) }}" class="btn btn-info">Pre-dialisis</a>
                                    <a href="{{ route('treatment.createPres', ['id' => $patient->id]) }}" class="btn btn-primary">Prescripción</a>
                                    <a href="{{ route('treatment.createPreHemo', ['id' => $patient->id]) }}" class="btn btn-success">Pre-Hemodiálisis</a>
                                    <a href="{{ route('treatment.createTransHemo', ['id' => $patient->id]) }}" class="btn btn-danger">Trans-Hemodialisis</a>
                                    <a href="{{ route('treatment.createPostHemo', ['id' => $patient->id]) }}" class="btn btn-warning">Post-Hemodialisis</a>
                                    <a href="{{ route('treatment.createEvaluation', ['id' => $patient->id]) }}" class="btn btn-secondary">Evaluación</a>
                                    <a href="{{ route('treatment.createEvaluation', ['id' => $patient->id]) }}" class="btn btn-secondary">Valoración de enfermería</a>
                                </div>
                            </div>
                        </td>
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
    @if ($errors->any())
                <div class="alert2 alert2-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ __($error) }}<br></li>
                        @endforeach
                        </ul>
                    </div>
            @endif
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@endsection
