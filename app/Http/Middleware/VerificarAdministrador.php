<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarAdministrador
{
    /**
     * Descripción: Restringe el acceso a las rutas de administración.
     *
     * Entrada: Solicitud del usuario autenticado.
     * Salida: Permite el acceso al administrador o lo rechaza.
     */
    public function handle(
        Request $solicitud,
        Closure $siguiente
    ): Response {
        // Obtener el usuario que inició sesión.
        $usuarioAutenticado = auth()->user();

        // Comprobar que el usuario tenga el rol de administrador.
        if (
            !$usuarioAutenticado ||
            (int) $usuarioAutenticado->rol !== 1
        ) {
            // Rechazar el acceso y mostrar el aviso de permisos.
            return redirect()
                ->route('inicio')
                ->with('modal_sin_permisos', true);
        }

        // Permitir que el administrador continúe.
        return $siguiente($solicitud);
    }
}
