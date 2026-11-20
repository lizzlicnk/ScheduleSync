
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

        <button type="button" class="boton-descargar-reporte" id="boton-descargar-reporte">
            <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
            Descargar PDF
        </button>

    </header>

    <!-- INDICADORES GENERALES -->
    <section class="seccion-indicadores-materias">

        <article class="indicador-materias indicador-total">
            <div class="icono-indicador">
                <i class="fa-solid fa-book" aria-hidden="true"></i>
            </div>

            <div>
                <p>Total de materias</p>
                <h3>{{ $totalMaterias ?? 0 }}</h3>
            </div>
        </article>

        <article class="indicador-materias indicador-asignadas">
            <div class="icono-indicador">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            </div>

            <div>
                <p>Materias asignadas</p>
                <h3>{{ $totalAsignadas ?? 0 }}</h3>
            </div>
        </article>

        <article class="indicador-materias indicador-pendientes">
            <div class="icono-indicador">
                <i class="fa-solid fa-clock" aria-hidden="true"></i>
            </div>

            <div>
                <p>Materias pendientes</p>
                <h3>{{ $totalPendientes ?? 0 }}</h3>
            </div>
        </article>

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
                            <td>{{ $dato->nombreCarrera }}</td>

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

                                <p>No hay información disponible para mostrar.</p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

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

            <p
                class="mensaje-grafica-vacia"
                id="mensaje-grafica-vacia"
                @if (count($datosGrafica ?? []) > 0) hidden @endif
            >
                No hay datos suficientes para generar la gráfica.
            </p>

        </div>

    </section>

</main>

<!-- LIBRERÍA CHART.JS 4.6.1 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.6.1/dist/chart.umd.min.js"></script>

<!-- DATOS DEL REPORTE ENVIADOS DESDE LARAVEL -->
<script>
    window.datosGraficaMaterias = @json($datosGrafica ?? []);
</script>

<!-- SCRIPT DEL REPORTE -->
<script src="{{ asset('js/Reportes/materias-asignadas-cuatrimestre.js') }}" defer></script>

@endsection
