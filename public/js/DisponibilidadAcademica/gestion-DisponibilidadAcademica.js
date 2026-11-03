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

});
