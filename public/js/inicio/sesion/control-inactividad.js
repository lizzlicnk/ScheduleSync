
document.addEventListener('DOMContentLoaded', function () {
    // Descripción: Obtiene el token de seguridad de Laravel.
    const tokenCSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    // Descripción: Define las rutas para controlar la sesión.
    const rutaActividad = '/sesion/actividad';
    const rutaCerrarSesion = '/logout';
    const rutaLogin = '/login';

    // Descripción: Establece el límite de inactividad en 20 minutos.
    const tiempoMaximoInactividad = 20 * 60 * 1000;

    // Descripción: Guarda los temporizadores y el estado de la sesión.
    let temporizadorInactividad;
    let ultimaActividad = Date.now();
    let sesionCerrada = false;
    let enviandoActividad = false;

    // Descripción: Cierra la sesión cuando se cumple el tiempo de inactividad.
    async function cerrarSesionPorInactividad() {
        if (sesionCerrada) {
            return;
        }

        sesionCerrada = true;
        clearTimeout(temporizadorInactividad);

        try {
            if (tokenCSRF) {
                await fetch(rutaCerrarSesion, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': tokenCSRF,
                        'Accept': 'application/json'
                    }
                });
            }
        } catch (error) {
            console.error('No se pudo cerrar la sesión mediante la solicitud.', error);
        } finally {
            // Descripción: Regresa al login para mostrar el modal de sesión caducada.
            window.location.replace(rutaLogin + '?sesion=caducada');
        }
    }

    // Descripción: Reinicia el temporizador al detectar actividad del usuario.
    function registrarActividad() {
        if (sesionCerrada) {
            return;
        }

        ultimaActividad = Date.now();

        clearTimeout(temporizadorInactividad);

        temporizadorInactividad = setTimeout(
            cerrarSesionPorInactividad,
            tiempoMaximoInactividad
        );
    }

    // Descripción: Envía una solicitud periódica mientras el usuario está activo.
    async function actualizarActividadServidor() {
        if (
            sesionCerrada ||
            enviandoActividad ||
            !tokenCSRF ||
            Date.now() - ultimaActividad >= 60000
        ) {
            return;
        }

        enviandoActividad = true;

        try {
            const respuesta = await fetch(rutaActividad, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': tokenCSRF,
                    'Accept': 'application/json'
                }
            });

            // Descripción: Si Laravel ya no reconoce la sesión, regresa al login.
            if (respuesta.status === 401 || respuesta.redirected) {
                sesionCerrada = true;
                window.location.replace(rutaLogin + '?sesion=caducada');
            }
        } catch (error) {
            console.error('No se pudo actualizar la actividad de la sesión.', error);
        } finally {
            enviandoActividad = false;
        }
    }

    // Descripción: Detecta acciones que indican que el usuario está utilizando el sistema.
    [
        'mousemove',
        'mousedown',
        'keydown',
        'scroll',
        'touchstart',
        'click'
    ].forEach(function (tipoEvento) {
        document.addEventListener(tipoEvento, registrarActividad, {
            passive: true
        });
    });

    // Descripción: Revisa la actividad del usuario cada 30 segundos.
    setInterval(actualizarActividadServidor, 30000);

    // Descripción: Inicia el temporizador al cargar una página protegida.
    registrarActividad();
});
