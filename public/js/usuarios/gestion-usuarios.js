document.addEventListener('DOMContentLoaded', function () {

    // Obtener la tabla y las filas que contienen usuarios.
    const tablaUsuarios = document.getElementById('tabla-usuarios');
    const filasUsuarios = Array.from(
        tablaUsuarios.querySelectorAll('tr[data-rol]')
    );

    // Obtener los campos de búsqueda y filtros.
    const campoBuscar = document.getElementById('buscar-usuario');
    const filtroRol = document.getElementById('filtro-rol');
    const filtroEstado = document.getElementById('filtro-estado');

    // Obtener los elementos que muestran los resultados de la búsqueda.
    const totalResultados = document.getElementById('total-resultados');
    const filaSinUsuarios = document.getElementById('fila-sin-usuarios');
    const filaSinFiltros = document.getElementById('fila-sin-filtros');

    // Obtener los elementos de la paginación.
    const informacionPagina = document.getElementById('informacion-pagina');
    const botonAnterior = document.getElementById('pagina-anterior');
    const botonSiguiente = document.getElementById('pagina-siguiente');

    // Obtener los elementos para mostrar u ocultar los filtros.
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const botonFiltros = document.getElementById('boton-filtros');

    // Obtener los elementos de búsqueda y consulta de usuarios.
    const buscarConsulta = document.getElementById('buscar-consulta');
    const resultadosConsulta = document.getElementById('resultados-consulta');
    const tarjetaDetalle = document.getElementById('tarjeta-detalle');

    // Obtener los elementos del formulario y la contraseña.
    const formulario = document.getElementById('formulario-datos-usuario');
    const campoPassword = document.getElementById('password');
    const botonMostrarPassword = document.getElementById('mostrar-password');

    // Definir la cantidad de usuarios que se muestran por página.
    const filasPorPagina = 5;
    let paginaActual = 1;
    let usuariosCoincidentes = [];

    // Relacionar los identificadores de los roles con sus nombres.
    const nombresRoles = {
        '1': 'Administrador',
        '2': 'Personal administrativo',
        '3': 'Docente'
    };

    // Abrir y cerrar el panel de filtros.
    botonFiltros.addEventListener('click', function () {
        const seAbrira = contenedorFiltros.hidden;

        contenedorFiltros.hidden = !seAbrira;
        botonFiltros.setAttribute('aria-expanded', String(seAbrira));
        botonFiltros.classList.toggle('boton-activo', seAbrira);
    });

    // Aplicar búsqueda y filtros; mostrar cinco filas por página.
    function filtrarUsuarios() {
        const textoBusqueda = campoBuscar.value.trim().toLocaleLowerCase('es');
        const rolSeleccionado = filtroRol.value;
        const estadoSeleccionado = filtroEstado.value;

        // Comprobar si se seleccionó algún filtro.
        const hayFiltrosSeleccionados =
            rolSeleccionado !== '' || estadoSeleccionado !== '';

        // Obtener los usuarios que coinciden con el filtro
        usuariosCoincidentes = filasUsuarios.filter(function (fila) {
            const nombre = (fila.dataset.nombre || '').toLocaleLowerCase('es');
            const correo = (fila.dataset.correo || '').toLocaleLowerCase('es');

            
            const coincideBusqueda = nombre.includes(textoBusqueda) || correo.includes(textoBusqueda);

            const coincideRol = rolSeleccionado === '' || fila.dataset.rol === rolSeleccionado;

            const coincideEstado = estadoSeleccionado === '' || fila.dataset.estado === estadoSeleccionado;

            return coincideBusqueda && coincideRol && coincideEstado;
        });

        // Calcular el total de páginas disponibles.
        const totalPaginas = Math.max(
            1,
            Math.ceil(usuariosCoincidentes.length / filasPorPagina)
        );

         // Ajustar la página actual si supera el total de páginas.
        if (paginaActual > totalPaginas) {
            paginaActual = totalPaginas;
        }

        // Calcular las filas que corresponden a la página actual.
        const inicio = (paginaActual - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;
        const filasVisibles = new Set(usuariosCoincidentes.slice(inicio, fin));

        // Mostrar únicamente las filas correspondientes
        filasUsuarios.forEach(function (fila) {
            fila.hidden = !filasVisibles.has(fila);
        });

        // Comprobar si existen usuarios que coincidan con la búsqueda.
        const hayResultados = usuariosCoincidentes.length > 0;

        filaSinUsuarios.hidden = true;
        filaSinFiltros.hidden = true;

        // Mostrar el mensaje correspondiente cuando no hay resultados.
        if (!hayResultados) {
            if (hayFiltrosSeleccionados) {
                filaSinFiltros.hidden = false;
            } else {
                filaSinUsuarios.hidden = false;
            }
        }

        // Actualizar el total de resultados y la información de la página.
        totalResultados.textContent = usuariosCoincidentes.length;
        informacionPagina.textContent = 'Página ' + paginaActual + ' de ' + totalPaginas;

        // Habilitar o deshabilitar los botones de paginación.
        botonAnterior.disabled = paginaActual <= 1;
        botonSiguiente.disabled = paginaActual >= totalPaginas;

        // Ocultar la paginación cuando no hay resultados.
        botonAnterior.hidden = !hayResultados;
        botonSiguiente.hidden = !hayResultados;
    }

    // Actualizar los resultados cuando cambia el texto de búsqueda.
    campoBuscar.addEventListener('input', function () {
        paginaActual = 1;
        filtrarUsuarios();
    });

     // Actualizar los resultados cuando cambia el rol seleccionado.
    filtroRol.addEventListener('change', function () {
        paginaActual = 1;
        filtrarUsuarios();
    });

    // Actualizar los resultados cuando cambia el estado seleccionado.
    filtroEstado.addEventListener('change', function () {
        paginaActual = 1;
        filtrarUsuarios();
    });

    // Limpiar los campos de búsqueda y restablecer los filtros.
    document.getElementById('limpiar-filtros').addEventListener('click', function () {
        filtroRol.value = '';
        filtroEstado.value = '';
        campoBuscar.value = '';
        paginaActual = 1;
        filtrarUsuarios();
    });

    // Mostrar la página anterior cuando esté disponible.
    botonAnterior.addEventListener('click', function () {
        if (paginaActual > 1) {
            paginaActual--;
            filtrarUsuarios();
        }
    });

    // Mostrar la página siguiente cuando esté disponible.
    botonSiguiente.addEventListener('click', function () {
        const totalPaginas = Math.max(
            1,
            Math.ceil(usuariosCoincidentes.length / filasPorPagina)
        );

        if (paginaActual < totalPaginas) {
            paginaActual++;
            filtrarUsuarios();
        }
    });

    // Mostrar u ocultar la contraseña.
    botonMostrarPassword.addEventListener('click', function () {
        const mostrar = campoPassword.type === 'password';

        campoPassword.type = mostrar ? 'text' : 'password';
        botonMostrarPassword.textContent = mostrar ? 'Ocultar' : 'Mostrar';
        botonMostrarPassword.setAttribute('aria-label',
            mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        botonMostrarPassword.setAttribute('aria-pressed', String(mostrar));
    });

    // Calcular la seguridad de la contraseña mientras se escribe.
    function actualizarSeguridadPassword() {
        const password = campoPassword.value;
        const progreso = document.getElementById('progreso-seguridad');
        const textoSeguridad = document.getElementById('texto-seguridad');
        const barraSeguridad = document.querySelector('.barra-seguridad');

        let puntos = 0;

        // Evaluar la longitud y los tipos de caracteres de la contraseña.
        if (password.length >= 8) puntos++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) puntos++;
        if (/\d/.test(password)) puntos++;
        if (/[^A-Za-z0-9]/.test(password)) puntos++;

        // Definir los niveles de seguridad y sus clases CSS.
        const niveles = [
            { texto: 'Muy débil', clase: 'seguridad-muy-debil' },
            { texto: 'Débil', clase: 'seguridad-debil' },
            { texto: 'Aceptable', clase: 'seguridad-aceptable' },
            { texto: 'Buena', clase: 'seguridad-buena' },
            { texto: 'Excelente', clase: 'seguridad-excelente' }
        ];

        // Restablecer las clases de seguridad anteriores.
        progreso.className = '';

        // Mostrar el estado inicial cuando el campo está vacío.
        if (password.length === 0) {
            progreso.style.width = '0%';
            textoSeguridad.textContent = 'Sin contraseña';
            barraSeguridad.setAttribute('aria-valuenow', '0');
            return;
        }

        // Actualizar la barra y el texto según el nivel calculado.
        progreso.style.width = Math.max(25, puntos * 25) + '%';
        progreso.classList.add(niveles[puntos].clase);
        textoSeguridad.textContent = niveles[puntos].texto;
        barraSeguridad.setAttribute('aria-valuenow', String(puntos));
    }

    // Actualizar la seguridad cada vez que cambia la contraseña.
    campoPassword.addEventListener('input', actualizarSeguridadPassword);

    // Restablecer el formulario para registrar un usuario.
    window.abrirFormularioUsuario = function () {
        formulario.reset();
        formulario.action = window.rutaUsuarios;

        // Eliminar el método de modificación y restablecer los textos.
        document.getElementById('metodo-modificacion').replaceChildren();
        document.getElementById('titulo-formulario').textContent = 'Nuevo usuario';
        document.getElementById('descripcion-formulario').textContent =
            'Completa los datos para registrar un nuevo usuario.';
        document.getElementById('boton-guardar').textContent = 'Registrar usuario';
        document.getElementById('cancelar-edicion').style.display = 'none';

        // Marcar la contraseña como obligatoria durante el registro.
        campoPassword.required = true;
        document.getElementById('asterisco-password').hidden = false;
        document.getElementById('ayuda-password').textContent =
            'Usa al menos 8 caracteres, letras y números.';

        // Restablecer el botón para mostrar la contraseña.
        botonMostrarPassword.textContent = 'Mostrar';
        botonMostrarPassword.setAttribute('aria-label', 'Mostrar contraseña');
        botonMostrarPassword.setAttribute('aria-pressed', 'false');
        campoPassword.type = 'password';
        actualizarSeguridadPassword();

        // Desplazarse hasta el formulario de registro.
        document.getElementById('formulario-usuario').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // Colocar el cursor en el campo del nombre.
        document.getElementById('nombreUsuario').focus({ preventScroll: true });
    };

    // Mostrar los resultados de búsqueda de la consulta.
    function buscarUsuariosParaConsulta() {
        const texto = buscarConsulta.value.trim().toLocaleLowerCase('es');
        resultadosConsulta.replaceChildren();

        // Campo está vacío.
        if (texto.length === 0) {
            const mensaje = document.createElement('p');
            mensaje.className = 'texto-secundario';
            mensaje.textContent = 'Escribe un nombre para encontrar usuarios.';
            resultadosConsulta.appendChild(mensaje);
            return;
        }

        // Obtener los usuarios cuyo nombre coincide con la búsqueda.
        const coincidencias = filasUsuarios.filter(function (fila) {
            const nombre = (fila.dataset.nombre || '').toLocaleLowerCase('es');
            return nombre.includes(texto);
        });

        // No hay coincidencias.
        if (coincidencias.length === 0) {
            const mensaje = document.createElement('p');
            mensaje.className = 'texto-secundario';
            mensaje.textContent = 'No se encontraron usuarios con ese nombre.';
            resultadosConsulta.appendChild(mensaje);
            return;
        }

        // Crear un resultado por usuario, con un máximo de diez elementos.
        coincidencias.slice(0, 10).forEach(function (fila) {
            const botonOriginal = fila.querySelector('[onclick="consultarUsuario(this)"]');
            const botonResultado = document.createElement('button');

            botonResultado.type = 'button';
            botonResultado.className = 'resultado-consulta-item';

            // Obtener el nombre completo del usuario.
            const nombre = [
                botonOriginal.dataset.nombre,
                botonOriginal.dataset.paterno,
                botonOriginal.dataset.materno
            ].filter(Boolean).join(' ');

            // Crear los elementos que muestran el nombre y el correo.
            const nombreElemento = document.createElement('strong');
            nombreElemento.textContent = nombre;
            const correoElemento = document.createElement('span');
            correoElemento.textContent = botonOriginal.dataset.correo;

            // Agregar los datos al botón del resultado.
            botonResultado.append(nombreElemento, correoElemento);
            botonResultado.addEventListener('click', function () {
                mostrarDetalleUsuario(botonOriginal);
            });

            resultadosConsulta.appendChild(botonResultado);
        });
    }

    // Actualizar los resultados mientras se escribe el nombre.
    buscarConsulta.addEventListener('input', buscarUsuariosParaConsulta);

    // Mostrar los datos del usuario seleccionado en el panel lateral.
    function mostrarDetalleUsuario(boton) {
        // Mostrar los datos del usuario seleccionado en el panel lateral.
        tarjetaDetalle.replaceChildren();

        const nombreCompleto = [
            boton.dataset.nombre,
            boton.dataset.paterno,
            boton.dataset.materno
        ].filter(Boolean).join(' ');

        // Crear el encabezado con el icono, el nombre y el correo.
        const encabezado = document.createElement('div');
        encabezado.className = 'encabezado-detalle';

        const icono = document.createElement('i');
        icono.className = 'fa-solid fa-user icono-detalle';

        const datosEncabezado = document.createElement('div');

        const nombre = document.createElement('h3');
        nombre.textContent = nombreCompleto;

        const correo = document.createElement('p');
        correo.className = 'correo-detalle';
        correo.textContent = boton.dataset.correo;

        datosEncabezado.append(nombre, correo);
        encabezado.append(icono, datosEncabezado);

        // Preparar la lista con los datos generales del usuario.
        const listaDatos = document.createElement('div');
        listaDatos.className = 'lista-detalle';

        const datos = [
            ['ID de usuario', boton.dataset.id],
            ['Rol', nombresRoles[boton.dataset.rol] || 'Sin rol asignado'],
            ['Estado', boton.dataset.estado === '1' ? 'Activo' : 'Inactivo']
        ];

        // Crear una fila por cada dato del usuario.
        datos.forEach(function (dato) {
            const fila = document.createElement('div');
            fila.className = 'fila-detalle';

            const etiqueta = document.createElement('span');
            etiqueta.textContent = dato[0];

            const valor = document.createElement('strong');
            valor.textContent = dato[1] || 'No registrado';

            // Aplicar una clase según el estado del usuario.
            if (dato[0] === 'Estado') {
                valor.classList.add(
                    boton.dataset.estado === '1'
                        ? 'texto-estado-activo'
                        : 'texto-estado-inactivo'
                );
            }

            // Mostrar el encabezado y los datos en la tarjeta de consulta.
            fila.append(etiqueta, valor);
            listaDatos.appendChild(fila);
        });

        tarjetaDetalle.append(encabezado, listaDatos);
    }

    // Función utilizada por el botón Consultar de la tabla.
    window.consultarUsuario = function (botonConsulta) {
        //Nombre completo
        const nombreCompleto = [
            botonConsulta.dataset.nombre,
            botonConsulta.dataset.paterno,
            botonConsulta.dataset.materno
        ].filter(Boolean).join(' ');

        // Buscar al usuario y mostrar sus detalles.
        buscarConsulta.value = nombreCompleto;
        buscarUsuariosParaConsulta();
        mostrarDetalleUsuario(botonConsulta);

        // Desplazarse hasta el panel de consulta.
        document.getElementById('panel-consulta').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    };

    // Cargar los datos del usuario seleccionado en el formulario de edición.
    window.editarUsuario = function (botonEdicion) {
        // Establecer la ruta para actualizar al usuario seleccionado.
        formulario.action = window.rutaUsuarios + '/' + encodeURIComponent(botonEdicion.dataset.id);

        // Crear el campo que indica a Laravel que la solicitud es PUT.
        const metodo = document.createElement('input');
        metodo.type = 'hidden';
        metodo.name = '_method';
        metodo.value = 'PUT';

        const contenedorMetodo = document.getElementById('metodo-modificacion');
        contenedorMetodo.replaceChildren(metodo);

         // Cargar los datos actuales del usuario en los campos del formulario.
        document.getElementById('nombreUsuario').value = botonEdicion.dataset.nombre || '';
        document.getElementById('apellidoPaterno').value = botonEdicion.dataset.paterno || '';
        document.getElementById('apellidoMaterno').value = botonEdicion.dataset.materno || '';
        document.getElementById('correo').value = botonEdicion.dataset.correo || '';
        document.getElementById('rol').value = botonEdicion.dataset.rol || '';
        document.getElementById('estado').value = botonEdicion.dataset.estado || '1';

        // Dejar la contraseña vacía y hacer que sea opcional.
        campoPassword.value = '';
        campoPassword.required = false;
        campoPassword.type = 'password';

        // Indicar que la contraseña solo se modifica si se proporciona una nueva.
        document.getElementById('asterisco-password').hidden = true;
        document.getElementById('ayuda-password').textContent =
            'Déjala vacía si no deseas cambiar la contraseña.';

        //  Botón para mostrar la contraseña.
        botonMostrarPassword.textContent = 'Mostrar';
        botonMostrarPassword.setAttribute('aria-label', 'Mostrar contraseña');
        botonMostrarPassword.setAttribute('aria-pressed', 'false');
        actualizarSeguridadPassword();

        // Cambiar los textos del formulario al modo de edición.
        document.getElementById('titulo-formulario').textContent = 'Modificar usuario';
        document.getElementById('descripcion-formulario').textContent =
            'Actualiza los datos del usuario seleccionado.';
        document.getElementById('boton-guardar').textContent = 'Guardar cambios';
        document.getElementById('cancelar-edicion').style.display = 'inline-flex';

        // Desplazarse hasta el formulario de edición.
        document.getElementById('formulario-usuario').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    };

    // Cancelar edición y regresar al registro.
    window.cancelarEdicionUsuario = function () {
        window.abrirFormularioUsuario();
    };

    // Aplicar la paginación inicial.
    filtrarUsuarios();

    // Abrir el modal y mostrar el nombre del usuario seleccionado.
    window.abrirModalBajaUsuario = function (idUsuario, nombreUsuario) {

        const modalBajaUsuario = document.getElementById('modal-baja-usuario');

        const nombreUsuarioBaja = document.getElementById('nombre-usuario-baja');

        const botonConfirmarBaja = modalBajaUsuario.querySelector(
            '.boton-modal-confirmar'
        );

        // Guardar el ID del usuario que se desea dar de baja.
        botonConfirmarBaja.dataset.idUsuario = idUsuario;

        // Mostrar el nombre del usuario seleccionado.
        nombreUsuarioBaja.textContent = nombreUsuario;

        // Mostrar el modal.
        modalBajaUsuario.hidden = false;

        // Bloquear el desplazamiento de la página.
        document.body.style.overflow = 'hidden';
    };


    // Cerrar el modal sin modificar al usuario.
    window.cerrarModalBajaUsuario = function () {

        const modalBajaUsuario = document.getElementById('modal-baja-usuario');

        modalBajaUsuario.hidden = true;

        // Restaurar el desplazamiento de la página.
        document.body.style.overflow = '';
    };


    // Ejecutar la baja después de confirmar.
    window.ejecutarBajaUsuario = function () {

        // Obtener el botón de confirmación del modal.
        const botonConfirmarBaja = document.querySelector(
            '#modal-baja-usuario .boton-modal-confirmar'
        );

        // Obtener el identificador del usuario seleccionado.
        const idUsuario = botonConfirmarBaja.dataset.idUsuario;

        // Crear el formulario para enviar la solicitud a Laravel.
        const formularioBajaUsuario = document.createElement('form');

        formularioBajaUsuario.method = 'POST';

        // Establecer la ruta del usuario que se desea desactivar.
        formularioBajaUsuario.action = window.rutaUsuarios + '/' +
            encodeURIComponent(idUsuario);

        // Agregar el token de seguridad de Laravel.
        const campoTokenSeguridad = document.createElement('input');

        campoTokenSeguridad.type = 'hidden';
        campoTokenSeguridad.name = '_token';
        campoTokenSeguridad.value = window.tokenSeguridad;

        // Indicar a Laravel que la solicitud corresponde a DELETE.
        const campoMetodoSolicitud = document.createElement('input');

        campoMetodoSolicitud.type = 'hidden';
        campoMetodoSolicitud.name = '_method';
        campoMetodoSolicitud.value = 'DELETE';

        // Agregar los campos de seguridad al formulario.
        formularioBajaUsuario.append(
            campoTokenSeguridad,
            campoMetodoSolicitud
        );

        // Agregar el formulario al documento y enviarlo al controlador.
        document.body.appendChild(formularioBajaUsuario);
        formularioBajaUsuario.submit();
    };


    /* Ocultar automáticamente los mensajes de éxito y error. */
    const mensajesTemporales = document.querySelectorAll(
        '.mensaje-exito, .mensaje-error, .alerta-error, .alert-danger'
    );

    mensajesTemporales.forEach(function (mensaje) {
        setTimeout(function () {
            mensaje.style.transition = 'opacity 0.5s ease';
            mensaje.style.opacity = '0';

            setTimeout(function () {
                mensaje.remove();
            }, 500);

        }, 3000);
    });

    
    // Cerrar el modal únicamente después de registrar un usuario.
    const modalRegistroUsuario = document.getElementById(
        'modal-registro-usuario'
    );

    const botonCerrarRegistro = document.getElementById(
        'cerrar-modal-registro'
    );

    const botonAceptarRegistro = document.getElementById(
        'aceptar-modal-registro'
    );

    // Cerrar el modal de registro y restaurar el desplazamiento.
    function cerrarModalRegistroUsuario() {
        if (modalRegistroUsuario) {
            modalRegistroUsuario.remove();
            document.body.style.overflow = '';
        }
    }

    // Configurar el modal cuando esté presente en la página.
    if (modalRegistroUsuario) {
        // Bloquear el desplazamiento mientras el modal esté abierto.
        document.body.style.overflow = 'hidden';

        // Cerrar desde la X.
        botonCerrarRegistro.addEventListener(
            'click',
            cerrarModalRegistroUsuario
        );

        // Cerrar desde Aceptar.
        botonAceptarRegistro.addEventListener(
            'click',
            cerrarModalRegistroUsuario
        );
    }

    
});

    // Cerrar el modal después de modificar un usuario.
    document.addEventListener('DOMContentLoaded', function () {

        const modalModificacion = document.getElementById('modal-modificar-usuario');
        const botonAceptarModificacion = document.getElementById('aceptar-modal-modificacion');

        // Configurar el cierre cuando ambos elementos estén disponibles.
        if (modalModificacion && botonAceptarModificacion) {

            botonAceptarModificacion.addEventListener('click', function () {
                // Cerrar el modal.
                modalModificacion.remove();
                // Permitir nuevamente el desplazamiento de la página.
                document.body.style.overflow = '';

            });

        }

    });