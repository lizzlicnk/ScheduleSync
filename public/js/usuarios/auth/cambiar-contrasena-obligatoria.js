// Descripción: Gestiona la visualización, validación y actualización obligatoria de la contraseña.
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

    // Permite visualizar temporalmente la contraseña o volver a ocultarla al presionar el icono del ojo.
    botonesMostrarContrasena.forEach(function (botonMostrar) {

        // Detectar cuando el usuario presiona el botón del icono.
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

    // Valida los requisitos de la nueva contraseña
    function comprobarRequisitosContrasena() {

        // Obtener la contraseña.
        const contrasena = campoContrasena.value;
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

    // Compara la contraseña nueva con su confirmación.
    function comprobarCoincidencia() {

        // Obtener los valores de ambos campos.
        const contrasena = campoContrasena.value;
        const confirmacion = campoConfirmacion.value;

        // Si la confirmación está vacía, mostrar el aviso inicial.
        if (confirmacion.length === 0) {

            mensajeCoincidencia.textContent = 'Ambas contraseñas deben coincidir.';
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
            mensajeCoincidencia.textContent = 'Las contraseñas no coinciden.';

            // Aplicar el estilo de discrepancia.
            mensajeCoincidencia.classList.add('no-coincide');
            mensajeCoincidencia.classList.remove('coincide');

            // Impedir que el campo pase la validación del navegador.
            campoConfirmacion.setCustomValidity( 'Las contraseñas deben coincidir.');
        }
    }

    // Ejecuta las validaciones cada vez que cambia el contenido de la contraseña nueva o su confirmación.
    if (campoContrasena && campoConfirmacion && mensajeCoincidencia) {

        // Validar requisitos y coincidencia al cambiar la contraseña.
        campoContrasena.addEventListener('input', function () {
            comprobarRequisitosContrasena();
            comprobarCoincidencia();
        });

        // Comprobar coincidencia al cambiar la confirmación.
        campoConfirmacion.addEventListener('input', comprobarCoincidencia);
    }


    // VALIDAR EL FORMULARIO ANTES DE ENVIARLO
    if (formularioContrasena) {

        formularioContrasena.addEventListener('submit', function (evento) {

            // Ejecutar nuevamente las validaciones antes del envío.
            comprobarRequisitosContrasena();
            comprobarCoincidencia();

            // Cancelar el envío si hay errores de validación.
            if ( !formularioContrasena.checkValidity() || campoContrasena.value !== campoConfirmacion.value) {
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
            botonActualizarContrasena.innerHTML = 'Actualizando contraseña... <i class="fa-solid fa-spinner fa-spin"></i>';
        });
    }

});

// Descripción: Muestra el mensaje de éxito cuando la contraseña se actualiza correctamente 
// y redirige al usuario después de cuatro segundos.
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
