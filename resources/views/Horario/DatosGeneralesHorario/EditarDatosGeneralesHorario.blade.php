@extends('layouts.DisenoPrincipal')

@section('title', 'Editar datos generales | Schedule-Sync')

@push('estilos')
    <!-- Estilos de la edición de datos generales -->
    <link rel="stylesheet" href="{{ asset('css/Horarios/DatosGeneralesHorario/editar-DatosGeneralesHorario.css') }}">
@endpush

@section('contenido')
<div class="pagina-editar-horario">

    <section class="panel-editar-horario">

        <!-- Encabezado del formulario -->
        <div class="encabezado-editar">
            <h1> Editar horario: {{ $horarioEncontrado->nombreHorario }} </h1>

            <p>Modifica los datos generales del horario y guarda los cambios.</p>
        </div>

        <!-- Aviso cuando el horario ya tiene asignaciones -->
        @if ($tieneAsignaciones)
            <div class="aviso-horario-bloqueado">
                <i class="fa-solid fa-lock"></i>

                <div>
                    <strong>Modificación de datos generales restringida</strong>

                    <p>
                        Este horario ya tiene asignaciones académicas registradas. No es posible modificar su periodo, carrera o grupo.
                        Únicamente puedes cambiar su estado.
                    </p>
                </div>
            </div>
        @endif

        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div class="alerta-edicion alerta-error">
                <strong>Revisa los siguientes campos:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Mostrar errores generales -->
        @if (session('tipo_modal_horario') === 'error' && session('mensaje_modal_horario'))
            <div class="alerta-edicion alerta-error">
                {{ session('mensaje_modal_horario') }}
            </div>
        @endif

        <!-- Formulario para actualizar los datos generales -->
        <form action="{{ route('datos-generales.update', ['idHorario' => $horarioEncontrado->id_horario]) }}" method="POST" class="formulario-editar" id="formEditarHorario" >
            @csrf
            @method('PUT')

            <div class="campos-edicion">

                <!-- Nombre del horario -->
                <div class="campo-edicion campo-edicion-completo">
                    <label for="nombreHorario">
                        <i class="fa-solid fa-calendar-days"></i>
                        Nombre del horario
                    </label>

                    <input type="text" id="nombreHorario" value="{{ $horarioEncontrado->nombreHorario }}" readonly >

                    <small>
                        El nombre se genera automáticamente con el periodo y el grupo seleccionados.
                    </small>
                </div>

                <!-- Periodo escolar -->
                <div class="campo-edicion">
                    <label for="id_periodo">
                        <i class="fa-solid fa-calendar"></i>
                        Periodo escolar
                    </label>

                    @if ($tieneAsignaciones)
                        <input type="text" value="{{ $horarioEncontrado->periodo->estacion }} {{ $horarioEncontrado->periodo->anio }}" readonly >

                        <input type="hidden" name="id_periodo" value="{{ $horarioEncontrado->id_periodo }}" >
                    @else
                        <select id="id_periodo" name="id_periodo" required>
                            <option value="">Selecciona un periodo</option>

                            @foreach ($periodosDisponibles as $periodo)
                                <option value="{{ $periodo->id_periodo }}"
                                    {{ (string) old('id_periodo', $horarioEncontrado->id_periodo) === (string) $periodo->id_periodo ? 'selected' : '' }} >
                                    {{ $periodo->estacion }} {{ $periodo->anio }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Carrera -->
                <div class="campo-edicion">
                    <label for="id_carrera">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Carrera
                    </label>

                    @if ($tieneAsignaciones)
                        <input type="text" value="{{ $horarioEncontrado->grupo->carrera->nombreCarrera }}" readonly >

                        <input type="hidden" name="id_carrera" value="{{ $horarioEncontrado->grupo->id_carrera }}" >
                    @else
                        <select id="id_carrera" name="id_carrera" required>
                            <option value="">Selecciona una carrera</option>

                            @foreach ($carrerasDisponibles as $carrera)
                                <option value="{{ $carrera->id_carrera }}"
                                    {{ (string) old('id_carrera', $horarioEncontrado->grupo->id_carrera) === (string) $carrera->id_carrera ? 'selected' : '' }} >
                                    {{ $carrera->nombreCarrera }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Grupo -->
                <div class="campo-edicion">
                    <label for="id_grupo">
                        <i class="fa-solid fa-users"></i>
                        Grupo académico
                    </label>

                    @if ($tieneAsignaciones)
                        <input type="text" value="{{ $horarioEncontrado->grupo->nombreGrupo }}" readonly >

                        <input type="hidden" name="id_grupo" value="{{ $horarioEncontrado->id_grupo }}" >
                    @else
                        <select id="id_grupo" name="id_grupo" required>
                            <option value="">Selecciona un grupo</option>

                            @foreach ($gruposDisponibles as $grupo)
                                <option value="{{ $grupo->id_grupo }}" data-carrera="{{ $grupo->id_carrera }}" data-periodo="{{ $grupo->id_periodo }}"
                                    {{ (string) old('id_grupo', $horarioEncontrado->id_grupo) === (string) $grupo->id_grupo ? 'selected' : '' }}
                                >
                                    {{ $grupo->nombreGrupo }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Estado -->
                <div class="campo-edicion">
                    <label for="estado">
                        <i class="fa-solid fa-toggle-on"></i>
                        Estado del horario
                    </label>

                    <select id="estado" name="estado" required>
                        <option value="1" {{ (string) old('estado', $horarioEncontrado->estado) === '1' ? 'selected' : '' }} >
                            Activo
                        </option>

                        <option value="0" {{ (string) old('estado', $horarioEncontrado->estado) === '0' ? 'selected' : '' }} >
                            Inactivo
                        </option>
                    </select>

                    <small>
                        Puedes cambiar el estado del horario sin modificar sus asignaciones.
                    </small>
                </div>

            </div>

            <!-- Botones para cancelar o guardar -->
            <div class="acciones-edicion">

                <a href="{{ route('datos-generales.index') }}" class="boton-cancelar-edicion" >
                    Cancelar
                </a>

                <button type="submit" class="boton-guardar-cambios">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar cambios
                </button>

            </div>
        </form>

    </section>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/Horarios/DatosGeneralesHorario/editar-DatosGeneralesHorario.js') }}"></script>
@endpush
