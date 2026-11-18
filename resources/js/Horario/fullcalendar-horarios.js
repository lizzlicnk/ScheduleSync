
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

    // Obtener los elementos de la vista.
    const elementoCalendario = document.getElementById('calendario-horarios');
    const botonSabado = document.getElementById('boton-sabado');
    const listaAsignaturas = document.getElementById('lista-asignaturas-disponibles');

    // Evitar errores si esta vista no contiene el calendario.
    if (!elementoCalendario) {
        return;
    }

    // Indica si el sábado está visible.
    let sabadoVisible = false;

    // Contador para generar identificadores únicos.
    let contadorEventos = 0;

    // Guardar las tarjetas originales para poder recuperarlas.
    const tarjetasOriginales = new Map();


    // =========================================
    // CONFIRMAR USO DEL HORARIO EXTENDIDO
    // =========================================

    /**
     * Descripción:
     * Solicita confirmación cuando se utiliza el horario extendido
     * del sábado, comprendido entre las 2:00 y las 4:00 pm.
     *
     * Salidas:
     * Devuelve true si el usuario acepta y false si cancela.
     */
    async function confirmarHorarioSabado() {

        const resultado = await Swal.fire({
            ...configuracionAlertas,

            icon: 'warning',
            iconHtml: '<i class="fa-solid fa-triangle-exclamation"></i>',

            title: 'Horario extendido del sábado',

            text: 'La asignatura ocupará un horario extendido del sábado, de 2:00 a 4:00 pm. ¿Deseas continuar?',

            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',

            reverseButtons: true,
            allowOutsideClick: false,

            customClass: {
                popup: 'alerta-schedulesync',
                confirmButton: 'boton-confirmar-alerta',
                cancelButton: 'boton-cancelar-alerta'
            }
        });

        return resultado.isConfirmed;
    }


    // =========================================
    // FUNCIONES PARA VALIDAR LOS HORARIOS
    // =========================================

    /**
     * Descripción:
     * Comprueba si un evento utiliza el horario extendido
     * del sábado, de las 2:00 a las 4:00 pm.
     *
     * Entradas:
     * Recibe un evento del calendario.
     *
     * Salidas:
     * Devuelve true si el evento coincide con ese horario.
     */
    function tocaHorarioExtendidoSabado(evento) {

        if (!evento.start || !evento.end) {
            return false;
        }

        // Comprobar que el evento corresponda al sábado.
        if (evento.start.getDay() !== 6) {
            return false;
        }

        const inicio =
            evento.start.getHours() * 60 +
            evento.start.getMinutes();

        const fin =
            evento.end.getHours() * 60 +
            evento.end.getMinutes();

        // Comprobar si el bloque coincide con el horario de 2 a 4 pm.
        return fin > 14 * 60 && inicio < 16 * 60;
    }


    /**
     * Descripción:
     * Comprueba que un evento no sobrepase el horario permitido.
     *
     * Entradas:
     * Recibe un evento del calendario.
     *
     * Salidas:
     * Devuelve true si excede el horario permitido.
     */
    function excedeLimiteHorario(evento) {

        if (!evento.start || !evento.end) {
            return true;
        }

        // Evitar que un bloque abarque más de un día.
        if (
            evento.start.toDateString() !==
            evento.end.toDateString()
        ) {
            return true;
        }

        const horaFinal =
            evento.end.getHours() * 60 +
            evento.end.getMinutes();

        // El sábado termina a las 4 pm.
        // De lunes a viernes termina a las 9 pm.
        const limiteHorario = evento.start.getDay() === 6
            ? 16 * 60
            : 21 * 60;

        return horaFinal > limiteHorario;
    }


    /**
     * Descripción:
     * Comprueba que un bloque se encuentre dentro del horario permitido.
     *
     * Entradas:
     * Recibe las fechas de inicio y fin.
     *
     * Salidas:
     * Devuelve true si el bloque respeta los límites.
     */
    function horarioPermitido(inicio, fin) {

        if (!inicio || !fin) {
            return false;
        }

        if (inicio.toDateString() !== fin.toDateString()) {
            return false;
        }

        const horaInicio =
            inicio.getHours() * 60 +
            inicio.getMinutes();

        const horaFin =
            fin.getHours() * 60 +
            fin.getMinutes();

        const esSabado = inicio.getDay() === 6;

        const limiteInicio = 7 * 60;
        const limiteFin = esSabado ? 16 * 60 : 21 * 60;

        return (
            horaInicio >= limiteInicio &&
            horaFin <= limiteFin &&
            horaFin > horaInicio
        );
    }


    // =========================================
    // VALIDAR DISPONIBILIDAD DEL DOCENTE
    // =========================================

    /**
     * Descripción:
     * Comprueba que el docente tenga disponibilidad confirmada
     * durante todo el horario del evento.
     *
     * Entradas:
     * Recibe el evento que se desea validar.
     *
     * Salidas:
     * Devuelve true si el docente está disponible y false si no.
     */
    /**
     * Descripción:
     * Comprueba si el docente tiene algún bloque de indisponibilidad
     * confirmado que coincida con el horario del evento.
     *
     * Entradas:
     * - evento: Evento de FullCalendar que se desea validar.
     *
     * Salidas:
     * - true: No existe un bloque de indisponibilidad coincidente.
     * - false: Existe un bloque coincidente o faltan datos para validar.
     */
    function docenteDisponible(evento) {

        // Obtener los datos adicionales del evento.
        const datos = evento.extendedProps;

        // Comprobar que el evento tenga docente y fechas.
        if (
            !datos ||
            !datos.id_docente ||
            !evento.start ||
            !evento.end
        ) {
            console.error('No se encontraron los datos del docente o del evento.');
            return false;
        }

        // Comprobar que las indisponibilidades se hayan cargado desde Blade.
        if (!Array.isArray(window.disponibilidadesDocentes)) {

            console.error(
                'No se cargaron las indisponibilidades de los docentes.'
            );

            return false;
        }

        // Obtener los bloques de indisponibilidad del periodo seleccionado.
        const indisponibilidades = window.disponibilidadesDocentes;

        // Obtener el día de la semana.
        // 1 = lunes, 2 = martes ... 6 = sábado.
        const dia = evento.start.getDay() === 0
            ? 7
            : evento.start.getDay();

        // Convertir el inicio y fin del evento a minutos.
        const horaInicio =
            evento.start.getHours() * 60 +
            evento.start.getMinutes();

        const horaFin =
            evento.end.getHours() * 60 +
            evento.end.getMinutes();

        // Buscar si existe un bloque de indisponibilidad coincidente.
        const tieneIndisponibilidad = indisponibilidades.some(function (bloque) {

            // Comprobar que pertenezca al mismo docente,
            // al mismo día y que esté confirmado.
            if (
                String(bloque.id_docente) !== String(datos.id_docente) ||
                Number(bloque.dia) !== dia ||
                Number(bloque.estadoConfirmacion) !== 1
            ) {
                return false;
            }

            // Convertir las horas del registro a minutos.
            const inicioBloque = convertirHoraAMinutos(bloque.hora_inicio);
            const finBloque = convertirHoraAMinutos(bloque.hora_fin);

            // Comprobar si el evento se cruza con el bloque no disponible.
            return (
                horaInicio < finBloque &&
                horaFin > inicioBloque
            );
        });

        // Mostrar información para comprobar la validación.
        console.log('Validación de disponibilidad:', {
            idDocente: datos.id_docente,
            dia: dia,
            horaInicio: horaInicio,
            horaFin: horaFin,
            bloquesEncontrados: indisponibilidades.length,
            tieneIndisponibilidad: tieneIndisponibilidad
        });

        // El docente está disponible si no existe ningún cruce.
        return !tieneIndisponibilidad;
    }


    /**
     * Descripción:
     * Convierte una hora en formato HH:mm o HH:mm:ss a minutos.
     *
     * Entradas:
     * - hora: Hora que se desea convertir.
     *
     * Salidas:
     * - Cantidad de minutos transcurridos desde las 00:00.
     */
    function convertirHoraAMinutos(hora) {

        // Separar las horas y los minutos.
        const partes = String(hora).split(':');

        return (
            Number(partes[0]) * 60 +
            Number(partes[1])
        );
    }


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

        // Fecha inicial.
        initialDate: '2026-01-05',

        // La semana comienza el lunes.
        firstDay: 1,

        // Ocultar domingo y sábado inicialmente.
        hiddenDays: [0, 6],

        // Ocultar encabezado predeterminado.
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
        snapDuration: '01:00:00',

        // Mostrar desde las 7 am hasta las 9 pm.
        // La última fila será de 8 a 9 pm.
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',


        // Mostrar el intervalo completo de cada hora.
        // Ejemplo: 07:00 - 08:00.
        // ======================================

        // Una etiqueta por cada hora.
        slotHeaderInterval: '01:00:00',

        // Mostrar el intervalo completo. Ejemplo: 07:00 - 08:00.
        slotHeaderContent: function (info) {

            const horaInicio = info.date.getHours();

            const formatear = function (hora) {
                return String(hora).padStart(2, '0') + ':00';
            };

            const etiqueta = document.createElement('span');
            etiqueta.className = 'etiqueta-hora-horario';
            etiqueta.textContent =
                formatear(horaInicio) + ' - ' + formatear(horaInicio + 1);

            return {
                domNodes: [etiqueta]
            };
        },
        
        // =========================================
        // HORARIOS PERMITIDOS
        // =========================================

        // De lunes a viernes: 7 am a 9 pm.
        // Sábado: 7 am a 4 pm.
        businessHours: [
            {
                daysOfWeek: [1, 2, 3, 4, 5],
                startTime: '07:00',
                endTime: '21:00'
            },
            {
                daysOfWeek: [6],
                startTime: '07:00',
                endTime: '16:00'
            }
        ],

        // Restringir eventos al horario permitido.
        eventConstraint: 'businessHours',

        // Restringir las selecciones al horario permitido.
        selectConstraint: 'businessHours',

        // Permitir seleccionar arrastrando el cursor.
        selectable: true,

        // Mostrar una selección temporal.
        selectMirror: true,

        // Permitir seleccionar varias horas consecutivas.
        selectMinDistance: 0,

        // Permitir recibir tarjetas externas.
        droppable: true,

        // Permitir mover y modificar las asignaturas.
        editable: true,

        // Permitir cambiar el horario de inicio.
        eventStartEditable: true,

        // Permitir modificar la duración.
        eventDurationEditable: true,

        // No permitir cambiar la duración desde el borde superior.
        eventResizableFromStart: false,


        // =========================================
        // MOSTRAR INFORMACIÓN DE LAS TARJETAS
        // =========================================

        eventContent: function (info) {

            const datos = info.event.extendedProps;

            // Crear el contenedor principal.
            const contenedor = document.createElement('div');
            contenedor.className = 'contenido-evento-horario';

            // Nombre de la asignatura.
            const nombre = document.createElement('div');
            nombre.className = 'nombre-evento-asignatura';
            nombre.textContent = info.event.title;

            // Nombre del docente.
            const docente = document.createElement('div');
            docente.className = 'detalle-evento-horario';
            docente.textContent =
                'Docente: ' + (datos.nombreDocente || 'No asignado');

            // Espacio seleccionado.
            const espacio = document.createElement('div');
            espacio.className = 'detalle-evento-horario';
            espacio.textContent =
                'Espacio: ' + (datos.nombreEspacio || 'No asignado');

            // Obtener la hora de inicio.
            const horaInicio = info.event.start
                ? info.event.start.toLocaleTimeString('es-MX', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                })
                : '';

            // Obtener la hora de finalización.
            const horaFin = info.event.end
                ? info.event.end.toLocaleTimeString('es-MX', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                })
                : '';

            // Mostrar el horario.
            const horario = document.createElement('div');
            horario.className = 'horario-evento-horario';
            horario.textContent = horaInicio + ' - ' + horaFin;

            // Agregar los elementos.
            contenedor.appendChild(nombre);
            contenedor.appendChild(docente);
            contenedor.appendChild(espacio);
            contenedor.appendChild(horario);

            return {
                domNodes: [contenedor]
            };
        },


        // =========================================
        // VALIDAR SELECCIONES
        // =========================================

        selectAllow: function (info) {

            const ultimaFecha = new Date(
                info.end.getTime() - 1
            );

            return (
                !info.allDay &&
                info.start.toDateString() ===
                ultimaFecha.toDateString() &&
                horarioPermitido(info.start, info.end)
            );
        },


        // =========================================
        // SELECCIONAR BLOQUES DEL CALENDARIO
        // =========================================

        select: function () {

            // Limpiar la selección visual.
            calendario.unselect();
        },


        // =========================================
        // RECIBIR ASIGNATURAS ARRASTRADAS
        // =========================================

        eventReceive: async function (info) {

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

            // Validar la disponibilidad académica del docente.
            if (!docenteDisponible(info.event)) {

                info.event.remove();

                mostrarAdvertencia(
                    'El docente no está disponible en ese horario. Verifica su disponibilidad académica.',
                    'Docente no disponible'
                );

                return;
            }
            

            // Validar que no se exceda el horario permitido.
            if (excedeLimiteHorario(info.event)) {

                info.event.remove();

                mostrarAdvertencia(
                    'La asignatura debe estar dentro del horario permitido. De lunes a viernes hasta las 9:00 pm y los sábados hasta las 4:00 pm.',
                    'Horario no permitido'
                );

                return;
            }

            // Solicitar confirmación si utiliza el horario extendido del sábado.
            if (tocaHorarioExtendidoSabado(info.event)) {

                const aceptado = await confirmarHorarioSabado();

                if (!aceptado) {

                    // Al eliminar el evento se recuperan las horas.
                    info.event.remove();

                    return;
                }
            }

            // Aplicar los colores de ScheduleSync.
            info.event.setProp('backgroundColor', '#5A3E7D');
            info.event.setProp('borderColor', '#5A3E7D');
            info.event.setProp('textColor', '#FFFFFF');

            // Actualizar las horas disponibles.
            actualizarTarjetaDisponible(
                datos.id_asignatura,
                info.event
            );

            // Actualizar los campos del formulario.
            actualizarDetallesHorario();

            console.log('Asignatura colocada:', info.event);
        },


        // =========================================
        // REGRESAR ASIGNATURAS A DISPONIBLES
        // =========================================

        eventDragStop: function (info) {

            if (!listaAsignaturas || !info.jsEvent) {
                return;
            }

            // Obtener la posición donde terminó el arrastre.
            const elementoDestino = document.elementFromPoint(
                info.jsEvent.clientX,
                info.jsEvent.clientY
            );

            // Comprobar si se soltó dentro de la lista.
            if (
                !elementoDestino ||
                !listaAsignaturas.contains(elementoDestino)
            ) {
                return;
            }

            // Eliminar el evento del calendario.
            info.event.remove();

            console.log(
                'Asignatura regresada a disponibles:',
                info.event.title
            );
        },


        // =========================================
        // DETECTAR CAMBIOS DE HORARIO
        // =========================================

        eventDrop: async function (info) {

            // Validar la disponibilidad académica del docente.
            if (!docenteDisponible(info.event)) {

                info.revert();

                mostrarAdvertencia(
                    'El docente no está disponible en ese horario. Verifica su disponibilidad académica.',
                    'Docente no disponible'
                );

                return;
            }

            // Comprobar los límites del horario.
            if (excedeLimiteHorario(info.event)) {

                info.revert();

                mostrarAdvertencia(
                    'No puedes colocar una asignatura fuera del horario permitido.',
                    'Horario no permitido'
                );

                return;
            }

            // Solicitar confirmación si se mueve al horario extendido.
            if (tocaHorarioExtendidoSabado(info.event)) {

                const aceptado = await confirmarHorarioSabado();

                if (!aceptado) {

                    info.revert();

                    return;
                }
            }

            // Actualizar los campos del formulario.
            actualizarDetallesHorario();

            console.log('Horario actualizado:', {
                asignatura: info.event.title,
                inicio: info.event.start,
                fin: info.event.end
            });
        },


        // =========================================
        // DETECTAR CAMBIOS EN LA DURACIÓN
        // =========================================

        eventResize: async function (info) {

            const datos = info.event.extendedProps;

            // Obtener las horas totales originales.
            const horasTotales = Number(datos.totalHoras);

            // Validar que existan fechas.
            if (!info.event.start || !info.event.end) {

                info.revert();

                return;
            }

            // Validar que exista una cantidad total de horas.
            if (!horasTotales || horasTotales <= 0) {

                info.revert();

                mostrarAdvertencia(
                    'No se encontró una cantidad máxima de horas válida.'
                );

                return;
            }

            // Validar la disponibilidad académica del docente.
            if (!docenteDisponible(info.event)) {

                info.revert();

                mostrarAdvertencia(
                    'El docente no está disponible durante todo ese horario. Verifica su disponibilidad académica.',
                    'Docente no disponible'
                );

                return;
            }

            // Calcular la duración del bloque.
            const duracionHoras = (
                info.event.end.getTime() -
                info.event.start.getTime()
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
                    evento.end.getTime() -
                    evento.start.getTime()
                ) / (1000 * 60 * 60);

                return total + horas;

            }, 0);

            // Calcular las horas máximas que puede utilizar este bloque.
            const horasMaximas = horasTotales - horasOcupadas;

            // Validar que no se excedan las horas totales.
            if (duracionHoras > horasMaximas) {

                info.revert();

                if (horasMaximas <= 0) {

                    mostrarAdvertencia(
                        'Esta asignatura ya no tiene más horas por asignar.',
                        'Horas completadas'
                    );

                } else {

                    mostrarAdvertencia(
                        `No puedes exceder el límite de horas de esta asignatura. Solo puedes asignar ${horasMaximas} hora(s) más.`,
                        'Límite de horas alcanzado'
                    );
                }

                return;
            }

            // Solicitar confirmación si utiliza el horario extendido del sábado.
            if (tocaHorarioExtendidoSabado(info.event)) {

                const aceptado = await confirmarHorarioSabado();

                if (!aceptado) {

                    info.revert();

                    return;
                }
            }

            // Actualizar las horas disponibles.
            actualizarTarjetaDisponible(datos.id_asignatura);

            // Actualizar los campos del formulario.
            actualizarDetallesHorario();

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

            const idAsignatura = info.event.extendedProps.id_asignatura;

            // Actualizar las horas excluyendo el evento eliminado.
            actualizarTarjetaDisponible(
                idAsignatura,
                null,
                info.event
            );

            // Actualizar el formulario excluyendo el evento eliminado.
            actualizarDetallesHorario(info.event);
        },


        // =========================================
        // CONFIGURACIÓN VISUAL
        // =========================================
        // Aumentar la altura del calendario.
        height: 1000,

        // Establecer la altura del contenido.
        contentHeight: 800,

        expandRows: true,
        nowIndicator: false,
        allDayMaintainDuration: true,
    });


    // =========================================
    // CALCULAR HORAS OCUPADAS DE UNA ASIGNATURA
    // =========================================

    /**
     * Descripción:
     * Calcula las horas utilizadas por una asignatura.
     *
     * Entradas:
     * Recibe el identificador de la asignatura,
     * un evento que se desea incluir y uno que se desea excluir.
     *
     * Salidas:
     * Devuelve la cantidad de horas ocupadas.
     */
    function obtenerHorasOcupadas(
        idAsignatura,
        eventoIncluir = null,
        eventoExcluir = null
    ) {

        // Obtener los eventos correspondientes a la asignatura.
        const eventosAsignatura = calendario.getEvents().filter(function (evento) {

            // Excluir el evento que se está eliminando.
            if (
                eventoExcluir &&
                evento.id === eventoExcluir.id
            ) {
                return false;
            }

            return (
                String(evento.extendedProps.id_asignatura) ===
                String(idAsignatura)
            );
        });

        // Incluir el evento recibido si todavía no aparece en la lista.
        if (
            eventoIncluir &&
            !eventosAsignatura.some(function (evento) {
                return evento.id === eventoIncluir.id;
            })
        ) {
            eventosAsignatura.push(eventoIncluir);
        }

        // Sumar la duración de todos los bloques.
        return eventosAsignatura.reduce(function (total, evento) {

            if (!evento.start || !evento.end) {
                return total;
            }

            const horas = (
                evento.end.getTime() -
                evento.start.getTime()
            ) / (1000 * 60 * 60);

            return total + horas;

        }, 0);
    }


    // =========================================
    // VALIDAR SI UNA ASIGNATURA TIENE BLOQUES
    // =========================================

    /**
     * Descripción:
     * Comprueba si una asignatura tiene bloques en el calendario.
     *
     * Entradas:
     * Recibe el identificador de la asignatura.
     *
     * Salidas:
     * Devuelve true si tiene bloques y false si no tiene.
     */
    function asignaturaTieneBloques(idAsignatura) {

        return calendario.getEvents().some(function (evento) {

            return (
                String(evento.extendedProps.id_asignatura) ===
                String(idAsignatura)
            );
        });
    }


    // =========================================
    // OBTENER DOCENTE ASIGNADO
    // =========================================

    /**
     * Descripción:
     * Obtiene el docente asignado a los bloques de una asignatura.
     *
     * Entradas:
     * Recibe el identificador de la asignatura.
     *
     * Salidas:
     * Devuelve el identificador del docente o null.
     */
    function obtenerDocenteAsignado(idAsignatura) {

        const evento = calendario.getEvents().find(function (evento) {

            return (
                String(evento.extendedProps.id_asignatura) ===
                String(idAsignatura)
            );
        });

        return evento
            ? String(evento.extendedProps.id_docente)
            : null;
    }


    // =========================================
    // ACTUALIZAR TARJETA DE ASIGNATURA
    // =========================================

    /**
     * Descripción:
     * Actualiza las horas disponibles de una asignatura.
     *
     * Entradas:
     * Recibe el identificador de la asignatura,
     * un evento que se desea incluir y uno que se desea excluir.
     *
     * Salidas:
     * Actualiza la tarjeta o la elimina cuando ya no quedan horas.
     */
    function actualizarTarjetaDisponible(
        idAsignatura,
        eventoIncluir = null,
        eventoExcluir = null
    ) {

        // Obtener la tarjeta original.
        const tarjeta = tarjetasOriginales.get(String(idAsignatura));

        if (!tarjeta) {
            return;
        }

        // Obtener las horas totales originales.
        const horasTotales = Number(tarjeta.dataset.horasTotales);

        // Obtener las horas ocupadas.
        const horasOcupadas = obtenerHorasOcupadas(
            idAsignatura,
            eventoIncluir,
            eventoExcluir
        );

        // Calcular las horas restantes.
        const horasRestantes = Math.max(
            0,
            horasTotales - horasOcupadas
        );

        // Actualizar el valor de horas disponibles.
        tarjeta.dataset.cantidadHoras = horasRestantes;

        // Obtener la etiqueta de horas.
        const etiquetaHoras = tarjeta.querySelector(
            '.informacion-asignatura span'
        );

        // Actualizar el texto.
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

                // Quitar mensajes anteriores.
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

            // Retirar la tarjeta de la lista.
            // La referencia se conserva para poder recuperarla.
            tarjeta.remove();
        }

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
     * Descripción:
     * Genera los campos ocultos con las asignaturas distribuidas
     * en el calendario.
     *
     * Entradas:
     * Recibe opcionalmente el evento que debe excluirse.
     *
     * Salidas:
     * Actualiza los campos que se enviarán al controlador.
     */
    function actualizarDetallesHorario(eventoExcluir = null) {

        // Obtener el contenedor de los detalles.
        const detallesHorario = document.getElementById(
            'detalles-horario-registro'
        );

        // Obtener el botón para guardar.
        const botonGuardarHorario = document.getElementById(
            'boton-guardar-horario'
        );

        if (!detallesHorario || !botonGuardarHorario) {
            return;
        }

        // Limpiar los campos anteriores.
        detallesHorario.innerHTML = '';

        // Obtener los eventos actuales.
        const eventos = calendario.getEvents().filter(function (evento) {

            return !(
                eventoExcluir &&
                evento.id === eventoExcluir.id
            );
        });

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

        // Recorrer los eventos del calendario.
        eventos.forEach(function (evento, indice) {

            if (!evento.start || !evento.end) {
                return;
            }

            const datos = evento.extendedProps;

            // Obtener el día.
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

    if (listaAsignaturas) {

        new Draggable(listaAsignaturas, {

            // Seleccionar las tarjetas que se podrán arrastrar.
            itemSelector: '.tarjeta-asignatura-disponible',

            // Obtener los datos de cada tarjeta.
            eventData: function (elemento) {

                // Obtener la tarjeta.
                const tarjeta = elemento.closest(
                    '.tarjeta-asignatura-disponible'
                );

                if (!tarjeta) {
                    return { create: false };
                }

                // Obtener los selectores.
                const selectorDocente = tarjeta.querySelector(
                    '.selector-docente'
                );

                const selectorEspacio = tarjeta.querySelector(
                    '.selector-espacio'
                );

                if (!selectorDocente || !selectorEspacio) {
                    return { create: false };
                }

                // Obtener los valores seleccionados.
                const idDocente = selectorDocente.value;
                const espacio = selectorEspacio.value;

                // Obtener el nombre del docente.
                const nombreDocente = selectorDocente.options[
                    selectorDocente.selectedIndex
                ]?.textContent.trim() || '';

                // Obtener el nombre del espacio.
                const nombreEspacio = selectorEspacio.options[
                    selectorEspacio.selectedIndex
                ]?.textContent.trim() || '';

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

                if (!cantidadHoras || cantidadHoras <= 0) {

                    mostrarAdvertencia(
                        'Esta asignatura ya no tiene más horas por asignar.',
                        'Horas completadas'
                    );

                    return { create: false };
                }

                if (!horasTotales || horasTotales <= 0) {

                    mostrarAdvertencia(
                        'No se encontró la cantidad total de horas de la asignatura.'
                    );

                    return { create: false };
                }

                if (!tarjeta.dataset.idAsignatura) {

                    mostrarAdvertencia(
                        'No se encontró el identificador de la asignatura.'
                    );

                    return { create: false };
                }

                if (!nombreAsignatura) {

                    mostrarAdvertencia(
                        'No se encontró el nombre de la asignatura.'
                    );

                    return { create: false };
                }


                // =========================================
                // GUARDAR LA TARJETA ORIGINAL
                // =========================================

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
                        nombreDocente: nombreDocente,
                        aula: espacio,
                        nombreEspacio: nombreEspacio,

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

        ['mousedown', 'pointerdown', 'touchstart'].forEach(function (tipoEvento) {

            listaAsignaturas.addEventListener(tipoEvento, function (evento) {

                if (
                    evento.target.closest('.selector-docente') ||
                    evento.target.closest('.selector-espacio')
                ) {
                    evento.stopPropagation();
                }

            }, true);
        });


        // =========================================
        // CONTROLAR CAMBIOS EN EL SELECTOR DE DOCENTE
        // =========================================

        listaAsignaturas.addEventListener('change', function (evento) {

            // Comprobar que el cambio provenga del selector de docente.
            if (!evento.target.matches('.selector-docente')) {
                return;
            }

            // Obtener la tarjeta de la asignatura.
            const tarjeta = evento.target.closest(
                '.tarjeta-asignatura-disponible'
            );

            if (!tarjeta) {
                return;
            }

            // Obtener el identificador de la asignatura.
            const idAsignatura = tarjeta.dataset.idAsignatura;

            // Comprobar si ya tiene bloques en el calendario.
            if (asignaturaTieneBloques(idAsignatura)) {

                // Obtener el docente que ya está asignado.
                const idDocenteAsignado = obtenerDocenteAsignado(
                    idAsignatura
                );

                // Restaurar el docente anterior.
                evento.target.value = idDocenteAsignado;

                // Mostrar la alerta de ScheduleSync.
                mostrarAdvertencia(
                    'No se puede cambiar al docente porque ya está colocado en el horario. Elimina todos los bloques de esta materia del calendario para poder cambiar al docente.',
                    'No puedes cambiar al docente'
                );

                return;
            }
        });
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
