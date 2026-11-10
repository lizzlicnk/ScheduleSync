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
     * Descripción: Muestra la disponibilidad horaria de los docentes registrados.
     *
     * Entradas:
     * - $solicitud: Solicitud HTTP del usuario autenticado.
     *
     * Salidas:
     * - Vista de gestión con la disponibilidad de los docentes.
     */
    public function index(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2, 3]);

        // Obtener la información necesaria para mostrar la vista.
        $datos = $this->datosVista(
            $solicitud->input('id_periodo'),
            $solicitud->query('id_docente_consulta'),
            $solicitud->input('id_docente')
        );

        // Enviar la información a la vista.
        return view(
            'DisponibilidadAcademica.GestionDisponibilidadAcademica',
            $datos
        );
    }

    /**
     * Descripción: Muestra la disponibilidad horaria de un docente.
     *
     * Entradas:
     * - $idDocente: Identificador del docente.
     * - $idPeriodo: Identificador del periodo académico.
     *
     * Salidas:
     * - Redirección al panel de consulta de disponibilidad.
     */
    public function show($idDocente, $idPeriodo)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2, 3]);

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Buscar el docente seleccionado.
        $docenteEncontrado = Docente::with('usuario')
            ->findOrFail($idDocente);

        // Comprobar que el docente solamente consulte su propia información.
        if ((int) $usuarioAutenticado->rol === 3) {
            abort_unless(
                (int) $docenteEncontrado->id_usuario ===
                    (int) $usuarioAutenticado->id_usuario,
                403
            );
        }

        // Comprobar que el periodo académico exista.
        Periodo::findOrFail($idPeriodo);

        // Redirigir al listado con el docente seleccionado para consulta.
        return redirect()->to(
            route('disponibilidad-academica.index', [
                'id_periodo' => $idPeriodo,
                'id_docente_consulta' => $idDocente
            ]) . '#panel-consulta'
        );
    }

    /**
     * Descripción: Muestra el formulario para registrar la disponibilidad
     * académica de un docente.
     *
     * Entradas:
     * - $solicitud: Solicitud HTTP con el periodo seleccionado.
     * - $idDocente: Identificador del docente seleccionado.
     *
     * Salidas:
     * - Vista de gestión con el formulario de registro.
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
                    ->with('alerta_error', 'No hay periodos académicos registrados.');
            }

            // Buscar el docente seleccionado.
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

        // Obtener los datos compartidos de la vista.
        $datos = $this->datosVista(
            $periodoSeleccionado,
            null,
            $docente->id_docente
        );

        
        // Agregar la información específica del formulario.
        $datos['docente'] = $docente;
        $datos['periodos'] = $periodos;
        $datos['asignaturasSeleccionadas'] = [];
        $datos['bloquesNoDisponibles'] = [];
        $datos['esEdicion'] = false;
        $datos['esRegistro'] = true;

        // Enviar la información a la vista.
        return view(
            'DisponibilidadAcademica.GestionDisponibilidadAcademica',
            $datos
        );
    }

    /**
     * Descripción: Obtiene los datos compartidos que necesita la vista de disponibilidad académica.
     *
     * Entradas:
     * @param int|null $idPeriodo
     * @param int|null $idDocenteConsulta
     * @param int|null $idDocenteSeleccionado
     *
     * Salidas:
     * @return array
     */
    private function datosVista(
        $idPeriodo = null,
        $idDocenteConsulta = null,
        $idDocenteSeleccionado = null
    ): array {

        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Consultar los periodos registrados.
        $periodosRegistrados = Periodo::orderBy('anio', 'desc')
            ->orderBy('estacion')
            ->get();

        // Identificar el periodo seleccionado.
        $periodoSeleccionado = $idPeriodo
            ?: $periodosRegistrados->first()?->id_periodo;

        // Consultar los docentes activos junto con sus datos de usuario.
        $consultaDocentes = Usuario::where('rol', 3)
            ->where('estado', 1)
            ->with('docente');

        // Si el usuario autenticado es docente, consultar únicamente su información.
        if ((int) $usuarioAutenticado->rol === 3) {
            $consultaDocentes->where(
                'id_usuario',
                $usuarioAutenticado->id_usuario
            );
        }

        $docentesRegistrados = $consultaDocentes
            ->orderBy('nombreUsuario')
            ->get();

        // Consultar las disponibilidades registradas para el periodo seleccionado.
        $disponibilidadesRegistradas = DisponibilidadProfesor::where(
            'id_periodo',
            $periodoSeleccionado
        )->get();

        // Definir los días de la semana.
        $diasSemana = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];

        // Preparar la información de los docentes para el panel de consulta.
        $docentesConsulta = collect();

        foreach ($docentesRegistrados as $usuario) {

            // Comprobar que el usuario tenga información de docente.
            if (!$usuario->docente) {
                continue;
            }

            $docente = $usuario->docente;

            // Consultar la disponibilidad del docente en el periodo seleccionado.
            $disponibilidadesDocente = $disponibilidadesRegistradas
                ->where('id_docente', $docente->id_docente);

            // Omitir docentes que no tengan disponibilidad registrada.
            if ($disponibilidadesDocente->isEmpty()) {
                continue;
            }

            // Consultar las asignaturas preferidas del docente.
            $asignaturas = AsignaturaPreferida::where(
                'id_docente',
                $docente->id_docente
            )
                ->where('id_periodo', $periodoSeleccionado)
                ->with('asignatura.carrera')
                ->get()
                ->pluck('asignatura')
                ->filter()
                ->sortBy('cuatrimestre')
                ->values();

            // Agrupar los horarios por día.
            $horarios = [];

            foreach ($diasSemana as $numeroDia => $nombreDia) {

                $horarios[$nombreDia] = $disponibilidadesDocente
                    ->where('dia', $numeroDia)
                    ->map(function ($disponibilidad) {
                        return [
                            'hora_inicio' => substr(
                                $disponibilidad->hora_inicio,
                                0,
                                5
                            ),
                            'hora_fin' => substr(
                                $disponibilidad->hora_fin,
                                0,
                                5
                            ),
                        ];
                    })
                    ->values();
            }

            // Obtener el nombre completo del docente.
            $nombreCompleto = trim(
                $usuario->nombreUsuario . ' ' .
                ($usuario->apellidoPaterno ?? '') . ' ' .
                ($usuario->apellidoMaterno ?? '')
            );

            // Agregar la información del docente a la colección.
            $docentesConsulta->push([
                'id_docente' => $docente->id_docente,
                'nombre' => $nombreCompleto,
                'matricula' => $docente->matricula,
                'horarios' => $horarios,
                'asignaturas' => $asignaturas,
            ]);
        }

        // Comprobar que el docente solicitado exista en los resultados.
        if (
            $idDocenteConsulta &&
            !$docentesConsulta->contains('id_docente', (int) $idDocenteConsulta)
        ) {
            $idDocenteConsulta = null;
        }

        // Consultar las carreras activas.
        $carreras = Carrera::where('estado', 1)
            ->orderBy('nombreCarrera')
            ->get();

        // Obtener las asignaturas organizadas por carrera y cuatrimestre.
        $asignaturasPorCarrera = $this->obtenerAsignaturasPorCarreraYCuatrimestre(
            $periodoSeleccionado
        );

        // Inicializar los datos del formulario.
        $docente = null;
        $periodos = $periodosRegistrados;
        $asignaturasSeleccionadas = [];
        $bloquesNoDisponibles = [];
        $esEdicion = false;
        $esRegistro = false;

        // Devolver todas las variables necesarias para la vista.
        return compact(
            'periodosRegistrados',
            'periodoSeleccionado',
            'idDocenteSeleccionado',
            'docentesRegistrados',
            'disponibilidadesRegistradas',
            'docentesConsulta',
            'idDocenteConsulta',
            'carreras',
            'asignaturasPorCarrera',
            'docente',
            'periodos',
            'asignaturasSeleccionadas',
            'bloquesNoDisponibles',
            'esEdicion',
            'esRegistro'
        );
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

        // Obtener el rol del usuario autenticado.
        $rolUsuario = (int) auth()->user()->rol;

        // Definir las reglas de validación.
        $reglas = [
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
                'required',
                'array',
                'min:1',
                'max:1000'
            ],

            'asignaturas.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('asignatura', 'id_asignatura')
            ]
        ];

        // Solicitar la selección del docente a los usuarios administradores.
        if (in_array($rolUsuario, [1, 2])) {
            $reglas['id_docente'] = [
                'required',
                'integer',
                Rule::exists('docente', 'id_docente')
            ];
        }

        // Definir los mensajes personalizados.
        $mensajes = [
            'id_docente.required' => 'Te falta seleccionar un docente.',
            'id_docente.integer' => 'El docente seleccionado no es válido.',
            'id_docente.exists' => 'El docente seleccionado no se encuentra registrado.',

            'id_periodo.required' => 'Te falta seleccionar un periodo académico.',
            'id_periodo.integer' => 'El periodo académico seleccionado no es válido.',
            'id_periodo.exists' => 'El periodo académico seleccionado no existe.',

            'accion.required' => 'Selecciona qué acción deseas realizar.',

            'bloques_no_disponibles.required' => 'Te falta registrar los horarios en la tabla de disponibilidad.',
            'bloques_no_disponibles.array' => 'Los horarios seleccionados no son válidos.',
            'bloques_no_disponibles.min' => 'Te falta seleccionar al menos un horario en la tabla de disponibilidad.',
            'bloques_no_disponibles.max' => 'La cantidad de horarios seleccionados no es válida.',

            'bloques_no_disponibles.*.distinct' => 'No puedes seleccionar dos veces el mismo horario.',
            'bloques_no_disponibles.*.regex' => 'Uno de los horarios seleccionados no es válido.',

            'asignaturas.required' => 'Te falta seleccionar al menos una asignatura.',
            'asignaturas.array' => 'La selección de asignaturas no es válida.',
            'asignaturas.min' => 'Te falta seleccionar al menos una asignatura.',
            'asignaturas.max' => 'La cantidad de asignaturas seleccionadas no es válida.',
            'asignaturas.*.exists' => 'Una de las asignaturas seleccionadas ya no está disponible.'
        ];

        // Validar la información recibida.
        $datosValidados = $solicitud->validate($reglas, $mensajes);

        // Obtener el docente correspondiente al usuario autenticado.
        if ($rolUsuario === 3) {

            $docente = Docente::where(
                'id_usuario',
                auth()->id()
            )->firstOrFail();

        } else {

            // Buscar el docente seleccionado.
            $idDocente = $idDocente ?? $datosValidados['id_docente'];

            $docente = Docente::findOrFail($idDocente);
        }

        // Obtener los identificadores de las asignaturas seleccionadas.
        $idsAsignaturas = $datosValidados['asignaturas'];

        // Comprobar que las asignaturas seleccionadas sigan activas.
        $totalAsignaturasValidas = Asignatura::whereIn(
            'id_asignatura',
            $idsAsignaturas
        )
            ->where('estado', 1)
            ->whereHas('carrera', function ($consulta) {
                $consulta->where('estado', 1);
            })
            ->count();

        // Comprobar que todas las asignaturas sean válidas.
        if ($totalAsignaturasValidas !== count($idsAsignaturas)) {
            return back()
                ->with('alerta_error', 'Una o varias asignaturas pertenecen a carreras inactivas o ya no están disponibles.')
                ->withInput();
        }

        // Obtener el periodo seleccionado.
        $periodo = Periodo::findOrFail($datosValidados['id_periodo']);

        // Guardar la disponibilidad y las asignaturas seleccionadas.
        $this->guardarDatosDisponibilidad(
            $datosValidados,
            $docente->id_docente,
            $periodo->id_periodo
        );

        return redirect()->route('disponibilidad-academica.index', [
            'id_periodo' => $periodo->id_periodo
        ])->with(
            'alerta_exito',
            $datosValidados['accion'] === 'entregar'
                ? 'La disponibilidad académica fue entregada correctamente.'
                : 'La disponibilidad académica se guardó como borrador.'
        );

    }

    /**
     * Descripción: Muestra el formulario para modificar la disponibilidad
     * académica de un docente.
     *
     * Entradas:
     * - $solicitud: Solicitud HTTP.
     * - $idDocente: Identificador del docente.
     * - $idPeriodo: Identificador del periodo académico.
     *
     * Salidas:
     * - Vista de gestión con el formulario de modificación.
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
            (int) $docente->id_usuario !== (int) auth()->id()
        ) {
            abort(403);
        }

        // Obtiene el periodo académico.
        $periodoSeleccionado = Periodo::findOrFail($idPeriodo);

        // Obtiene los periodos registrados.
        $periodos = Periodo::orderByDesc('anio')
            ->orderByDesc('id_periodo')
            ->get();

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

        // Obtener los datos compartidos de la vista.
        $datos = $this->datosVista(
            $periodoSeleccionado->id_periodo,
            null,
            $docente->id_docente
        );

        // Agregar la información específica del formulario.
        $datos['docente'] = $docente;
        $datos['periodos'] = $periodos;
        $datos['asignaturasSeleccionadas'] = $asignaturasSeleccionadas;
        $datos['bloquesNoDisponibles'] = $bloquesNoDisponibles;
        $datos['esEdicion'] = true;
        $datos['esRegistro'] = false;

        // Enviar la información a la vista.
        return view(
            'DisponibilidadAcademica.EditarDisponibilidadAcademica',
            $datos
        );
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
                    ->with('alerta_error', 'Una o varias asignaturas pertenecen a carreras inactivas o ya no están disponibles.')
                    ->withInput();
            }
        }

        // Guarda los cambios realizados.
        $this->guardarDatosDisponibilidad(
            $datosValidados,
            $docente->id_docente,
            $idPeriodo
        );

        return redirect()->to(
            route('disponibilidad-academica.index', [
                'id_periodo' => $idPeriodo,
                'id_docente_consulta' => $docente->id_docente
            ]) . '#panel-consulta'
        )->with(
            'alerta_exito',
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


            // Regresar al listado con un mensaje de confirmación.
return redirect()
    ->route('disponibilidad-academica.index', [
        'id_periodo' => $idPeriodo
    ])
    ->with('alerta_exito', 'La disponibilidad académica fue eliminada correctamente.');
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