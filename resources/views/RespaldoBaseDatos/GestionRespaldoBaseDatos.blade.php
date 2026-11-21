
@extends('layouts.DisenoPrincipal')

@section('title', 'Respaldo y restauración | ScheduleSync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/RespaldoBaseDatos/gestion-respaldoBaseDatos.css') }}">
@endpush

@section('contenido')

<div class="pagina-respaldo">

    <!-- Encabezado de la página -->
    <div class="encabezado-respaldo">

        <div>
            <h1>Respaldo y restauración</h1>
            <p>Administra las copias de seguridad de la base de datos de ScheduleSync.</p>
        </div>

        <div class="icono-encabezado-respaldo">
            <i class="fa-solid fa-database"></i>
        </div>

    </div>

    <!-- Información general -->
    <div class="tarjeta-informacion-respaldo">

        <div class="icono-informacion-respaldo">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <div>
            <h3>Protección de la información</h3>
            <p>
                Genera respaldos periódicos para conservar la información académica
                y contar con archivos que permitan recuperar la base de datos.
            </p>
        </div>

    </div>

    <!-- Sección para generar respaldos -->
    <section class="seccion-respaldo">

        <div class="encabezado-seccion-respaldo">
            <div>
                <h2>Generar respaldo</h2>
                <p>Crea una copia de seguridad de la base de datos actual.</p>
            </div>

            <span class="etiqueta-respaldo">
                <i class="fa-solid fa-cloud-arrow-down"></i>
                Copia de seguridad
            </span>
        </div>

        <div class="contenido-generar-respaldo">

            <div class="descripcion-generar-respaldo">
                <div class="icono-generar-respaldo">
                    <i class="fa-solid fa-file-circle-plus"></i>
                </div>

                <div class="titulo-generar-respaldo">
                    <div class="titulo-con-informacion">
                        <h3>Crear nuevo respaldo</h3>

                        <!-- Icono con información del respaldo -->
                        <div class="contenedor-informacion-respaldo">

                            <i class="fa-solid fa-circle-info icono-informacion-respaldo"></i>

                            <div class="tooltip-informacion-respaldo">
                                <h4>¿Qué contiene este respaldo?</h4>

                                <p>
                                    El archivo SQL contiene una copia de la estructura
                                    y los datos almacenados en la base de datos ScheduleSync.
                                </p>

                                <ul>
                                    <li>
                                        <i class="fa-solid fa-table"></i>
                                        Estructura de las tablas: usuario, docente, carrera,
                                        grupo, asignatura, asignatura_preferida, periodo,
                                        horario, detalle_horario, disponibilidadprofesor,
                                        notificacion.
                                    </li>

                                    <li>
                                        <i class="fa-solid fa-database"></i>
                                        Registros almacenados en las tablas.
                                    </li>
                                </ul>

                                <span>
                                    Formato del archivo: .SQL
                                </span>
                            </div>

                        </div>
                    </div>

                    <p>
                        El sistema generará un archivo SQL con la información
                        almacenada actualmente en la base de datos.
                    </p>
                </div>
            </div>

            <form id="formulario-generar-respaldo"
                  action="{{ route('respaldo.generar') }}"
                  method="POST">

                @csrf

                <button type="submit" class="boton-generar-respaldo">
                    <i class="fa-solid fa-database"></i>
                    Generar respaldo
                </button>

            </form>

        </div>

    </section>

    <!-- Sección de respaldos existentes -->
    <section class="seccion-respaldo">

        <div class="encabezado-seccion-respaldo">

            <div>
                <h2>Respaldos disponibles</h2>
                <p>Consulta y descarga los archivos de respaldo generados.</p>
            </div>

            <span class="contador-respaldos">
                <i class="fa-solid fa-folder-open"></i>
                {{ count($respaldos) }} archivos
            </span>

        </div>

        <div class="contenedor-tabla-respaldos">

            <table class="tabla-respaldos">

                <thead>
                    <tr>
                        <th>Archivo de respaldo</th>
                        <th>Fecha de generación</th>
                        <th>Formato</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="tabla-respaldos">

                    @forelse ($respaldos as $respaldo)

                        @php
                            $nombreArchivo = basename($respaldo);

                            $fechaArchivo = str_replace(
                                ['respaldo_', '.sql'],
                                '',
                                $nombreArchivo
                            );

                            try {
                                $fechaFormateada = \Carbon\Carbon::createFromFormat(
                                    'Ymd_His',
                                    substr($fechaArchivo, strpos($fechaArchivo, '_') + 1)
                                )->format('d/m/Y H:i:s');
                            } catch (\Throwable $error) {
                                $fechaFormateada = 'Fecha no disponible';
                            }
                        @endphp

                        <tr class="fila-respaldo">

                            <td>
                                <div class="informacion-archivo-respaldo">
                                    <div class="icono-archivo-sql">
                                        <i class="fa-solid fa-file-code"></i>
                                    </div>

                                    <span>{{ $nombreArchivo }}</span>
                                </div>
                            </td>

                            <td>{{ $fechaFormateada }}</td>

                            <td>
                                <span class="etiqueta-sql">SQL</span>
                            </td>

                            <td>
                                <a href="{{ route('respaldo.descargar', ['archivo' => $nombreArchivo]) }}"
                                class="boton-descargar-respaldo"
                                data-nombre="{{ $nombreArchivo }}"
                                title="Descargar respaldo">

                                    <i class="fa-solid fa-download"></i>
                                    Descargar

                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr id="fila-sin-respaldos">
                            <td colspan="4" class="sin-respaldos">
                                <i class="fa-solid fa-folder-open"></i>
                                <p>No hay respaldos disponibles.</p>
                                <span>Genera un respaldo para que aparezca en esta sección.</span>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- Controles para cambiar de página -->
        @if (count($respaldos) > 0)
            <div class="paginacion-respaldos" id="paginacion-respaldos">

                <span id="informacion-pagina-respaldos">
                    Página 1 de 1
                </span>

                <div class="controles-paginacion">

                    <button type="button" class="boton-pagina" id="pagina-anterior-respaldos" aria-label="Página anterior" disabled>
                        ‹
                    </button>

                    <button type="button" class="boton-pagina" id="pagina-siguiente-respaldos" aria-label="Página siguiente" disabled>
                        ›
                    </button>

                </div>

            </div>
        @endif

    </section>

    <!-- Sección para restaurar la base de datos -->
    <section class="seccion-respaldo seccion-restauracion">

        <div class="encabezado-seccion-respaldo">

            <div>
                <h2>Restaurar base de datos</h2>
                <p>Recupera la información utilizando un respaldo generado anteriormente.</p>
            </div>

            <span class="etiqueta-restauracion">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Restauración
            </span>

        </div>

        <div class="advertencia-restauracion">

            <i class="fa-solid fa-triangle-exclamation"></i>

            <div>
                <h3>Advertencia importante</h3>
                <p>
                    La restauración reemplazará la información actual de la base de datos
                    por la contenida en el respaldo seleccionado. Los cambios realizados
                    después de generar ese respaldo podrían perderse y no podrán recuperarse
                    si no existe otra copia de seguridad.
                </p>
            </div>

        </div>

        <form id="formulario-restaurar-respaldo"
              action="{{ route('respaldo.restaurar') }}"
              method="POST">

            @csrf

            <div class="campo-restauracion">

                <label for="archivo-respaldo">
                    Seleccionar respaldo
                </label>

                <select id="archivo-respaldo"
                        name="archivo"
                        class="selector-respaldo"
                        required
                        disabled>

                    <option value="">Selecciona un respaldo disponible</option>

                    @foreach ($respaldos as $respaldo)

                        <option value="{{ basename($respaldo) }}">
                            {{ basename($respaldo) }}
                        </option>

                    @endforeach

                </select>

            </div>

            <button type="submit"
                    id="boton-restaurar-respaldo"
                    class="boton-restaurar-respaldo"
                    disabled>

                <i class="fa-solid fa-rotate-left"></i>
                Restaurar respaldo

            </button>

            <p class="mensaje-restauracion-pendiente">
                La restauración estará disponible cuando se complete la implementación
                de esta operación.
            </p>

        </form>

    </section>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/RespaldoBaseDatos/gestion-respaldoBaseDatos.js') }}"></script>
@endpush
