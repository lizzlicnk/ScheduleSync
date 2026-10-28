@extends('layouts.DisenoPrincipal')

@section('title', 'Editar carrera | Schedule-Sync')

@push('estilos')
    <!-- Estilos de la edición de carreras -->
    <link rel="stylesheet" href="{{ asset('css/carreras/editar-carreras.css') }}">
@endpush

@section('contenido')
<div class="pagina-editar-carrera">

    <section class="panel-editar-carrera">

        <!-- Encabezado del formulario de edición -->
        <div class="encabezado-editar-carrera">
            <h1>Editar carrera: {{ $carreraEncontrada->nombreCarrera }}</h1>
            <p>Modifica los datos de la carrera y guarda los cambios.</p>
        </div>

        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div class="alerta-edicion-carrera alerta-error-carrera">
                <strong>Revisa los siguientes campos:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Mostrar errores generales -->
        @if (session('error'))
            <div class="alerta-edicion-carrera alerta-error-carrera">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulario para actualizar los datos de la carrera -->
        <form action="{{ route('carreras.update', ['idCarrera' => $carreraEncontrada->id_carrera]) }}" method="POST" class="formulario-editar-carrera" >
            @csrf
            @method('PUT')

            <div class="campos-edicion-carrera">

                <!-- Nombre de la carrera -->
                <div class="campo-edicion-carrera">
                    <label for="nombreCarrera">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Nombre de la carrera
                    </label>

                    <input type="text" id="nombreCarrera" name="nombreCarrera" value="{{ old('nombreCarrera', $carreraEncontrada->nombreCarrera) }}" maxlength="100" placeholder="Ingresa el nombre de la carrera" required >
                </div>

                <!-- Clave de la carrera -->
                <div class="campo-edicion-carrera">
                    <label for="claveCarrera">
                        <i class="fa-solid fa-key"></i>
                        Clave de la carrera
                    </label>

                    <input type="text" id="claveCarrera" name="claveCarrera" value="{{ old('claveCarrera', $carreraEncontrada->claveCarrera) }}" maxlength="45" placeholder="Ingresa la clave de la carrera" required >
                </div>

                <!-- Estado de la carrera -->
                @if ((int) auth()->user()->rol === 1)
                    <div class="campo-edicion-carrera campo-edicion-carrera-completo">
                        <label for="estado">
                            <i class="fa-solid fa-toggle-on"></i>
                            Estado de la carrera
                        </label>

                        <select id="estado" name="estado" required>
                            <option value="1" {{ (string) old('estado', $carreraEncontrada->estado) === '1' ? 'selected' : '' }} >
                                Activa
                            </option>

                            <option value="0" {{ (string) old('estado', $carreraEncontrada->estado) === '0' ? 'selected' : '' }} >
                                Inactiva
                            </option>
                        </select>
                    </div>
                @endif

            </div>

            <!-- Botones para cancelar o guardar los cambios -->
            <div class="acciones-edicion-carrera">

                <a href="{{ route('carreras.index') }}" class="boton-cancelar-edicion-carrera">
                    Cancelar
                </a>

                <button type="submit" class="boton-guardar-cambios-carrera">
                    Guardar cambios
                </button>

            </div>
        </form>
    </section>
</div>

@endsection
