
/* =====================================================
   GESTIÓN DE FILTROS DE REPORTES
===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // Obtener todos los botones de filtros de las tarjetas.
    const botonesFiltros = document.querySelectorAll('.boton-filtros-reporte');

    // Recorrer los botones para agregar su funcionamiento.
    botonesFiltros.forEach(function (boton) {

        // Identificar el panel de filtros correspondiente.
        const identificador = boton.dataset.filtros;

        const panelFiltros = document.getElementById(identificador);

        // Comprobar que exista el panel relacionado con el botón.
        if (!panelFiltros) {
            return;
        }

        // Mostrar u ocultar los filtros al presionar el botón.
        boton.addEventListener('click', function () {

            const estaAbierto = boton.getAttribute('aria-expanded') === 'true';

            // Cambiar el estado de visibilidad del panel.
            panelFiltros.hidden = estaAbierto;

            // Actualizar el estado accesible del botón.
            boton.setAttribute('aria-expanded', String(!estaAbierto));

        });

    });


    // ========================================
    // 3. CONFIGURAR EL BOTÓN GENERAR
    //    DEL REPORTE DE DISPONIBILIDAD
    // ========================================

    // Obtener el selector de periodo y el botón Generar.
    const selectorPeriodoDisponibilidad = document.getElementById(
        'periodo-disponibilidad'
    );

    const botonGenerarDisponibilidad = document.getElementById(
        'boton-generar-disponibilidad'
    );

    // Comprobar que existan ambos elementos.
    if (selectorPeriodoDisponibilidad && botonGenerarDisponibilidad) {

        /**
         * Descripción:
         * Habilita o bloquea el botón Generar dependiendo
         * de si se seleccionó un periodo escolar.
         *
         * Entradas:
         * Valor seleccionado en el selector de periodo.
         *
         * Salidas:
         * Actualización del estado del botón Generar.
         */
        function actualizarBotonGenerarDisponibilidad() {

            const periodoSeleccionado =
                selectorPeriodoDisponibilidad.value !== '';

            // Habilitar o deshabilitar el botón Generar.
            botonGenerarDisponibilidad.disabled = !periodoSeleccionado;

            // Actualizar la apariencia del botón.
            botonGenerarDisponibilidad.classList.toggle(
                'boton-generar-deshabilitado',
                !periodoSeleccionado
            );

        }

        // Detectar cuando cambia el periodo seleccionado.
        selectorPeriodoDisponibilidad.addEventListener(
            'change',
            actualizarBotonGenerarDisponibilidad
        );

        // Establecer el estado inicial del botón.
        actualizarBotonGenerarDisponibilidad();

    }


    // ========================================
    // 4. CONFIGURAR LA DESCARGA DEL PDF
    // ========================================

    // Obtener los elementos necesarios para generar el PDF.
    const botonDescargar = document.getElementById(
        'boton-descargar-reporte'
    );

    const contenidoReporte = document.querySelector(
        '.pagina-reporte-disponibilidad'
    );

    const existenDatos = contenidoReporte
        ? contenidoReporte.querySelector('table tbody tr') !== null
        : false;

    if (botonDescargar) {

        botonDescargar.addEventListener('click', async function () {

            if (typeof html2canvas === 'undefined') {

                alert('No fue posible generar el PDF. Comprueba que html2canvas esté cargado.');

                return;

            }

            if (!contenidoReporte) {

                alert('No fue posible encontrar el contenido del reporte.');

                return;

            }

            if (!existenDatos) {

                alert('No existen datos para generar el reporte.');

                return;

            }

            botonDescargar.disabled = true;

            const contenidoOriginal = botonDescargar.innerHTML;

            // Mostrar porcentaje de avance.
            function actualizarProgreso(porcentaje, mensaje) {

                botonDescargar.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                    ${mensaje} ${porcentaje}%
                `;

            }

            try {

                // PASO 1. PREPARAR EL REPORTE.
                actualizarProgreso(10, 'Preparando reporte...');

                await new Promise(resolve => setTimeout(resolve, 100));

                // PASO 2. CAPTURAR EL REPORTE.
                actualizarProgreso(30, 'Capturando información...');

                const imagenReporte = await html2canvas(
                    contenidoReporte,
                    {

                        scale: 2,

                        backgroundColor: '#ffffff',

                        useCORS: true,

                        logging: false,

                        windowWidth: contenidoReporte.scrollWidth,

                        windowHeight: contenidoReporte.scrollHeight,

                        ignoreElements: function (elemento) {

                            return elemento.id === 'boton-descargar-reporte';

                        }

                    }
                );

                const imagenBase64 = imagenReporte.toDataURL('image/png');

                imagenReporte.width = 0;
                imagenReporte.height = 0;

                // PASO 3. VALIDAR TOKEN CSRF.
                actualizarProgreso(50, 'Enviando reporte...');

                const tokenCSRF = document.querySelector(
                    'meta[name="csrf-token"]'
                );

                if (!tokenCSRF) {

                    throw new Error('No se encontró el token de seguridad CSRF.');

                }

                // PASO 4. SOLICITAR LA GENERACIÓN DEL PDF.
                actualizarProgreso(70, 'Generando PDF...');

                const respuesta = await fetch(
                    '/reportes/disponibilidad-docente/pdf',
                    {

                        method: 'POST',

                        headers: {

                            'Content-Type': 'application/json',

                            'X-CSRF-TOKEN': tokenCSRF.content,

                            'Accept': 'application/pdf, application/json'

                        },

                        body: JSON.stringify({

                            imagen: imagenBase64

                        })

                    }
                );

                if (!respuesta.ok) {

                    let mensajeError = 'No fue posible generar el PDF.';

                    try {

                        const datosError = await respuesta.json();

                        if (datosError.mensaje) {
                            mensajeError = datosError.mensaje;
                        } else if (datosError.message) {
                            mensajeError = datosError.message;
                        }

                    } catch (error) {

                        console.error(
                            'No se pudo interpretar la respuesta del servidor.',
                            error
                        );

                    }

                    throw new Error(mensajeError);

                }

                // PASO 5. OBTENER EL ARCHIVO PDF.
                actualizarProgreso(90, 'Preparando descarga...');

                const archivoPDF = await respuesta.blob();

                if (
                    archivoPDF.size === 0 ||
                    archivoPDF.type !== 'application/pdf'
                ) {

                    throw new Error('El servidor no devolvió un archivo PDF válido.');

                }

                // PASO 6. DESCARGAR EL ARCHIVO.
                const direccionPDF = URL.createObjectURL(archivoPDF);

                const enlaceDescarga = document.createElement('a');

                enlaceDescarga.href = direccionPDF;

                enlaceDescarga.download =
                    'reporte-disponibilidad-docente.pdf';

                document.body.appendChild(enlaceDescarga);

                enlaceDescarga.click();

                document.body.removeChild(enlaceDescarga);

                actualizarProgreso(100, 'Descarga completada');

                setTimeout(function () {

                    URL.revokeObjectURL(direccionPDF);

                }, 1000);

            } catch (error) {

                console.error(
                    'Error al generar el PDF:',
                    error
                );

                alert(
                    error.message || 'Ocurrió un error al generar el PDF.'
                );

            } finally {

                botonDescargar.disabled = false;

                botonDescargar.innerHTML = contenidoOriginal;

            }

        });

    }

});
