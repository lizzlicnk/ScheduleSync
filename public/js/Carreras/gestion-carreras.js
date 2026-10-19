document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos principales de la tabla.
    const buscarCarrera = document.getElementById('buscar-carrera');
    const cuerpoTabla = document.getElementById('cuerpo-tabla-carreras');
    const totalResultados = document.getElementById('total-resultados');
    const filaSinCarreras = document.getElementById('fila-sin-carreras');
    const filaSinResultados = document.getElementById('fila-sin-resultados');

    // Obtiene los elementos del panel de filtros.
    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const limpiarFiltros = document.getElementById('limpiar-filtros');
    const filtroEstado = document.getElementById('filtro-estado');

    // Obtiene los controles de paginación.
    const informacionPagina = document.getElementById('informacion-pagina');
    const paginaAnterior = document.getElementById('pagina-anterior');
    const paginaSiguiente = document.getElementById('pagina-siguiente');

    // Obtiene los elementos del formulario de registro.
    const botonNuevaCarrera = document.getElementById('boton-nueva-carrera');
    const formularioCarrera = document.getElementById('formulario-carrera');
    const formularioAgregarCarrera = document.getElementById('form-agregar-carrera');

    // Obtiene los elementos del panel de consulta.
    const panelConsulta = document.getElementById('panel-consulta');
    const buscarConsulta = document.getElementById('buscar-consulta-carrera');
    const resultadosConsulta = document.getElementById('resultados-consulta-carrera');
    const sinResultadosConsulta = document.getElementById('sin-resultados-consulta-carrera');
    const tarjetaDetalle = document.getElementById('tarjeta-detalle-carrera');
    const detalleVacio = document.getElementById('detalle-vacio-carrera');

    // Obtiene los elementos de los modales.
    const modalBajaCarrera = document.getElementById('modal-baja-carrera');
    const modalActivarCarrera = document.getElementById('modal-activar-carrera');
    const modalConfirmacionCarrera = document.getElementById('modal-confirmacion-carrera');

    // Obtener los elementos del modal de registro exitoso.
    const modalRegistroCarrera = document.getElementById('modal-registro-carrera');
    const botonCerrarRegistroCarrera = document.getElementById('cerrar-modal-registro-carrera');
    const botonAceptarRegistroCarrera = document.getElementById('aceptar-modal-registro-carrera');

    // Obtener los elementos del modal de error de carrera.
    const modalErrorCarrera = document.getElementById('modal-error-carrera');
    const botonCerrarErrorCarrera = document.getElementById('cerrar-modal-error-carrera');
    const botonAceptarErrorCarrera = document.getElementById('aceptar-modal-error-carrera');

    // Guarda la página actual de la tabla.
    let paginaActual = 1;

    // Define la cantidad de carreras que se muestran por página.
    const carrerasPorPagina = 5;

    // Obtiene las filas reales y excluye las filas de mensajes.
    const filasCarreras = cuerpoTabla
        ? Array.from(cuerpoTabla.querySelectorAll('tr[data-id-carrera]'))
        : [];

    // Obtiene los resultados disponibles para consultar.
    const opcionesConsulta = resultadosConsulta
        ? Array.from(resultadosConsulta.querySelectorAll('[data-resultado-carrera]'))
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

    // Obtiene las carreras que coinciden con el texto de búsqueda.
    function obtenerCarrerasFiltradas() {
        const texto = normalizarTexto(buscarCarrera?.value);
        const estadoSeleccionado = filtroEstado?.value ?? '';

        return filasCarreras.filter(function (fila) {
            // Obtiene los datos de la carrera.
            const clave = normalizarTexto(fila.dataset.clave);
            const nombre = normalizarTexto(fila.dataset.nombre);
            const id = normalizarTexto(fila.dataset.idCarrera);
            const estado = fila.dataset.estado;

            // Comprueba si coincide con el texto de búsqueda.
            const coincideBusqueda =
                !texto ||
                clave.includes(texto) ||
                nombre.includes(texto) ||
                id.includes(texto);

            // Comprueba si coincide con el estado seleccionado.
            const coincideEstado =
                estadoSeleccionado === '' ||
                estado === estadoSeleccionado;

            // Devuelve únicamente las carreras que cumplen ambos criterios.
            return coincideBusqueda && coincideEstado;
        });
    }

    // Muestra las carreras correspondientes a la página actual.
    function actualizarTabla() {
        const carrerasFiltradas = obtenerCarrerasFiltradas();
        const cantidadCarreras = carrerasFiltradas.length;

        const totalPaginas = Math.max( 1, Math.ceil(cantidadCarreras / carrerasPorPagina) );

        // Mantiene la página actual dentro del rango disponible.
        paginaActual = Math.min(paginaActual, totalPaginas);

        // Oculta todas las filas antes de mostrar los resultados de la página.
        filasCarreras.forEach(function (fila) {
            fila.hidden = true;
        });

        // Calcula los límites de las carreras que deben mostrarse.
        const indiceInicial = (paginaActual - 1) * carrerasPorPagina;
        const indiceFinal = indiceInicial + carrerasPorPagina;

        // Muestra únicamente las carreras correspondientes a la página actual.
        carrerasFiltradas
            .slice(indiceInicial, indiceFinal)
            .forEach(function (fila) {
                fila.hidden = false;
            });

        // Actualiza el contador de resultados.
        if (totalResultados) {
            totalResultados.textContent = cantidadCarreras;
        }

        // Muestra los mensajes correspondientes cuando no hay resultados.
        if (filaSinCarreras) {
            filaSinCarreras.hidden = filasCarreras.length > 0;
        }

        if (filaSinResultados) {
            filaSinResultados.hidden =
                filasCarreras.length === 0 || cantidadCarreras > 0;
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
    buscarCarrera?.addEventListener('input', function () {
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
        const totalPaginas = Math.max( 1, Math.ceil(obtenerCarrerasFiltradas().length / carrerasPorPagina));

        if (paginaActual < totalPaginas) {
            paginaActual++;
            actualizarTabla();
        }
    });

    // Abre el formulario para registrar una nueva carrera.
    function abrirFormularioCarrera() {
        if (!formularioCarrera) return;

        formularioCarrera.hidden = false;

        formularioCarrera.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        document.getElementById('claveCarrera')?.focus({
            preventScroll: true
        });
    }

    // Abre el formulario desde el botón Nueva carrera.
    botonNuevaCarrera?.addEventListener('click', abrirFormularioCarrera);

    // Cierra el formulario y limpia sus campos.
    document.querySelectorAll('[data-cerrar-formulario]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            if (formularioCarrera) {
                formularioCarrera.hidden = true;
            }
        });
    });

    // Limpia el formulario cuando se presiona el botón de reinicio.
    formularioAgregarCarrera?.addEventListener('reset', function () {
    });

    // Muestra los detalles de la carrera seleccionada.
    function consultarCarrera(idCarrera, botonSeleccionado = null) {
        if (!tarjetaDetalle) return;

        const detalleSeleccionado = tarjetaDetalle.querySelector( `[data-detalle-carrera="${idCarrera}"]` );

        if (!detalleSeleccionado) return;

        // Oculta el mensaje inicial y los detalles anteriores.
        if (detalleVacio) {
            detalleVacio.hidden = true;
        }

        tarjetaDetalle.querySelectorAll('[data-detalle-carrera]').forEach(function (detalle) {
            detalle.hidden = true;
        });

        // Muestra los datos de la carrera seleccionada.
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

    // Permite consultar una carrera desde la tabla.
    document.querySelectorAll('[data-consultar-carrera]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const idCarrera = boton.dataset.consultarCarrera;

            // Limpia la búsqueda para mostrar todos los resultados.
            if (buscarConsulta) {
                buscarConsulta.value = '';
            }

            actualizarResultadosConsulta();

            // Encuentra y selecciona la carrera correspondiente.
            const resultado = opcionesConsulta.find(function (opcion) {
                return opcion.dataset.resultadoCarrera === idCarrera;
            });

            if (resultado) {
                resultado.hidden = false;
                consultarCarrera(idCarrera, resultado);
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
            const clave = normalizarTexto(opcion.dataset.claveConsulta);
            const nombre = normalizarTexto(opcion.dataset.nombreConsulta);

            // Muestra coincidencias por clave o nombre.
            const mostrar = texto.length > 0 &&
                (clave.includes(texto) || nombre.includes(texto));

            opcion.hidden = !mostrar;

            if (mostrar) {
                cantidadVisible++;
            }
        });

        // Oculta los detalles cuando se limpia la búsqueda.
        if (!texto) {
            tarjetaDetalle?.querySelectorAll('[data-detalle-carrera]').forEach(function (detalle) {
                detalle.hidden = true;
            });

            if (detalleVacio) {
                detalleVacio.hidden = false;
            }
        }

        // Muestra un aviso cuando la búsqueda no encuentra coincidencias.
        if (sinResultadosConsulta) {
            sinResultadosConsulta.hidden = !texto || cantidadVisible > 0;
        }
    }

    // Actualiza los resultados mientras se escribe una clave o nombre.
    buscarConsulta?.addEventListener('input', actualizarResultadosConsulta);

    // Permite consultar una carrera desde la lista de resultados.
    opcionesConsulta.forEach(function (opcion) {
        opcion.setAttribute('aria-pressed', 'false');

        opcion.addEventListener('click', function () {
            consultarCarrera(opcion.dataset.resultadoCarrera, opcion);
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

    // Limpia el filtro de estado seleccionado.
    limpiarFiltros?.addEventListener('click', function () {
        if (filtroEstado) {
            filtroEstado.value = '';
        }

        // Regresa a la primera página y actualiza los resultados.
        paginaActual = 1;
        actualizarTabla();
    });

    // Actualiza la tabla cuando cambia el estado seleccionado.
    filtroEstado?.addEventListener('change', function () {
        paginaActual = 1;
        actualizarTabla();
    });

    // Abre el modal para confirmar la baja de una carrera.
    document.querySelectorAll('[data-baja-carrera]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            abrirModalBajaCarrera(
                boton.dataset.bajaCarrera,
                boton.dataset.nombreCarrera
            );
        });
    });

    // Abre el modal para confirmar la activación de una carrera.
    document.querySelectorAll('[data-activar-carrera]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            abrirModalActivarCarrera(
                boton.dataset.activarCarrera,
                boton.dataset.nombreCarrera
            );
        });
    });

    // Cierra los modales desde sus botones de cancelación.
    document.querySelectorAll('[data-cerrar-baja-carrera]').forEach(function (boton) {
        boton.addEventListener('click', cerrarModalBajaCarrera);
    });

    document.querySelectorAll('[data-cerrar-activar-carrera]').forEach(function (boton) {
        boton.addEventListener('click', cerrarModalActivarCarrera);
    });

    // Cierra los modales cuando se hace clic en el fondo.
    [modalBajaCarrera, modalActivarCarrera].forEach(function (modal) {
        modal?.addEventListener('click', function (evento) {
            if (evento.target === modal) {
                modal.hidden = true;
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    });

    // Cierra los modales de operación al presionar Escape.
    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarModalBajaCarrera();
            cerrarModalActivarCarrera();
        }
    });

    // Muestra el modal de confirmación que devuelve Laravel.
    if (modalConfirmacionCarrera) {
        modalConfirmacionCarrera.hidden = false;

        // Cierra el mensaje y regresa a la lista de carreras.
        document.getElementById('aceptar-modal-carrera')?.addEventListener('click', function () {
            window.location.href = `${window.rutaCarreras}`;
        });
    }

    // Abre el formulario si Laravel devuelve errores de validación.
    if (formularioCarrera && document.getElementById('mensaje-errores-carrera')) {
        formularioCarrera.hidden = false;

        formularioCarrera.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    // Oculta los resultados de consulta al cargar la página.
    actualizarResultadosConsulta();

    // Aplica la búsqueda y muestra la primera página de la tabla.
    actualizarTabla();

    //Modal de acierto al registrar 

    // Cerrar el modal al pulsar la X.
    botonCerrarRegistroCarrera?.addEventListener('click', function () {
        modalRegistroCarrera.hidden = true;
    });

    // Cerrar el modal al pulsar Aceptar.
    botonAceptarRegistroCarrera?.addEventListener('click', function () {
        modalRegistroCarrera.hidden = true;
    });

    // Cerrar el modal al pulsar fuera de su contenido.
    modalRegistroCarrera?.addEventListener('click', function (evento) {
        if (evento.target === modalRegistroCarrera) {
            modalRegistroCarrera.hidden = true;
        }
    });

    // Cerrar el modal al pulsar Escape.
    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && modalRegistroCarrera) {
            modalRegistroCarrera.hidden = true;
        }
    });

    //Modal de error al registrar misma clave 

    // Cerrar el modal al pulsar la X.
    botonCerrarErrorCarrera?.addEventListener('click', function () {
        modalErrorCarrera.hidden = true;
    });

    // Cerrar el modal al pulsar Aceptar.
    botonAceptarErrorCarrera?.addEventListener('click', function () {
        modalErrorCarrera.hidden = true;
    });

    // Cerrar el modal al pulsar fuera de su contenido.
    modalErrorCarrera?.addEventListener('click', function (evento) {
        if (evento.target === modalErrorCarrera) {
            modalErrorCarrera.hidden = true;
        }
    });

    // Cerrar el modal al pulsar Escape.
    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && modalErrorCarrera) {
            modalErrorCarrera.hidden = true;
        }
    });
});

    /**
     * Descripción: Abre el modal para confirmar la baja de una carrera.
     * Entradas:
     * idCarrera - Identificador de la carrera.
     * nombreCarrera - Nombre de la carrera seleccionada.
     *
     * Salidas: Muestra el modal y establece la ruta para procesar la baja.
     */
    function abrirModalBajaCarrera(idCarrera, nombreCarrera) {
        const modal = document.getElementById('modal-baja-carrera');
        const nombre = document.getElementById('nombre-carrera-baja');
        const formulario = document.getElementById('formulario-baja-carrera');

        // Verificar que existan los elementos necesarios.
        if (!modal || !nombre || !formulario) {
            return;
        }

        // Mostrar el nombre de la carrera que se desactivará.
        nombre.textContent = nombreCarrera || 'Carrera seleccionada';

        // Establecer la ruta DELETE definida en web.php.
        formulario.action = `${window.rutaCarreras}/${encodeURIComponent(idCarrera)}/baja`;

        // Mostrar el modal.
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
    }

    /**
     * Descripción: Cierra el modal de confirmación de baja de una carrera.
     * Entradas: No recibe parámetros.
     * Salidas: Oculta el modal de confirmación.
     */
    function cerrarModalBajaCarrera() {
        const modal = document.getElementById('modal-baja-carrera');

        // Ocultar el modal si existe.
        if (modal) {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
        }
    }

// MODAL DE MODIFICACIÓN EXITOSA

// Obtiene los elementos del modal de modificación.
const modalModificarCarrera = document.getElementById(
    'modal-modificar-carrera'
);

const cerrarModalModificarCarrera = document.getElementById(
    'cerrar-modal-modificar-carrera'
);

const aceptarModalModificarCarrera = document.getElementById(
    'aceptar-modal-modificar-carrera'
);

// Cerrar el modal mediante el botón superior.
cerrarModalModificarCarrera?.addEventListener('click', function () {
    modalModificarCarrera.hidden = true;
});

// Cerrar el modal mediante el botón Aceptar.
aceptarModalModificarCarrera?.addEventListener('click', function () {
    modalModificarCarrera.hidden = true;
});

// Cerrar el modal al hacer clic fuera de su contenido.
modalModificarCarrera?.addEventListener('click', function (evento) {
    if (evento.target === modalModificarCarrera) {
        modalModificarCarrera.hidden = true;
    }
});

// Cerrar el modal al presionar Escape.
document.addEventListener('keydown', function (evento) {
    if (evento.key === 'Escape' && modalModificarCarrera) {
        modalModificarCarrera.hidden = true;
    }
});