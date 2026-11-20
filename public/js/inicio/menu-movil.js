document.addEventListener('DOMContentLoaded', function () {

    // ELEMENTOS DEL MENÚ LATERAL
    const appLayout = document.querySelector('.app-layout');
    const botonMenu = document.getElementById('boton-menu-lateral');
    const iconoMenu = botonMenu?.querySelector('i');

    if (!appLayout || !botonMenu || !iconoMenu) {
        return;
    }

    // Detectar pantallas pequeñas.
    const esPantallaPequena = () => window.innerWidth <= 767.98;

    // ACTUALIZAR EL BOTÓN
    function actualizarBoton() {

        const menuAbierto = appLayout.classList.contains('menu-movil-expandido');

        iconoMenu.className = menuAbierto ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';

        botonMenu.setAttribute('aria-expanded', String(menuAbierto));
        botonMenu.setAttribute( 'aria-label', menuAbierto ? 'Cerrar menú lateral' : 'Abrir menú lateral' );
    }

    // ABRIR O CERRAR MENÚ EN PANTALLAS PEQUEÑAS
    botonMenu.addEventListener('click', function () {

        if (!esPantallaPequena()) {
            return;
        }

        appLayout.classList.toggle('menu-movil-expandido');

        actualizarBoton();
    });

    // CERRAR AL SELECCIONAR UNA OPCIÓN
    const enlacesMenu = document.querySelectorAll( '.sidebar .menu-item, .sidebar .submenu-item' );

    enlacesMenu.forEach(function (enlace) {

        enlace.addEventListener('click', function () {

            if (esPantallaPequena()) {
                appLayout.classList.remove('menu-movil-expandido');
                actualizarBoton();
            }

        });

    });

    // AJUSTAR AL CAMBIAR EL TAMAÑO DE PANTALLA
    window.addEventListener('resize', function () {

        if (!esPantallaPequena()) {
            appLayout.classList.remove('menu-movil-expandido');
        }

        actualizarBoton();

    });

    // ESTADO INICIAL
    appLayout.classList.remove('menu-movil-expandido');

    actualizarBoton();

});
