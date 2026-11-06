<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Docente;

class CargaAcademicaController extends Controller
{
    /**
     * Descripción: Consulta los docentes registrados y su carga académica.
     * Entradas: $solicitud: solicitud HTTP con los filtros de búsqueda.
     * Salidas: Retorna la vista de gestión de carga académica con los docentes registrados.
     */
    public function index(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2, 3]);

        // Preparar la consulta de los docentes con su información académica.
        $consultaDocentes = Usuario::where('rol', 3)
            ->where('estado', 1)
            ->with('docente');

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Si es docente, solamente consultar su propia información.
        if ((int) $usuarioAutenticado->rol === 3) {
            $consultaDocentes->where( 'id_usuario', $usuarioAutenticado->id_usuario );
        }

        // Obtener los docentes registrados.
        $docentesRegistrados = $consultaDocentes->orderBy('nombreUsuario')->get();

        // Retornar la vista con los docentes registrados.
        return view( 'CargaAcademica.GestionCargaAcademica', compact('docentesRegistrados') );
    }

    /**
     * Descripción: Muestra el formulario para modificar la carga académica de un docente.
     * Entradas: $idDocente: identificador del docente que se desea modificar.
     * Salidas:
     * Retorna la vista de edición con los datos del docente seleccionado.
     */
    public function edit($idDocente)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1]);

        // Buscar al docente por su identificador.
        $docenteEncontrado = Docente::findOrFail($idDocente);

        // Obtener los datos del usuario al que pertenece el docente.
        $usuarioEncontrado = Usuario::findOrFail($docenteEncontrado->id_usuario);

        // Retornar la vista con la información del docente.
        return view( 'CargaAcademica.EditarCargaAcademica', compact('docenteEncontrado', 'usuarioEncontrado') );
    }

    /**
     * Descripción: Actualiza la carga académica de un docente.
     *
     * Entradas:
     * $solicitud: solicitud HTTP con la nueva cantidad de horas.
     * $idDocente: identificador del docente que se desea modificar.
     *
     * Salidas:
     * Redirige a la gestión de carga académica con un mensaje de confirmación.
     */
    public function update(Request $solicitud, $idDocente)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1]);

        // Buscar al docente por su identificador.
        $docenteEncontrado = Docente::findOrFail($idDocente);

        // Establecer el límite de horas según el tipo de docente.
        $limiteHoras = (int) $docenteEncontrado->tipoDocente === 2 ? 25 : 100;

        // Validar la cantidad de horas ingresada.
        $datosValidados = $solicitud->validate(
            [
                'cargaAcademica' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:' . $limiteHoras
                ]
            ],
            [
                'cargaAcademica.required' => 'La carga académica es obligatoria.',
                'cargaAcademica.integer' => 'La carga académica debe ser un número entero.',
                'cargaAcademica.min' => 'La carga académica debe ser de al menos 1 hora.',
                'cargaAcademica.max' => 'La carga académica no puede superar las ' . $limiteHoras . ' horas permitidas para este tipo de docente.'
            ]
        );

        // Actualizar la carga académica del docente.
        $docenteEncontrado->cargaAcademica = $datosValidados['cargaAcademica'];

        // Guardar los cambios.
        $docenteEncontrado->save();

        // Regresar a la página con un mensaje de éxito.
        return redirect()->route('carga-academica.index')
            ->with('alerta_exito', 'La carga académica se modificó correctamente.');
    }

    /**
     * Descripción: Elimina la carga académica registrada de un docente.
     *
     * Entradas:
     * $idDocente: identificador del docente cuya carga académica se desea eliminar.
     *
     * Salidas:
     * Redirige a la gestión de carga académica con un mensaje de confirmación.
     */
    public function destroy($idDocente)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1]);

        // Buscar al docente por su identificador.
        $docenteEncontrado = Docente::findOrFail($idDocente);

        // Eliminar la carga académica registrada.
        $docenteEncontrado->cargaAcademica = 0;

        // Guardar los cambios.
        $docenteEncontrado->save();

        // Regresar a la página con un mensaje de éxito.
        return redirect()->route('carga-academica.index')
            ->with('alerta_exito', 'La carga académica se eliminó correctamente.');
    }

    /**
     * Descripción: Comprueba que el usuario tenga un rol autorizado.
     * Entradas: $rolesPermitidos: identificadores de los roles autorizados.
     *  Salidas: No devuelve datos; genera un error 403 si no tiene permiso.
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
