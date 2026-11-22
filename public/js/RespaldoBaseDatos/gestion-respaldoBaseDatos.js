
/**
 * Descripción: Gestiona las acciones de generación y restauración
 * de respaldos de la base de datos, así como la paginación
 * de los respaldos disponibles.
 *
 * Entradas:
 * Formularios, botones, enlaces y controles de la vista
 * de respaldo y restauración.
 *
 * Salidas:
 * Confirmaciones, envío de formularios, mensajes de espera
 * y navegación entre las páginas de respaldos.
 */

document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       GENERACIÓN DE RESPALDOS
    ===================================================== */

    const formularioGenerar = document.getElementById(
        'formulario-generar-respaldo'
    );

    if (formularioGenerar) {

        formularioGenerar.addEventListener('submit', async function (evento) {

            // Detener el envío hasta que el usuario confirme.
            evento.preventDefault();

            const confirmado = await confirmarGenerarRespaldo();

            if (!confirmado) {
                return;
            }

            // Mostrar el estado de generación.
            const botonGenerar = formularioGenerar.querySelector(
                'button[type="submit"]'
            );

            if (botonGenerar) {
                botonGenerar.disabled = true;
                botonGenerar.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    Generando respaldo...
                `;
            }

            // submit() no vuelve a disparar este evento.
            formularioGenerar.submit();
        });
    }


    /* =====================================================
       RESTAURACIÓN DE RESPALDOS
    ===================================================== */

    // Obtener el formulario de restauración.
    const formularioRestaurar = document.getElementById(
        'formulario-restaurar-respaldo'
    );

    // Obtener el selector de archivos.
    const selectorRespaldo = document.getElementById(
        'archivo-respaldo'
    );

    // Obtener el botón de restauración.
    const botonRestaurar = document.getElementById(
        'boton-restaurar-respaldo'
    );

    // Comprobar que los elementos existan.
    if (formularioRestaurar && selectorRespaldo && botonRestaurar) {

        // Habilitar el botón cuando se seleccione un respaldo.
        selectorRespaldo.addEventListener('change', function () {

            botonRestaurar.disabled = selectorRespaldo.value === '';

        });

        // Escuchar el envío del formulario.
        formularioRestaurar.addEventListener('submit', function (evento) {

            // Evitar el envío automático.
            evento.preventDefault();

            // Comprobar que se haya seleccionado un respaldo.
            if (!selectorRespaldo.value) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Selecciona un respaldo',
                    text: 'Debes seleccionar un archivo de respaldo para continuar.',
                    confirmButtonColor: '#5A3E7D'
                });

                return;
            }

            // Obtener el nombre del respaldo seleccionado.
            const nombreArchivo = selectorRespaldo.options[
                selectorRespaldo.selectedIndex
            ].text;

            // Solicitar confirmación antes de restaurar.
            Swal.fire({
                html: `
                    <div class="modal-restauracion">

                        <div class="icono-modal-restauracion">
                            <i class="fa-solid fa-database"></i>
                            <span class="icono-alerta-restauracion">
                                <i class="fa-solid fa-exclamation"></i>
                            </span>
                        </div>

                        <h2>¿Restaurar este respaldo?</h2>

                        <p class="descripcion-modal-restauracion">
                            Se utilizará el siguiente archivo para recuperar
                            la base de datos:
                        </p>

                        <div class="archivo-modal-restauracion">
                            <i class="fa-solid fa-file-code"></i>
                            <span class="nombre-archivo-restauracion"></span>
                        </div>

                        <div class="advertencia-modal-restauracion">
                            <div class="titulo-advertencia-restauracion">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                Ten en cuenta
                            </div>

                            <p>
                                Los datos actuales serán reemplazados por los
                                del respaldo. Los cambios posteriores a su
                                generación se perderán.
                            </p>
                        </div>

                        <p class="nota-modal-restauracion">
                            Esta operación no se puede deshacer.
                        </p>

                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: `
                    <i class="fa-solid fa-rotate-left"></i>
                    Restaurar respaldo
                `,
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    popup: 'modal-sweet-restauracion',
                    confirmButton: 'boton-confirmar-restauracion',
                    cancelButton: 'boton-cancelar-restauracion',
                    actions: 'acciones-modal-restauracion'
                },
                reverseButtons: true,
                focusCancel: true,
                allowOutsideClick: false,

                // Insertar el nombre del respaldo de forma segura.
                didOpen: () => {
                    const nombreArchivoModal = Swal.getPopup().querySelector(
                        '.nombre-archivo-restauracion'
                    );

                    nombreArchivoModal.textContent = nombreArchivo;
                }

            }).then((resultado) => {

                // Restaurar únicamente cuando se confirme la operación.
                if (resultado.isConfirmed) {
                    formularioRestaurar.submit();
                }

            });

        });
    }


    /* =====================================================
       DESCARGA DE RESPALDOS
    ===================================================== */

    document.querySelectorAll('.boton-descargar-respaldo').forEach(function (enlace) {

        enlace.addEventListener('click', async function (evento) {

            // Detener la descarga hasta que el usuario confirme.
            evento.preventDefault();

            const nombreArchivo = enlace.dataset.nombre;

            const confirmado = await confirmarDescargarRespaldo(nombreArchivo);

            if (confirmado) {
                window.location.href = enlace.href;
            }
        });
    });


    /* =====================================================
       PAGINACIÓN DE RESPALDOS
    ===================================================== */

    // Obtener la tabla de respaldos.
    const tablaRespaldos = document.getElementById('tabla-respaldos');

    // Obtener los controles de paginación.
    const informacionPagina = document.getElementById(
        'informacion-pagina-respaldos'
    );

    const botonAnterior = document.getElementById(
        'pagina-anterior-respaldos'
    );

    const botonSiguiente = document.getElementById(
        'pagina-siguiente-respaldos'
    );

    // Comprobar que los elementos de paginación existan.
    if (!tablaRespaldos || !informacionPagina ||
        !botonAnterior || !botonSiguiente) {
        return;
    }

    // Obtener las filas que contienen respaldos.
    const filasRespaldos = Array.from(
        tablaRespaldos.querySelectorAll('.fila-respaldo')
    );

    // Cantidad de respaldos que se mostrarán por página.
    const filasPorPagina = 5;

    // Página inicial.
    let paginaActual = 1;


    /**
     * Descripción: Muestra los respaldos correspondientes
     * a la página actual y actualiza los controles.
     *
     * Entradas:
     * No recibe parámetros.
     *
     * Salidas:
     * Actualiza la visibilidad de las filas y los botones
     * de navegación.
     */
    function mostrarRespaldos() {

        const totalPaginas = Math.ceil(
            filasRespaldos.length / filasPorPagina
        );

        // Calcular los índices de las filas visibles.
        const inicio = (paginaActual - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;

        // Obtener las filas que corresponden a la página actual.
        const filasVisibles = new Set(
            filasRespaldos.slice(inicio, fin)
        );

        // Mostrar u ocultar las filas.
        filasRespaldos.forEach(function (fila) {
            fila.hidden = !filasVisibles.has(fila);
        });

        // Actualizar el indicador de página.
        informacionPagina.textContent =
            'Página ' + paginaActual + ' de ' + totalPaginas;

        // Habilitar o deshabilitar los botones.
        botonAnterior.disabled = paginaActual <= 1;
        botonSiguiente.disabled = paginaActual >= totalPaginas;
    }


    // Ir a la página anterior.
    botonAnterior.addEventListener('click', function () {

        if (paginaActual > 1) {
            paginaActual--;
            mostrarRespaldos();
        }
    });


    // Ir a la página siguiente.
    botonSiguiente.addEventListener('click', function () {

        const totalPaginas = Math.ceil(
            filasRespaldos.length / filasPorPagina
        );

        if (paginaActual < totalPaginas) {
            paginaActual++;
            mostrarRespaldos();
        }
    });


    // Inicializar la paginación cuando existen respaldos.
    if (filasRespaldos.length > 0) {
        mostrarRespaldos();
    }

});
