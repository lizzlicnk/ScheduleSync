
document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos principales del formulario.
    const formulario = document.getElementById('formulario-disponibilidad');
    const bloquesSeleccionados = document.getElementById('bloques-seleccionados');

    // Obtiene los elementos del modal de validación.
    const modalValidacion = document.getElementById('modal-validacion');
    const botonCerrarModal = document.getElementById('cerrar-modal-validacion');

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
            boton.textContent = '';

            // Aplica directamente el color amarillo.
            boton.style.setProperty('background-color', '#FFF1B8', 'important');
            boton.style.setProperty('border-color', '#E8C95B', 'important');
            boton.style.setProperty('color', '#755B12', 'important');

        } else {

            boton.classList.remove('bloque-no-disponible');
            boton.setAttribute('aria-pressed', 'false');
            boton.textContent = '';

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
    

    // Obtiene el selector de periodos académicos.
    const selectorPeriodo = document.getElementById('id_periodo');

    // Detecta cuando cambia el periodo seleccionado.
    selectorPeriodo?.addEventListener('change', function () {

        // Obtiene el identificador del periodo seleccionado.
        const idPeriodo = this.value;

        // Comprueba que se haya seleccionado un periodo.
        if (!idPeriodo) {
            return;
        }

        // Obtiene los datos que se deben conservar.
        const datosDisponibilidad = {

            // Conserva el docente seleccionado.
            id_docente: document.getElementById('id_docente')?.value ?? '',

            // Conserva los bloques de horario no disponibles.
            bloques_no_disponibles: Array.from(
                bloquesSeleccionados?.querySelectorAll('[data-input-bloque]') ?? []
            ).map(function (input) {
                return input.value;
            }),

            // Conserva las asignaturas preferidas seleccionadas.
            asignaturas: Array.from(
                formulario?.querySelectorAll('input[name="asignaturas[]"]:checked') ?? []
            ).map(function (input) {
                return input.value;
            })

        };

        // Guarda temporalmente los datos antes de recargar la página.
        sessionStorage.setItem(
            'datosDisponibilidadPendientes',
            JSON.stringify(datosDisponibilidad)
        );

        // Obtiene la dirección actual.
        const url = new URL(window.location.href);

        // Actualiza el periodo seleccionado.
        url.searchParams.set('id_periodo', idPeriodo);

        // Regresa a la sección del formulario.
        url.hash = 'registro-disponibilidad';

        // Recarga la vista con el nuevo periodo.
        window.location.href = url.toString();

    });


    // Recupera los datos guardados después de cambiar el periodo.
    const datosGuardados = sessionStorage.getItem(
        'datosDisponibilidadPendientes'
    );

    // Comprueba si existen datos pendientes por recuperar.
    if (datosGuardados) {

        // Convierte los datos almacenados a un objeto.
        const datosDisponibilidad = JSON.parse(datosGuardados);

        // Recupera el docente seleccionado.
        const selectorDocente = document.getElementById('id_docente');

        if (selectorDocente && datosDisponibilidad.id_docente) {
            selectorDocente.value = datosDisponibilidad.id_docente;
        }

        // Recupera los bloques de horario no disponibles.
        if (bloquesSeleccionados) {

            // Limpia los bloques generados inicialmente.
            bloquesSeleccionados.innerHTML = '';

            datosDisponibilidad.bloques_no_disponibles.forEach(function (bloque) {

                // Crea nuevamente el campo oculto del bloque.
                const nuevoInput = document.createElement('input');

                nuevoInput.type = 'hidden';
                nuevoInput.name = 'bloques_no_disponibles[]';
                nuevoInput.value = bloque;
                nuevoInput.dataset.inputBloque = bloque;

                bloquesSeleccionados.appendChild(nuevoInput);

                // Recupera la apariencia visual del bloque.
                const boton = Array.from(botonesHorario).find(function (elemento) {
                    return elemento.dataset.bloque === bloque;
                });

                if (boton) {
                    cambiarEstadoVisual(boton, true);
                }

            });

        }

        // Recupera las asignaturas preferidas seleccionadas.
        const asignaturasSeleccionadas = formulario?.querySelectorAll(
            'input[name="asignaturas[]"]'
        );

        asignaturasSeleccionadas?.forEach(function (checkbox) {

            checkbox.checked = datosDisponibilidad.asignaturas.includes(
                checkbox.value
            );

        });

        // Elimina los datos temporales después de recuperarlos.
        sessionStorage.removeItem('datosDisponibilidadPendientes');

    }

    //MODAL

    // Comprueba los datos obligatorios antes de entregar la disponibilidad.
    formulario?.addEventListener('submit', function (evento) {

        // Obtiene los datos seleccionados.
        const docente = document.getElementById('id_docente');
        const periodo = document.getElementById('id_periodo');

        const bloques = bloquesSeleccionados
            ? bloquesSeleccionados.querySelectorAll('[data-input-bloque]')
            : [];

        const asignaturas = formulario.querySelectorAll(
            'input[name="asignaturas[]"]:checked'
        );

        // Comprueba si falta algún dato obligatorio.
        const faltanDatos =
            (docente && !docente.value) ||
            (periodo && !periodo.value) ||
            bloques.length === 0 ||
            asignaturas.length === 0;

        if (faltanDatos) {

            // Evita que se envíe el formulario.
            evento.preventDefault();

            // Muestra el modal de información incompleta.
            if (modalValidacion) {
                modalValidacion.hidden = false;
            }

        }

    });

    // Cierra el modal y permite continuar con el formulario.
    botonCerrarModal?.addEventListener('click', function () {

        modalValidacion.hidden = true;

    });

});
