@extends('layouts.DisenoPrincipal')

@section('title', 'Registrar disponibilidad académica | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/DisponibilidadAcademica/gestion-disponibilidadAcademica.css') }}">
@endpush

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

    <!-- MENSAJES DE VALIDACIÓN -->
    @if ($errors->any())
        <div class="alerta-disponibilidad alerta-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>

            <div>
                <strong>Revisa la siguiente información:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

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
                                <td>
                                    <div class="acciones-tabla">

                                        @if ($periodoSeleccionado)

                                            @if ($disponibilidadesRegistradas->where('id_docente', $usuario->docente->id_docente)->isNotEmpty())

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
                                                    <button type="button" class="boton-accion boton-eliminar" data-baja-disponibilidad="{{ $usuario->docente->id_docente }}"
                                                        data-periodo-disponibilidad="{{ $periodoSeleccionado }}" data-nombre-docente="{{ $usuario->nombreUsuario }}" >
                                                        <i class="fa-solid fa-trash"></i>
                                                        Eliminar
                                                    </button>
                                                @endif

                                            @else

                                                <!-- AGREGAR -->
                                                @if (in_array((int) auth()->user()->rol, [1, 3]))
                                                    <a
                                                        href="{{ route('disponibilidad-academica.create',
                                                            (int) auth()->user()->rol === 1
                                                                ? ['idDocente' => $usuario->docente->id_docente]
                                                                : []
                                                        ) }}"
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
    <!--
    <section class="panel-registro-disponibilidad">
        <div class="encabezado-panel">
            <div>
                <h2>Registrar disponibilidad</h2>

                <p class="texto-secundario">
                    Selecciona al docente, el periodo académico y los horarios en los que se encuentra disponible.
                </p>
            </div>
        </div>

        <form
            action="{{ route('disponibilidad-academica.store') }}"
            method="POST"
            id="formulario-disponibilidad"
            data-rol="{{ auth()->user()->rol }}"
            data-ruta-registro="{{ url('/disponibilidad-academica/registrar') }}"
        >
            @csrf

            <div class="formulario-grid">

                <!-- DOCENTE 
                <div class="campo campo-docente-disponibilidad">
                    <label for="id_docente">
                        <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
                        Docente
                        <span class="campo-requerido">*</span>
                    </label>

                    @if ((int) auth()->user()->rol === 1)

                        <select name="id_docente" id="id_docente" required>
                            <option value="">Selecciona un docente</option>

                            @foreach ($docentesRegistrados as $usuario)
                                @if ($usuario->docente)
                                    <option
                                        value="{{ $usuario->docente->id_docente }}"
                                        {{ old('id_docente') == $usuario->docente->id_docente ? 'selected' : '' }}
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

                <!-- PERIODO ACADÉMICO 
                <div class="campo campo-periodo-disponibilidad">
                    <label for="id_periodo">
                        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                        Periodo académico
                        <span class="campo-requerido">*</span>
                    </label>

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

            <!-- SELECCIÓN DE HORARIOS 
            <div class="seccion-horarios-disponibilidad">

                <div class="encabezado-panel">
                    <div>
                        <h2>Disponibilidad horaria</h2>

                        <p class="texto-secundario">
                            Selecciona los espacios de tiempo en los que el docente puede impartir clases.
                        </p>
                    </div>
                </div>

                <!-- LEYENDA DE COLORES 
                <div class="leyenda-disponibilidad">

                    <div class="elemento-leyenda">
                        <span class="color-disponible"></span>
                        <span>Horario seleccionado</span>
                    </div>

                    <div class="elemento-leyenda">
                        <span class="color-no-disponible"></span>
                        <span>Horario no seleccionado</span>
                    </div>

                </div>

                <!-- INSTRUCCIONES 
                <div class="ayuda-disponibilidad">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>

                    <p>
                        Selecciona y arrastra el cursor sobre el calendario para marcar un horario disponible. Puedes seleccionar varios intervalos en un mismo día. Para quitar un horario, haz clic sobre el intervalo seleccionado.
                    </p>
                </div>

                <!-- CALENDARIO 
                <div class="contenedor-calendario">
                    <div id="calendario-disponibilidad"></div>
                </div>

                <!-- HORARIOS SELECCIONADOS 
                <div class="resumen-disponibilidad">

                    <div class="encabezado-resumen-disponibilidad">
                        <h3>Horarios seleccionados</h3>

                        <span id="total-horarios-seleccionados">
                            0 horarios
                        </span>
                    </div>

                    <div
                        id="lista-horarios-seleccionados"
                        class="lista-horarios-seleccionados"
                    >
                        <p class="texto-secundario" id="sin-horarios-seleccionados">
                            Todavía no has seleccionado ningún horario.
                        </p>
                    </div>

                </div>

                <!-- CAMPOS OCULTOS GENERADOS POR JAVASCRIPT
                <div id="campos-disponibilidad"></div>

            </div>

            <!-- ACCIONES DEL FORMULARIO 
            <div class="acciones-registro-disponibilidad">

                <a
                    href="{{ route('disponibilidad-academica.index') }}"
                    class="boton-cancelar-disponibilidad"
                >
                    Cancelar
                </a>

                <button type="submit" class="boton-guardar-disponibilidad">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    Registrar disponibilidad
                </button>

            </div>

        </form>
    </section>
-->

    <!-- PANEL PARA CONSULTAR ASIGNATURAS SELECCIONADAS -->
    <section class="panel-disponibilidad panel-consulta-disponibilidad" id="panel-consulta">
        <div class="encabezado-panel">
            <div>
                <h2>Consultar asignaturas seleccionadas</h2>
                <p class="texto-secundario">
                    Consulta las asignaturas seleccionadas por el docente.
                </p>
            </div>
        </div>

        <div class="total-asignaturas">
            <div class="icono-total-asignaturas" aria-hidden="true">
                <i class="fa-solid fa-book-open"></i>
            </div>

            <div>
                <span>Total de asignaturas seleccionadas</span>
                <strong id="total-asignaturas-seleccionadas">
                    {{ isset($asignaturasSeleccionadas) ? $asignaturasSeleccionadas->count() : '-' }}
                </strong>
            </div>
        </div>

        <div class="consulta-grid">
            <div class="buscador-consulta">
                <div class="campo">
                    <label for="buscar-consulta-asignatura">
                        Nombre de la asignatura o carrera
                    </label>

                    <div class="entrada-busqueda">
                        <span aria-hidden="true">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>

                        <input
                            type="search"
                            id="buscar-consulta-asignatura"
                            placeholder="Escribe el nombre de una asignatura o carrera..."
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div class="resultados-consulta">
                    <h3>Resultados</h3>

                    <div id="resultados-consulta-asignatura" aria-live="polite">
                        @forelse (($asignaturasSeleccionadas ?? collect()) as $asignatura)
                            <button
                                type="button"
                                class="resultado-consulta-item"
                                data-resultado-asignatura="{{ $asignatura->id_asignatura }}"
                                data-nombre-consulta="{{ mb_strtolower($asignatura->nombreAsignatura) }}"
                                data-carrera-consulta="{{ mb_strtolower($asignatura->carrera->nombreCarrera ?? '') }}"
                            >
                                <span class="texto-resultado">
                                    <strong>
                                        {{ $asignatura->nombreAsignatura }} -
                                        {{ $asignatura->carrera->nombreCarrera ?? '-' }}
                                    </strong>
                                </span>
                            </button>
                        @empty
                            <p class="texto-secundario mensaje-sin-resultados">
                                -
                            </p>
                        @endforelse

                        <p
                            class="texto-secundario mensaje-sin-resultados"
                            id="sin-resultados-consulta-asignatura"
                            hidden
                        >
                            No se encontraron asignaturas con ese nombre o carrera.
                        </p>
                    </div> 
                </div>
            </div>

            <div class="tarjeta-detalle" id="tarjeta-detalle-asignatura">
                <div class="detalle-vacio" id="detalle-vacio-asignatura">
                    <div class="icono-detalle" aria-hidden="true">
                        <i class="fa-solid fa-book-open"></i>
                    </div>

                    <h3>Información de la asignatura</h3>

                    <p class="texto-secundario">
                        Selecciona una asignatura de los resultados para ver su información.
                    </p>
                </div>

                @foreach (($asignaturasSeleccionadas ?? collect()) as $asignatura)
                    <div
                        class="contenido-detalle-asignatura"
                        data-detalle-asignatura="{{ $asignatura->id_asignatura }}"
                        hidden
                        >
                        <div class="encabezado-detalle">
                            <div class="icono-detalle" aria-hidden="true">
                                <i class="fa-solid fa-book-open"></i>
                            </div>

                            <div>
                                <h3>{{ $asignatura->nombreAsignatura }}</h3>

                                <p class="texto-secundario">
                                    {{ $asignatura->carrera->nombreCarrera ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="lista-detalle">
                            <div class="fila-detalle">
                                <span>ID de asignatura</span>
                                <strong>{{ $asignatura->id_asignatura }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Clave</span>
                                <strong>{{ $asignatura->clave ?? '-' }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Nombre de la asignatura</span>
                                <strong>{{ $asignatura->nombreAsignatura }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Carrera</span>
                                <strong>{{ $asignatura->carrera->nombreCarrera ?? '-' }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Cuatrimestre</span>
                                <strong>{{ $asignatura->cuatrimestre ?? '-' }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Horas</span>
                                <strong>{{ $asignatura->cantidadHoras ?? '-' }}</strong>
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

<!-- BIBLIOTECA FULLCALENDAR -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<!-- FUNCIONAMIENTO DEL CALENDARIO -->
<script src="{{ asset('js/DisponibilidadAcademica/calendario-disponibilidadAcademica.js') }}" defer></script>

<!-- GESTIÓN DE LOS HORARIOS SELECCIONADOS -->
<script src="{{ asset('js/DisponibilidadAcademica/gestion-DisponibilidadAcademica.js') }}" defer></script>

@endsection
