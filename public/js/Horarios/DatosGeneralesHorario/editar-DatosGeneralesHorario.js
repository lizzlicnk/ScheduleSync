document.addEventListener('DOMContentLoaded', function () {

    // Obtiene los elementos del formulario.
    const formularioEditar = document.getElementById('formEditarHorario');
    const filtroPeriodo = document.getElementById('id_periodo');
    const filtroCarrera = document.getElementById('id_carrera');
    const filtroGrupo = document.getElementById('id_grupo');

    // Filtra los grupos según el periodo y la carrera seleccionados.
    function actualizarGrupos() {

        // Comprueba que los selectores estén disponibles.
        if (!filtroPeriodo || !filtroCarrera || !filtroGrupo) {
            return;
        }

        const periodoSeleccionado = filtroPeriodo.value;
        const carreraSeleccionada = filtroCarrera.value;

        const opcionesGrupo = filtroGrupo.querySelectorAll('option');

        // Guarda el grupo seleccionado actualmente.
        const grupoSeleccionado = filtroGrupo.value;

        let grupoDisponible = false;

        opcionesGrupo.forEach(function (opcion, indice) {

            // Mantiene visible la opción inicial.
            if (indice === 0) {
                opcion.hidden = false;
                return;
            }

            // Obtiene los datos del grupo.
            const periodoGrupo = opcion.dataset.periodo;
            const carreraGrupo = opcion.dataset.carrera;

            // Comprueba si corresponde al periodo y carrera seleccionados.
            const coincidePeriodo = periodoGrupo === periodoSeleccionado;
            const coincideCarrera = carreraGrupo === carreraSeleccionada;

            const coincide = coincidePeriodo && coincideCarrera;

            // Muestra u oculta la opción.
            opcion.hidden = !coincide;

            // Comprueba si el grupo seleccionado continúa disponible.
            if (coincide && opcion.value === grupoSeleccionado) {
                grupoDisponible = true;
            }
        });

        // Conserva el grupo si todavía corresponde a los filtros.
        if (!grupoDisponible) {
            filtroGrupo.value = '';
        }
    }

    // Actualiza los grupos cuando cambia el periodo.
    if (filtroPeriodo) {
        filtroPeriodo.addEventListener('change', actualizarGrupos);
    }

    // Actualiza los grupos cuando cambia la carrera.
    if (filtroCarrera) {
        filtroCarrera.addEventListener('change', actualizarGrupos);
    }

    // Aplica los filtros al cargar el formulario.
    actualizarGrupos();

    // Controla el modal de modificación exitosa.
    const modalModificar = document.getElementById('modal-modificar-horario');
    const botonAceptar = document.getElementById('aceptar-modal-modificacion');

    if (modalModificar && botonAceptar) {

        botonAceptar.addEventListener('click', function () {
            // Regresa al listado de horarios.
            window.location.href = '/horarios/datos-generales';
        });
    }

});
