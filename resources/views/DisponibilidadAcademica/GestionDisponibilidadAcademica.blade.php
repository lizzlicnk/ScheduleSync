@extends('layouts.DisenoPrincipal')

@section('title', 'Registrar disponibilidad académica | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/DisponibilidadAcademica/gestion-disponibilidadAcademica.css') }}">
@endpush

@php
    // Solo se preselecciona un docente si viene del botón "Agregar" o de un error de validación.
    $docentePreseleccionado = old('id_docente', $idDocenteSeleccionado ?? '');
@endphp

@section('contenido')
<main class="pagina-registro-disponibilidad pagina-disponibilidad">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <p class="ruta-modulo">
                Inicio / Administración / Disponibilidad académica
            </p>

            <h1>Disponibilidad académica</h1>

            <p class="descripcion-modulo">
                @if (auth()->user()->rol == 3)
                    Registra los días y horarios en los que te encuentras disponible para impartir clases durante el periodo académico.
                @else
                    Registra los días y horarios en los que los docentes se encuentran disponibles para impartir clases durante el periodo académico.
                @endif
            </p>
        </div>
    </header>



    <!-- FILTROS DE DISPONIBILIDAD -->
    <section class="panel-disponibilidad panel-filtros" id="contenedor-filtros" hidden>

        <div class="encabezado-panel">
            <div>
                <h2>Filtrar disponibilidades</h2>

                <p class="texto-secundario">
                    Selecciona el periodo académico que deseas consultar.
                </p>
            </div>

            <a href="{{ route('disponibilidad-academica.index') }}" class="boton-secundario" >
                Limpiar filtros
            </a>
        </div>

        <form action="{{ route('disponibilidad-academica.index') }}" method="GET" class="filtros-disponibilidad" >
            <div class="campo">
                <label for="filtro-periodo">Periodo académico</label>

                <select id="filtro-periodo" name="id_periodo" onchange="this.form.submit()" >
                    <option value="">Selecciona un periodo</option>

                    @foreach ($periodosRegistrados as $periodo)
                        <option value="{{ $periodo->id_periodo }}" {{ $periodoSeleccionado == $periodo->id_periodo ? 'selected' : '' }} >
                            {{ $periodo->estacion }} {{ $periodo->anio }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

    </section>

    <!-- TABLA DE DISPONIBILIDADES -->
    <section class="panel-disponibilidad">

        <div class="encabezado-panel encabezado-tabla">

            <div class="titulo-tabla">
               
                <h2>Disponibilidad académica registrada</h2>

                <p class="texto-secundario">
                    <span id="total-resultados">{{ $docentesRegistrados->count() }}</span>
                    docente(s) encontrado(s)
                </p>
            </div>

            <div class="botones-encabezado">

                <button type="button" class="boton-secundario" id="boton-filtros" aria-expanded="false" aria-controls="contenedor-filtros" >
                    <span aria-hidden="true">☷</span>
                    Filtros
                </button>

            </div>

            <div class="busqueda-tabla campo">
                <label for="buscar-disponibilidad">Buscar docente</label>

                <div class="entrada-busqueda">
                    <span aria-hidden="true">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <input type="search" id="buscar-disponibilidad" placeholder="Matrícula o nombre del docente..." autocomplete="off" >
                </div>
            </div>

        </div>

        <div class="tabla-contenedor">

            <table class="tabla-disponibilidad">

                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Matrícula</th>
                        <th scope="col">Nombre del docente</th>
                        <th scope="col">Asignaturas seleccionadas</th>
                        <th scope="col">Periodo</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody id="cuerpo-tabla-disponibilidad">

                    @forelse ($docentesRegistrados as $usuario)
                        @if ($usuario->docente)
                            <tr
                                data-id-docente="{{ $usuario->docente->id_docente }}"
                                data-matricula="{{ mb_strtolower($usuario->docente->matricula ?? '') }}"
                                data-nombre="{{ mb_strtolower(trim(
                                    $usuario->nombreUsuario . ' ' .
                                    ($usuario->apellidoPaterno ?? '') . ' ' .
                                    ($usuario->apellidoMaterno ?? '')
                                )) }}"
                            >

                                <!-- ID -->
                                <td>
                                    {{ $usuario->docente->id_docente }}
                                </td>

                                <!-- MATRÍCULA -->
                                <td>
                                    <span class="matricula-docente">
                                        {{ $usuario->docente->matricula ?? '-' }}
                                    </span>
                                </td>

                                <!-- NOMBRE DEL DOCENTE -->
                                <td>
                                    <div class="informacion-docente">
                                        <strong>
                                            {{ trim(
                                                $usuario->nombreUsuario . ' ' .
                                                ($usuario->apellidoPaterno ?? '') . ' ' .
                                                ($usuario->apellidoMaterno ?? '')
                                            ) ?: '-' }}
                                        </strong>
                                    </div>
                                </td>
                                                                                                                                    <!-- ME FALTA -->
                                <!-- ASIGNATURAS SELECCIONADAS -->
                                <td>
                                    -
                                </td>

                                <!-- PERIODO -->
                                <td>
                                    @if ($periodoSeleccionado)
                                        {{ $periodosRegistrados->firstWhere('id_periodo', $periodoSeleccionado)?->estacion ?? '-' }}
                                        {{ $periodosRegistrados->firstWhere('id_periodo', $periodoSeleccionado)?->anio ?? '' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- ACCIONES -->
                                <!-- ACCIONES -->
                                <td>
                                    <div class="acciones-tabla">

                                        @if ($periodoSeleccionado)

                                            @php
                                                // Comprueba si el docente tiene disponibilidad en el periodo.
                                                $tieneDisponibilidad = $disponibilidadesRegistradas
                                                    ->where('id_docente', $usuario->docente->id_docente)
                                                    ->isNotEmpty();
                                            @endphp

                                            @if ($tieneDisponibilidad)

                                                <!-- CONSULTAR -->
                                                <!-- CONSULTAR -->
                                                <a
                                                    href="{{ route('disponibilidad-academica.show', [
                                                        'idDocente' => $usuario->docente->id_docente,
                                                        'idPeriodo' => $periodoSeleccionado
                                                    ]) }}"
                                                    class="boton-accion boton-consultar"
                                                    aria-label="Consultar disponibilidad de {{ $usuario->nombreUsuario }}"
                                                >
                                                    <i class="fa-solid fa-eye"></i>
                                                    Consultar
                                                </a>

                                                <!-- MODIFICAR -->
                                                @if (in_array((int) auth()->user()->rol, [1, 3]))
                                                    <a
                                                        href="{{ route('disponibilidad-academica.edit', [
                                                            'idDocente' => $usuario->docente->id_docente,
                                                            'idPeriodo' => $periodoSeleccionado
                                                        ]) }}"
                                                        class="boton-accion boton-modificar"
                                                        aria-label="Modificar disponibilidad de {{ $usuario->nombreUsuario }}"
                                                    >
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                        Modificar
                                                    </a>
                                                @endif

                                                <!-- ELIMINAR -->
                                                @if ((int) auth()->user()->rol === 1)
                                                    <form
                                                        action="{{ route('disponibilidad-academica.destroy', [
                                                            'idDocente' => $usuario->docente->id_docente,
                                                            'idPeriodo' => $periodoSeleccionado
                                                        ]) }}"
                                                        method="POST"
                                                        class="formulario-eliminar-disponibilidad"
                                                        data-nombre-docente="{{ $usuario->nombreUsuario }}"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="boton-accion boton-eliminar"
                                                        >
                                                            <i class="fa-solid fa-trash"></i>
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endif

                                            @else

                                                <!-- AGREGAR -->
                                                @if (in_array((int) auth()->user()->rol, [1, 3]))
                                                    <a
                                                        href="{{ route('disponibilidad-academica.create', array_filter([
                                                            'idDocente' => (int) auth()->user()->rol === 1
                                                                ? $usuario->docente->id_docente
                                                                : null,
                                                            'id_periodo' => $periodoSeleccionado
                                                        ])) }}#registro-disponibilidad"
                                                        class="boton-accion boton-agregar"
                                                        aria-label="Agregar disponibilidad de {{ $usuario->nombreUsuario }}"
                                                    >
                                                        <i class="fa-solid fa-plus"></i>
                                                        Agregar
                                                    </a>
                                                @else
                                                    -
                                                @endif

                                            @endif

                                        @else
                                            -
                                        @endif

                                    </div>
                                </td>
                            </tr>

                        @endif

                    @empty
                        <tr id="fila-sin-disponibilidad">
                            <td colspan="6" class="tabla-vacia">
                                No hay docentes registrados.
                            </td>
                        </tr>
                    @endforelse

                    <!-- SIN RESULTADOS DE BÚSQUEDA -->
                    <tr id="fila-sin-resultados" hidden>
                        <td colspan="6" class="tabla-vacia">
                            No hay docentes que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="paginacion-disponibilidad">

            <span id="informacion-pagina">
                Página 1 de 1
            </span>

            <div class="controles-paginacion">

                <button type="button" class="boton-pagina" id="pagina-anterior" aria-label="Página anterior" disabled >
                    ‹
                </button>

                <button type="button" class="boton-pagina" id="pagina-siguiente" aria-label="Página siguiente" disabled >
                    ›
                </button>
            </div>
        </div>
    </section>

    
    <!-- FORMULARIO PARA REGISTRAR DISPONIBILIDAD -->
    <section class="panel-disponibilidad panel-registro-disponibilidad" id="registro-disponibilidad">
        <div class="franja-institucional"></div>
    
        <div class="contenido-formulario">
            <div class="encabezado-panel">
                <div>
                    <h2>Registrar disponibilidad académica</h2>

                    <p class="texto-secundario">
                        Registra los días y horarios en los que el docente no puede impartir clases durante el periodo académico.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <form
                id="formulario-disponibilidad"
                action="{{ route('disponibilidad-academica.store') }}"
                method="POST"
                novalidate
            >
                @csrf

                <!-- INFORMACIÓN DEL REGISTRO -->
                <div class="informacion-registro">

                    <!-- DOCENTE -->
                    <div class="campo">
                        <label for="id_docente">Docente <span class="obligatorio">*</span></label>

                        @if (in_array((int) auth()->user()->rol, [1, 2]))

                            <select name="id_docente" id="id_docente" autocomplete="off" required>

                                <option value="" {{ $docentePreseleccionado === '' || $docentePreseleccionado === null ? 'selected' : '' }}>
                                    Selecciona un docente
                                </option>


                                @foreach ($docentesRegistrados as $usuario)
                                    @if ($usuario->docente)
                                        <option
                                            value="{{ $usuario->docente->id_docente }}"
                                            {{ (string) $docentePreseleccionado === (string) $usuario->docente->id_docente ? 'selected' : '' }}
                                        >
                                            {{ trim(
                                                $usuario->nombreUsuario . ' ' .
                                                ($usuario->apellidoPaterno ?? '') . ' ' .
                                                ($usuario->apellidoMaterno ?? '')
                                            ) }}
                                            - {{ $usuario->docente->matricula }}
                                        </option>
                                    @endif
                                @endforeach

                            </select>

                        @else

                            <input
                                type="text"
                                value="{{ trim(
                                    ($docentesRegistrados->first()->nombreUsuario ?? '') . ' ' .
                                    ($docentesRegistrados->first()->apellidoPaterno ?? '') . ' ' .
                                    ($docentesRegistrados->first()->apellidoMaterno ?? '')
                                ) }}"
                                readonly
                            >

                        @endif
                    </div>

                    <!-- PERIODO ACADÉMICO -->
                    <div class="campo">
                        <label for="id_periodo">Periodo académico <span class="obligatorio">*</span></label>

                        <select name="id_periodo" id="id_periodo" required>
                            <option value="">Selecciona un periodo</option>

                            @foreach ($periodosRegistrados as $periodo)
                                <option
                                    value="{{ $periodo->id_periodo }}"
                                    {{ old('id_periodo', $periodoSeleccionado) == $periodo->id_periodo ? 'selected' : '' }}
                                >
                                    {{ $periodo->estacion }} {{ $periodo->anio }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <!-- CUADRÍCULA DE HORARIOS -->
                <div class="seccion-horarios-disponibilidad">

                    <div class="encabezado-panel">
                        <div class="titulo-horarios">

                            <div class="titulo-con-ayuda">
                                <h2>
                                    Registrar horarios no disponibles
                                    <span class="obligatorio">*</span>
                                </h2>

                                <!-- INFORMACIÓN DE AYUDA -->
                                <div class="ayuda-horarios">

                                    <i class="fa-solid fa-circle-info icono-ayuda-horarios"></i>

                                    <div class="mensaje-ayuda-horarios">
                                        <strong>¿Cómo registrar tu disponibilidad académica?</strong>

                                        <p>
                                            Haz clic sobre el cuadro correspondiente al día y la hora
                                            en los que no puedes impartir clases. El cuadro cambiará
                                            a color amarillo para indicar que ese horario no está disponible.
                                        </p>

                                        <p>
                                            Si vuelves a hacer clic sobre el mismo cuadro, cambiará
                                            nuevamente a color blanco, indicando que el horario está disponible.
                                        </p>
                                    </div>

                                </div>
                            </div>

                            <p class="texto-secundario">
                                Selecciona los bloques de tiempo en los que el docente no puede impartir clases.
                            </p>

                        </div>
                    </div>

                    <!-- REFERENCIA DE COLORES -->
                    <div class="referencia-horarios">
                        <div>
                            <span class="referencia-disponible"></span>
                            Disponible
                        </div>

                        <div>
                            <span class="referencia-no-disponible"></span>
                            No disponible
                        </div>
                    </div>

                    <!-- CUADRÍCULA -->
                    <div class="contenedor-cuadricula">

                        <table class="tabla-horarios">

                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Lunes</th>
                                    <th>Martes</th>
                                    <th>Miércoles</th>
                                    <th>Jueves</th>
                                    <th>Viernes</th>
                                    <th>Sábado</th>
                                </tr>
                            </thead>

                            <tbody>

                                @for ($hora = 7; $hora < 21; $hora++)

                                    <tr>

                                        <th>
                                            {{ sprintf('%02d:00', $hora) }}
                                            -
                                            {{ sprintf('%02d:00', $hora + 1) }}
                                        </th>

                                        @for ($dia = 1; $dia <= 6; $dia++)

                                            @if (!($dia == 6 && $hora >= 15))

                                                @php
                                                    $bloque = $dia . '-' . $hora;
                                                    $seleccionado = in_array(
                                                        $bloque,
                                                        old('bloques_no_disponibles', [])
                                                    );
                                                @endphp

                                                <td>
                                                    <button
                                                        type="button"
                                                        class="bloque-horario {{ $seleccionado ? 'bloque-no-disponible' : '' }}"
                                                        data-bloque="{{ $bloque }}"
                                                        aria-pressed="{{ $seleccionado ? 'true' : 'false' }}"
                                                        aria-label="Día {{ $dia }}, {{ $hora }}:00 a {{ $hora + 1 }}:00"
                                                    >
                                                        {{ $seleccionado ? 'No disponible' : 'Disponible' }}
                                                    </button>
                                                </td>

                                            @endif

                                        @endfor
                                    </tr>

                                @endfor

                            </tbody>
                        </table>

                    </div>

                    <!-- BLOQUES SELECCIONADOS -->
                    <div id="bloques-seleccionados">

                        @foreach (old('bloques_no_disponibles', []) as $bloque)
                            <input
                                type="hidden"
                                name="bloques_no_disponibles[]"
                                value="{{ $bloque }}"
                                data-input-bloque="{{ $bloque }}"
                            >
                        @endforeach

                    </div>

                    <p class="texto-ayuda-horarios">
                        Haz clic sobre un bloque para cambiar su estado. Los bloques marcados en color amarillo representan horarios en los que el docente no está disponible.
                    </p>

                </div>

                <!-- ASIGNATURAS PREFERIDAS -->
                <div class="encabezado-panel encabezado-asignaturas">
                    <div>
                        <h2>Asignaturas <span class="obligatorio">*</span></h2>

                        <p class="texto-secundario">
                            Selecciona las asignaturas que te gustaría impartir durante el periodo seleccionado.
                        </p>
                    </div>
                </div>

                <div class="contenedor-carreras-preferidas">

                    @forelse ($asignaturasPorCarrera as $grupoCarrera)

                        @php
                            $carrera = $grupoCarrera['carrera'];
                        @endphp

                        <details class="carrera-preferida">

                            <summary class="encabezado-carrera-preferida">
                                <span>
                                    {{ $carrera->claveCarrera }} - {{ $carrera->nombreCarrera }}
                                </span>

                                <i class="fa-solid fa-chevron-down"></i>
                            </summary>

                            <div class="contenido-carrera-preferida">

                                @foreach ($grupoCarrera['cuatrimestres'] as $grupoCuatrimestre)

                                    <div class="cuatrimestre-preferido">

                                        <h4>
                                            {{ $grupoCuatrimestre['cuatrimestre'] }}°
                                            cuatrimestre
                                        </h4>

                                        <div class="lista-asignaturas-preferidas">

                                            @foreach ($grupoCuatrimestre['asignaturas'] as $asignatura)

                                                <label class="opcion-asignatura-preferida">

                                                    <input
                                                        type="checkbox"
                                                        name="asignaturas[]"
                                                        value="{{ $asignatura->id_asignatura }}"
                                                        @checked(
                                                            in_array(
                                                                $asignatura->id_asignatura,
                                                                old('asignaturas', $asignaturasSeleccionadas ?? [])
                                                            )
                                                        )
                                                    >

                                                    <span>
                                                        {{ $asignatura->nombreAsignatura }}
                                                    </span>

                                                </label>

                                            @endforeach

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </details>

                    @empty

                        <p class="mensaje-sin-asignaturas">
                            No hay asignaturas disponibles para las carreras y cuatrimestres
                            registrados en el periodo seleccionado.
                        </p>

                    @endforelse

                </div>


                <!-- ACCIONES -->
                <div class="acciones-disponibilidad">

                    <button
                        type="reset"
                        class="boton-secundario"
                    >
                        Limpiar formulario
                    </button>

                    <!--
                    <button
                        type="submit"
                        name="accion"
                        value="borrador"
                        class="boton-guardar-borrador"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar borrador
                    </button> -->

                    <button
                        type="submit"
                        name="accion"
                        value="entregar"
                        class="boton-entregar-disponibilidad"
                    >
                        <i class="fa-solid fa-check"></i>
                        Entregar disponibilidad
                    </button>

                </div>

            </form>
        </div>
    </section>

    <!-- PANEL PARA CONSULTAR DISPONIBILIDAD -->
    <section class="panel-disponibilidad panel-consulta-disponibilidad" id="panel-consulta">

        <!-- ENCABEZADO -->
        <div class="encabezado-panel">
            <div>
                <h2>Consultar disponibilidad</h2>

                @if (in_array((int) auth()->user()->rol, [1, 2], true))
                    <p class="texto-secundario">
                        Busca por nombre o matrícula para consultar la disponibilidad académica del docente.
                    </p>
                @else
                    <p class="texto-secundario">
                        Consulta tu disponibilidad académica y las asignaturas que deseas impartir.
                    </p>
                @endif
            </div>
        </div>

        <!-- CONTENEDOR DE CONSULTA -->
        <div class="consulta-grid">

            <!-- BUSCADOR Y RESULTADOS -->
            <div class="buscador-consulta">

                <div class="campo">
                    <label for="buscar-consulta">Nombre del docente</label>

                    <div class="entrada-busqueda">
                        <span aria-hidden="true">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>

                        <input
                            type="search"
                            id="buscar-consulta"
                            placeholder="Escribe un nombre o matrícula para encontrar al docente..."
                            autocomplete="off"
                        >
                    </div>
                </div>

                <!-- RESULTADOS -->
                <div class="resultados-consulta">
                    <h3>Resultados</h3>

                    <div id="resultados-consulta">

                        @forelse ($docentesConsulta as $docenteConsulta)

                            <button
                                type="button"
                                class="resultado-consulta-item"
                                data-resultado-docente="{{ $docenteConsulta['id_docente'] }}"
                                data-nombre-consulta="{{ mb_strtolower($docenteConsulta['nombre']) }}"
                                data-matricula-consulta="{{ mb_strtolower($docenteConsulta['matricula']) }}"
                                aria-pressed="{{ (int) $idDocenteConsulta === (int) $docenteConsulta['id_docente'] ? 'true' : 'false' }}"
                            >
                                <span class="texto-resultado">
                                    <strong>
                                        {{ $docenteConsulta['nombre'] }}
                                    </strong>

                                    <small>
                                        Matrícula: {{ $docenteConsulta['matricula'] }}
                                    </small>
                                </span>
                            </button>

                        @empty

                            <p class="texto-secundario mensaje-sin-resultados">
                                No hay docentes con disponibilidad registrada en este periodo.
                            </p>

                        @endforelse

                        <!-- SIN RESULTADOS DE BÚSQUEDA -->
                        <p
                            class="texto-secundario mensaje-sin-resultados"
                            id="sin-resultados-consulta"
                            hidden
                        >
                            No se encontraron docentes con ese nombre o matrícula.
                        </p>

                    </div>
                </div>

            </div>

            <!-- TARJETA DE DETALLE -->
            <div class="tarjeta-detalle" id="tarjeta-detalle">

                <!-- MENSAJE INICIAL -->
                <div class="detalle-vacio" id="detalle-vacio">

                    <div class="icono-detalle" aria-hidden="true">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <h3>Información de disponibilidad</h3>

                    <p class="texto-secundario">
                        Selecciona un docente de los resultados para consultar
                        sus horarios no disponibles y las asignaturas seleccionadas.
                    </p>

                </div>

                <!-- INFORMACIÓN DE CADA DOCENTE -->
                @foreach ($docentesConsulta as $docenteConsulta)

                    <div
                        class="contenido-detalle-docente"
                        data-detalle-docente="{{ $docenteConsulta['id_docente'] }}"
                        @if ((int) $idDocenteConsulta !== (int) $docenteConsulta['id_docente'])
                            hidden
                        @endif
                    >

                        <!-- ENCABEZADO DEL DOCENTE -->
                        <div class="encabezado-detalle">

                            <div class="icono-detalle" aria-hidden="true">
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <div>
                                <h3>{{ $docenteConsulta['nombre'] }}</h3>

                                <p class="correo-detalle">
                                    Matrícula: {{ $docenteConsulta['matricula'] }}
                                </p>
                            </div>

                        </div>

                        <!-- PERIODO ACADÉMICO -->
                        <div class="lista-detalle">

                            <div class="fila-detalle">
                                <span>Periodo académico</span>

                                <strong>
                                    {{ $periodosRegistrados->firstWhere('id_periodo', $periodoSeleccionado)?->estacion ?? '-' }}
                                    {{ $periodosRegistrados->firstWhere('id_periodo', $periodoSeleccionado)?->anio ?? '' }}
                                </strong>
                            </div>

                        </div>

                        <!-- HORARIOS NO DISPONIBLES -->
                        <div class="seccion-detalle-consulta">

                            <h4>
                                <i class="fa-regular fa-clock"></i>
                                Horarios no disponibles
                            </h4>

                            <div class="lista-horarios-consulta">

                                @foreach ($docenteConsulta['horarios'] as $dia => $horarios)

                                    <div class="fila-horario-consulta">

                                        <strong class="dia-consulta">
                                            {{ $dia }}
                                        </strong>

                                        <div class="horas-consulta">

                                            @forelse ($horarios as $horario)

                                                <span class="hora-consulta">
                                                    {{ $horario['hora_inicio'] }} -
                                                    {{ $horario['hora_fin'] }}
                                                </span>

                                            @empty

                                                <span class="sin-horarios-consulta">
                                                    Sin horarios no disponibles
                                                </span>

                                            @endforelse

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                        <!-- ASIGNATURAS SELECCIONADAS -->
                        <div class="seccion-detalle-consulta">

                            <div class="encabezado-asignaturas-consulta">

                                <h4>
                                    <i class="fa-solid fa-book"></i>
                                    Asignaturas seleccionadas
                                </h4>

                                <span class="contador-asignaturas-consulta">
                                    {{ $docenteConsulta['asignaturas']->count() }}
                                    {{ $docenteConsulta['asignaturas']->count() === 1 ? 'materia seleccionada' : 'materias seleccionadas' }}
                                </span>

                            </div>

                            <div class="lista-asignaturas-consulta">

                                @forelse ($docenteConsulta['asignaturas'] as $asignatura)

                                    <article class="tarjeta-asignatura-consulta">

                                        <strong>
                                            {{ $asignatura->nombreAsignatura }}
                                        </strong>

                                        <div class="detalle-asignatura-consulta">
                                            <span>Carrera</span>

                                            <strong>
                                                {{ $asignatura->carrera->nombreCarrera ?? '-' }}
                                            </strong>
                                        </div>

                                        <div class="detalle-asignatura-consulta">
                                            <span>Cuatrimestre</span>

                                            <strong>
                                                {{ $asignatura->cuatrimestre }}°
                                            </strong>
                                        </div>

                                    </article>

                                @empty

                                    <p class="texto-secundario mensaje-sin-resultados">
                                        El docente no ha seleccionado asignaturas.
                                    </p>

                                @endforelse

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

        <!-- DATOS PARA JAVASCRIPT -->
        <script>
            window.docentesDisponibilidadConsulta = @json($docentesConsulta->values());
            window.idDocenteConsultaInicial = @json($idDocenteConsulta);
        </script>

    </section>
</main>

<!-- CONFIGURACIÓN PARA JAVASCRIPT -->
<script>
    window.rutaDisponibilidadAcademica =
        @json(url('/disponibilidad-academica'));

    window.tokenSeguridad = @json(csrf_token());

    // Ajustar la ruta de registro según el docente seleccionado.
    document.addEventListener('DOMContentLoaded', function () {

        const formulario = document.getElementById('formulario-disponibilidad');

        if (formulario) {

            formulario.addEventListener('submit', function () {

                const rol = formulario.dataset.rol;
                const rutaRegistro = formulario.dataset.rutaRegistro;

                // El administrador debe enviar el ID del docente seleccionado.
                if (parseInt(rol) === 1) {

                    const idDocente = document.getElementById('id_docente').value;

                    formulario.action = `${rutaRegistro}/${idDocente}`;

                }

            });

        }

    });
</script>

<!-- MODAL DE CAMPOS OBLIGATORIOS -->
<div class="modal-validacion" id="modal-validacion" hidden>
    <div class="contenido-modal-validacion">

        <div class="icono-modal-validacion">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>

        <h2>Información incompleta</h2>

        <p>
            Te faltan datos por completar. Revisa los campos obligatorios
            antes de entregar tu disponibilidad académica.
        </p>

        <button type="button" id="cerrar-modal-validacion">
            Entendido
        </button>

    </div>
</div>

<!-- GESTIÓN DE LOS HORARIOS SELECCIONADOS -->
<script src="{{ asset('js/DisponibilidadAcademica/gestion-DisponibilidadAcademica.js') }}" defer></script>

<!-- REGISTRO DE DISPONIBILIDAD -->
<script src="{{ asset('js/DisponibilidadAcademica/registro-DisponibilidadAcademica.js') }}" defer></script>

@endsection
