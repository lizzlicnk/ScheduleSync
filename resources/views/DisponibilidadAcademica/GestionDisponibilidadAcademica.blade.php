
@extends('layouts.DisenoPrincipal')

@section('title', 'Registrar disponibilidad académica | Schedule-Sync')

@push('estilos')
    <!-- Estilos para el registro de disponibilidad académica -->
    <link rel="stylesheet" href="{{ asset('css/DisponibilidadAcademica/registrar-disponibilidad-academica.css') }}">
@endpush

@section('contenido')

@php
    // Construir el nombre completo del docente.
    $nombreCompleto = trim(
        $docenteEncontrado->usuario->nombreUsuario . ' ' .
        ($docenteEncontrado->usuario->apellidoPaterno ?? '') . ' ' .
        ($docenteEncontrado->usuario->apellidoMaterno ?? '')
    );
@endphp

<main class="pagina-registro-disponibilidad">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <p class="ruta-modulo">
                Inicio / Administración / Disponibilidad académica / Registrar
            </p>

            <h1>Registrar disponibilidad académica</h1>

            <p class="descripcion-modulo">
                Selecciona los días y horarios en los que te encuentras disponible
                para impartir clases durante el periodo académico.
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

    <!-- INFORMACIÓN DEL DOCENTE -->
    <section class="panel-registro-disponibilidad">

        <div class="encabezado-panel">
            <div>
                <h2>Información del docente</h2>

                <p class="texto-secundario">
                    Verifica tus datos antes de registrar tu disponibilidad.
                </p>
            </div>
        </div>

        <div class="datos-docente-disponibilidad">

            <div class="dato-docente">
                <span>Nombre del docente</span>
                <strong>{{ $nombreCompleto }}</strong>
            </div>

            <div class="dato-docente">
                <span>Matrícula</span>
                <strong>{{ $docenteEncontrado->matricula }}</strong>
            </div>

            <div class="dato-docente">
                <span>Tipo de docente</span>
                <strong>
                    {{ (int) $docenteEncontrado->tipoDocente === 1 ? 'PTC' : 'PA' }}
                </strong>
            </div>

        </div>

    </section>

    <!-- FORMULARIO DE DISPONIBILIDAD -->
    <form
        action="{{ (int) auth()->user()->rol === 1
            ? route('disponibilidad-academica.store', ['idDocente' => $docenteEncontrado->id_docente])
            : route('disponibilidad-academica.store') }}"
        method="POST"
        id="formulario-disponibilidad"
    >
        @csrf

        <!-- SELECCIÓN DEL PERIODO -->
        <section class="panel-registro-disponibilidad">

            <div class="encabezado-panel">
                <div>
                    <h2>Periodo académico</h2>

                    <p class="texto-secundario">
                        Selecciona el periodo en el que deseas registrar tu disponibilidad.
                    </p>
                </div>
            </div>

            <div class="campo-periodo-disponibilidad">

                <label for="id_periodo">
                    <i class="fa-solid fa-calendar-days"></i>
                    Periodo cuatrimestral
                </label>

                <select
                    name="id_periodo"
                    id="id_periodo"
                    required
                >
                    <option value="">Selecciona un periodo</option>

                    @foreach ($periodosRegistrados as $periodo)
                        <option
                            value="{{ $periodo->id_periodo }}"
                            {{ old('id_periodo') == $periodo->id_periodo ? 'selected' : '' }}
                        >
                            {{ $periodo->estacion }} {{ $periodo->anio }}
                        </option>
                    @endforeach

                </select>

            </div>

        </section>

        <!-- SELECCIÓN DE HORARIOS -->
        <section class="panel-registro-disponibilidad">

            <div class="encabezado-panel">
                <div>
                    <h2>Disponibilidad horaria</h2>

                    <p class="texto-secundario">
                        Selecciona con el cursor los espacios de tiempo en los que
                        puedes impartir clases.
                    </p>
                </div>
            </div>

            <!-- LEYENDA DE COLORES -->
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

            <!-- INSTRUCCIONES -->
            <div class="ayuda-disponibilidad">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>

                <p>
                    Selecciona y arrastra el cursor sobre la tabla para marcar
                    un horario disponible. Puedes seleccionar varios intervalos
                    en un mismo día. Para quitar un horario, haz clic sobre el
                    intervalo seleccionado.
                </p>
            </div>

            <!-- CALENDARIO -->
            <div class="contenedor-calendario-disponibilidad">
                <div id="calendario-disponibilidad"></div>
            </div>

            <!-- HORARIOS SELECCIONADOS -->
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

            <!-- CAMPOS OCULTOS GENERADOS POR JAVASCRIPT -->
            <div id="campos-disponibilidad"></div>

        </section>

        <!-- ACCIONES DEL FORMULARIO -->
        <div class="acciones-registro-disponibilidad">

            <a
                href="{{ route('disponibilidad-academica.index') }}"
                class="boton-cancelar-disponibilidad"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="boton-guardar-disponibilidad"
            >
                <i class="fa-solid fa-floppy-disk"></i>
                Guardar disponibilidad
            </button>

        </div>

    </form>

</main>

<!-- CONFIGURACIÓN PARA JAVASCRIPT -->
<script>
    window.rutaDisponibilidadAcademica =
        @json(url('/disponibilidad-academica'));

    window.tokenSeguridad = @json(csrf_token());
</script>

<!-- Biblioteca FullCalendar 
<script
    src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"
    defer>
</script>
 -->

{{-- Librería FullCalendar --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

{{-- Configuración y funcionamiento del calendario --}}
<script src="{{ asset('js/DisponibilidadAcademica/calendario-disponibilidad.js') }}" defer></script>

{{-- Gestión de los horarios seleccionados y del formulario --}}
<script src="{{ asset('js/DisponibilidadAcademica/gestion-CargaA.js') }}" defer></script>

@endsection
