// Ejecuta el código cuando el documento HTML termina de cargarse.
document.addEventListener('DOMContentLoaded', function () {
    // Obtiene el token de seguridad.
    const tokenCSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    // Define las rutas para controlar la sesión.
    const rutaActividad = '/sesion/actividad';
    const rutaCerrarSesion = '/logout';
    const rutaLogin = '/login';

    // Establece el límite de inactividad en 20 minutos.
    const tiempoMaximoInactividad = 20 * 60 * 1000;

    // Guarda los temporizadores y el estado de la sesión.
    let temporizadorInactividad;
    let ultimaActividad = Date.now();
    let sesionCerrada = false;
    let enviandoActividad = false;

    // Cierra la sesión cuando se cumple el tiempo de inactividad.
    async function cerrarSesionPorInactividad() {
        if (sesionCerrada) {
            return;
        }

        //  Marca la sesión como cerrada y detiene el temporizador.
        sesionCerrada = true;
        clearTimeout(temporizadorInactividad);

        try {
             // Envía la solicitud para cerrar la sesión  si existe el token CSRF.
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
            // Registra el error si no se puede enviar la solicitud de cierre de sesión.
            console.error('No se pudo cerrar la sesión mediante la solicitud.', error);
        } finally {
            //  Regresa al login para mostrar el modal de sesión caducada.
            window.location.replace(rutaLogin + '?sesion=caducada');
        }
    }

    // Reinicia el temporizador al detectar actividad del usuario.
    function registrarActividad() {

        // Evita registrar actividad cuando la sesión ya está cerrada.
        if (sesionCerrada) {
            return;
        }

        ultimaActividad = Date.now();

        // Cancela el temporizador anterior para iniciar uno nuevo.
        clearTimeout(temporizadorInactividad);

        // Programa el cierre de sesión después de 20 minutos de inactividad.
        temporizadorInactividad = setTimeout(
            cerrarSesionPorInactividad,
            tiempoMaximoInactividad
        );
    }

    // Envía una solicitud periódica mientras el usuario está activo.
    async function actualizarActividadServidor() {

        // Evita solicitudes duplicadas, innecesarias o realizadas después de un periodo sin actividad.
        if ( sesionCerrada || enviandoActividad || !tokenCSRF || Date.now() - ultimaActividad >= 60000 ) {
            return;
        }

        enviandoActividad = true;

        // Redirige al login si el servidor rechaza la sesión o responde con una redirección.
        try {

            //Envía la solicitud al servidor utilizando la sesión actual.
            const respuesta = await fetch(rutaActividad, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': tokenCSRF,
                    'Accept': 'application/json'
                }
            });

            // Si Laravel ya no reconoce la sesión, regresa al login.
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

    // Detecta acciones que indican que el usuario está utilizando el sistema.
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

    // Inicia el temporizador al cargar una página protegida.
    registrarActividad();
});
