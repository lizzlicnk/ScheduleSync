<?php

namespace App\Http\Middleware;

// Importar la herramienta para continuar con la siguiente etapa de la solicitud.
use Closure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

// Importar la clase para indicar el tipo de respuesta HTTP que retorna el middleware.
use Symfony\Component\HttpFoundation\Response;

class ForzarCambioContrasena
{

    /**
     * Descripción: Impide que el usuario autenticado acceda a otras secciones
     * del sistema mientras tenga pendiente el cambio obligatorio de contraseña.
     *
     * Entradas:
     * $solicitud: contiene la información de la solicitud HTTP recibida.
     * $siguiente: permite continuar con la ejecución de los siguientes
     * middleware o del controlador correspondiente.
     *
     * Salidas: Retorna la respuesta de la siguiente etapa si no existe
     * un cambio obligatorio pendiente o si el usuario no está autenticado.
     * En caso contrario, redirige al formulario de cambio de contraseña.
     */
    public function handle(Request $solicitud, Closure $siguiente): Response
    {
        // Permitir que la solicitud continúe si no hay un usuario autenticado.
        if (!Auth::check()) {
            return $siguiente($solicitud);
        }

        // Obtener los datos del usuario que inició sesión.
        $usuario = Auth::user();

        // Generar la clave de caché asociada con el cambio obligatorio de contraseña.
        $claveCambioObligatorio =
            'usuario_debe_cambiar_contrasena_' . $usuario->id_usuario;

        // Comprobar si el cambio es obligatorio y si la solicitud
        // corresponde a una ruta distinta del formulario y su actualización.
        if (
            Cache::store('file')->has($claveCambioObligatorio)
            && !$solicitud->routeIs('contrasena.obligatoria')
            && !$solicitud->routeIs('contrasena.obligatoria.actualizar')
        ) {
            // Redirigir al formulario de cambio obligatorio de contraseña.
            return redirect()->route('contrasena.obligatoria');
        }
        
        // Permitir que la solicitud continúe si cumple las condiciones de acceso.
        return $siguiente($solicitud);
    }
}
