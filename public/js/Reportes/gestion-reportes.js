/* =====================================================
   GESTIÓN DE FILTROS DE REPORTES
===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // Obtener todos los botones de filtros de las tarjetas.
    const botonesFiltros = document.querySelectorAll('.boton-filtros-reporte');

    // Recorrer los botones para agregar su funcionamiento.
    botonesFiltros.forEach(function (boton) {

        // Identificar el panel de filtros correspondiente.
        const identificador = boton.dataset.filtros;

        const panelFiltros = document.getElementById(identificador);

        // Comprobar que exista el panel relacionado con el botón.
        if (!panelFiltros) {
            return;
        }

        // Mostrar u ocultar los filtros al presionar el botón.
        boton.addEventListener('click', function () {

            const estaAbierto = boton.getAttribute('aria-expanded') === 'true';

            // Cambiar el estado de visibilidad del panel.
            panelFiltros.hidden = estaAbierto;

            // Actualizar el estado accesible del botón.
            boton.setAttribute('aria-expanded', String(!estaAbierto));

        });

    });

});