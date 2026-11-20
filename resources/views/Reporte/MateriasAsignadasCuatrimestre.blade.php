
@extends('layouts.DisenoPrincipal')

@section('title', 'Materias asignadas por cuatrimestre | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Reportes/materias-asignadas-cuatrimestre.css') }}">
@endpush

@section('contenido')

<main class="pagina-reporte-materias">

    <!-- ENCABEZADO DEL REPORTE -->
    <header class="encabezado-reporte-materias">

        <div>
            <p class="ruta-reporte">
                Inicio / Reportes / Materias asignadas
            </p>

            <h1>Materias con docente asignado por cuatrimestre</h1>

            <p class="descripcion-reporte-principal">
                Consulta el avance de asignación de docentes a las materias
                correspondientes a cada carrera y cuatrimestre.
            </p>
        </div>

        <button
            type="button"
            class="boton-descargar-reporte"
            id="boton-descargar-reporte"
        >
            <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
            Descargar PDF
        </button>

    </header>


    <!-- INDICADORES GENERALES -->
    <section class="seccion-indicadores-materias">

        <!-- TOTAL DE MATERIAS -->
        <article class="indicador-materias indicador-total">
            <div class="icono-indicador">
                <i class="fa-solid fa-book" aria-hidden="true"></i>
            </div>

            <div>
                <p>Total de materias</p>
                <h3>{{ $totalMaterias ?? 0 }}</h3>
            </div>
        </article>

        <!-- MATERIAS ASIGNADAS -->
        <article class="indicador-materias indicador-asignadas">
            <div class="icono-indicador">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            </div>

            <div>
                <p>Materias asignadas</p>
                <h3>{{ $totalAsignadas ?? 0 }}</h3>
            </div>
        </article>

        <!-- MATERIAS PENDIENTES -->
        <article class="indicador-materias indicador-pendientes">
            <div class="icono-indicador">
                <i class="fa-solid fa-clock" aria-hidden="true"></i>
            </div>

            <div>
                <p>Materias pendientes</p>
                <h3>{{ $totalPendientes ?? 0 }}</h3>
            </div>
        </article>

        <!-- PORCENTAJE DE ASIGNACIÓN -->
        <article class="indicador-materias indicador-porcentaje">
            <div class="icono-indicador">
                <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
            </div>

            <div>
                <p>Avance de asignación</p>
                <h3>{{ number_format($porcentajeAsignacion ?? 0, 1) }}%</h3>
            </div>
        </article>

    </section>


    <!-- TABLA DE MATERIAS -->
    <section class="seccion-tabla-materias">

        <div class="encabezado-seccion-reporte">
            <div>
                <h2>Detalle de asignación por cuatrimestre</h2>

                <p>
                    Cantidad de materias asignadas y pendientes por carrera.
                </p>
            </div>
        </div>

        <div class="contenedor-tabla-materias">

            <table class="tabla-materias-asignadas">

                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th>Cuatrimestre</th>
                        <th>Materias asignadas</th>
                        <th>Materias pendientes</th>
                        <th>Porcentaje asignado</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($datosReporte ?? [] as $dato)

                        <tr>
                            <td>
                                {{ $dato->nombreCarrera }}
                            </td>

                            <td>
                                {{ $dato->cuatrimestre }}°
                            </td>

                            <td>
                                <span class="valor-asignado">
                                    {{ $dato->materiasAsignadas }}
                                </span>
                            </td>

                            <td>
                                <span class="valor-pendiente">
                                    {{ $dato->materiasPendientes }}
                                </span>
                            </td>

                            <td>
                                <div class="celda-porcentaje">

                                    <span>
                                        {{ number_format($dato->porcentajeAsignacion, 1) }}%
                                    </span>

                                    <div class="barra-progreso-materias">
                                        <div
                                            class="progreso-materias"
                                            style="width: {{ $dato->porcentajeAsignacion }}%"
                                        ></div>
                                    </div>

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="mensaje-sin-resultados">
                                <i class="fa-solid fa-folder-open" aria-hidden="true"></i>

                                <p>
                                    No hay información disponible para mostrar.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>


    <!-- MATERIAS PENDIENTES DE ASIGNACIÓN -->
    <section class="seccion-materias-pendientes">

        <div class="encabezado-pendientes">
            <div>
                <h2>Cantidad de materias pendientes de asignación</h2>

                <p>
                    Materias que todavía no están presentes en todos los grupos
                    correspondientes a su carrera y cuatrimestre.
                </p>
            </div>

            <span class="total-pendientes">
                {{ isset($materiasPendientesDetalle) ? $materiasPendientesDetalle->count() : 0 }}
            </span>
        </div>

        @if (isset($materiasPendientesDetalle) && $materiasPendientesDetalle->isNotEmpty())

            <div class="lista-materias-pendientes">

                @foreach ($materiasPendientesDetalle as $materia)

                    <article class="elemento-materia-pendiente">

                        <div class="informacion-materia-pendiente">

                            <h3>
                                {{ $materia['nombreAsignatura'] }}
                            </h3>

                            <span>
                                Clave: {{ $materia['clave'] }}
                            </span>

                            <span>
                                {{ $materia['carrera'] }}
                                ·
                                {{ $materia['cuatrimestre'] }}.º cuatrimestre
                            </span>

                        </div>

                        <div class="grupos-materia-pendiente">

                            @if ($materia['sinGrupos'])

                                <span>
                                    No existen grupos activos para este periodo.
                                </span>

                            @else

                                <strong>
                                    Falta en
                                    {{ $materia['cantidadGruposFaltantes'] }}
                                    {{ $materia['cantidadGruposFaltantes'] === 1 ? 'grupo' : 'grupos' }}:
                                </strong>

                                <span>
                                    {{ implode(', ', $materia['gruposFaltantes']) }}
                                </span>

                            @endif

                        </div>

                    </article>

                @endforeach

            </div>

        @else

            <p class="mensaje-sin-pendientes">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>

                Todas las materias están presentes en sus grupos correspondientes.
            </p>

        @endif

    </section>


    <!-- GRÁFICA DE MATERIAS -->
    <section class="seccion-grafica-materias">

        <div class="encabezado-seccion-reporte">
            <div>
                <h2>Avance de asignación por cuatrimestre</h2>

                <p>
                    Comparación de materias asignadas y pendientes por carrera.
                </p>
            </div>
        </div>

        <div class="contenedor-grafica-materias">

            <canvas id="grafica-materias-cuatrimestre"></canvas>

            <p class="mensaje-grafica-vacia" id="mensaje-grafica-vacia" @if (count($datosGrafica ?? []) > 0) hidden @endif >
                No hay datos suficientes para generar la gráfica.
            </p>

        </div>

    </section>

</main>


<!-- ======================================== -->
<!-- LIBRERÍAS PARA LA GRÁFICA Y EL PDF       -->
<!-- ======================================== -->

<!-- CHART.JS 4.6.1 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.6.1/dist/chart.umd.min.js"></script>



<!-- HTML2CANVAS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


<!-- ======================================== -->
<!-- DATOS DEL REPORTE ENVIADOS DESDE LARAVEL -->
<!-- ======================================== -->

<script>
    // Datos para construir la gráfica de materias asignadas y pendientes.
    window.datosGraficaMaterias = @json($datosGrafica ?? []);

    // Información de las materias que todavía presentan grupos faltantes.
    window.materiasPendientesDetalle = @json($materiasPendientesDetalle ?? []);
</script>


<!-- ======================================== -->
<!-- SCRIPT PRINCIPAL DEL REPORTE             -->
<!-- ======================================== -->

<script
    src="{{ asset('js/Reportes/materias-asignadas-cuatrimestre.js') }}"
    defer
></script>

@endsection
