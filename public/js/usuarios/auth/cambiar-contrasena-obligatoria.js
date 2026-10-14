// ============================================================
// BLOQUE 1. INICIALIZACIÓN DEL SCRIPT
// Descripción: Espera a que el documento HTML esté cargado
// antes de buscar los campos, botones y formulario.
// Entradas: Documento HTML de cambio de contraseña.
// Salidas: Referencias a los elementos que se utilizarán.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // Obtener los campos de contraseña y confirmación.
    const campoContrasenaActual = document.getElementById('password_actual');
    const campoContrasena = document.getElementById('password');
    const campoConfirmacion = document.getElementById('password_confirmation');

    // Obtener el mensaje que indica si las contraseñas coinciden.
    const mensajeCoincidencia = document.getElementById('mensaje-coincidencia');

    // Obtener el formulario y el botón para actualizar la contraseña.
    const formularioContrasena = document.getElementById('formulario-cambio-contrasena');
    const botonActualizarContrasena = document.getElementById('botonActualizarContrasena');

    // Obtener todos los botones que permiten mostrar u ocultar contraseñas.
    const botonesMostrarContrasena = document.querySelectorAll('.boton-ver-contrasena');


    // ============================================================
    // BLOQUE 2. MOSTRAR U OCULTAR LAS CONTRASEÑAS
    // Descripción: Permite visualizar temporalmente la contraseña
    // o volver a ocultarla al presionar el icono del ojo.
    // Entradas: Botón presionado, identificador y tipo del campo.
    // Salidas: Campo visible u oculto e icono actualizado.
    // ============================================================

    botonesMostrarContrasena.forEach(function (botonMostrar) {

        botonMostrar.addEventListener('click', function () {

            // Identificar el campo asociado al botón presionado.
            const identificadorCampo = botonMostrar.dataset.campo;

            // Obtener el campo y el icono del botón.
            const campoSeleccionado = document.getElementById(identificadorCampo);
            const iconoBoton = botonMostrar.querySelector('i');

            // Comprobar que existan el campo y el icono.
            if (!campoSeleccionado || !iconoBoton) {
                console.error('No se encontró el campo o el icono:', identificadorCampo);
                return;
            }

            // Determinar si la contraseña está oculta.
            const mostrarContrasena = campoSeleccionado.type === 'password';

            // Cambiar el tipo para mostrar u ocultar el contenido.
            campoSeleccionado.type = mostrarContrasena ? 'text' : 'password';

            // Actualizar el icono según el estado del campo.
            iconoBoton.classList.toggle('fa-eye', !mostrarContrasena);
            iconoBoton.classList.toggle('fa-eye-slash', mostrarContrasena);

            // Actualizar la descripción accesible del botón.
            botonMostrar.setAttribute(
                'aria-label',
                mostrarContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña'
            );
        });

    });


    // ============================================================
    // BLOQUE 3. VALIDAR LOS REQUISITOS DE LA CONTRASEÑA
    // Descripción: Comprueba que la nueva contraseña tenga al
    // menos ocho caracteres, una letra y un número.
    // Entradas: Texto escrito en el campo de contraseña nueva.
    // Salidas: Mensaje de validación del campo, si es necesario.
    // ============================================================

    function comprobarRequisitosContrasena() {

        // Obtener la contraseña escrita.
        const contrasena = campoContrasena.value;

        // Comprobar la longitud mínima.
        const cumpleLongitud = contrasena.length >= 8;

        // Comprobar que contenga al menos una letra.
        const contieneLetra = /[a-zA-ZÁÉÍÓÚáéíóúÑñ]/.test(contrasena);

        // Comprobar que contenga al menos un número.
        const contieneNumero = /[0-9]/.test(contrasena);

        // Establecer el resultado de la validación del navegador.
        campoContrasena.setCustomValidity(
            cumpleLongitud && contieneLetra && contieneNumero
                ? ''
                : 'La contraseña debe tener al menos 8 caracteres, una letra y un número.'
        );
    }


    // ============================================================
    // BLOQUE 4. COMPROBAR QUE LAS CONTRASEÑAS COINCIDAN
    // Descripción: Compara la contraseña nueva con su confirmación.
    // Entradas: Valores de los campos de contraseña y confirmación.
    // Salidas: Mensaje visual y estado de validación actualizado.
    // ============================================================

    function comprobarCoincidencia() {

        // Obtener los valores de ambos campos.
        const contrasena = campoContrasena.value;
        const confirmacion = campoConfirmacion.value;

        // Si la confirmación está vacía, mostrar el aviso inicial.
        if (confirmacion.length === 0) {

            mensajeCoincidencia.textContent =
                'Ambas contraseñas deben coincidir.';

            // Quitar los estilos de coincidencia y discrepancia.
            mensajeCoincidencia.classList.remove('coincide', 'no-coincide');

            // Limpiar el mensaje de validación del campo.
            campoConfirmacion.setCustomValidity('');
            return;
        }

        // Si los valores son iguales, indicar que coinciden.
        if (contrasena === confirmacion) {

            mensajeCoincidencia.textContent =
                'Las contraseñas coinciden.';

            // Aplicar el estilo de coincidencia.
            mensajeCoincidencia.classList.add('coincide');
            mensajeCoincidencia.classList.remove('no-coincide');

            // Permitir que el campo pase esta validación.
            campoConfirmacion.setCustomValidity('');

        } else {

            // Si los valores son diferentes, mostrar el aviso.
            mensajeCoincidencia.textContent =
                'Las contraseñas no coinciden.';

            // Aplicar el estilo de discrepancia.
            mensajeCoincidencia.classList.add('no-coincide');
            mensajeCoincidencia.classList.remove('coincide');

            // Impedir que el campo pase la validación del navegador.
            campoConfirmacion.setCustomValidity(
                'Las contraseñas deben coincidir.'
            );
        }
    }


    // ============================================================
    // BLOQUE 5. VALIDACIÓN MIENTRAS SE ESCRIBE
    // Descripción: Ejecuta las validaciones cada vez que cambia
    // el contenido de la contraseña nueva o su confirmación.
    // Entradas: Eventos de escritura de los campos.
    // Salidas: Requisitos y coincidencia actualizados en pantalla.
    // ============================================================

    // Comprobar que existan los campos y el mensaje.
    if (campoContrasena && campoConfirmacion && mensajeCoincidencia) {

        // Validar requisitos y coincidencia al cambiar la contraseña.
        campoContrasena.addEventListener('input', function () {
            comprobarRequisitosContrasena();
            comprobarCoincidencia();
        });

        // Comprobar coincidencia al cambiar la confirmación.
        campoConfirmacion.addEventListener('input', comprobarCoincidencia);
    }


    // ============================================================
    // BLOQUE 6. VALIDAR EL FORMULARIO ANTES DE ENVIARLO
    // Descripción: Comprueba los requisitos y evita enviar el
    // formulario si hay errores o las contraseñas son diferentes.
    // Entradas: Evento de envío del formulario.
    // Salidas: Formulario enviado si es válido o envío cancelado.
    // ============================================================

    // Comprobar que el formulario exista.
    if (formularioContrasena) {

        formularioContrasena.addEventListener('submit', function (evento) {

            // Ejecutar nuevamente las validaciones antes del envío.
            comprobarRequisitosContrasena();
            comprobarCoincidencia();

            // Cancelar el envío si hay errores de validación.
            if (
                !formularioContrasena.checkValidity() ||
                campoContrasena.value !== campoConfirmacion.value
            ) {
                evento.preventDefault();

                // Mostrar los mensajes de validación del navegador.
                formularioContrasena.reportValidity();
                return;
            }

            // Evitar que se envíe el formulario más de una vez.
            if (botonActualizarContrasena.disabled) {
                evento.preventDefault();
                return;
            }

            // Deshabilitar el botón mientras se procesa la solicitud.
            botonActualizarContrasena.disabled = true;

            // Mostrar un indicador de carga en el botón.
            botonActualizarContrasena.innerHTML =
                'Actualizando contraseña... <i class="fa-solid fa-spinner fa-spin"></i>';
        });
    }

});

document.addEventListener('DOMContentLoaded', function () {
    const elementoModal = document.getElementById(
        'modalContrasenaActualizada'
    );

    if (elementoModal && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(elementoModal);

        modal.show();

        setTimeout(function () {
            window.location.href = '/inicio';
        }, 4000);
    }
});
