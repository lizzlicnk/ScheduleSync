@extends('layouts.DisenoPrincipal')

@section('title', 'Editar asignatura | Schedule-Sync')

@push('estilos')
    <!-- Estilos -->
    <link rel="stylesheet" href="{{ asset('css/Asignaturas/editar-asignaturas.css') }}">
@endpush

@section('contenido')
<div class="pagina-editar-asignatura">

    <section class="panel-editar-asignatura">

        <!-- Encabezado del formulario de edición -->
        <div class="encabezado-editar-asignatura">
            <h1>Editar asignatura: {{ $asignaturaEncontrada->nombreAsignatura }}</h1>
            <p>Modifica los datos de la asignatura y guarda los cambios.</p>
        </div>

        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div class="alerta-edicion-asignatura alerta-error-asignatura">
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
            <div class="alerta-edicion-asignatura alerta-error-asignatura">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulario para actualizar la asignatura -->
        <form action="{{ route('asignaturas.update', ['idAsignatura' => $asignaturaEncontrada->id_asignatura]) }}" method="POST" class="formulario-editar-asignatura">
            @csrf
            @method('PUT')

            <div class="campos-edicion-asignatura">
                <!-- Clave de la asignatura -->
                <div class="campo-edicion-asignatura">
                    <label for="claveAsignatura">
                        <i class="fa-solid fa-key"></i>
                        Clave de la asignatura
                    </label>

                    <input type="text" id="claveAsignatura" name="claveAsignatura"
                        value="{{ old('claveAsignatura', $asignaturaEncontrada->clave) }}"
                        maxlength="20" placeholder="Ingresa la clave de la asignatura" required >
                </div>

                <!-- Cuatrimestre -->
                <div class="campo-edicion-asignatura">
                    <label for="cuatrimestre">
                        <i class="fa-solid fa-calendar-days"></i>
                        Cuatrimestre
                    </label>

                    <select id="cuatrimestre" name="cuatrimestre" required>
                        <option value="" disabled>
                            Selecciona el cuatrimestre
                        </option>

                        @for ($cuatrimestre = 1; $cuatrimestre <= 12; $cuatrimestre++)
                            <option value="{{ $cuatrimestre }}" {{ (string) old('cuatrimestre', $asignaturaEncontrada->cuatrimestre) === (string) $cuatrimestre ? 'selected' : '' }} >
                                {{ $cuatrimestre }}° cuatrimestre
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Cantidad de horas -->
                <div class="campo-edicion-asignatura">
                    <label for="horas">
                        <i class="fa-solid fa-clock"></i>
                        Horas semanales
                    </label>

                    <input type="number" id="horas" name="horas"
                        value="{{ old('horas', $asignaturaEncontrada->cantidadHoras) }}"
                        min="1" max="15" placeholder="Ingresa la cantidad de horas" required >
                </div>

                <!-- Estado de la asignatura -->
                @if ((int) auth()->user()->rol === 1)
                <div class="campo-edicion-asignatura">
                    <label for="estado">
                        <i class="fa-solid fa-circle-check"></i>
                        Estado
                    </label>

                    <select id="estado" name="estado" required>
                        <option value="1"
                            {{ (int) $asignaturaEncontrada->estado === 1 ? 'selected' : '' }}>
                            Activa
                        </option>

                        <option value="0"
                            {{ (int) $asignaturaEncontrada->estado === 0 ? 'selected' : '' }}>
                            Inactiva
                        </option>
                    </select>
                </div>
                @endif

                <!-- Nombre de la asignatura -->
                <div class="campo-edicion-asignatura campo-edicion-asignatura-completo">
                    <label for="nombreAsignatura">
                        <i class="fa-solid fa-book"></i>
                        Nombre de la asignatura
                    </label>

                    <input type="text" id="nombreAsignatura" name="nombreAsignatura"
                        value="{{ old('nombreAsignatura', $asignaturaEncontrada->nombreAsignatura) }}"
                        maxlength="100" placeholder="Ingresa el nombre de la asignatura" required >
                </div>

                <!-- Carrera -->
                <div class="campo-edicion-asignatura campo-edicion-asignatura-completo">
                    <label for="id_carrera">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Carrera
                    </label>

                    <select id="id_carrera" name="id_carrera" required>
                        <option value="" disabled>
                            Selecciona una carrera
                        </option>

                        @foreach ($carrerasActivas as $carrera)
                            <option value="{{ $carrera->id_carrera }}" {{ (string) old('id_carrera', $asignaturaEncontrada->id_carrera) === (string) $carrera->id_carrera ? 'selected' : '' }} >
                                {{ $carrera->claveCarrera }} - {{ $carrera->nombreCarrera }}

                                @if ((int) $carrera->estado === 0)
                                    (Inactiva)
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <small>
                        Solo puedes cambiar la asignatura a una carrera activa.
                    </small>
                </div>
            </div>

            <!-- Botones para cancelar o guardar los cambios -->
            <div class="acciones-edicion-asignatura">
                <a href="{{ route('asignaturas.index') }}" class="boton-cancelar-edicion-asignatura">
                    Cancelar
                </a>

                <button type="submit" class="boton-guardar-cambios-asignatura">
                    Guardar cambios
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
