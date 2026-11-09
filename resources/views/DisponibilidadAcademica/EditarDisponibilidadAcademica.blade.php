@extends('layouts.DisenoPrincipal')

@section('title', 'Modificar disponibilidad académica | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/DisponibilidadAcademica/editar-disponibilidad-academica.css') }}">
@endpush

@section('contenido')

@php
    // Obtener el periodo académico que se está modificando.
    $periodoActual = $periodos->firstWhere('id_periodo', $periodoSeleccionado);

    // Obtener el nombre completo del docente.
    $nombreDocente = trim(
        $docente->usuario->nombreUsuario . ' ' .
        ($docente->usuario->apellidoPaterno ?? '') . ' ' .
        ($docente->usuario->apellidoMaterno ?? '')
    );

    // Recuperar los bloques seleccionados.
    $bloquesMarcados = old('bloques_no_disponibles', $bloquesNoDisponibles);

    // Recuperar las asignaturas seleccionadas.
    $asignaturasMarcadas = old('asignaturas', $asignaturasSeleccionadas);
@endphp

<div class="pagina-editar-disponibilidad">

    <section class="panel-edicion-disponibilidad">


<!-- ENCABEZADO DEL FORMULARIO DE EDICIÓN -->
<div class="encabezado-editar-disponibilidad">

    <div class="informacion-docente">
        <h1>
            Editar disponibilidad académica:
            {{ $nombreDocente }}
        </h1>

        <p>
            Modifica los datos de la disponibilidad académica y guarda los cambios.
        </p>
    </div>

    <!-- INFORMACIÓN DEL PERIODO -->
    <div class="informacion-periodo">
        <span>Periodo académico</span>

        <strong>
            {{ $periodoActual->estacion ?? '' }}
            {{ $periodoActual->anio ?? '' }}
        </strong>
    </div>

</div>

        <form
            id="formulario-editar-disponibilidad"
            action="{{ route('disponibilidad-academica.update', [
                'idDocente' => $docente->id_docente,
                'idPeriodo' => $periodoSeleccionado
            ]) }}"
            method="POST"
            novalidate
        >
            @csrf
            @method('PUT')

            <input
                type="hidden"
                name="id_periodo"
                value="{{ $periodoSeleccionado }}"
            >

            <!-- TABLA DE HORARIOS -->
            <section class="seccion-horarios-edicion">

                <div class="encabezado-seccion-edicion">
                    <div>

                        <div class="titulo-con-ayuda">
                            <h3>
                                Editar disponibilidad académica
                                <span>*</span>
                            </h3>

                            <div class="ayuda-horarios">
                                <i class="fa-solid fa-circle-info icono-ayuda-horarios"></i>

                                <div class="mensaje-ayuda-horarios">
                                    <strong>¿Cómo editar tu disponibilidad académica?</strong>

                                    <p>
                                        Haz clic sobre el cuadro correspondiente al día y la hora en los que no puedes impartir clases.
                                        El cuadro cambiará a color amarillo para indicar que ese horario no está disponible.
                                    </p>

                                    <p>
                                        Si vuelves a hacer clic sobre el mismo cuadro, cambiará nuevamente a color blanco,
                                        indicando que el horario está disponible.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <p>
                            Edita los días y horarios en los que el docente no puede impartir clases durante el periodo académico.
                        </p>

                    </div>
                </div>

                <div class="referencia-horarios">
                    <span class="referencia-bloque seleccionado"></span>
                        No disponible
                    <span class="referencia-bloque disponible"></span>
                        Disponible
                </div>

                <div class="contenedor-tabla-horarios">
                    <table class="tabla-horarios-edicion">
                        <thead>
                            <tr>
                                <th>Horario</th>
                                <th>Lunes</th>
                                <th>Martes</th>
                                <th>Miércoles</th>
                                <th>Jueves</th>
                                <th>Viernes</th>
                                <th>Sábado</th>
                            </tr>
                        </thead>

                        <tbody>
                            @for ($hora = 7; $hora <= 20; $hora++)
                                <tr>
                                    <th>
                                        {{ sprintf('%02d:00', $hora) }}
                                        -
                                        {{ sprintf('%02d:00', $hora + 1) }}
                                    </th>

                                    @for ($dia = 1; $dia <= 6; $dia++)
                                        <td>
                                            @if ($dia !== 6 || $hora <= 14)
                                                @php
                                                    $identificadorBloque = $dia . '-' . $hora;
                                                @endphp

                                                <button
                                                    type="button"
                                                    class="bloque-horario"
                                                    data-bloque="{{ $identificadorBloque }}"
                                                    aria-pressed="{{ in_array($identificadorBloque, $bloquesMarcados) ? 'true' : 'false' }}"
                                                >
                                                    <span class="texto-bloque">
                                                        {{ in_array($identificadorBloque, $bloquesMarcados) ? 'No disponible' : 'Disponible' }}
                                                    </span>
                                                </button>
                                            @else
                                                <span class="bloque-no-disponible">
                                                    
                                                </span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <!-- Aquí se guardan los bloques seleccionados -->
                <div id="bloques-seleccionados">
                    @foreach ($bloquesMarcados as $bloque)
                        <input
                            type="hidden"
                            name="bloques_no_disponibles[]"
                            value="{{ $bloque }}"
                            data-input-bloque
                        >
                    @endforeach
                </div>

                @error('bloques_no_disponibles')
                    <p class="error-edicion">{{ $message }}</p>
                @enderror

                @error('bloques_no_disponibles.*')
                    <p class="error-edicion">{{ $message }}</p>
                @enderror

            </section>

            <!-- ASIGNATURAS -->
            <section class="seccion-asignaturas-edicion">

                <!-- ENCABEZADO DE ASIGNATURAS -->
                <div class="encabezado-seccion-edicion">
                    <div>
                        <h3> Asignaturas <span>*</span> </h3>

                        <p>
                            Selecciona las asignaturas que el docente prefiere impartir durante este periodo.
                        </p>
                    </div>
                </div>


                <div class="contenedor-carreras-edicion">

                    @forelse ($asignaturasPorCarrera as $grupoCarrera)

                        @php
                            $carrera = $grupoCarrera['carrera'];
                        @endphp

                        <details class="carrera-edicion">

                            <summary class="encabezado-carrera-edicion">
                                <span>
                                    {{ $carrera->claveCarrera }} - {{ $carrera->nombreCarrera }}
                                </span>

                                <i class="fa-solid fa-chevron-down"></i>
                            </summary>

                            <div class="contenido-carrera-edicion">

                                @foreach ($grupoCarrera['cuatrimestres'] as $grupoCuatrimestre)

                                    <div class="cuatrimestre-edicion">

                                        <h4>
                                            {{ $grupoCuatrimestre['cuatrimestre'] }}°
                                            cuatrimestre
                                        </h4>

                                        <div class="lista-asignaturas-edicion">

                                            @foreach ($grupoCuatrimestre['asignaturas'] as $asignatura)

                                                <label class="opcion-asignatura-edicion">

                                                    <input
                                                        type="checkbox"
                                                        name="asignaturas[]"
                                                        value="{{ $asignatura->id_asignatura }}"
                                                        @checked(
                                                            in_array(
                                                                $asignatura->id_asignatura,
                                                                $asignaturasMarcadas
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
                            No hay asignaturas disponibles para este periodo académico.
                        </p>

                    @endforelse

                </div>

            </section>
            <!-- ACCIONES -->
            <div class="acciones-edicion-disponibilidad">

                <a
                    href="{{ route('disponibilidad-academica.show', [
                        'idDocente' => $docente->id_docente,
                        'idPeriodo' => $periodoSeleccionado
                    ]) }}"
                    class="boton-cancelar-edicion"
                >
                    Cancelar
                </a>
<!--
                <button
                    type="submit"
                    name="accion"
                    value="borrador"
                    class="boton-guardar-borrador"
                >
                    Guardar cambios
                </button> 
-->

                <button type="submit" name="accion" value="entregar" class="boton-entregar-edicion" >
                    Entregar disponibilidad
                </button>

            </div>

        </form>
    </section>

</main>

@endsection

<script src="{{ asset('js/DisponibilidadAcademica/editar-DisponibilidadAcademica.js') }}"></script>



