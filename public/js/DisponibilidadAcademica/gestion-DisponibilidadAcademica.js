document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos del panel de filtros.
    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const limpiarFiltros = document.getElementById('limpiar-filtros');
    const filtroPeriodo = document.getElementById('filtro-periodo');

    // Obtiene los elementos del panel de consulta.
    const panelConsulta = document.getElementById('panel-consulta');
    const buscarConsulta = document.getElementById('buscar-consulta-asignatura');
    const resultadosConsulta = document.getElementById('resultados-consulta-asignatura');
    const sinResultadosConsulta = document.getElementById('sin-resultados-consulta-asignatura');
    const tarjetaDetalle = document.getElementById('tarjeta-detalle-asignatura');
    const detalleVacio = document.getElementById('detalle-vacio-asignatura');

    //Asignaturas disponibles para consultar.
    const opcionesConsulta = resultadosConsulta ? Array.from(resultadosConsulta.querySelectorAll('[data-resultado-asignatura]')) : [];

    // Total de asignaturas seleccionadas.
    const totalAsignaturas = document.getElementById('total-asignaturas-seleccionadas');

    // Normaliza los textos para ignorar mayúsculas, minúsculas y acentos.
    function normalizarTexto(texto) {
        return (texto || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    // Muestra los detalles de la asignatura seleccionada.
    function consultarAsignatura(idAsignatura, botonSeleccionado = null) {
        if (!tarjetaDetalle) return;

        const detalleSeleccionado = tarjetaDetalle.querySelector( `[data-detalle-asignatura="${idAsignatura}"]` );

        if (!detalleSeleccionado) return;

        // Oculta el mensaje inicial y los detalles anteriores.
        if (detalleVacio) {
            detalleVacio.hidden = true;
        }

        tarjetaDetalle.querySelectorAll('[data-detalle-asignatura]').forEach(function (detalle) {
            detalle.hidden = true;
        });

        // Muestra los datos de la asignatura seleccionada.
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

    // Filtra los resultados del panel de consulta.
    function actualizarResultadosConsulta() {
        const texto = normalizarTexto(buscarConsulta?.value);
        let cantidadVisible = 0;

        opcionesConsulta.forEach(function (opcion) {

            // Obtiene el nombre de la asignatura y de la carrera.
            const nombre = normalizarTexto(opcion.dataset.nombreConsulta);
            const carrera = normalizarTexto(opcion.dataset.carreraConsulta);

            // Comprueba si coincide con el texto de búsqueda.
            const mostrar = !texto || nombre.includes(texto) || carrera.includes(texto);

            opcion.hidden = !mostrar;

            if (mostrar) {
                cantidadVisible++;
            }
        });

        // Oculta los detalles cuando se modifica la búsqueda.
        if (texto) {
            tarjetaDetalle?.querySelectorAll('[data-detalle-asignatura]').forEach(function (detalle) {
                detalle.hidden = true;
            });

            if (detalleVacio) {
                detalleVacio.hidden = false;
            }

            opcionesConsulta.forEach(function (opcion) {
                opcion.classList.remove('seleccionado');
                opcion.setAttribute('aria-pressed', 'false');
            });
        } else {
            // Muestra nuevamente todas las asignaturas.
            if (detalleVacio) {
                detalleVacio.hidden = false;
            }

            tarjetaDetalle?.querySelectorAll('[data-detalle-asignatura]').forEach(function (detalle) {
                detalle.hidden = true;
            });

            opcionesConsulta.forEach(function (opcion) {
                opcion.classList.remove('seleccionado');
                opcion.setAttribute('aria-pressed', 'false');
            });
        }

        // Muestra un aviso cuando no hay coincidencias.
        if (sinResultadosConsulta) {
            sinResultadosConsulta.hidden = cantidadVisible > 0;
        }
    }

    // Actualiza los resultados mientras se escribe.
    buscarConsulta?.addEventListener('input', actualizarResultadosConsulta);

    // Permite consultar una asignatura desde la lista de resultados.
    opcionesConsulta.forEach(function (opcion) {
        opcion.setAttribute('aria-pressed', 'false');

        opcion.addEventListener('click', function () {
            consultarAsignatura(
                opcion.dataset.resultadoAsignatura,
                opcion
            );
        });
    });

    // Oculta los detalles y muestra todas las asignaturas al cargar.
    if (buscarConsulta) {
        buscarConsulta.value = '';
    }

    actualizarResultadosConsulta();

    // Abre o cierra el panel de filtros.
    botonFiltros?.addEventListener('click', function () {
        if (!contenedorFiltros) return;

        // Cambia la visibilidad del panel de filtros.
        contenedorFiltros.hidden = !contenedorFiltros.hidden;

        // Actualiza el estado de accesibilidad del botón.
        botonFiltros.setAttribute( 'aria-expanded', String(!contenedorFiltros.hidden) );

        // Resalta el botón mientras el panel esté abierto.
        botonFiltros.classList.toggle( 'boton-activo', !contenedorFiltros.hidden );
    });

    // Limpia el periodo seleccionado.
    limpiarFiltros?.addEventListener('click', function () {
        if (filtroPeriodo) {
            filtroPeriodo.value = '';
        }
    });


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

    // Obtiene las filas de docentes.
    const filasDocentes = cuerpoTabla
        ? Array.from(cuerpoTabla.querySelectorAll('tr[data-id-docente]'))
        : [];

    // Configuración de la paginación.
    const registrosPorPagina = 10;
    let paginaActual = 1;
    let docentesFiltrados = [...filasDocentes];

    // Normaliza los textos para ignorar mayúsculas, minúsculas y acentos.
    function normalizarTexto(texto) {
        return (texto || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

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

    // Busca docentes por matrícula o nombre.
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


});
