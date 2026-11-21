
/**
 * Descripción: Configura la gráfica y la descarga del reporte de materias
 * con docente asignado por carrera y cuatrimestre.
 *
 * Entradas:
 * Datos de materias enviados desde Laravel.
 * Información de materias pendientes de asignación.
 *
 * Salidas:
 * Gráfica de barras apiladas y archivo PDF generado mediante FPDF.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ========================================
    // 1. OBTENER LOS ELEMENTOS PRINCIPALES
    // ========================================

    // Obtener el elemento donde se mostrará la gráfica.
    const elementoGrafica = document.getElementById(
        'grafica-materias-cuatrimestre'
    );

    // Obtener el botón para descargar el reporte.
    const botonDescargar = document.getElementById(
        'boton-descargar-reporte'
    );

    // Obtener los datos enviados desde Laravel.
    const datosReporte = window.datosGraficaMaterias || [];

    // Obtener los detalles de las materias pendientes.
    const materiasPendientesDetalle = window.materiasPendientesDetalle || [];

    // Obtener el mensaje que se muestra cuando no existen datos.
    const mensajeGrafica = document.getElementById('mensaje-grafica-vacia');

    // Obtener el contenido principal del reporte.
    const contenidoReporte = document.querySelector(
        '.pagina-reporte-materias'
    );

    // ========================================
    // 2. CALCULAR LOS INDICADORES
    // ========================================

    // Calcular el total de materias.
    const totalMaterias = datosReporte.reduce(
        (total, registro) => total + Number(registro.total || 0),
        0
    );

    // Calcular el total de materias asignadas.
    const totalAsignadas = datosReporte.reduce(
        (total, registro) => total + Number(registro.materiasAsignadas || 0),
        0
    );

    // Calcular el total de materias pendientes.
    const totalPendientes = datosReporte.reduce(
        (total, registro) => total + Number(registro.materiasPendientes || 0),
        0
    );

    // Calcular el porcentaje general de asignación.
    const porcentaje = totalMaterias > 0
        ? ((totalAsignadas / totalMaterias) * 100).toFixed(1)
        : '0.0';

    // ========================================
    // 3. CONFIGURAR LA GRÁFICA
    // ========================================

    // Comprobar que Chart.js se haya cargado correctamente.
    if (typeof Chart === 'undefined') {

        console.error('No se pudo cargar la librería Chart.js.');

    } else if (!elementoGrafica || datosReporte.length === 0) {

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

        // Obtener los cuatrimestres disponibles.
        const cuatrimestres = [...new Set(
            datosReporte.map(registro => Number(registro.cuatrimestre))
        )].sort((a, b) => a - b);

        // Obtener las carreras disponibles.
        const carreras = [...new Set(
            datosReporte.map(registro => registro.carrera)
        )];

        // Crear los conjuntos de datos de la gráfica.
        const conjuntosDatos = [];

        // Recorrer cada carrera.
        carreras.forEach(function (carrera) {

            // Obtener los registros de la carrera actual.
            const registrosCarrera = datosReporte.filter(
                registro => registro.carrera === carrera
            );

            // Obtener las materias asignadas por cuatrimestre.
            const materiasAsignadas = cuatrimestres.map(
                function (cuatrimestre) {

                    const registro = registrosCarrera.find(
                        dato => Number(dato.cuatrimestre) === cuatrimestre
                    );

                    return registro
                        ? Number(registro.materiasAsignadas || 0)
                        : 0;
                }
            );

            // Obtener las materias pendientes por cuatrimestre.
            const materiasPendientes = cuatrimestres.map(
                function (cuatrimestre) {

                    const registro = registrosCarrera.find(
                        dato => Number(dato.cuatrimestre) === cuatrimestre
                    );

                    return registro
                        ? Number(registro.materiasPendientes || 0)
                        : 0;
                }
            );

            // Agregar las materias asignadas.
            conjuntosDatos.push({
                label: carrera + ' - Materias asignadas',
                data: materiasAsignadas,
                backgroundColor: '#5A3E7D',
                borderRadius: 4,
                stack: carrera
            });

            // Agregar las materias pendientes.
            conjuntosDatos.push({
                label: carrera + ' - Materias pendientes',
                data: materiasPendientes,
                backgroundColor: '#D8C6E8',
                borderRadius: 4,
                stack: carrera
            });

        });

        // Configurar la gráfica de barras apiladas.
        new Chart(elementoGrafica, {

            type: 'bar',

            data: {
                labels: cuatrimestres.map(
                    cuatrimestre => cuatrimestre + '°'
                ),

                datasets: conjuntosDatos
            },

            options: {

                responsive: true,
                maintainAspectRatio: false,

                plugins: {

                    title: {
                        display: true,
                        text: 'Materias asignadas y pendientes por cuatrimestre'
                    },

                    legend: {
                        position: 'bottom'
                    },

                    tooltip: {
                        enabled: true
                    }
                },

                scales: {

                    x: {
                        stacked: true,

                        title: {
                            display: true,
                            text: 'Cuatrimestres'
                        }
                    },

                    y: {
                        stacked: true,
                        beginAtZero: true,

                        ticks: {
                            precision: 0
                        },

                        title: {
                            display: true,
                            text: 'Cantidad de materias'
                        }
                    }
                }
            }
        });
    }

    // ========================================
    // 4. CONFIGURAR LA DESCARGA DEL PDF
    // ========================================

    if (botonDescargar) {

        /**
         * Descripción: Obtiene la fecha actual en formato AAAA-MM-DD.
         */
        function obtenerFechaArchivo() {

            const hoy = new Date();

            const anio = hoy.getFullYear();
            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
            const dia = String(hoy.getDate()).padStart(2, '0');

            return anio + '-' + mes + '-' + dia;
        }

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

            // Comprobar que existan datos para generar el reporte.
            if (datosReporte.length === 0) {

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
                    '/reportes/materias-asignadas-cuatrimestre/pdf',
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': tokenCSRF.content,
                            'Accept': 'application/pdf'
                        },

                        body: JSON.stringify({
                            imagen: imagenBase64
                        })
                    }
                );

                // Comprobar si Laravel respondió correctamente.
                if (!respuesta.ok) {

                    let mensajeError = 'No fue posible generar el PDF.';

                    try {

                        const datosError = await respuesta.json();

                        if (datosError.mensaje) {
                            mensajeError = datosError.mensaje;
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
                // 7. DESCARGAR EL ARCHIVO PDF
                // ========================================

                // Obtener el documento PDF generado por Laravel.
                const archivoPDF = await respuesta.blob();

                // Crear una dirección temporal para la descarga.
                const direccionPDF = URL.createObjectURL(archivoPDF);

                // Crear un enlace temporal.
                const enlaceDescarga = document.createElement('a');

                enlaceDescarga.href = direccionPDF;

                enlaceDescarga.download =
                    'reporte-materias-asignadas-' + obtenerFechaArchivo() + '.pdf';

                // Ejecutar la descarga.
                document.body.appendChild(enlaceDescarga);

                enlaceDescarga.click();

                // Eliminar el enlace temporal.
                document.body.removeChild(enlaceDescarga);

                // Liberar la dirección temporal.
                URL.revokeObjectURL(direccionPDF);

                mostrarExito(
                    'El reporte de materias asignadas se descargó correctamente.',
                    '¡Descarga completa!'
                );

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
