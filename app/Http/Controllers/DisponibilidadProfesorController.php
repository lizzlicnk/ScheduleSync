<?php

namespace App\Http\Controllers;

use App\Models\DisponibilidadProfesor;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Periodo;
use App\Models\Asignatura;
use App\Models\AsignaturaPreferida;
use App\Models\Grupo;
use App\Models\Carrera;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisponibilidadProfesorController extends Controller
{
    /**
     * Descripción: Muestra la disponibilidad horaria de los docentes registrados para el periodo seleccionado.
     * Entradas: $solicitud: solicitud HTTP del usuario autenticado.
     * Salidas: Vista de gestión con la disponibilidad de los docentes.
     */
    public function index(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2, 3]);

        // Obtener los periodos registrados.
        $periodosRegistrados = Periodo::orderBy('anio', 'desc')->orderBy('estacion')->get();

        // Obtener el periodo seleccionado.
        $periodoSeleccionado = $solicitud->input( 'id_periodo', $periodosRegistrados->first()?->id_periodo );

        // Obtener el docente seleccionado, si se recibió desde la solicitud.
        $idDocenteSeleccionado = $solicitud->input('id_docente');

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Consultar los docentes registrados.
        $consultaDocentes = Usuario::where('rol', 3)->where('estado', 1)->with('docente');

        // Si es docente, limitar la consulta a su propia información.
        if ((int) $usuarioAutenticado->rol === 3) {
            $consultaDocentes->where( 'id_usuario', $usuarioAutenticado->id_usuario );
        }

        // Obtener los docentes registrados.
        $docentesRegistrados = $consultaDocentes->orderBy('nombreUsuario')->get();

        // Obtener las disponibilidades registradas en el periodo seleccionado.
        $disponibilidadesRegistradas = DisponibilidadProfesor::where(
            'id_periodo',
            $periodoSeleccionado
        )->get();

        // Obtener las carreras activas.
        $carreras = Carrera::where('estado', 1)
            ->orderBy('nombreCarrera')
            ->get();

        // Obtiene las asignaturas de las carreras y cuatrimestres del periodo seleccionado.
        $asignaturasPorCarrera = $this->obtenerAsignaturasPorCarreraYCuatrimestre(
            $periodoSeleccionado
        );

        
        // Enviar la información a la vista.
        return view( 'DisponibilidadAcademica.GestionDisponibilidadAcademica',
            compact(
                    'periodosRegistrados',
                    'periodoSeleccionado',
                    'idDocenteSeleccionado',
                    'docentesRegistrados',
                    'disponibilidadesRegistradas',
                    'carreras',
                    'asignaturasPorCarrera'
                )
        );
    }

    /**
     * Descripción: Muestra la información de disponibilidad de un docente.
     *
     * Entradas:
     * $idDocente: identificador del docente.
     * $idPeriodo: identificador del periodo académico.
     *
     * Salidas: Vista con la disponibilidad horaria del docente.
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
                (int) $docenteEncontrado->id_usuario === (int) $usuarioAutenticado->id_usuario,
                403
            );
        }

        // Buscar el periodo seleccionado.
        $periodoEncontrado = Periodo::findOrFail($idPeriodo);

        // Obtener la disponibilidad del docente en ese periodo.
        $disponibilidadesRegistradas = DisponibilidadProfesor::where( 'id_docente', $idDocente )
        ->where( 'id_periodo', $idPeriodo )->orderBy('dia')->orderBy('hora_inicio')->get();

        // Enviar los datos a la vista.
        return view( 'DisponibilidadAcademica.GestionDisponibilidadAcademica',
            compact( 'docenteEncontrado', 'periodoEncontrado', 'disponibilidadesRegistradas' )
        );
    }

    /**
     * Descripción: Mostrar el formulario para registrar la disponibilidad académica de un docente.
     *
     * Entradas:
     * $solicitud: Solicitud HTTP.
     * $idDocente: Identificador del docente.
     *
     * Salidas: Vista para registrar la disponibilidad académica.
     */
    /**
     * Muestra el formulario para registrar la disponibilidad académica de un docente.
     *
     * Descripción:
     * Obtiene la información del docente, los periodos académicos, las carreras
     * y las asignaturas necesarias para registrar una nueva disponibilidad.
     *
     * Entradas:
     * - $solicitud: Solicitud HTTP con el identificador del periodo seleccionado.
     * - $idDocente: Identificador del docente seleccionado (opcional).
     *
     * Salidas:
     * - Vista de gestión de disponibilidad académica.
     */
    public function create(Request $solicitud, $idDocente = null)
    {
        // Verifica que el usuario tenga permiso para acceder al módulo.
        $this->verificarRol([1, 2, 3]);

        // Obtiene el docente correspondiente al usuario autenticado.
        if ((int) auth()->user()->rol === 3) {
            $docente = Docente::where('id_usuario', auth()->id())
                ->firstOrFail();
        } else {
            // El personal administrativo debe indicar el docente.
            if (!$idDocente) {
                return redirect()
                    ->route('disponibilidad-academica.index')
                    ->withErrors([
                        'docente' => 'Selecciona un docente para registrar su disponibilidad.'
                    ]);
            }

            $docente = Docente::with('usuario')
                ->findOrFail($idDocente);
        }

        // Obtiene los periodos académicos registrados.
        $periodos = Periodo::orderByDesc('anio')
            ->orderByDesc('id_periodo')
            ->get();

        // Obtiene el periodo seleccionado o el más reciente.
        $periodoFormulario = $periodos->firstWhere(
            'id_periodo',
            (int) $solicitud->query('id_periodo')
        ) ?? $periodos->first();

        // Verifica que existan periodos académicos registrados.
        if (!$periodoFormulario) {
            return redirect()
                ->route('disponibilidad-academica.index')
                ->withErrors([
                    'periodo' => 'No hay periodos académicos registrados.'
                ]);
        }

        // Obtiene el identificador del periodo seleccionado.
        $periodoSeleccionado = $periodoFormulario->id_periodo;

        // Evita registrar nuevamente una disponibilidad existente.
        $disponibilidadExistente = DisponibilidadProfesor::where(
            'id_docente',
            $docente->id_docente
        )
            ->where('id_periodo', $periodoSeleccionado)
            ->exists();

        if ($disponibilidadExistente) {
            return redirect()->route('disponibilidad-academica.edit', [
                'idDocente' => $docente->id_docente,
                'idPeriodo' => $periodoSeleccionado
            ]);
        }

        // Obtiene los periodos registrados para el listado.
        $periodosRegistrados = $periodos;

        // Obtiene el identificador del docente seleccionado.
        $idDocenteSeleccionado = $docente->id_docente;

        // Obtiene los docentes registrados.
        $consultaDocentes = Usuario::where('rol', 3)
            ->where('estado', 1)
            ->with('docente');

        if ((int) auth()->user()->rol === 3) {
            $consultaDocentes->where('id_usuario', auth()->user()->id_usuario);
        }

        $docentesRegistrados = $consultaDocentes
            ->orderBy('nombreUsuario')
            ->get();

        // Obtiene las disponibilidades registradas del periodo.
        $disponibilidadesRegistradas = DisponibilidadProfesor::where(
            'id_periodo',
            $periodoSeleccionado
        )
            ->with('docente')
            ->get();

        // Obtiene las carreras activas.
        $carreras = Carrera::where('estado', 1)
            ->orderBy('nombreCarrera')
            ->get();

        // Obtiene las asignaturas de las carreras y cuatrimestres del periodo seleccionado.
        $asignaturasPorCarrera = $this->obtenerAsignaturasPorCarreraYCuatrimestre(
            $periodoSeleccionado
        );

        // Inicializa las selecciones.
        $asignaturasSeleccionadas = [];
        $bloquesNoDisponibles = [];

        // Indica que se está registrando una disponibilidad nueva.
        $esEdicion = false;

        // Indica que se está mostrando el formulario de registro.
        $esRegistro = true;

        return view('DisponibilidadAcademica.GestionDisponibilidadAcademica', compact(
            'periodosRegistrados',
            'periodoSeleccionado',
            'idDocenteSeleccionado',
            'docentesRegistrados',
            'disponibilidadesRegistradas',
            'docente',
            'periodos',
            'carreras',
            'asignaturasPorCarrera',
            'asignaturasSeleccionadas',
            'bloquesNoDisponibles',
            'esEdicion',
            'esRegistro'
        ));
    }

    /**
     * Descripción:
     * Registrar la disponibilidad académica de un docente.
     *
     * Entradas:
     * $solicitud: Solicitud HTTP con las asignaturas y bloques seleccionados.
     * $idDocente: Identificador del docente.
     *
     * Salidas:
     * Redirección a la consulta de disponibilidad.
     */
    public function store(Request $solicitud, $idDocente = null)
    {
        // Verifica que el usuario tenga permiso para registrar.
        $this->verificarRol([1, 2, 3]);

        // Obtiene el docente correspondiente al usuario autenticado.
        if ((int) auth()->user()->rol === 3) {

            $docente = Docente::where('id_usuario', auth()->id())->firstOrFail();

        } else {

            // Obtiene el identificador del docente seleccionado.
            $idDocente = $idDocente ?? $solicitud->input('id_docente');

            // Buscar el docente seleccionado.
            $docente = Docente::findOrFail($idDocente);
        }

        // Valida la información recibida.
        $datosValidados = $solicitud->validate([
            'id_periodo' => [
                'required',
                'integer',
                Rule::exists('periodo', 'id_periodo')
            ],
            'accion' => [
                'required',
                Rule::in(['borrador', 'entregar'])
            ],
            'bloques_no_disponibles' => [
                'required',
                'array',
                'min:1',
                'max:84'
            ],
            'bloques_no_disponibles.*' => [
                'required',
                'string',
                'distinct',
                'regex:/^(?:[1-5]-(?:[7-9]|1[0-9]|20)|6-(?:[7-9]|1[0-4]))$/'
            ],
            'asignaturas' => [
                'sometimes',
                'array',
                'max:1000'
            ],
            'asignaturas.*' => [
                'integer',
                'distinct',
                Rule::exists('asignatura', 'id_asignatura')
            ]
        ], [
            'id_periodo.required' => 'Selecciona un periodo académico.',
            'accion.required' => 'Selecciona qué acción deseas realizar.',
            'bloques_no_disponibles.min' => 'Selecciona al menos un horario en el que no estés disponible.',
            'bloques_no_disponibles.max' => 'La cantidad de horarios seleccionados no es válida.',
            'bloques_no_disponibles.*.distinct' => 'No puedes seleccionar dos veces el mismo horario.',
            'bloques_no_disponibles.*.regex' => 'Uno de los horarios seleccionados no es válido.',
            'asignaturas.*.exists' => 'Una de las asignaturas seleccionadas ya no está disponible.'
        ]);

        // Comprueba que las asignaturas seleccionadas sigan activas.
        $idsAsignaturas = $datosValidados['asignaturas'] ?? [];

        if (count($idsAsignaturas) > 0) {
            $totalAsignaturasValidas = Asignatura::whereIn(
                'id_asignatura',
                $idsAsignaturas
            )
                ->where('estado', 1)
                ->whereHas('carrera', function ($consulta) {
                    $consulta->where('estado', 1);
                })
                ->count();

            if ($totalAsignaturasValidas !== count($idsAsignaturas)) {
                return back()
                    ->withErrors([
                        'asignaturas' => 'Una o varias asignaturas pertenecen a carreras inactivas o ya no están disponibles.'
                    ])
                    ->withInput();
            }
        }

        // Obtiene el periodo seleccionado.
        $periodo = Periodo::findOrFail($datosValidados['id_periodo']);

        // Guarda la disponibilidad y las asignaturas seleccionadas.
        $this->guardarDatosDisponibilidad(
            $datosValidados,
            $docente->id_docente,
            $periodo->id_periodo
        );

        // Regresa al listado conservando el periodo en el que se registró la información.
        return redirect()->route('disponibilidad-academica.index', [
            'id_periodo' => $periodo->id_periodo
        ])->with(
            'mensaje_exito',
            $datosValidados['accion'] === 'entregar'
                ? 'La disponibilidad académica fue entregada correctamente.'
                : 'La disponibilidad académica se guardó como borrador.'
        );
    }

    /**
     * Descripción:
     * Mostrar el formulario para modificar la disponibilidad académica de un docente.
     *
     * Entradas:
     * $solicitud: Solicitud HTTP.
     * $idDocente: Identificador del docente.
     * $idPeriodo: Identificador del periodo académico.
     *
     * Salidas:
     * Vista para modificar la disponibilidad académica.
     */
    /**
     * Descripción:
     * Mostrar el formulario para modificar la disponibilidad académica de un docente.
     *
     * Entradas:
     * $solicitud: Solicitud HTTP.
     * $idDocente: Identificador del docente.
     * $idPeriodo: Identificador del periodo académico.
     *
     * Salidas:
     * Vista para modificar la disponibilidad académica.
     */
    public function edit(Request $solicitud, $idDocente, $idPeriodo)
    {
        // Verifica que el usuario tenga permiso para acceder al módulo.
        $this->verificarRol([1, 2, 3]);

        // Obtiene el docente registrado.
        $docente = Docente::with('usuario')
            ->findOrFail($idDocente);

        // El docente solamente puede modificar su propia disponibilidad.
        if (
            (int) auth()->user()->rol === 3 &&
            $docente->id_usuario !== auth()->id()
        ) {
            abort(403);
        }

        // Obtiene el periodo académico.
        $periodoSeleccionado = Periodo::findOrFail($idPeriodo);

        // Obtiene los periodos registrados.
        $periodos = Periodo::orderByDesc('anio')
            ->orderByDesc('id_periodo')
            ->get();

        // Obtiene las asignaturas de las carreras y cuatrimestres del periodo seleccionado.
        $asignaturasPorCarrera = $this->obtenerAsignaturasPorCarreraYCuatrimestre(
            $periodoSeleccionado->id_periodo
        );

        // Obtiene las asignaturas seleccionadas por el docente en el periodo.
        $asignaturasSeleccionadas = AsignaturaPreferida::where(
            'id_docente',
            $docente->id_docente
        )
            ->where('id_periodo', $periodoSeleccionado->id_periodo)
            ->pluck('id_asignatura')
            ->toArray();

        // Obtiene los bloques en los que el docente no está disponible.
        $disponibilidades = DisponibilidadProfesor::where(
            'id_docente',
            $docente->id_docente
        )
            ->where('id_periodo', $periodoSeleccionado->id_periodo)
            ->orderBy('dia')
            ->orderBy('hora_inicio')
            ->get();

        // Convierte los intervalos registrados en identificadores para la cuadrícula.
        $bloquesNoDisponibles = [];

        foreach ($disponibilidades as $disponibilidad) {

            // Obtiene la hora inicial y final del intervalo.
            $horaInicio = (int) substr($disponibilidad->hora_inicio, 0, 2);
            $horaFin = (int) substr($disponibilidad->hora_fin, 0, 2);

            // Recupera cada bloque de una hora incluido en el intervalo.
            for ($hora = $horaInicio; $hora < $horaFin; $hora++) {

                $bloquesNoDisponibles[] = (int) $disponibilidad->dia . '-' . $hora;
            }
        }

        // Indica que se está modificando una disponibilidad.
        $esEdicion = true;

        // Identificador del docente seleccionado.
        $idDocenteSeleccionado = $docente->id_docente;

        // Obtiene los docentes registrados.
        $docentesRegistrados = Docente::with('usuario')
            ->whereHas('usuario', function ($consulta) {
                $consulta->where('estado', 1);
            })
            ->get();

        // Obtiene las disponibilidades registradas del docente en el periodo.
        $disponibilidadesRegistradas = $disponibilidades;

        // Devuelve la vista de gestión de disponibilidad académica.
        return view('DisponibilidadAcademica.GestionDisponibilidadAcademica', compact(
            'periodos',
            'periodoSeleccionado',
            'idDocenteSeleccionado',
            'docentesRegistrados',
            'disponibilidadesRegistradas',
            'asignaturasPorCarrera',
            'docente',
            'asignaturasSeleccionadas',
            'bloquesNoDisponibles',
            'esEdicion'
        ));
    }

    /**
     * Descripción:
     * Actualizar la disponibilidad académica de un docente.
     *
     * Entradas:
     * $solicitud: Solicitud HTTP con la información modificada.
     * $idDocente: Identificador del docente.
     * $idPeriodo: Identificador del periodo académico.
     *
     * Salidas:
     * Redirección a la consulta de disponibilidad.
     */
    public function update(Request $solicitud, $idDocente, $idPeriodo)
    {
        // Verifica que el usuario tenga permiso para modificar.
        $this->verificarRol([1, 2, 3]);

        // Obtiene el docente registrado.
        $docente = Docente::findOrFail($idDocente);

        // El docente solamente puede modificar su propia disponibilidad.
        if (
            (int) auth()->user()->rol === 3 &&
            $docente->id_usuario !== auth()->id()
        ) {
            abort(403);
        }

        // Valida la información recibida.
        $datosValidados = $solicitud->validate([
            'id_periodo' => [
                'required',
                'integer',
                Rule::in([(int) $idPeriodo])
            ],
            'accion' => [
                'required',
                Rule::in(['borrador', 'entregar'])
            ],
            'bloques_no_disponibles' => [
                'required',
                'array',
                'min:1',
                'max:84'
            ],
            'bloques_no_disponibles.*' => [
                'required',
                'string',
                'distinct',
                'regex:/^(?:[1-5]-(?:[7-9]|1[0-9]|20)|6-(?:[7-9]|1[0-4]))$/'
            ],
            'asignaturas' => [
                'sometimes',
                'array',
                'max:1000'
            ],
            'asignaturas.*' => [
                'integer',
                'distinct',
                Rule::exists('asignatura', 'id_asignatura')
            ]
        ], [
            'bloques_no_disponibles.min' => 'Selecciona al menos un horario en el que no estés disponible.',
            'bloques_no_disponibles.*.distinct' => 'No puedes seleccionar dos veces el mismo horario.',
            'bloques_no_disponibles.*.regex' => 'Uno de los horarios seleccionados no es válido.',
            'asignaturas.*.exists' => 'Una de las asignaturas seleccionadas ya no está disponible.'
        ]);

        // Comprueba que las asignaturas seleccionadas sigan activas.
        $idsAsignaturas = $datosValidados['asignaturas'] ?? [];

        if (count($idsAsignaturas) > 0) {
            $totalAsignaturasValidas = Asignatura::whereIn(
                'id_asignatura',
                $idsAsignaturas
            )
                ->where('estado', 1)
                ->whereHas('carrera', function ($consulta) {
                    $consulta->where('estado', 1);
                })
                ->count();

            if ($totalAsignaturasValidas !== count($idsAsignaturas)) {
                return back()
                    ->withErrors([
                        'asignaturas' => 'Una o varias asignaturas pertenecen a carreras inactivas o ya no están disponibles.'
                    ])
                    ->withInput();
            }
        }

        // Guarda los cambios realizados.
        $this->guardarDatosDisponibilidad(
            $datosValidados,
            $docente->id_docente,
            $idPeriodo
        );

        return redirect()->route('disponibilidad-academica.show', [
            'idDocente' => $docente->id_docente,
            'idPeriodo' => $idPeriodo
        ])->with(
            'mensaje_exito',
            $datosValidados['accion'] === 'entregar'
                ? 'La disponibilidad académica fue entregada correctamente.'
                : 'Los cambios se guardaron como borrador.'
        );
    }

    /**
     * Descripción:
     * Eliminar la disponibilidad académica de un docente en un periodo.
     *
     * Entradas:
     * $idDocente: Identificador del docente.
     * $idPeriodo: Identificador del periodo académico.
     *
     * Salidas:
     * Redirección al listado de disponibilidades.
     */
    public function destroy($idDocente, $idPeriodo)
    {
        // Solamente el administrador puede eliminar una disponibilidad.
        $this->verificarRol([1]);

        // Elimina los bloques registrados.
        DisponibilidadProfesor::where('id_docente', $idDocente)
            ->where('id_periodo', $idPeriodo)
            ->delete();

        // Elimina las asignaturas preferidas del mismo periodo.
        AsignaturaPreferida::where('id_docente', $idDocente)
            ->where('id_periodo', $idPeriodo)
            ->delete();

        return redirect()
            ->route('disponibilidad-academica.index', [
                'id_periodo' => $idPeriodo
            ])
            ->with('mensaje_exito', 'La disponibilidad académica fue eliminada correctamente.');
    }


    /**
     * Descripción:
     * Guardar los intervalos no disponibles y las asignaturas preferidas del docente.
     *
     * Entradas:
     * $datosValidados: Información validada del formulario.
     * $idDocente: Identificador del docente.
     * $idPeriodo: Identificador del periodo académico.
     *
     * Salidas:
     * Registros actualizados en disponibilidadProfesor y asignatura_preferida.
     */
    private function guardarDatosDisponibilidad(array $datosValidados, $idDocente, $idPeriodo)
    {
        // Determina si la disponibilidad se guarda como borrador o se entrega.
        $estadoConfirmacion = $datosValidados['accion'] === 'entregar' ? 1 : 0;

        // Establece la fecha de confirmación únicamente cuando se entrega.
        $fechaConfirmacion = $estadoConfirmacion === 1 ? now() : null;

        // Elimina los intervalos anteriores del docente en el periodo.
        DisponibilidadProfesor::where('id_docente', $idDocente)
            ->where('id_periodo', $idPeriodo)
            ->delete();

        // Agrupa los bloques seleccionados por día.
        $bloquesPorDia = [];

        foreach ($datosValidados['bloques_no_disponibles'] as $bloque) {

            // Separa el día y la hora del bloque seleccionado.
            [$dia, $hora] = explode('-', $bloque);

            $bloquesPorDia[(int) $dia][] = (int) $hora;
        }

        // Recorre los días que contienen bloques seleccionados.
        foreach ($bloquesPorDia as $dia => $horas) {

            // Ordena las horas de menor a mayor.
            sort($horas);

            // Inicializa el intervalo con la primera hora.
            $horaInicio = $horas[0];
            $horaAnterior = $horas[0];

            // Recorre las horas para agrupar los bloques consecutivos.
            for ($i = 1; $i <= count($horas); $i++) {

                // Comprueba si existe otra hora consecutiva.
                if (isset($horas[$i]) && $horas[$i] === $horaAnterior + 1) {

                    $horaAnterior = $horas[$i];

                } else {

                    // Guarda el intervalo completo.
                    DisponibilidadProfesor::create([
                        'dia' => $dia,
                        'hora_inicio' => sprintf('%02d:00:00', $horaInicio),
                        'hora_fin' => sprintf('%02d:00:00', $horaAnterior + 1),
                        'estadoConfirmacion' => $estadoConfirmacion,
                        'fechaConfirmacion' => $fechaConfirmacion,
                        'id_docente' => $idDocente,
                        'id_periodo' => $idPeriodo
                    ]);

                    // Inicializa un nuevo intervalo si quedan horas por recorrer.
                    if (isset($horas[$i])) {
                        $horaInicio = $horas[$i];
                        $horaAnterior = $horas[$i];
                    }
                }
            }
        }

        // Elimina las asignaturas preferidas anteriores del mismo periodo.
        AsignaturaPreferida::where('id_docente', $idDocente)
            ->where('id_periodo', $idPeriodo)
            ->delete();

        // Registra las nuevas asignaturas seleccionadas.
        foreach ($datosValidados['asignaturas'] ?? [] as $idAsignatura) {

            AsignaturaPreferida::create([
                'id_periodo' => $idPeriodo,
                'id_docente' => $idDocente,
                'id_asignatura' => $idAsignatura
            ]);
        }
    }



    /**
     * Descripción:
     * Obtiene las asignaturas activas de las carreras activas,
     * de acuerdo con los cuatrimestres correspondientes a la
     * estación del periodo académico seleccionado.
     *
     * Entradas:
     * - $idPeriodo: Identificador del periodo académico.
     *
     * Salidas:
     * - Arreglo de carreras con sus respectivos cuatrimestres
     *   y asignaturas activas.
     */
    private function obtenerAsignaturasPorCarreraYCuatrimestre($idPeriodo)
    {
        // Obtiene el periodo académico seleccionado.
        $periodo = Periodo::find($idPeriodo);

        // Comprueba que el periodo exista.
        if (!$periodo) {
            return [];
        }

        // Determina los cuatrimestres correspondientes a la estación.
        $cuatrimestresPeriodo = match (mb_strtolower(trim($periodo->estacion))) {
            'otoño' => [1, 4, 7, 10],
            'invierno' => [2, 5, 8],
            'primavera' => [3, 6, 9],
            default => []
        };

        // Comprueba que la estación tenga cuatrimestres registrados.
        if (empty($cuatrimestresPeriodo)) {
            return [];
        }

        // Obtiene todas las carreras activas.
        $carrerasActivas = Carrera::where('estado', 1)
            ->orderBy('nombreCarrera')
            ->get();

        // Inicializa el arreglo de asignaturas por carrera.
        $asignaturasPorCarrera = [];

        // Recorre las carreras activas.
        foreach ($carrerasActivas as $carrera) {

            // Obtiene las asignaturas activas de la carrera
            // que pertenecen a los cuatrimestres del periodo.
            $asignaturas = Asignatura::where('id_carrera', $carrera->id_carrera)
                ->whereIn('cuatrimestre', $cuatrimestresPeriodo)
                ->where('estado', 1)
                ->orderBy('cuatrimestre')
                ->orderBy('nombreAsignatura')
                ->get()
                ->groupBy('cuatrimestre');

            // Inicializa los cuatrimestres de la carrera.
            $cuatrimestres = [];

            // Organiza las asignaturas por cuatrimestre.
            foreach ($cuatrimestresPeriodo as $cuatrimestre) {

                // Obtiene las asignaturas del cuatrimestre actual.
                $asignaturasCuatrimestre = $asignaturas->get($cuatrimestre, collect());

                // Agrega el cuatrimestre si tiene asignaturas.
                if ($asignaturasCuatrimestre->isNotEmpty()) {

                    $cuatrimestres[] = [
                        'cuatrimestre' => $cuatrimestre,
                        'asignaturas' => $asignaturasCuatrimestre
                    ];
                }
            }

            // Agrega la carrera si tiene asignaturas disponibles.
            if (!empty($cuatrimestres)) {

                $asignaturasPorCarrera[$carrera->id_carrera] = [
                    'carrera' => $carrera,
                    'cuatrimestres' => $cuatrimestres
                ];
            }
        }

        return $asignaturasPorCarrera;
    }


    /**
     * Descripción: Comprueba que el usuario autenticado tenga uno de los roles permitidos.
     * Entradas:
     * $rolesPermitidos: identificadores de los roles autorizados.
     * Salidas: No devuelve datos; genera un error 403 si el usuario no tiene permiso.
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