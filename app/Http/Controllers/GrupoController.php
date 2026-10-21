<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Carrera;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class GrupoController extends Controller
{
    /**
     * Descripción: Muestra los grupos registrados y permite filtrarlos.
     *
     * Entradas:
     * $solicitud: solicitud HTTP con los filtros de búsqueda.
     *
     * Salidas: Vista de gestión con los grupos, carreras y periodos.
     */
    public function index(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Consultar los grupos con su carrera y periodo.
        $consultaGrupos = Grupo::with(['carrera', 'periodo']);

        // Filtrar por estado del grupo, si se seleccionó uno.
        if ($solicitud->filled('estado')) {
            $consultaGrupos->where('estado', $solicitud->input('estado'));
        }

        // Filtrar por nombre del grupo.
        if ($solicitud->filled('buscar')) {
            $nombreBuscado = trim($solicitud->input('buscar'));

            $consultaGrupos->where( 'nombreGrupo', 'like','%' . $nombreBuscado . '%' );
        }

        // Filtrar por carrera seleccionada.
        if ($solicitud->filled('id_carrera')) {
            $consultaGrupos->where( 'id_carrera', $solicitud->input('id_carrera')
            );
        }

        // Obtener los grupos ordenados del más reciente al más antiguo.
        $gruposRegistrados = $consultaGrupos->orderBy('id_grupo', 'desc')->get();

        // Obtener las carreras para el filtro.
        $carrerasRegistradas = Carrera::orderBy('nombreCarrera')->get();

        // Obtener los periodos disponibles.
        $periodosRegistrados = Periodo::orderBy('anio')->orderBy('id_periodo')->get();

        return view('grupo.GestionGrupo', compact(
            'gruposRegistrados',
            'carrerasRegistradas',
            'periodosRegistrados'
        ));
    }

    /**
     * Descripción: Prepara los datos para registrar un grupo.
     * Entradas: Ninguna.
     * Salidas:Vista de gestión con carreras activas y periodos disponibles.
     */
    public function create()
    {
        // Roles permitidos
        $this->verificarRol([1, 2]);

        // Obtener únicamente las carreras activas.
        $carrerasRegistradas = Carrera::where('estado', 1)->orderBy('nombreCarrera')->get();

        // Obtener los periodos disponibles.
        $periodosRegistrados = Periodo::orderBy('anio')->orderBy('id_periodo')->get();

        // Obtener los grupos para mantener la información del listado.
        $gruposRegistrados = Grupo::with(['carrera', 'periodo'])->orderBy('id_grupo', 'desc')->get();

        return view('grupo.GestionGrupo', compact(
            'gruposRegistrados',
            'carrerasRegistradas',
            'periodosRegistrados'
        ));
    }

    /**
     * Descripción: Valida y registra un grupo académico.
     *
     * Entradas:
     * $solicitud: datos enviados desde el formulario.
     *
     * Salidas: Redirección al listado con un mensaje de éxito o al formulario con un modal de error.
     */
    public function store(Request $solicitud)
    {
        // Roles permitidos
        $this->verificarRol([1, 2]);

        // Validar los datos recibidos del formulario.
        $datosValidados = $solicitud->validate([
            'id_carrera' => [
                'required',
                'integer',
                Rule::exists('carrera', 'id_carrera'),
            ],
            'cuatrimestre' => [
                'required',
                'integer',
                'min:1',
                'max:10',
            ],
            'letraGrupo' => [
                'required',
                'string',
                'regex:/^[A-Za-z]$/',
            ],
            'id_periodo' => [
                'required',
                'integer',
                Rule::exists('periodo', 'id_periodo'),
            ],
        ], [
            'id_carrera.required' => 'Debes seleccionar una carrera.',
            'id_carrera.exists' => 'La carrera seleccionada no existe.',
            'cuatrimestre.required' => 'Debes seleccionar un cuatrimestre.',
            'cuatrimestre.integer' => 'El cuatrimestre debe ser un número entero.',
            'cuatrimestre.min' => 'El cuatrimestre debe ser como mínimo 1.',
            'cuatrimestre.max' => 'El cuatrimestre no puede ser mayor que 10.',
            'letraGrupo.required' => 'Debes ingresar la letra del grupo.',
            'letraGrupo.regex' => 'La letra del grupo debe ser una sola letra.',
            'id_periodo.required' => 'Debes seleccionar un periodo.',
            'id_periodo.exists' => 'El periodo seleccionado no existe.',
        ]);

        // Buscar la carrera seleccionada.
        $carreraEncontrada = Carrera::findOrFail(
            $datosValidados['id_carrera']
        );

        // Comprobar que la carrera esté activa.
        if ((int) $carreraEncontrada->estado !== 1) {
            return redirect()->back()->withInput()
                ->with('tipo_modal_grupo', 'error')
                ->with(
                    'mensaje_modal_grupo',
                    'No se puede registrar el grupo porque la carrera está inactiva.'
                );
        }

        // Generar el nombre del grupo con la clave, cuatrimestre y letra.
        $nombreGrupo = strtoupper(trim($carreraEncontrada->claveCarrera))
            . '-' . $datosValidados['cuatrimestre']. strtoupper($datosValidados['letraGrupo']);

        // Comprobar que el nombre generado no supere los 10 caracteres.
        if (strlen($nombreGrupo) > 10) {
            return redirect()->back()->withInput()
                ->with('tipo_modal_grupo', 'error')
                ->with(
                    'mensaje_modal_grupo',
                    'El nombre generado supera los 10 caracteres permitidos.'
                );
        }

        // Comprobar que el grupo no exista en el mismo periodo.
        $grupoDuplicado = Grupo::where('nombreGrupo', $nombreGrupo)
            ->where('id_periodo', $datosValidados['id_periodo'])
            ->exists();

        if ($grupoDuplicado) {
            return redirect()->back()->withInput()
                ->with('tipo_modal_grupo', 'error')
                ->with(
                    'mensaje_modal_grupo',
                    'Ya existe un grupo con ese nombre en el periodo seleccionado.'
                );
        }

        try {
            // Registrar el grupo con su nombre generado.
            Grupo::create([
                'nombreGrupo' => $nombreGrupo,
                'cuatrimestre' => $datosValidados['cuatrimestre'],
                'estado' => 1,
                'id_periodo' => $datosValidados['id_periodo'],
                'id_carrera' => $datosValidados['id_carrera'],
            ]);
        } catch (QueryException $error) {
            // Informar si ocurre un problema al guardar en la base de datos.
            return redirect()->back()->withInput()
                ->with('tipo_modal_grupo', 'error')
                ->with(
                    'mensaje_modal_grupo',
                    'No fue posible registrar el grupo. Verifica los datos e inténtalo nuevamente.'
                );
        }

        // Regresar al listado con el modal de registro exitoso.
        return redirect()->route('grupos.index')->with('tipo_modal_grupo', 'registro')
            ->with('mensaje_modal_grupo', 'El grupo ' . $nombreGrupo . ' se registró correctamente.');
    }

    /**
     * Descripción: Muestra la información de un grupo específico.
     *
     * Entradas:
     * $idGrupo: identificador del grupo que se desea consultar.
     *
     * Salidas:Vista de gestión con los grupos registrados.
     */
    public function show($idGrupo)
    {
        // Roles permitidos
        $this->verificarRol([1, 2]);

        // Buscar el grupo con su carrera y periodo.
        $grupoEncontrado = Grupo::with(['carrera', 'periodo'])->findOrFail($idGrupo);

        // Obtener los datos necesarios para el listado.
        $gruposRegistrados = Grupo::with(['carrera', 'periodo'])->orderBy('id_grupo', 'desc')->get();
        $carrerasRegistradas = Carrera::orderBy('nombreCarrera')->get();
        $periodosRegistrados = Periodo::orderBy('anio')->orderBy('id_periodo')->get();

        return view('grupo.GestionGrupo', compact( 'grupoEncontrado','gruposRegistrados','carrerasRegistradas','periodosRegistrados'));
    }

    /**
     * Descripción: Prepara los datos para editar un grupo.
     *
     * Entradas:
     * $idGrupo: identificador del grupo que se desea modificar.
     *
     * Salidas: Vista de edición con los datos del grupo seleccionado.
     */
    public function edit($idGrupo)
    {
        // Roles permitidos
        $this->verificarRol([1, 2]);

        // Buscar el grupo que se desea modificar.
        $grupoEncontrado = Grupo::with(['carrera', 'periodo'])->findOrFail($idGrupo);

        // Obtener las carreras para el formulario.
        $carrerasRegistradas = Carrera::orderBy('nombreCarrera')->get();

        // Obtener los periodos disponibles.
        $periodosRegistrados = Periodo::orderBy('anio')->orderBy('id_periodo')->get();

        return view('grupo.EditarGrupo', compact('grupoEncontrado','carrerasRegistradas', 'periodosRegistrados' ));
    }

    /**
     * Descripción: Actualiza la información de un grupo existente.
     *
     * Entradas:
     * $solicitud: datos enviados desde el formulario.
     * $idGrupo: identificador del grupo que se actualizará.
     *
     * Salidas: Redirección al listado con un mensaje de confirmación.
     */
    public function update(Request $solicitud, $idGrupo)
    {
        // Verificar que el usuario pueda editar grupos.
        $this->verificarRol([1, 2]);

        // Buscar el grupo que se actualizará.
        $grupoEncontrado = Grupo::findOrFail($idGrupo);

        // Identificar si el usuario es administrador.
        $esAdministrador = (int) $solicitud->user()->rol === 1;

        // Validar los datos recibidos.
        $validador = Validator::make($solicitud->all(), [
            'id_carrera' => [
                'required',
                'integer',
                Rule::exists('carrera', 'id_carrera'),
            ],
            'cuatrimestre' => [
                'required',
                'integer',
                'min:1',
                'max:10',
            ],
            'letraGrupo' => [
                'required',
                'string',
                'regex:/^[A-Za-z]$/',
            ],
            'id_periodo' => [
                'required',
                'integer',
                Rule::exists('periodo', 'id_periodo'),
            ],
            'estado' => [
                'sometimes',
                'required',
                'integer',
                'in:0,1',
            ],
        ], [
            'id_carrera.required' => 'Debes seleccionar una carrera.',
            'id_carrera.exists' => 'La carrera seleccionada no existe.',
            'cuatrimestre.required' => 'Debes seleccionar un cuatrimestre.',
            'cuatrimestre.integer' => 'El cuatrimestre debe ser un número entero.',
            'cuatrimestre.min' => 'El cuatrimestre debe ser como mínimo 1.',
            'cuatrimestre.max' => 'El cuatrimestre no puede ser mayor que 10.',
            'letraGrupo.required' => 'Debes ingresar la letra del grupo.',
            'letraGrupo.regex' => 'La letra del grupo debe ser una sola letra.',
            'id_periodo.required' => 'Debes seleccionar un periodo.',
            'id_periodo.exists' => 'El periodo seleccionado no existe.',
            'estado.required' => 'Debes seleccionar el estado del grupo.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ]);

        // Mostrar el error en el modal personalizado si la validación falla.
        if ($validador->fails()) {
            return redirect()->route('grupos.index')
                ->withInput()
                ->with('tipo_modal_grupo', 'error')
                ->with('mensaje_modal_grupo', $validador->errors()->first());
        }

        // Obtener los datos que pasaron la validación.
        $datosValidados = $validador->validated();

        // Impedir que el personal administrativo modifique el estado.
        if (!$esAdministrador) {
            unset($datosValidados['estado']);
        }

        // Buscar la carrera seleccionada.
        $carreraEncontrada = Carrera::find($datosValidados['id_carrera']);

        // Impedir cambiar a una carrera inactiva, pero permitir conservar la actual.
        if (
            (!$carreraEncontrada || (int) $carreraEncontrada->estado !== 1)
            && (int) $datosValidados['id_carrera'] !== (int) $grupoEncontrado->id_carrera
        ) {
            return redirect()->route('grupos.index')
                ->withInput()
                ->with('tipo_modal_grupo', 'error')
                ->with(
                    'mensaje_modal_grupo',
                    'No puedes cambiar el grupo a una carrera inactiva.'
                );
        }

        // Generar el nombre actualizado del grupo.
        $nombreGrupo = strtoupper(trim($carreraEncontrada->claveCarrera))
            . '-' . $datosValidados['cuatrimestre']
            . strtoupper($datosValidados['letraGrupo']);

        // Comprobar la longitud permitida.
        if (strlen($nombreGrupo) > 10) {
            return redirect()->route('grupos.index')
                ->withInput()
                ->with('tipo_modal_grupo', 'error')
                ->with(
                    'mensaje_modal_grupo',
                    'El nombre generado supera los 10 caracteres permitidos.'
                );
        }

        // Comprobar que no exista otro grupo con el mismo nombre y periodo.
        $grupoDuplicado = Grupo::where('nombreGrupo', $nombreGrupo)
            ->where('id_periodo', $datosValidados['id_periodo'])
            ->where('id_grupo', '!=', $grupoEncontrado->id_grupo)
            ->exists();

        if ($grupoDuplicado) {
            return redirect()->route('grupos.index')
                ->withInput()
                ->with('tipo_modal_grupo', 'error')
                ->with(
                    'mensaje_modal_grupo',
                    'Ya existe otro grupo con ese nombre en el periodo seleccionado.'
                );
        }

        // Preparar los datos que se actualizarán.
        $datosActualizar = [
            'nombreGrupo' => $nombreGrupo,
            'cuatrimestre' => $datosValidados['cuatrimestre'],
            'id_periodo' => $datosValidados['id_periodo'],
            'id_carrera' => $datosValidados['id_carrera'],
        ];

        // Agregar el estado únicamente si el usuario es administrador.
        if ($esAdministrador && array_key_exists('estado', $datosValidados)) {
            $datosActualizar['estado'] = (int) $datosValidados['estado'];
        }

        try {
            // Actualizar la información del grupo.
            $grupoEncontrado->update($datosActualizar);
        } catch (QueryException $error) {
            // Informar si ocurre un problema al actualizar.
            return redirect()->route('grupos.index')
                ->withInput()
                ->with('tipo_modal_grupo', 'error')
                ->with(
                    'mensaje_modal_grupo',
                    'No fue posible modificar el grupo. Verifica los datos e inténtalo nuevamente.'
                );
        }

        // Regresar a la gestión con un mensaje de éxito.
        return redirect()->route('grupos.index')
            ->with('tipo_modal_grupo', 'modificacion')
            ->with(
                'mensaje_modal_grupo',
                'El grupo ' . $nombreGrupo . ' se modificó correctamente.'
            );
    }

    /**
     * Descripción: Da de baja lógica un grupo académico.
     *
     * Entradas:
     * $idGrupo: Identificador del grupo que se desea dar de baja.
     *
     * Salidas: Redirección a la gestión de grupos con un modal de confirmación o de error.
     */
    public function destroy($idGrupo)
    {
        // Verificar que el usuario tenga el rol de administrador.
        $this->verificarRol([1]);

        // Buscar el grupo que se desea dar de baja.
        $grupoEncontrado = Grupo::findOrFail($idGrupo);

        try {
            // Cambiar el estado del grupo a inactivo.
            $grupoEncontrado->estado = 0;
            $grupoEncontrado->save();

        } catch (QueryException $error) {
            // Evitar la baja si existen restricciones relacionadas.
            return redirect()->route('grupos.index')
                ->with('tipo_modal_grupo', 'error')
                ->with( 'mensaje_modal_grupo', 'No se puede dar de baja el grupo porque está relacionado con otros registros.' );
        }

        // Regresar al listado con el modal de baja exitosa.
        return redirect()->route('grupos.index')
            ->with('tipo_modal_grupo', 'baja')
            ->with( 'mensaje_modal_grupo', 'El grupo ' . $grupoEncontrado->nombreGrupo . ' se dio de baja correctamente.' );
    }

    /**
     * Descripción: Comprueba que el usuario tenga un rol autorizado.
     *
     * Entradas:
     * $rolesPermitidos: identificadores de los roles autorizados.
     *
     * Salidas: No devuelve datos; genera un error 403 si el usuario no tiene permiso.
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
