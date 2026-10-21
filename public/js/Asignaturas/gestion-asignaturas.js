
document.addEventListener('DOMContentLoaded', function () {

    /*  ELEMENTOS DE LA TABLA */

    const cuerpoTabla = document.getElementById('cuerpo-tabla-asignaturas');
    const buscarAsignatura = document.getElementById('buscar-asignatura');
    const totalResultados = document.getElementById('total-resultados');
    const filaSinAsignaturas = document.getElementById('fila-sin-asignaturas');
    const filaSinResultados = document.getElementById('fila-sin-resultados');
    const informacionPagina = document.getElementById('informacion-pagina');
    const botonPaginaAnterior = document.getElementById('pagina-anterior');
    const botonPaginaSiguiente = document.getElementById('pagina-siguiente');

    // Obtener las asignaturas registradas en la tabla.
    const filasAsignaturas = cuerpoTabla
        ? Array.from(cuerpoTabla.querySelectorAll('tr[data-id-asignatura]'))
        : [];

    // Configurar la cantidad de registros por página.
    let paginaActual = 1;
    const asignaturasPorPagina = 5;

    /*  ELEMENTOS DE LOS FILTROS */

    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const limpiarFiltros = document.getElementById('limpiar-filtros');

    const filtroCarrera = document.getElementById('filtro-carrera');
    const filtroCuatrimestre = document.getElementById('filtro-cuatrimestre');
    const filtroEstado = document.getElementById('filtro-estado');

    /*  ELEMENTOS DEL FORMULARIO DE REGISTRO */

    const botonNuevaAsignatura = document.getElementById('boton-nueva-asignatura');
    const formularioAsignatura = document.getElementById('formulario-asignatura');
    const formularioRegistro = document.getElementById('form-agregar-asignatura');
    const campoNombreAsignatura = document.getElementById('nombreAsignatura');

    /*   ELEMENTOS DEL PANEL DE CONSULTA */

    const panelConsulta = document.getElementById('panel-consulta');
    const buscarConsulta = document.getElementById('buscar-consulta-asignatura');
    const resultadosConsulta = document.getElementById('resultados-consulta-asignatura');
    const mensajeSinResultadosConsulta = document.getElementById(
        'sin-resultados-consulta-asignatura'
    );

    const tarjetaDetalle = document.getElementById('tarjeta-detalle-asignatura');
    const detalleVacio = document.getElementById('detalle-vacio-asignatura');

    const resultadosAsignaturas = resultadosConsulta
        ? Array.from(resultadosConsulta.querySelectorAll('[data-resultado-asignatura]'))
        : [];

    const detallesAsignaturas = tarjetaDetalle
        ? Array.from(tarjetaDetalle.querySelectorAll('[data-detalle-asignatura]'))
        : [];

    /*  ELEMENTOS DE LOS MODALES */

    const modalBaja = document.getElementById('modal-baja-asignatura');
    const formularioBaja = document.getElementById('formulario-baja-asignatura');
    const nombreAsignaturaBaja = document.getElementById('nombre-asignatura-baja');

    const modalRegistro = document.getElementById('modal-registro-asignatura');
    const cerrarModalRegistro = document.getElementById('cerrar-modal-registro-asignatura');
    const aceptarModalRegistro = document.getElementById('aceptar-modal-registro-asignatura');

    const modalError = document.getElementById('modal-error-asignatura');
    const cerrarModalError = document.getElementById('cerrar-modal-error-asignatura');
    const aceptarModalError = document.getElementById('aceptar-modal-error-asignatura');


    /*   NORMALIZAR TEXTO PARA LAS BÚSQUEDAS */

    // Eliminar acentos y convertir el texto a minúsculas.
    function normalizarTexto(texto) {
        return (texto || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    /*   OBTENER ASIGNATURAS FILTRADAS */

    // Aplicar la búsqueda y los filtros seleccionados.
    function obtenerAsignaturasFiltradas() {
        const textoBusqueda = normalizarTexto(buscarAsignatura?.value);
        const carreraSeleccionada = filtroCarrera?.value || '';
        const cuatrimestreSeleccionado = filtroCuatrimestre?.value || '';
        const estadoSeleccionado = filtroEstado?.value || '';

        return filasAsignaturas.filter(function (fila) {
            const clave = normalizarTexto(fila.dataset.clave);
            const nombre = normalizarTexto(fila.dataset.nombre);
            const idAsignatura = normalizarTexto(fila.dataset.idAsignatura);
            const carrera = fila.dataset.carrera || '';
            const cuatrimestre = fila.dataset.cuatrimestre || '';
            const estado = fila.dataset.estado || '';

            const coincideBusqueda = clave.includes(textoBusqueda) || nombre.includes(textoBusqueda) || idAsignatura.includes(textoBusqueda);

            const coincideCarrera = carreraSeleccionada === '' || carrera === carreraSeleccionada;

            const coincideCuatrimestre = cuatrimestreSeleccionado === '' || cuatrimestre === cuatrimestreSeleccionado;

            const coincideEstado = estadoSeleccionado === '' || estado === estadoSeleccionado;

            return ( coincideBusqueda && coincideCarrera && coincideCuatrimestre && coincideEstado );
        });
    }

    /*   ACTUALIZAR LA TABLA Y LA PAGINACIÓN */

    // Mostrar los registros correspondientes a la página actual.
    function actualizarTabla() {
        const asignaturasFiltradas = obtenerAsignaturasFiltradas();
        const total = asignaturasFiltradas.length;

        const totalPaginas = Math.max( 1, Math.ceil(total / asignaturasPorPagina) );

        // Mantener la página actual dentro del rango disponible.
        if (paginaActual > totalPaginas) {
            paginaActual = totalPaginas;
        }

        const inicio = (paginaActual - 1) * asignaturasPorPagina;
        const fin = inicio + asignaturasPorPagina;
        const asignaturasVisibles = asignaturasFiltradas.slice(inicio, fin);

        // Ocultar todas las filas antes de mostrar la página seleccionada.
        filasAsignaturas.forEach(function (fila) {
            fila.hidden = true;
        });

        // Mostrar únicamente las asignaturas de la página actual.
        asignaturasVisibles.forEach(function (fila) {
            fila.hidden = false;
        });

        // Actualizar el contador de resultados.
        if (totalResultados) {
            totalResultados.textContent = total;
        }

        // Mostrar el mensaje correspondiente cuando no hay registros.
        if (filaSinAsignaturas) {
            filaSinAsignaturas.hidden = filasAsignaturas.length !== 0;
        }

        if (filaSinResultados) {
            filaSinResultados.hidden = !(
                filasAsignaturas.length > 0 && total === 0
            );
        }

        // Actualizar la información de la paginación.
        if (informacionPagina) {
            informacionPagina.textContent =
                `Página ${paginaActual} de ${totalPaginas}`;
        }

        // Habilitar o deshabilitar los botones de paginación.
        if (botonPaginaAnterior) {
            botonPaginaAnterior.disabled =
                paginaActual === 1 || total === 0;
        }

        if (botonPaginaSiguiente) {
            botonPaginaSiguiente.disabled =
                paginaActual === totalPaginas || total === 0;
        }
    }

    /*  EVENTOS DE BÚSQUEDA Y FILTROS */

    // Actualizar la tabla cuando cambia el texto de búsqueda.
    if (buscarAsignatura) {
        buscarAsignatura.addEventListener('input', function () {
            paginaActual = 1;
            actualizarTabla();
        });
    }

    // Actualizar la tabla al cambiar cualquiera de los filtros.
    [filtroCarrera, filtroCuatrimestre, filtroEstado].forEach(function (filtro) {
        if (filtro) {
            filtro.addEventListener('change', function () {
                paginaActual = 1;
                actualizarTabla();
            });
        }
    });


    // Mostrar u ocultar el panel de filtros.
    if (botonFiltros && contenedorFiltros) {
        botonFiltros.addEventListener('click', function () {
            // Comprobar si el panel de filtros está cerrado.
            const filtrosOcultos = contenedorFiltros.hidden;
            contenedorFiltros.hidden = !filtrosOcultos;

            // Mantener el botón morado mientras los filtros estén abiertos.
            botonFiltros.classList.toggle('boton-activo', filtrosOcultos);
            botonFiltros.setAttribute( 'aria-expanded', String(filtrosOcultos));
        });
    }


    // Restablecer la búsqueda y los filtros.
    if (limpiarFiltros) {
        limpiarFiltros.addEventListener('click', function () {
            if (buscarAsignatura) {
                buscarAsignatura.value = '';
            }

            if (filtroCarrera) {
                filtroCarrera.value = '';
            }

            if (filtroCuatrimestre) {
                filtroCuatrimestre.value = '';
            }

            if (filtroEstado) {
                filtroEstado.value = '';
            }

            paginaActual = 1;
            actualizarTabla();
        });
    }

    /*   EVENTOS DE PAGINACIÓN */

    // Mostrar la página anterior.
    if (botonPaginaAnterior) {
        botonPaginaAnterior.addEventListener('click', function () {
            if (paginaActual > 1) {
                paginaActual--;
                actualizarTabla();
            }
        });
    }

    // Mostrar la página siguiente.
    if (botonPaginaSiguiente) {
        botonPaginaSiguiente.addEventListener('click', function () {
            const totalPaginas = Math.ceil( obtenerAsignaturasFiltradas().length / asignaturasPorPagina );

            if (paginaActual < totalPaginas) {
                paginaActual++;
                actualizarTabla();
            }
        });
    }

    /*  FORMULARIO DE REGISTRO */

    // Desplazarse al formulario y enfocar el nombre de la asignatura.
    if (botonNuevaAsignatura && formularioAsignatura) {
        botonNuevaAsignatura.addEventListener('click', function () {
            formularioAsignatura.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            if (campoNombreAsignatura) {
                campoNombreAsignatura.focus({ preventScroll: true });
            }
        });
    }

    // Permitir cerrar el formulario si la vista incluye un botón de cierre.
    document.querySelectorAll('[data-cerrar-formulario]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            if (formularioAsignatura) {
                formularioAsignatura.hidden = true;
            }
        });
    });

    /*  CONSULTA DE ASIGNATURAS */

    // Mostrar el detalle de una asignatura seleccionada.
    function consultarAsignatura(idAsignatura) {
        let asignaturaEncontrada = false;

        detallesAsignaturas.forEach(function (detalle) {
            const coincide = detalle.dataset.detalleAsignatura === String(idAsignatura);
            detalle.hidden = !coincide;

            if (coincide) {
                asignaturaEncontrada = true;
            }
        });

        if (detalleVacio) {
            detalleVacio.hidden = asignaturaEncontrada;
        }

        if (tarjetaDetalle && asignaturaEncontrada) {
            tarjetaDetalle.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }

    // Filtrar los resultados del panel de consulta.
    function actualizarResultadosConsulta() {
        if (!buscarConsulta || !resultadosConsulta) {
            return;
        }

        const textoBusqueda = normalizarTexto(buscarConsulta.value);
        let coincidencias = 0;

        resultadosAsignaturas.forEach(function (resultado) {
            const clave = normalizarTexto(resultado.dataset.claveConsulta);
            const nombre = normalizarTexto(resultado.dataset.nombreConsulta);

            const coincide = textoBusqueda !== '' && (clave.includes(textoBusqueda) || nombre.includes(textoBusqueda));
            resultado.hidden = !coincide;

            if (coincide) {
                coincidencias++;
            }
        });

        // Mostrar el mensaje cuando la búsqueda no devuelve resultados.
        if (mensajeSinResultadosConsulta) {
            mensajeSinResultadosConsulta.hidden = !(
                textoBusqueda !== '' && coincidencias === 0
            );
        }

        // Ocultar el mensaje inicial de lista vacía mientras se busca.
        const mensajeListaVacia = resultadosConsulta.querySelector( '.mensaje-sin-resultados:not(#sin-resultados-consulta-asignatura)' );

        if (mensajeListaVacia) {
            mensajeListaVacia.hidden = textoBusqueda !== '' || resultadosAsignaturas.length > 0;
        }
    }

    // Actualizar los resultados al escribir en el buscador de consulta.
    if (buscarConsulta) {
        buscarConsulta.addEventListener('input', actualizarResultadosConsulta);
    }

    // Mostrar los detalles al seleccionar un resultado de consulta.
    if (resultadosConsulta) {
        resultadosConsulta.addEventListener('click', function (evento) {
            const resultado = evento.target.closest('[data-resultado-asignatura]');

            if (!resultado) {
                return;
            }

            consultarAsignatura(resultado.dataset.resultadoAsignatura);
        });
    }

    // Abrir la consulta desde el botón de una fila de la tabla.
    if (cuerpoTabla) {
        cuerpoTabla.addEventListener('click', function (evento) {
            const botonConsulta = evento.target.closest('[data-consultar-asignatura]');

            if (!botonConsulta) {
                return;
            }

            const idAsignatura = botonConsulta.dataset.consultarAsignatura;

            if (panelConsulta) {
                panelConsulta.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (buscarConsulta) {
                buscarConsulta.value = '';
            }

            actualizarResultadosConsulta();
            consultarAsignatura(idAsignatura);
        });
    }


    /*  MODAL PARA ELIMINAR UNA ASIGNATURA */

    // Abrir el modal y preparar el formulario de eliminación.
    function abrirModalBajaAsignatura(idAsignatura, nombreAsignatura) {
        if (!modalBaja || !formularioBaja) {
            console.error('No se encontró el modal o el formulario de eliminación.');
            return;
        }

        if (nombreAsignaturaBaja) {
            nombreAsignaturaBaja.textContent = nombreAsignatura;
        }

        // Preparar la ruta para eliminar la asignatura seleccionada.
        formularioBaja.action = `${window.rutaAsignaturas}/${encodeURIComponent(idAsignatura)}`;

        // Mostrar el modal.
        modalBaja.hidden = false;
        modalBaja.removeAttribute('hidden');
    }

    // Cerrar el modal de eliminación.
    function cerrarModalBajaAsignatura() {
        if (modalBaja) {
            modalBaja.hidden = true;
        }
    }

    // Permitir que los botones del HTML cierren el modal.
    window.cerrarModalBajaAsignatura = cerrarModalBajaAsignatura;

    // Detectar el clic en el botón de eliminar de la tabla.
    if (cuerpoTabla) {
        cuerpoTabla.addEventListener('click', function (evento) {
            const botonBaja = evento.target.closest('[data-baja-asignatura]');

            if (!botonBaja) {
                return;
            }

            
            abrirModalBajaAsignatura(
                botonBaja.dataset.bajaAsignatura,
                botonBaja.dataset.nombreAsignatura || ''
            );
        });
    }

    // Cerrar el modal al hacer clic fuera de su contenido.
    if (modalBaja) {
        modalBaja.addEventListener('click', function (evento) {
            if (evento.target === modalBaja) {
                cerrarModalBajaAsignatura();
            }
        });
    }

    /*   MODAL DE REGISTRO EXITOSO */

    // Cerrar el modal de operación exitosa.
    function cerrarModalRegistroAsignatura() {
        if (modalRegistro) {
            modalRegistro.hidden = true;
        }
    }

    if (cerrarModalRegistro) {
        cerrarModalRegistro.addEventListener(
            'click',
            cerrarModalRegistroAsignatura
        );
    }

    if (aceptarModalRegistro) {
        aceptarModalRegistro.addEventListener(
            'click',
            cerrarModalRegistroAsignatura
        );
    }

    // Cerrar el modal exitoso al hacer clic fuera del contenido.
    if (modalRegistro) {
        modalRegistro.addEventListener('click', function (evento) {
            if (evento.target === modalRegistro) {
                cerrarModalRegistroAsignatura();
            }
        });
    }

    /*  MODAL DE ERROR */

    // Cerrar el modal de error.
    function cerrarModalErrorAsignatura() {
        if (modalError) {
            modalError.hidden = true;
        }
    }

    if (cerrarModalError) {
        cerrarModalError.addEventListener(
            'click',
            cerrarModalErrorAsignatura
        );
    }

    if (aceptarModalError) {
        aceptarModalError.addEventListener(
            'click',
            cerrarModalErrorAsignatura
        );
    }

    // Cerrar el modal de error al hacer clic fuera del contenido.
    if (modalError) {
        modalError.addEventListener('click', function (evento) {
            if (evento.target === modalError) {
                cerrarModalErrorAsignatura();
            }
        });
    }

    /*   CERRAR MODALES CON LA TECLA ESCAPE  */

    document.addEventListener('keydown', function (evento) {
        if (evento.key !== 'Escape') {
            return;
        }

        cerrarModalBajaAsignatura();
        cerrarModalRegistroAsignatura();
        cerrarModalErrorAsignatura();
    });

    /*  INICIALIZAR LA VISTA */

    actualizarTabla();
    actualizarResultadosConsulta();

});
