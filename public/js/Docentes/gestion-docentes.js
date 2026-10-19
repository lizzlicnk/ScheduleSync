let formularioBajaDocenteSeleccionado = null;

document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos principales del módulo.
    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const limpiarFiltros = document.getElementById('limpiar-filtros');
    const buscarDocente = document.getElementById('buscar-docente');
    const filtroTipo = document.getElementById('filtro-tipo');
    const filtroEstado = document.getElementById('filtro-estado');
    const cuerpoTabla = document.getElementById('cuerpo-tabla-docentes');
    const totalResultados = document.getElementById('total-resultados');
    const filaSinFiltros = document.getElementById('fila-sin-filtros');

    const botonNuevoDocente = document.getElementById('boton-nuevo-docente');
    const formularioDocente = document.getElementById('formulario-docente');
    const formularioAgregar = document.getElementById('formAgregarDocente');
    const selectorUsuario = document.getElementById('id_usuario');

    const panelConsulta = document.getElementById('panel-consulta');
    const buscarConsulta = document.getElementById('buscar-consulta');
    const resultadosConsulta = document.getElementById('resultados-consulta');
    const sinResultadosConsulta = document.getElementById('sin-resultados-consulta');
    const tarjetaDetalle = document.getElementById('tarjeta-detalle');
    const detalleVacio = document.getElementById('detalle-vacio');

    const modalDocenteAgregado = document.getElementById('modal-docente-agregado');
    const botonAceptarDocente = document.getElementById('boton-aceptar-docente');

    // Obtiene los elementos del modal de baja de docente.
    const modalBajaDocente = document.getElementById('modal-baja-docente');
    const nombreDocenteBaja = document.getElementById('nombre-docente-baja');

    // Obtiene las filas reales de docentes y excluye los mensajes de la tabla.
    const filasDocentes = cuerpoTabla
        ? Array.from(cuerpoTabla.querySelectorAll('tr[data-id-usuario]'))
        : [];

    // Obtiene las opciones disponibles para consultar.
    const opcionesConsulta = resultadosConsulta
        ? Array.from(resultadosConsulta.querySelectorAll('[data-resultado-usuario]'))
        : [];

    // Normaliza los textos para que la búsqueda ignore mayúsculas y acentos.
    function normalizarTexto(texto) {
        return (texto || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    // Abre o cierra el panel de filtros.
    if (botonFiltros && contenedorFiltros) {
        botonFiltros.addEventListener('click', function () {
            // Comprueba si el panel está oculto.
            const estaOculto = contenedorFiltros.hidden;

            // Cambia la visibilidad del panel de filtros.
            contenedorFiltros.hidden = !estaOculto;

            // Actualiza el estado de accesibilidad del botón.
            botonFiltros.setAttribute('aria-expanded', String(estaOculto));

            // Resalta el botón mientras el panel esté abierto.
            botonFiltros.classList.toggle('boton-activo', estaOculto);
        });
    }

    // Filtra la tabla por texto, tipo y estado de la cuenta.
    function aplicarFiltros() {
        const texto = normalizarTexto(buscarDocente?.value);
        const tipoSeleccionado = filtroTipo?.value || '';
        const estadoSeleccionado = filtroEstado?.value || '';
        let cantidadVisible = 0;

        filasDocentes.forEach(function (fila) {
            const nombre = normalizarTexto(fila.dataset.nombre);
            const correo = normalizarTexto(fila.dataset.correo);
            const matricula = normalizarTexto(fila.dataset.matricula);
            const especialidad = normalizarTexto(fila.dataset.especialidad);
            const tipo = fila.dataset.tipo || 'sin-datos';
            const estado = fila.dataset.estado || '0';

            // Busca coincidencias en nombre, correo, matrícula y especialidad.
            const coincideTexto = !texto ||
                nombre.includes(texto) ||
                correo.includes(texto) ||
                matricula.includes(texto) ||
                especialidad.includes(texto);

            // Comprueba si coincide el tipo seleccionado.
            const coincideTipo = !tipoSeleccionado ||
                tipo === tipoSeleccionado;

            // Comprueba si coincide el estado seleccionado.
            const coincideEstado = !estadoSeleccionado ||
                estado === estadoSeleccionado;

            // Muestra únicamente las filas que cumplen todos los criterios.
            const mostrar = coincideTexto && coincideTipo && coincideEstado;

            fila.hidden = !mostrar;

            if (mostrar) {
                cantidadVisible++;
            }
        });

        // Actualiza el contador y el mensaje de tabla vacía.
        if (totalResultados) {
            totalResultados.textContent = cantidadVisible;
        }

        if (filaSinFiltros) {
            filaSinFiltros.hidden = cantidadVisible > 0;
        }
    }

    // Ejecuta los filtros cuando cambia la búsqueda o una selección.
    buscarDocente?.addEventListener('input', aplicarFiltros);
    filtroTipo?.addEventListener('change', aplicarFiltros);
    filtroEstado?.addEventListener('change', aplicarFiltros);

    // Restablece la búsqueda y los filtros.
    limpiarFiltros?.addEventListener('click', function () {
        if (buscarDocente) buscarDocente.value = '';
        if (filtroTipo) filtroTipo.value = '';
        if (filtroEstado) filtroEstado.value = '';

        aplicarFiltros();
    });

    // Abre el formulario y opcionalmente selecciona una cuenta de usuario.
    function abrirFormulario(idUsuario = '') {
        if (!formularioDocente || !formularioAgregar) return;

        // Mantiene visibles el formulario y el panel de consulta.
        formularioDocente.hidden = false;

        if (panelConsulta) {
            panelConsulta.hidden = false;
        }

        // Reinicia el formulario al iniciar un nuevo registro.
        formularioAgregar.reset();

        // Selecciona la cuenta solicitada, si está disponible.
        if (selectorUsuario) {
            selectorUsuario.value = idUsuario ? String(idUsuario) : '';
        }

        // Sitúa al usuario en el formulario.
        formularioDocente.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        selectorUsuario?.focus({ preventScroll: true });
    }

    // Abre el formulario desde el botón Nuevo docente.
    botonNuevoDocente?.addEventListener('click', function () {
        abrirFormulario();
    });

    // Abre el formulario con la cuenta elegida desde la tabla.
    document.querySelectorAll('[data-agregar-docente]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            abrirFormulario(boton.dataset.agregarDocente);
        });
    });

    // Cierra el formulario y devuelve la vista a la tabla.
    document.querySelectorAll('[data-cerrar-formulario]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            if (formularioDocente) {
                formularioDocente.hidden = true;
            }

            if (panelConsulta) {
                panelConsulta.hidden = false;
            }
        });
    });

    // Muestra la tarjeta de información de la cuenta seleccionada.
    function consultarUsuario(idUsuario, botonSeleccionado = null) {
        if (!resultadosConsulta || !tarjetaDetalle) return;

        const detalleSeleccionado = tarjetaDetalle.querySelector(
            '[data-detalle-usuario="' + idUsuario + '"]'
        );

        if (!detalleSeleccionado) return;

        // Oculta el mensaje inicial y los detalles anteriores.
        if (detalleVacio) {
            detalleVacio.hidden = true;
        }

        tarjetaDetalle.querySelectorAll('[data-detalle-usuario]').forEach(function (detalle) {
            detalle.hidden = true;
        });

        // Muestra la información de la cuenta seleccionada.
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

        // Lleva al panel de consulta si se encuentra oculto.
        if (panelConsulta) {
            panelConsulta.hidden = false;
        }
    }

    // Permite consultar desde el botón de acciones de la tabla.
    document.querySelectorAll('[data-consultar-usuario]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            if (formularioDocente) {
                formularioDocente.hidden = false;
            }

            if (panelConsulta) {
                panelConsulta.hidden = false;
            }

            // La consulta directa selecciona el nombre correspondiente.
            if (buscarConsulta) {
                buscarConsulta.value = '';
            }

            actualizarResultadosConsulta();

            const idUsuario = boton.dataset.consultarUsuario;
            const resultado = opcionesConsulta.find(function (opcion) {
                return opcion.dataset.resultadoUsuario === idUsuario;
            });

            if (resultado) {
                consultarUsuario(idUsuario, resultado);
            }

            panelConsulta?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });

    /*
    Descripción: Cierra el modal de confirmación después de aceptar el mensaje.
    Entradas: Clic sobre el botón Aceptar.
    Salidas: Modal oculto.
    */

    botonAceptarDocente?.addEventListener('click', function () {
        if (!modalDocenteAgregado) return;

        modalDocenteAgregado.hidden = true;
        modalDocenteAgregado.setAttribute('aria-hidden', 'true');
    });

    // Filtra los resultados de consulta solamente cuando hay texto escrito.
    function actualizarResultadosConsulta() {
        const texto = normalizarTexto(buscarConsulta?.value);
        let cantidadVisible = 0;

        opcionesConsulta.forEach(function (opcion) {
            const nombre = normalizarTexto(opcion.dataset.nombreConsulta);

            // Al abrir el panel, no muestra docentes si el campo está vacío.
            const mostrar = texto.length > 0 && nombre.includes(texto);

            opcion.hidden = !mostrar;

            if (mostrar) {
                cantidadVisible++;
            }
        });

        // Oculta los detalles anteriores mientras se escribe una búsqueda nueva.
        if (!texto) {
            tarjetaDetalle?.querySelectorAll('[data-detalle-usuario]').forEach(function (detalle) {
                detalle.hidden = true;
            });

            if (detalleVacio) {
                detalleVacio.hidden = false;
            }
        }

        // Muestra el aviso solo cuando existe una búsqueda sin coincidencias.
        if (sinResultadosConsulta) {
            sinResultadosConsulta.hidden = !texto || cantidadVisible > 0;
        }
    }

    // Actualiza los resultados conforme se escribe el nombre o apellido.
    buscarConsulta?.addEventListener('input', actualizarResultadosConsulta);

    // Permite seleccionar un resultado de la búsqueda.
    opcionesConsulta.forEach(function (opcion) {
        opcion.addEventListener('click', function () {
            consultarUsuario(opcion.dataset.resultadoUsuario, opcion);
        });
    });

    // Abre el formulario si Laravel devuelve errores de validación.
    if (formularioDocente && document.getElementById('mensaje-errores-docente')) {
        formularioDocente.hidden = false;

        panelConsulta && (panelConsulta.hidden = true);

        formularioDocente.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    // Oculta los resultados al cargar la página por primera vez.
    actualizarResultadosConsulta();

    // Aplica el estado inicial de los filtros de la tabla.
    aplicarFiltros();
});
    

/**
 * Descripción:
 * Controla la apertura, el cierre y la confirmación del modal de baja.
 *
 * Entradas:
 * Clic en los botones del modal y formulario del docente seleccionado.
 *
 * Salidas:
 * Muestra el modal, permite cancelarlo o envía la solicitud de baja.
 */

// Registra los eventos del modal cuando el documento está disponible.
document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos del modal de baja.
    const modalBajaDocente = document.getElementById('modal-baja-docente');
    const nombreDocenteBaja = document.getElementById('nombre-docente-baja');

    // Abre el modal cuando se presiona Eliminar.
    document.addEventListener('click', function (evento) {
        const botonBaja = evento.target.closest('[data-baja-docente]');

        // Ignora los clics que no correspondan al botón de baja.
        if (!botonBaja) {
            return;
        }

        // Guarda el formulario asociado al docente seleccionado.
        formularioBajaDocenteSeleccionado = botonBaja.closest(
            'form.formulario-baja-docente'
        );

        // Comprueba que los elementos necesarios existan.
        if (
            !formularioBajaDocenteSeleccionado ||
            !modalBajaDocente ||
            !nombreDocenteBaja
        ) {
            return;
        }

        // Muestra el nombre del docente en el modal.
        nombreDocenteBaja.textContent =
            formularioBajaDocenteSeleccionado.dataset.nombreDocente ||
            'Docente seleccionado';

        // Muestra el modal de confirmación.
        modalBajaDocente.hidden = false;
        modalBajaDocente.setAttribute('aria-hidden', 'false');
    });

    // Cierra el modal al presionar Escape.
    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && modalBajaDocente && !modalBajaDocente.hidden) {
            cerrarModalBajaDocente();
        }
    });
});

/**
 * Descripción:
 * Cierra el modal de confirmación de baja.
 *
 * Entradas:
 * Clic en Cancelar, en cerrar o tecla Escape.
 *
 * Salidas:
 * Oculta el modal y limpia el formulario seleccionado.
 */
function cerrarModalBajaDocente() {
    const modal = document.getElementById('modal-baja-docente');

    // Oculta el modal.
    if (modal) {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
    }

    // Limpia la selección del formulario.
    formularioBajaDocenteSeleccionado = null;
}

/**
 * Descripción:
 * Confirma y envía la solicitud de baja del docente.
 *
 * Entradas:
 * Formulario del docente seleccionado.
 *
 * Salidas:
 * Envía el formulario a Laravel mediante DELETE.
 */
function ejecutarBajaDocente() {

    // Verifica que exista un formulario seleccionado.
    if (!formularioBajaDocenteSeleccionado) {
        console.error('No se seleccionó un docente para dar de baja.');
        return;
    }

    // Envía la solicitud de baja lógica.
    formularioBajaDocenteSeleccionado.submit();
}

// Controla el modal de error al registrar un docente.
document.addEventListener('DOMContentLoaded', function () {

    // Obtener el modal y sus botones.
    const modalErrorDocente = document.getElementById('modal-error-docente');
    const cerrarModalErrorDocente = document.getElementById('cerrar-modal-error-docente');
    const aceptarModalErrorDocente = document.getElementById('aceptar-modal-error-docente');

    // Cerrar el modal de error.
    function cerrarErrorDocente() {
        if (modalErrorDocente) {
            modalErrorDocente.remove();
        }
    }

    // Asignar el evento al botón de cerrar.
    if (cerrarModalErrorDocente) {
        cerrarModalErrorDocente.addEventListener('click', cerrarErrorDocente);
    }

    // Asignar el evento al botón Aceptar.
    if (aceptarModalErrorDocente) {
        aceptarModalErrorDocente.addEventListener('click', cerrarErrorDocente);
    }
});
