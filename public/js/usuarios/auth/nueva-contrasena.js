// Descripción: Gestiona la visualización, validación y envío de la nueva contraseña.
document.addEventListener('DOMContentLoaded', function () {
    // Obtener los elementos del formulario de nueva contraseña.
    const campoContrasena = document.getElementById('password');
    const campoConfirmacion = document.getElementById('password_confirmation');
    const mensajeCoincidencia = document.getElementById('mensaje-coincidencia');
    const formularioContrasena = document.getElementById('formulario-nueva-contrasena');
    const botonesMostrarContrasena = document.querySelectorAll('.boton-ver-contrasena');
    const botonActualizarContrasena = document.getElementById('botonActualizarContrasena');

    // Mostrar u ocultar la contraseña seleccionada.
    botonesMostrarContrasena.forEach(function (botonMostrar) {
        botonMostrar.addEventListener('click', function () {
            
            // Obtener el identificador del campo asociado al botón seleccionado.
            const identificadorCampo = botonMostrar.dataset.campo;
            // Obtener el campo de contraseña y el icono correspondiente al botón.
            const campoSeleccionado = document.getElementById(identificadorCampo);
            const iconoBoton = botonMostrar.querySelector('i');

            // Verificar que existan el campo y el icono antes de continuar.
            if (!campoSeleccionado || !iconoBoton) {
                return;
            }

            // Determinar si la contraseña está oculta.
            const mostrarContrasena = campoSeleccionado.type === 'password';

            // Cambiar el tipo del campo para mostrar u ocultar la contraseña.
            campoSeleccionado.type = mostrarContrasena ? 'text' : 'password';
            iconoBoton.classList.toggle('fa-eye', !mostrarContrasena);
            iconoBoton.classList.toggle('fa-eye-slash', mostrarContrasena);

            // Actualizar la etiqueta accesible del botón para indicar su acción.
            botonMostrar.setAttribute(
                'aria-label',
                mostrarContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña'
            );
        });
    });

    // Comprobar si ambas contraseñas coinciden.
    function comprobarCoincidencia() {
        // Obtener los valores actuales de los campos de contraseña.
        const contrasena = campoContrasena.value;
        const confirmacion = campoConfirmacion.value;

        // Mostrar el mensaje inicial cuando todavía no se ha escrito la confirmación.
        if (confirmacion.length === 0) {
            mensajeCoincidencia.textContent =
                'Ambas contraseñas deben coincidir.';

            mensajeCoincidencia.classList.remove('coincide', 'no-coincide');

            // Limpiar cualquier mensaje de error personalizado del campo.
            campoConfirmacion.setCustomValidity('');
            return;
        }

        // Comprobar si la contraseña y su confirmación son iguales.
        if (contrasena === confirmacion) {
            mensajeCoincidencia.textContent =
                'Las contraseñas coinciden.';

            mensajeCoincidencia.classList.add('coincide');
            mensajeCoincidencia.classList.remove('no-coincide');
            campoConfirmacion.setCustomValidity('');
        } else { 
            // Mostrar un mensaje cuando las contraseñas son diferentes.
            mensajeCoincidencia.textContent =
                'Las contraseñas no coinciden.';

            mensajeCoincidencia.classList.add('no-coincide');
            mensajeCoincidencia.classList.remove('coincide');
            campoConfirmacion.setCustomValidity(
                'Las contraseñas deben coincidir.'
            );
        }
    }

    // Comprobar la coincidencia mientras se escriben las contraseñas.
    campoContrasena.addEventListener('input', comprobarCoincidencia);
    campoConfirmacion.addEventListener('input', comprobarCoincidencia);

    // Validar el formulario y evitar envíos repetidos.
    formularioContrasena.addEventListener('submit', function (evento) {
        comprobarCoincidencia();

        // Verificar que el formulario sea válido y que las contraseñas sean iguales.
        if ( !formularioContrasena.checkValidity() || campoContrasena.value !== campoConfirmacion.value ) {
            
            // Cancelar el envío cuando existe algún error de validación.            
            evento.preventDefault();
            formularioContrasena.reportValidity();
            return;
        }

        // Evitar que se envíe nuevamente mientras se procesa.
        if (botonActualizarContrasena.disabled) {
            evento.preventDefault();
            return;
        }

        // Deshabilitar el botón 
        botonActualizarContrasena.disabled = true;
        botonActualizarContrasena.innerHTML =
            'Actualizando contraseña... <i class="fa-solid fa-spinner fa-spin"></i>';
    });
});
