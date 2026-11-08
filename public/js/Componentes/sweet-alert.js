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


