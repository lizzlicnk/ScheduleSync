
@extends('layouts.DisenoPrincipal')

@section('title', 'Disponibilidad académica docente | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Reportes/disponibilidad-docente.css') }}">
@endpush

@section('contenido')

<main class="pagina-reporte-disponibilidad">

    <!-- ENCABEZADO DEL REPORTE -->
    <header class="encabezado-reporte-disponibilidad">

        <div>
            <p class="ruta-reporte">
                Inicio / Reportes / Disponibilidad académica
            </p>

            <h1>Disponibilidad académica docente</h1>

            <p class="descripcion-reporte-principal">
                Consulta el registro de disponibilidad académica de los docentes
                y el avance de confirmación correspondiente.
            </p>

            <!-- PERIODO SELECCIONADO -->
            <span class="periodo-reporte">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                {{ $periodoSeleccionado->estacion }}
                {{ $periodoSeleccionado->anio }}
            </span>
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
    <section class="seccion-indicadores-disponibilidad">

        <!-- TOTAL DE DOCENTES -->
        <article class="indicador-disponibilidad indicador-total">
            <div class="icono-indicador">
                <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
            </div>

            <div>
                <p>Total de docentes</p>
                <h3>{{ $totalDocentes ?? 0 }}</h3>
            </div>
        </article>


        <!-- DISPONIBILIDADES REGISTRADAS -->
        <article class="indicador-disponibilidad indicador-registradas">
            <div class="icono-indicador">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            </div>

            <div>
                <p>Disponibilidades registradas</p>
                <h3>{{ $totalRegistrados ?? 0 }}</h3>
            </div>
        </article>


        <!-- DISPONIBILIDADES PENDIENTES -->
        <article class="indicador-disponibilidad indicador-pendientes">
            <div class="icono-indicador">
                <i class="fa-solid fa-clock" aria-hidden="true"></i>
            </div>

            <div>
                <p>Disponibilidades pendientes</p>
                <h3>{{ $totalPendientes ?? 0 }}</h3>
            </div>
        </article>


        <!-- PORCENTAJE DE REGISTRO -->
        <article class="indicador-disponibilidad indicador-porcentaje">
            <div class="icono-indicador">
                <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
            </div>

            <div>
                <p>Avance de registro</p>
                <h3>{{ number_format($porcentajeRegistrados ?? 0, 1) }}%</h3>
            </div>
        </article>

    </section>


    <!-- TABLA DE DISPONIBILIDAD DOCENTE -->
    <section class="seccion-tabla-disponibilidad">

        <div class="encabezado-seccion-reporte">
            <div>
                <h2>Detalle de disponibilidad docente</h2>

                <p>
                    Estado de registro y fecha de confirmación de cada docente.
                </p>
            </div>
        </div>


        <div class="contenedor-tabla-disponibilidad">

            <table class="tabla-disponibilidad-docente">

                <thead>
                    <tr>
                        <th>Docente</th>
                        <th>Tipo de docente</th>
                        <th>Estado de disponibilidad</th>
                        <th>Fecha de confirmación</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($datosReporte ?? [] as $dato)

                        <tr>

                            <td>
                                {{ $dato->nombreDocente }}
                            </td>

                            <td>
                                <span class="tipo-docente">
                                    {{ $dato->tipoDocente }}
                                </span>
                            </td>

                            <td>
                                @if ($dato->estadoDisponibilidad === 'Registrada')

                                    <span class="estado-disponibilidad estado-registrada">
                                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                        Registrada
                                    </span>

                                @else

                                    <span class="estado-disponibilidad estado-pendiente">
                                        <i class="fa-solid fa-clock" aria-hidden="true"></i>
                                        Pendiente
                                    </span>

                                @endif
                            </td>

                            <td>
                                @if ($dato->fechaRegistro)
                                    {{ \Carbon\Carbon::parse($dato->fechaRegistro)->format('d/m/Y H:i') }}
                                @else
                                    <span class="fecha-sin-registro">
                                        Sin confirmar
                                    </span>
                                @endif
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="mensaje-sin-resultados">
                                <i class="fa-solid fa-folder-open" aria-hidden="true"></i>

                                <p>
                                    No hay docentes disponibles para mostrar.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>


    <!-- RESUMEN DE DISPONIBILIDADES PENDIENTES -->
    <section class="seccion-disponibilidades-pendientes">

        <div class="encabezado-pendientes">
            <div>
                <h2>Docentes con disponibilidad pendiente</h2>

                <p>
                    Docentes que todavía no han confirmado su disponibilidad
                    académica para el periodo consultado.
                </p>
            </div>

            <span class="total-pendientes">
                {{ $totalPendientes ?? 0 }}
            </span>
        </div>


        @php
            $docentesPendientes = ($datosReporte ?? collect())
                ->where('estadoDisponibilidad', 'Pendiente');
        @endphp


        @if ($docentesPendientes->isNotEmpty())

            <div class="lista-disponibilidades-pendientes">

                @foreach ($docentesPendientes as $docente)

                    <article class="elemento-disponibilidad-pendiente">

                        <div class="informacion-docente-pendiente">

                            <h3>
                                {{ $docente->nombreDocente }}
                            </h3>

                            <span>
                                Tipo de docente: {{ $docente->tipoDocente }}
                            </span>

                        </div>

                        <span class="etiqueta-pendiente">
                            <i class="fa-solid fa-clock" aria-hidden="true"></i>
                            Pendiente de confirmación
                        </span>

                    </article>

                @endforeach

            </div>

        @else

            <p class="mensaje-sin-pendientes">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>

                Todos los docentes han confirmado su disponibilidad académica.
            </p>

        @endif

    </section>


    <!-- GRÁFICA DE DISPONIBILIDAD -->
    <section class="seccion-grafica-disponibilidad">

        <div class="encabezado-seccion-reporte">
            <div>
                <h2>Avance de disponibilidad académica</h2>

                <p>
                    Comparación entre docentes con disponibilidad registrada
                    y aquellos que tienen pendiente la confirmación.
                </p>
            </div>
        </div>


        <div class="contenedor-grafica-disponibilidad">

            <canvas id="grafica-disponibilidad-docente"></canvas>

            <p
                class="mensaje-grafica-vacia"
                id="mensaje-grafica-vacia"
                @if (($totalDocentes ?? 0) > 0) hidden @endif
            >
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
    // Datos de disponibilidades registradas y pendientes.
    window.datosGraficaDisponibilidad = @json($datosGrafica ?? []);

    // Total de docentes incluidos en el reporte.
    window.totalDocentesDisponibilidad = @json($totalDocentes ?? 0);
</script>


<!-- ======================================== -->
<!-- SCRIPT PRINCIPAL DEL REPORTE             -->
<!-- ======================================== -->

<script
    src="{{ asset('js/Reportes/disponibilidad-docente.js') }}"
    defer
></script>

@endsection
