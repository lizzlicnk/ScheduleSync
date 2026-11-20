
@extends('layouts.DisenoPrincipal')

@section('titulo', 'Editar horario académico | Schedule-Sync')

@push('estilos')
    {{-- Estilos de la edición de horarios. --}}
    <link rel="stylesheet" href="{{ asset('css/Horarios/CreaciónHorario/editar-creaciónHorario.css') }}">
@endpush

@section('contenido')

<div class="pagina-editar-horario">

    <section class="panel-editar-horario">

        {{-- ENCABEZADO DE LA EDICIÓN --}}
        <div class="encabezado-editar-horario">

            <div>
                <h1>Editar horario académico</h1>

                <p>
                    Modifica las asignaturas, docentes y bloques del horario seleccionado.
                </p>
            </div>

            <a href="{{ route('creacion-horario.index') }}" class="boton-regresar-horario">
                <i class="fa-solid fa-arrow-left"></i>
                Regresar
            </a>

        </div>


        {{-- MOSTRAR ERRORES DE VALIDACIÓN --}}
        @if ($errors->any())

            <div class="alerta-edicion-horario alerta-error-horario">

                <strong>Revisa los siguientes datos:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        @endif


        {{-- MOSTRAR ERRORES GENERALES --}}
        @if (session('error'))

            <div class="alerta-edicion-horario alerta-error-horario">
                {{ session('error') }}
            </div>

        @endif


        {{-- INFORMACIÓN GENERAL DEL HORARIO --}}
        <div class="informacion-edicion-horario">

            <h2 class="titulo-seccion-horario">

                <span class="icono-seccion-horario">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>

                <span>Información del horario</span>

            </h2>


            <div class="datos-generales-horario">

                <div class="campo-informacion-horario">

                    <span>Carrera</span>

                    <strong>
                        {{ $horario->grupo->carrera->nombreCarrera ?? 'No disponible' }}
                    </strong>

                </div>


                <div class="campo-informacion-horario">

                    <span>Grupo</span>

                    <strong>
                        {{ $horario->grupo->nombreGrupo ?? 'No disponible' }}
                    </strong>

                </div>


                <div class="campo-informacion-horario">

                    <span>Cuatrimestre</span>

                    <strong>
                        {{ $horario->grupo->cuatrimestre ?? 'No disponible' }}
                    </strong>

                </div>


                <div class="campo-informacion-horario">

                    <span>Periodo escolar</span>

                    <strong>
                        {{ $horario->periodo->estacion ?? '' }}
                        {{ $horario->periodo->anio ?? '' }}
                    </strong>

                </div>

            </div>

        </div>


        {{-- FORMULARIO DE EDICIÓN --}}
        <form id="formulario-registro-horario"
            action="{{ route('horarios.update', $horario->id_horario) }}"
            method="POST"
            data-modo="editar"
            novalidate>

            @csrf
            @method('PUT')


            {{-- IDENTIFICADOR DEL HORARIO --}}
            <input type="hidden"
                name="id_horario"
                value="{{ $horario->id_horario }}">


            {{-- SELECTOR UTILIZADO POR EL JAVASCRIPT --}}
            <select id="horario-registro"
                class="d-none"
                data-url="{{ route('horarios.asignaturas', ['idHorario' => 'HORARIO_ID']) }}">

                <option value="{{ $horario->id_horario }}" selected>
                    {{ $horario->id_horario }}
                </option>

            </select>


            {{-- DISTRIBUCIÓN DE ASIGNATURAS Y CALENDARIO --}}
            <section class="panel-distribucion-horario">


                {{-- ENCABEZADO DE DISTRIBUCIÓN --}}
                <div class="encabezado-distribucion-horario">

                    <span class="icono-seccion-horario">
                        <i class="fa-solid fa-table-cells-large"></i>
                    </span>

                    <div class="texto-encabezado-distribucion">

                        <h2>Distribución de asignaturas</h2>

                        <p>
                            Arrastra las asignaturas al calendario y organiza sus bloques.
                        </p>

                    </div>

                </div>


                {{-- CONTENEDOR DE ASIGNATURAS Y CALENDARIO --}}
                <div class="distribucion-contenedor">


                    {{-- ASIGNATURAS DISPONIBLES --}}
                    <div class="panel-asignaturas">

                        <div class="encabezado-panel-asignaturas">

                            <h3>Asignaturas disponibles</h3>

                            <p>
                                Selecciona el docente y el espacio antes de agregar cada bloque.
                            </p>

                        </div>


                        <div id="lista-asignaturas-disponibles"
                            class="lista-asignaturas">

                            {{-- Las tarjetas se generan desde JavaScript. --}}

                        </div>

                    </div>


                    {{-- CALENDARIO --}}
                    <div class="panel-calendario">


                        {{-- ENCABEZADO DEL CALENDARIO --}}
                        <div class="encabezado-panel-calendario">

                            <div class="texto-encabezado-calendario">

                                <h3>Horario semanal</h3>

                                <p>
                                    Organiza los bloques de cada asignatura.
                                </p>

                            </div>

                            {{-- BOTÓN PARA MOSTRAR SÁBADO --}}
                            <button type="button" id="boton-sabado" class="boton-sabado">

                                <i class="fa-regular fa-calendar"></i>
                                Mostrar sábado

                            </button>
                        </div>


                        {{-- INDICACIÓN DE ARRASTRE --}}
                        <div class="indicacion-arrastrar">

                            <i class="fa-solid fa-circle-info"></i>

                            <span>
                                Arrastra las asignaturas al día y horario correspondiente.
                            </span>

                        </div>


                        {{-- CONTENEDOR DE FULLCALENDAR --}}
                        <div class="contenedor-calendario">

                            <div id="calendario-horarios"></div>

                        </div>

                    </div>

                </div>


                {{-- DETALLES QUE SE ENVIARÁN AL CONTROLADOR --}}
                <div id="detalles-horario-registro"></div>


                {{-- BOTONES DE EDICIÓN --}}
                <div class="acciones-edicion-horario">

                    <a href="{{ route('creacion-horario.index') }}"
                        class="boton-cancelar-edicion-horario">

                        Cancelar

                    </a>


                    <button type="submit"
                        id="boton-guardar-horario"
                        class="boton-guardar-edicion-horario">

                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar cambios

                    </button>

                </div>

            </section>

        </form>

    </section>

</div>

@endsection


@push('scripts')

    {{-- Cargar la función que crea las tarjetas de asignaturas. --}}
    <script src="{{ asset('js/Horarios/CreaciónHorario/gestion-CreaciónHorario.js') }}"></script>

    {{-- Cargar FullCalendar y las funcionalidades del calendario. --}}
    @vite('resources/js/Horario/fullcalendar-horarios.js')

@endpush
