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

        if (evento.start.getDay() !== 6) {
            return false;
        }

        const inicio =
            evento.start.getHours() * 60 +
            evento.start.getMinutes();

        const fin =
            evento.end.getHours() * 60 +
            evento.end.getMinutes();

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
        if (evento.start.toDateString() !== evento.end.toDateString()) {
            return true;
        }

        const horaFinal =
            evento.end.getHours() * 60 +
            evento.end.getMinutes();

        const limiteHorario =
            evento.start.getDay() === 6
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

        const datos = evento.extendedProps;

        if (!datos || !datos.id_docente || !evento.start || !evento.end) {
            console.error('No se encontraron los datos del docente o del evento.');
            return false;
        }

        if (!Array.isArray(window.disponibilidadesDocentes)) {

            console.error(
                'No se cargaron las indisponibilidades de los docentes.'
            );

            return false;
        }

        const indisponibilidades = window.disponibilidadesDocentes;

        // 1 = lunes, 2 = martes ... 6 = sábado.
        const dia =
            evento.start.getDay() === 0
                ? 7
                : evento.start.getDay();

        const horaInicio =
            evento.start.getHours() * 60 +
            evento.start.getMinutes();

        const horaFin =
            evento.end.getHours() * 60 +
            evento.end.getMinutes();

        const tieneIndisponibilidad = indisponibilidades.some(function (bloque) {

            if (
                String(bloque.id_docente) !== String(datos.id_docente) ||
                Number(bloque.dia) !== dia ||
                Number(bloque.estadoConfirmacion) !== 1
            ) {
                return false;
            }

            const inicioBloque = convertirHoraAMinutos(bloque.hora_inicio);
            const finBloque = convertirHoraAMinutos(bloque.hora_fin);

            return (
                horaInicio < finBloque &&
                horaFin > inicioBloque
            );
        });

        console.log('Validación de disponibilidad:', {
            idDocente: datos.id_docente,
            dia: dia,
            horaInicio: horaInicio,
            horaFin: horaFin,
            bloquesEncontrados: indisponibilidades.length,
            tieneIndisponibilidad: tieneIndisponibilidad
        });

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

        const partes = String(hora).split(':');

        return (
            Number(partes[0]) * 60 +
            Number(partes[1])
        );
    }


    // =========================================
    // VALIDAR EMPALMES ENTRE ASIGNATURAS
    // =========================================

    /**
     * Descripción:
     * Comprueba si un evento se empalma con otra asignatura
     * que ya está colocada en el calendario.
     *
     * Entradas:
     * - evento: Evento que se está moviendo o modificando.
     *
     * Salidas:
     * - Devuelve el evento con el que existe un empalme.
     * - Devuelve null cuando no existe ningún empalme.
     */
    function obtenerEventoEmpalmado(evento) {

        if (!evento.start || !evento.end) {
            return null;
        }

        const otrosEventos = calendario.getEvents();

        return otrosEventos.find(function (otroEvento) {

            if (otroEvento.id === evento.id) {
                return false;
            }

            if (!otroEvento.start || !otroEvento.end) {
                return false;
            }

            if (
                evento.start.toDateString() !==
                otroEvento.start.toDateString()
            ) {
                return false;
            }

            return (
                evento.start < otroEvento.end &&
                evento.end > otroEvento.start
            );

        }) || null;
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
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',

        // Mostrar el intervalo completo de cada hora.
        slotHeaderInterval: '01:00:00',

        slotHeaderContent: function (info) {

            const horaInicio = info.date.getHours();

            const formatear = function (hora) {
                return String(hora).padStart(2, '0') + ':00';
            };

            const etiqueta = document.createElement('span');

            etiqueta.className = 'etiqueta-hora-horario';

            etiqueta.textContent =
                formatear(horaInicio) +
                ' - ' +
                formatear(horaInicio + 1);

            return {
                domNodes: [etiqueta]
            };
        },


        // =========================================
        // HORARIOS PERMITIDOS
        // =========================================

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

        eventConstraint: 'businessHours',
        selectConstraint: 'businessHours',

        selectable: true,
        selectMirror: true,
        selectMinDistance: 0,

        droppable: true,
        editable: true,
        eventStartEditable: true,
        eventDurationEditable: true,
        eventResizableFromStart: false,


        // =========================================
        // MOSTRAR INFORMACIÓN DE LAS TARJETAS
        // =========================================

        eventContent: function (info) {

            const datos = info.event.extendedProps;

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
                info.start.toDateString() === ultimaFecha.toDateString() &&
                horarioPermitido(info.start, info.end)
            );
        },


        // =========================================
        // SELECCIONAR BLOQUES DEL CALENDARIO
        // =========================================

        select: function () {
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
                    'Debes seleccionar un docente antes de colocar la asignatura.',
                    'Docente no seleccionado'
                );

                return;
            }

            // Validar que se haya seleccionado un espacio.
            if (!datos.aula) {

                info.event.remove();

                mostrarAdvertencia(
                    'Debes seleccionar un aula o laboratorio antes de colocar la asignatura.',
                    'Espacio no seleccionado'
                );

                return;
            }


            // =====================================
            // VALIDAR EMPALMES EN OTROS HORARIOS
            // =====================================

            const conflictoDocente = obtenerConflictoDocenteOtroHorario(
                info.event
            );

            if (conflictoDocente) {

                // Retirar la asignatura del calendario.
                info.event.remove();

                // Mostrar la advertencia correspondiente.
                mostrarAdvertencia(
                    'El docente está dando clases en otro horario durante el horario seleccionado.',
                    'Conflicto de horario'
                );

                return;
            }


            // Obtener la duración original del evento.
            const horasSolicitadas = (
                info.event.end.getTime() -
                info.event.start.getTime()
            ) / (1000 * 60 * 60);


            // =====================================
            // VALIDAR LAS HORAS DE CARGA ACADÉMICA
            // =====================================

            const cargaAcademica = Number(datos.cargaAcademica);

            const horasOtrosHorarios = Number(
                datos.horasAsignadasOtrosHorarios || 0
            );

            const horasCalendario = obtenerHorasAsignadasCalendario(
                datos.id_docente,
                info.event
            );

            const horasRestantesCarga =
                cargaAcademica -
                horasOtrosHorarios -
                horasCalendario;

            if (
                Number.isFinite(cargaAcademica) &&
                horasRestantesCarga < horasSolicitadas
            ) {

                info.event.remove();

                mostrarAdvertencia(
                    `Al docente solamente le quedan ${Math.max(0, horasRestantesCarga).toFixed(2)} horas de carga académica disponibles, pero la asignatura requiere ${horasSolicitadas} horas. No es posible colocarla en el horario.`,
                    'Carga académica insuficiente'
                );

                return;
            }


            // =====================================
            // CALCULAR EL HORARIO DISPONIBLE
            // =====================================

            const nuevoFin = obtenerFinDisponible(info.event);

            if (!nuevoFin) {

                info.event.remove();

                mostrarAdvertencia(
                    'No fue posible comprobar la disponibilidad del horario. Verifica que la información de disponibilidad del docente se haya cargado correctamente.',
                    'No se pudo comprobar el horario'
                );

                return;
            }


            // =====================================
            // CALCULAR LAS HORAS QUE PUEDEN COLOCARSE
            // =====================================

            const horasDisponibles = (
                nuevoFin.getTime() -
                info.event.start.getTime()
            ) / (1000 * 60 * 60);

            if (horasDisponibles < 1) {

                const horasRestantesCarga =
                    Number(datos.cargaAcademica) -
                    Number(datos.horasAsignadasOtrosHorarios || 0) -
                    obtenerHorasAsignadasCalendario(
                        datos.id_docente,
                        info.event
                    );

                info.event.remove();

                if (
                    Number.isFinite(cargaAcademica) &&
                    horasRestantesCarga <= 0
                ) {

                    mostrarAdvertencia(
                        `El docente ya completó su carga académica de ${cargaAcademica} horas. No es posible asignarle más horas en este periodo. Las horas de la asignatura permanecen disponibles.`,
                        'Carga académica completada'
                    );

                    return;
                }

                if (
                    Number.isFinite(cargaAcademica) &&
                    horasRestantesCarga > 0 &&
                    horasRestantesCarga < 1
                ) {

                    mostrarAdvertencia(
                        `Al docente solamente le quedan ${horasRestantesCarga.toFixed(2)} horas de carga académica disponibles. Se necesita al menos una hora completa para colocar la asignatura. Las horas de la asignatura permanecen disponibles.`,
                        'Carga académica insuficiente'
                    );

                    return;
                }

                mostrarAdvertencia(
                    'No fue posible colocar la asignatura porque no existe al menos una hora consecutiva disponible. Puede haber otra asignatura ocupando ese horario o una indisponibilidad registrada del docente. Las horas de la asignatura permanecen disponibles.',
                    'Horario no disponible'
                );

                return;
            }


            // =====================================
            // AJUSTAR LA DURACIÓN DE LA ASIGNATURA
            // =====================================

            if (horasDisponibles < horasSolicitadas) {

                info.event.setEnd(nuevoFin);

                mostrarAdvertencia(
                    `La asignatura se colocó durante ${horasDisponibles} hora(s), porque después existe un horario ocupado o una indisponibilidad del docente. Las horas restantes continúan disponibles.`,
                    'Horario ajustado'
                );
            }


            // =====================================
            // VALIDAR LA DISPONIBILIDAD DEL DOCENTE
            // =====================================

            if (!docenteDisponible(info.event)) {

                info.event.remove();

                mostrarAdvertencia(
                    'El docente tiene una indisponibilidad registrada durante el horario seleccionado. Las horas de la asignatura permanecen disponibles.',
                    'Docente no disponible'
                );

                return;
            }


            // =====================================
            // VALIDAR LOS LÍMITES DEL CALENDARIO
            // =====================================

            if (excedeLimiteHorario(info.event)) {

                info.event.remove();

                mostrarAdvertencia(
                    'La asignatura excede el horario permitido para el día seleccionado. Las horas permanecen disponibles.',
                    'Horario fuera de los límites'
                );

                return;
            }


            // =====================================
            // CONFIRMAR HORARIO EXTENDIDO DEL SÁBADO
            // =====================================

            if (tocaHorarioExtendidoSabado(info.event)) {

                const confirmado = await confirmarHorarioSabado();

                if (!confirmado) {
                    info.event.remove();
                    return;
                }
            }


            // =====================================
            // CONFIGURAR LA APARIENCIA DEL EVENTO
            // =====================================

            info.event.setProp('backgroundColor', '#5A3E7D');
            info.event.setProp('borderColor', '#5A3E7D');
            info.event.setProp('textColor', '#FFFFFF');


            // =====================================
            // ACTUALIZAR LAS HORAS DE LA ASIGNATURA
            // =====================================

            actualizarTarjetaDisponible(
                datos.id_asignatura,
                info.event
            );


            // =====================================
            // ACTUALIZAR LOS DETALLES DEL HORARIO
            // =====================================

            actualizarDetallesHorario();

            console.log('Asignatura agregada al calendario:', {
                idAsignatura: datos.id_asignatura,
                idDocente: datos.id_docente,
                horasSolicitadas: horasSolicitadas,
                horasColocadas: horasDisponibles
            });
        },


        // =========================================
        // REGRESAR ASIGNATURAS A DISPONIBLES
        // =========================================

        eventDragStop: function (info) {

            if (!listaAsignaturas || !info.jsEvent) {
                return;
            }

            const elementoDestino = document.elementFromPoint(
                info.jsEvent.clientX,
                info.jsEvent.clientY
            );

            if (
                !elementoDestino ||
                !listaAsignaturas.contains(elementoDestino)
            ) {
                return;
            }

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

            const eventoEmpalmado = obtenerEventoEmpalmado(info.event);

            if (eventoEmpalmado) {

                info.revert();

                mostrarAdvertencia(
                    `No puedes mover esta asignatura porque se empalma con "${eventoEmpalmado.title}", que ya está colocada en ese horario.`,
                    'Horario ocupado'
                );

                return;
            }

            if (!docenteDisponible(info.event)) {

                info.revert();

                mostrarAdvertencia(
                    'El docente no está disponible en ese horario. Verifica su disponibilidad académica.',
                    'Docente no disponible'
                );

                return;
            }

            if (
                excedeLimiteHorario(info.event) ||
                !horarioPermitido(info.event.start, info.event.end)
            ) {

                info.revert();

                mostrarAdvertencia(
                    'No puedes colocar una asignatura fuera del horario permitido.',
                    'Horario no permitido'
                );

                return;
            }

            if (tocaHorarioExtendidoSabado(info.event)) {

                const aceptado = await confirmarHorarioSabado();

                if (!aceptado) {
                    info.revert();
                    return;
                }
            }

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

            const horasTotales = Number(datos.totalHoras);

            if (!info.event.start || !info.event.end) {
                info.revert();
                return;
            }

            if (!horasTotales || horasTotales <= 0) {

                info.revert();

                mostrarAdvertencia(
                    'No se encontró una cantidad máxima de horas válida.'
                );

                return;
            }


            // =====================================
            // VALIDAR EMPALMES CON OTRAS ASIGNATURAS
            // =====================================

            const eventoEmpalmado = obtenerEventoEmpalmado(info.event);

            if (eventoEmpalmado) {

                info.revert();

                mostrarAdvertencia(
                    `No puedes ampliar esta asignatura porque se empalma con "${eventoEmpalmado.title}", que ya está colocada en ese horario.`,
                    'Horario ocupado'
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

            actualizarTarjetaDisponible(
                idAsignatura,
                null,
                info.event
            );

            actualizarDetallesHorario(info.event);
        },


        // =========================================
        // CONFIGURACIÓN VISUAL
        // =========================================

        height: 1000,
        contentHeight: 800,
        expandRows: true,
        nowIndicator: false,
        allDayMaintainDuration: true
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

        const eventosAsignatura = calendario.getEvents().filter(function (evento) {

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

        if (
            eventoIncluir &&
            !eventosAsignatura.some(function (evento) {
                return evento.id === eventoIncluir.id;
            })
        ) {
            eventosAsignatura.push(eventoIncluir);
        }

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

        const tarjeta = tarjetasOriginales.get(String(idAsignatura));

        if (!tarjeta) {
            return;
        }

        const horasTotales = Number(tarjeta.dataset.horasTotales);

        const horasOcupadas = obtenerHorasOcupadas(
            idAsignatura,
            eventoIncluir,
            eventoExcluir
        );

        const horasRestantes = Math.max(
            0,
            horasTotales - horasOcupadas
        );

        tarjeta.dataset.cantidadHoras = horasRestantes;

        const etiquetaHoras = tarjeta.querySelector(
            '.informacion-asignatura span'
        );

        if (etiquetaHoras) {
            etiquetaHoras.textContent = `${horasRestantes} horas`;
        }

        if (horasRestantes > 0) {

            if (
                listaAsignaturas &&
                !listaAsignaturas.contains(tarjeta)
            ) {

                const mensajeLista = listaAsignaturas.querySelector(
                    '.texto-secundario'
                );

                if (mensajeLista) {
                    mensajeLista.remove();
                }

                listaAsignaturas.appendChild(tarjeta);
            }

        } else {

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

        const detallesHorario = document.getElementById(
            'detalles-horario-registro'
        );

        const botonGuardarHorario = document.getElementById(
            'boton-guardar-horario'
        );

        if (!detallesHorario || !botonGuardarHorario) {
            return;
        }

        detallesHorario.innerHTML = '';

        const eventos = calendario.getEvents().filter(function (evento) {

            return !(
                eventoExcluir &&
                evento.id === eventoExcluir.id
            );
        });

        const diasSemana = [
            'domingo',
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
            'sabado'
        ];

        eventos.forEach(function (evento, indice) {

            if (!evento.start || !evento.end) {
                return;
            }

            const datos = evento.extendedProps;

            const dia = diasSemana[evento.start.getDay()];

            const horaInicio = evento.start.toTimeString().substring(0, 5);
            const horaFin = evento.end.toTimeString().substring(0, 5);

            const campos = {
                dia: dia,
                hora_inicio: horaInicio,
                hora_fin: horaFin,
                aula: datos.aula,
                id_docente: datos.id_docente,
                id_asignatura: datos.id_asignatura
            };

            Object.entries(campos).forEach(function ([nombre, valor]) {

                const input = document.createElement('input');

                input.type = 'hidden';
                input.name = `detalles[${indice}][${nombre}]`;
                input.value = valor ?? '';

                detallesHorario.appendChild(input);
            });
        });

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

            itemSelector: '.tarjeta-asignatura-disponible',

            eventData: function (elemento) {

                const tarjeta = elemento.closest(
                    '.tarjeta-asignatura-disponible'
                );

                if (!tarjeta) {
                    return { create: false };
                }

                const selectorDocente = tarjeta.querySelector(
                    '.selector-docente'
                );

                const selectorEspacio = tarjeta.querySelector(
                    '.selector-espacio'
                );

                if (!selectorDocente || !selectorEspacio) {
                    return { create: false };
                }

                const idDocente = selectorDocente.value;
                const espacio = selectorEspacio.value;

                const nombreDocente = selectorDocente.options[
                    selectorDocente.selectedIndex
                ]?.textContent.trim() || '';

                const opcionDocente = selectorDocente.options[
                    selectorDocente.selectedIndex
                ];

                const cargaAcademica = Number(
                    opcionDocente?.dataset.cargaAcademica
                );

                const horasAsignadasOtrosHorarios = Number(
                    opcionDocente?.dataset.horasAsignadasOtrosHorarios
                ) || 0;

                const nombreEspacio = selectorEspacio.options[
                    selectorEspacio.selectedIndex
                ]?.textContent.trim() || '';

                const nombreAsignatura = tarjeta.querySelector(
                    '.nombre-asignatura-tarjeta'
                )?.textContent.trim() || '';

                const cantidadHoras = Number(
                    tarjeta.dataset.cantidadHoras
                );

                const horasTotales = Number(
                    tarjeta.dataset.horasTotales
                );

                // =====================================
                // VALIDAR LOS DATOS DE LA TARJETA
                // =====================================

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

                // Comprobar que se haya seleccionado un docente.
                if (!idDocente) {

                    mostrarAdvertencia(
                        'Debes seleccionar un docente antes de colocar la asignatura.',
                        'Docente no seleccionado'
                    );

                    return { create: false };
                }

                // Comprobar que se haya seleccionado un espacio.
                if (!espacio) {

                    mostrarAdvertencia(
                        'Debes seleccionar un aula o laboratorio antes de colocar la asignatura.',
                        'Espacio no seleccionado'
                    );

                    return { create: false };
                }


                // =====================================
                // GUARDAR LA TARJETA ORIGINAL
                // =====================================

                tarjetasOriginales.set(
                    String(tarjeta.dataset.idAsignatura),
                    tarjeta
                );


                // =====================================
                // DEVOLVER LOS DATOS DEL EVENTO
                // =====================================

                return {

                    id: `bloque-${++contadorEventos}`,

                    title: nombreAsignatura,

                    duration: {
                        hours: cantidadHoras
                    },

                    extendedProps: {
                        id_asignatura: tarjeta.dataset.idAsignatura,
                        id_docente: idDocente,
                        nombreDocente: nombreDocente,
                        aula: espacio,
                        nombreEspacio: nombreEspacio,

                        totalHoras: horasTotales,
                        cantidadHoras: cantidadHoras,

                        cargaAcademica: cargaAcademica,
                        horasAsignadasOtrosHorarios:
                            horasAsignadasOtrosHorarios
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

            if (!evento.target.matches('.selector-docente')) {
                return;
            }

            // Obtener primero la tarjeta de la asignatura.
            const tarjeta = evento.target.closest(
                '.tarjeta-asignatura-disponible'
            );

            if (!tarjeta) {
                return;
            }

            const idAsignatura = tarjeta.dataset.idAsignatura;

            // =====================================
            // VALIDAR SI LA ASIGNATURA YA TIENE BLOQUES
            // =====================================

            if (asignaturaTieneBloques(idAsignatura)) {

                const idDocenteAsignado = obtenerDocenteAsignado(
                    idAsignatura
                );

                evento.target.value = idDocenteAsignado;

                mostrarAdvertencia(
                    'No se puede cambiar al docente porque ya está colocado en el horario. Elimina todos los bloques de esta materia del calendario para poder cambiar al docente.',
                    'No puedes cambiar al docente'
                );

                return;
            }


            // =====================================
            // VALIDAR LA CARGA ACADÉMICA DEL DOCENTE
            // =====================================

            const idDocente = evento.target.value;

            if (idDocente) {

                const opcionDocente = evento.target.options[
                    evento.target.selectedIndex
                ];

                const cargaAcademica = Number(
                    opcionDocente.dataset.cargaAcademica
                );

                const horasOtrosHorarios = Number(
                    opcionDocente.dataset.horasAsignadasOtrosHorarios
                ) || 0;

                const horasAsignatura = Number(
                    tarjeta.dataset.horasTotales
                ) || 0;

                // Obtener las horas comprometidas en las demás asignaturas.
                const horasOtrasAsignaturas =
                    obtenerHorasComprometidasDocente(
                        idDocente,
                        tarjeta
                    );

                const horasComprometidas =
                    horasOtrosHorarios + horasOtrasAsignaturas;

                const horasRestantes =
                    cargaAcademica - horasComprometidas;

                if (
                    Number.isFinite(cargaAcademica) &&
                    cargaAcademica > 0 &&
                    horasAsignatura > 0 &&
                    horasRestantes < horasAsignatura
                ) {

                    evento.target.value = '';

                    mostrarAdvertencia(
                        `Este docente tiene ${Math.max(0, horasRestantes).toFixed(2)} horas de carga académica disponibles, pero la asignatura requiere ${horasAsignatura} horas. Selecciona otro docente para esta asignatura.`,
                        'Carga académica insuficiente'
                    );

                    return;
                }
            }
        });
    }


    // =========================================
    // MOSTRAR U OCULTAR EL SÁBADO
    // =========================================

    if (botonSabado) {

        botonSabado.addEventListener('click', function () {

            if (sabadoVisible) {

                const existenAsignaturasSabado = calendario.getEvents().some(
                    function (evento) {

                        return (
                            evento.start &&
                            evento.start.getDay() === 6
                        );
                    }
                );

                if (existenAsignaturasSabado) {

                    mostrarAdvertencia(
                        'No puedes ocultar el sábado porque ya existen asignaturas colocadas en ese día. Retira primero todas las asignaturas del sábado.',
                        'No se puede ocultar el sábado'
                    );

                    return;
                }
            }

            sabadoVisible = !sabadoVisible;

            calendario.setOption(
                'hiddenDays',
                sabadoVisible ? [0] : [0, 6]
            );

            botonSabado.innerHTML = sabadoVisible
                ? '<i class="fa-solid fa-minus"></i> Ocultar sábado'
                : '<i class="fa-solid fa-plus"></i> Mostrar sábado';

            botonSabado.setAttribute(
                'aria-expanded',
                sabadoVisible.toString()
            );
        });
    }


    // =========================================
    // CALCULAR LA DURACIÓN DISPONIBLE DEL BLOQUE
    // =========================================

    /**
     * Descripción:
     * Calcula cuánto tiempo puede ocupar una asignatura desde
     * la hora en la que se soltó, sin coincidir con otros eventos,
     * indisponibilidades del docente o límites del calendario.
     *
     * Entradas:
     * - evento: Evento que se acaba de arrastrar al calendario.
     *
     * Salidas:
     * - Devuelve la fecha y hora hasta donde puede colocarse.
     */
    function obtenerFinDisponible(evento) {

        const inicio = evento.start;
        const finSolicitado = evento.end;
        const datos = evento.extendedProps;

        if (!inicio || !finSolicitado) {
            return null;
        }

        let finDisponible = new Date(finSolicitado);


        // =====================================
        // VALIDAR LOS LÍMITES DEL CALENDARIO
        // =====================================

        const esSabado = inicio.getDay() === 6;

        const limiteHorario = new Date(inicio);

        limiteHorario.setHours(
            esSabado ? 16 : 21,
            0,
            0,
            0
        );

        if (limiteHorario < finDisponible) {
            finDisponible = limiteHorario;
        }


        // =====================================
        // VALIDAR OTROS EVENTOS DEL CALENDARIO
        // =====================================

        const otrosEventos = calendario.getEvents().filter(function (otroEvento) {

            return (
                otroEvento.id !== evento.id &&
                otroEvento.start &&
                otroEvento.end &&
                otroEvento.start.toDateString() === inicio.toDateString()
            );
        });

        otrosEventos.forEach(function (otroEvento) {

            const existeCruce =
                inicio < otroEvento.end &&
                finDisponible > otroEvento.start;

            if (!existeCruce) {
                return;
            }

            const momentoImpedimento = new Date(
                Math.max(
                    inicio.getTime(),
                    otroEvento.start.getTime()
                )
            );

            if (momentoImpedimento < finDisponible) {
                finDisponible = momentoImpedimento;
            }
        });


        // =====================================
        // VALIDAR INDISPONIBILIDAD DEL DOCENTE
        // =====================================

        const dia =
            inicio.getDay() === 0
                ? 7
                : inicio.getDay();

        const indisponibilidades = window.disponibilidadesDocentes;

        if (!Array.isArray(indisponibilidades)) {
            return null;
        }

        indisponibilidades.forEach(function (bloque) {

            if (
                String(bloque.id_docente) !== String(datos.id_docente) ||
                Number(bloque.dia) !== dia ||
                Number(bloque.estadoConfirmacion) !== 1
            ) {
                return;
            }

            const inicioBloque = convertirHoraAMinutos(
                bloque.hora_inicio
            );

            const finBloque = convertirHoraAMinutos(
                bloque.hora_fin
            );

            const horaInicioEvento =
                inicio.getHours() * 60 +
                inicio.getMinutes();

            const horaFinEvento =
                finDisponible.getHours() * 60 +
                finDisponible.getMinutes();

            const existeCruce =
                horaInicioEvento < finBloque &&
                horaFinEvento > inicioBloque;

            if (!existeCruce) {
                return;
            }

            const momentoImpedimento = new Date(inicio);

            const minutosImpedimento = Math.max(
                horaInicioEvento,
                inicioBloque
            );

            momentoImpedimento.setHours(
                Math.floor(minutosImpedimento / 60),
                minutosImpedimento % 60,
                0,
                0
            );

            if (momentoImpedimento < finDisponible) {
                finDisponible = momentoImpedimento;
            }
        });


        // =====================================
        // VALIDAR LA CARGA ACADÉMICA DEL DOCENTE
        // =====================================

        const cargaAcademica = Number(datos.cargaAcademica);

        const horasOtrosHorarios = Number(
            datos.horasAsignadasOtrosHorarios
        ) || 0;

        const horasCalendario = obtenerHorasAsignadasCalendario(
            datos.id_docente,
            evento
        );

        const horasRestantesCarga =
            cargaAcademica -
            horasOtrosHorarios -
            horasCalendario;

        if (horasRestantesCarga <= 0) {
            return new Date(inicio);
        }

        const limiteCarga = new Date(inicio);

        limiteCarga.setMinutes(
            limiteCarga.getMinutes() + (horasRestantesCarga * 60)
        );

        if (limiteCarga < finDisponible) {
            finDisponible = limiteCarga;
        }


        // =====================================
        // AJUSTAR A HORAS COMPLETAS
        // =====================================

        const minutosDisponibles =
            (finDisponible.getTime() - inicio.getTime()) / 60000;

        const horasCompletas = Math.floor(
            minutosDisponibles / 60
        );

        const nuevoFin = new Date(inicio);

        nuevoFin.setMinutes(
            nuevoFin.getMinutes() + (horasCompletas * 60)
        );

        return nuevoFin;
    }


    // =========================================
    // CALCULAR HORAS ASIGNADAS AL DOCENTE
    // =========================================

    /**
     * Descripción:
     * Calcula las horas que un docente ya tiene colocadas en el
     * calendario actual, sin contar el evento que se está validando.
     *
     * Entradas:
     * - idDocente: Identificador del docente.
     * - eventoExcluir: Evento que debe excluirse del cálculo.
     *
     * Salidas:
     * - Retorna las horas ocupadas por el docente en este calendario.
     */
    function obtenerHorasAsignadasCalendario(
        idDocente,
        eventoExcluir = null
    ) {

        let horasAsignadas = 0;

        calendario.getEvents().forEach(function (otroEvento) {

            if (
                eventoExcluir &&
                otroEvento.id === eventoExcluir.id
            ) {
                return;
            }

            if (
                String(otroEvento.extendedProps.id_docente) !==
                String(idDocente)
            ) {
                return;
            }

            if (!otroEvento.start || !otroEvento.end) {
                return;
            }

            const minutos =
                (otroEvento.end.getTime() - otroEvento.start.getTime()) / 60000;

            horasAsignadas += minutos / 60;
        });

        return horasAsignadas;
    }


    // =========================================
    // CALCULAR HORAS COMPROMETIDAS DEL DOCENTE
    // =========================================

    /**
     * Descripción:
     * Calcula las horas totales de las asignaturas en las que ya se
     * seleccionó a un docente, independientemente de que estén colocadas
     * o no en el calendario.
     *
     * Entradas:
     * - idDocente: Identificador del docente.
     * - tarjetaExcluir: Tarjeta que se está validando.
     *
     * Salidas:
     * - Devuelve las horas comprometidas en las demás asignaturas.
     */
    function obtenerHorasComprometidasDocente(
        idDocente,
        tarjetaExcluir = null
    ) {

        const tarjetas = new Set();

        // Obtener las tarjetas que todavía están en la lista.
        if (listaAsignaturas) {

            listaAsignaturas.querySelectorAll(
                '.tarjeta-asignatura-disponible'
            ).forEach(function (tarjeta) {

                tarjetas.add(tarjeta);
            });
        }

        // Incluir las tarjetas que se retiraron de la lista
        // porque ya no tenían horas disponibles.
        tarjetasOriginales.forEach(function (tarjeta) {
            tarjetas.add(tarjeta);
        });

        let horasComprometidas = 0;

        tarjetas.forEach(function (tarjeta) {

            if (tarjeta === tarjetaExcluir) {
                return;
            }

            const selector = tarjeta.querySelector(
                '.selector-docente'
            );

            if (
                selector &&
                String(selector.value) === String(idDocente)
            ) {

                horasComprometidas += Number(
                    tarjeta.dataset.horasTotales
                ) || 0;
            }
        });

        return horasComprometidas;
    }

    // LIMPIAR EL CALENDARIO CUANDO CAMBIA EL HORARIO

    window.addEventListener('horario-cambiado', function () {

        // Retirar los eventos del horario anterior.
        calendario.removeAllEvents();

        // Limpiar las referencias de las tarjetas anteriores.
        tarjetasOriginales.clear();

        // Limpiar los detalles del formulario.
        actualizarDetallesHorario();
    });

  
/**
 * Descripción:
 * Comprueba si un docente tiene una clase registrada en otro
 * horario activo que coincida con el bloque que se intenta colocar.
 *
 * Entradas:
 * - evento: Evento que se intenta agregar al calendario.
 *
 * Salidas:
 * - Retorna el bloque con el que existe un empalme.
 * - Retorna null cuando no existe ningún empalme.
 */
function obtenerConflictoDocenteOtroHorario(evento) {

    const datos = evento.extendedProps;

    const horariosActivos = window.horariosDocentesActivos;

    // Comprobar que existan los datos necesarios.
    if (
        !datos.id_docente ||
        !evento.start ||
        !evento.end ||
        !Array.isArray(horariosActivos)
    ) {
        return null;
    }

    // Obtener el nombre del día del evento.
    const diasSemana = [
        'domingo',
        'lunes',
        'martes',
        'miercoles',
        'jueves',
        'viernes',
        'sabado'
    ];

    const diaEvento = diasSemana[evento.start.getDay()];

    // Obtener las horas del evento en minutos.
    const horaInicioEvento =
        evento.start.getHours() * 60 +
        evento.start.getMinutes();

    const horaFinEvento =
        evento.end.getHours() * 60 +
        evento.end.getMinutes();

    // Buscar un bloque del mismo docente que coincida.
    return horariosActivos.find(function (bloque) {

        // Normalizar el día para evitar diferencias por acentos.
        const diaBloque = String(bloque.dia)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();

        // Comprobar que sea el mismo docente y día.
        if (
            String(bloque.id_docente) !== String(datos.id_docente) ||
            diaBloque !== diaEvento
        ) {
            return false;
        }

        // Convertir las horas registradas a minutos.
        const horaInicioBloque = convertirHoraAMinutos(
            bloque.hora_inicio
        );

        const horaFinBloque = convertirHoraAMinutos(
            bloque.hora_fin
        );

        // Comprobar si los horarios se cruzan.
        return (
            horaInicioEvento < horaFinBloque &&
            horaFinEvento > horaInicioBloque
        );

    }) || null;
}


});