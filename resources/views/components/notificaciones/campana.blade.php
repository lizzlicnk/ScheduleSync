{{-- Muestra la campana únicamente al administrador y al personal administrativo. --}}
@if (auth()->check() && in_array((int) auth()->user()->rol, [1, 2]))

    @php
        // Establece valores predeterminados si todavía no hay notificaciones.
        $notificaciones = $notificaciones ?? collect();
        $cantidadNoLeidas = $cantidadNoLeidas ?? 0;
    @endphp

    <div class="contenedor-campana" id="contenedorCampana">

        <!-- Botón para abrir y cerrar las notificaciones. -->
        <button type="button" class="boton-campana {{ $cantidadNoLeidas > 0 ? 'tiene-notificaciones' : '' }}" id="botonCampana" 
            aria-label="Mostrar notificaciones" aria-expanded="false" aria-controls="listaNotificaciones">

            <i class="fa-regular fa-bell"></i>

            <!-- Contador de notificaciones sin leer. -->
            @if ($cantidadNoLeidas > 0)
                <span class="contador-notificaciones" id="contadorNotificaciones">
                    {{ $cantidadNoLeidas > 99 ? '99+' : $cantidadNoLeidas }}
                </span>
            @endif
        </button>

        <!-- Panel desplegable de notificaciones. -->
        <div class="panel-notificaciones" id="listaNotificaciones" hidden>

            <div class="encabezado-notificaciones">
                <div>
                    <h5>Notificaciones</h5>
                    <span id="textoCantidadNotificaciones">
                        {{ $cantidadNoLeidas }} sin leer
                    </span>
                </div>
            </div>

            <div class="contenido-notificaciones" id="contenidoNotificaciones">

                @forelse ($notificaciones as $notificacion)

                    <article class="elemento-notificacion" id="notificacion-{{ $notificacion->id_notificacion }}">

                        <div class="icono-notificacion">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>

                        <div class="informacion-notificacion">
                            <p class="mensaje-notificacion">
                                {{ $notificacion->mensaje }}
                            </p>

                            <span class="fecha-notificacion">
                                {{ \Carbon\Carbon::parse($notificacion->fechaEnvio)->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <!-- Marca la notificación como leída. -->
                        <button type="button" class="boton-marcar-leida" data-url="{{ route('notificaciones.marcar-leida', $notificacion->id_notificacion) }}"
                            aria-label="Marcar como leída" title="Marcar como leída">

                            <i class="fa-solid fa-check"></i>
                        </button>

                    </article>

                @empty

                    <div class="notificaciones-vacias" id="notificacionesVacias">
                        <i class="fa-regular fa-bell-slash"></i>
                        <p>No tienes notificaciones pendientes.</p>
                    </div>

                @endforelse

            </div>
        </div>
    </div>

@endif
