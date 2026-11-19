@extends('layouts.DisenoPrincipal')

@section('titulo', 'Editar horario académico')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Horarios/CreaciónHorario/editar-creaciónHorario.css') }}">
@endpush

@section('contenido')

<div class="pagina-editar-horario">

    {{-- ENCABEZADO --}}
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


    {{-- INFORMACIÓN GENERAL DEL HORARIO --}}
    <section class="panel-informacion-horario">

        <div class="franja-institucional"></div>

        <div class="contenido-informacion-horario">

            <h2>Información del horario</h2>

            <div class="datos-generales-horario">

                <div>
                    <span>Carrera</span>

                    <strong>
                        {{ $horario->grupo->carrera->nombreCarrera ?? 'No disponible' }}
                    </strong>
                </div>

                <div>
                    <span>Grupo</span>

                    <strong>
                        {{ $horario->grupo->nombreGrupo ?? 'No disponible' }}
                    </strong>
                </div>

                <div>
                    <span>Cuatrimestre</span>

                    <strong>
                        {{ $horario->grupo->cuatrimestre ?? 'No disponible' }}
                    </strong>
                </div>

                <div>
                    <span>Periodo escolar</span>

                    <strong>
                        {{ $horario->periodo->estacion ?? '' }}
                        {{ $horario->periodo->anio ?? '' }}
                    </strong>
                </div>

            </div>

        </div>

    </section>


    {{-- FORMULARIO DE EDICIÓN --}}
    <form id="formulario-registro-horario"
        action="{{ route('horarios.update', $horario->id_horario) }}"
        method="POST"
        data-modo="editar"
        novalidate>

        @csrf
        @method('PUT')

        {{-- Identificador del horario que se está editando. --}}
        <input type="hidden"
            name="id_horario"
            value="{{ $horario->id_horario }}">

        {{-- Este selector conserva el identificador que utiliza el JS de creación. --}}
        <select id="horario-registro"
            class="d-none"
            data-url="{{ route('horarios.asignaturas', ['idHorario' => 'HORARIO_ID']) }}">

            <option value="{{ $horario->id_horario }}" selected>
                {{ $horario->id_horario }}
            </option>

        </select>


        {{-- DISTRIBUCIÓN DE ASIGNATURAS Y CALENDARIO --}}
        <section class="panel-horarios">

            <div class="encabezado-panel">

                <div>
                    <h2>Distribución de asignaturas</h2>

                    <p>
                        Arrastra las asignaturas al calendario y organiza sus bloques.
                    </p>
                </div>

            </div>


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

                    <div class="encabezado-panel-calendario">

                        <h3>Horario semanal</h3>

                        <p>
                            Organiza los bloques de cada asignatura.
                        </p>

                    </div>

                    <div class="indicacion-arrastrar">
                        <i class="fa-solid fa-circle-info"></i>

                        Arrastra las asignaturas al día y horario correspondiente.
                    </div>

                    <div class="contenedor-calendario">

                        <div id="calendario-horarios"></div>

                    </div>

                    <div class="acciones-distribucion">

                        <button type="button"
                            id="boton-sabado"
                            class="boton-sabado">

                            <i class="fa-regular fa-calendar"></i>
                            Mostrar sábado

                        </button>

                    </div>

                </div>

            </div>


            {{-- DETALLES DEL HORARIO --}}
            <div id="detalles-horario-registro"></div>


            {{-- ACCIONES --}}
            <div class="acciones-editar-horario">

                <a href="{{ route('creacion-horario.index') }}"
                    class="boton-cancelar-edicion">

                    Cancelar

                </a>

                <button type="submit"
                    id="boton-guardar-horario"
                    class="boton-guardar-edicion">

                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar cambios

                </button>

            </div>

        </section>

    </form>

</div>

@endsection

@push('scripts')

    {{-- Cargar la función que crea las tarjetas de asignaturas. --}}
    <script src="{{ asset('js/Horarios/CreaciónHorario/gestion-CreaciónHorario.js') }}"></script>

    {{-- Cargar FullCalendar y las funcionalidades del calendario. --}}
    @vite('resources/js/Horario/fullcalendar-horarios.js')

@endpush