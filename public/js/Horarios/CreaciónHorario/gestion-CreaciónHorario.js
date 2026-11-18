document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos principales de la tabla.
    const buscarHorario = document.getElementById('buscar-horario');
    const cuerpoTabla = document.getElementById('cuerpo-tabla-horarios');
    const totalResultados = document.getElementById('total-resultados-horarios');

    // Obtiene los elementos del panel de filtros.
    const botonFiltros = document.getElementById('boton-filtros');
    const contenedorFiltros = document.getElementById('contenedor-filtros');
    const limpiarFiltros = document.getElementById('limpiar-filtros');

    // Obtiene los campos de los filtros.
    const filtroPeriodo = document.getElementById('filtro-periodo');
    const filtroCuatrimestre = document.getElementById('filtro-cuatrimestre');
    const filtroCarrera = document.getElementById('filtro-carrera');
    const filtroHorarioCreado = document.getElementById('filtro-horario-creado');
    const filtroEstadoHorario = document.getElementById('filtro-estado-horario');

    // Obtiene los elementos de paginación.
    const informacionPagina = document.getElementById('informacion-pagina-horarios');
    const paginaAnterior = document.getElementById('pagina-anterior-horarios');
    const paginaSiguiente = document.getElementById('pagina-siguiente-horarios');

    // Obtener los elementos del registro.
    const botonMostrarRegistro = document.getElementById('boton-mostrar-registro');
    const botonCancelarRegistro = document.getElementById('boton-cancelar-registro');
    const contenedorRegistro = document.getElementById('contenedor-registro-horario');


    // Obtiene las filas correspondientes a los grupos.
    const filasHorarios = cuerpoTabla.querySelectorAll('tr[data-grupo]');

    // Define la cantidad de registros por página.
    const horariosPorPagina = 5;

    // Establece la página inicial.
    let paginaActual = 1;

    // Normaliza el texto para facilitar las búsquedas.
    function normalizarTexto(texto) {
        return String(texto ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    // Obtiene los grupos que coinciden con la búsqueda y los filtros.
    function obtenerHorariosFiltrados() {

        const busqueda = normalizarTexto(buscarHorario.value);

        return Array.from(filasHorarios).filter(function (fila) {

            // Comprueba si el grupo coincide con la búsqueda.
            const coincideBusqueda =
                normalizarTexto(fila.dataset.nombre).includes(busqueda) ||
                normalizarTexto(fila.dataset.grupo).includes(busqueda) ||
                normalizarTexto(fila.dataset.nombreCarrera).includes(busqueda);

            // Comprueba el periodo escolar.
            const coincidePeriodo = !filtroPeriodo.value || fila.dataset.periodo === filtroPeriodo.value;

            // Comprueba el cuatrimestre.
            const coincideCuatrimestre = !filtroCuatrimestre.value || fila.dataset.cuatrimestre === filtroCuatrimestre.value;

            // Comprueba la carrera.
            const coincideCarrera = !filtroCarrera.value || fila.dataset.carrera === filtroCarrera.value;

            // Comprueba si el grupo tiene un horario creado.
            const coincideHorarioCreado = !filtroHorarioCreado.value || fila.dataset.horarioCreado === filtroHorarioCreado.value;

            // Comprueba el estado del horario.
            const coincideEstadoHorario = !filtroEstadoHorario.value || fila.dataset.estadoHorario === filtroEstadoHorario.value;

            return coincideBusqueda && coincidePeriodo && coincideCuatrimestre && coincideCarrera && coincideHorarioCreado && coincideEstadoHorario;
        });
    }

    // Actualiza los registros visibles y la paginación.
    function actualizarTabla() {

        const horariosFiltrados = obtenerHorariosFiltrados();

        const totalPaginas = Math.max(
            1,
            Math.ceil(horariosFiltrados.length / horariosPorPagina)
        );

        // Evita permanecer en una página que ya no existe.
        if (paginaActual > totalPaginas) {
            paginaActual = totalPaginas;
        }

        // Oculta todas las filas.
        filasHorarios.forEach(function (fila) {
            fila.hidden = true;
        });

        // Calcula los registros que corresponden a la página actual.
        const inicio = (paginaActual - 1) * horariosPorPagina;
        const fin = inicio + horariosPorPagina;

        // Muestra los registros correspondientes.
        horariosFiltrados.slice(inicio, fin).forEach(function (fila) {
            fila.hidden = false;
        });

        // Actualiza el total de resultados.
        totalResultados.textContent = horariosFiltrados.length;

        // Actualiza la información de la página.
        informacionPagina.textContent =
            `Página ${paginaActual} de ${totalPaginas}`;

        // Controla los botones de paginación.
        paginaAnterior.disabled = paginaActual === 1;
        paginaSiguiente.disabled = paginaActual === totalPaginas;

        // Muestra el mensaje cuando no existen coincidencias.
        const filaSinResultados = document.getElementById(
            'fila-sin-resultados-horarios'
        );

        if (filaSinResultados) {
            filaSinResultados.hidden = horariosFiltrados.length !== 0;
        }
    }

    // Muestra u oculta el panel de filtros.
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

    // Actualiza la tabla al realizar una búsqueda.
    buscarHorario.addEventListener('input', function () {
        paginaActual = 1;
        actualizarTabla();
    });

    // Actualiza la tabla cuando cambia algún filtro.
    [
        filtroPeriodo,
        filtroCuatrimestre,
        filtroCarrera,
        filtroHorarioCreado,
        filtroEstadoHorario
    ].forEach(function (filtro) {

        filtro.addEventListener('change', function () {
            paginaActual = 1;
            actualizarTabla();
        });
    });

    // Limpia los filtros y la búsqueda.
    limpiarFiltros.addEventListener('click', function () {

        filtroPeriodo.value = '';
        filtroCuatrimestre.value = '';
        filtroCarrera.value = '';
        filtroHorarioCreado.value = '';
        filtroEstadoHorario.value = '';

        buscarHorario.value = '';

        paginaActual = 1;

        actualizarTabla();
    });

    // Cambia a la página anterior.
    paginaAnterior.addEventListener('click', function () {

        if (paginaActual > 1) {
            paginaActual--;
            actualizarTabla();
        }
    });

    // Cambia a la página siguiente.
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

    // Inicializa la tabla.
    actualizarTabla();



    if (botonMostrarRegistro && botonCancelarRegistro && contenedorRegistro) {

        botonMostrarRegistro.addEventListener('click', function () {
            contenedorRegistro.hidden = false;
            botonMostrarRegistro.setAttribute('aria-expanded', 'true');
            contenedorRegistro.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        botonCancelarRegistro.addEventListener('click', function () {
            contenedorRegistro.hidden = true;
            botonMostrarRegistro.setAttribute('aria-expanded', 'false');
        });
    }

    
    // ========================================
    // CONSULTA DE ASIGNATURAS POR HORARIO
    // ========================================

    // Obtiene los elementos de selección y visualización.
    const horarioRegistro = document.getElementById('horario-registro');

    const listaAsignaturas = document.getElementById(
        'lista-asignaturas-disponibles'
    );

    // Obtiene el contenedor de los detalles del horario.
    const detallesHorario = document.getElementById(
        'detalles-horario-registro'
    );

    // Obtiene el botón para guardar el horario.
    const botonGuardarHorario = document.getElementById(
        'boton-guardar-horario'
    );

    // Almacena las asignaturas consultadas.
    let asignaturasDisponibles = [];

    // Comprueba que los elementos existan.
    if (horarioRegistro && listaAsignaturas) {

        // Consulta las asignaturas cuando cambia el horario seleccionado.
        horarioRegistro.addEventListener('change', async function () {

            const idHorario = this.value;

            // Limpiar los detalles anteriores.
            detallesHorario.innerHTML = '';

            // Deshabilitar el botón de guardar.
            botonGuardarHorario.disabled = true;

            // Si no hay un horario seleccionado, limpia la lista.
            if (!idHorario) {

                asignaturasDisponibles = [];

                listaAsignaturas.innerHTML = `
                    <p class="texto-secundario">
                        Selecciona un horario para consultar sus asignaturas.
                    </p>
                `;

                return;
            }

            // Mostrar mensaje mientras se consultan las asignaturas.
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

                // Comprobar si existen asignaturas disponibles.
                if (!datos.asignaturas || datos.asignaturas.length === 0) {

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

                // Generar las tarjetas de las asignaturas.
                asignaturasDisponibles.forEach(function (asignatura) {

                    const tarjeta = crearTarjetaAsignatura(asignatura);

                    listaAsignaturas.appendChild(tarjeta);

                });

            } catch (error) {

                // Limpiar las asignaturas anteriores.
                asignaturasDisponibles = [];

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
    // CREACIÓN DE TARJETAS DE ASIGNATURAS
    // ========================================

    /**
     * Descripción: Construye la tarjeta visual de una asignatura.
     * Entradas: Recibe la información de la asignatura y sus docentes.
     * Salidas: Retorna un elemento HTML con la información y selectores.
     */
    function crearTarjetaAsignatura(asignatura) {

        // Crear el contenedor principal.
        const tarjeta = document.createElement('div');

        tarjeta.classList.add('tarjeta-asignatura-disponible');

        // Guardar los datos de la asignatura en la tarjeta.
        tarjeta.dataset.idAsignatura = asignatura.id_asignatura;

        // Guardar las horas que quedan disponibles.
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

        const opcionInicialDocente = document.createElement('option');

        opcionInicialDocente.value = '';
        opcionInicialDocente.textContent = 'Selecciona un docente';

        selectorDocente.appendChild(opcionInicialDocente);

        // Agregar los docentes disponibles.
        (asignatura.docentes || []).forEach(function (docente) {

            const opcion = document.createElement('option');

            opcion.value = docente.id_docente;

            opcion.textContent = docente.nombreCompleto;

            selectorDocente.appendChild(opcion);
        });

        // Si no existen docentes disponibles, informar al usuario.
        if (!asignatura.docentes || asignatura.docentes.length === 0) {

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

        // ========================================
        // VALIDAR SELECCIONES ANTES DE ARRASTRAR
        // ========================================

    

        return tarjeta;
    }




});
