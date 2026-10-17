<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocenteController extends Controller
{
    /**
     * Descripción: Muestra todas las cuentas con rol docente y la información académica
     * que tengan registrada, incluyendo las cuentas sin registro docente.
     *
     * Entradas:
     * $solicitud: solicitud HTTP del usuario autenticado.
     *
     * Salidas:
     * Vista de gestión de docentes con todas las cuentas correspondientes.
     */
    public function index(Request $solicitud)
    {
        // Obtiene al usuario que inició sesión.
        $usuarioAutenticado = $solicitud->user();

        // Comprueba que exista una sesión autenticada.
        abort_unless($usuarioAutenticado, 401);

        $rolUsuario = (int) $usuarioAutenticado->rol;

        // Permite el acceso únicamente a los roles autorizados.
        abort_unless(in_array($rolUsuario, [1, 2, 3], true), 403);

        // Consulta todas las cuentas con rol docente, incluyendo las que están inactivas.
        $consultaUsuarios = Usuario::withTrashed()->where('rol', 3)->with('docente');

        // Si inició sesión un docente, limita el listado a su cuenta.
        if ($rolUsuario === 3) {
            $consultaUsuarios->where(
                'id_usuario',
                $usuarioAutenticado->id_usuario
            );
        }

        // Obtiene las cuentas ordenadas por nombre.
        $usuariosDocentes = $consultaUsuarios->orderBy('nombreUsuario')->orderBy('apellidoPaterno')->get();

        $docentesRegistrados = $usuariosDocentes;

        // Obtiene las cuentas activas que todavía no tienen información académica registrada.
        $usuariosDisponibles = Usuario::where('rol', 3)
            ->where('estado', 1)
            ->whereNotIn(
                'id_usuario',
                Docente::select('id_usuario')
            )
            ->orderBy('nombreUsuario')
            ->get();

        return view('docentes.GestionDocentes', compact('docentesRegistrados','usuariosDisponibles'));
    }

    /**
     * Descripción: Muestra la pantalla de gestión para el administrador.
     *
     * Entradas: Ninguna.
     *
     * Salidas: Vista de gestión con todas las cuentas docentes.
     */
    public function create()
    {
        // Solo el administrador puede registrar información docente.
        $this->verificarRol([1]);

        // Obtiene las cuentas activas que todavía no tienen un registro
        $usuariosDisponibles = Usuario::where('rol', 3)
            ->where('estado', 1)
            ->whereNotIn(
                'id_usuario',
                Docente::select('id_usuario')
            )
            ->orderBy('nombreUsuario')
            ->get();

        // Obtiene todas las cuentas docentes, (con o sin información)
        $docentesRegistrados = Usuario::withTrashed()
            ->where('rol', 3)
            ->with('docente')
            ->orderBy('nombreUsuario')
            ->get();

        // Envía los datos necesarios a la vista.
        return view( 'docentes.GestionDocentes', compact('docentesRegistrados', 'usuariosDisponibles'));
    }

    /**
     * Descripción: Registra la información académica de una cuenta docente existente.
     *
     * Entradas:
     * $solicitud: datos enviados desde el formulario.
     *
     * Salidas: Redirección al listado con un mensaje de confirmación.
     */
    public function store(Request $solicitud)
    {
        // Solo el administrador puede registrar docentes.
        $this->verificarRol([1]);

        // Valida que la cuenta seleccionada sea un docente activo
        $datosValidados = $solicitud->validate([
            'id_usuario' => [
                'required',
                'integer',
                Rule::exists('usuario', 'id_usuario')
                    ->where('rol', 3)
                    ->where('estado', 1),
                Rule::unique('docente', 'id_usuario'),
            ],

            'matricula' => [
                'required',
                'string',
                'max:20',
                Rule::unique('docente', 'matricula'),
            ],

            'especialidad' => [
                'required',
                'string',
                'max:100',
            ],

            'tipoDocente' => [
                'required',
                'integer',
                Rule::in([1, 2]),
            ],

            'cargaAcademica' => [
                'required',
                'numeric',
                'min:0',
                'max:20',
            ],
        ], [
            'id_usuario.required' => 'Debes seleccionar una cuenta docente.',
            'id_usuario.exists' => 'La cuenta seleccionada no está activa o no tiene el rol de docente.',
            'id_usuario.unique' => 'Esta cuenta ya tiene información registrada como docente.',

            'matricula.required' => 'La matrícula es obligatoria.',
            'matricula.max' => 'La matrícula no puede superar los 20 caracteres.',
            'matricula.unique' => 'Esta matrícula ya está registrada.',

            'especialidad.required' => 'La especialidad es obligatoria.',
            'especialidad.max' => 'La especialidad no puede superar los 100 caracteres.',

            'tipoDocente.required' => 'Debes seleccionar el tipo de docente.',
            'tipoDocente.in' => 'El tipo de docente seleccionado no es válido.',

            'cargaAcademica.required' => 'La carga académica es obligatoria.',
            'cargaAcademica.numeric' => 'La carga académica debe ser un número.',
            'cargaAcademica.min' => 'La carga académica no puede ser negativa.',
            'cargaAcademica.max' => 'La carga académica no puede superar las 20 horas.',
        ]);

        // Registra únicamente los datos académicos validados.
        Docente::create($datosValidados);

        // Regresa al listado con un mensaje de confirmación.
        return redirect()->route('docentes.index')->with('docente_agregado', true);
    }

    /**
     * Descripción: Muestra la información de una cuenta docente específica.
     *
     * Entradas:
     * $idDocente: identificador del registro docente.
     *
     * Salidas: Vista de gestión con la cuenta docente seleccionada.
     */
    public function show($idDocente)
    {
        // Busca el registro académico solicitado. Incluye la cuenta aunque tenga baja lógica.
        $docenteEncontrado = Docente::with([
            'usuario' => function ($consultaUsuario) {
                $consultaUsuario->withTrashed();
            }
        ])->findOrFail($idDocente);

        // Comprueba que el usuario tenga permiso para consultar el registro.
        $this->verificarPropietario($docenteEncontrado);

        // Obtiene todas las cuentas docentes para mantener
        // la estructura esperada por la vista.
        $docentesRegistrados = Usuario::withTrashed()
            ->where('rol', 3)
            ->with('docente')
            ->orderBy('nombreUsuario')
            ->get();

        // Obtiene las cuentas activas sin información académica.
        $usuariosDisponibles = Usuario::where('rol', 3)
            ->where('estado', 1)
            ->whereNotIn(
                'id_usuario',
                Docente::select('id_usuario')
            )
            ->orderBy('nombreUsuario')
            ->get();

        // Envía el listado y el registro seleccionado a la vista.
        return view(
            'docentes.GestionDocentes',
            compact(
                'docentesRegistrados',
                'usuariosDisponibles',
                'docenteEncontrado'
            )
        );
    }

    
    /**
     * Descripción: Muestra el formulario independiente para modificar los datos académicos de un docente.
     *
     * Entradas:
     * $idDocente: identificador del registro docente recibido desde la ruta.
     *
     * Salidas: Vista de editar docentes con la información del docente seleccionado.
     */
    public function edit($idDocente)
    {
        // Comprueba que solo el administrador pueda editar información docente.
        $this->verificarRol([1,2,3]);

        // Busca el registro académico y carga la cuenta de usuario asociada.
        $docenteEncontrado = Docente::with([
            'usuario' => function ($consultaUsuario) {
                // Permite cargar la cuenta aunque tenga baja lógica.
                $consultaUsuario->withTrashed();
            }
        ])->findOrFail($idDocente);

        // Muestra la página independiente de edición.
        return view('docentes.EditarDocentes', compact('docenteEncontrado'));
    }

    /**
     * Descripción: Actualiza la información académica de un docente existente.
     *
     * Entradas:
     * $solicitud: datos enviados desde el formulario.
     * $idDocente: identificador del registro que se actualizará.
     *
     * Salidas: Redirección al listado con un mensaje de confirmación.
     */
    public function update(Request $solicitud, $idDocente)
    {
        // Solo el administrador puede actualizar información docente.
        $this->verificarRol([1,2,3]);

        // Busca el registro que se desea actualizar.
        $docenteEncontrado = Docente::findOrFail($idDocente);

        // Establece automáticamente la carga académica en 20 horas.
        $solicitud->merge([
            'cargaAcademica' => 20,
        ]);

        // Valida los datos y excluye el registro actual
        $datosValidados = $solicitud->validate([
            'id_usuario' => [
                'required',
                'integer',
                Rule::exists('usuario', 'id_usuario')
                    ->where('rol', 3)
                    ->where('estado', 1),
                Rule::unique('docente', 'id_usuario')
                    ->ignore($docenteEncontrado->id_docente, 'id_docente'),
            ],

            'matricula' => [
                'required',
                'string',
                'max:20',
                Rule::unique('docente', 'matricula')
                    ->ignore($docenteEncontrado->id_docente, 'id_docente'),
            ],

            'especialidad' => [
                'required',
                'string',
                'max:100',
            ],

            'tipoDocente' => [
                'required',
                'integer',
                Rule::in([1, 2]),
            ],

            'cargaAcademica' => [
                'required',
                'integer',
                'in:20',
            ],
        ], [
            'id_usuario.required' => 'Debes seleccionar una cuenta docente.',
            'id_usuario.exists' => 'La cuenta seleccionada no está activa o no tiene el rol de docente.',
            'id_usuario.unique' => 'Esta cuenta ya tiene información registrada como docente.',

            'matricula.required' => 'La matrícula es obligatoria.',
            'matricula.max' => 'La matrícula no puede superar los 20 caracteres.',
            'matricula.unique' => 'Esta matrícula ya está registrada.',

            'especialidad.required' => 'La especialidad es obligatoria.',
            'especialidad.max' => 'La especialidad no puede superar los 100 caracteres.',

            'tipoDocente.required' => 'Debes seleccionar el tipo de docente.',
            'tipoDocente.in' => 'El tipo de docente seleccionado no es válido.',

            'cargaAcademica.required' => 'La carga académica es obligatoria.',
            'cargaAcademica.numeric' => 'La carga académica debe ser un número.',
            'cargaAcademica.min' => 'La carga académica no puede ser negativa.',
            'cargaAcademica.max' => 'La carga académica no puede superar las 20 horas.',
        ]);

        // Guarda los cambios del registro académico.
        $docenteEncontrado->update($datosValidados);

        return redirect()
            ->route('docentes.edit', [
                'idDocente' => $docenteEncontrado->id_docente,
            ])
            ->with('tipo_modal_docente', 'modificacion')
            ->with('mensaje_modal_docente','Los datos del docente se actualizaron correctamente.');
    }

    /**
     * Descripción: Comprueba que el usuario autenticado tenga uno de los roles permitidos.
     *
     * Entradas:
     * $rolesPermitidos: identificadores de los roles autorizados.
     *
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

    /**
     * Descripción: Comprueba que un docente consulte únicamente su propio registro,
     * mientras que administración puede consultar los registros permitidos.
     *
     * Entradas:
     * $docenteEncontrado: registro que se desea consultar.
     *
     * Salidas:
     * No devuelve datos; genera un error 403 si no tiene permiso.
     */
    private function verificarPropietario(Docente $docenteEncontrado): void
    {
        $usuarioAutenticado = auth()->user();

        abort_unless($usuarioAutenticado, 401);

        // Obtiene el rol de la cuenta autenticada.
        $rolUsuario = (int) $usuarioAutenticado->rol;

        // Permite consultar registros a administración.
        if (in_array($rolUsuario, [1, 2], true)) {
            return;
        }

        // Restringe al docente a su propio registro.
        abort_unless(
            $rolUsuario === 3
            && (int) $docenteEncontrado->id_usuario
                === (int) $usuarioAutenticado->id_usuario,
            403
        );
    }
}
