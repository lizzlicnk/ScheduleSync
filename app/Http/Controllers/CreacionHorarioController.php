<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Horario;
use App\Models\DetalleHorario;
use App\Models\Asignatura;
use App\Models\AsignaturaPreferida;
use App\Models\Docente;
use App\Models\DisponibilidadProfesor;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Carrera;

class CreacionHorarioController extends Controller
{
    /**
     * Descripción: Muestra los grupos registrados y permite consultar
     * el horario académico seleccionado.
     *
     * Entradas: Recibe los parámetros de la solicitud HTTP.
     *
     * Salidas: Retorna la vista de gestión de horarios con los grupos,
     * periodos, carreras, asignaturas y docentes disponibles.
     */
    public function index(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Consultar los grupos registrados con su carrera, periodo y horarios.
        $gruposRegistrados = Grupo::with([
            'carrera',
            'periodo',
            'horarios'
        ])
            ->orderBy('id_grupo', 'desc')
            ->get();

        // Consultar los horarios académicos registrados y activos.
        $horariosDisponibles = Horario::with([
            'grupo.carrera',
            'periodo'
        ])
            ->where('estado', 1)
            ->orderBy('id_horario', 'desc')
            ->get();

        // Consultar los periodos escolares disponibles.
        $periodosDisponibles = Periodo::orderBy('anio', 'desc')
            ->orderBy('estacion')
            ->get();

        // Consultar las carreras registradas.
        $carrerasDisponibles = Carrera::orderBy('nombreCarrera')
            ->get();

        // Inicializar los datos del horario seleccionado.
        $horarioConsultado = null;
        $asignaturasDisponibles = collect();
        $docentesDisponibles = collect();


        // Comprobar si se seleccionó un horario.
        if ($solicitud->filled('consultar')) {

            // Validar el identificador del horario.
            $datosValidados = $solicitud->validate([
                'consultar' => [
                    'required',
                    'integer',
                    'min:1'
                ]
            ]);

            // Consultar el horario seleccionado y sus relaciones.
            $horarioConsultado = Horario::where('estado', 1)
                ->with([
                    'grupo',
                    'grupo.carrera',
                    'periodo',
                    'detalles.docente.usuario',
                    'detalles.asignatura'
                ])
                ->findOrFail($datosValidados['consultar']);

            // Obtener el grupo correspondiente al horario.
            $grupoSeleccionado = $horarioConsultado->grupo;

            // Consultar las asignaturas de la carrera y cuatrimestre del grupo.
            $asignaturasDisponibles = Asignatura::where(
                'id_carrera',
                $grupoSeleccionado->id_carrera
            )
                ->where(
                    'cuatrimestre',
                    $grupoSeleccionado->cuatrimestre
                )
                ->where('estado', 1)
                ->orderBy('nombreAsignatura')
                ->get();

            // Consultar los docentes que tienen una cuenta activa.
            $docentesDisponibles = Docente::whereHas(
                'usuario',
                function ($consulta) {
                    $consulta->where('estado', 1);
                }
            )
                ->with('usuario')
                ->orderBy('matricula')
                ->get();


            // Consultar las disponibilidades confirmadas de los docentes
            // correspondientes al periodo del horario seleccionado.
            $disponibilidadesDocentes = DisponibilidadProfesor::where(
                'id_periodo',
                $horarioConsultado->id_periodo
            )
                ->where('estadoConfirmacion', 1)
                ->get([
                    'id_docente',
                    'dia',
                    'hora_inicio',
                    'hora_fin',
                    'estadoConfirmacion'
                ]);
                
        }

        $idHorarioAgregar = $solicitud->query('agregar');
        
        // Retornar la vista con la información necesaria.
        return view('Horario.CreaciónHorario.GestionCreaciónHorario',
            compact(
                'gruposRegistrados',
                'periodosDisponibles',
                'carrerasDisponibles',
                'horarioConsultado',
                'asignaturasDisponibles',
                'docentesDisponibles',
                'horariosDisponibles',
                'idHorarioAgregar'
            )
        );
    }


    
    /**
     * Descripción: Registra las asignaciones académicas en un horario existente.
     *
     * Entradas:
     * $solicitud: Contiene el identificador del horario y sus bloques asignados.
     *
     * Salidas:
     * Retorna una respuesta indicando si las asignaciones fueron registradas.
     */
    public function store(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Validar los datos recibidos.
        $datosValidados = $this->validarDatosHorario($solicitud);

        // Consultar el horario seleccionado.
        $horarioSeleccionado = Horario::with([
            'grupo',
            'periodo'
        ])
            ->where('estado', 1)
            ->findOrFail($datosValidados['id_horario']);

        // Obtener el grupo correspondiente al horario.
        $grupoSeleccionado = $horarioSeleccionado->grupo;

        // Comprobar que el grupo se encuentre activo.
        if (!$grupoSeleccionado || (int) $grupoSeleccionado->estado !== 1) {
            throw ValidationException::withMessages([
                'id_horario' => 'El grupo correspondiente al horario seleccionado se encuentra inactivo.'
            ]);
        }

        // Obtener el periodo correspondiente al horario.
        $idPeriodo = (int) $horarioSeleccionado->id_periodo;

        // Comprobar que el periodo sea del año 2026 en adelante.
        if (
            !$horarioSeleccionado->periodo ||
            (int) $horarioSeleccionado->periodo->anio < 2026
        ) {
            throw ValidationException::withMessages([
                'id_horario' => 'Solo se pueden registrar asignaciones en horarios de periodos del año 2026 en adelante.'
            ]);
        }

        // =========================================
        // COMPROBAR SI EL HORARIO YA TIENE ASIGNACIONES
        // =========================================

        // Evitar que se registren nuevamente asignaciones en el mismo horario.
        if ($horarioSeleccionado->detalles()->exists()) {
            throw ValidationException::withMessages([
                'id_horario' => 'Este horario ya cuenta con asignaciones registradas. No es posible volver a registrarlas.'
            ]);
        }

        // Validar las asignaciones antes de guardar.
        $detallesValidados = $this->validarAsignaciones(
            $datosValidados['detalles'],
            $grupoSeleccionado,
            $idPeriodo,
            $horarioSeleccionado->id_horario
        );

        // =========================================
        // VALIDAR LA CARGA ACADÉMICA DE LOS DOCENTES
        // =========================================

        // Comprobar que ningún docente supere su carga académica.
        $this->validarCargaAcademicaDocentes(
            $detallesValidados,
            $idPeriodo,
            $horarioSeleccionado->id_horario
        );

        // =========================================
        // GUARDAR LAS ASIGNACIONES
        // =========================================

        // Registrar los detalles en el horario seleccionado.
        DB::transaction(function () use (
            $horarioSeleccionado,
            $detallesValidados
        ) {
            // Guardar los bloques asignados al horario existente.
            $this->guardarDetallesHorario(
                $horarioSeleccionado,
                $detallesValidados
            );
        });

        // Retornar a la pantalla anterior con el mensaje de éxito.
        return redirect()->back()->with(
            'success',
            'Las asignaciones del horario se registraron correctamente.'
        );
    }



    /**
     * Descripción: Valida el identificador del horario y sus bloques asignados.
     *
     * Entradas:
     * $solicitud: Datos enviados desde el formulario.
     *
     * Salidas:
     * Retorna los datos validados.
     */
    private function validarDatosHorario(Request $solicitud): array
    {
        return $solicitud->validate([
            'id_horario' => [
                'required',
                'integer',
                'exists:horario,id_horario'
            ],
            'detalles' => [
                'required',
                'array',
                'min:1'
            ],
            'detalles.*.dia' => [
                'required',
                'string',
                'max:15'
            ],
            'detalles.*.hora_inicio' => [
                'required',
                'date_format:H:i'
            ],
            'detalles.*.hora_fin' => [
                'required',
                'date_format:H:i'
            ],
            'detalles.*.aula' => [
                'nullable',
                'string',
                'max:20'
            ],
            'detalles.*.id_docente' => [
                'required',
                'integer',
                'exists:docente,id_docente'
            ],
            'detalles.*.id_asignatura' => [
                'required',
                'integer',
                'exists:asignatura,id_asignatura'
            ]
        ]);
    }


    /**
     * Descripción: Comprueba las reglas de negocio de todas las asignaciones.
     *
     * Entradas:
     * $detalles: Bloques recibidos desde el calendario.
     * $grupoSeleccionado: Grupo al que pertenece el horario.
     * $idPeriodo: Periodo escolar seleccionado.
     * $idHorario: Identificador del horario que se modifica, si corresponde.
     *
     * Salidas:
     * Retorna los bloques normalizados y validados.
     */
    private function validarAsignaciones(
        array $detalles,
        Grupo $grupoSeleccionado,
        int $idPeriodo,
        ?int $idHorario = null
    ): array {
        // Normalizar los días y comprobar la duración de cada bloque.
        foreach ($detalles as $indice => $detalle) {
            $diaNormalizado = $this->normalizarDia($detalle['dia']);

            if (!$diaNormalizado) {
                throw ValidationException::withMessages([
                    "detalles.$indice.dia" => 'El día seleccionado no es válido.'
                ]);
            }

            $detalles[$indice]['dia'] = $diaNormalizado;

            // Comprobar que la hora final sea posterior a la inicial.
            if (
                strtotime($detalle['hora_fin']) <=
                strtotime($detalle['hora_inicio'])
            ) {
                throw ValidationException::withMessages([
                    "detalles.$indice.hora_fin" => 'La hora final debe ser posterior a la hora inicial.'
                ]);
            }

            // Comprobar que la duración sea de horas completas.
            $duracion = (
                strtotime($detalle['hora_fin']) -
                strtotime($detalle['hora_inicio'])
            ) / 3600;

            if ($duracion <= 0 || $duracion != (int) $duracion) {
                throw ValidationException::withMessages([
                    "detalles.$indice.hora_fin" => 'La asignación debe tener una duración en horas completas.'
                ]);
            }
        }

        // Comprobar que las asignaturas y docentes sean válidos.
        foreach ($detalles as $indice => $detalle) {
            $asignatura = Asignatura::where('estado', 1)
                ->where('id_carrera', $grupoSeleccionado->id_carrera)
                ->where('cuatrimestre', $grupoSeleccionado->cuatrimestre)
                ->find($detalle['id_asignatura']);

            if (!$asignatura) {
                throw ValidationException::withMessages([
                    "detalles.$indice.id_asignatura" => 'La asignatura no pertenece a la carrera y cuatrimestre del grupo.'
                ]);
            }

            // Comprobar que el docente tenga una cuenta activa.
            $docente = Docente::whereHas('usuario', function ($consulta) {
                $consulta->where('estado', 1);
            })->find($detalle['id_docente']);

            if (!$docente) {
                throw ValidationException::withMessages([
                    "detalles.$indice.id_docente" => 'El docente no tiene una cuenta activa.'
                ]);
            }

            // Comprobar que el docente haya registrado la asignatura como preferida.
            $asignaturaPreferida = AsignaturaPreferida::where(
                'id_periodo',
                $idPeriodo
            )
                ->where('id_docente', $detalle['id_docente'])
                ->where('id_asignatura', $detalle['id_asignatura'])
                ->exists();

            if (!$asignaturaPreferida) {
                throw ValidationException::withMessages([
                    "detalles.$indice.id_docente" => 'El docente no tiene registrada esta asignatura como preferida para el periodo seleccionado.'
                ]);
            }

            // Comprobar la disponibilidad confirmada del docente.
            $this->validarDisponibilidadDocente(
                $detalle,
                $idPeriodo,
                $indice
            );
        }

        // Comprobar que no existan empalmes entre bloques.
        $this->validarEmpalmes(
            $detalles,
            $idPeriodo,
            $idHorario
        );

        // Comprobar que no se excedan las horas de cada asignatura.
        $this->validarHorasAsignatura($detalles);

        return $detalles;
    }

    /**
     * Descripción: Comprueba que el docente tenga disponibilidad confirmada
     * durante todo el bloque asignado.
     *
     * Entradas:
     * $detalle: Bloque que se desea asignar.
     * $idPeriodo: Periodo escolar seleccionado.
     * $indice: Posición del bloque en la solicitud.
     *
     * Salidas:
     * No devuelve datos; genera un error si no existe disponibilidad.
     */
    /*
    private function validarDisponibilidadDocente(
        array $detalle,
        int $idPeriodo,
        int $indice
    ): void {
        // Obtener el número correspondiente al día.
        $numeroDia = $this->obtenerNumeroDia($detalle['dia']);

        // Buscar una disponibilidad confirmada que cubra todo el bloque.
        $disponibilidad = DisponibilidadProfesor::where(
            'id_docente',
            $detalle['id_docente']
        )
            ->where('id_periodo', $idPeriodo)
            ->where('dia', $numeroDia)
            ->where('estadoConfirmacion', 1)
            ->where('hora_inicio', '<=', $detalle['hora_inicio'])
            ->where('hora_fin', '>=', $detalle['hora_fin'])
            ->exists();

        if (!$disponibilidad) {
            throw ValidationException::withMessages([
                "detalles.$indice.id_docente" => 'El docente no tiene disponibilidad confirmada durante todo el bloque seleccionado.'
            ]);
        }
    }*/

    /**
     * Descripción:
     * Comprueba que el docente no tenga registrado un bloque
     * de indisponibilidad que coincida con el horario asignado.
     *
     * Entradas:
     * $detalle: Bloque que se desea asignar.
     * $idPeriodo: Periodo escolar seleccionado.
     * $indice: Posición del bloque en la solicitud.
     *
     * Salidas:
     * No devuelve datos; genera un error si el docente
     * no puede impartir clases durante ese horario.
     */
    private function validarDisponibilidadDocente(
        array $detalle,
        int $idPeriodo,
        int $indice
    ): void {

        // Obtener el número correspondiente al día.
        $numeroDia = $this->obtenerNumeroDia($detalle['dia']);

        // Buscar si existe algún bloque de indisponibilidad
        // que se cruce con el horario que se desea asignar.
        $tieneIndisponibilidad = DisponibilidadProfesor::where(
            'id_docente',
            $detalle['id_docente']
        )
            ->where('id_periodo', $idPeriodo)
            ->where('dia', $numeroDia)
            ->where('estadoConfirmacion', 1)
            ->where('hora_inicio', '<', $detalle['hora_fin'])
            ->where('hora_fin', '>', $detalle['hora_inicio'])
            ->exists();

        // Impedir la asignación si existe un cruce.
        if ($tieneIndisponibilidad) {
            throw ValidationException::withMessages([
                "detalles.$indice.id_docente" =>
                    'El docente no está disponible durante ese horario. Selecciona otro bloque o docente.'
            ]);
        }
    }


    /**
     * Descripción: Comprueba que los bloques no se empalmen entre sí
     * ni con otros horarios activos del mismo periodo.
     *
     * Entradas:
     * $detalles: Bloques que se desean guardar.
     * $idPeriodo: Periodo escolar seleccionado.
     * $idHorario: Horario que se modifica, si corresponde.
     *
     * Salidas:
     * No devuelve datos; genera un error cuando encuentra un empalme.
     */
    private function validarEmpalmes(
        array $detalles,
        int $idPeriodo,
        ?int $idHorario = null
    ): void {
        // Comparar los bloques que se enviaron en la solicitud.
        foreach ($detalles as $indice => $detalle) {
            foreach ($detalles as $otroIndice => $otroDetalle) {

                // Evitar comparar un bloque consigo mismo.
                if ($indice >= $otroIndice) {
                    continue;
                }

                // Comprobar si los bloques pertenecen al mismo día
                // y sus horas se cruzan.
                $mismoDia = $detalle['dia'] === $otroDetalle['dia'];

                $horasSeCruzan =
                    $detalle['hora_inicio'] < $otroDetalle['hora_fin']
                    && $detalle['hora_fin'] > $otroDetalle['hora_inicio'];

                if ($mismoDia && $horasSeCruzan) {
                    throw ValidationException::withMessages([
                        "detalles.$otroIndice.hora_inicio" => 'Existe un empalme entre las asignaciones del grupo.'
                    ]);
                }
            }
        }

        // Comprobar empalmes con otros horarios activos del periodo.
        foreach ($detalles as $indice => $detalle) {
            $empalmeDocente = DetalleHorario::where(
                'id_docente',
                $detalle['id_docente']
            )
                ->where('dia', $detalle['dia'])
                ->where('hora_inicio', '<', $detalle['hora_fin'])
                ->where('hora_fin', '>', $detalle['hora_inicio'])
                ->whereHas('horario', function ($consulta) use (
                    $idPeriodo,
                    $idHorario
                ) {
                    $consulta->where('estado', 1)
                        ->where('id_periodo', $idPeriodo);

                    // Excluir el horario que se está modificando.
                    if ($idHorario !== null) {
                        $consulta->where('id_horario', '!=', $idHorario);
                    }
                })
                ->exists();

            if ($empalmeDocente) {
                throw ValidationException::withMessages([
                    "detalles.$indice.id_docente" => 'El docente ya tiene otra clase asignada durante ese horario.'
                ]);
            }
        }
    }

    /**
     * Descripción: Comprueba que las horas asignadas a cada asignatura
     * no superen las horas semanales establecidas.
     *
     * Entradas:
     * $detalles: Bloques que se desean guardar.
     *
     * Salidas:
     * No devuelve datos; genera un error si se exceden las horas.
     */
    private function validarHorasAsignatura(array $detalles): void
    {
        // Agrupar las horas asignadas por asignatura.
        $horasAsignadas = [];

        foreach ($detalles as $detalle) {
            $idAsignatura = $detalle['id_asignatura'];

            $duracion = (
                strtotime($detalle['hora_fin']) -
                strtotime($detalle['hora_inicio'])
            ) / 3600;

            if (!isset($horasAsignadas[$idAsignatura])) {
                $horasAsignadas[$idAsignatura] = 0;
            }

            $horasAsignadas[$idAsignatura] += $duracion;
        }

        // Comprobar las horas requeridas de cada asignatura.
        foreach ($horasAsignadas as $idAsignatura => $totalHoras) {
            $asignatura = Asignatura::findOrFail($idAsignatura);

            if ($totalHoras > $asignatura->cantidadHoras) {
                throw ValidationException::withMessages([
                    'detalles' => 'La asignatura "' . $asignatura->nombreAsignatura .
                        '" requiere ' . $asignatura->cantidadHoras .
                        ' horas y no se pueden asignar ' . $totalHoras . ' horas.'
                ]);
            }
        }
    }

    /**
     * Descripción: Guarda los bloques de asignación relacionados con un horario.
     *
     * Entradas:
     * $horario: Horario al que pertenecen las asignaciones.
     * $detalles: Bloques previamente validados.
     *
     * Salidas:
     * Guarda los detalles en la base de datos.
     */
    private function guardarDetallesHorario(
        Horario $horario, array $detalles ): void {
        // Recorrer los bloques que se van a registrar.
        foreach ($detalles as $detalle) {
            $horario->detalles()->create([
                'dia' => $detalle['dia'],
                'hora_inicio' => $detalle['hora_inicio'],
                'hora_fin' => $detalle['hora_fin'],
                'aula' => $detalle['aula'] ?? null,
                'id_docente' => $detalle['id_docente'],
                'id_asignatura' => $detalle['id_asignatura']
            ]);
        }
    }

    /**
     * Descripción: Convierte el nombre del día a un formato uniforme.
     *
     * Entradas:
     * $dia: Nombre del día recibido desde el calendario.
     *
     * Salidas:
     * Retorna el nombre normalizado o null si no es válido.
     */
    private function normalizarDia(string $dia): ?string
    {
        // Definir los días permitidos.
        $dias = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'miércoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado',
            'sábado' => 'Sábado',
            'domingo' => 'Domingo'
        ];

        // Normalizar el texto recibido.
        $diaNormalizado = mb_strtolower(trim($dia), 'UTF-8');

        return $dias[$diaNormalizado] ?? null;
    }

    /**
     * Descripción: Obtiene el número correspondiente a cada día de la semana.
     *
     * Entradas:
     * $dia: Nombre normalizado del día.
     *
     * Salidas:
     * Retorna el número del día utilizado en disponibilidadProfesor.
     */
    private function obtenerNumeroDia(string $dia): int
    {
        // Relacionar los nombres de los días con sus números.
        $dias = [
            'Lunes' => 1,
            'Martes' => 2,
            'Miércoles' => 3,
            'Jueves' => 4,
            'Viernes' => 5,
            'Sábado' => 6,
            'Domingo' => 7
        ];

        return $dias[$dia];
    }

    /**
     * Descripción: Obtiene las asignaturas y los docentes disponibles
     * para el horario seleccionado.
     *
     * Entradas: Recibe el identificador del horario.
     *
     * Salidas: Retorna las asignaturas, sus horas y los docentes
     * que tienen registrada su preferencia para el periodo.
     */
    /*
    public function obtenerAsignaturas($idHorario)
    {
        // Verificar que el usuario tenga permisos para consultar horarios.
        $this->verificarRol([1, 2]);

        // Buscar el horario seleccionado.
        $horario = Horario::with(['grupo', 'periodo'])
            ->where('estado', 1)
            ->findOrFail($idHorario);

        // Obtener el grupo relacionado con el horario.
        $grupo = $horario->grupo;

        
        // Consultar las disponibilidades confirmadas de los docentes
        // correspondientes al periodo del horario seleccionado.
        $disponibilidadesDocentes = DisponibilidadProfesor::where(
            'id_periodo',
            $horario->id_periodo
        )
            ->where('estadoConfirmacion', 1)
            ->get([
                'id_docente',
                'dia',
                'hora_inicio',
                'hora_fin',
                'estadoConfirmacion'
            ]);


        // =========================================
        // CONSULTAR CARGA ACADÉMICA OCUPADA
        // =========================================

        // Consultar las horas que cada docente ya tiene asignadas
        // en otros horarios activos del mismo periodo escolar.
        $cargasDocentes = DB::table('detalle_horario as detalle')

            // Relacionar los detalles con sus horarios.
            ->join(
                'horario',
                'detalle.id_horario',
                '=',
                'horario.id_horario'
            )

            // Consultar únicamente horarios del mismo periodo.
            ->where('horario.id_periodo', $horario->id_periodo)

            // Considerar únicamente horarios activos.
            ->where('horario.estado', 1)

            // Excluir el horario que se está gestionando actualmente.
            ->where('horario.id_horario', '!=', $horario->id_horario)

            // Agrupar las horas por docente.
            ->select('detalle.id_docente')

            // Sumar la duración de todos sus bloques.
            ->selectRaw('
                SUM(
                    TIME_TO_SEC(
                        TIMEDIFF(detalle.hora_fin, detalle.hora_inicio)
                    ) / 3600
                ) AS horas_asignadas
            ')

            ->groupBy('detalle.id_docente')

            ->get()

            // Organizar los resultados por identificador del docente.
            ->keyBy('id_docente');

        // Verificar que el grupo esté activo.
        if (!$grupo || (int) $grupo->estado !== 1) {
            return response()->json([
                'mensaje' => 'El grupo seleccionado no está disponible.',
                'asignaturas' => []
            ], 422);
        }

        // Consultar las asignaturas correspondientes al grupo.
        $asignaturas = Asignatura::with([
            'asignaturasPreferidas' => function ($consulta) use ($horario) {

                // Obtener las preferencias del periodo seleccionado.
                $consulta->where('id_periodo', $horario->id_periodo)

                    // Consultar únicamente docentes con usuario activo.
                    ->whereHas('docente.usuario', function ($usuario) {
                        $usuario->where('estado', 1);
                    })

                    // Obtener la información del docente y su usuario.
                    ->with('docente.usuario');
            }
        ])
            ->where('id_carrera', $grupo->id_carrera)
            ->where('cuatrimestre', $grupo->cuatrimestre)
            ->where('estado', 1)
            ->orderBy('nombreAsignatura')
            ->get([
                'id_asignatura',
                'nombreAsignatura',
                'cantidadHoras'
            ]);

        // Organizar la información para enviarla al JavaScript.
$asignaturas = $asignaturas->map(function ($asignatura) use ($cargasDocentes) {

    // Obtener los docentes que tienen preferida la asignatura.
    $docentes = $asignatura->asignaturasPreferidas
        ->pluck('docente')
        ->filter()
        ->unique('id_docente')
        ->map(function ($docente) use ($cargasDocentes) {

            // Obtener la cuenta de usuario del docente.
            $usuario = $docente->usuario;

            // Obtener las horas asignadas en otros horarios.
            $cargaRegistrada = $cargasDocentes->get(
                $docente->id_docente
            );

            $horasAsignadas = $cargaRegistrada
                ? (float) $cargaRegistrada->horas_asignadas
                : 0;

            // Obtener la carga académica máxima del docente.
            $cargaAcademica = (float) $docente->cargaAcademica;

            // Calcular las horas que todavía puede utilizar.
            $horasDisponibles = max(
                0,
                $cargaAcademica - $horasAsignadas
            );

            return [
                'id_docente' => $docente->id_docente,

                // Construir el nombre completo.
                'nombreCompleto' => $usuario
                    ? trim(
                        $usuario->nombreUsuario . ' ' .
                        $usuario->apellidoPaterno . ' ' .
                        $usuario->apellidoMaterno
                    )
                    : 'Docente no disponible',

                // Enviar información de su carga académica.
                'cargaAcademica' => $cargaAcademica,
                'horasAsignadasOtrosHorarios' => $horasAsignadas,
                'horasDisponibles' => $horasDisponibles
            ];
        })
        ->values();

    return [
        'id_asignatura' => $asignatura->id_asignatura,
        'nombreAsignatura' => $asignatura->nombreAsignatura,
        'cantidadHoras' => $asignatura->cantidadHoras,
        'docentes' => $docentes
    ];
}); 


        // Retornar las asignaturas y las disponibilidades confirmadas
        // correspondientes al periodo del horario seleccionado.
        return response()->json([
            'asignaturas' => $asignaturas,
            'disponibilidadesDocentes' => $disponibilidadesDocentes
        ]);
    }*/


/**
 * Descripción: Obtiene las asignaturas y los docentes disponibles
 * para el horario seleccionado, incluyendo la carga académica
 * que cada docente tiene ocupada en otros horarios del periodo.
 *
 * Entradas:
 * $idHorario: Identificador del horario seleccionado.
 *
 * Salidas:
 * Retorna las asignaturas, sus horas, los docentes que tienen
 * registrada su preferencia y la información de su carga académica.
 */
public function obtenerAsignaturas($idHorario)
{
    // Verificar que el usuario tenga permisos para consultar horarios.
    $this->verificarRol([1, 2]);

    // Buscar el horario seleccionado.
    $horario = Horario::with(['grupo', 'periodo'])
        ->where('estado', 1)
        ->findOrFail($idHorario);

    // Obtener el grupo relacionado con el horario.
    $grupo = $horario->grupo;

    // Consultar las disponibilidades confirmadas de los docentes
    // correspondientes al periodo del horario seleccionado.
    $disponibilidadesDocentes = DisponibilidadProfesor::where(
        'id_periodo',
        $horario->id_periodo
    )
        ->where('estadoConfirmacion', 1)
        ->get([
            'id_docente',
            'dia',
            'hora_inicio',
            'hora_fin',
            'estadoConfirmacion'
        ]);

    // =========================================
    // CONSULTAR CARGA ACADÉMICA OCUPADA
    // =========================================

    // Consultar las horas que cada docente ya tiene asignadas
    // en otros horarios activos del mismo periodo escolar.
    $cargasDocentes = DB::table('detalle_horario as detalle')

        // Relacionar los detalles con sus horarios.
        ->join(
            'horario',
            'detalle.id_horario',
            '=',
            'horario.id_horario'
        )

        // Consultar únicamente horarios del mismo periodo.
        ->where('horario.id_periodo', $horario->id_periodo)

        // Considerar únicamente horarios activos.
        ->where('horario.estado', 1)

        // Excluir el horario que se está gestionando actualmente.
        ->where('horario.id_horario', '!=', $horario->id_horario)

        // Agrupar las horas por docente.
        ->select('detalle.id_docente')

        // Sumar la duración de todos sus bloques.
        ->selectRaw('
            SUM(
                TIME_TO_SEC(
                    TIMEDIFF(detalle.hora_fin, detalle.hora_inicio)
                ) / 3600
            ) AS horas_asignadas
        ')

        ->groupBy('detalle.id_docente')

        ->get()

        // Organizar los resultados por identificador del docente.
        ->keyBy('id_docente');

    // =========================================
    // VALIDAR GRUPO
    // =========================================

    // Verificar que el grupo esté activo.
    if (!$grupo || (int) $grupo->estado !== 1) {
        return response()->json([
            'mensaje' => 'El grupo seleccionado no está disponible.',
            'asignaturas' => []
        ], 422);
    }

    // =========================================
    // CONSULTAR ASIGNATURAS
    // =========================================

    // Consultar las asignaturas correspondientes al grupo.
    $asignaturas = Asignatura::with([
        'asignaturasPreferidas' => function ($consulta) use ($horario) {

            // Obtener las preferencias del periodo seleccionado.
            $consulta->where('id_periodo', $horario->id_periodo)

                // Consultar únicamente docentes con usuario activo.
                ->whereHas('docente.usuario', function ($usuario) {
                    $usuario->where('estado', 1);
                })

                // Obtener la información del docente y su usuario.
                ->with('docente.usuario');
        }
    ])
        ->where('id_carrera', $grupo->id_carrera)
        ->where('cuatrimestre', $grupo->cuatrimestre)
        ->where('estado', 1)
        ->orderBy('nombreAsignatura')
        ->get([
            'id_asignatura',
            'nombreAsignatura',
            'cantidadHoras'
        ]);

    // =========================================
    // ORGANIZAR INFORMACIÓN PARA JAVASCRIPT
    // =========================================

    // Organizar las asignaturas y sus docentes preferidos.
    $asignaturas = $asignaturas->map(function ($asignatura) use ($cargasDocentes) {

        // Obtener los docentes que tienen preferida la asignatura.
        $docentes = $asignatura->asignaturasPreferidas
            ->pluck('docente')
            ->filter()
            ->unique('id_docente')
            ->map(function ($docente) use ($cargasDocentes) {

                // Obtener la cuenta de usuario del docente.
                $usuario = $docente->usuario;

                // Obtener las horas asignadas en otros horarios.
                $cargaRegistrada = $cargasDocentes->get(
                    $docente->id_docente
                );

                $horasAsignadas = $cargaRegistrada
                    ? (float) $cargaRegistrada->horas_asignadas
                    : 0;

                // Obtener la carga académica máxima del docente.
                $cargaAcademica = (float) $docente->cargaAcademica;

                // Calcular las horas que todavía puede utilizar.
                $horasDisponibles = max(
                    0,
                    $cargaAcademica - $horasAsignadas
                );

                return [
                    'id_docente' => $docente->id_docente,

                    // Construir el nombre completo.
                    'nombreCompleto' => $usuario
                        ? trim(
                            $usuario->nombreUsuario . ' ' .
                            $usuario->apellidoPaterno . ' ' .
                            $usuario->apellidoMaterno
                        )
                        : 'Docente no disponible',

                    // Enviar información de su carga académica.
                    'cargaAcademica' => $cargaAcademica,
                    'horasAsignadasOtrosHorarios' => $horasAsignadas,
                    'horasDisponibles' => $horasDisponibles
                ];
            })
            ->values();

        return [
            'id_asignatura' => $asignatura->id_asignatura,
            'nombreAsignatura' => $asignatura->nombreAsignatura,
            'cantidadHoras' => $asignatura->cantidadHoras,
            'docentes' => $docentes
        ];
    });

    // =========================================
    // RETORNAR INFORMACIÓN
    // =========================================

    // Retornar las asignaturas y las disponibilidades confirmadas
    // correspondientes al periodo del horario seleccionado.
    return response()->json([
        'asignaturas' => $asignaturas,
        'disponibilidadesDocentes' => $disponibilidadesDocentes
    ]);
}


    /**
 * Descripción:
 * Comprueba que las horas asignadas a cada docente, considerando
 * los demás horarios activos del mismo periodo y el horario actual,
 * no superen su carga académica.
 *
 * Entradas:
 * - $detalles: Bloques que se desean registrar.
 * - $idPeriodo: Identificador del periodo escolar.
 * - $idHorario: Identificador del horario que se está registrando.
 *
 * Salidas:
 * - No retorna información si todos los docentes cumplen su carga.
 * - Genera una excepción de validación si algún docente la supera.
 */
private function validarCargaAcademicaDocentes(
    $detalles,
    $idPeriodo,
    $idHorario
) {

    // Obtener los identificadores de los docentes del horario.
    $idsDocentes = collect($detalles)
        ->pluck('id_docente')
        ->unique()
        ->values();

    // Consultar las horas que ya tienen asignadas en otros horarios.
    $horasOtrosHorarios = DB::table('detalle_horario as detalle')
        ->join(
            'horario',
            'detalle.id_horario',
            '=',
            'horario.id_horario'
        )
        ->where('horario.id_periodo', $idPeriodo)
        ->where('horario.estado', 1)
        ->where('horario.id_horario', '!=', $idHorario)
        ->whereIn('detalle.id_docente', $idsDocentes)
        ->select('detalle.id_docente')
        ->selectRaw('
            SUM(
                TIME_TO_SEC(
                    TIMEDIFF(
                        detalle.hora_fin,
                        detalle.hora_inicio
                    )
                ) / 3600
            ) AS horas_asignadas
        ')
        ->groupBy('detalle.id_docente')
        ->pluck('horas_asignadas', 'detalle.id_docente');

    // Calcular las horas que se están registrando en este horario.
    $horasHorarioActual = [];

    foreach ($detalles as $detalle) {

        $idDocente = $detalle['id_docente'];

        // Convertir las horas a minutos para calcular su duración.
        $inicio = strtotime($detalle['hora_inicio']);
        $fin = strtotime($detalle['hora_fin']);

        $horasBloque = ($fin - $inicio) / 3600;

        // Acumular las horas de cada docente.
        if (!isset($horasHorarioActual[$idDocente])) {
            $horasHorarioActual[$idDocente] = 0;
        }

        $horasHorarioActual[$idDocente] += $horasBloque;
    }

    // Consultar la carga académica de los docentes involucrados.
    $docentes = Docente::whereIn('id_docente', $idsDocentes)
        ->get([
            'id_docente',
            'cargaAcademica'
        ])
        ->keyBy('id_docente');

    // Comprobar la carga académica de cada docente.
    foreach ($horasHorarioActual as $idDocente => $horasActuales) {

        $docente = $docentes->get($idDocente);

        // Comprobar que el docente exista.
        if (!$docente) {
            continue;
        }

        // Obtener las horas ocupadas en otros horarios.
        $horasRegistradas = (float) (
            $horasOtrosHorarios->get($idDocente, 0)
        );

        // Sumar las horas de otros horarios y del horario actual.
        $horasTotales = $horasRegistradas + $horasActuales;

        // Obtener la carga académica máxima.
        $cargaAcademica = (float) $docente->cargaAcademica;

        // Impedir que se supere la carga académica.
        if ($horasTotales > $cargaAcademica) {

            throw ValidationException::withMessages([
                'detalles' =>
                    'No se pueden registrar las asignaciones porque un docente superaría su carga académica de ' .
                    $cargaAcademica .
                    ' horas. Actualmente tiene ' .
                    $horasRegistradas .
                    ' horas asignadas en otros horarios y se intentan agregar ' .
                    $horasActuales .
                    ' horas en este horario.'
            ]);
        }
    }
}

    /**
     * Descripción: Modifica un horario académico y sus asignaciones.
     *
     * Entradas:
     * $solicitud: Contiene los nuevos datos del horario.
     * $idHorario: Identificador del horario que se modificará.
     *
     * Salidas:
     * Retorna una respuesta indicando si el horario fue actualizado.
     */
    public function update(Request $solicitud, $idHorario)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Consultar el horario activo.
        $horario = Horario::where('estado', 1)
            ->with('grupo.periodo')
            ->findOrFail($idHorario);

        // Validar los datos recibidos.
        $datosValidados = $this->validarDatosHorario($solicitud);

        // Consultar el grupo seleccionado.
        $grupoSeleccionado = Grupo::with('carrera', 'periodo')
            ->where('estado', 1)
            ->findOrFail($datosValidados['id_grupo']);

        // Comprobar que el periodo corresponda al grupo.
        if ((int) $grupoSeleccionado->id_periodo !== (int) $datosValidados['id_periodo']) {
            throw ValidationException::withMessages([
                'id_periodo' => 'El periodo seleccionado no corresponde al grupo.'
            ]);
        }

        // Comprobar que el periodo sea del año 2026 en adelante.
        if ((int) $grupoSeleccionado->periodo->anio < 2026) {
            throw ValidationException::withMessages([
                'id_periodo' => 'Solo se pueden modificar horarios de periodos del año 2026 en adelante.'
            ]);
        }

        // Comprobar que el grupo no tenga otro horario activo.
        $horarioExistente = Horario::where('id_grupo', $grupoSeleccionado->id_grupo)
            ->where('id_periodo', $datosValidados['id_periodo'])
            ->where('estado', 1)
            ->where('id_horario', '!=', $horario->id_horario)
            ->exists();

        if ($horarioExistente) {
            throw ValidationException::withMessages([
                'id_grupo' => 'Este grupo ya cuenta con otro horario activo.'
            ]);
        }

        // Validar las nuevas asignaciones.
        $detallesValidados = $this->validarAsignaciones(
            $datosValidados['detalles'],
            $grupoSeleccionado,
            $datosValidados['id_periodo'],
            $horario->id_horario
        );

        // Actualizar el horario y reemplazar sus detalles.
        DB::transaction(function () use (
            $horario,
            $grupoSeleccionado,
            $datosValidados,
            $detallesValidados
        ) {
            // Actualizar la información general del horario.
            $horario->update([
                'nombreHorario' => $datosValidados['nombreHorario']
                    ?? $horario->nombreHorario,
                'id_grupo' => $grupoSeleccionado->id_grupo,
                'id_periodo' => $datosValidados['id_periodo']
            ]);

            // Eliminar los detalles anteriores.
            $horario->detalles()->delete();

            // Guardar los nuevos bloques.
            $this->guardarDetallesHorario($horario, $detallesValidados);
        });

        // Retornar el resultado de la modificación.
        return $this->respuestaOperacion(
            $solicitud,
            'El horario se actualizó correctamente.',
            $horario->id_horario
        );
    }

    /**
     * Descripción: Realiza la eliminación lógica de un horario.
     *
     * Entradas:
     * $idHorario: Identificador del horario que se eliminará.
     *
     * Salidas:
     * Retorna una respuesta indicando si el horario fue eliminado.
     */
    public function destroy(Request $solicitud, $idHorario)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Consultar el horario activo.
        $horario = Horario::where('estado', 1)
            ->findOrFail($idHorario);

        // Cambiar el estado del horario a inactivo.
        $horario->update([
            'estado' => 0
        ]);

        // Retornar el resultado de la eliminación.
        return $this->respuestaOperacion(
            $solicitud,
            'El horario se eliminó correctamente.',
            $horario->id_horario
        );
    }

    /**
     * Descripción: Comprueba que el usuario autenticado tenga
     * uno de los roles permitidos.
     *
     * Entradas:
     * $rolesPermitidos: Identificadores de los roles autorizados.
     *
     * Salidas:
     * No devuelve datos; genera un error 403 si el usuario
     * no cuenta con los permisos necesarios.
     */
    private function verificarRol(array $rolesPermitidos): void
    {
        // Obtener el usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Comprobar que tenga un rol autorizado.
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
