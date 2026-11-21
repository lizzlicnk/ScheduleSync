/**
 * Descripción: Configura la gráfica y la descarga del reporte
 * de disponibilidad académica docente.
 *
 * Entradas:
 * Datos de disponibilidad enviados desde Laravel.
 * Total de docentes incluidos en el reporte.
 *
 * Salidas:
 * Gráfica de anillo y archivo PDF generado mediante FPDF.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ========================================
    // 1. OBTENER LOS ELEMENTOS PRINCIPALES
    // ========================================

    // Obtener el elemento donde se mostrará la gráfica.
    const elementoGrafica = document.getElementById(
        'grafica-disponibilidad-docente'
    );

    // Obtener el botón para descargar el reporte.
    const botonDescargar = document.getElementById(
        'boton-descargar-reporte'
    );

    // Obtener los datos enviados desde Laravel.
    const datosReporte = window.datosGraficaDisponibilidad || {};

    // Obtener el total de docentes.
    const totalDocentes = Number(
        window.totalDocentesDisponibilidad || 0
    );

    // Obtener el mensaje que se muestra cuando no existen datos.
    const mensajeGrafica = document.getElementById(
        'mensaje-grafica-vacia'
    );

    // Obtener el contenido principal del reporte.
    const contenidoReporte = document.querySelector(
        '.pagina-reporte-disponibilidad'
    );

    // Obtener el periodo seleccionado en el reporte.
    const elementoPeriodo = document.querySelector('.periodo-reporte');

    // ========================================
    // 2. OBTENER LOS DATOS DE LA GRÁFICA
    // ========================================

    // Obtener las etiquetas de la gráfica.
    const etiquetas = datosReporte.etiquetas || [];

    // Obtener los valores de cada categoría.
    const valores = datosReporte.valores || [];

    // Comprobar si existen docentes para mostrar.
    const existenDatos = totalDocentes > 0;


    // ========================================
    // 3. CONFIGURAR LA GRÁFICA DE ANILLO
    // ========================================

    // Plugin que escribe el porcentaje sobre cada sección del anillo.
    const pluginPorcentajes = {

        id: 'pluginPorcentajes',

        afterDatasetsDraw: function (grafica) {

            const contexto = grafica.ctx;
            const datos = grafica.data.datasets[0].data;
            const total = datos.reduce(function (suma, valor) {
                return suma + Number(valor || 0);
            }, 0);

            if (total === 0) {
                return;
            }

            grafica.getDatasetMeta(0).data.forEach(function (arco, indice) {

                const valor = Number(datos[indice] || 0);

                // No dibujar secciones vacías.
                if (valor === 0) {
                    return;
                }

                const porcentaje = ((valor / total) * 100).toFixed(1) + '%';

                // Obtener el centro de la sección.
                const posicion = arco.tooltipPosition();

                contexto.save();
                contexto.font = 'bold 16px sans-serif';
                contexto.textAlign = 'center';
                contexto.textBaseline = 'middle';

                // Texto blanco sobre el color oscuro y oscuro sobre el claro.
                contexto.fillStyle = indice === 0 ? '#FFFFFF' : '#51346F';

                contexto.fillText(porcentaje, posicion.x, posicion.y);
                contexto.restore();
            });
        }
    };

    // Comprobar que Chart.js se haya cargado correctamente.
    if (typeof Chart === 'undefined') {

        console.error('No se pudo cargar la librería Chart.js.');

    } else if (!elementoGrafica || !existenDatos) {

        // Mostrar el mensaje cuando no existen datos.
        if (mensajeGrafica) {
            mensajeGrafica.hidden = false;
        }

        // Ocultar la gráfica.
        if (elementoGrafica) {
            elementoGrafica.hidden = true;
        }

    } else {

        // Ocultar el mensaje de gráfica vacía.
        if (mensajeGrafica) {
            mensajeGrafica.hidden = true;
        }

        // Mostrar el elemento de la gráfica.
        elementoGrafica.hidden = false;

        // Crear la gráfica de anillo.
        new Chart(elementoGrafica, {

            type: 'doughnut',

            data: {

                labels: etiquetas,

                datasets: [{

                    label: 'Cantidad de docentes',

                    data: valores,

                    backgroundColor: [
                        '#5A3E7D',
                        '#D8C6E8'
                    ],

                    borderColor: [
                        '#51346F',
                        '#C5AEDB'
                    ],

                    borderWidth: 2,

                    hoverOffset: 12

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '65%',

                plugins: {

                    title: {

                        display: true,

                        text: 'Disponibilidades registradas y pendientes',

                        font: {
                            size: 16,
                            weight: 'bold'
                        },

                        padding: {
                            bottom: 20
                        }

                    },

                    legend: {

                        display: true,

                        position: 'bottom',

                        labels: {

                            padding: 20,

                            usePointStyle: true,

                            pointStyle: 'circle',

                            font: {
                                size: 13
                            }

                        }

                    },

                    tooltip: {

                        enabled: true,

                        callbacks: {

                            label: function (context) {

                                const valor = Number(context.raw || 0);

                                const porcentaje = totalDocentes > 0
                                    ? ((valor / totalDocentes) * 100).toFixed(1)
                                    : '0.0';

                                return context.label + ': ' +
                                    valor + ' docentes (' +
                                    porcentaje + '%)';

                            }

                        }

                    }

                }

            },

            plugins: [pluginPorcentajes]
        });

    }


    // ========================================
    // 4. CONFIGURAR LA DESCARGA DEL PDF
    // ========================================

    if (botonDescargar) {

        botonDescargar.addEventListener('click', async function () {

            
            // Comprobar que html2canvas esté disponible.
            if (typeof html2canvas === 'undefined') {

                console.error('No se pudo cargar html2canvas.');

                alert(
                    'No fue posible generar el PDF. Comprueba que html2canvas esté cargado.'
                );

                return;

            }

            // Comprobar que exista el contenido del reporte.
            if (!contenidoReporte) {

                console.error('No se encontró el contenido del reporte.');

                alert(
                    'No fue posible encontrar el contenido del reporte.'
                );

                return;

            }

            // Comprobar que existan docentes para generar el reporte.
            if (!existenDatos) {

                alert('No existen datos para generar el reporte.');

                return;

            }

            // Evitar múltiples solicitudes durante la generación del PDF.
            botonDescargar.disabled = true;

            // Guardar el contenido original del botón.
            const contenidoOriginal = botonDescargar.innerHTML;

            try {

                // Mostrar el estado de generación.
                botonDescargar.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                    Generando PDF...
                `;


                // ========================================
                // 5. CAPTURAR EL REPORTE
                // ========================================

                const imagenReporte = await html2canvas(
                    contenidoReporte,
                    {

                        scale: 2,

                        backgroundColor: '#ffffff',

                        useCORS: true,

                        logging: false,

                        windowWidth: contenidoReporte.scrollWidth,

                        windowHeight: contenidoReporte.scrollHeight,

                        // Evitar que el botón de descarga aparezca en el PDF.
                        ignoreElements: function (elemento) {

                            return elemento.id === 'boton-descargar-reporte';

                        }

                    }
                );

                // Convertir la captura a formato PNG Base64.
                const imagenBase64 = imagenReporte.toDataURL('image/png');

                // Liberar el canvas después de obtener la imagen.
                imagenReporte.width = 0;
                imagenReporte.height = 0;


                // ========================================
                // 6. ENVIAR LA CAPTURA A LARAVEL
                // ========================================

                // Obtener el token CSRF de la página.
                const tokenCSRF = document.querySelector(
                    'meta[name="csrf-token"]'
                );

                if (!tokenCSRF) {

                    throw new Error(
                        'No se encontró el token de seguridad CSRF.'
                    );

                }

                // Enviar la imagen al controlador de reportes.
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


                // ========================================
                // 7. COMPROBAR LA RESPUESTA DE LARAVEL
                // ========================================

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


                // ========================================
                // 8. DESCARGAR EL ARCHIVO PDF
                // ========================================

                // Obtener el documento PDF generado por Laravel.
                const archivoPDF = await respuesta.blob();

                // Comprobar que el servidor haya generado un PDF.
                if (
                    archivoPDF.size === 0 ||
                    archivoPDF.type !== 'application/pdf'
                ) {

                    throw new Error(
                        'El servidor no devolvió un archivo PDF válido.'
                    );

                }

                // Crear una dirección temporal para la descarga.
                const direccionPDF = URL.createObjectURL(archivoPDF);

                // Crear un enlace temporal.
                const enlaceDescarga = document.createElement('a');

                enlaceDescarga.href = direccionPDF;

                //Nombre archivo
                const periodo = elementoPeriodo
                    ? elementoPeriodo.textContent.trim()
                        .replace(/\s+/g, '_')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .toUpperCase()
                    : 'SIN_PERIODO';

                // Obtener la fecha actual.
                const fechaActual = new Date();

                const fecha = [
                    fechaActual.getFullYear(),
                    String(fechaActual.getMonth() + 1).padStart(2, '0'),
                    String(fechaActual.getDate()).padStart(2, '0')
                ].join('-');

                // Asignar el nombre al archivo PDF.
                enlaceDescarga.download =
                    `reporte-disponibilidad-docente_${periodo}_${fecha}.pdf`;

                // Ejecutar la descarga.
                document.body.appendChild(enlaceDescarga);

                enlaceDescarga.click();

                mostrarExito(
                    'El reporte de disponibilidad docente se descargó correctamente.',
                    '¡Descarga completa!'
                );

                // Eliminar el enlace temporal.
                document.body.removeChild(enlaceDescarga);

                // Liberar la dirección temporal.
                setTimeout(function () {

                    URL.revokeObjectURL(direccionPDF);

                }, 1000);


            } catch (error) {

                console.error('Error al generar el PDF:', error);

                mostrarError(
                    error.message || 'Ocurrió un error al generar el PDF.',
                    'No se pudo descargar el reporte'
                );

            } finally {
                botonDescargar.disabled = false;
                botonDescargar.innerHTML = contenidoOriginal;
            }

        });

    }

});