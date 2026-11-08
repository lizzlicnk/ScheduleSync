
document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos principales del formulario.
    const formulario = document.getElementById('formulario-disponibilidad');
    const bloquesSeleccionados = document.getElementById('bloques-seleccionados');

    // Comprueba que el archivo JavaScript se haya cargado.
    console.log('JavaScript de registro de disponibilidad cargado.');

    // Obtiene los bloques registrados inicialmente.
    const botonesHorario = document.querySelectorAll('.bloque-horario');

    botonesHorario.forEach(function (boton) {

        // Obtiene el identificador del bloque.
        const identificadorBloque = boton.dataset.bloque;

        // Comprueba si el bloque ya está seleccionado.
        const inputInicial = bloquesSeleccionados
            ? Array.from(
                bloquesSeleccionados.querySelectorAll('[data-input-bloque]')
            ).find(function (input) {
                return input.dataset.inputBloque === identificadorBloque;
            })
            : null;

        // Establece el estado visual inicial.
        cambiarEstadoVisual(boton, Boolean(inputInicial));

    });

    // Detecta los clics realizados sobre los bloques de horario.
    document.addEventListener('click', function (evento) {

        // Identifica el botón seleccionado.
        const boton = evento.target.closest('.bloque-horario');

        // Comprueba que se haya seleccionado un bloque.
        if (!boton) {
            return;
        }

        // Obtiene el identificador del bloque.
        const identificadorBloque = boton.dataset.bloque;

        // Comprueba si el bloque ya está registrado.
        const inputExistente = bloquesSeleccionados
            ? Array.from(
                bloquesSeleccionados.querySelectorAll('[data-input-bloque]')
            ).find(function (input) {
                return input.dataset.inputBloque === identificadorBloque;
            })
            : null;

        if (inputExistente) {

            // Elimina el bloque de los horarios no disponibles.
            inputExistente.remove();

            // Cambia el bloque a disponible.
            cambiarEstadoVisual(boton, false);

        } else {

            // Registra el bloque como no disponible.
            if (bloquesSeleccionados) {

                const nuevoInput = document.createElement('input');

                nuevoInput.type = 'hidden';
                nuevoInput.name = 'bloques_no_disponibles[]';
                nuevoInput.value = identificadorBloque;
                nuevoInput.dataset.inputBloque = identificadorBloque;

                bloquesSeleccionados.appendChild(nuevoInput);

            } else {

                console.error('No se encontró el contenedor bloques-seleccionados.');

            }

            // Cambia el bloque a no disponible.
            cambiarEstadoVisual(boton, true);

        }

    });

    // Cambia la apariencia visual de un bloque de horario.
    function cambiarEstadoVisual(boton, noDisponible) {

        if (noDisponible) {

            boton.classList.add('bloque-no-disponible');
            boton.setAttribute('aria-pressed', 'true');
            boton.textContent = 'No disponible';

            // Aplica directamente el color amarillo.
            boton.style.setProperty('background-color', '#FFF1B8', 'important');
            boton.style.setProperty('border-color', '#E8C95B', 'important');
            boton.style.setProperty('color', '#755B12', 'important');

        } else {

            boton.classList.remove('bloque-no-disponible');
            boton.setAttribute('aria-pressed', 'false');
            boton.textContent = 'Disponible';

            // Restablece los estilos originales del bloque.
            boton.style.removeProperty('background-color');
            boton.style.removeProperty('border-color');
            boton.style.removeProperty('color');

        }

    }

    // Restablece la selección de los bloques de horario.
    formulario?.addEventListener('reset', function () {

        // Espera a que el formulario restablezca sus valores.
        setTimeout(function () {

            // Elimina los bloques registrados.
            if (bloquesSeleccionados) {
                bloquesSeleccionados.innerHTML = '';
            }

            // Restablece la apariencia de todos los bloques.
            botonesHorario.forEach(function (boton) {
                cambiarEstadoVisual(boton, false);
            });

        }, 0);

    });

    // Confirma la entrega de la disponibilidad académica.
    formulario?.addEventListener('submit', function (evento) {

        // Identifica el botón que realizó el envío.
        const botonEnviar = evento.submitter;

        if (botonEnviar && botonEnviar.value === 'entregar') {

            const confirmarEntrega = confirm(
                '¿Estás seguro de entregar tu disponibilidad académica? Después de entregarla, quedará registrada como confirmada.'
            );

            if (!confirmarEntrega) {
                evento.preventDefault();
            }

        }

    });


        // Obtiene el selector de docentes.
    const selectorDocente = document.getElementById('id_docente');

    // Obtiene el section del formulario de registro.
    const panelRegistro = document.querySelector('.panel-registro-disponibilidad');

    // Centra el section del formulario en la pantalla.
    function centrarPanelRegistro() {

        if (!panelRegistro) {
            return;
        }

        // Calcula la posición del section respecto a toda la página.
        const posicionPanel = panelRegistro.getBoundingClientRect().top + window.scrollY;

        // Calcula la posición necesaria para centrar el section.
        const posicionCentrada =
            posicionPanel - (window.innerHeight / 2) + (panelRegistro.offsetHeight / 2);

        // Desplaza la página hasta centrar el formulario.
        window.scrollTo({
            top: Math.max(0, posicionCentrada),
            behavior: 'smooth'
        });
    }

    // Detecta cuando se cambia el docente seleccionado.
    selectorDocente?.addEventListener('change', function () {

        if (!this.value) {
            return;
        }

        centrarPanelRegistro();
    });

    // Si se llegó desde el botón "Agregar", baja al formulario de registro.
    if (window.location.hash === '#registro-disponibilidad') {

        // Espera a que la página termine de cargar para calcular bien las posiciones.
        window.addEventListener('load', function () {
            centrarPanelRegistro();
        });
    }
    
});
