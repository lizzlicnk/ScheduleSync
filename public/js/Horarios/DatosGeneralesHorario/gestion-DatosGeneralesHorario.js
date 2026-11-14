document.addEventListener('DOMContentLoaded', function () {

    /* ELEMENTOS DEL PANEL DE FILTROS */

    // Obtiene los elementos del panel de filtros.
    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const limpiarFiltros = document.getElementById('limpiar-filtros');

    // Obtiene los campos utilizados para filtrar los horarios.
    const filtroAnio = document.getElementById('filtro-anio');
    const filtroPeriodo = document.getElementById('filtro-periodo');
    const filtroCarrera = document.getElementById('filtro-carrera');
    const filtroGrupo = document.getElementById('filtro-grupo');
    const filtroEstado = document.getElementById('filtro-estado');

    // Obtiene los elementos de búsqueda y de la tabla.
    const buscarHorario = document.getElementById('buscar-horario');
    const cuerpoTabla = document.getElementById('cuerpo-tabla-horarios');
    const totalResultados = document.getElementById('total-resultados');
    const filaSinFiltros = document.getElementById('fila-sin-filtros');

    // Obtiene las filas que contienen horarios registrados.
    const filasHorarios = cuerpoTabla ? Array.from(cuerpoTabla.querySelectorAll('tr[data-nombre]')): [];

    // Obtiene los elementos de la paginación.
    const informacionPagina = document.getElementById('informacion-pagina-horarios');
    const botonAnterior = document.getElementById('pagina-anterior-horarios');
    const botonSiguiente = document.getElementById('pagina-siguiente-horarios');

    // Define la cantidad de horarios por página.
    const filasPorPagina = 5;
    let paginaActual = 1;
    let horariosCoincidentes = [];

    /* CONSULTA DE HORARIOS */

    // Obtiene los elementos del panel de consulta.
    const panelConsulta = document.getElementById('panel-consulta');
    const buscarConsulta = document.getElementById('buscar-consulta');
    const resultadosConsulta = document.getElementById('resultados-consulta');
    const sinResultadosConsulta = document.getElementById('sin-resultados-consulta');
    const tarjetaDetalle = document.getElementById('tarjeta-detalle');
    const detalleVacio = document.getElementById('detalle-vacio');

    // Consultar automáticamente el horario seleccionado desde la tabla.
    const parametrosURL = new URLSearchParams(window.location.search);
    const idHorarioConsultar = parametrosURL.get('consultar');

    /* MOSTRAR Y OCULTAR FILTROS */

    // Muestra u oculta el panel de filtros.
    if (botonFiltros && contenedorFiltros) {

        botonFiltros.addEventListener('click', function () {

            const filtrosAbiertos = !contenedorFiltros.hidden;

            contenedorFiltros.hidden = filtrosAbiertos;

            botonFiltros.setAttribute(
                'aria-expanded',
                String(!filtrosAbiertos)
            );

            botonFiltros.classList.toggle(
                'boton-activo',
                !filtrosAbiertos
            );

        });

    }

    /* FILTRAR HORARIOS */

    // Aplica la búsqueda, los filtros y la paginación.
    function filtrarHorarios() {

        const textoBusqueda = normalizarTexto(buscarHorario?.value);
        const anioSeleccionado = filtroAnio?.value || '';
        const periodoSeleccionado = filtroPeriodo?.value || '';
        const carreraSeleccionada = filtroCarrera?.value || '';
        const grupoSeleccionado = filtroGrupo?.value || '';
        const estadoSeleccionado = filtroEstado?.value || '';

        // Obtiene los horarios que coinciden con los filtros.
        horariosCoincidentes = filasHorarios.filter(function (fila) {

            const nombre = normalizarTexto(fila.dataset.nombre);
            const periodo = normalizarTexto(fila.dataset.periodo);
            const carrera = normalizarTexto(fila.dataset.carrera);
            const grupo = normalizarTexto(fila.dataset.grupo);

            const coincideBusqueda =
                nombre.includes(textoBusqueda) ||
                periodo.includes(textoBusqueda) ||
                carrera.includes(textoBusqueda) ||
                grupo.includes(textoBusqueda);

            const coincideAnio =
                anioSeleccionado === '' || fila.dataset.anio === anioSeleccionado;

            const coincidePeriodo =
                periodoSeleccionado === '' || fila.dataset.idPeriodo === periodoSeleccionado;

            const coincideCarrera =
                carreraSeleccionada === '' || fila.dataset.idCarrera === carreraSeleccionada;

            const coincideGrupo =
                grupoSeleccionado === '' || fila.dataset.idGrupo === grupoSeleccionado;

            const coincideEstado =
                estadoSeleccionado === '' || fila.dataset.estado === estadoSeleccionado;

            return coincideBusqueda && coincideAnio && coincidePeriodo &&
                coincideCarrera && coincideGrupo && coincideEstado;

        });

        // Calcula el total de páginas.
        const totalPaginas = Math.max(
            1,
            Math.ceil(horariosCoincidentes.length / filasPorPagina)
        );

        // Ajusta la página actual si supera el total disponible.
        if (paginaActual > totalPaginas) {
            paginaActual = totalPaginas;
        }

        // Obtiene las filas correspondientes a la página actual.
        const inicio = (paginaActual - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;

        const filasVisibles = new Set(
            horariosCoincidentes.slice(inicio, fin)
        );

        // Muestra únicamente las filas de la página actual.
        filasHorarios.forEach(function (fila) {
            fila.hidden = !filasVisibles.has(fila);
        });

        const hayResultados = horariosCoincidentes.length > 0;

        // Actualiza los mensajes de la tabla.
        if (filaSinFiltros) {
            filaSinFiltros.hidden = hayResultados || filasHorarios.length === 0;
        }

        // Actualiza el total de resultados.
        if (totalResultados) {
            totalResultados.textContent = horariosCoincidentes.length;
        }

        // Actualiza la información de la página.
        if (informacionPagina) {
            informacionPagina.textContent =
                'Página ' + paginaActual + ' de ' + totalPaginas;
        }

        // Habilita o deshabilita los botones de paginación.
        if (botonAnterior) {
            botonAnterior.disabled = paginaActual <= 1;
            botonAnterior.hidden = !hayResultados;
        }

        if (botonSiguiente) {
            botonSiguiente.disabled = paginaActual >= totalPaginas;
            botonSiguiente.hidden = !hayResultados;
        }

    }

    /* EVENTOS DE BÚSQUEDA Y FILTROS */

    // Regresa a la primera página cuando cambia la búsqueda.
    buscarHorario?.addEventListener('input', function () {
        paginaActual = 1;
        filtrarHorarios();
    });

    // Ejecuta los filtros y regresa a la primera página.
    [filtroAnio, filtroPeriodo, filtroCarrera, filtroGrupo, filtroEstado]
        .forEach(function (filtro) {

            filtro?.addEventListener('change', function () {

                // Restablece el periodo si pertenece a otro año.
                if (filtro === filtroAnio && filtroPeriodo) {

                    const opcionPeriodo = filtroPeriodo.selectedOptions[0];

                    if (
                        opcionPeriodo &&
                        opcionPeriodo.dataset.anio &&
                        filtroAnio.value !== '' &&
                        opcionPeriodo.dataset.anio !== filtroAnio.value
                    ) {
                        filtroPeriodo.value = '';
                    }

                }

                paginaActual = 1;
                filtrarHorarios();

            });

        });

    // Muestra la página anterior.
    botonAnterior?.addEventListener('click', function () {

        if (paginaActual > 1) {
            paginaActual--;
            filtrarHorarios();
        }

    });

    // Muestra la página siguiente.
    botonSiguiente?.addEventListener('click', function () {

        const totalPaginas = Math.ceil(
            horariosCoincidentes.length / filasPorPagina
        );

        if (paginaActual < totalPaginas) {
            paginaActual++;
            filtrarHorarios();
        }

    });

    /* LIMPIAR FILTROS */

    if (limpiarFiltros) {

        limpiarFiltros.addEventListener('click', function () {

            // Restablece todos los campos de filtros.
            if (filtroAnio) filtroAnio.value = '';
            if (filtroPeriodo) filtroPeriodo.value = '';
            if (filtroCarrera) filtroCarrera.value = '';
            if (filtroGrupo) filtroGrupo.value = '';
            if (filtroEstado) filtroEstado.value = '';
            if (buscarHorario) buscarHorario.value = '';

            // Restablece la página actual.
            paginaActual = 1;

            // Aplica nuevamente los filtros.
            filtrarHorarios();

        });

    }

    // Inicializa la tabla y la paginación al cargar la página.
    filtrarHorarios();

    /*  CAMPOS DEPENDIENTES DEL FORMULARIO */

    // Obtiene los campos del formulario de registro.
    const formularioHorario = document.getElementById('formAgregarHorario');

    if (formularioHorario) {

        const periodoFormulario = document.getElementById('id_periodo');
        const carreraFormulario = document.getElementById('id_carrera');
        const grupoFormulario = document.getElementById('id_grupo');

        // Guarda las opciones originales de grupos.
        const opcionesGrupos = grupoFormulario ? Array.from(grupoFormulario.options).slice(1) : [];

        // Actualiza los grupos disponibles según el periodo y la carrera.
        function actualizarGrupos() {

            if (!periodoFormulario || !carreraFormulario || !grupoFormulario) {
                return;
            }

            const periodoSeleccionado = periodoFormulario.value;
            const carreraSeleccionada = carreraFormulario.value;

            const grupoAnterior = grupoFormulario.value;

            // Limpia las opciones actuales del selector.
            grupoFormulario.innerHTML = '';

            const opcionInicial = document.createElement('option');
            opcionInicial.value = '';
            opcionInicial.textContent = 'Selecciona un grupo';

            grupoFormulario.appendChild(opcionInicial);

            let grupoAnteriorDisponible = false;

            // Agrega únicamente los grupos que cumplen los criterios.
            opcionesGrupos.forEach(function (opcion) {

                const coincidePeriodo = periodoSeleccionado !== '' && opcion.dataset.periodo === periodoSeleccionado;
                const coincideCarrera = carreraSeleccionada !== '' && opcion.dataset.carrera === carreraSeleccionada;

                // Comprueba si el grupo ya tiene un horario registrado para ese periodo.
                const grupoYaTieneHorario = filasHorarios.some(function (fila) {

                    return (
                        fila.dataset.idGrupo === opcion.value &&
                        fila.dataset.idPeriodo === periodoSeleccionado
                    );

                });

                // Agrega el grupo únicamente si todavía no tiene un horario registrado.
                if ( coincidePeriodo && coincideCarrera && !grupoYaTieneHorario ) {

                    const nuevaOpcion = opcion.cloneNode(true);

                    grupoFormulario.appendChild(nuevaOpcion);

                    if (nuevaOpcion.value === grupoAnterior) {
                        grupoAnteriorDisponible = true;
                    }
                }
            });

            // Conserva la selección si todavía está disponible.
            if (grupoAnteriorDisponible) {
                grupoFormulario.value = grupoAnterior;
            } else {
                grupoFormulario.value = '';
            }
        }

        // Actualiza los grupos al modificar el periodo o la carrera.
        [periodoFormulario, carreraFormulario].forEach(function (campo) {

            if (campo) {
                campo.addEventListener('change', actualizarGrupos);
            }
        });


        // Ejecuta la actualización inicial.
        actualizarGrupos();
    }

    /* CONSULTA */

    // Obtiene las opciones disponibles para consultar.
    const opcionesConsulta = resultadosConsulta ? Array.from(resultadosConsulta.querySelectorAll('[data-resultado-horario]')) : [];

    // Normaliza los textos para ignorar mayúsculas y acentos.
    function normalizarTexto(texto) {
        return (texto || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    }

    // Muestra la información del horario seleccionado.
    function consultarHorario(idHorario, botonSeleccionado = null) {

        if (!resultadosConsulta || !tarjetaDetalle) return;

        const detalleSeleccionado = tarjetaDetalle.querySelector( '[data-detalle-horario="' + idHorario + '"]' );

        if (!detalleSeleccionado) return;

        // Oculta el mensaje inicial y los detalles anteriores.
        if (detalleVacio) {
            detalleVacio.hidden = true;
        }

        tarjetaDetalle.querySelectorAll('[data-detalle-horario]').forEach(function (detalle) {
            detalle.hidden = true;
        });

        // Muestra la información del horario seleccionado.
        detalleSeleccionado.hidden = false;

        // Marca visualmente el resultado seleccionado.
        opcionesConsulta.forEach(function (opcion) {
            opcion.classList.remove('seleccionado');
            opcion.setAttribute('aria-pressed', 'false');
        });

        if (botonSeleccionado) {
            botonSeleccionado.classList.add('seleccionado');
            botonSeleccionado.setAttribute('aria-pressed', 'true');
        }
    }

    // Filtra los resultados conforme se escribe el nombre.
    function actualizarResultadosConsulta() {

        const texto = normalizarTexto(buscarConsulta?.value);
        let cantidadVisible = 0;

        opcionesConsulta.forEach(function (opcion) {

            const nombre = normalizarTexto(opcion.dataset.nombreConsulta);

            // Solo muestra resultados cuando existe texto de búsqueda.
            const mostrar = texto.length > 0 && nombre.includes(texto);

            opcion.hidden = !mostrar;

            if (mostrar) {
                cantidadVisible++;
            }
        });

        // Oculta los detalles anteriores 
        if (!texto) {

            tarjetaDetalle?.querySelectorAll('[data-detalle-horario]').forEach(function (detalle) {
                detalle.hidden = true;
            });

            if (detalleVacio) {
                detalleVacio.hidden = false;
            }

            opcionesConsulta.forEach(function (opcion) {
                opcion.classList.remove('seleccionado');
                opcion.setAttribute('aria-pressed', 'false');
            });
        }

        // no hay  coincidencias.
        if (sinResultadosConsulta) {
            sinResultadosConsulta.hidden = !texto || cantidadVisible > 0;
        }
    }

    // Actualiza los resultados 
    buscarConsulta?.addEventListener('input', actualizarResultadosConsulta);

    // Permite seleccionar un horario de los resultados.
    opcionesConsulta.forEach(function (opcion) {

        opcion.addEventListener('click', function () {
            consultarHorario( opcion.dataset.resultadoHorario, opcion );
        });

    });

    // Oculta los resultados al cargar la página.
    actualizarResultadosConsulta();

    // Consultar automáticamente el horario seleccionado desde la tabla.
    if (idHorarioConsultar) {

        // Busca el resultado correspondiente al horario.
        const resultadoHorario = opcionesConsulta.find(function (opcion) {
            return opcion.dataset.resultadoHorario === idHorarioConsultar;
        });

        // Verifica que el horario exista.
        if (resultadoHorario) {

            // Limpia el campo de búsqueda 
            if (buscarConsulta) {
                buscarConsulta.value = '';
            }

            // Actualiza la lista de resultados.
            actualizarResultadosConsulta();

            // Muestra directamente la información del horario seleccionado.
            consultarHorario( idHorarioConsultar, resultadoHorario );

            // Desplaza la página hasta el panel de consulta.
            panelConsulta?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

        }
    }

    /*  CERRAR MODALES CON LA TECLA ESCAPE */

    document.addEventListener('keydown', function (evento) {

        if (evento.key === 'Escape') {

            cerrarModalBajaHorario();
            cerrarModalMensajeHorario();

        }

    });

    /* CONFIRMAR DESACTIVACIÓN DE HORARIO */

    // Obtener los formularios para desactivar horarios.
    const formulariosDesactivar = document.querySelectorAll(
        '.formulario-desactivar-horario'
    );

    formulariosDesactivar.forEach(function (formulario) {

        formulario.addEventListener('submit', async function (evento) {

            // Evitar que el formulario se envíe inmediatamente.
            evento.preventDefault();

            const nombreHorario = formulario.dataset.nombre;

            // Solicitar confirmación antes de desactivar.
            const confirmado = await confirmarDesactivarHorario(nombreHorario);

            // Enviar el formulario solamente si se confirma.
            if (confirmado) {
                formulario.submit();
            }

        });

    });

});