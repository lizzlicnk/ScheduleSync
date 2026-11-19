
document.addEventListener('DOMContentLoaded', function () {

    // ========================================
    // ELEMENTOS PRINCIPALES DE LA TABLA
    // ========================================

    const buscarHorario = document.getElementById('buscar-horario');
    const cuerpoTabla = document.getElementById('cuerpo-tabla-horarios');
    const totalResultados = document.getElementById('total-resultados-horarios');

    // Elementos del panel de filtros.
    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const limpiarFiltros = document.getElementById('limpiar-filtros');

    // Campos de los filtros.
    const filtroPeriodo = document.getElementById('filtro-periodo');
    const filtroCuatrimestre = document.getElementById('filtro-cuatrimestre');
    const filtroCarrera = document.getElementById('filtro-carrera');
    const filtroHorarioCreado = document.getElementById('filtro-horario-creado');
    const filtroEstadoHorario = document.getElementById('filtro-estado-horario');

    // Elementos de paginación.
    const informacionPagina = document.getElementById('informacion-pagina-horarios');
    const paginaAnterior = document.getElementById('pagina-anterior-horarios');
    const paginaSiguiente = document.getElementById('pagina-siguiente-horarios');

    // Elementos del registro.
    const botonMostrarRegistro = document.getElementById('boton-mostrar-registro');
    const botonCancelarRegistro = document.getElementById('boton-cancelar-registro');
    const contenedorRegistro = document.getElementById('contenedor-registro-horario');

    // Obtener las filas correspondientes a los grupos.
    const filasHorarios = cuerpoTabla
        ? cuerpoTabla.querySelectorAll('tr[data-grupo]')
        : [];

    // Cantidad de registros por página.
    const horariosPorPagina = 5;

    // Página inicial.
    let paginaActual = 1;

    // ========================================
    // NORMALIZACIÓN DE TEXTO
    // ========================================

    /**
     * Descripción:
     * Normaliza un texto para facilitar las búsquedas.
     *
     * Entradas:
     * - texto: Valor que se desea normalizar.
     *
     * Salidas:
     * - Retorna el texto en minúsculas y sin acentos.
     */
    function normalizarTexto(texto) {

        return String(texto ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    // ========================================
    // OBTENER HORARIOS FILTRADOS
    // ========================================

    /**
     * Descripción:
     * Obtiene los horarios que coinciden con la búsqueda
     * y los filtros seleccionados.
     *
     * Salidas:
     * - Retorna las filas que cumplen los criterios.
     */
    function obtenerHorariosFiltrados() {

        const busqueda = normalizarTexto(buscarHorario?.value);

        return Array.from(filasHorarios).filter(function (fila) {

            // Comprobar si coincide con la búsqueda.
            const coincideBusqueda =
                normalizarTexto(fila.dataset.nombre).includes(busqueda) ||
                normalizarTexto(fila.dataset.grupo).includes(busqueda) ||
                normalizarTexto(fila.dataset.nombreCarrera).includes(busqueda);

            // Comprobar el periodo escolar.
            const coincidePeriodo =
                !filtroPeriodo?.value ||
                fila.dataset.periodo === filtroPeriodo.value;

            // Comprobar el cuatrimestre.
            const coincideCuatrimestre =
                !filtroCuatrimestre?.value ||
                fila.dataset.cuatrimestre === filtroCuatrimestre.value;

            // Comprobar la carrera.
            const coincideCarrera =
                !filtroCarrera?.value ||
                fila.dataset.carrera === filtroCarrera.value;

            // Comprobar si tiene un horario creado.
            const coincideHorarioCreado =
                !filtroHorarioCreado?.value ||
                fila.dataset.horarioCreado === filtroHorarioCreado.value;

            // Comprobar el estado del horario.
            const coincideEstadoHorario =
                !filtroEstadoHorario?.value ||
                fila.dataset.estadoHorario === filtroEstadoHorario.value;

            return (
                coincideBusqueda &&
                coincidePeriodo &&
                coincideCuatrimestre &&
                coincideCarrera &&
                coincideHorarioCreado &&
                coincideEstadoHorario
            );
        });
    }

    // ========================================
    // ACTUALIZAR TABLA Y PAGINACIÓN
    // ========================================

    /**
     * Descripción:
     * Actualiza las filas visibles y la información
     * de paginación de la tabla.
     */
    function actualizarTabla() {

        if (!cuerpoTabla) {
            return;
        }

        const horariosFiltrados = obtenerHorariosFiltrados();

        const totalPaginas = Math.max(
            1,
            Math.ceil(horariosFiltrados.length / horariosPorPagina)
        );

        // Evitar permanecer en una página inexistente.
        if (paginaActual > totalPaginas) {
            paginaActual = totalPaginas;
        }

        // Ocultar todas las filas.
        filasHorarios.forEach(function (fila) {
            fila.hidden = true;
        });

        // Calcular los registros de la página actual.
        const inicio = (paginaActual - 1) * horariosPorPagina;
        const fin = inicio + horariosPorPagina;

        // Mostrar las filas correspondientes.
        horariosFiltrados.slice(inicio, fin).forEach(function (fila) {
            fila.hidden = false;
        });

        // Actualizar el total de resultados.
        if (totalResultados) {
            totalResultados.textContent = horariosFiltrados.length;
        }

        // Actualizar la información de la página.
        if (informacionPagina) {
            informacionPagina.textContent =
                `Página ${paginaActual} de ${totalPaginas}`;
        }

        // Controlar los botones de paginación.
        if (paginaAnterior) {
            paginaAnterior.disabled = paginaActual === 1;
        }

        if (paginaSiguiente) {
            paginaSiguiente.disabled = paginaActual === totalPaginas;
        }

        // Mostrar el mensaje cuando no existen coincidencias.
        const filaSinResultados = document.getElementById(
            'fila-sin-resultados-horarios'
        );

        if (filaSinResultados) {
            filaSinResultados.hidden = horariosFiltrados.length !== 0;
        }
    }

    // ========================================
    // MOSTRAR Y OCULTAR FILTROS
    // ========================================

    if (botonFiltros && contenedorFiltros) {

        botonFiltros.addEventListener('click', function () {

            contenedorFiltros.hidden = !contenedorFiltros.hidden;

            const filtrosActivos = !contenedorFiltros.hidden;

            botonFiltros.setAttribute(
                'aria-expanded',
                String(filtrosActivos)
            );

            botonFiltros.classList.toggle(
                'boton-activo',
                filtrosActivos
            );
        });
    }

    // ========================================
    // EVENTOS DE BÚSQUEDA Y FILTROS
    // ========================================

    if (buscarHorario) {

        buscarHorario.addEventListener('input', function () {

            paginaActual = 1;

            actualizarTabla();
        });
    }

    // Actualizar la tabla cuando cambia algún filtro.
    [
        filtroPeriodo,
        filtroCuatrimestre,
        filtroCarrera,
        filtroHorarioCreado,
        filtroEstadoHorario
    ].forEach(function (filtro) {

        if (filtro) {

            filtro.addEventListener('change', function () {

                paginaActual = 1;

                actualizarTabla();
            });
        }
    });

    // ========================================
    // LIMPIAR FILTROS
    // ========================================

    if (limpiarFiltros) {

        limpiarFiltros.addEventListener('click', function () {

            if (filtroPeriodo) filtroPeriodo.value = '';
            if (filtroCuatrimestre) filtroCuatrimestre.value = '';
            if (filtroCarrera) filtroCarrera.value = '';
            if (filtroHorarioCreado) filtroHorarioCreado.value = '';
            if (filtroEstadoHorario) filtroEstadoHorario.value = '';

            if (buscarHorario) {
                buscarHorario.value = '';
            }

            paginaActual = 1;

            actualizarTabla();
        });
    }

    // ========================================
    // PAGINACIÓN
    // ========================================

    // Cambiar a la página anterior.
    if (paginaAnterior) {

        paginaAnterior.addEventListener('click', function () {

            if (paginaActual > 1) {

                paginaActual--;

                actualizarTabla();
            }
        });
    }

    // Cambiar a la página siguiente.
    if (paginaSiguiente) {

        paginaSiguiente.addEventListener('click', function () {

            const totalPaginas = Math.max(
                1,
                Math.ceil(
                    obtenerHorariosFiltrados().length / horariosPorPagina
                )
            );

            if (paginaActual < totalPaginas) {

                paginaActual++;

                actualizarTabla();
            }
        });
    }

    // Inicializar la tabla.
    actualizarTabla();

    // ========================================
    // MOSTRAR Y CANCELAR REGISTRO
    // ========================================

    if (
        botonMostrarRegistro &&
        botonCancelarRegistro &&
        contenedorRegistro
    ) {

        // Mostrar el formulario de registro.
        botonMostrarRegistro.addEventListener('click', function () {

            contenedorRegistro.hidden = false;

            botonMostrarRegistro.setAttribute(
                'aria-expanded',
                'true'
            );

            contenedorRegistro.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });

        // Ocultar el formulario de registro.
        botonCancelarRegistro.addEventListener('click', function () {

            contenedorRegistro.hidden = true;

            botonMostrarRegistro.setAttribute(
                'aria-expanded',
                'false'
            );
        });
    }

    // ========================================
    // CONSULTA DE ASIGNATURAS POR HORARIO
    // ========================================

    // Elemento para seleccionar el horario.
    const horarioRegistro = document.getElementById('horario-registro');

    // Contenedor de las asignaturas disponibles.
    const listaAsignaturas = document.getElementById(
        'lista-asignaturas-disponibles'
    );

    // Contenedor de los detalles del horario.
    const detallesHorario = document.getElementById(
        'detalles-horario-registro'
    );

    // Botón para guardar el horario.
    const botonGuardarHorario = document.getElementById(
        'boton-guardar-horario'
    );

    // Almacenar las asignaturas consultadas.
    let asignaturasDisponibles = [];

    // Comprobar que existan los elementos necesarios.
    if (horarioRegistro && listaAsignaturas) {

        // Consultar las asignaturas cuando cambia el horario.
        horarioRegistro.addEventListener('change', async function () {

            const idHorario = this.value;

            // Limpiar los detalles anteriores.
            if (detallesHorario) {
                detallesHorario.innerHTML = '';
            }

            // Limpiar las disponibilidades anteriores.
            window.disponibilidadesDocentes = [];

            // Deshabilitar el botón de guardar.
            if (botonGuardarHorario) {
                botonGuardarHorario.disabled = true;
            }

            // Limpiar las asignaturas anteriores.
            asignaturasDisponibles = [];

            // Restablecer el mensaje inicial.
            listaAsignaturas.innerHTML = `
                <p class="texto-secundario">
                    Selecciona un horario para consultar sus asignaturas.
                </p>
            `;

            // Si no existe un horario seleccionado, finalizar.
            if (!idHorario) {
                return;
            }

            // Mostrar mensaje de consulta.
            listaAsignaturas.innerHTML = `
                <p class="texto-secundario">
                    Consultando asignaturas...
                </p>
            `;

            try {

                // Construir la dirección de consulta.
                const url = this.dataset.url.replace(
                    'HORARIO_ID',
                    encodeURIComponent(idHorario)
                );

                // Consultar las asignaturas al controlador.
                const respuesta = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const datos = await respuesta.json();

                // Comprobar si la consulta fue exitosa.
                if (!respuesta.ok) {

                    throw new Error(
                        datos.mensaje ||
                        'No fue posible consultar las asignaturas.'
                    );
                }

                // Actualizar las disponibilidades confirmadas.
                window.disponibilidadesDocentes =
                    datos.disponibilidadesDocentes || [];

                // Comprobar si existen asignaturas.
                if (
                    !datos.asignaturas ||
                    datos.asignaturas.length === 0
                ) {

                    asignaturasDisponibles = [];

                    listaAsignaturas.innerHTML = `
                        <p class="texto-secundario">
                            No hay asignaturas activas para la carrera
                            y cuatrimestre de este grupo.
                        </p>
                    `;

                    return;
                }

                // Guardar las asignaturas recibidas.
                asignaturasDisponibles = datos.asignaturas;

                // Limpiar el contenido anterior.
                listaAsignaturas.innerHTML = '';

                // Crear las tarjetas de las asignaturas.
                asignaturasDisponibles.forEach(function (asignatura) {

                    const tarjeta = crearTarjetaAsignatura(asignatura);

                    listaAsignaturas.appendChild(tarjeta);
                });

            } catch (error) {

                // Limpiar las asignaturas anteriores.
                asignaturasDisponibles = [];

                // Limpiar las disponibilidades.
                window.disponibilidadesDocentes = [];

                // Mostrar el mensaje de error.
                listaAsignaturas.innerHTML = '';

                const mensaje = document.createElement('p');

                mensaje.classList.add('texto-secundario');

                mensaje.textContent = error.message;

                listaAsignaturas.appendChild(mensaje);

                console.error(
                    'Error al consultar asignaturas:',
                    error
                );
            }
        });
    }

    // ========================================
    // CARGA AUTOMÁTICA DEL HORARIO SELECCIONADO
    // ========================================

    // Consultar automáticamente si ya existe un horario seleccionado.
    if (horarioRegistro && horarioRegistro.value !== '') {

        horarioRegistro.dispatchEvent(
            new Event('change')
        );
    }

// ========================================
// CREACIÓN DE TARJETAS DE ASIGNATURAS
// ========================================

/**
 * Descripción:
 * Construye la tarjeta visual de una asignatura,
 * incluyendo sus horas, docentes y espacios disponibles.
 *
 * Entradas:
 * - asignatura: Información de la asignatura y sus docentes.
 *
 * Salidas:
 * - Retorna un elemento HTML con la información de la asignatura.
 */
function crearTarjetaAsignatura(asignatura) {

    // ========================================
    // CONTENEDOR PRINCIPAL
    // ========================================

    const tarjeta = document.createElement('div');

    tarjeta.classList.add('tarjeta-asignatura-disponible');

    // Guardar los datos de la asignatura.
    tarjeta.dataset.idAsignatura = asignatura.id_asignatura;

    // Guardar las horas disponibles.
    tarjeta.dataset.cantidadHoras = asignatura.cantidadHoras;

    // Conservar las horas totales originales.
    tarjeta.dataset.horasTotales = asignatura.cantidadHoras;

    // ========================================
    // NOMBRE DE LA ASIGNATURA
    // ========================================

    const nombre = document.createElement('h4');

    nombre.classList.add('nombre-asignatura-tarjeta');

    nombre.textContent = asignatura.nombreAsignatura;

    // ========================================
    // TOTAL DE HORAS
    // ========================================

    const contenedorHoras = document.createElement('div');

    contenedorHoras.classList.add('informacion-asignatura');

    const etiquetaHoras = document.createElement('strong');

    etiquetaHoras.textContent = 'Horas disponibles: ';

    const cantidadHoras = document.createElement('span');

    cantidadHoras.textContent =
        `${asignatura.cantidadHoras} horas`;

    contenedorHoras.appendChild(etiquetaHoras);
    contenedorHoras.appendChild(cantidadHoras);

    // ========================================
    // SELECCIÓN DEL DOCENTE
    // ========================================

    const contenedorDocente = document.createElement('div');

    contenedorDocente.classList.add('campo-tarjeta-asignatura');

    const etiquetaDocente = document.createElement('label');

    etiquetaDocente.textContent = 'Docente';

    const selectorDocente = document.createElement('select');

    selectorDocente.classList.add('selector-docente');

    selectorDocente.required = true;

    // Opción inicial.
    const opcionInicialDocente = document.createElement('option');

    opcionInicialDocente.value = '';
    opcionInicialDocente.textContent = 'Selecciona un docente';

    selectorDocente.appendChild(opcionInicialDocente);

    // Obtener los docentes asociados con la asignatura.
    const docentes = asignatura.docentes || [];

    // Agregar las opciones de los docentes.
    docentes.forEach(function (docente) {

        const opcion = document.createElement('option');

        opcion.value = docente.id_docente;

        // Guardar la carga académica máxima del docente.
        opcion.dataset.cargaAcademica =
            Number(docente.cargaAcademica) || 0;

        // Guardar las horas asignadas en otros horarios.
        opcion.dataset.horasAsignadasOtrosHorarios =
            Number(docente.horasAsignadasOtrosHorarios) || 0;

        // Guardar las horas disponibles en otros horarios.
        opcion.dataset.horasDisponibles =
            Number(docente.horasDisponibles) || 0;

        // Comprobar si el docente ya completó su carga académica.
        if (Number(docente.horasDisponibles) <= 0) {

            opcion.disabled = true;

            opcion.textContent =
                `${docente.nombreCompleto} (Carga académica completa)`;

        } else {

            opcion.textContent =
                `${docente.nombreCompleto} (${docente.horasDisponibles} h disponibles)`;
        }

        selectorDocente.appendChild(opcion);
    });

    // Informar cuando no existen docentes asociados.
    if (docentes.length === 0) {

        const opcionSinDocentes = document.createElement('option');

        opcionSinDocentes.value = '';
        opcionSinDocentes.textContent = 'Sin docentes disponibles';
        opcionSinDocentes.disabled = true;

        selectorDocente.appendChild(opcionSinDocentes);
    }

    contenedorDocente.appendChild(etiquetaDocente);
    contenedorDocente.appendChild(selectorDocente);

    // ========================================
    // SELECCIÓN DEL ESPACIO
    // ========================================

    const contenedorEspacio = document.createElement('div');

    contenedorEspacio.classList.add('campo-tarjeta-asignatura');

    const etiquetaEspacio = document.createElement('label');

    etiquetaEspacio.textContent = 'ESPACIO';

    const selectorEspacio = document.createElement('select');

    selectorEspacio.classList.add('selector-espacio');

    selectorEspacio.required = true;

    // Opción inicial.
    const opcionInicialEspacio = document.createElement('option');

    opcionInicialEspacio.value = '';
    opcionInicialEspacio.textContent = 'Selecciona un espacio';

    // Opción Aula.
    const opcionAula = document.createElement('option');

    opcionAula.value = 'Aula';
    opcionAula.textContent = 'Aula';

    // Opción Laboratorio.
    const opcionLaboratorio = document.createElement('option');

    opcionLaboratorio.value = 'Laboratorio';
    opcionLaboratorio.textContent = 'Laboratorio';

    selectorEspacio.appendChild(opcionInicialEspacio);
    selectorEspacio.appendChild(opcionAula);
    selectorEspacio.appendChild(opcionLaboratorio);

    contenedorEspacio.appendChild(etiquetaEspacio);
    contenedorEspacio.appendChild(selectorEspacio);

    // ========================================
    // AGREGAR ELEMENTOS A LA TARJETA
    // ========================================

    tarjeta.appendChild(nombre);
    tarjeta.appendChild(contenedorHoras);
    tarjeta.appendChild(contenedorDocente);
    tarjeta.appendChild(contenedorEspacio);

    return tarjeta;
}


});
