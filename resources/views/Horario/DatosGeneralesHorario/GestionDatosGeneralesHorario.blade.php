@extends('layouts.DisenoPrincipal')

@section('title', 'Datos generales de horarios | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Horarios/DatosGeneralesHorario/gestion-DatosGeneralesHorario.css') }}">
@endpush

@section('contenido')

<main class="pagina-horarios">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <p class="ruta-modulo">Inicio / Horarios / Datos generales</p>
            <h1>Gestión de datos generales</h1>
            <p class="descripcion-modulo">
                Administra la información general de los horarios académicos registrados en Schedule-Sync.
            </p>
        </div>
    </header>

    <!-- MENSAJES DEL SISTEMA -->
    @if (session('exito'))
        <div class="alerta-horarios alerta-exito" role="alert">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <span>{{ session('exito') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alerta-horarios alerta-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- FILTROS DE HORARIOS -->
    <section class="panel-horarios panel-filtros" id="contenedor-filtros" hidden>

        <div class="encabezado-panel">
            <div>
                <h2>Filtrar horarios</h2>
                <p class="texto-secundario">
                    Selecciona los criterios para encontrar los datos generales de un horario.
                </p>
            </div>

            <button type="button" class="boton-secundario" id="limpiar-filtros">
                <i class="fa-solid fa-eraser" aria-hidden="true"></i>
                Limpiar filtros
            </button>
        </div>

        <div class="filtros-horarios">

            <div class="campo">
                <label for="filtro-anio">Año</label>
                <select id="filtro-anio">
                    <option value="">Todos los años</option>

                    @foreach ($periodosDisponibles->pluck('anio')->unique()->sort() as $anio)
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endforeach
                </select>
            </div>

            <div class="campo">
                <label for="filtro-periodo">Periodo escolar</label>
                <select id="filtro-periodo">
                    <option value="">Todos los periodos</option>

                    @foreach ($periodosDisponibles as $periodo)
                        <option value="{{ $periodo->id_periodo }}" data-anio="{{ $periodo->anio }}" >
                            {{ $periodo->estacion }} {{ $periodo->anio }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="campo">
                <label for="filtro-carrera">Carrera</label>
                <select id="filtro-carrera">
                    <option value="">Todas las carreras</option>

                    @foreach ($carrerasDisponibles as $carrera)
                        <option value="{{ $carrera->id_carrera }}">
                            {{ $carrera->nombreCarrera }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="campo">
                <label for="filtro-estado">Estado del horario</label>
                <select id="filtro-estado">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

        </div>
    </section>

    <!-- TABLA DE HORARIOS -->
    <section class="panel-horarios">

        <div class="encabezado-panel encabezado-tabla">

            <div class="titulo-tabla">
                <h2>Horarios registrados</h2>
                <p class="texto-secundario">
                    <span id="total-resultados">{{ $horariosRegistrados->count() }}</span>
                    horario(s) encontrado(s)
                </p>
            </div>

            <div class="botones-encabezado">

                <button type="button" class="boton-secundario" id="boton-filtros" aria-expanded="false" aria-controls="contenedor-filtros" >
                    <span aria-hidden="true">☷</span>
                    Filtros
                </button>

                <a href="#formulario-horario" class="boton-principal">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    Nuevo horario
                </a>

            </div>

            <div class="busqueda-tabla campo">
                <label for="buscar-horario">Buscar horario</label>

                <div class="entrada-busqueda">
                    <span aria-hidden="true">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <input type="search" id="buscar-horario" placeholder="Nombre, carrera o grupo..." autocomplete="off" >
                </div>
            </div>

        </div>

        <div class="tabla-contenedor">

            <table class="tabla-horarios">

                <thead>
                    <tr>
                        <th scope="col">Nombre del horario</th>
                        <th scope="col">Periodo escolar</th>
                        <th scope="col">Carrera</th>
                        <th scope="col">Grupo</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody id="cuerpo-tabla-horarios">

                    @forelse ($horariosRegistrados as $horario)

                        @php
                            $grupo = $horario->grupo;
                            $carrera = $grupo?->carrera;
                            $periodo = $horario->periodo;
                            $horarioActivo = (int) $horario->estado === 1;
                        @endphp

                        <tr
                            data-nombre="{{ mb_strtolower($horario->nombreHorario) }}"
                            data-periodo="{{ mb_strtolower(($periodo?->estacion ?? '') . ' ' . ($periodo?->anio ?? '')) }}"
                            data-anio="{{ $periodo?->anio ?? '' }}"
                            data-id-periodo="{{ $horario->id_periodo }}"
                            data-carrera="{{ mb_strtolower($carrera?->nombreCarrera ?? '') }}"
                            data-id-carrera="{{ $grupo?->id_carrera ?? '' }}"
                            data-grupo="{{ mb_strtolower($grupo?->nombreGrupo ?? '') }}"
                            data-id-grupo="{{ $horario->id_grupo }}"
                            data-estado="{{ $horarioActivo ? '1' : '0' }}"
                        >

                            <td>
                                <div class="informacion-horario-tabla">
                                    <strong>{{ $horario->nombreHorario }}</strong>
                                </div>
                            </td>

                            <td>
                                {{ $periodo?->estacion ?? '-' }}
                                {{ $periodo?->anio ?? '' }}
                            </td>

                            <td>{{ $carrera?->nombreCarrera ?? '-' }}</td>

                            <td>
                                <span class="grupo-horario">
                                    {{ $grupo?->nombreGrupo ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="estado-horario {{ $horarioActivo ? 'estado-activo' : 'estado-inactivo' }}">
                                    {{ $horarioActivo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td>
                                <div class="acciones-tabla">

                                    <a href="{{ route('datos-generales.index', ['consultar' => $horario->id_horario]) }}#panel-consulta"
                                        class="boton-accion boton-consultar" title="Consultar datos generales">

                                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                            Consultar
                                        </a>

                                    <a href="{{ route('datos-generales.edit', $horario->id_horario) }}" class="boton-accion boton-modificar" title="Modificar datos generales" >
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        Modificar
                                    </a>

                                    @if ((int) auth()->user()->rol === 1)
                                        @if ($horarioActivo)

                                            <form action="{{ route('datos-generales.destroy', $horario->id_horario) }}"
                                                method="POST"
                                                class="formulario-desactivar-horario"
                                                data-nombre="{{ $horario->nombreHorario }}">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="boton-accion boton-eliminar"
                                                        title="Desactivar horario">

                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                    Desactivar

                                                </button>

                                            </form>

                                        @endif
                                    @endif

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr id="fila-sin-horarios">
                            <td colspan="6" class="tabla-vacia">
                                No hay horarios registrados.
                            </td>
                        </tr>

                    @endforelse

                    <tr id="fila-sin-filtros" hidden>
                        <td colspan="6" class="tabla-vacia">
                            No hay horarios que coincidan con la búsqueda o los filtros seleccionados.
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </section>

    <!-- FORMULARIO PARA REGISTRAR DATOS GENERALES -->
    <section class="panel-horarios panel-formulario-horario" id="formulario-horario">

        <div class="franja-institucional"></div>
        <div class="contenido-formulario">

            <div class="encabezado-panel encabezado-formulario">

                <div class="icono-formulario" aria-hidden="true">
                    <i class="fa-solid fa-calendar-plus"></i>
                </div>

                <div>
                    <h2>Registrar datos generales</h2>
                    <p class="texto-secundario"> Completa la información para registrar un nuevo horario académico. </p>
                </div>

            </div>

            <form action="{{ route('datos-generales.store') }}" method="POST" id="formAgregarHorario" >
            @csrf

                <div class="formulario-grid">

                    <div class="campo">
                        <label for="id_periodo">
                            <i class="fa-solid fa-calendar" aria-hidden="true"></i>
                            Periodo escolar
                            <span class="campo-obligatorio">*</span>
                        </label>

                        <select id="id_periodo" name="id_periodo" required>
                            <option value="">Selecciona un periodo</option>

                            @foreach ($periodosDisponibles as $periodo)
                                <option value="{{ $periodo->id_periodo }}" data-anio="{{ $periodo->anio }}" @selected(old('id_periodo') == $periodo->id_periodo) >
                                    {{ $periodo->estacion }} {{ $periodo->anio }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_periodo')
                            <span class="error-campo">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="campo">
                        <label for="id_carrera">
                            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                            Carrera
                            <span class="campo-obligatorio">*</span>
                        </label>

                        <select id="id_carrera" name="id_carrera" required>
                            <option value="">Selecciona una carrera</option>

                                @foreach ($carrerasDisponibles as $carrera)
                                    <option value="{{ $carrera->id_carrera }}" @selected(old('id_carrera') == $carrera->id_carrera) >
                                    {{ $carrera->nombreCarrera }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_carrera')
                            <span class="error-campo">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="campo">
                        <label for="id_grupo">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                            Grupo
                            <span class="campo-obligatorio">*</span>
                        </label>

                        <select id="id_grupo" name="id_grupo" required>
                            <option value="">Selecciona un grupo</option>

                            @foreach ($gruposDisponibles as $grupo)
                                <option value="{{ $grupo->id_grupo }}" data-carrera="{{ $grupo->id_carrera }}" data-periodo="{{ $grupo->id_periodo }}" data-cuatrimestre="{{ $grupo->cuatrimestre }}" @selected(old('id_grupo') == $grupo->id_grupo) >
                                    {{ $grupo->nombreGrupo }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_grupo')
                            <span class="error-campo">{{ $message }}</span>
                        @enderror

                        <small class="ayuda-campo">
                            Los grupos disponibles dependerán de la carrera y el periodo escolar seleccionados.
                        </small>
                    </div>

                    <div class="campo">
                        <label for="estado">
                            <i class="fa-solid fa-toggle-on" aria-hidden="true"></i>
                            Estado del horario
                            <span class="campo-obligatorio">*</span>
                        </label>

                        <select id="estado" name="estado" required>
                            <option value="">Selecciona un estado</option>
                            <option value="1" @selected(old('estado') === '1')>
                                Activo
                            </option>
                            <option value="0" @selected(old('estado') === '0')>
                                Inactivo
                            </option>
                        </select>

                        @error('estado')
                            <span class="error-campo">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="acciones-formulario">

                    <a href="{{ route('datos-generales.index') }}" class="boton-secundario">
                        Cancelar
                    </a>

                    <button type="submit" class="boton-principal">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                        Guardar horario
                    </button>

                </div>

            </form>

        </div>
    </section>

    <!-- PANEL PARA CONSULTAR DATOS GENERALES DEL HORARIO -->
    <section class="panel-horarios panel-consulta-horarios" id="panel-consulta">

        <div class="encabezado-panel">
            <div>
                <h2>Consultar horario</h2>
                <p class="texto-secundario">
                    Busca por nombre para consultar los datos generales del horario.
                </p>
            </div>
        </div>

        <div class="consulta-grid">

            <div class="buscador-consulta">
                <div class="campo">
                    <label for="buscar-consulta">Nombre del horario</label>

                    <div class="entrada-busqueda">
                        <span aria-hidden="true">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>

                        <input type="search" id="buscar-consulta" placeholder="Escribe el nombre de un horario..." autocomplete="off" >
                    </div>
                </div>

                <div class="resultados-consulta">
                    <h3>Resultados</h3>

                    <div id="resultados-consulta">

                        @forelse ($horariosRegistrados as $horario)
                            <button
                                type="button"
                                class="resultado-consulta-item"
                                data-resultado-horario="{{ $horario->id_horario }}"
                                data-nombre-consulta="{{ mb_strtolower($horario->nombreHorario) }}"
                            >
                                <span class="texto-resultado">
                                    <strong>{{ $horario->nombreHorario }}</strong>
                                    <small>
                                        {{ $horario->grupo?->carrera?->nombreCarrera ?? '-' }} · {{ $horario->grupo?->nombreGrupo ?? '-' }}
                                    </small>
                                </span>
                            </button>
                        @empty
                            <p class="texto-secundario mensaje-sin-resultados">
                                No hay horarios para consultar.
                            </p>
                        @endforelse

                        <p class="texto-secundario mensaje-sin-resultados" id="sin-resultados-consulta" hidden >
                            No se encontraron horarios con ese nombre.
                        </p>

                    </div>
                </div>
            </div>

            <div class="tarjeta-detalle" id="tarjeta-detalle">

                <div class="detalle-vacio" id="detalle-vacio">
                    <div class="icono-detalle" aria-hidden="true">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <h3>Información del horario</h3>

                    <p class="texto-secundario">
                        Selecciona un horario de los resultados para ver su información.
                    </p>
                </div>

                @foreach ($horariosRegistrados as $horario)
                    <div class="contenido-detalle-horario" data-detalle-horario="{{ $horario->id_horario }}" hidden >
                        <div class="encabezado-detalle">
                            <div class="icono-detalle" aria-hidden="true">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>

                            <div>
                                <h3>{{ $horario->nombreHorario }}</h3>
                                <p class="correo-detalle">
                                    {{ $horario->grupo?->carrera?->nombreCarrera ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="lista-detalle">

                            <div class="fila-detalle">
                                <span>Nombre del horario</span>
                                <strong>{{ $horario->nombreHorario }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Periodo escolar</span>
                                <strong>
                                    {{ $horario->periodo?->estacion ?? '-' }}
                                    {{ $horario->periodo?->anio ?? '' }}
                                </strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Carrera</span>
                                <strong>
                                    {{ $horario->grupo?->carrera?->nombreCarrera ?? '-' }}
                                </strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Grupo</span>
                                <strong>
                                    {{ $horario->grupo?->nombreGrupo ?? '-' }}
                                </strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Cuatrimestre</span>
                                <strong>
                                    {{ $horario->grupo?->cuatrimestre ? $horario->grupo->cuatrimestre . '°' : '-' }}
                                </strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Estado</span>
                                <strong class="{{ (int) $horario->estado === 1 ? 'texto-estado-activo' : 'texto-estado-inactivo' }}">
                                    {{ (int) $horario->estado === 1 ? 'Activo' : 'Inactivo' }}
                                </strong>
                            </div>

                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

</main>

<!-- CONFIGURACIÓN PARA JAVASCRIPT -->
<script>
    window.rutaDatosGenerales = @json(route('datos-generales.index'));
</script>

<script src="{{ asset('js/Horarios/DatosGeneralesHorario/gestion-DatosGeneralesHorario.js') }}" defer></script>

@endsection