// CONFIGURACIÓN GENERAL DE LAS ALERTAS

const configuracionAlertas = {
    confirmButtonColor: '#5A3E7D',
    cancelButtonColor: '#6c757d',

    customClass: {
        popup: 'alerta-schedulesync',
        confirmButton: 'boton-confirmar-alerta',
        cancelButton: 'boton-cancelar-alerta'
    },

    buttonsStyling: true
};

/**
 * Descripción: Muestra una alerta de éxito.
 * Entradas:
 * mensaje: Mensaje que se mostrará.
 * titulo: Título de la alerta.
 */
function mostrarExito( mensaje, titulo = '¡Operación exitosa!' ) {
    Swal.fire({
        ...configuracionAlertas,
        icon: 'success',
        title: titulo,
        text: mensaje,
        confirmButtonText: 'Aceptar'
    });
}

/**
 * Descripción: Muestra una alerta de error.
 *
 * Entradas:
 * mensaje: Mensaje que se mostrará.
 * titulo: Título de la alerta.
 * icono: Icono que representa el error.
 */
function mostrarError( mensaje, titulo = '¡Ha ocurrido un error!', icono = 'fa-circle-exclamation' ) {
    Swal.fire({
        ...configuracionAlertas,
        icon: 'error',
        iconHtml: `<i class="fa-solid ${icono}"></i>`,
        title: titulo,
        text: mensaje,
        confirmButtonText: 'Aceptar'
    });
}


/**
 * Descripción: Muestra una alerta de advertencia.
 *
 * Entradas:
 * mensaje: Mensaje que se mostrará.
 * titulo: Título de la alerta.
 * icono: Icono que representa la advertencia.
 */
function mostrarAdvertencia( mensaje, titulo = '¡Atención!', icono = 'fa-triangle-exclamation' ) {
    Swal.fire({
        ...configuracionAlertas,
        icon: 'warning',
        iconHtml: `<i class="fa-solid ${icono}"></i>`,
        title: titulo,
        text: mensaje,
        confirmButtonText: 'Entendido'
    });
}


/**
 * Descripción: Muestra una alerta informativa.
 *
 * Entradas:
 * mensaje: Mensaje que se mostrará.
 * titulo: Título de la alerta.
 * icono: Icono que representa la información.
 */
function mostrarInformacion( mensaje, titulo = 'Información', icono = 'fa-circle-info' ) {
    Swal.fire({
        ...configuracionAlertas,
        icon: 'info',
        iconHtml: `<i class="fa-solid ${icono}"></i>`,
        title: titulo,
        text: mensaje,
        confirmButtonText: 'Aceptar'
    });
}

/**
 * Descripción: Solicita confirmación antes de eliminar la carga académica de un docente.
 * Entradas: nombreUsuario: Nombre completo del docente.
 * Salida: Devuelve true si se confirma la eliminación y false si se cancela.
 */
async function confirmarEliminarCarga(nombreUsuario) {

    // Escapar el nombre para evitar interpretar caracteres como HTML.
    const nombreSeguro = document.createElement('span');

    nombreSeguro.textContent = nombreUsuario;

    // Mostrar el modal de confirmación.
    const resultado = await Swal.fire({

        customClass: {
            popup: 'alerta-schedulesync modal-eliminar-carga',
            confirmButton: 'boton-eliminar-carga',
            cancelButton: 'boton-cancelar-carga'
        },

        icon: 'warning',
        title: '¿Deseas eliminar esta carga académica?',

        html: `
            <div class="mensaje-eliminar-carga">
                <p> Estás a punto de dar de baja la carga académica de: </p>

                <strong class="nombre-docente-alerta">
                    ${nombreSeguro.innerHTML}
                </strong>

                <p class="nota-eliminar-carga"> Esta acción no se puede deshacer. </p>
            </div>
        `,

        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC3545',
        cancelButtonColor: 'transparent',
        reverseButtons: true,
        buttonsStyling: false
    });

    // Devolver el resultado de la confirmación.
    return resultado.isConfirmed;
}

/**
 * Descripción: Solicita confirmación antes de eliminar una disponibilidad.
 * Salida: true si se confirma, false si se cancela.
 */
async function confirmarEliminarDisponibilidad(nombreUsuario) {

    const nombreSeguro = document.createElement('span');
    nombreSeguro.textContent = nombreUsuario;

    const resultado = await Swal.fire({
        customClass: {
            popup: 'alerta-schedulesync modal-eliminar-carga',
            confirmButton: 'boton-eliminar-carga',
            cancelButton: 'boton-cancelar-carga'
        },
        icon: 'warning',
        title: '¿Deseas eliminar esta disponibilidad?',
        html: `
            <div class="mensaje-eliminar-carga">
                <p> Estás a punto de eliminar la disponibilidad académica de: </p>
                <strong class="nombre-docente-alerta">${nombreSeguro.innerHTML}</strong>
                <p class="nota-eliminar-carga"> Esta acción no se puede deshacer. </p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC3545',
        cancelButtonColor: 'transparent',
        reverseButtons: true,
        buttonsStyling: false
    });

    return resultado.isConfirmed;
}

/**
 * Descripción: Solicita confirmación antes de entregar la disponibilidad.
 * Salida: true si se confirma, false si se cancela.
 */
async function confirmarEntregaDisponibilidad() {

    const resultado = await Swal.fire({
        ...configuracionAlertas,
        icon: 'question',
        title: '¿Entregar disponibilidad?',
        text: 'Después de entregarla, quedará registrada como confirmada.',
        showCancelButton: true,
        confirmButtonText: 'Sí, entregar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    return resultado.isConfirmed;
}

/**
 * Descripción: Solicita confirmación antes de desactivar un horario.
 * Entradas:
 * nombreHorario: Nombre del horario.
 * tieneDetalles: Indica si el horario cuenta con asignaturas registradas.
 * Salida: true si se confirma, false si se cancela.
 */
async function confirmarDesactivarHorario(nombreHorario, tieneDetalles) {

    const nombreSeguro = document.createElement('span');
    nombreSeguro.textContent = nombreHorario;

    // Definir el mensaje según los detalles registrados.
    const mensajeDetalles = tieneDetalles
    ? 'Este horario ya cuenta con asignaturas y horarios registrados. Al desactivarlo, toda su información se conservará en el historial.'
    : 'El horario se conservará en el historial como inactivo.';

    const resultado = await Swal.fire({
        customClass: {
            popup: 'alerta-schedulesync modal-eliminar-carga',
            confirmButton: 'boton-eliminar-carga',
            cancelButton: 'boton-cancelar-carga'
        },
        icon: 'warning',
        title: '¿Deseas desactivar este horario?',
        html: `
            <div class="mensaje-eliminar-carga">
                <p>Estás a punto de desactivar el horario:</p>
                <strong class="nombre-docente-alerta">${nombreSeguro.innerHTML}</strong>
                <p class="nota-eliminar-carga">${mensajeDetalles}</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC3545',
        cancelButtonColor: 'transparent',
        reverseButtons: true,
        buttonsStyling: false
    });

    return resultado.isConfirmed;
}

                                    // HORARIOS
/**
 * Descripción: Muestra una lista de errores de validación.
 * Entradas: errores (arreglo de textos) y titulo.
 */
function mostrarErroresValidacion(errores, titulo = '¡Revisa la información!') {

    // Escapar cada mensaje para no interpretarlo como HTML.
    const items = errores.map(function (mensaje) {
        const nodo = document.createElement('span');
        nodo.textContent = mensaje;
        return `<li>${nodo.innerHTML}</li>`;
    }).join('');

    Swal.fire({
        ...configuracionAlertas,
        icon: 'error',
        iconHtml: '<i class="fa-solid fa-circle-exclamation"></i>',
        title: titulo,
        html: `<ul style="text-align:left; padding-left:20px; margin:0;">${items}</ul>`,
        confirmButtonText: 'Aceptar'
    });
}

/**
 * Descripción: Confirma el guardado definitivo del horario.
 * Salida: true si se confirma, false si se cancela.
 */
async function confirmarGuardadoDefinitivo() {

    const resultado = await Swal.fire({
        ...configuracionAlertas,
        icon: 'question',
        title: '¿Guardar el horario definitivamente?',
        text: 'Una vez guardado dejará de ser borrador y no podrás modificarlo desde este formulario.',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    return resultado.isConfirmed;
}

//RESPALDO Y RESTAURACIOÓN DE LA BASE DE DATOS

/**
 * Descripción: Solicita confirmación antes de generar un respaldo.
 * Salida: true si se confirma, false si se cancela.
 */
async function confirmarGenerarRespaldo() {

    const resultado = await Swal.fire({
        ...configuracionAlertas,
        icon: 'question',
        title: '¿Deseas generar un respaldo?',
        text: 'Se creará una copia de seguridad con la información actual de la base de datos.',
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    return resultado.isConfirmed;
}

/**
 * Descripción: Solicita confirmación antes de descargar un respaldo.
 * Entradas: nombreArchivo: Nombre del archivo de respaldo.
 * Salida: true si se confirma, false si se cancela.
 */
async function confirmarDescargarRespaldo(nombreArchivo) {

    // Escapar el nombre para no interpretarlo como HTML.
    const nombreSeguro = document.createElement('span');
    nombreSeguro.textContent = nombreArchivo;

    const resultado = await Swal.fire({
        ...configuracionAlertas,
        icon: 'question',
        title: '¿Deseas descargar este respaldo?',
        html: `
            <p>Se descargará el archivo:</p>
            <strong>${nombreSeguro.innerHTML}</strong>
        `,
        showCancelButton: true,
        confirmButtonText: 'Sí, descargar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    return resultado.isConfirmed;
}