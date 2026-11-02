<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class CargaAcademicaController extends Controller
{
    /**
     * Descripción: Consulta la carga académica registrada de los docentes y permite filtrar por docente.
     * Entradas: $solicitud: solicitud HTTP con los filtros de búsqueda.
     * Salidas: Vista con las cuentas docentes y su carga académica.
     */

    public function index(Request $solicitud)
    {
        // rol autorizado.
        $this->verificarRol([1, 2, 3]);

        // Obtener al usuario y rol 
        $usuarioAutenticado = $solicitud->user();
        $rolUsuario = (int) $usuarioAutenticado->rol;

        // Consultar las cuentas con rol docente y su información académica.
        $consultaUsuarios = Usuario::where('rol', 3)->where('estado', 1)->with('docente');

        // Si inició sesión un docente, mostrar únicamente su información.
        if ($rolUsuario === 3) {
            $consultaUsuarios->where( 'id_usuario', $usuarioAutenticado->id_usuario );
        }

        // Filtrar por docente cuando administración seleccione una cuenta.
        if ($rolUsuario !== 3 && $solicitud->filled('id_usuario')) {
            $consultaUsuarios->where( 'id_usuario', $solicitud->input('id_usuario') );
        }

        // Obtener las cuentas ordenadas por nombre.
        $docentesRegistrados = $consultaUsuarios->orderBy('nombreUsuario')->orderBy('apellidoPaterno')->get();

        // Obtener las cuentas disponibles para el filtro.
        $usuariosDocentes = Usuario::where('rol', 3)->where('estado', 1)->orderBy('nombreUsuario')->orderBy('apellidoPaterno')->get();

        // Enviar los datos a la vista.
        return view(
            'cargaAcademica.GestionCargaAcademica',
            compact('docentesRegistrados', 'usuariosDocentes')
        );
    }

    /**
     * Descripción: Comprueba que el usuario autenticado tenga uno
     * de los roles permitidos.
     *
     * Entradas:
     * $rolesPermitidos: identificadores de los roles autorizados.
     *
     * Salidas:
     * No devuelve datos; genera un error 403 si no tiene permiso.
     */

    private function verificarRol(array $rolesPermitidos): void
    {
        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Comprobar que tenga uno de los roles permitidos.
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
