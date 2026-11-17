@extends('layouts.DisenoPrincipal')

@section('title', 'Gestión de horarios | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Horarios/CreaciónHorario/gestion-creaciónHorario.css') }}">
@endpush

@section('contenido')

<main class="pagina-creacion-horarios">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <p class="ruta-modulo">
                Inicio / Administración / Creación de horarios
            </p>

            <h1>Gestión de horarios académicos</h1>

            <p class="descripcion-modulo">
                Organiza y distribuye las asignaturas de cada grupo
                dentro de su horario académico.
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


    <!-- FILTROS DE GRUPOS Y HORARIOS -->
    <section class="panel-horarios panel-filtros" id="contenedor-filtros" hidden>

        <div class="encabezado-panel">
            <div>
                <h2>Filtrar grupos y horarios</h2>
                <p class="texto-secundario">
                    Selecciona los criterios para encontrar los grupos y sus horarios académicos.
                </p>
            </div>

            <button type="button" class="boton-secundario" id="limpiar-filtros">
                <i class="fa-solid fa-eraser" aria-hidden="true"></i>
                Limpiar filtros
            </button>
        </div>

        <div class="filtros-horarios">

            <!-- FILTRO POR PERIODO -->
            <div class="campo">
                <label for="filtro-periodo">Periodo escolar</label>
                <select id="filtro-periodo">
                    <option value="">Todos los periodos</option>

                    @foreach ($periodosDisponibles as $periodo)
                        <option value="{{ $periodo->id_periodo }}">
                            {{ $periodo->estacion }} {{ $periodo->anio }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- FILTRO POR CUATRIMESTRE -->
            <div class="campo">
                <label for="filtro-cuatrimestre">Cuatrimestre</label>
                <select id="filtro-cuatrimestre">
                    <option value="">Todos los cuatrimestres</option>

                    @for ($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">
                            {{ $i }}.° cuatrimestre
                        </option>
                    @endfor
                </select>
            </div>

            <!-- FILTRO POR CARRERA -->
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

            <!-- FILTRO POR CREACIÓN DEL HORARIO -->
            <div class="campo">
                <label for="filtro-horario-creado"> Datos generales del horario</label>
                <select id="filtro-horario-creado">
                    <option value="">Todos</option>
                    <option value="1">Con horario</option>
                    <option value="0">Sin horario</option>
                </select>
            </div>

            <!-- FILTRO POR ESTADO DEL HORARIO -->
            <div class="campo">
                <label for="filtro-estado-horario">Estado del horario</label>
                <select id="filtro-estado-horario">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

        </div>
    </section>

    <!-- TABLA DE GRUPOS Y HORARIOS -->
    <section class="panel-horarios">

        <!-- ENCABEZADO DE LA TABLA -->
        <div class="encabezado-panel encabezado-tabla">

            <div class="titulo-tabla">
                <h2>Grupos registrados</h2>

                <p class="texto-secundario">
                    <span id="total-resultados-horarios">
                        {{ $gruposRegistrados->count() }}
                    </span>
                    grupo(s) encontrado(s).
                </p>
            </div>

            <div class="botones-encabezado">

                <!-- BOTÓN DE FILTROS -->
                <button type="button" class="boton-secundario" id="boton-filtros" aria-expanded="false" aria-controls="contenedor-filtros">
                    <span aria-hidden="true">☷</span>
                    Filtros
                </button>

                <!-- BOTÓN PARA MOSTRAR EL REGISTRO -->
                <button type="button" class="boton-principal" id="boton-mostrar-registro" aria-expanded="false" aria-controls="contenedor-registro-horario">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    Crear horario
                </button>

            </div>

            <!-- BÚSQUEDA -->
            <div class="busqueda-tabla campo">
                <label for="buscar-horario">Buscar grupo u horario</label>

                <div class="entrada-busqueda">
                    <span aria-hidden="true">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <input type="search" id="buscar-horario" placeholder="Nombre del grupo, carrera u horario..." autocomplete="off">
                </div>
            </div>
        </div>

        <!-- CONTENEDOR DE LA TABLA -->
        <div class="tabla-contenedor">

            <table class="tabla-horarios">

                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nombre del grupo</th>
                        <th scope="col">Carrera</th>
                        <th scope="col">Cuatrimestre</th>
                        <th scope="col">Periodo escolar</th>
                        <th scope="col">Horario</th>
                        <th scope="col">Estado del horario</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody id="cuerpo-tabla-horarios">

                    @forelse ($gruposRegistrados as $grupo)

                        @php
                            $carrera = $grupo->carrera;
                            $periodo = $grupo->periodo;
                            $horario = $grupo->horarios->first();
                            $horarioCreado = $horario !== null;
                            $horarioActivo = $horarioCreado
                                && (int) $horario->estado === 1;
                        @endphp

                        <tr
                            data-nombre="{{ mb_strtolower(($grupo->nombreGrupo ?? '') . ' ' . ($horario?->nombreHorario ?? '')) }}"
                            data-grupo="{{ mb_strtolower($grupo->nombreGrupo ?? '') }}"
                            data-periodo="{{ $grupo->id_periodo }}"
                            data-cuatrimestre="{{ $grupo->cuatrimestre }}"
                            data-carrera="{{ $grupo->id_carrera }}"
                            data-nombre-carrera="{{ mb_strtolower($carrera?->nombreCarrera ?? '') }}"
                            data-horario-creado="{{ $horarioCreado ? '1' : '0' }}"
                            data-estado-horario="{{ $horarioCreado ? (string) $horario->estado : '' }}">

                            <!-- IDENTIFICADOR -->
                            <td>
                                {{ $grupo->id_grupo }}
                            </td>

                            <!-- NOMBRE DEL GRUPO -->
                            <td>
                                <div class="informacion-horario-tabla">
                                    <strong>{{ $grupo->nombreGrupo }}</strong>
                                </div>
                            </td>

                            <!-- CARRERA -->
                            <td>
                                {{ $carrera?->nombreCarrera ?? '-' }}
                            </td>

                            <!-- CUATRIMESTRE -->
                            <td>
                                {{ $grupo->cuatrimestre }}.°
                            </td>

                            <!-- PERIODO ESCOLAR -->
                            <td>
                                {{ $periodo?->estacion ?? '-' }}
                                {{ $periodo?->anio ?? '' }}
                            </td>

                            <!-- EXISTENCIA DEL HORARIO -->
                            <td>
                                @if ($horarioCreado)
                                    <div class="informacion-horario-tabla">
                                        <strong>{{ $horario->nombreHorario }}</strong>
                                    </div>
                                @else
                                    <span class="texto-secundario">
                                        Sin horario creado
                                    </span>
                                @endif
                            </td>

                            <!-- ESTADO DEL HORARIO -->
                            <td>
                                @if ($horarioCreado)
                                    <span class="estado-horario {{ $horarioActivo ? 'estado-activo' : 'estado-inactivo' }}">
                                        {{ $horarioActivo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                @else
                                    <span class="texto-secundario">-</span>
                                @endif
                            </td>

                            <!-- ACCIONES -->
                            <td>
                                <div class="acciones-tabla">
                                    @if ($horarioCreado && $horarioActivo)
                                        <a href="{{ route('creacion-horario.index', ['consultar' => $horario->id_horario]) }}" 
                                            class="boton-accion boton-consultar" title="Gestionar horario"
                                            aria-label="Gestionar horario del grupo {{ $grupo->nombreGrupo }}">
                                            <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                            Gestionar
                                        </a>
                                    @elseif ($horarioCreado)
                                        <span class="texto-secundario">
                                            Horario inactivo
                                        </span>
                                    @else
                                        <span class="texto-secundario">
                                            Pendiente
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="fila-sin-horarios">
                            <td colspan="8" class="tabla-vacia">
                                No hay grupos registrados.
                            </td>
                        </tr>
                    @endforelse

                    <!-- MENSAJE CUANDO NO HAY COINCIDENCIAS -->
                    <tr id="fila-sin-resultados-horarios" hidden>
                        <td colspan="8" class="tabla-vacia">
                            No hay grupos que coincidan con la búsqueda o los filtros seleccionados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>


        <!-- PAGINACIÓN -->
        <div class="paginacion-horarios">

            <span id="informacion-pagina-horarios"> Página 1 de 1 </span>

            <div class="controles-paginacion">

                <button type="button" class="boton-pagina" id="pagina-anterior-horarios" aria-label="Página anterior" disabled>
                    ‹
                </button>

                <button type="button" class="boton-pagina" id="pagina-siguiente-horarios" aria-label="Página siguiente" disabled>
                    ›
                </button>
            </div>
        </div>

    </section>

    <!-- REGISTRO DE HORARIO ACADÉMICO -->
    <section class="panel-horarios panel-registro-horario" id="contenedor-registro-horario" >
        <!-- FRANJA INSTITUCIONAL -->
        <div class="franja-institucional"></div>

        <!-- CONTENIDO DEL PANEL -->
        <div class="contenido-formulario">

            <!-- ENCABEZADO DEL REGISTRO -->
            <div class="encabezado-panel">

                <div>
                    <h2>Registro de horario académico</h2>

                    <p class="texto-secundario">
                        Selecciona un grupo y distribuye sus asignaturas
                        dentro de la cuadrícula semanal.
                    </p>
                </div>

            </div>

            <!-- FORMULARIO DE REGISTRO -->
            <form id="formulario-registro-horario" action="{{ route('horarios.store') }}" method="POST">

                @csrf
                
                <!-- DATOS GENERALES - SELECCIÓN DEL HORARIO REGISTRADO -->
                <div class="datos-generales-registro">

                    <div class="campo campo-completo">
                        <label for="horario-registro">
                            Nombre del horario académico
                        </label>

                        <select name="id_horario" id="horario-registro" data-url="{{ route('horarios.asignaturas', ['idHorario' => 'HORARIO_ID']) }}" required>
                            <option value="">
                                Selecciona un horario registrado
                            </option>

                            @foreach ($horariosDisponibles as $horario)
                                <option value="{{ $horario->id_horario }}">
                                    {{ $horario->nombreHorario }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                </div>

                <!-- ÁREA DE DISTRIBUCIÓN DE ASIGNATURAS -->
                <div class="area-distribucion-horario">

                    <!-- ASIGNATURAS DISPONIBLES -->
                    <div class="panel-asignaturas-disponibles">

                        <div class="encabezado-panel">
                            <div>
                                <h3>Asignaturas disponibles</h3>

                                <p class="texto-secundario"> Selecciona las asignaturas y los docentes disponibles para el grupo. </p>
                            </div>
                        </div>

                        <div class="lista-asignaturas" id="lista-asignaturas-disponibles">

                            <p class="texto-secundario">
                                Selecciona un grupo para consultar sus asignaturas.
                            </p>

                        </div>

                    </div>

                    <!-- CUADRÍCULA SEMANAL -->
                    <div class="panel-calendario-horario">

                        <div class="encabezado-panel">
                            <div>
                                <h3>Distribución semanal</h3>

                                <p class="texto-secundario">
                                    Organiza las asignaturas en bloques de horas consecutivas.
                                </p>
                                
                            </div>

                            <!-- BOTÓN PARA MOSTRAR U OCULTAR EL SÁBADO -->
                            <div class="acciones-calendario espacio-inferior-calendario">
                                <button type="button" class="boton-secundario" id="boton-sabado" aria-expanded="false" aria-controls="calendario-horarios">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    Mostrar sábado
                                </button>
                            </div>
                        </div>

                        <div class="contenedor-calendario">
                            <div id="calendario-horarios"></div>
                        </div>

                    </div>

                </div>

                <!-- CONTENEDOR DE LOS DETALLES QUE SE ENVIARÁN AL CONTROLADOR -->
                <div id="detalles-horario-registro"></div>

                <!-- ACCIONES DEL REGISTRO -->
                <div class="acciones-registro-horario">

                    <button type="button" class="boton-secundario" id="boton-cancelar-registro">
                        Cancelar
                    </button>

                    <button type="submit" class="boton-principal" id="boton-guardar-horario" disabled>
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                        Guardar horario
                    </button>

                </div>

            </form>
        </div> 
    </section>

</main>


<!-- JAVASCRIPT DE LA GESTIÓN DE HORARIOS -->
<script src="{{ asset('js/Horarios/CreaciónHorario/gestion-CreaciónHorario.js') }}"></script>

@endsection
