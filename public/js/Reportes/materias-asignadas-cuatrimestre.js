
/**
 * Descripción: Configura la gráfica y la descarga del reporte de materias
 * con docente asignado por carrera y cuatrimestre.
 *
 * Entradas:
 * Datos de materias enviados desde Laravel.
 *
 * Salidas:
 * Gráfica de barras apiladas y archivo PDF del reporte.
 */

document.addEventListener('DOMContentLoaded', function () {

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

    // Obtener los indicadores del reporte.
    const elementoTotalMaterias = document.getElementById('total-materias');
    const elementoMateriasAsignadas = document.getElementById('materias-asignadas');
    const elementoMateriasPendientes = document.getElementById('materias-pendientes');
    const elementoPorcentajeAsignacion = document.getElementById('porcentaje-asignacion');

    // Obtener el mensaje que se muestra cuando no existen datos.
    const mensajeGrafica = document.getElementById('mensaje-grafica-vacia');

    // Comprobar que Chart.js se haya cargado correctamente.
    if (typeof Chart === 'undefined') {
        console.error('No se pudo cargar la librería Chart.js.');
        return;
    }

    // Calcular los totales del reporte.
    const totalMaterias = datosReporte.reduce(
        (total, registro) => total + Number(registro.total || 0),
        0
    );

    const totalAsignadas = datosReporte.reduce(
        (total, registro) => total + Number(registro.materiasAsignadas || 0),
        0
    );

    const totalPendientes = datosReporte.reduce(
        (total, registro) => total + Number(registro.materiasPendientes || 0),
        0
    );

    // Calcular el porcentaje general de asignación.
    const porcentaje = totalMaterias > 0
        ? ((totalAsignadas / totalMaterias) * 100).toFixed(1)
        : '0.0';

    // Mostrar los indicadores del reporte.
    if (elementoTotalMaterias) {
        elementoTotalMaterias.textContent = totalMaterias;
    }

    if (elementoMateriasAsignadas) {
        elementoMateriasAsignadas.textContent = totalAsignadas;
    }

    if (elementoMateriasPendientes) {
        elementoMateriasPendientes.textContent = totalPendientes;
    }

    if (elementoPorcentajeAsignacion) {
        elementoPorcentajeAsignacion.textContent = porcentaje + '%';
    }

    // Comprobar si existen datos para generar la gráfica.
    if (!elementoGrafica || datosReporte.length === 0) {

        if (mensajeGrafica) {
            mensajeGrafica.hidden = false;
        }

        if (elementoGrafica) {
            elementoGrafica.hidden = true;
        }

    } else {

        if (mensajeGrafica) {
            mensajeGrafica.hidden = true;
        }

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

        // Configurar la gráfica de barras apiladas con Chart.js.
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

    // Configurar la descarga del reporte en formato PDF.
    if (botonDescargar) {

        botonDescargar.addEventListener('click', async function () {

            // Comprobar que las librerías necesarias estén disponibles.
            if (
                !window.jspdf ||
                typeof html2canvas === 'undefined'
            ) {
                console.error(
                    'No se pudieron cargar las librerías necesarias para generar el PDF.'
                );
                return;
            }

            // Comprobar que existan datos para generar el reporte.
            if (datosReporte.length === 0) {
                alert('No existen datos para generar el reporte.');
                return;
            }

            // Crear el documento PDF en orientación horizontal.
            const { jsPDF } = window.jspdf;

            const documentoPDF = new jsPDF('landscape', 'mm', 'a4');

            // Agregar el encabezado del documento.
            documentoPDF.setFontSize(18);
            documentoPDF.setTextColor(81, 52, 111);

            documentoPDF.text(
                'Reporte de materias con docente asignado',
                15,
                18
            );

            // Agregar la descripción del sistema.
            documentoPDF.setFontSize(10);
            documentoPDF.setTextColor(100, 100, 100);

            documentoPDF.text(
                'Schedule-Sync | Planeacion academica',
                15,
                25
            );

            // Agregar los indicadores generales.
            documentoPDF.setFontSize(11);
            documentoPDF.setTextColor(50, 50, 50);

            documentoPDF.text(
                `Total de materias: ${totalMaterias}`,
                15,
                38
            );

            documentoPDF.text(
                `Materias asignadas: ${totalAsignadas}`,
                85,
                38
            );

            documentoPDF.text(
                `Materias pendientes: ${totalPendientes}`,
                160,
                38
            );

            documentoPDF.text(
                `Porcentaje de asignacion: ${porcentaje}%`,
                240,
                38
            );

            // Convertir la gráfica en una imagen para el PDF.
            if (elementoGrafica && datosReporte.length > 0) {

                const imagenGrafica = await html2canvas(elementoGrafica, {
                    scale: 2,
                    backgroundColor: '#ffffff'
                });

                const imagenBase64 = imagenGrafica.toDataURL('image/png');

                documentoPDF.addImage(
                    imagenBase64,
                    'PNG',
                    15,
                    48,
                    265,
                    100
                );
            }

            // Establecer la posición inicial de la tabla.
            let posicionY = 160;

            // Configurar los encabezados de la tabla.
            documentoPDF.setFontSize(10);
            documentoPDF.setTextColor(81, 52, 111);

            const encabezados = [
                'Carrera',
                'Cuatrimestre',
                'Total',
                'Asignadas',
                'Pendientes',
                'Porcentaje'
            ];

            const posicionesX = [15, 65, 105, 145, 185, 235];

            // Agregar los encabezados al documento.
            encabezados.forEach(function (encabezado, indice) {

                documentoPDF.text(
                    encabezado,
                    posicionesX[indice],
                    posicionY
                );

            });

            posicionY += 8;

            // Configurar el color del contenido de la tabla.
            documentoPDF.setTextColor(70, 70, 70);

            // Recorrer los registros para agregarlos a la tabla.
            datosReporte.forEach(function (registro) {

                const total = Number(registro.total || 0);
                const asignadas = Number(registro.materiasAsignadas || 0);
                const pendientes = Number(registro.materiasPendientes || 0);

                // Calcular el porcentaje de asignación del registro.
                const porcentajeRegistro = total > 0
                    ? ((asignadas / total) * 100).toFixed(1)
                    : '0.0';

                const valores = [
                    registro.carrera,
                    registro.cuatrimestre + '°',
                    String(total),
                    String(asignadas),
                    String(pendientes),
                    porcentajeRegistro + '%'
                ];

                // Agregar los valores de cada fila.
                valores.forEach(function (valor, indice) {

                    documentoPDF.text(
                        String(valor),
                        posicionesX[indice],
                        posicionY
                    );

                });

                posicionY += 8;

                // Crear una nueva página si no existe espacio suficiente.
                if (posicionY > 190) {

                    documentoPDF.addPage();

                    posicionY = 20;

                    documentoPDF.setFontSize(10);
                    documentoPDF.setTextColor(81, 52, 111);

                    encabezados.forEach(function (encabezado, indice) {

                        documentoPDF.text(
                            encabezado,
                            posicionesX[indice],
                            posicionY
                        );

                    });

                    posicionY += 8;

                    documentoPDF.setTextColor(70, 70, 70);
                }

            });

            // Descargar el documento PDF.
            documentoPDF.save('Reporte_Materias_Asignadas.pdf');

        });
    }

});
