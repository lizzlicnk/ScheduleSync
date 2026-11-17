
// =========================================
// IMPORTACIONES DE FULLCALENDAR
// =========================================

// Importar FullCalendar.
import { Calendar } from 'fullcalendar';

// Importar el tema Monarch.
import themePlugin from 'fullcalendar/themes/monarch';

// Importar la vista semanal.
import timeGridPlugin from 'fullcalendar/timegrid';

// Importar la funcionalidad de selección y arrastre.
import interactionPlugin, { Draggable } from 'fullcalendar/interaction';

// Importar los estilos de FullCalendar.
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

        // La selección se ajusta a intervalos de una hora.
        snapDuration: '01:00:00',

        // Horario visible provisional.
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

        // Permitir mover las asignaturas dentro del calendario.
        editable: true,

        // Permitir cambiar el horario de inicio.
        eventStartEditable: true,

        // Evitar modificar la duración de las asignaturas.
        eventDurationEditable: false,


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

                // Eliminar el evento del calendario.
                info.event.remove();

                mostrarAdvertencia(
                    'Selecciona un docente antes de agregar la asignatura.',
                    '¡Selecciona un docente!'
                );

                return;
            }

            // Validar que se haya seleccionado un espacio.
            if (!datos.aula) {

                // Eliminar el evento del calendario.
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

            // Actualizar los campos del formulario.
            actualizarDetallesHorario();

            // Mostrar los datos recibidos.
            console.log('Asignatura colocada:', info.event);
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
        // DETECTAR ELIMINACIÓN DE ASIGNATURAS
        // =========================================

        eventRemove: function () {

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


    // Dibujar el calendario.
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

                // Obtener la cantidad total de horas.
                const cantidadHoras = Number(
                    tarjeta.dataset.cantidadHoras
                );

                // =========================================
                // VALIDAR LOS DATOS DE LA TARJETA
                // =========================================

                // Validar la cantidad de horas.
                if (!cantidadHoras || cantidadHoras <= 0) {
                    mostrarAdvertencia(
                        'La asignatura no tiene una cantidad de horas válida.'
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
                // DEVOLVER LOS DATOS DEL EVENTO
                // =========================================

                return {

                    // Nombre que aparecerá en el calendario.
                    title: nombreAsignatura,

                    // Duración total de la asignatura.
                    duration: {
                        hours: cantidadHoras
                    },

                    // Datos adicionales de la asignatura.
                    extendedProps: {
                        id_asignatura: tarjeta.dataset.idAsignatura,
                        id_docente: idDocente,
                        aula: espacio,
                        cantidadHoras: cantidadHoras
                    }
                };
            }
        });

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
