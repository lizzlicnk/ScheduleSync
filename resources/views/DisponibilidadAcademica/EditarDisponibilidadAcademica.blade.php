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


       <!-- PANEL PARA CONSULTAR DISPONIBILIDAD ACADÉMICA -->
    <section class="panel-disponibilidad panel-consulta-disponibilidad" id="consulta-disponibilidad">

        <div class="encabezado-panel">
            <div>
                <h2>Consulta de disponibilidad académica</h2>

                <p class="texto-secundario">
                    Consulta los horarios no disponibles y las asignaturas seleccionadas por el docente.
                </p>
            </div>
        </div>

        @if (isset($docenteConsultado) && $docenteConsultado)

            @php
                // Obtiene el nombre completo del docente.
                $nombreDocenteConsulta = trim(
                    $docenteConsultado->usuario->nombreUsuario . ' ' .
                    ($docenteConsultado->usuario->apellidoPaterno ?? '') . ' ' .
                    ($docenteConsultado->usuario->apellidoMaterno ?? '')
                );

                // Define los días de la semana.
                $diasConsulta = [
                    1 => 'Lunes',
                    2 => 'Martes',
                    3 => 'Miércoles',
                    4 => 'Jueves',
                    5 => 'Viernes',
                    6 => 'Sábado',
                ];
            @endphp

            <!-- INFORMACIÓN DEL DOCENTE -->
            <div class="tarjeta-detalle">

                <div class="encabezado-detalle">
                    <div class="icono-detalle">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>
                        <h3>Información del docente</h3>
                        <p class="texto-secundario">
                            Datos generales del registro consultado.
                        </p>
                    </div>
                </div>

                <div class="lista-detalle">

                    <div class="fila-detalle">
                        <span>Nombre del docente</span>
                        <strong>{{ $nombreDocenteConsulta }}</strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Matrícula</span>
                        <strong>{{ $docenteConsultado->matricula }}</strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Periodo</span>
                        <strong>
                            {{ $periodoConsultado->estacion }}
                            {{ $periodoConsultado->anio }}
                        </strong>
                    </div>

                </div>
            </div>

            <!-- HORARIOS NO DISPONIBLES -->
            <div class="tarjeta-detalle">

                <div class="encabezado-detalle">
                    <div class="icono-detalle">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <div>
                        <h3>Disponibilidad académica</h3>
                        <p class="texto-secundario">
                            Horarios en los que el docente no puede impartir clases.
                        </p>
                    </div>
                </div>

                <div class="lista-detalle">

                    @foreach ($diasConsulta as $numeroDia => $nombreDia)

                        @php
                            // Obtiene los horarios registrados para el día.
                            $horariosDia = $disponibilidadesConsulta
                                ->where('dia', $numeroDia)
                                ->sortBy('hora_inicio')
                                ->values();

                            // Agrupa los horarios consecutivos.
                            $intervalosDia = [];

                            foreach ($horariosDia as $horario) {

                                $inicio = substr($horario->hora_inicio, 0, 5);
                                $fin = substr($horario->hora_fin, 0, 5);

                                $ultimoIndice = count($intervalosDia) - 1;

                                if (
                                    $ultimoIndice >= 0 &&
                                    $intervalosDia[$ultimoIndice]['fin'] === $inicio
                                ) {
                                    $intervalosDia[$ultimoIndice]['fin'] = $fin;
                                } else {
                                    $intervalosDia[] = [
                                        'inicio' => $inicio,
                                        'fin' => $fin,
                                    ];
                                }
                            }
                        @endphp

                        <div class="fila-detalle">
                            <strong>{{ $nombreDia }}</strong>

                            <span>
                                @if (count($intervalosDia) > 0)

                                    @foreach ($intervalosDia as $indice => $intervalo)

                                        {{ $intervalo['inicio'] }} - {{ $intervalo['fin'] }}{{ $indice < count($intervalosDia) - 1 ? ' y ' : '' }}

                                    @endforeach

                                @else
                                    Sin horarios registrados
                                @endif
                            </span>
                        </div>

                    @endforeach

                </div>
            </div>

            <!-- ASIGNATURAS SELECCIONADAS -->
            <div class="tarjeta-detalle">

                <div class="encabezado-detalle">
                    <div class="icono-detalle">
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <div>
                        <h3>Asignaturas seleccionadas</h3>

                        <p class="texto-secundario">
                            {{ $asignaturasConsulta->count() }}
                            {{ $asignaturasConsulta->count() === 1 ? 'materia seleccionada' : 'materias seleccionadas' }}
                        </p>
                    </div>
                </div>

                @if ($asignaturasConsulta->isNotEmpty())

                    <div class="lista-detalle">

                        @foreach ($asignaturasConsulta as $asignatura)

                            <div class="fila-detalle">

                                <div>
                                    <strong>{{ $asignatura->nombreAsignatura }}</strong>

                                    <p class="texto-secundario">
                                        {{ $asignatura->carrera->nombreCarrera ?? 'Carrera no disponible' }}
                                    </p>
                                </div>

                                <span>
                                    {{ $asignatura->cuatrimestre }}.º cuatrimestre
                                </span>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="tabla-vacia">
                        El docente no ha seleccionado asignaturas.
                    </div>

                @endif

            </div>

            <!-- ACCIONES DE CONSULTA -->
            <div class="acciones-disponibilidad">

                <button
                    type="button"
                    class="boton-secundario"
                    id="boton-imprimir-consulta"
                >
                    <i class="fa-solid fa-print"></i>
                    Imprimir consulta
                </button>

            </div>

        @else

            <div class="tabla-vacia">
                Selecciona la opción Consultar de un docente para visualizar su disponibilidad académica.
            </div>

        @endif

    </section>