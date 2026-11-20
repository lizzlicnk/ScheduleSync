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

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                mostrarErroresValidacion(
                    @json($errors->all()),
                    'No se pudo guardar el horario'
                );
            });
        </script>
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

                            // Comprobar si el horario ya tiene asignaturas registradas.
                            $horarioTieneAsignaciones = $horarioCreado
                            && $horario->detalles->isNotEmpty();

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

                            @if ($horarioCreado && $horarioActivo && !$horarioTieneAsignaciones)

                                <!-- AGREGAR ASIGNATURAS -->
                                <a href="{{ route('creacion-horario.index', ['agregar' => $horario->id_horario]) }}#contenedor-registro-horario"
                                    class="boton-accion boton-secundario"
                                    title="Agregar asignaturas al horario"
                                    aria-label="Agregar asignaturas al horario del grupo {{ $grupo->nombreGrupo }}">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    Agregar
                                </a>

                            @elseif ($horarioCreado)

                                <!-- EDITAR HORARIO FINALIZADO -->
                                @if ($horarioActivo && (int) $horario->borrador === 1)
                                    <a href="{{ route('horarios.edit', ['idHorario' => $horario->id_horario]) }}"
                                        class="boton-accion boton-secundario" title="Editar horario"
                                        aria-label="Editar el horario del grupo {{ $grupo->nombreGrupo }}">
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        Editar
                                    </a>
                                @endif

                                <!-- CONSULTAR HORARIO -->
                                <a href="{{ route('creacion-horario.index', ['consultar' => $horario->id_horario]) }}#panel-consulta"
                                    class="boton-accion boton-secundario" title="Consultar horario" aria-label="Consultar el horario del grupo {{ $grupo->nombreGrupo }}">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    Consultar
                                </a>

                                <!-- ELIMINAR HORARIO -->
                                @if ((int) auth()->user()->rol === 1)
                                    <form action="{{ route('horarios.destroy', $horario->id_horario) }}"
                                        method="POST" class="formulario-desactivar-horario" data-nombre="{{ $horario->nombreHorario }}"
                                        data-tiene-detalles="{{ $horario->detalles->isNotEmpty() ? 'true' : 'false' }}">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="boton-accion boton-eliminar" title="Eliminar horario" aria-label="Eliminar el horario del grupo {{ $grupo->nombreGrupo }}">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="texto-secundario">Pendiente</span>
                            @endif
                        </div>
                    </td>       
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
        <div class="franja-institucional"></div>

        <!-- CONTENIDO DEL PANEL -->
        <div class="contenido-formulario">
            <div class="encabezado-panel">
                <div>
                    <h2>Registro de horario académico</h2>
                    <p class="texto-secundario">
                        Selecciona un grupo y distribuye sus asignaturas dentro de la cuadrícula semanal.
                    </p>
                </div>

            </div>

            <!-- FORMULARIO DE REGISTRO -->
            <form id="formulario-registro-horario" action="{{ route('horarios.store') }}" method="POST" novalidate>
                @csrf
                
                <!-- DATOS GENERALES - SELECCIÓN DEL HORARIO REGISTRADO -->
                <div class="datos-generales-registro">
                    <div class="campo campo-completo">
                        <label for="horario-registro">
                            Nombre del horario académico
                        </label>

                        <select name="id_horario" id="horario-registro" data-url="{{ route('horarios.asignaturas', ['idHorario' => 'HORARIO_ID']) }}" required>
                            <option value=""> Selecciona un horario registrado </option>

                            @foreach ($horariosDisponibles as $horario)
                            <option value="{{ $horario->id_horario }}" @selected((string) $idHorarioAgregar === (string) $horario->id_horario) >
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

                                <!-- TÍTULO CON INFORMACIÓN DE AYUDA -->
                                <div class="titulo-con-ayuda">

                                    <h3>Asignaturas disponibles</h3>

                                    <!-- INFORMACIÓN DE AYUDA -->
                                    <div class="ayuda-horarios">

                                        <i class="fa-solid fa-circle-info icono-ayuda-horarios"></i>

                                        <div class="mensaje-ayuda-horarios">

                                            <strong>¿Cómo registrar un horario académico?</strong>

                                            <p>
                                                Primero, selecciona el horario académico en el apartado
                                                <strong>Nombre del horario académico</strong> Una vez
                                                seleccionado, se mostrarán las tarjetas de las asignaturas
                                                correspondientes al grupo.
                                            </p>

                                            <p>
                                                Antes de distribuir cada asignatura, selecciona el docente
                                                que la impartirá y el espacio de horas correspondiente.
                                            </p>

                                            <p>
                                                Después, mantén presionado el botón izquierdo del ratón
                                                sobre la tarjeta de la asignatura, arrástrala hasta el día
                                                y horario deseados en la cuadrícula semanal y suelta el
                                                botón para colocarla. Repite este procedimiento hasta
                                                distribuir las asignaturas.
                                            </p>

                                            <p>
                                                Puedes reorganizar las asignaturas en cualquier momento,
                                                incluso después de haberlas colocado en la cuadrícula.
                                                Para ello, mantén presionado el botón izquierdo del ratón
                                                sobre la tarjeta, arrástrala hasta el nuevo día y horario
                                                deseados y suelta el botón para actualizar su ubicación.
                                            </p>

                                            <p>
                                                También puedes modificar la duración de cada asignatura
                                                directamente en la cuadrícula, arrastrando el borde inferior
                                                de su tarjeta hacia arriba o hacia abajo para reducir o
                                                aumentar la cantidad de horas asignadas. Si deseas retirar
                                                una asignatura del horario, arrastra su tarjeta nuevamente
                                                al contenedor de asignaturas disponibles.
                                            </p>

                                            <p>
                                                <strong>Guardar borrador:</strong> permite guardar el
                                                avance del horario para continuar su elaboración
                                                posteriormente, sin finalizar el registro.
                                            </p>

                                            <p>
                                                <strong>Guardar horario:</strong> permite guardar
                                                definitivamente el horario cuando hayas terminado
                                                de distribuir las asignaturas en la cuadrícula.
                                            </p>

                                        </div>

                                    </div>

                                </div>

                                <p class="texto-secundario">
                                    Selecciona las asignaturas y los docentes disponibles para el grupo.
                                </p>

                            </div>

                        </div>

                        <div class="lista-asignaturas" id="lista-asignaturas-disponibles">
                            <p class="texto-secundario"> Selecciona un grupo para consultar sus asignaturas. </p>
                        </div>

                    </div>

                    <!-- CUADRÍCULA SEMANAL -->
                    <div class="panel-calendario-horario">

                        <div class="encabezado-panel">
                            <div>
                                <h3>Distribución semanal</h3>
                                <p class="texto-secundario"> Organiza las asignaturas en bloques de horas consecutivas. </p>       
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

                    <!-- Guardar como borrador -->
                    <button type="button" id="boton-guardar-borrador" class="boton-secundario" >
                        <i class="fa-solid fa-file-pen"></i>
                        Guardar borrador
                    </button>

                    <!-- Guardar horario definitivamente -->
                    <button type="button" id="boton-guardar-horario" class="boton-principal" disabled>
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar horario
                    </button>

                </div>
            </form>
        </div> 
    </section>


    <!--  PANEL PARA CONSULTAR HORARIOS -->
    <section class="panel-horarios panel-consulta-horarios" id="panel-consulta">
        <div class="encabezado-panel">
            <div>
                <h2>Consultar horario</h2>
                <p class="texto-secundario">
                    Selecciona un horario para consultar sus datos generales y asignaciones.
                </p>
            </div>
        </div>

        <div class="consulta-grid">

            <!-- COLUMNA IZQUIERDA: BUSCADOR Y RESULTADOS -->
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

                            <button type="button" class="resultado-consulta-item" data-resultado-horario="{{ $horario->id_horario }}" data-nombre-consulta="{{ mb_strtolower($horario->nombreHorario) }}" >
                                <span class="texto-resultado">
                                    <strong>{{ $horario->nombreHorario }}</strong>

                                    <small>
                                        {{ $horario->grupo?->carrera?->nombreCarrera ?? '-' }}
                                        ·
                                        {{ $horario->grupo?->nombreGrupo ?? '-' }}
                                    </small>
                                </span>

                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        @empty
                            <p class="texto-secundario mensaje-sin-resultados">
                                No hay horarios registrados para consultar.
                            </p>
                        @endforelse

                        <p class="texto-secundario mensaje-sin-resultados" id="sin-resultados-consulta" hidden >
                            No se encontraron horarios con ese nombre.
                        </p>

                    </div>
                </div>

            </div>

            <!-- COLUMNA DERECHA: DETALLE DEL HORARIO -->
            <div class="tarjeta-detalle" id="tarjeta-detalle">

                <!-- MENSAJE INICIAL -->
                <div class="detalle-vacio" id="detalle-vacio">

                    <div class="icono-detalle">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <h3>Información del horario</h3>
                    <p class="texto-secundario"> Selecciona un horario de los resultados para visualizar su información. </p>

                </div>

                <!-- INFORMACIÓN DE CADA HORARIO -->
                @foreach ($horariosRegistrados as $horario)
                    <div class="contenido-detalle-horario" data-detalle-horario="{{ $horario->id_horario }}" hidden >
                        <div class="encabezado-detalle">

                            <div class="icono-detalle">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>

                            <div>
                                <h3>{{ $horario->nombreHorario }}</h3>

                                <p class="correo-detalle">
                                    {{ $horario->grupo?->carrera?->nombreCarrera ?? '-' }}
                                </p>
                            </div>

                        </div>

                        <!-- DATOS GENERALES -->
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
                                    {{ $horario->grupo?->cuatrimestre
                                        ? $horario->grupo->cuatrimestre . '°'
                                        : '-' }}
                                </strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Estado</span>

                                <strong class="{{ (int) $horario->estado === 1
                                    ? 'texto-estado-activo'
                                    : 'texto-estado-inactivo' }}">
                                    {{ (int) $horario->estado === 1 ? 'Activo' : 'Inactivo' }}
                                </strong>
                            </div>

                        </div>

                        <!-- ASIGNACIONES REGISTRADAS -->
                        <div class="asignaciones-consulta">

                            <h4>Asignaturas registradas</h4>

                            @php
                                // Definir los días que se mostrarán en la consulta.
                                $diasSemana = [
                                    'Lunes',
                                    'Martes',
                                    'Miércoles',
                                    'Jueves',
                                    'Viernes',
                                    'Sábado'
                                ];

                                // Organizar las asignaciones del horario por día.
                                $asignacionesPorDia = $horario->detalles->sortBy('hora_inicio')->groupBy('dia');
                            @endphp

                            <div class="lista-dias-consulta">

                                @foreach ($diasSemana as $dia)

                                    <div class="grupo-dia-consulta">

                                        {{-- Nombre del día --}}
                                        <h5 class="titulo-dia-consulta">
                                            {{ $dia }}
                                        </h5>

                                        @php
                                            // Obtener las asignaturas correspondientes al día.
                                            $asignacionesDia = $asignacionesPorDia->get(
                                                $dia,
                                                collect()
                                            );
                                        @endphp

                                        @if ($asignacionesDia->isNotEmpty())

                                            <div class="lista-asignaturas-consulta">

                                                @foreach ($asignacionesDia as $detalle)

                                                    <div class="tarjeta-asignatura-consulta">

                                                        <!-- Nombre de la asignatura -->
                                                        <strong>
                                                            {{ $detalle->asignatura?->nombreAsignatura ?? 'Asignatura no disponible' }}
                                                        </strong>

                                                        <!-- Horario de la asignatura -->
                                                        <div class="detalle-asignatura-consulta">
                                                            <span>Horario</span>

                                                            <strong>
                                                                {{ \Carbon\Carbon::parse($detalle->hora_inicio)->format('H:i') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($detalle->hora_fin)->format('H:i') }}
                                                            </strong>
                                                        </div>

                                                        <!-- Nombre del docente -->
                                                        <div class="detalle-asignatura-consulta">
                                                            <span>Docente</span>

                                                            <strong>
                                                                @if ($detalle->docente?->usuario)
                                                                    {{ trim(
                                                                        $detalle->docente->usuario->nombreUsuario . ' ' .
                                                                        $detalle->docente->usuario->apellidoPaterno . ' ' .
                                                                        $detalle->docente->usuario->apellidoMaterno
                                                                    ) }}
                                                                @else
                                                                    No disponible
                                                                @endif
                                                            </strong>
                                                        </div>
                                                    </div>

                                                @endforeach
                                            </div>

                                        @else
                                            <!-- Mensaje cuando no hay  ese día -->
                                            <p class="texto-secundario dia-sin-asignaturas"> Sin asignaturas asignadas </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </section>

</main>

<script>
    // Enviar al JavaScript los bloques de indisponibilidad
    // confirmados correspondientes al periodo seleccionado.
    window.disponibilidadesDocentes = @json($disponibilidadesDocentes ?? []);
</script>


<!-- JAVASCRIPT DE LA GESTIÓN DE HORARIOS -->
<script src="{{ asset('js/Horarios/CreaciónHorario/gestion-CreaciónHorario.js') }}"></script>

    {{-- Cargar FullCalendar y las funcionalidades del calendario. --}}
    @vite('resources/js/Horario/fullcalendar-horarios.js')

@endsection
