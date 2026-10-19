document.addEventListener('DOMContentLoaded', function () {
    // Obtiene los elementos principales de la tabla.
    const buscarGrupo = document.getElementById('buscar-grupo');
    const cuerpoTabla = document.getElementById('cuerpo-tabla-grupos');
    const totalResultados = document.getElementById('total-resultados');
    const filaSinGrupos = document.getElementById('fila-sin-grupos');
    const filaSinResultados = document.getElementById('fila-sin-resultados');

    // Obtiene los controles de filtros.
    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const filtroCarrera = document.getElementById('filtro-carrera');
    const filtroPeriodo = document.getElementById('filtro-periodo');
    const limpiarFiltros = document.getElementById('limpiar-filtros');

    // Obtiene los controles de paginación.
    const informacionPagina = document.getElementById('informacion-pagina');
    const paginaAnterior = document.getElementById('pagina-anterior');
    const paginaSiguiente = document.getElementById('pagina-siguiente');

    // Obtiene los elementos del formulario.
    const botonNuevoGrupo = document.getElementById('boton-nuevo-grupo');
    const formularioGrupo = document.getElementById('formulario-grupo');
    const formularioAgregarGrupo = document.getElementById('form-agregar-grupo');

    // Obtiene los elementos del panel de consulta.
    const panelConsulta = document.getElementById('panel-consulta');
    const buscarConsulta = document.getElementById('buscar-consulta-grupo');
    const resultadosConsulta = document.getElementById('resultados-consulta-grupo');
    const sinResultadosConsulta = document.getElementById('sin-resultados-consulta-grupo');
    const detalleVacio = document.getElementById('detalle-vacio-grupo');

    // Obtiene los resultados y los detalles disponibles para consultar.
    const opcionesConsulta = resultadosConsulta
        ? Array.from(resultadosConsulta.querySelectorAll('[data-resultado-grupo]'))
        : [];

    const detallesConsulta = panelConsulta
        ? Array.from(panelConsulta.querySelectorAll('[data-detalle-grupo]'))
        : [];

    // Obtiene el contenedor de los detalles del grupo.
    const tarjetaDetalle = panelConsulta?.querySelector('.contenido-detalle-grupo');

    // Obtiene los modales definidos en el Blade.
    const modalBajaGrupo = document.getElementById('modal-baja-grupo');
    const modalRegistroGrupo = document.getElementById('modal-registro-grupo');
    const modalErrorGrupo = document.getElementById('modal-error-grupo');
    const modalModificarGrupo = document.getElementById('modal-modificar-grupo');

    // Agrupa los modales utilizados para mostrar mensajes.
    const modalesMensaje = [ modalRegistroGrupo, modalErrorGrupo, modalModificarGrupo].filter(Boolean);

    // Obtiene el formulario y el nombre del grupo que se dará de baja.
    const formularioBajaGrupo = document.getElementById('formulario-baja-grupo');
    const nombreGrupoBaja = document.getElementById('nombre-grupo-baja');

    // Obtiene las filas reales de la tabla.
    const filasGrupos = cuerpoTabla
        ? Array.from(cuerpoTabla.querySelectorAll('tr[data-id-grupo]'))
        : [];

    // Guarda la página actual.
    let paginaActual = 1;
    // Define la cantidad de registros por página.
    const gruposPorPagina = 5;

    // Normaliza los textos 
    function normalizarTexto(texto) {
        return (texto || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    // Obtiene los grupos de la búsqueda y los filtros.
    function obtenerGruposFiltrados() {
        const texto = normalizarTexto(buscarGrupo?.value);
        const carreraSeleccionada = filtroCarrera?.value ?? '';
        const periodoSeleccionado = filtroPeriodo?.value ?? '';

        return filasGrupos.filter(function (fila) {
            const nombre = normalizarTexto(fila.dataset.nombre);
            const claveCarrera = normalizarTexto(fila.dataset.claveCarrera);
            const nombreCarrera = normalizarTexto(fila.dataset.nombreCarrera);
            const cuatrimestre = normalizarTexto(fila.dataset.cuatrimestre);

            const periodo = normalizarTexto(
                `${fila.dataset.estacion || ''} ${fila.dataset.anio || ''}`
            );

            // Comprueba si el grupo coincide 
            const coincideBusqueda =
                !texto ||
                nombre.includes(texto) ||
                claveCarrera.includes(texto) ||
                nombreCarrera.includes(texto) ||
                cuatrimestre.includes(texto) ||
                periodo.includes(texto);

            // Comprueba si el grupo pertenece a la carrera seleccionada.
            const coincideCarrera = !carreraSeleccionada || fila.dataset.idCarrera === carreraSeleccionada;

            // Comprueba si el grupo pertenece al periodo seleccionado.
            const coincidePeriodo = !periodoSeleccionado || fila.dataset.idPeriodo === periodoSeleccionado;

            return coincideBusqueda && coincideCarrera && coincidePeriodo;
        });
    }

    // Actualiza las filas, los mensajes y la paginación de la tabla.
    function actualizarTabla() {
        const gruposFiltrados = obtenerGruposFiltrados();
        const cantidadGrupos = gruposFiltrados.length;

        const totalPaginas = Math.max( 1, Math.ceil(cantidadGrupos / gruposPorPagina) );

        // Mantiene la página actual dentro del rango
        paginaActual = Math.min(paginaActual, totalPaginas);

        // Oculta todas las filas antes de mostrar los resultados.
        filasGrupos.forEach(function (fila) { fila.hidden = true; });

        // Calcula los grupos que corresponden a la página actual.
        const indiceInicial = (paginaActual - 1) * gruposPorPagina;
        const indiceFinal = indiceInicial + gruposPorPagina;

        gruposFiltrados
            .slice(indiceInicial, indiceFinal)
            .forEach(function (fila) {
                fila.hidden = false;
            });

        // Actualiza el contador de resultados.
        if (totalResultados) {
            totalResultados.textContent = cantidadGrupos;
        }

        // Muestra el mensaje cuando no existen grupos registrados.
        if (filaSinGrupos) {
            filaSinGrupos.hidden = filasGrupos.length > 0;
        }

        // Muestra el mensaje cuando la búsqueda no obtiene coincidencias.
        if (filaSinResultados) {
            filaSinResultados.hidden =
                filasGrupos.length === 0 || cantidadGrupos > 0;
        }

        // Actualiza la información de la página actual.
        if (informacionPagina) {
            informacionPagina.textContent =
                `Página ${paginaActual} de ${totalPaginas}`;
        }

        // Deshabilita los botones cuando no hay más páginas disponibles.
        if (paginaAnterior) {
            paginaAnterior.disabled = paginaActual <= 1;
        }

        if (paginaSiguiente) {
            paginaSiguiente.disabled = paginaActual >= totalPaginas;
        }
    }

    // Actualiza la tabla cuando cambia el texto de búsqueda.
    buscarGrupo?.addEventListener('input', function () {
        paginaActual = 1;
        actualizarTabla();
    });

    // Actualiza la tabla cuando cambia el filtro de carrera.
    filtroCarrera?.addEventListener('change', function () {
        paginaActual = 1;
        actualizarTabla();
    });

    // Actualiza la tabla cuando cambia el filtro de periodo.
    filtroPeriodo?.addEventListener('change', function () {
        paginaActual = 1;
        actualizarTabla();
    });

    // Muestra la página anterior.
    paginaAnterior?.addEventListener('click', function () {
        if (paginaActual > 1) {
            paginaActual--;
            actualizarTabla();
        }
    });

    // Muestra la página siguiente.
    paginaSiguiente?.addEventListener('click', function () {
        const totalPaginas = Math.max( 1, Math.ceil(obtenerGruposFiltrados().length / gruposPorPagina) );

        if (paginaActual < totalPaginas) {
            paginaActual++;
            actualizarTabla();
        }
    });

    // Abre o cierra el panel de filtros.
    botonFiltros?.addEventListener('click', function () {
        if (!contenedorFiltros) {
            return;
        }

        contenedorFiltros.hidden = !contenedorFiltros.hidden;

        botonFiltros.setAttribute(
            'aria-expanded',
            String(!contenedorFiltros.hidden)
        );

        botonFiltros.classList.toggle(
            'boton-activo',
            !contenedorFiltros.hidden
        );
    });

    // Limpia los filtros y restablece la tabla.
    limpiarFiltros?.addEventListener('click', function () {
        if (buscarGrupo) {
            buscarGrupo.value = '';
        }

        if (filtroCarrera) {
            filtroCarrera.value = '';
        }

        if (filtroPeriodo) {
            filtroPeriodo.value = '';
        }

        paginaActual = 1;
        actualizarTabla();
    });

    // Abre el formulario de registro.
    function abrirFormularioGrupo() {
        if (!formularioGrupo) {
            return;
        }

        formularioGrupo.hidden = false;

        formularioGrupo.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        document.getElementById('id_carrera')?.focus({
            preventScroll: true
        });
    }

    botonNuevoGrupo?.addEventListener('click', abrirFormularioGrupo);

    // Cierra el formulario cuando se cancela el registro.
    document.querySelectorAll('[data-cerrar-formulario]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            if (formularioGrupo) {
                formularioGrupo.hidden = true;
            }
        });
    });

    // Restablece los campos del formulario al cancelar el registro.
    formularioAgregarGrupo?.addEventListener('reset', function () {
        window.setTimeout(function () {
            formularioAgregarGrupo.querySelectorAll('select').forEach(function (campo) {
                campo.value = '';
            });
        }, 0);
    });

    // Muestra los detalles del grupo seleccionado.
    function consultarGrupo(idGrupo, opcionSeleccionada = null) {
        if (!panelConsulta) {
            return;
        }

        // Busca los detalles correspondientes al grupo seleccionado.
        const detalleSeleccionado = detallesConsulta.find(function (detalle) {
            return detalle.dataset.detalleGrupo === String(idGrupo);
        });

        if (!detalleSeleccionado) {
            return;
        }

        // Oculta el mensaje que indica que no hay un grupo seleccionado.
        if (detalleVacio) {
            detalleVacio.hidden = true;
        }

        // Oculta los detalles de los demás grupos.
        detallesConsulta.forEach(function (detalle) {
            detalle.hidden = true;
        });

        // Muestra los detalles del grupo seleccionado.
        detalleSeleccionado.hidden = false;

        // Actualiza la selección visual de los resultados.
        opcionesConsulta.forEach(function (opcion) {
            opcion.classList.remove('seleccionado');
            opcion.setAttribute('aria-pressed', 'false');
        });

        if (opcionSeleccionada) {
            opcionSeleccionada.classList.add('seleccionado');
            opcionSeleccionada.setAttribute('aria-pressed', 'true');
        }

        // Muestra el panel de consulta.
        panelConsulta.hidden = false;
    }

    // Filtra los resultados del panel de consulta.
    function actualizarResultadosConsulta() {
        const texto = normalizarTexto(buscarConsulta?.value);
        let cantidadVisible = 0;

        // Muestra únicamente los resultados que coinciden con la búsqueda.
        opcionesConsulta.forEach(function (opcion) {
            const nombre = normalizarTexto(opcion.dataset.nombreConsulta);
            const clave = normalizarTexto(opcion.dataset.claveConsulta);

            const mostrar = texto.length > 0 && (nombre.includes(texto) || clave.includes(texto));

            opcion.hidden = !mostrar;

            if (mostrar) {
                cantidadVisible++;
            }
        });

        // Restablece los detalles cuando se limpia la búsqueda.
        if (!texto) {
            detallesConsulta.forEach(function (detalle) {
                detalle.hidden = true;
            });

            opcionesConsulta.forEach(function (opcion) {
                opcion.classList.remove('seleccionado');
                opcion.setAttribute('aria-pressed', 'false');
            });

            if (detalleVacio) {
                detalleVacio.hidden = false;
            }
        }

        // Muestra un aviso cuando no existen coincidencias.
        if (sinResultadosConsulta) {
            sinResultadosConsulta.hidden =
                !texto || cantidadVisible > 0;
        }
    }

    buscarConsulta?.addEventListener('input', actualizarResultadosConsulta);

    // Permite seleccionar un grupo en los resultados de consulta.
    opcionesConsulta.forEach(function (opcion) {
        opcion.setAttribute('aria-pressed', 'false');

        opcion.addEventListener('click', function () {
            consultarGrupo(opcion.dataset.resultadoGrupo, opcion);
        });
    });

    // Abre la consulta de un grupo desde la tabla principal.
    document.querySelectorAll('[data-consultar-grupo]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const idGrupo = boton.dataset.consultarGrupo;

            if (!panelConsulta || !buscarConsulta) {
                return;
            }

            // Limpia la búsqueda anterior.
            buscarConsulta.value = '';

            // Restablece los resultados de consulta.
            actualizarResultadosConsulta();

            // Busca el resultado correspondiente al grupo seleccionado.
            const resultado = opcionesConsulta.find(function (opcion) {
                return opcion.dataset.resultadoGrupo === String(idGrupo);
            });

            if (resultado) {
                resultado.hidden = false;
                consultarGrupo(idGrupo, resultado);
            }

            // Muestra el panel y desplaza la pantalla hasta él.
            panelConsulta.hidden = false;

            panelConsulta.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });

    // Abre el modal de confirmación para dar de baja un grupo.
    function abrirModalBajaGrupo(idGrupo, nombreGrupo) {
        if (!modalBajaGrupo || !formularioBajaGrupo || !window.rutaGrupos) {
            return;
        }

        // Muestra el nombre del grupo seleccionado.
        if (nombreGrupoBaja) {
            nombreGrupoBaja.textContent = nombreGrupo || 'Grupo seleccionado';
        }

        // Configura la ruta para enviar la solicitud de eliminación.
        formularioBajaGrupo.action =
            `${window.rutaGrupos}/${encodeURIComponent(idGrupo)}`;

        modalBajaGrupo.hidden = false;
        modalBajaGrupo.setAttribute('aria-hidden', 'false');

        // Coloca el foco en el botón para cerrar el modal.
        modalBajaGrupo.querySelector('[data-cerrar-baja-grupo]')?.focus();
    }

    // Cierra el modal de baja de grupo.
    function cerrarModalBajaGrupo() {
        if (!modalBajaGrupo) {
            return;
        }

        modalBajaGrupo.hidden = true;
        modalBajaGrupo.setAttribute('aria-hidden', 'true');
    }

    // Asigna el evento de apertura a los botones de baja.
    document.querySelectorAll('[data-baja-grupo]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            abrirModalBajaGrupo(
                boton.dataset.bajaGrupo,
                boton.dataset.nombreGrupo
            );
        });
    });

    // Cierra el modal de baja desde sus botones de cancelación.
    document.querySelectorAll('[data-cerrar-baja-grupo]').forEach(function (boton) {
        boton.addEventListener('click', cerrarModalBajaGrupo);
    });

    // Cierra el modal de baja al hacer clic fuera de su contenido.
    modalBajaGrupo?.addEventListener('click', function (evento) {
        if (evento.target === modalBajaGrupo) {
            cerrarModalBajaGrupo();
        }
    });

    // Cierra un modal de mensajes.
    function cerrarModal(modal) {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
    }

    // Relaciona cada botón de aceptación con su modal correspondiente.
    const botonesCerrarModales = [
        {
            boton: 'aceptar-modal-registro-grupo',
            modal: modalRegistroGrupo
        },
        {
            boton: 'cerrar-modal-registro-grupo',
            modal: modalRegistroGrupo
        },
        {
            boton: 'aceptar-modal-error-grupo',
            modal: modalErrorGrupo
        },
        {
            boton: 'cerrar-modal-error-grupo',
            modal: modalErrorGrupo
        },
        {
            boton: 'aceptar-modal-modificar-grupo',
            modal: modalModificarGrupo
        },
        {
            boton: 'cerrar-modal-modificar-grupo',
            modal: modalModificarGrupo
        }
    ];

    // Asigna los eventos para cerrar los modales de mensajes.
    botonesCerrarModales.forEach(function (elemento) {
        document.getElementById(elemento.boton)?.addEventListener('click', function () {
            cerrarModal(elemento.modal);
        });
    });

    // Cierra los modales de mensajes al hacer clic fuera del contenido.
    modalesMensaje.forEach(function (modal) {
        modal.addEventListener('click', function (evento) {
            if (evento.target === modal) {
                cerrarModal(modal);
            }
        });
    });

    // Permite cerrar los modales con la tecla Escape.
    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarModalBajaGrupo();

            modalesMensaje.forEach(function (modal) {
                cerrarModal(modal);
            });
        }
    });

    // Abre el formulario cuando Laravel devuelve errores de validación.
    if (
        formularioGrupo &&
        document.getElementById('mensaje-errores-grupo')
    ) {
        formularioGrupo.hidden = false;

        formularioGrupo.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    // Inicializa los resultados de consulta y la tabla.
    actualizarResultadosConsulta();
    actualizarTabla();
});
