
document.addEventListener('DOMContentLoaded', function () {
    const campoContrasena = document.getElementById('password');
    const campoConfirmacion = document.getElementById('password_confirmation');
    const mensajeCoincidencia = document.getElementById('mensaje-coincidencia');
    const formularioContrasena = document.getElementById('formulario-nueva-contrasena');
    const botonesMostrarContrasena = document.querySelectorAll('.boton-ver-contrasena');
    const botonActualizarContrasena = document.getElementById('botonActualizarContrasena');

    // Mostrar u ocultar la contraseña seleccionada.
    botonesMostrarContrasena.forEach(function (botonMostrar) {
        botonMostrar.addEventListener('click', function () {
            const identificadorCampo = botonMostrar.dataset.campo;
            const campoSeleccionado = document.getElementById(identificadorCampo);
            const iconoBoton = botonMostrar.querySelector('i');

            if (!campoSeleccionado || !iconoBoton) {
                return;
            }

            const mostrarContrasena = campoSeleccionado.type === 'password';

            campoSeleccionado.type = mostrarContrasena ? 'text' : 'password';
            iconoBoton.classList.toggle('fa-eye', !mostrarContrasena);
            iconoBoton.classList.toggle('fa-eye-slash', mostrarContrasena);

            botonMostrar.setAttribute(
                'aria-label',
                mostrarContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña'
            );
        });
    });

    // Comprobar si ambas contraseñas coinciden.
    function comprobarCoincidencia() {
        const contrasena = campoContrasena.value;
        const confirmacion = campoConfirmacion.value;

        if (confirmacion.length === 0) {
            mensajeCoincidencia.textContent =
                'Ambas contraseñas deben coincidir.';

            mensajeCoincidencia.classList.remove('coincide', 'no-coincide');
            campoConfirmacion.setCustomValidity('');
            return;
        }

        if (contrasena === confirmacion) {
            mensajeCoincidencia.textContent =
                'Las contraseñas coinciden.';

            mensajeCoincidencia.classList.add('coincide');
            mensajeCoincidencia.classList.remove('no-coincide');
            campoConfirmacion.setCustomValidity('');
        } else {
            mensajeCoincidencia.textContent =
                'Las contraseñas no coinciden.';

            mensajeCoincidencia.classList.add('no-coincide');
            mensajeCoincidencia.classList.remove('coincide');
            campoConfirmacion.setCustomValidity(
                'Las contraseñas deben coincidir.'
            );
        }
    }

    campoContrasena.addEventListener('input', comprobarCoincidencia);
    campoConfirmacion.addEventListener('input', comprobarCoincidencia);

    // Validar el formulario y evitar envíos repetidos.
    formularioContrasena.addEventListener('submit', function (evento) {
        comprobarCoincidencia();

        if (
            !formularioContrasena.checkValidity() ||
            campoContrasena.value !== campoConfirmacion.value
        ) {
            evento.preventDefault();
            formularioContrasena.reportValidity();
            return;
        }

        // Evitar que se envíe nuevamente mientras se procesa.
        if (botonActualizarContrasena.disabled) {
            evento.preventDefault();
            return;
        }

        botonActualizarContrasena.disabled = true;
        botonActualizarContrasena.innerHTML =
            'Actualizando contraseña... <i class="fa-solid fa-spinner fa-spin"></i>';
    });
});
