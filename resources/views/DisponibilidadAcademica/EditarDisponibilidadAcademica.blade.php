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