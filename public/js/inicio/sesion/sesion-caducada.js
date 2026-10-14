document.addEventListener('DOMContentLoaded', function () {

    console.log('JavaScript de sesión caducada cargado.');

    // Buscar el modal de sesión caducada.
    const modalSesionCaducada = document.getElementById(
        'modalSesionCaducada'
    );

    // Buscar el botón para cerrar el aviso.
    const botonEntendido = document.getElementById(
        'botonEntendidoSesionCaducada'
    );

    // Comprobar que Bootstrap y el modal existan.
    if (!modalSesionCaducada) {
        console.error('No se encontró #modalSesionCaducada.');
        return;
    }

    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap no está cargado.');
        return;
    }

    // Preparar el modal de Bootstrap.
    const modalSesion = bootstrap.Modal.getOrCreateInstance(
        modalSesionCaducada,
        {
            backdrop: 'static',
            keyboard: false
        }
    );

    // Leer el parámetro de la dirección.
    const parametrosURL = new URLSearchParams(
        window.location.search
    );

    // Mostrar el modal si la dirección indica que caducó la sesión.
    if (parametrosURL.get('sesion') === 'caducada') {
        modalSesion.show();
    }

    // Cerrar el modal al pulsar Entendido.
    if (botonEntendido) {
        botonEntendido.addEventListener('click', function () {
            modalSesion.hide();

            // Limpiar el parámetro para que no vuelva a aparecer al actualizar.
            window.history.replaceState(
                {},
                document.title,
                window.location.pathname
            );

            document.getElementById('correo')?.focus();
        });
    }
});
