
document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos del panel de filtros.
    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const limpiarFiltros = document.getElementById('limpiar-filtros');
    const filtroPeriodo = document.getElementById('filtro-periodo');


    // Obtiene los elementos del panel de consulta.
    const panelConsulta = document.getElementById('panel-consulta');
    const buscarConsulta = document.getElementById('buscar-consulta');
    const resultadosConsulta = document.getElementById('resultados-consulta');
    const sinResultadosConsulta = document.getElementById('sin-resultados-consulta');
    const tarjetaDetalle = document.getElementById('tarjeta-detalle');
    const detalleVacio = document.getElementById('detalle-vacio');


    // Obtiene los docentes disponibles para consultar.
    const opcionesConsulta = resultadosConsulta
        ? Array.from(resultadosConsulta.querySelectorAll('[data-resultado-docente]'))
        : [];


    // Normaliza los textos para ignorar mayúsculas, minúsculas y acentos.
    function normalizarTexto(texto) {

        return (texto || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();

    }


    // Abre o cierra el panel de filtros.
    botonFiltros?.addEventListener('click', function () {

        if (!contenedorFiltros) return;

        // Cambia la visibilidad del panel de filtros.
        contenedorFiltros.hidden = !contenedorFiltros.hidden;

        // Actualiza el estado de accesibilidad del botón.
        botonFiltros.setAttribute(
            'aria-expanded',
            String(!contenedorFiltros.hidden)
        );

        // Resalta el botón mientras el panel esté abierto.
        botonFiltros.classList.toggle(
            'boton-activo',
            !contenedorFiltros.hidden
        );

    });


    // Limpia el periodo seleccionado.
    limpiarFiltros?.addEventListener('click', function () {

        if (filtroPeriodo) {
            filtroPeriodo.value = '';
        }

    });


    // Muestra la información del docente seleccionado.
    function consultarDocente(idDocente, botonSeleccionado = null) {

        if (!resultadosConsulta || !tarjetaDetalle) return;

        const detalleSeleccionado = tarjetaDetalle.querySelector(
            '[data-detalle-docente="' + idDocente + '"]'
        );

        if (!detalleSeleccionado) return;

        // Oculta el mensaje inicial y los detalles anteriores.
        if (detalleVacio) {
            detalleVacio.hidden = true;
        }

        tarjetaDetalle.querySelectorAll('[data-detalle-docente]').forEach(function (detalle) {
            detalle.hidden = true;
        });

        // Muestra la información del docente seleccionado.
        detalleSeleccionado.hidden = false;

        // Quita la selección visual de los resultados anteriores.
        opcionesConsulta.forEach(function (opcion) {
            opcion.classList.remove('seleccionado');
            opcion.setAttribute('aria-pressed', 'false');
        });

        // Marca el resultado seleccionado.
        if (botonSeleccionado) {
            botonSeleccionado.classList.add('seleccionado');
            botonSeleccionado.setAttribute('aria-pressed', 'true');
        }

        // Mantiene visible el panel de consulta.
        if (panelConsulta) {
            panelConsulta.hidden = false;
        }

    }


    // Filtra los docentes por nombre o matrícula.
    function actualizarResultadosConsulta() {

        const texto = normalizarTexto(buscarConsulta?.value);
        let cantidadVisible = 0;

        opcionesConsulta.forEach(function (opcion) {

            const nombre = normalizarTexto(opcion.dataset.nombreConsulta);
            const matricula = normalizarTexto(opcion.dataset.matriculaConsulta);

            // Comprueba si el docente coincide con la búsqueda.
            const mostrar = texto.length > 0 &&
                (nombre.includes(texto) || matricula.includes(texto));

            opcion.hidden = !mostrar;

            if (mostrar) {
                cantidadVisible++;
            }

        });


        // Oculta los detalles anteriores cuando no hay una búsqueda.
        if (!texto) {

            tarjetaDetalle?.querySelectorAll('[data-detalle-docente]').forEach(function (detalle) {
                detalle.hidden = true;
            });

            if (detalleVacio) {
                detalleVacio.hidden = false;
            }

            // Quita la selección visual de los resultados.
            opcionesConsulta.forEach(function (opcion) {
                opcion.classList.remove('seleccionado');
                opcion.setAttribute('aria-pressed', 'false');
            });

        }


        // Muestra un aviso cuando no hay coincidencias.
        if (sinResultadosConsulta) {
            sinResultadosConsulta.hidden = !texto || cantidadVisible > 0;
        }

    }


    // Actualiza los resultados conforme se escribe el nombre o matrícula.
    buscarConsulta?.addEventListener('input', actualizarResultadosConsulta);


    // Permite seleccionar un docente de los resultados.
    opcionesConsulta.forEach(function (opcion) {

        opcion.setAttribute('aria-pressed', 'false');

        opcion.addEventListener('click', function () {

            consultarDocente(
                opcion.dataset.resultadoDocente,
                opcion
            );

        });

    });


    // Oculta los resultados al cargar la página por primera vez.
    actualizarResultadosConsulta();


    // Confirma la eliminación de la disponibilidad académica.
    document.querySelectorAll('.formulario-eliminar-disponibilidad').forEach(function (formulario) {

        formulario.addEventListener('submit', function (evento) {

            // Obtiene el nombre del docente.
            const nombreDocente = formulario.dataset.nombreDocente;

            // Solicita confirmación antes de eliminar.
            const confirmarEliminacion = confirm(
                `¿Estás seguro de eliminar la disponibilidad académica de ${nombreDocente}?`
            );

            // Cancela el envío si no se confirma.
            if (!confirmarEliminacion) {
                evento.preventDefault();
            }

        });

    });


    // Obtiene los elementos de búsqueda y paginación de docentes.
    const buscarDisponibilidad = document.getElementById('buscar-disponibilidad');
    const cuerpoTabla = document.getElementById('cuerpo-tabla-disponibilidad');
    const totalResultados = document.getElementById('total-resultados');
    const filaSinResultados = document.getElementById('fila-sin-resultados');
    const informacionPagina = document.getElementById('informacion-pagina');
    const botonPaginaAnterior = document.getElementById('pagina-anterior');
    const botonPaginaSiguiente = document.getElementById('pagina-siguiente');


    // Obtiene las filas reales de docentes.
    const filasDocentes = cuerpoTabla
        ? Array.from(cuerpoTabla.querySelectorAll('tr[data-id-docente]'))
        : [];


    // Configuración de la paginación.
    const registrosPorPagina = 10;
    let paginaActual = 1;
    let docentesFiltrados = [...filasDocentes];


    // Actualiza la información y visibilidad de la tabla.
    function actualizarTablaDisponibilidad() {

        const totalDocentes = docentesFiltrados.length;
        const totalPaginas = Math.ceil(totalDocentes / registrosPorPagina);

        // Evita permanecer en una página que ya no existe.
        if (paginaActual > totalPaginas && totalPaginas > 0) {
            paginaActual = totalPaginas;
        }

        const inicio = (paginaActual - 1) * registrosPorPagina;
        const fin = inicio + registrosPorPagina;

        // Oculta todas las filas antes de mostrar la página correspondiente.
        filasDocentes.forEach(function (fila) {
            fila.hidden = true;
        });

        // Muestra únicamente los docentes de la página actual.
        docentesFiltrados.slice(inicio, fin).forEach(function (fila) {
            fila.hidden = false;
        });

        // Actualiza el contador de resultados.
        if (totalResultados) {
            totalResultados.textContent = totalDocentes;
        }

        // Muestra el mensaje cuando no hay coincidencias.
        if (filaSinResultados) {
            filaSinResultados.hidden = totalDocentes > 0;
        }

        // Actualiza la información de la página.
        if (informacionPagina) {
            informacionPagina.textContent =
                `Página ${totalDocentes > 0 ? paginaActual : 0} de ${totalPaginas}`;
        }

        // Actualiza el estado de los botones.
        if (botonPaginaAnterior) {
            botonPaginaAnterior.disabled = paginaActual <= 1;
        }

        if (botonPaginaSiguiente) {
            botonPaginaSiguiente.disabled =
                paginaActual >= totalPaginas || totalPaginas === 0;
        }

    }


    // Busca docentes por matrícula o nombre en la tabla.
    buscarDisponibilidad?.addEventListener('input', function () {

        const texto = normalizarTexto(buscarDisponibilidad.value);

        docentesFiltrados = filasDocentes.filter(function (fila) {

            const matricula = normalizarTexto(fila.dataset.matricula);
            const nombre = normalizarTexto(fila.dataset.nombre);

            return matricula.includes(texto) || nombre.includes(texto);

        });

        // Reinicia la paginación después de cada búsqueda.
        paginaActual = 1;

        actualizarTablaDisponibilidad();

    });


    // Muestra la página anterior.
    botonPaginaAnterior?.addEventListener('click', function () {

        if (paginaActual > 1) {
            paginaActual--;
            actualizarTablaDisponibilidad();
        }

    });


    // Muestra la página siguiente.
    botonPaginaSiguiente?.addEventListener('click', function () {

        const totalPaginas = Math.ceil(
            docentesFiltrados.length / registrosPorPagina
        );

        if (paginaActual < totalPaginas) {
            paginaActual++;
            actualizarTablaDisponibilidad();
        }

    });


    // Inicializa la tabla.
    actualizarTablaDisponibilidad();

    // Obtiene el docente que se desea consultar inicialmente.
    const idDocenteConsultaInicial = window.idDocenteConsultaInicial;

    // Comprueba si se recibió un docente desde el botón Consultar.
    if (idDocenteConsultaInicial) {

        const opcionSeleccionada = opcionesConsulta.find(function (opcion) {
            return opcion.dataset.resultadoDocente === String(idDocenteConsultaInicial);
        });

        if (opcionSeleccionada) {

            // Coloca el nombre del docente en el buscador.
            buscarConsulta.value = opcionSeleccionada.dataset.nombreConsulta;

            // Actualiza los resultados de búsqueda.
            actualizarResultadosConsulta();

            // Muestra directamente la información del docente.
            consultarDocente(
                idDocenteConsultaInicial,
                opcionSeleccionada
            );

        } else {

            actualizarResultadosConsulta();

        }

    } else {

        // Oculta los resultados al cargar normalmente la página.
        actualizarResultadosConsulta();

    }


});
