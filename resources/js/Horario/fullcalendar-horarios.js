/*
// =========================================
// IMPORTACIONES DE FULLCALENDAR
// =========================================

// Importar FullCalendar.
import { Calendar } from 'fullcalendar';

// Importar el tema Monarch.
import themePlugin from 'fullcalendar/themes/monarch';

// Importar las vistas del calendario.
import dayGridPlugin from 'fullcalendar/daygrid';
import timeGridPlugin from 'fullcalendar/timegrid';
import listPlugin from 'fullcalendar/list';

// Importar la funcionalidad de interacción.
import interactionPlugin, { Draggable } from 'fullcalendar/interaction';

// Importar los estilos de FullCalendar.
import 'fullcalendar/skeleton.css';
import 'fullcalendar/themes/monarch/theme.css';
import 'fullcalendar/themes/monarch/palettes/purple.css';

*/

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
import interactionPlugin from 'fullcalendar/interaction';

// Importar los estilos de FullCalendar.
import 'fullcalendar/skeleton.css';
import 'fullcalendar/themes/monarch/theme.css';
import 'fullcalendar/themes/monarch/palette.css';


// =========================================
// INICIALIZACIÓN DEL CALENDARIO
// =========================================

document.addEventListener('DOMContentLoaded', function () {

    // Obtener el contenedor del calendario.
    const elementoCalendario = document.getElementById('calendario-horarios');

    // Obtener el botón para mostrar u ocultar el sábado.
    const botonSabado = document.getElementById('boton-sabado');

    // Evitar errores si esta vista no contiene el calendario.
    if (!elementoCalendario) {
        return;
    }

    // Indica si el sábado está visible.
    let sabadoVisible = false;

    // Crear el calendario.
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

        // No permitir selecciones que abarquen varios días.
        selectAllow: function (info) {

            // La hora final es exclusiva.
            const ultimaFecha = new Date(info.end.getTime() - 1);

            // Comprobar que el inicio y el final pertenecen al mismo día.
            return (
                !info.allDay &&
                info.start.toDateString() === ultimaFecha.toDateString()
            );
        },

        // Ejecutar cuando el usuario selecciona un bloque.
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

        // Configuración visual.
        height: 'auto',
        expandRows: true,
        nowIndicator: false,
        allDayMaintainDuration: true
    });

    // Dibujar el calendario.
    calendario.render();


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
