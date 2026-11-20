
@extends('layouts.DisenoPrincipal')

@section('title', 'Reportes | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Reportes/gestion-reportes.css') }}">
@endpush

@section('contenido')

<main class="pagina-reportes">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <p class="ruta-modulo">Inicio / Planeación académica / Reportes</p>

            <h1>Reportes</h1>

            <p class="descripcion-modulo">
                Consulta y administra los reportes del sistema para dar seguimiento
                a la planeación académica.
            </p>
        </div>
    </header>


    <!-- TARJETAS DE REPORTES -->
    <section class="seccion-reportes">

        <div class="encabezado-seccion-reportes">
            <div>
                <h2>Reportes disponibles</h2>

                <p class="texto-secundario">
                    Selecciona el reporte que deseas consultar.
                </p>
            </div>

            <span class="cantidad-reportes">
                <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
                4 reportes
            </span>
        </div>


        <div class="grid-reportes">

            <!-- REPORTE 1: CARGA ACADÉMICA POR DOCENTE -->
            <article class="tarjeta-reporte">

                <div class="franja-institucional"></div>

                <div class="contenido-tarjeta-reporte">

                    <div class="encabezado-tarjeta-reporte">
                        <div class="icono-reporte">
                            <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
                        </div>

                        <span class="numero-reporte">REPORTE 01</span>
                    </div>

                    <h3>Carga académica por docente</h3>

                    <p class="descripcion-reporte">
                        Consulta las horas asignadas a cada docente, así como la
                        cantidad de grupos y asignaturas que tiene a su cargo.
                    </p>

                    <div class="acciones-reporte">

                        <button
                            type="button"
                            class="boton-filtros-reporte"
                            data-filtros="filtros-docente"
                            aria-expanded="false"
                            aria-controls="filtros-docente"
                        >
                            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                            Filtros
                            <i class="fa-solid fa-chevron-down icono-flecha" aria-hidden="true"></i>
                        </button>

                        <form action="#" method="GET" class="formulario-reporte">

                            <div class="panel-filtros-reporte" id="filtros-docente" hidden>

                                <div class="campo-filtro-reporte">
                                    <label for="carrera-docente">Carrera</label>

                                    <select id="carrera-docente" name="carrera">
                                        <option value="">Todas las carreras</option>
                                        <option value="ITI">ITI</option>
                                        <option value="IET">IET</option>
                                        <option value="ISE">ISE</option>
                                        <option value="IID">IID</option>
                                    </select>
                                </div>

                                <div class="campo-filtro-reporte">
                                    <label for="periodo-docente">Periodo</label>

                                    <select id="periodo-docente" name="periodo">
                                        <option value="">Todos los periodos</option>
                                        <option value="Invierno">Invierno</option>
                                        <option value="Primavera">Primavera</option>
                                        <option value="Otoño">Otoño</option>
                                    </select>
                                </div>

                                <div class="campo-filtro-reporte">
                                    <label for="anio-docente">Año</label>

                                    <select id="anio-docente" name="anio">
                                        <option value="">Todos los años</option>

                                        @for ($anio = date('Y'); $anio >= 2026; $anio--)
                                            <option value="{{ $anio }}">{{ $anio }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <button type="submit" class="boton-generar-filtrado">
                                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                                    Aplicar filtros
                                </button>

                            </div>

                            <button type="submit" class="boton-general-reporte">
                                <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
                                General
                            </button>

                        </form>

                    </div>

                </div>
            </article>


            <!-- REPORTE 2: CARGA ACADÉMICA POR GRUPO -->
            <article class="tarjeta-reporte">

                <div class="franja-institucional"></div>

                <div class="contenido-tarjeta-reporte">

                    <div class="encabezado-tarjeta-reporte">
                        <div class="icono-reporte">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                        </div>

                        <span class="numero-reporte">REPORTE 02</span>
                    </div>

                    <h3>Carga académica por grupo</h3>

                    <p class="descripcion-reporte">
                        Identifica las horas asignadas y las horas pendientes por asignar en cada grupo académico.
                    </p>

                    <div class="acciones-reporte">

                        <button
                            type="button"
                            class="boton-filtros-reporte"
                            data-filtros="filtros-grupo"
                            aria-expanded="false"
                            aria-controls="filtros-grupo"
                        >
                            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                            Filtros
                            <i class="fa-solid fa-chevron-down icono-flecha" aria-hidden="true"></i>
                        </button>

                        <form action="#" method="GET" class="formulario-reporte">

                            <div class="panel-filtros-reporte" id="filtros-grupo" hidden>

                                <div class="campo-filtro-reporte">
                                    <label for="carrera-grupo">Carrera</label>

                                    <select id="carrera-grupo" name="carrera">
                                        <option value="">Todas las carreras</option>
                                        <option value="ITI">ITI</option>
                                        <option value="IET">IET</option>
                                        <option value="ISE">ISE</option>
                                        <option value="IID">IID</option>
                                    </select>
                                </div>

                                <div class="campo-filtro-reporte">
                                    <label for="periodo-grupo">Periodo</label>

                                    <select id="periodo-grupo" name="periodo">
                                        <option value="">Todos los periodos</option>
                                        <option value="Invierno">Invierno</option>
                                        <option value="Primavera">Primavera</option>
                                        <option value="Otoño">Otoño</option>
                                    </select>
                                </div>

                                <div class="campo-filtro-reporte">
                                    <label for="anio-grupo">Año</label>

                                    <select id="anio-grupo" name="anio">
                                        <option value="">Todos los años</option>

                                        @for ($anio = date('Y'); $anio >= 2026; $anio--)
                                            <option value="{{ $anio }}">{{ $anio }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <button type="submit" class="boton-generar-filtrado">
                                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                                    Aplicar filtros
                                </button>

                            </div>

                            <button type="submit" class="boton-general-reporte">
                                <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
                                General
                            </button>

                        </form>

                    </div>

                </div>
            </article>


            <!-- REPORTE 3: DISPONIBILIDAD DOCENTE -->
            <article class="tarjeta-reporte">

                <div class="franja-institucional"></div>

                <div class="contenido-tarjeta-reporte">

                    <div class="encabezado-tarjeta-reporte">
                        <div class="icono-reporte">
                            <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                        </div>

                        <span class="numero-reporte">REPORTE 03</span>
                    </div>

                    <h3>Disponibilidad docente</h3>

                    <p class="descripcion-reporte">
                        Consulta los docentes que han registrado su disponibilidad
                        y aquellos que todavía tienen pendiente este proceso.
                    </p>

                    <div class="acciones-reporte">

                        <button
                            type="button"
                            class="boton-filtros-reporte"
                            data-filtros="filtros-disponibilidad"
                            aria-expanded="false"
                            aria-controls="filtros-disponibilidad"
                        >
                            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                            Filtros
                            <i class="fa-solid fa-chevron-down icono-flecha" aria-hidden="true"></i>
                        </button>

                        <form action="#" method="GET" class="formulario-reporte">

                            <div class="panel-filtros-reporte" id="filtros-disponibilidad" hidden>

                                <div class="campo-filtro-reporte">
                                    <label for="carrera-disponibilidad">Carrera</label>

                                    <select id="carrera-disponibilidad" name="carrera">
                                        <option value="">Todas las carreras</option>
                                        <option value="ITI">ITI</option>
                                        <option value="IET">IET</option>
                                        <option value="ISE">ISE</option>
                                        <option value="IID">IID</option>
                                    </select>
                                </div>

                                <div class="campo-filtro-reporte">
                                    <label for="periodo-disponibilidad">Periodo</label>

                                    <select id="periodo-disponibilidad" name="periodo">
                                        <option value="">Todos los periodos</option>
                                        <option value="Invierno">Invierno</option>
                                        <option value="Primavera">Primavera</option>
                                        <option value="Otoño">Otoño</option>
                                    </select>
                                </div>

                                <div class="campo-filtro-reporte">
                                    <label for="anio-disponibilidad">Año</label>

                                    <select id="anio-disponibilidad" name="anio">
                                        <option value="">Todos los años</option>

                                        @for ($anio = date('Y'); $anio >= 2026; $anio--)
                                            <option value="{{ $anio }}">{{ $anio }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <button type="submit" class="boton-generar-filtrado">
                                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                                    Aplicar filtros
                                </button>

                            </div>

                            <button type="submit" class="boton-general-reporte">
                                <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
                                General
                            </button>

                        </form>

                    </div>

                </div>
            </article>


            <!-- REPORTE 4: MATERIAS CON DOCENTE ASIGNADO -->
            <article class="tarjeta-reporte">

                <div class="franja-institucional"></div>

                <div class="contenido-tarjeta-reporte">

                    <div class="encabezado-tarjeta-reporte">
                        <div class="icono-reporte">
                            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                        </div>

                        <span class="numero-reporte">REPORTE 04</span>
                    </div>

                    <h3>Materias con docente asignado</h3>

                    <p class="descripcion-reporte">
                        Identifica las materias que cuentan con un docente asignado
                        y aquellas que se encuentran pendientes por carrera y cuatrimestre.
                    </p>

                    <div class="acciones-reporte">

                        <button
                            type="button"
                            class="boton-filtros-reporte"
                            data-filtros="filtros-materias"
                            aria-expanded="false"
                            aria-controls="filtros-materias"
                        >
                            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                            Filtros
                            <i class="fa-solid fa-chevron-down icono-flecha" aria-hidden="true"></i>
                        </button>

                        <form
                            action="{{ route('reportes.materias-asignadas-cuatrimestre') }}"
                            method="GET"
                            class="formulario-reporte"
                            target="_blank"
                            rel="noopener noreferrer"
                        >

                            <div class="panel-filtros-reporte" id="filtros-materias" hidden>

                                <div class="campo-filtro-reporte">
                                    <label for="carrera-materias">Carrera</label>

                                    <select id="carrera-materias" name="carrera">
                                        <option value="">Todas las carreras</option>
                                        <option value="ITI">ITI</option>
                                        <option value="IET">IET</option>
                                        <option value="ISE">ISE</option>
                                        <option value="IID">IID</option>
                                    </select>
                                </div>

                                <div class="campo-filtro-reporte">
                                    <label for="cuatrimestre-materias">Cuatrimestre</label>

                                    <select id="cuatrimestre-materias" name="cuatrimestre">
                                        <option value="">Todos los cuatrimestres</option>

                                        @for ($cuatrimestre = 1; $cuatrimestre <= 10; $cuatrimestre++)
                                            <option value="{{ $cuatrimestre }}">
                                                {{ $cuatrimestre }}°
                                            </option>
                                        @endfor
                                    </select>
                                </div>


                                <button type="submit" class="boton-generar-filtrado">
                                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                                    Aplicar filtros
                                </button>

                            </div>

                            <!-- ABRIR EL REPORTE GENERAL EN UNA PESTAÑA NUEVA -->
                            <a
                                href="{{ route('reportes.materias-asignadas-cuatrimestre') }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="boton-general-reporte"
                            >
                                <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
                                General
                            </a>

                        </form>

                    </div>

                </div>
            </article>

        </div>

    </section>

</main>

<script src="{{ asset('js/Reportes/gestion-reportes.js') }}" defer></script>

@endsection
