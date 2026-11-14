<?php

namespace App\Http\Controllers;

class HorarioController extends Controller
{
    /**
     * Descripción: Muestra la página principal del módulo de horarios.
     * Entradas: Ninguna.
     * Salidas: Vista principal con las opciones de gestión de horarios.
     */
    public function inicio()
    {
        // Verificar Roles
        $this->verificarRol([1, 2]);

        return view('Horario.InicioHorarios');
    }

    /**
     * Descripción: Comprueba que el usuario autenticado tenga uno de los roles permitidos.
     * Entradas:
     * $rolesPermitidos: identificadores de los roles autorizados.
     * Salidas: No devuelve datos; genera un error 403 si no tiene permiso.
     */
    private function verificarRol(array $rolesPermitidos): void
    {
        // Obtiene al usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Comprueba que exista una sesión y tenga un rol autorizado.
        abort_unless(
            $usuarioAutenticado
            && in_array(
                (int) $usuarioAutenticado->rol,
                $rolesPermitidos,
                true
            ),
            403
        );
    }
}
