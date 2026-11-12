<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Periodo;
use App\Models\Carrera;
use App\Models\Grupo;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DatosGeneralesController extends Controller
{
    /**
     * Descripción: Muestra los datos generales de los horarios académicos registrados
     * Entradas: $solicitud: solicitud HTTP con los filtros de búsqueda.
     * Salidas: Vista de gestión de horarios con los registros encontrados.
     */
    public function index(Request $solicitud)
    {
        // Roles
        $this->verificarRol([1, 2]);

        $consultaHorarios = Horario::with([ 'grupo.carrera', 'periodo', ]);

        // Filtros año, periodo, carrera, grupo y estado 
        if ($solicitud->filled('anio')) {
            $consultaHorarios->whereHas('periodo', function ($consultaPeriodo) use ($solicitud) {
                $consultaPeriodo->where('anio', $solicitud->anio);
            });
        }

        if ($solicitud->filled('id_periodo')) {
            $consultaHorarios->where('id_periodo', $solicitud->id_periodo);
        }

        if ($solicitud->filled('id_carrera')) {
            $consultaHorarios->whereHas('grupo', function ($consultaGrupo) use ($solicitud) {
                $consultaGrupo->where('id_carrera', $solicitud->id_carrera);
            });
        }

        if ($solicitud->filled('id_grupo')) {
            $consultaHorarios->where('id_grupo', $solicitud->id_grupo);
        }

        if ($solicitud->filled('estado')) {
            $consultaHorarios->where('estado', $solicitud->estado);
        }

        // Obtiene los datos generales de los horarios, periodo, carrera, grupo 
        $horariosRegistrados = $consultaHorarios->orderBy('id_horario', 'desc')->get();

        $periodosDisponibles = Periodo::where('anio', '>=', 2026)->orderBy('anio')->orderBy('estacion')->get();

        $carrerasDisponibles = Carrera::where('estado', 1)->orderBy('nombreCarrera')->get();

        $gruposDisponibles = Grupo::where('estado', 1)->orderBy('nombreGrupo')->get();

        return view( 'Horario.DatosGeneralesHorario.GestionDatosGeneralesHorario',
            compact( 'horariosRegistrados', 'periodosDisponibles', 'carrerasDisponibles', 'gruposDisponibles' )
        );
    }

    /**
     * Descripción: Muestra el formulario para registrar los datos generales de un nuevo horario académico.
     * Entradas: Ninguna.
     * Salidas: Vista de gestión con los datos necesarios para registrar un horario.
     */
    public function create()
    {
        //Roles
        $this->verificarRol([1, 2]);

        // Obtiene los datos necesarios para el formulario.
        $datosFormulario = $this->obtenerDatosFormulario();

        // Muestra la vista con los horarios registrados, juntando los datos necesarios para mostrar la información.
        return view( 'Horario.DatosGeneralesHorario.GestionDatosGeneralesHorario',
            array_merge($datosFormulario, [
                'horariosRegistrados' => Horario::with([ 'grupo.carrera', 'periodo', ])->orderBy('id_horario', 'desc')->get(),
            ])
        );
    }

    /**
     * Descripción: Registra los datos generales de un nuevo horario académico.
     * Entradas: $solicitud: datos enviados desde el formulario.
     * Salidas: Redirección al listado con un mensaje de éxito o al formulario con un modal de error.
     */
    public function store(Request $solicitud)
    {
        // Roles
        $this->verificarRol([1, 2]);

        // Validar los datos 
        $datosValidados = $solicitud->validate([

            'id_periodo' => [
                'required',
                'integer',
                Rule::exists('periodo', 'id_periodo')
                    ->where(function ($consultaPeriodo) {
                        $consultaPeriodo->where('anio', '>=', 2026);
                    }),
            ],
            'id_carrera' => [
                'required',
                'integer',
                Rule::exists('carrera', 'id_carrera')->where('estado', 1),
            ],
            'id_grupo' => [
                'required',
                'integer',
                Rule::exists('grupo', 'id_grupo')->where('estado', 1),
            ],
            'estado' => [
                'required',
                'integer',
                'in:0,1',
            ],
        ], [
            'id_periodo.required' => 'Debes seleccionar un periodo escolar.',
            'id_periodo.integer' => 'El periodo seleccionado no es válido.',
            'id_periodo.exists' => 'El periodo seleccionado no existe o no corresponde a un año permitido.',

            'id_carrera.required' => 'Debes seleccionar una carrera.',
            'id_carrera.integer' => 'La carrera seleccionada no es válida.',
            'id_carrera.exists' => 'La carrera seleccionada no existe o está inactiva.',

            'id_grupo.required' => 'Debes seleccionar un grupo académico.',
            'id_grupo.integer' => 'El grupo seleccionado no es válido.',
            'id_grupo.exists' => 'El grupo seleccionado no existe o está inactivo.',

            'estado.required' => 'Debes seleccionar el estado del horario.',
            'estado.integer' => 'El estado seleccionado no es válido.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
        ]);

        // Busca el grupo y el periodo seleccionados.
        $grupoEncontrado = Grupo::findOrFail($datosValidados['id_grupo']);
        $periodoEncontrado = Periodo::findOrFail($datosValidados['id_periodo']);

        // Comprueba que el grupo pertenezca a la carrera y al periodo seleccionados. 
        if ( (int) $grupoEncontrado->id_periodo !== (int) $datosValidados['id_periodo'] || (int) $grupoEncontrado->id_carrera !== (int) $datosValidados['id_carrera'] ) {
            return redirect()->back()->withInput()->with('alerta_error', 'El grupo seleccionado no corresponde a la carrera o periodo escolar indicado.');
        }

        // Comprueba si ya existe un horario para el mismo grupo y periodo. Incluye los horarios desactivados para conservar el historial.
        if (Horario::where('id_grupo', $datosValidados['id_grupo'])->where('id_periodo', $datosValidados['id_periodo'])->exists()) {
            return redirect()->back()->withInput()->with('alerta_error', 'Ya existe un horario registrado para el grupo y periodo escolar seleccionados.');

        }

        // Generar nombre del horario.
        $nombreHorario = $periodoEncontrado->estacion . ''
            . $periodoEncontrado->anio . '-'
            . $grupoEncontrado->nombreGrupo;

        // Guarda los datos generales del horario.
        Horario::create([ 'nombreHorario' => $nombreHorario, 'estado' => $datosValidados['estado'], 'id_grupo' => $datosValidados['id_grupo'], 'id_periodo' => $datosValidados['id_periodo'], ]);

        return redirect()->route('datos-generales.index')->with('alerta_exito', 'Los datos generales del horario se registraron correctamente.');
    }

    /**
     * Descripción: Muestra la información general de un horario específico.
     * Entradas: $idHorario: identificador del horario que se desea consultar.
     * Salidas: Vista de gestión con el horario seleccionado.
     */
    public function show($idHorario)
    {
        // Busca el horario y obtiene su información relacionada.
        $horarioEncontrado = Horario::with([ 'grupo.carrera', 'periodo', 'detalles', ])->findOrFail($idHorario);

        //Rol
        $this->verificarRol([1, 2]);

        // Obtiene horarios registrados
        $horariosRegistrados = Horario::with([ 'grupo.carrera', 'periodo', ])->orderBy('id_horario', 'desc')->get();

        // Obtiene los datos necesarios para el formulario.
        $datosFormulario = $this->obtenerDatosFormulario();

        // Muestra la vista con los horarios registrados y el horario seleccionado, juntando los datos necesarios para mostrar la información.
        return view('Horario.DatosGeneralesHorario.GestionDatosGeneralesHorario',
            array_merge($datosFormulario, [ 'horariosRegistrados' => $horariosRegistrados, 'horarioEncontrado' => $horarioEncontrado, ])
        );
    }

    /**
     * Descripción: Muestra la interfaz para modificar los datos generales de un horario.
     * Entradas: $idHorario: identificador del horario recibido desde la ruta.
     * Salidas: Vista de edición con la información del horario seleccionado.
     */
    public function edit($idHorario)
    {
        //Rol
        $this->verificarRol([1, 2]);

        $horarioEncontrado = Horario::with([ 'grupo.carrera', 'periodo', ])->findOrFail($idHorario);

        // Comprueba si el horario ya tiene asignaciones registradas.
        $tieneAsignaciones = $horarioEncontrado->detalles()->exists();

        // Obtiene los datos necesarios para el formulario.
        $datosFormulario = $this->obtenerDatosFormulario();

        // Muestra la vista de edición del horario, juntando los datos necesarios para mostrar su información.
        return view( 'Horario.DatosGeneralesHorario.EditarDatosGeneralesHorario',
            array_merge($datosFormulario, [ 'horarioEncontrado' => $horarioEncontrado, 'tieneAsignaciones' => $tieneAsignaciones, ])
        );
    }

    /**
     * Descripción: Actualiza los datos generales de un horario existente.
     *
     * Entradas:
     * $solicitud: datos enviados desde el formulario.
     * $idHorario: identificador del horario que se actualizará.
     *
     * Salidas:
     * Redirección al formulario de edición con un mensaje de éxito o error.
     */
    public function update(Request $solicitud, $idHorario)
    {
        // Rol
        $this->verificarRol([1, 2]);

        // Busca el horario que se desea actualizar.
        $horarioEncontrado = Horario::findOrFail($idHorario);
        // Comprueba si el horario ya tiene asignaciones registradas.
        $tieneAsignaciones = $horarioEncontrado->detalles()->exists();

        // Valida los datos a
        $datosValidados = $solicitud->validate([
            'id_periodo' => [
                'required',
                'integer',
                Rule::exists('periodo', 'id_periodo')
                    ->where(function ($consultaPeriodo) {
                        $consultaPeriodo->where('anio', '>=', 2026);
                    }),
            ],
            'id_carrera' => [
                'required',
                'integer',
                Rule::exists('carrera', 'id_carrera')->where('estado', 1),
            ],
            'id_grupo' => [
                'required',
                'integer',
                Rule::exists('grupo', 'id_grupo')->where('estado', 1),
            ],
            'estado' => [
                'sometimes',
                'required',
                'integer',
                'in:0,1',
            ],
        ], [
            'id_periodo.required' => 'Debes seleccionar un periodo escolar.',
            'id_periodo.integer' => 'El periodo seleccionado no es válido.',
            'id_periodo.exists' => 'El periodo seleccionado no existe o no corresponde a un año permitido.',

            'id_carrera.required' => 'Debes seleccionar una carrera.',
            'id_carrera.integer' => 'La carrera seleccionada no es válida.',
            'id_carrera.exists' => 'La carrera seleccionada no existe o está inactiva.',

            'id_grupo.required' => 'Debes seleccionar un grupo académico.',
            'id_grupo.integer' => 'El grupo seleccionado no es válido.',
            'id_grupo.exists' => 'El grupo seleccionado no existe o está inactivo.',

            'estado.required' => 'Debes seleccionar el estado del horario.',
            'estado.integer' => 'El estado seleccionado no es válido.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
        ]);

        // Comprueba si se intenta modificar el grupo o el periodo.
        $cambioDatosGenerales = (int) $horarioEncontrado->id_grupo !== (int) $datosValidados['id_grupo'] || (int) $horarioEncontrado->id_periodo !== (int) $datosValidados['id_periodo'];

        // Impide modificar el grupo o periodo cuando existen asignaciones.
        if ($tieneAsignaciones && $cambioDatosGenerales) {
            return redirect()->back()->withInput()
                ->with('alerta_error', 'No puedes modificar el grupo o periodo porque este horario ya tiene asignaciones registradas.');
        }

        // Busca el grupo y el periodo seleccionados.
        $grupoEncontrado = Grupo::findOrFail($datosValidados['id_grupo']);
        $periodoEncontrado = Periodo::findOrFail($datosValidados['id_periodo']);

        // Comprueba que el grupo pertenezca a la carrera y al periodo seleccionados.
        if ( (int) $grupoEncontrado->id_periodo !== (int) $datosValidados['id_periodo'] || (int) $grupoEncontrado->id_carrera !== (int) $datosValidados['id_carrera'] ) {
            return redirect()->back()->withInput()
                ->with('alerta_error', 'El grupo seleccionado no corresponde a la carrera o periodo escolar indicado.');
        }

        // Comprueba que no exista otro horario para el mismo grupo y periodo.
        if (Horario::where('id_grupo', $datosValidados['id_grupo'])->where('id_periodo', $datosValidados['id_periodo'])->where('id_horario', '!=', $horarioEncontrado->id_horario)->exists()
        ) {
            return redirect()->back()->withInput()
                ->with('alerta_error', 'Ya existen otros datos generales registrados para el grupo y periodo escolar seleccionados.');
        
        }

        // Nuevo nombre
        $nombreHorario = $periodoEncontrado->estacion . ' '
            . $periodoEncontrado->anio . '-'
            . $grupoEncontrado->nombreGrupo;

        // Actualiza los datos generales del horario.
        $horarioEncontrado->update([
            'nombreHorario' => $nombreHorario,
            'id_grupo' => $datosValidados['id_grupo'],
            'id_periodo' => $datosValidados['id_periodo'],
            'estado' => $datosValidados['estado'] ?? $horarioEncontrado->estado,
        ]);

        // Regresa al listado y muestra el mensaje de confirmación.
        return redirect()->route('datos-generales.index')
            ->with('alerta_exito', 'Los datos generales del horario se actualizaron correctamente.');
    }

    /**
     * Descripción: Desactiva un horario mediante borrado lógico.
     * Entradas: $idHorario: identificador del horario que se desea desactivar.
     * Salidas: Redirección al listado con un mensaje de confirmación.
     */
    public function destroy($idHorario)
    {
        // Roles
        $this->verificarRol([1]);

        $horarioEncontrado = Horario::findOrFail($idHorario);

        // Comprueba si el horario ya está desactivado.
        if ((int) $horarioEncontrado->estado === 0) {
            return redirect()->route('datos-generales.index')
                ->with('alerta_error', 'El horario seleccionado ya se encuentra desactivado.');
        }

        // Cambia el estado 
        $horarioEncontrado->update([ 'estado' => 0, ]);

        // Regresa al listado y muestra el modal de confirmación.
        return redirect()->route('datos-generales.index')    
            ->with('alerta_exito', 'Los datos generales del horario se desactivó correctamente.');
    }


    /**
     * Descripción: Obtiene los datos necesarios para los formularios de registro y edición.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna un arreglo con periodos, carreras y grupos disponibles.
     */
    private function obtenerDatosFormulario(): array
    {
        // Obtiener los periodos, carreras activas y grupos activos.
        $periodosDisponibles = Periodo::where('anio', '>=', 2026)->orderBy('anio')->orderBy('estacion')->get();
        $carrerasDisponibles = Carrera::where('estado', 1)->orderBy('nombreCarrera')->get();
        $gruposDisponibles = Grupo::where('estado', 1)->orderBy('nombreGrupo')->get();

        // Retorna los datos para utilizarlos en las vistas.
        return compact( 'periodosDisponibles', 'carrerasDisponibles', 'gruposDisponibles' );
    }

    /**
     * Descripción: Comprueba que el usuario autenticado tenga uno de los roles permitidos.
     * Entradas: $rolesPermitidos: identificadores de los roles autorizados.
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
