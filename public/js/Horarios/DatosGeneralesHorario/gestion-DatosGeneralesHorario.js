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
    const filasHorarios = cuerpoTabla
        ? Array.from(cuerpoTabla.querySelectorAll('tr[data-nombre]'))
        : [];

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

    // Aplica la búsqueda y los filtros seleccionados en la tabla.
    function filtrarHorarios() {

        const textoBusqueda = buscarHorario ? buscarHorario.value.trim().toLowerCase() : '';
        const anioSeleccionado = filtroAnio ? filtroAnio.value : '';
        const periodoSeleccionado = filtroPeriodo ? normalizarTexto(filtroPeriodo.value) : '';
        const carreraSeleccionada = filtroCarrera ? filtroCarrera.value : '';
        const grupoSeleccionado = filtroGrupo ? filtroGrupo.value : '';
        const estadoSeleccionado = filtroEstado ? filtroEstado.value : '';

        let horariosVisibles = 0;

        filasHorarios.forEachfunction (fila) 

            // Obtiene la información de cada horario.
            const nombre = fila.dataset.nombre || '';
            const periodo = fila.dataset.periodo || '';
            const anio = fila.dataset.anio || '';
            const idPeriodo = fila.dataset.idPeriodo || '';
            const carrera = fila.dataset.carrera || '';
            const idCarrera = fila.dataset.idCarrera || '';
            const grupo = fila.dataset.grupo || '';
            const idGrupo = fila.dataset.idGrupo || '';
            const estado = fila.dataset.estado || '';

            // Comprueba si coincide con el texto de búsqueda.
            const coincideBusqueda = nombre.includes(textoBusqueda) || periodo.includes(textoBusqueda) || carrera.includes(textoBusqueda) || grupo.includes(textoBusqueda);

            const coincideAnio = anioSeleccionado === '' || anio === anioSeleccionado;
            const coincidePeriodo = periodoSeleccionado === '' || idPeriodo === periodoSeleccionado;
            const coincideCarrera = carreraSeleccionada === '' || idCarrera === carreraSeleccionada;
            const coincideGrupo = grupoSeleccionado === '' || idGrupo === grupoSeleccionado;
            const coincideEstado = estadoSeleccionado === '' || estado === estadoSeleccionado;

            // Determina si la fila debe mostrarse.
            const mostrarFila = coincideBusqueda && coincideAnio && coincidePeriodo && coincideCarrera && coincideGrupo && coincideEstado;

            fila.hidden = !mostrarFila;

            if (mostrarFila) {
                horariosVisibles++;
            }

        ;

        // Actualiza el total de resultados visibles.
        if (totalResultados) {
            totalResultados.textContent = horariosVisibles;
        }

        // Muestra el mensaje únicamente cuando existen horarios registrados,
        if (filaSinFiltros) {
            filaSinFiltros.hidden =
                filasHorarios.length === 0 || horariosVisibles !== 0;
        }
    }

    /*  EVENTOS DE BÚSQUEDA Y FILTROS */

    // Ejecuta la búsqueda mientras se escribe.
    if (buscarHorario) {
        buscarHorario.addEventListener('input', filtrarHorarios);
    }

    // Ejecuta el filtrado cuando cambia el año.
    if (filtroAnio) {
        filtroAnio.addEventListener('change', function () {

            // Restablece el periodo si pertenece a otro año.
            if (filtroPeriodo) {

                const opcionPeriodo = filtroPeriodo.selectedOptions[0];

                if ( opcionPeriodo && opcionPeriodo.dataset.anio && filtroAnio.value !== '' && opcionPeriodo.dataset.anio !== filtroAnio.value) {
                    filtroPeriodo.value = '';
                }

            }

            filtrarHorarios();

        });
    }

    // Ejecuta el filtrado cuando cambia cualquier otro criterio.
    [filtroPeriodo, filtroCarrera, filtroGrupo, filtroEstado].forEach(function (filtro) {

        if (filtro) {
            filtro.addEventListener('change', filtrarHorarios);
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

            // Vuelve a mostrar todos los horarios.
            filtrarHorarios();

        });

    }

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