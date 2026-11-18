
 // =========================================
 // IMPORTACIONES DE FULLCALENDAR
 // =========================================

import { Calendar } from 'fullcalendar';
import themePlugin from 'fullcalendar/themes/monarch';
import timeGridPlugin from 'fullcalendar/timegrid';
import interactionPlugin, { Draggable } from 'fullcalendar/interaction';

import 'fullcalendar/skeleton.css';
import 'fullcalendar/themes/monarch/theme.css';
import 'fullcalendar/themes/monarch/palettes/purple.css';


// =========================================
// INICIALIZACIÓN DEL CALENDARIO
// =========================================

document.addEventListener('DOMContentLoaded', function () {

    // Obtener el contenedor del calendario.
    const elementoCalendario = document.getElementById(
        'calendario-horarios'
    );

    // Obtener el botón para mostrar u ocultar el sábado.
    const botonSabado = document.getElementById('boton-sabado');

    // Obtener el contenedor de las asignaturas.
    const listaAsignaturas = document.getElementById(
        'lista-asignaturas-disponibles'
    );

    // Evitar errores si esta vista no contiene el calendario.
    if (!elementoCalendario) {
        return;
    }

    // Indica si el sábado está visible.
    let sabadoVisible = false;

    // Guardar las tarjetas originales para poder recuperarlas.
    const tarjetasOriginales = new Map();

    // Contador para identificar cada bloque del calendario.
    let contadorEventos = 0;


    // =========================================
    // CREAR EL CALENDARIO
    // =========================================

    const calendario = new Calendar(elementoCalendario, {

        // Complementos necesarios.
        plugins: [
            themePlugin,
            timeGridPlugin,
            interactionPlugin
        ],

        // Vista semanal.
        initialView: 'timeGridWeek',

        // Establecer un lunes como fecha inicial.
        initialDate: '2026-01-05',

        // La semana comienza el lunes.
        firstDay: 1,

        // Ocultar domingo y sábado inicialmente.
        hiddenDays: [0, 6],

        // Configuración del encabezado.
        headerToolbar: false,

        // Mostrar los nombres de los días en español.
        dayHeaderContent: function (info) {
            return info.date.toLocaleDateString('es-MX', {
                weekday: 'long'
            });
        },

        // No mostrar la fila de todo el día.
        allDaySlot: false,

        // Intervalos de una hora.
        slotDuration: '01:00:00',

        // La selección y el redimensionamiento se ajustan a intervalos de una hora.
        snapDuration: '01:00:00',

        // Horario visible.
        slotMinTime: '07:00:00',
        slotMaxTime: '22:00:00',

        // Formato de las horas.
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },

        // Permitir seleccionar arrastrando el cursor.
        selectable: true,

        // Mostrar una selección temporal mientras se arrastra.
        selectMirror: true,

        // Permitir seleccionar varias horas consecutivas.
        selectMinDistance: 0,

        // Permitir recibir tarjetas externas.
        droppable: true,

        // Permitir mover y modificar las asignaturas.
        editable: true,

        // Permitir cambiar el horario de inicio.
        eventStartEditable: true,

        // Permitir modificar la duración de las asignaturas.
        eventDurationEditable: true,

        // Permitir cambiar la duración desde el borde inferior.
        eventResizableFromStart: false,


        // =========================================
        // VALIDAR SELECCIONES
        // =========================================

        // No permitir selecciones que abarquen varios días.
        selectAllow: function (info) {

            // La hora final es exclusiva.
            const ultimaFecha = new Date(
                info.end.getTime() - 1
            );

            // Comprobar que el inicio y el final pertenecen al mismo día.
            return (
                !info.allDay &&
                info.start.toDateString() === ultimaFecha.toDateString()
            );
        },


        // =========================================
        // SELECCIONAR BLOQUES DEL CALENDARIO
        // =========================================

        select: function (info) {

            // Obtener las horas seleccionadas.
            const horaInicio = info.start.toLocaleTimeString('es-MX', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            const horaFin = info.end.toLocaleTimeString('es-MX', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            // Calcular la duración del bloque.
            const cantidadHoras = (
                info.end.getTime() - info.start.getTime()
            ) / (1000 * 60 * 60);

            // Mostrar los datos de la selección.
            alert(
                'Bloque seleccionado:\n' +
                'Horario: ' + horaInicio + ' - ' + horaFin + '\n' +
                'Duración: ' + cantidadHoras + ' hora(s)'
            );

            // Limpiar la selección visual.
            calendario.unselect();
        },


        // =========================================
        // RECIBIR ASIGNATURAS ARRASTRADAS
        // =========================================

        eventReceive: function (info) {

            // Obtener los datos adicionales de la asignatura.
            const datos = info.event.extendedProps;

            // Validar que se haya seleccionado un docente.
            if (!datos.id_docente) {

                info.event.remove();

                mostrarAdvertencia(
                    'Selecciona un docente antes de agregar la asignatura.',
                    '¡Selecciona un docente!'
                );

                return;
            }

            // Validar que se haya seleccionado un espacio.
            if (!datos.aula) {

                info.event.remove();

                mostrarAdvertencia(
                    'Selecciona un espacio antes de agregar la asignatura.',
                    '¡Selecciona un espacio!'
                );

                return;
            }

            // Aplicar los colores de ScheduleSync.
            info.event.setProp('backgroundColor', '#5A3E7D');
            info.event.setProp('borderColor', '#5A3E7D');
            info.event.setProp('textColor', '#FFFFFF');

            // Actualizar las horas disponibles de la tarjeta.
            actualizarTarjetaDisponible(datos.id_asignatura);

            // Actualizar los campos del formulario.
            actualizarDetallesHorario();

            // Mostrar los datos recibidos.
            console.log('Asignatura colocada:', info.event);
        },


        // =========================================
        // REGRESAR ASIGNATURAS A DISPONIBLES
        // =========================================

        eventDragStop: function (info) {

            // Verificar que exista el contenedor de asignaturas.
            if (!listaAsignaturas || !info.jsEvent) {
                return;
            }

            // Obtener la posición donde terminó el arrastre.
            const elementoDestino = document.elementFromPoint(
                info.jsEvent.clientX,
                info.jsEvent.clientY
            );

            // Comprobar si se soltó dentro del contenedor.
            if (
                !elementoDestino ||
                !listaAsignaturas.contains(elementoDestino)
            ) {
                return;
            }

            // Eliminar el evento del calendario.
            // eventRemove se encargará de recuperar las horas.
            info.event.remove();

            console.log(
                'Asignatura regresada a disponibles:',
                info.event.title
            );
        },


        // =========================================
        // DETECTAR CAMBIOS DE HORARIO
        // =========================================

        eventDrop: function (info) {

            // Actualizar los campos del formulario.
            actualizarDetallesHorario();

            // Mostrar el nuevo horario.
            console.log('Horario actualizado:', {
                asignatura: info.event.title,
                inicio: info.event.start,
                fin: info.event.end
            });
        },


        // =========================================
        // DETECTAR CAMBIOS EN LA DURACIÓN
        // =========================================

        eventResize: function (info) {

            // Obtener los datos adicionales de la asignatura.
            const datos = info.event.extendedProps;

            // Obtener las horas totales originales.
            const horasTotales = Number(datos.totalHoras);

            // Evitar errores si el evento no tiene fechas.
            if (!info.event.start || !info.event.end) {
                info.revert();
                return;
            }

            // Validar que exista una cantidad total de horas válida.
            if (!horasTotales || horasTotales <= 0) {

                info.revert();

                mostrarAdvertencia(
                    'No se encontró una cantidad máxima de horas válida.'
                );

                return;
            }

            // Calcular la duración del bloque modificado.
            const duracionHoras = (
                info.event.end.getTime() - info.event.start.getTime()
            ) / (1000 * 60 * 60);

            // Validar que dure al menos una hora.
            if (duracionHoras < 1) {

                info.revert();

                mostrarAdvertencia(
                    'Una asignatura debe durar al menos una hora.'
                );

                return;
            }

            // Obtener los demás eventos de la misma asignatura.
            const otrosEventos = calendario.getEvents().filter(function (evento) {

                return (
                    evento.id !== info.event.id &&
                    String(evento.extendedProps.id_asignatura) ===
                    String(datos.id_asignatura)
                );

            });

            // Calcular las horas ocupadas por los demás bloques.
            const horasOcupadas = otrosEventos.reduce(function (total, evento) {

                if (!evento.start || !evento.end) {
                    return total;
                }

                const horas = (
                    evento.end.getTime() - evento.start.getTime()
                ) / (1000 * 60 * 60);

                return total + horas;

            }, 0);

            // Calcular las horas máximas que puede utilizar este bloque.
            const horasMaximas = horasTotales - horasOcupadas;

            // Validar que no se excedan las horas totales.
            if (duracionHoras > horasMaximas) {

                info.revert();

                mostrarAdvertencia(
                    `Solo puedes utilizar ${horasMaximas} hora(s) adicionales para esta asignatura.`
                );

                return;
            }

            // Actualizar las horas disponibles de la tarjeta.
            actualizarTarjetaDisponible(datos.id_asignatura);

            // Actualizar los campos del formulario.
            actualizarDetallesHorario();

            // Mostrar los datos de la duración actualizada.
            console.log('Duración actualizada:', {
                asignatura: info.event.title,
                horasDelBloque: duracionHoras,
                horasOcupadas: horasOcupadas + duracionHoras,
                horasTotales: horasTotales
            });
        },


        // =========================================
        // DETECTAR ELIMINACIÓN DE ASIGNATURAS
        // =========================================

        eventRemove: function (info) {

            // Obtener el identificador de la asignatura.
            const idAsignatura = info.event.extendedProps.id_asignatura;

            // Actualizar las horas disponibles de la tarjeta.
            actualizarTarjetaDisponible(idAsignatura);

            // Actualizar los campos del formulario.
            actualizarDetallesHorario();
        },


        // =========================================
        // CONFIGURACIÓN VISUAL
        // =========================================

        height: 'auto',
        expandRows: true,
        nowIndicator: false,
        allDayMaintainDuration: true

    });


    // =========================================
    // CALCULAR HORAS OCUPADAS DE UNA ASIGNATURA
    // =========================================

    /**
     * Descripción: Calcula las horas utilizadas por una asignatura en el calendario.
     * Entradas: Recibe el identificador de la asignatura.
     * Salidas: Devuelve la cantidad de horas ocupadas.
     */
    function obtenerHorasOcupadas(idAsignatura) {

        // Obtener los eventos correspondientes a la asignatura.
        const eventosAsignatura = calendario.getEvents().filter(function (evento) {

            return (
                String(evento.extendedProps.id_asignatura) ===
                String(idAsignatura)
            );

        });

        // Sumar la duración de todos sus bloques.
        return eventosAsignatura.reduce(function (total, evento) {

            // Evitar errores si el evento no tiene fechas.
            if (!evento.start || !evento.end) {
                return total;
            }

            // Calcular la duración del evento.
            const horas = (
                evento.end.getTime() - evento.start.getTime()
            ) / (1000 * 60 * 60);

            return total + horas;

        }, 0);
    }


    // =========================================
    // ACTUALIZAR TARJETA DE ASIGNATURA
    // =========================================

    /**
     * Descripción: Actualiza las horas disponibles de una asignatura.
     * Entradas: Recibe el identificador de la asignatura.
     * Salidas: Actualiza la tarjeta o la elimina cuando ya no quedan horas.
     */
    function actualizarTarjetaDisponible(idAsignatura) {

        // Obtener la tarjeta original.
        const tarjeta = tarjetasOriginales.get(String(idAsignatura));

        // Verificar que exista la tarjeta.
        if (!tarjeta) {
            return;
        }

        // Obtener las horas totales originales.
        const horasTotales = Number(tarjeta.dataset.horasTotales);

        // Obtener las horas que ya están ocupadas.
        const horasOcupadas = obtenerHorasOcupadas(idAsignatura);

        // Calcular las horas restantes.
        const horasRestantes = Math.max(
            0,
            horasTotales - horasOcupadas
        );

        // Actualizar el valor de horas disponibles.
        tarjeta.dataset.cantidadHoras = horasRestantes;

        // Obtener el elemento que muestra las horas.
        const etiquetaHoras = tarjeta.querySelector(
            '.informacion-asignatura span'
        );

        // Actualizar el texto de las horas disponibles.
        if (etiquetaHoras) {
            etiquetaHoras.textContent = `${horasRestantes} horas`;
        }

        // Si todavía existen horas disponibles.
        if (horasRestantes > 0) {

            // Recuperar la tarjeta si no está en la lista.
            if (
                listaAsignaturas &&
                !listaAsignaturas.contains(tarjeta)
            ) {

                // Quitar mensajes anteriores de la lista.
                const mensajeLista = listaAsignaturas.querySelector(
                    '.texto-secundario'
                );

                if (mensajeLista) {
                    mensajeLista.remove();
                }

                // Agregar nuevamente la tarjeta.
                listaAsignaturas.appendChild(tarjeta);
            }

        } else {

            // Si ya se ocuparon todas las horas, quitar la tarjeta.
            // La referencia se conserva en el Map para poder recuperarla.
            tarjeta.remove();
        }

        // Mostrar información en consola.
        console.log('Horas de asignatura actualizadas:', {
            idAsignatura: idAsignatura,
            horasTotales: horasTotales,
            horasOcupadas: horasOcupadas,
            horasRestantes: horasRestantes
        });
    }


    // =========================================
    // ACTUALIZAR DETALLES DEL FORMULARIO
    // =========================================

    /**
     * Descripción: Genera los campos ocultos con las asignaturas distribuidas en el calendario.
     * Entradas: Obtiene los eventos actuales del calendario.
     * Salidas: Actualiza los campos que se enviarán al controlador.
     */
    function actualizarDetallesHorario() {

        // Obtener el contenedor de los detalles.
        const detallesHorario = document.getElementById(
            'detalles-horario-registro'
        );

        // Obtener el botón para guardar.
        const botonGuardarHorario = document.getElementById(
            'boton-guardar-horario'
        );

        // Evitar errores si no existen los elementos.
        if (!detallesHorario || !botonGuardarHorario) {
            return;
        }

        // Limpiar los campos anteriores.
        detallesHorario.innerHTML = '';

        // Obtener las asignaturas colocadas en el calendario.
        const eventos = calendario.getEvents();

        // Días de la semana.
        const diasSemana = [
            'domingo',
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
            'sabado'
        ];

        // Recorrer las asignaturas del calendario.
        eventos.forEach(function (evento, indice) {

            // Evitar errores si el evento no tiene fechas.
            if (!evento.start || !evento.end) {
                return;
            }

            // Obtener los datos adicionales.
            const datos = evento.extendedProps;

            // Obtener el día de la asignatura.
            const dia = diasSemana[evento.start.getDay()];

            // Obtener la hora inicial.
            const horaInicio = evento.start.toTimeString().substring(0, 5);

            // Obtener la hora final.
            const horaFin = evento.end.toTimeString().substring(0, 5);

            // Datos que se enviarán al controlador.
            const campos = {
                dia: dia,
                hora_inicio: horaInicio,
                hora_fin: horaFin,
                aula: datos.aula,
                id_docente: datos.id_docente,
                id_asignatura: datos.id_asignatura
            };

            // Crear los campos ocultos.
            Object.entries(campos).forEach(function ([nombre, valor]) {

                const input = document.createElement('input');

                input.type = 'hidden';
                input.name = `detalles[${indice}][${nombre}]`;
                input.value = valor ?? '';

                detallesHorario.appendChild(input);
            });
        });

        // Habilitar guardar cuando existan asignaturas.
        botonGuardarHorario.disabled = eventos.length === 0;
    }


    // =========================================
    // DIBUJAR EL CALENDARIO
    // =========================================

    calendario.render();


    // =========================================
    // ARRASTRAR ASIGNATURAS AL CALENDARIO
    // =========================================

    // Permitir arrastrar las tarjetas al calendario.
    if (listaAsignaturas) {

        // Inicializar el arrastre de las tarjetas.
        new Draggable(listaAsignaturas, {

            // Seleccionar las tarjetas que se podrán arrastrar.
            itemSelector: '.tarjeta-asignatura-disponible',

            // Obtener los datos de cada tarjeta.
            eventData: function (elemento) {

                // Obtener la tarjeta que contiene la asignatura.
                const tarjeta = elemento.closest(
                    '.tarjeta-asignatura-disponible'
                );

                // Verificar que exista la tarjeta.
                if (!tarjeta) {
                    return { create: false };
                }

                // Obtener los selectores de docente y espacio.
                const selectorDocente = tarjeta.querySelector(
                    '.selector-docente'
                );

                const selectorEspacio = tarjeta.querySelector(
                    '.selector-espacio'
                );

                // Verificar que existan los selectores.
                if (!selectorDocente || !selectorEspacio) {
                    return { create: false };
                }

                // Obtener los valores seleccionados.
                const idDocente = selectorDocente.value;
                const espacio = selectorEspacio.value;

                // Obtener el nombre de la asignatura.
                const nombreAsignatura = tarjeta.querySelector(
                    '.nombre-asignatura-tarjeta'
                )?.textContent.trim() || '';

                // Obtener las horas disponibles.
                const cantidadHoras = Number(
                    tarjeta.dataset.cantidadHoras
                );

                // Obtener las horas totales originales.
                const horasTotales = Number(
                    tarjeta.dataset.horasTotales
                );


                // =========================================
                // VALIDAR LOS DATOS DE LA TARJETA
                // =========================================

                // Validar la cantidad de horas disponibles.
                if (!cantidadHoras || cantidadHoras <= 0) {

                    mostrarAdvertencia(
                        'La asignatura no tiene una cantidad de horas disponible válida.'
                    );

                    return { create: false };
                }

                // Validar las horas totales.
                if (!horasTotales || horasTotales <= 0) {

                    mostrarAdvertencia(
                        'No se encontró la cantidad total de horas de la asignatura.'
                    );

                    return { create: false };
                }

                // Validar el identificador de la asignatura.
                if (!tarjeta.dataset.idAsignatura) {

                    mostrarAdvertencia(
                        'No se encontró el identificador de la asignatura.'
                    );

                    return { create: false };
                }

                // Validar el nombre de la asignatura.
                if (!nombreAsignatura) {

                    mostrarAdvertencia(
                        'No se encontró el nombre de la asignatura.'
                    );

                    return { create: false };
                }


                // =========================================
                // GUARDAR LA TARJETA ORIGINAL
                // =========================================

                // Conservar la referencia de la tarjeta.
                // No se elimina del Map aunque desaparezca de la lista.
                tarjetasOriginales.set(
                    String(tarjeta.dataset.idAsignatura),
                    tarjeta
                );


                // =========================================
                // DEVOLVER LOS DATOS DEL EVENTO
                // =========================================

                return {

                    // Identificador único para cada bloque.
                    id: `bloque-${++contadorEventos}`,

                    // Nombre que aparecerá en el calendario.
                    title: nombreAsignatura,

                    // Duración inicial según las horas disponibles.
                    duration: {
                        hours: cantidadHoras
                    },

                    // Datos adicionales de la asignatura.
                    extendedProps: {
                        id_asignatura: tarjeta.dataset.idAsignatura,
                        id_docente: idDocente,
                        nombreDocente: selectorDocente.options[
                            selectorDocente.selectedIndex
                        ].textContent,
                        aula: espacio,

                        // Conservar las horas totales originales.
                        totalHoras: horasTotales,

                        // Guardar las horas disponibles al momento del arrastre.
                        cantidadHoras: cantidadHoras
                    }
                };
            }
        });


        // =========================================
        // EVITAR QUE LOS SELECTORES INICIEN EL ARRASTRE
        // =========================================

        // Evitar que los selectores inicien el arrastre.
        listaAsignaturas.addEventListener('mousedown', function (evento) {

            if (
                evento.target.closest('.selector-docente') ||
                evento.target.closest('.selector-espacio')
            ) {
                evento.stopPropagation();
            }

        }, true);


        listaAsignaturas.addEventListener('pointerdown', function (evento) {

            if (
                evento.target.closest('.selector-docente') ||
                evento.target.closest('.selector-espacio')
            ) {
                evento.stopPropagation();
            }

        }, true);


        listaAsignaturas.addEventListener('touchstart', function (evento) {

            if (
                evento.target.closest('.selector-docente') ||
                evento.target.closest('.selector-espacio')
            ) {
                evento.stopPropagation();
            }

        }, true);
    }


    // =========================================
    // MOSTRAR U OCULTAR EL SÁBADO
    // =========================================

    if (botonSabado) {

        botonSabado.addEventListener('click', function () {

            // Cambiar el estado de visibilidad.
            sabadoVisible = !sabadoVisible;

            // Actualizar los días visibles.
            calendario.setOption(
                'hiddenDays',
                sabadoVisible ? [0] : [0, 6]
            );

            // Actualizar el texto del botón.
            botonSabado.innerHTML = sabadoVisible
                ? '<i class="fa-solid fa-minus"></i> Ocultar sábado'
                : '<i class="fa-solid fa-plus"></i> Mostrar sábado';

            // Actualizar el estado accesible del botón.
            botonSabado.setAttribute(
                'aria-expanded',
                sabadoVisible.toString()
            );
        });
    }

});
