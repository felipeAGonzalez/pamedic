@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ isset($medicine) ? 'Editar Medicamentos' : 'Dar de Alta Medicamentos' }}</h2>
        <form action="{{ isset($medicine) ? route('medicines.update', $medicine->id) : route('medicines.store') }}" enctype="multipart/form-data" method="POST">
            @csrf
            @if (isset($medicine))
                @method('PUT')
            @endif
            <div class="container">
        <div class="row">
                <div class = "col-md-6 mx-auto">
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ isset($medicine) ? $medicine->name : old('name') }}">
                    </div>

                    <div class="form-group">
                        <label for="route_administration">Vía de Administración:</label>
                        <select class="form-control" id="route_administration" name="route_administration">
                            <option value="" {{ !isset($medicine) || $medicine->route_administration == '' ? 'selected' : '' }}>Seleccione una opción</option>
                            <option value="oral" {{ isset($medicine) && $medicine->route_administration == 'oral' ? 'selected' : '' }}>Oral</option>
                            <option value="intravenous" {{ isset($medicine) && $medicine->route_administration == 'intravenous' ? 'selected' : '' }}>Intravenosa</option>
                            <option value="intramuscular" {{ isset($medicine) && $medicine->route_administration == 'intramuscular' ? 'selected' : '' }}>Intramuscular</option>
                            <option value="subcutaneous" {{ isset($medicine) && $medicine->route_administration == 'subcutaneous' ? 'selected' : '' }}>Subcutánea</option>
                            <option value="intradermal" {{ isset($medicine) && $medicine->route_administration == 'intradermal' ? 'selected' : '' }}>Intradérmica</option>
                            <option value="inhalation" {{ isset($medicine) && $medicine->route_administration == 'inhalation' ? 'selected' : '' }}>Inhalatoria</option>
                        </select>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="medicine_controlled" name="medicine_controlled" value="1" {{ isset($medicine) && $medicine->medicine_controlled ? 'checked' : '' }}>
                        <label class="form-check-label" for="medicine_controlled">¿Es un medicamento controlado?</label>
                    </div>

                </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ isset($medicine) ? 'Actualizar' : 'Guardar' }}</button>
        <a href="{{ route('medicines.index') }}" class="btn btn-info">Volver</a>
        </form>
        @if ($errors->any())
                <div class="alert2 alert2-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ __($error) }}<br></li>
                        @endforeach
                        </ul>
                    </div>
            @endif
    </div>
@endsection
