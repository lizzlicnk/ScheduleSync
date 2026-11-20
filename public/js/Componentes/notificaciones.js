
document.addEventListener('DOMContentLoaded', function () {

    // Obtener los elementos de la campana.
    const contenedorCampana = document.getElementById('contenedorCampana');
    const botonCampana = document.getElementById('botonCampana');
    const panelNotificaciones = document.getElementById('listaNotificaciones');
    const contenidoNotificaciones = document.getElementById('contenidoNotificaciones');
    const textoCantidad = document.getElementById('textoCantidadNotificaciones');

    // Verificar que la campana exista en la vista actual.
    if (!contenedorCampana || !botonCampana || !panelNotificaciones) {
        return;
    }

    // =====================================================
    // ABRIR Y CERRAR EL PANEL
    // =====================================================

    botonCampana.addEventListener('click', function () {

        const estaAbierto = !panelNotificaciones.hidden;

        panelNotificaciones.hidden = estaAbierto;
        botonCampana.setAttribute('aria-expanded', String(!estaAbierto));
    });

    // Cerrar el panel al hacer clic fuera de la campana.
    document.addEventListener('click', function (evento) {

        if (!contenedorCampana.contains(evento.target)) {

            panelNotificaciones.hidden = true;
            botonCampana.setAttribute('aria-expanded', 'false');
        }
    });

    // Cerrar el panel al presionar Escape.
    document.addEventListener('keydown', function (evento) {

        if (evento.key === 'Escape') {

            panelNotificaciones.hidden = true;
            botonCampana.setAttribute('aria-expanded', 'false');
        }
    });

    // =====================================================
    // MARCAR NOTIFICACIÓN COMO LEÍDA
    // =====================================================

    contenidoNotificaciones.addEventListener('click', async function (evento) {

        const botonMarcar = evento.target.closest('.boton-marcar-leida');

        if (!botonMarcar) {
            return;
        }

        const url = botonMarcar.dataset.url;
        const tokenCSRF = document.querySelector('meta[name="csrf-token"]').content;

        botonMarcar.disabled = true;

        try {

            // Enviar la solicitud para actualizar el estado de la notificación.
            const respuesta = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': tokenCSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!respuesta.ok) {
                throw new Error('No fue posible actualizar la notificación.');
            }

            // Eliminar la notificación de la lista de pendientes.
            const elemento = botonMarcar.closest('.elemento-notificacion');

            if (elemento) {
                elemento.remove();
            }

            // Actualizar el contador de notificaciones.
            const contador = document.getElementById('contadorNotificaciones');

            if (contador) {

                const cantidadActual = parseInt(contador.textContent, 10) || 0;
                const nuevaCantidad = Math.max(0, cantidadActual - 1);

                if (nuevaCantidad === 0) {

                    contador.remove();
                    botonCampana.classList.remove('tiene-notificaciones');

                } else {

                    contador.textContent = nuevaCantidad > 99 ? '99+' : nuevaCantidad;
                }

                textoCantidad.textContent = `${nuevaCantidad} sin leer`;
            }

            // Mostrar el mensaje cuando ya no existan notificaciones pendientes.
            if (!contenidoNotificaciones.querySelector('.elemento-notificacion')) {

                contenidoNotificaciones.innerHTML = `
                    <div class="notificaciones-vacias">
                        <i class="fa-regular fa-bell-slash"></i>
                        <p>No tienes notificaciones pendientes.</p>
                    </div>
                `;
            }

        } catch (error) {

            console.error(error);
            botonMarcar.disabled = false;
            alert('No se pudo marcar la notificación como leída. Inténtalo nuevamente.');
        }
    });
});
