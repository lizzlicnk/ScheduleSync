document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos principales del formulario.
    const formulario = document.getElementById('formulario-editar-disponibilidad');
    const bloquesSeleccionados = document.getElementById('bloques-seleccionados');

    // Obtiene todos los botones de la tabla de horarios.
    const botonesBloque = document.querySelectorAll('.bloque-horario');

    // Comprueba que existan los elementos necesarios.
    if (!formulario || !bloquesSeleccionados) {
        console.error('No se encontró el formulario o el contenedor de bloques.');
        return;
    }

    // Actualiza el texto y el estado visual de un bloque.
    function cambiarEstadoVisual(boton, noDisponible) {

        boton.setAttribute('aria-pressed', noDisponible ? 'true' : 'false');

        const texto = boton.querySelector('.texto-bloque');

        if (texto) {
            texto.textContent = noDisponible ? 'No disponible' : 'Disponible';
        }
    }

    // Recupera los bloques que ya estaban registrados.
    botonesBloque.forEach(function (boton) {

        const identificador = boton.dataset.bloque;

        const inputExistente = Array.from(
            bloquesSeleccionados.querySelectorAll('[data-input-bloque]')
        ).find(function (input) {
            return input.value === identificador;
        });

        // Establece el estado visual inicial.
        cambiarEstadoVisual(boton, Boolean(inputExistente));

    });

    // Permite seleccionar o deseleccionar los bloques.
    botonesBloque.forEach(function (boton) {

        boton.addEventListener('click', function () {

            console.log('Clic en bloque:', boton.dataset.bloque);
            
            const identificador = boton.dataset.bloque;

            const inputExistente = Array.from(
                bloquesSeleccionados.querySelectorAll('[data-input-bloque]')
            ).find(function (input) {
                return input.value === identificador;
            });

            if (inputExistente) {

                // Si ya estaba seleccionado, elimina el bloque.
                inputExistente.remove();

                cambiarEstadoVisual(boton, false);

            } else {

                // Si no estaba seleccionado, agrega el bloque.
                const nuevoInput = document.createElement('input');

                nuevoInput.type = 'hidden';
                nuevoInput.name = 'bloques_no_disponibles[]';
                nuevoInput.value = identificador;
                nuevoInput.dataset.inputBloque = '';

                bloquesSeleccionados.appendChild(nuevoInput);

                cambiarEstadoVisual(boton, true);
            }

        });

    });

});