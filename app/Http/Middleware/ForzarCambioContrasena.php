<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ForzarCambioContrasena
{
    /**
     * Descripción: Impide acceder al sistema mientras el cambio esté pendiente.
     */
    public function handle(Request $solicitud, Closure $siguiente): Response
    {
        if (!Auth::check()) {
            return $siguiente($solicitud);
        }

        $usuario = Auth::user();

        $claveCambioObligatorio =
            'usuario_debe_cambiar_contrasena_' . $usuario->id_usuario;

        if (
            Cache::store('file')->has($claveCambioObligatorio)
            && !$solicitud->routeIs('contrasena.obligatoria')
            && !$solicitud->routeIs('contrasena.obligatoria.actualizar')
        ) {
            return redirect()->route('contrasena.obligatoria');
        }

        return $siguiente($solicitud);
    }
}
