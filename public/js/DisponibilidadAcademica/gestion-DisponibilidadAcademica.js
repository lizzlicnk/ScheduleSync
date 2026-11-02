
document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos principales de la tabla.
    const buscarDisponibilidad = document.getElementById('buscar-disponibilidad-academica');
    const cuerpoTabla = document.getElementById('cuerpo-tabla-disponibilidad-academica');
    const totalResultados = document.getElementById('total-resultados');
    const filaSinDisponibilidad = document.getElementById('fila-sin-disponibilidad-academica');
    const filaSinResultados = document.getElementById('fila-sin-resultados');

    // Obtiene los elementos del panel de filtros.
    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const limpiarFiltros = document.getElementById('limpiar-filtros');
    const filtroTipo = document.getElementById('filtro-tipo');

    // Obtiene los controles de paginación.
    const informacionPagina = document.getElementById('informacion-pagina');
    const paginaAnterior = document.getElementById('pagina-anterior');
    const paginaSiguiente = document.getElementById('pagina-siguiente');

    // Obtiene los elementos del panel de consulta.
    const panelConsulta = document.getElementById('panel-consulta');
    const buscarConsulta = document.getElementById('buscar-consulta-disponibilidad');
    const resultadosConsulta = document.getElementById('resultados-consulta-disponibilidad');
    const sinResultadosConsulta = document.getElementById('sin-resultados-consulta-disponibilidad');
    const tarjetaDetalle = document.getElementById('tarjeta-detalle-disponibilidad');
    const detalleVacio = document.getElementById('detalle-vacio-disponibilidad');

    // Guarda la página actual de la tabla.
    let paginaActual = 1;

    // Define la cantidad de docentes que se muestran por página.
    const docentesPorPagina = 5;

    // Obtiene las filas reales y excluye las filas de mensajes.
    const filasDocentes = cuerpoTabla
        ? Array.from(cuerpoTabla.querySelectorAll('tr[data-id-docente]'))
        : [];

    // Obtiene los resultados disponibles para consultar.
    const opcionesConsulta = resultadosConsulta
        ? Array.from(resultadosConsulta.querySelectorAll('[data-resultado-disponibilidad]'))
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

    // Obtiene los docentes que coinciden con la búsqueda y el filtro.
    function obtenerDocentesFiltrados() {
        const texto = normalizarTexto(buscarDisponibilidad?.value);
        const tipoSeleccionado = filtroTipo?.value ?? '';

        return filasDocentes.filter(function (fila) {

            // Obtiene los datos del docente.
            const matricula = normalizarTexto(fila.dataset.matricula);
            const nombre = normalizarTexto(fila.dataset.nombre);
            const id = normalizarTexto(fila.dataset.idDocente);
            const tipo = fila.dataset.tipo;

            // Comprueba si coincide con el texto de búsqueda.
            const coincideBusqueda =
                !texto ||
                matricula.includes(texto) ||
                nombre.includes(texto) ||
                id.includes(texto);

            // Comprueba si coincide con el tipo de docente seleccionado.
            const coincideTipo =
                tipoSeleccionado === '' ||
                tipo === tipoSeleccionado;

            // Devuelve únicamente los docentes que cumplen ambos criterios.
            return coincideBusqueda && coincideTipo;
        });
    }

    // Muestra los docentes correspondientes a la página actual.
    function actualizarTabla() {
        const docentesFiltrados = obtenerDocentesFiltrados();
        const cantidadDocentes = docentesFiltrados.length;

        const totalPaginas = Math.max(
            1,
            Math.ceil(cantidadDocentes / docentesPorPagina)
        );

        // Mantiene la página actual dentro del rango disponible.
        paginaActual = Math.min(paginaActual, totalPaginas);

        // Oculta todas las filas antes de mostrar los resultados de la página.
        filasDocentes.forEach(function (fila) {
            fila.hidden = true;
        });

        // Calcula los límites de los docentes que deben mostrarse.
        const indiceInicial = (paginaActual - 1) * docentesPorPagina;
        const indiceFinal = indiceInicial + docentesPorPagina;

        // Muestra únicamente los docentes correspondientes a la página actual.
        docentesFiltrados
            .slice(indiceInicial, indiceFinal)
            .forEach(function (fila) {
                fila.hidden = false;
            });

        // Actualiza el contador de resultados.
        if (totalResultados) {
            totalResultados.textContent = cantidadDocentes;
        }

        // Muestra los mensajes correspondientes cuando no hay resultados.
        if (filaSinDisponibilidad) {
            filaSinDisponibilidad.hidden = filasDocentes.length > 0;
        }

        if (filaSinResultados) {
            filaSinResultados.hidden =
                filasDocentes.length === 0 || cantidadDocentes > 0;
        }

        // Actualiza la información y el estado de los botones de paginación.
        if (informacionPagina) {
            informacionPagina.textContent =
                `Página ${paginaActual} de ${totalPaginas}`;
        }

        if (paginaAnterior) {
            paginaAnterior.disabled = paginaActual <= 1;
        }

        if (paginaSiguiente) {
            paginaSiguiente.disabled = paginaActual >= totalPaginas;
        }
    }

    // Actualiza la tabla cuando se escribe una búsqueda nueva.
    buscarDisponibilidad?.addEventListener('input', function () {
        paginaActual = 1;
        actualizarTabla();
    });

    // Cambia a la página anterior.
    paginaAnterior?.addEventListener('click', function () {
        if (paginaActual > 1) {
            paginaActual--;
            actualizarTabla();
        }
    });

    // Cambia a la página siguiente.
    paginaSiguiente?.addEventListener('click', function () {
        const totalPaginas = Math.max(
            1,
            Math.ceil(obtenerDocentesFiltrados().length / docentesPorPagina)
        );

        if (paginaActual < totalPaginas) {
            paginaActual++;
            actualizarTabla();
        }
    });

    // Muestra los detalles del docente seleccionado.
    function consultarDisponibilidad(idDocente, botonSeleccionado = null) {
        if (!tarjetaDetalle) return;

        const detalleSeleccionado = tarjetaDetalle.querySelector(
            `[data-detalle-disponibilidad="${idDocente}"]`
        );

        if (!detalleSeleccionado) return;

        // Oculta el mensaje inicial y los detalles anteriores.
        if (detalleVacio) {
            detalleVacio.hidden = true;
        }

        tarjetaDetalle.querySelectorAll('[data-detalle-disponibilidad]').forEach(function (detalle) {
            detalle.hidden = true;
        });

        // Muestra los datos del docente seleccionado.
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

    // Permite consultar un docente desde la tabla.
    document.querySelectorAll('[data-consultar-disponibilidad]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const idDocente = boton.dataset.consultarDisponibilidad;

            // Limpia la búsqueda para mostrar todos los resultados.
            if (buscarConsulta) {
                buscarConsulta.value = '';
            }

            actualizarResultadosConsulta();

            // Encuentra y selecciona el docente correspondiente.
            const resultado = opcionesConsulta.find(function (opcion) {
                return opcion.dataset.resultadoDisponibilidad === idDocente;
            });

            if (resultado) {
                resultado.hidden = false;
                consultarDisponibilidad(idDocente, resultado);
            }

            // Desplaza la pantalla hasta el panel de consulta.
            panelConsulta?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });

    // Filtra los resultados del panel de consulta.
    function actualizarResultadosConsulta() {
        const texto = normalizarTexto(buscarConsulta?.value);
        let cantidadVisible = 0;

        opcionesConsulta.forEach(function (opcion) {

            // Obtiene los datos del docente.
            const matricula = normalizarTexto(opcion.dataset.matriculaConsulta);
            const nombre = normalizarTexto(opcion.dataset.nombreConsulta);

            // Muestra coincidencias por matrícula o nombre.
            const mostrar = texto.length > 0 &&
                (matricula.includes(texto) || nombre.includes(texto));

            opcion.hidden = !mostrar;

            if (mostrar) {
                cantidadVisible++;
            }
        });

        // Oculta los detalles cuando se limpia la búsqueda.
        if (!texto) {
            tarjetaDetalle?.querySelectorAll('[data-detalle-disponibilidad]').forEach(function (detalle) {
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

        // Muestra un aviso cuando la búsqueda no encuentra coincidencias.
        if (sinResultadosConsulta) {
            sinResultadosConsulta.hidden = !texto || cantidadVisible > 0;
        }
    }

    // Actualiza los resultados mientras se escribe una matrícula o nombre.
    buscarConsulta?.addEventListener('input', actualizarResultadosConsulta);

    // Permite consultar un docente desde la lista de resultados.
    opcionesConsulta.forEach(function (opcion) {
        opcion.setAttribute('aria-pressed', 'false');

        opcion.addEventListener('click', function () {
            consultarDisponibilidad(
                opcion.dataset.resultadoDisponibilidad,
                opcion
            );
        });
    });

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

    // Limpia el tipo de docente seleccionado.
    limpiarFiltros?.addEventListener('click', function () {
        if (filtroTipo) {
            filtroTipo.value = '';
        }

        // Regresa a la primera página y actualiza los resultados.
        paginaActual = 1;
        actualizarTabla();
    });

    // Actualiza la tabla cuando cambia el tipo de docente seleccionado.
    filtroTipo?.addEventListener('change', function () {
        paginaActual = 1;
        actualizarTabla();
    });

    // Oculta los resultados de consulta al cargar la página.
    actualizarResultadosConsulta();

    // Aplica la búsqueda y muestra la primera página de la tabla.
    actualizarTabla();

    /* ==========================================
       ELIMINAR CARGA ACADÉMICA
    ========================================== */

    // Obtener los formularios para eliminar la carga académica.
    const formulariosEliminar = document.querySelectorAll(
        '.formulario-eliminar-disponibilidad'
    );

    // Recorrer los formularios registrados.
    formulariosEliminar.forEach(formulario => {

        // Escuchar el envío del formulario.
        formulario.addEventListener('submit', function (evento) {

            // Evitar el envío inmediato.
            evento.preventDefault();

            // Obtener el nombre del docente.
            const nombreDocente = this.dataset.nombreDocente;

            // Mostrar confirmación antes de eliminar.
            const confirmarEliminacion = confirm(
                `¿Estás seguro de eliminar la carga académica de ${nombreDocente}?`
            );

            // Enviar el formulario si se confirma.
            if (confirmarEliminacion) {
                this.submit();
            }
        });
    });
});
