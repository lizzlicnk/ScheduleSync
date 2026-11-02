document.addEventListener('DOMContentLoaded', function () {

    // Obtiene el contenedor donde se mostrará FullCalendar.
    const contenedorCalendario = document.getElementById(
        'calendario-disponibilidad'
    );

    // Verifica que exista el contenedor y que FullCalendar esté disponible.
    if (!contenedorCalendario || typeof FullCalendar === 'undefined') {
        return;
    }

    // Define los días permitidos para registrar disponibilidad.
    const diasPermitidos = [1, 2, 3, 4, 5, 6];

    // Define la hora inicial y final del calendario.
    const horaInicioCalendario = '07:00:00';
    const horaFinCalendario = '21:00:00';

    // Define los nombres de los días de la semana.
    const nombresDias = {
        1: 'Lunes',
        2: 'Martes',
        3: 'Miércoles',
        4: 'Jueves',
        5: 'Viernes',
        6: 'Sábado'
    };

    // Configura las opciones principales de FullCalendar.
    const configuracionCalendario = {

        // Establece el idioma del calendario.
        locale: 'es',

        // Muestra la semana como vista inicial.
        initialView: 'timeGridWeek',

        // Comienza la semana en lunes.
        firstDay: 1,

        // Oculta los domingos.
        hiddenDays: [0],

        // Oculta la sección de eventos de todo el día.
        allDaySlot: false,

        // Establece el rango de horas que se mostrará.
        slotMinTime: horaInicioCalendario,
        slotMaxTime: horaFinCalendario,

        // Muestra una fila por cada hora.
        slotDuration: '01:00:00',

        // Permite seleccionar intervalos de media hora.
        snapDuration: '00:30:00',

        // Permite seleccionar horarios arrastrando el cursor.
        selectable: true,

        // Permite seleccionar varios horarios en un mismo día.
        selectMirror: true,

        // Evita que dos horarios seleccionados se encimen.
        selectOverlap: false,

        // Oculta la hora actual.
        nowIndicator: false,

        // Configura el formato de las horas.
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },

        // Configura el formato de los encabezados de los días.
        dayHeaderFormat: {
            weekday: 'long'
        },

        // Configura los botones del encabezado.
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },

        // Define los textos de los botones.
        buttonText: {
            today: 'Hoy'
        },

        // Configura la selección de los horarios.
        selectAllow: function (informacionSeleccion) {

            // Obtiene el día de la semana seleccionado.
            const diaSeleccionado = informacionSeleccion.start.getDay();

            // Obtiene la hora inicial y final de la selección.
            const horaInicioSeleccion = informacionSeleccion.start;
            const horaFinSeleccion = informacionSeleccion.end;

            // Convierte las horas permitidas a minutos.
            const minutosInicioPermitido = 7 * 60;
            const minutosFinPermitido = 21 * 60;

            // Convierte la hora inicial seleccionada a minutos.
            const minutosHoraInicio =
                horaInicioSeleccion.getHours() * 60 +
                horaInicioSeleccion.getMinutes();

            // Convierte la hora final seleccionada a minutos.
            const minutosHoraFin =
                horaFinSeleccion.getHours() * 60 +
                horaFinSeleccion.getMinutes();

            // Comprueba que el inicio y el fin correspondan al mismo día.
            const mismoDia =
                horaInicioSeleccion.toDateString() ===
                horaFinSeleccion.toDateString();

            // Comprueba que el día esté permitido.
            const diaPermitido = diasPermitidos.includes(diaSeleccionado);

            // Comprueba que el horario esté dentro del rango establecido.
            const horarioPermitido =
                minutosHoraInicio >= minutosInicioPermitido &&
                minutosHoraFin <= minutosFinPermitido;

            // Permite la selección únicamente si cumple las condiciones.
            return mismoDia && diaPermitido && horarioPermitido;
        },

        // Registra un horario cuando el usuario termina de seleccionarlo.
        select: function (informacionSeleccion) {

            // Obtiene el día de la semana seleccionado.
            const diaSeleccionado = informacionSeleccion.start.getDay();

            // Verifica que el día seleccionado esté permitido.
            if (!diasPermitidos.includes(diaSeleccionado)) {
                return;
            }

            // Agrega el horario seleccionado al calendario.
            calendarioDisponibilidad.addEvent({
                id: String(Date.now()) + Math.random().toString(16).slice(2),
                title: 'Disponible',
                start: informacionSeleccion.start,
                end: informacionSeleccion.end,
                backgroundColor: '#FDE68A',
                borderColor: '#EAB308',
                textColor: '#654B00',
                classNames: ['evento-disponibilidad']
            });

            // Limpia la selección temporal del calendario.
            calendarioDisponibilidad.unselect();

            // Notifica al archivo de gestión que cambiaron los horarios.
            actualizarHorariosSeleccionados();
        },

        // Permite eliminar un horario al hacer clic sobre él.
        eventClick: function (informacionEvento) {

            // Evita que el evento realice otra acción.
            informacionEvento.jsEvent.preventDefault();

            // Elimina el horario seleccionado.
            informacionEvento.event.remove();

            // Notifica al archivo de gestión que cambiaron los horarios.
            actualizarHorariosSeleccionados();
        }
    };

    // Crea la instancia de FullCalendar.
    const calendarioDisponibilidad = new FullCalendar.Calendar(
        contenedorCalendario,
        configuracionCalendario
    );

    // Muestra el calendario en la página.
    calendarioDisponibilidad.render();

    // Comparte la instancia para que otros archivos puedan consultar sus horarios.
    window.calendarioDisponibilidad = calendarioDisponibilidad;

    /**
     * Descripción: Obtiene los horarios seleccionados y notifica los cambios.
     * Entradas: No recibe parámetros.
     * Salidas: Envía los horarios seleccionados al archivo de gestión.
     */
    function actualizarHorariosSeleccionados() {

        // Obtiene los eventos registrados en el calendario.
        const horariosSeleccionados = calendarioDisponibilidad
            .getEvents()
            .map(function (evento) {

                // Obtiene el día de la semana del horario.
                const diaSeleccionado = evento.start.getDay();

                // Obtiene la hora inicial del horario.
                const horaInicio = obtenerHoraFormato(evento.start);

                // Obtiene la hora final del horario.
                const horaFin = obtenerHoraFormato(evento.end);

                // Devuelve los datos necesarios para registrar la disponibilidad.
                return {
                    id: evento.id,
                    dia: diaSeleccionado,
                    nombreDia: nombresDias[diaSeleccionado],
                    hora_inicio: horaInicio,
                    hora_fin: horaFin
                };
            });

        // Envía los horarios al archivo encargado de la gestión.
        document.dispatchEvent(
            new CustomEvent('disponibilidad:actualizada', {
                detail: horariosSeleccionados
            })
        );
    }

    /**
     * Descripción: Convierte una fecha a formato de hora HH:mm.
     * Entradas:
     * fecha - Fecha que contiene la hora seleccionada.
     * Salidas: Hora en formato de 24 horas.
     */
    function obtenerHoraFormato(fecha) {

        // Obtiene la hora y los minutos.
        const hora = String(fecha.getHours()).padStart(2, '0');
        const minutos = String(fecha.getMinutes()).padStart(2, '0');

        // Devuelve la hora en formato HH:mm.
        return `${hora}:${minutos}`;
    }

    // Escucha las solicitudes para eliminar horarios desde el resumen.
    document.addEventListener('disponibilidad:eliminar', function (evento) {

        // Obtiene el identificador del horario que se desea eliminar.
        const idHorario = evento.detail;

        // Busca el horario correspondiente en el calendario.
        const horarioSeleccionado = calendarioDisponibilidad.getEventById(
            idHorario
        );

        // Elimina el horario si existe.
        if (horarioSeleccionado) {
            horarioSeleccionado.remove();

            // Actualiza la información del archivo de gestión.
            actualizarHorariosSeleccionados();
        }
    });

    // Notifica al archivo de gestión que el calendario inició vacío.
    actualizarHorariosSeleccionados();

});