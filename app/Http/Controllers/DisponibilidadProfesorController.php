<?php

namespace App\Http\Controllers;

use App\Models\DisponibilidadProfesor;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisponibilidadProfesorController extends Controller
{
    /**
     * Descripción: Muestra la disponibilidad horaria de los docentes
     * registrados para el periodo seleccionado.
     *
     * Entradas:
     * $solicitud: solicitud HTTP del usuario autenticado.
     *
     * Salidas:
     * Vista de gestión con la disponibilidad de los docentes.
     */
    public function index(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2, 3]);

        // Obtener los periodos registrados.
        $periodosRegistrados = Periodo::orderBy('anio', 'desc')
            ->orderBy('estacion')
            ->get();

        // Obtener el periodo seleccionado.
        $periodoSeleccionado = $solicitud->input(
            'id_periodo',
            $periodosRegistrados->first()?->id_periodo
        );

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Consultar los docentes registrados.
        $consultaDocentes = Usuario::where('rol', 3)
            ->where('estado', 1)
            ->with('docente');

        // Si es docente, limitar la consulta a su propia información.
        if ((int) $usuarioAutenticado->rol === 3) {
            $consultaDocentes->where(
                'id_usuario',
                $usuarioAutenticado->id_usuario
            );
        }

        // Obtener los docentes registrados.
        $docentesRegistrados = $consultaDocentes
            ->orderBy('nombreUsuario')
            ->get();

        // Obtener las disponibilidades del periodo seleccionado.
        $disponibilidadesRegistradas = collect();

        if ($periodoSeleccionado) {
            $disponibilidadesRegistradas = DisponibilidadProfesor::where(
                'id_periodo',
                $periodoSeleccionado
            )->with('docente')->get();
        }

        // Enviar la información a la vista.
        return view(
            'DisponibilidadAcademica.GestionDisponibilidadAcademica',
            compact(
                'periodosRegistrados',
                'periodoSeleccionado',
                'docentesRegistrados',
                'disponibilidadesRegistradas'
            )
        );
    }

    /**
     * Descripción: Muestra el formulario para registrar la disponibilidad
     * horaria de un docente.
     *
     * Entradas:
     * $idDocente: identificador del docente.
     *
     * Salidas:
     * Vista para registrar la disponibilidad del docente.
     */
    public function create($idDocente = null)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 3]);

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Si es docente, solamente puede registrar su disponibilidad.
        if ((int) $usuarioAutenticado->rol === 3) {
            $docenteEncontrado = Docente::where(
                'id_usuario',
                $usuarioAutenticado->id_usuario
            )->firstOrFail();
        } else {
            // Buscar el docente seleccionado por el administrador.
            $docenteEncontrado = Docente::findOrFail($idDocente);
        }

        // Obtener los periodos registrados.
        $periodosRegistrados = Periodo::orderBy('anio', 'desc')
            ->orderBy('estacion')
            ->get();

        // Enviar los datos a la vista.
        return view(
            'DisponibilidadAcademica.RegistrarDisponibilidadAcademica',
            compact('docenteEncontrado', 'periodosRegistrados')
        );
    }

    /**
     * Descripción: Valida y registra la disponibilidad horaria de un docente.
     *
     * Entradas:
     * $solicitud: datos enviados desde el formulario.
     * $idDocente: identificador del docente.
     *
     * Salidas:
     * Redirige al listado con un mensaje de confirmación.
     */
    public function store(Request $solicitud, $idDocente = null)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 3]);

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Determinar el docente al que pertenece la disponibilidad.
        if ((int) $usuarioAutenticado->rol === 3) {
            $docenteEncontrado = Docente::where(
                'id_usuario',
                $usuarioAutenticado->id_usuario
            )->firstOrFail();
        } else {
            $docenteEncontrado = Docente::findOrFail($idDocente);
        }

        // Validar los datos recibidos.
        $datosValidados = $solicitud->validate([
            'id_periodo' => [
                'required',
                'integer',
                Rule::exists('periodo', 'id_periodo'),
            ],
            'disponibilidades' => [
                'required',
                'array',
                'min:1',
            ],
            'disponibilidades.*.dia' => [
                'required',
                'integer',
                Rule::in([1, 2, 3, 4, 5, 6, 7]),
            ],
            'disponibilidades.*.hora_inicio' => [
                'required',
                'date_format:H:i',
            ],
            'disponibilidades.*.hora_fin' => [
                'required',
                'date_format:H:i',
                'after:disponibilidades.*.hora_inicio',
            ],
        ], [
            'id_periodo.required' => 'Debes seleccionar un periodo académico.',
            'id_periodo.exists' => 'El periodo seleccionado no existe.',
            'disponibilidades.required' => 'Debes registrar al menos un horario disponible.',
            'disponibilidades.min' => 'Debes registrar al menos un horario disponible.',
            'disponibilidades.*.dia.required' => 'Debes seleccionar un día de la semana.',
            'disponibilidades.*.dia.in' => 'El día seleccionado no es válido.',
            'disponibilidades.*.hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'disponibilidades.*.hora_inicio.date_format' => 'La hora de inicio no tiene un formato válido.',
            'disponibilidades.*.hora_fin.required' => 'La hora de término es obligatoria.',
            'disponibilidades.*.hora_fin.date_format' => 'La hora de término no tiene un formato válido.',
            'disponibilidades.*.hora_fin.after' => 'La hora de término debe ser posterior a la hora de inicio.',
        ]);

        // Registrar cada horario disponible del docente.
        foreach ($datosValidados['disponibilidades'] as $disponibilidad) {
            DisponibilidadProfesor::create([
                'dia' => $disponibilidad['dia'],
                'hora_inicio' => $disponibilidad['hora_inicio'],
                'hora_fin' => $disponibilidad['hora_fin'],
                'estadoConfirmacion' => 0,
                'fechaConfirmacion' => null,
                'id_docente' => $docenteEncontrado->id_docente,
                'id_periodo' => $datosValidados['id_periodo'],
            ]);
        }

        // Regresar al módulo con un mensaje de éxito.
        return redirect()
            ->route('disponibilidad-academica.index')
            ->with('tipo_modal_disponibilidad', 'registro')
            ->with(
                'mensaje_modal_disponibilidad',
                'La disponibilidad horaria se registró correctamente.'
            );
    }

    /**
     * Descripción: Muestra la información de disponibilidad de un docente.
     *
     * Entradas:
     * $idDocente: identificador del docente.
     * $idPeriodo: identificador del periodo académico.
     *
     * Salidas:
     * Vista con la disponibilidad horaria del docente.
     */
    public function show($idDocente, $idPeriodo)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2, 3]);

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Buscar el docente seleccionado.
        $docenteEncontrado = Docente::with('usuario')->findOrFail($idDocente);

        // Comprobar que el docente solamente consulte su información.
        if ((int) $usuarioAutenticado->rol === 3) {
            abort_unless(
                (int) $docenteEncontrado->id_usuario ===
                (int) $usuarioAutenticado->id_usuario,
                403
            );
        }

        // Buscar el periodo seleccionado.
        $periodoEncontrado = Periodo::findOrFail($idPeriodo);

        // Obtener la disponibilidad del docente en ese periodo.
        $disponibilidadesRegistradas = DisponibilidadProfesor::where(
            'id_docente',
            $idDocente
        )->where(
            'id_periodo',
            $idPeriodo
        )->orderBy('dia')
            ->orderBy('hora_inicio')
            ->get();

        // Enviar los datos a la vista.
        return view(
            'DisponibilidadAcademica.ConsultarDisponibilidadAcademica',
            compact(
                'docenteEncontrado',
                'periodoEncontrado',
                'disponibilidadesRegistradas'
            )
        );
    }

    /**
     * Descripción: Muestra el formulario para modificar la disponibilidad
     * horaria de un docente.
     *
     * Entradas:
     * $idDocente: identificador del docente.
     * $idPeriodo: identificador del periodo académico.
     *
     * Salidas:
     * Vista de edición con la disponibilidad registrada.
     */
    public function edit($idDocente, $idPeriodo)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 3]);

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Buscar el docente seleccionado.
        $docenteEncontrado = Docente::with('usuario')->findOrFail($idDocente);

        // Comprobar que el docente solamente modifique su información.
        if ((int) $usuarioAutenticado->rol === 3) {
            abort_unless(
                (int) $docenteEncontrado->id_usuario ===
                (int) $usuarioAutenticado->id_usuario,
                403
            );
        }

        // Buscar el periodo seleccionado.
        $periodoEncontrado = Periodo::findOrFail($idPeriodo);

        // Obtener los horarios registrados.
        $disponibilidadesRegistradas = DisponibilidadProfesor::where(
            'id_docente',
            $idDocente
        )->where(
            'id_periodo',
            $idPeriodo
        )->orderBy('dia')
            ->orderBy('hora_inicio')
            ->get();

        // Enviar los datos a la vista.
        return view(
            'DisponibilidadAcademica.EditarDisponibilidadAcademica',
            compact(
                'docenteEncontrado',
                'periodoEncontrado',
                'disponibilidadesRegistradas'
            )
        );
    }

    /**
     * Descripción: Actualiza la disponibilidad horaria de un docente.
     *
     * Entradas:
     * $solicitud: datos enviados desde el formulario.
     * $idDocente: identificador del docente.
     * $idPeriodo: identificador del periodo académico.
     *
     * Salidas:
     * Redirección al módulo con un mensaje de confirmación.
     */
    public function update(Request $solicitud, $idDocente, $idPeriodo)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 3]);

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Buscar el docente seleccionado.
        $docenteEncontrado = Docente::findOrFail($idDocente);

        // Comprobar que el docente solamente modifique su información.
        if ((int) $usuarioAutenticado->rol === 3) {
            abort_unless(
                (int) $docenteEncontrado->id_usuario ===
                (int) $usuarioAutenticado->id_usuario,
                403
            );
        }

        // Buscar el periodo seleccionado.
        Periodo::findOrFail($idPeriodo);

        // Validar los horarios enviados.
        $datosValidados = $solicitud->validate([
            'disponibilidades' => [
                'required',
                'array',
                'min:1',
            ],
            'disponibilidades.*.dia' => [
                'required',
                'integer',
                Rule::in([1, 2, 3, 4, 5, 6, 7]),
            ],
            'disponibilidades.*.hora_inicio' => [
                'required',
                'date_format:H:i',
            ],
            'disponibilidades.*.hora_fin' => [
                'required',
                'date_format:H:i',
                'after:disponibilidades.*.hora_inicio',
            ],
        ], [
            'disponibilidades.required' => 'Debes registrar al menos un horario disponible.',
            'disponibilidades.min' => 'Debes registrar al menos un horario disponible.',
            'disponibilidades.*.dia.required' => 'Debes seleccionar un día de la semana.',
            'disponibilidades.*.dia.in' => 'El día seleccionado no es válido.',
            'disponibilidades.*.hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'disponibilidades.*.hora_inicio.date_format' => 'La hora de inicio no tiene un formato válido.',
            'disponibilidades.*.hora_fin.required' => 'La hora de término es obligatoria.',
            'disponibilidades.*.hora_fin.date_format' => 'La hora de término no tiene un formato válido.',
            'disponibilidades.*.hora_fin.after' => 'La hora de término debe ser posterior a la hora de inicio.',
        ]);

        // Obtener los horarios existentes del docente en el periodo.
        $disponibilidadesAnteriores = DisponibilidadProfesor::where(
            'id_docente',
            $idDocente
        )->where(
            'id_periodo',
            $idPeriodo
        )->get();

        // Eliminar los horarios anteriores.
        foreach ($disponibilidadesAnteriores as $disponibilidadAnterior) {
            $disponibilidadAnterior->delete();
        }

        // Registrar los nuevos horarios.
        foreach ($datosValidados['disponibilidades'] as $disponibilidad) {
            DisponibilidadProfesor::create([
                'dia' => $disponibilidad['dia'],
                'hora_inicio' => $disponibilidad['hora_inicio'],
                'hora_fin' => $disponibilidad['hora_fin'],
                'estadoConfirmacion' => 0,
                'fechaConfirmacion' => null,
                'id_docente' => $docenteEncontrado->id_docente,
                'id_periodo' => $idPeriodo,
            ]);
        }

        // Regresar al módulo con un mensaje de éxito.
        return redirect()
            ->route('disponibilidad-academica.index')
            ->with('tipo_modal_disponibilidad', 'modificacion')
            ->with(
                'mensaje_modal_disponibilidad',
                'La disponibilidad horaria se modificó correctamente.'
            );
    }

    /**
     * Descripción: Elimina la disponibilidad horaria de un docente
     * correspondiente a un periodo académico.
     *
     * Entradas:
     * $idDocente: identificador del docente.
     * $idPeriodo: identificador del periodo académico.
     *
     * Salidas:
     * Redirección al módulo con un mensaje de confirmación.
     */
    public function destroy($idDocente, $idPeriodo)
    {
        // Solo el administrador puede eliminar disponibilidades.
        $this->verificarRol([1]);

        // Buscar las disponibilidades del docente en el periodo.
        $disponibilidadesRegistradas = DisponibilidadProfesor::where(
            'id_docente',
            $idDocente
        )->where(
            'id_periodo',
            $idPeriodo
        )->get();

        // Comprobar que existan registros.
        if ($disponibilidadesRegistradas->isEmpty()) {
            return redirect()
                ->route('disponibilidad-academica.index')
                ->with('tipo_modal_disponibilidad', 'error')
                ->with(
                    'mensaje_modal_disponibilidad',
                    'No se encontraron disponibilidades para eliminar.'
                );
        }

        // Eliminar los registros de disponibilidad.
        foreach ($disponibilidadesRegistradas as $disponibilidadRegistrada) {
            $disponibilidadRegistrada->delete();
        }

        return redirect()
            ->route('disponibilidad-academica.index')
            ->with('tipo_modal_disponibilidad', 'baja')
            ->with(
                'mensaje_modal_disponibilidad',
                'La disponibilidad del docente se eliminó correctamente.'
            );
    }

    /**
     * Descripción: Comprueba que el usuario autenticado tenga uno de los
     * roles permitidos.
     *
     * Entradas:
     * $rolesPermitidos: identificadores de los roles autorizados.
     *
     * Salidas:
     * No devuelve datos; genera un error 403 si el usuario no tiene permiso.
     */
    private function verificarRol(array $rolesPermitidos): void
    {
        // Obtener al usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Comprobar que exista una sesión y tenga un rol autorizado.
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
