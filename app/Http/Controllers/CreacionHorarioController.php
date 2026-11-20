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
            'horarios.detalles'
        ])
            ->orderBy('id_grupo', 'desc')
            ->get();

        // Consultar los horarios académicos registrados y activos
        // que todavía no tienen asignaciones.
        // Consultar los horarios activos que todavía no se finalizan.
        // Se incluyen los horarios nuevos y los que ya están guardados como borrador.
        $horariosDisponibles = Horario::with([
            'grupo.carrera',
            'periodo',
            'detalles.docente.usuario',
            'detalles.asignatura'
        ])
            ->where('estado', 1)
            ->where(function ($consulta) {
                $consulta->whereNull('borrador')
                    ->orWhere('borrador', 0);
            })
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
        $disponibilidadesDocentes = collect();

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

        // Obtener el identificador del horario solicitado para agregar asignaturas.
        $idHorarioAgregar = $solicitud->query('agregar');

        // CONSULTAR HORARIOS REGISTRADOS
        $horariosRegistrados = Horario::with([
            'grupo.carrera',
            'periodo',
            'detalles.docente.usuario',
            'detalles.asignatura'
        ])
        ->orderBy('id_horario', 'desc')
        ->get();


        return view('Horario.CreaciónHorario.GestionCreaciónHorario', compact(
            'gruposRegistrados',
            'horariosDisponibles',
            'periodosDisponibles',
            'carrerasDisponibles',
            'horarioConsultado',
            'asignaturasDisponibles',
            'docentesDisponibles',
            'disponibilidadesDocentes',
            'horariosRegistrados',
            'idHorarioAgregar'
        ));

    }


    /**
     * Descripción: Registra las asignaciones académicas en un horario existente.
     * Permite guardar el horario como borrador o definitivamente.
     *
     * Entradas:
     * $solicitud: Contiene el identificador del horario, sus bloques asignados
     * y el tipo de guardado solicitado.
     *
     * Salidas:
     * Retorna una respuesta indicando si las asignaciones fueron registradas.
     */
    public function store(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

       // Obtener el tipo de guardado solicitado.
        $borrador = $solicitud->input('borrador', 1);

        // Agregar el valor predeterminado a la solicitud.
        $solicitud->merge([
            'borrador' => $borrador
        ]);

        // Validar que el tipo de guardado sea permitido.
        $solicitud->validate([
            'borrador' => [
                'required',
                'integer',
                'in:0,1'
            ]
        ]);

        // Convertir el tipo de guardado a entero.
        $borrador = (int) $borrador;

        // Validar los datos recibidos.
        // Si es borrador, se permite que no tenga asignaciones.
        $datosValidados = $this->validarDatosHorario(
            $solicitud,
            $borrador === 0
        );

        // Obtener los detalles recibidos.
        // Si no se enviaron asignaciones, se utiliza un arreglo vacío.
        $detallesRecibidos = $datosValidados['detalles'] ?? [];

        // Consultar el horario seleccionado.
        $horarioSeleccionado = Horario::with([ 'grupo', 'periodo' ])->where('estado', 1)->findOrFail($datosValidados['id_horario']);

        // Comprobar que el horario todavía no se encuentre finalizado.
        if ((int) $horarioSeleccionado->borrador === 1) {
            throw ValidationException::withMessages([
                'id_horario' => 'Este horario ya está finalizado y no se puede modificar desde este formulario.'
            ]);
        }

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

        // Validar las asignaciones antes de guardar.
        // Si se trata de un borrador vacío, se validará un arreglo sin elementos.
        $detallesValidados = $this->validarAsignaciones(
            $detallesRecibidos,
            $grupoSeleccionado,
            $idPeriodo,
            $horarioSeleccionado->id_horario
        );

        // VALIDAR LA CARGA ACADÉMICA DE LOS DOCENTES
        // Comprobar que ningún docente supere su carga académica.
        $this->validarCargaAcademicaDocentes(
            $detallesValidados,
            $idPeriodo,
            $horarioSeleccionado->id_horario
        );

        // =========================================
        // GUARDAR LAS ASIGNACIONES Y EL TIPO DE HORARIO
        // =========================================

        // Registrar los detalles y actualizar el tipo de guardado.
        DB::transaction(function () use ( $horarioSeleccionado, $detallesValidados, $borrador ) {
            // Eliminar los detalles anteriores para evitar duplicados.
            $horarioSeleccionado->detalles()->delete();

            // Registrar los bloques asignados al horario existente.
            $this->guardarDetallesHorario(
                $horarioSeleccionado,
                $detallesValidados
            );

            // Actualizar el indicador de borrador.
            $horarioSeleccionado->borrador = $borrador;
            $horarioSeleccionado->save();
        });

        // Retornar a la pantalla anterior con el mensaje correspondiente.

        return redirect()->back()->with(
            'alerta_exito',
            $borrador === 0
                ? 'El horario se guardó como borrador correctamente.'
                : 'Las asignaciones del horario se registraron correctamente.'
        );
    }

    /**
     * Descripción: Valida el identificador del horario y sus bloques asignados.
     * Permite omitir los detalles cuando se solicita guardar un borrador.
     *
     * Entradas:
     * $solicitud: Datos enviados desde el formulario.
     * $permitirDetallesVacios: Indica si se permite guardar sin asignaciones.
     *
     * Salidas:
     * Retorna los datos validados.
     */
    private function validarDatosHorario(
        Request $solicitud,
        bool $permitirDetallesVacios = false
    ): array {
        return $solicitud->validate([
            'id_horario' => [
                'required',
                'integer',
                'exists:horario,id_horario'
            ],

            // Permitir detalles vacíos únicamente cuando se guarda un borrador.
            'detalles' => $permitirDetallesVacios
                ? ['sometimes', 'array']
                : ['required', 'array', 'min:1'],

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
                        ->where('borrador', 1)
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

        // CONSULTAR CARGA ACADÉMICA OCUPADA

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

            // Considerar únicamente horarios finalizados.
            ->where('horario.borrador', 1)

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

        // VALIDAR GRUPO

        // Verificar que el grupo esté activo.
        if (!$grupo || (int) $grupo->estado !== 1) {
            return response()->json([
                'mensaje' => 'El grupo seleccionado no está disponible.',
                'asignaturas' => []
            ], 422);
        }

        // CONSULTAR CLASES EN OTROS HORARIOS

        // Obtener los bloques que los docentes tienen registrados
        // en otros horarios activos del mismo periodo escolar.
        $horariosDocentesActivos = DB::table('detalle_horario as detalle')

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

            ->where('horario.borrador', 1)

            // Excluir el horario que se está gestionando.
            ->where('horario.id_horario', '!=', $horario->id_horario)

            // Obtener los datos necesarios para comprobar empalmes.
            ->get([
                'detalle.id_docente',
                'detalle.dia',
                'detalle.hora_inicio',
                'detalle.hora_fin'
            ]);

        // =========================================
        // CONSULTAR LOS DETALLES DEL HORARIO ACTUAL
        // =========================================

        // Obtener los bloques que ya están guardados en el horario seleccionado.
        $detallesHorario = $horario->detalles()
            ->with([
                'asignatura',
                'docente.usuario'
            ])
            ->get()
            ->map(function ($detalle) {

                // Obtener el nombre completo del docente.
                $usuario = $detalle->docente->usuario ?? null;

                $nombreDocente = $usuario
                    ? trim(
                        $usuario->nombreUsuario . ' ' .
                        $usuario->apellidoPaterno . ' ' .
                        $usuario->apellidoMaterno
                    )
                    : 'Docente no disponible';

                return [
                    'id_asignatura' => $detalle->id_asignatura,
                    'id_docente' => $detalle->id_docente,
                    'dia' => $detalle->dia,
                    'hora_inicio' => $detalle->hora_inicio,
                    'hora_fin' => $detalle->hora_fin,
                    'aula' => $detalle->aula,
                    'nombreAsignatura' => $detalle->asignatura->nombreAsignatura ?? 'Asignatura no disponible',
                    'nombreDocente' => $nombreDocente
                ];
            })
            ->values();

        // CONSULTAR ASIGNATURAS

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

        
        // ORGANIZAR INFORMACIÓN PARA JAVASCRIPT

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

        
        
        // RETORNAR INFORMACIÓN

        // Retornar las asignaturas, disponibilidades, clases
        // registradas en otros horarios y detalles del horario actual.
        return response()->json([
            'asignaturas' => $asignaturas,
            'disponibilidadesDocentes' => $disponibilidadesDocentes,
            'horariosDocentesActivos' => $horariosDocentesActivos,
            'detallesHorario' => $detallesHorario
        ]);
    }


    /**
     * Descripción: Comprueba que las horas asignadas a cada docente,
     * considerando los demás horarios activos del mismo periodo y
     * las nuevas asignaciones del horario actual, no superen su carga académica.
     *
     * Entradas:
     * $detalles: Bloques que se desean registrar.
     * $idPeriodo: Identificador del periodo escolar.
     * $idHorario: Identificador del horario que se está registrando o modificando.
     *
     * Salidas:
     * No devuelve datos si todos los docentes cumplen su carga académica.
     * Genera una excepción de validación si algún docente supera su límite.
     */
    private function validarCargaAcademicaDocentes(
        array $detalles,
        int $idPeriodo,
        int $idHorario
    ): void {

        // Obtener los identificadores únicos de los docentes involucrados.
        $idsDocentes = collect($detalles)
            ->pluck('id_docente')
            ->unique()
            ->values();

        // Consultar las horas que los docentes tienen asignadas
        // en otros horarios activos del mismo periodo.
        $horasOtrosHorarios = DB::table('detalle_horario as detalle')
            ->join(
                'horario',
                'detalle.id_horario',
                '=',
                'horario.id_horario'
            )
            ->where('horario.id_periodo', $idPeriodo)
            ->where('horario.estado', 1)
            ->where('horario.borrador', 1)
            ->where('horario.id_horario', '!=', $idHorario)
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

        // Calcular las horas que se están asignando en el horario actual.
        $horasHorarioActual = [];

        foreach ($detalles as $detalle) {

            $idDocente = (int) $detalle['id_docente'];

            // Calcular la duración del bloque en horas.
            $inicio = strtotime($detalle['hora_inicio']);
            $fin = strtotime($detalle['hora_fin']);

            $horasBloque = ($fin - $inicio) / 3600;

            // Acumular las horas de cada docente.
            if (!isset($horasHorarioActual[$idDocente])) {
                $horasHorarioActual[$idDocente] = 0;
            }

            $horasHorarioActual[$idDocente] += $horasBloque;
        }

        // Consultar la carga académica máxima de los docentes.
        $docentes = Docente::whereIn('id_docente', $idsDocentes)
            ->get([
                'id_docente',
                'cargaAcademica'
            ])
            ->keyBy('id_docente');

        // Comprobar que ningún docente supere su carga académica.
        foreach ($horasHorarioActual as $idDocente => $horasActuales) {

            $docente = $docentes->get($idDocente);

            // Obtener las horas asignadas en otros horarios.
            $horasRegistradas = (float) (
                $horasOtrosHorarios->get($idDocente, 0)
            );

            // Sumar las horas de otros horarios y del horario actual.
            $horasTotales = $horasRegistradas + $horasActuales;

            // Obtener la carga académica máxima del docente.
            $cargaAcademica = (float) $docente->cargaAcademica;

            // Impedir que se supere la carga académica.
            if ($horasTotales > $cargaAcademica) {

                throw ValidationException::withMessages([
                    'detalles' =>
                        'No se pueden guardar las asignaciones porque el docente supera su carga académica máxima de ' .
                        $cargaAcademica .
                        ' horas. Tiene ' .
                        $horasRegistradas .
                        ' horas asignadas en otros horarios y se intentan agregar ' .
                        $horasActuales .
                        ' horas en este horario, dando un total de ' .
                        $horasTotales .
                        ' horas.'
                ]);
            }
        }
    }

    /**
     * Descripción: Muestra el formulario para editar las asignaciones
     * de un horario académico finalizado.
     *
     * Entradas:
     * $idHorario: Identificador del horario que se desea editar.
     *
     * Salidas:
     * Retorna la vista de edición con los datos generales del horario.
     */
    public function edit($idHorario) 
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Consultar únicamente horarios activos y finalizados.
        $horario = Horario::with([
            'grupo.carrera',
            'periodo',
            'detalles.asignatura',
            'detalles.docente.usuario'
            ])->where('estado', 1)->where('borrador', 1)->findOrFail($idHorario);

        // Comprobar que el grupo correspondiente esté activo.
        if (!$horario->grupo || (int) $horario->grupo->estado !== 1) {
            abort(404, 'El grupo correspondiente al horario no está disponible.');
        }

        // Comprobar que el periodo corresponda al año permitido.
        if (!$horario->periodo || (int) $horario->periodo->anio < 2026) {
            abort(404, 'El periodo del horario no está disponible para edición.');
        }

        return view( 'Horario.CreaciónHorario.EditarCreaciónHorario', compact('horario') );
    }

    /**
     * Descripción: Actualiza las asignaciones de un horario académico finalizado.
     *
     * Entradas:
     * $solicitud: Contiene los bloques modificados desde el calendario.
     * $idHorario: Identificador del horario que se desea actualizar.
     *
     * Salidas:
     * Retorna una respuesta indicando si el horario fue actualizado.
     */
    public function update(Request $solicitud, $idHorario)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Consultar únicamente horarios activos y finalizados.
        $horario = Horario::with([ 'grupo', 'periodo' ])->where('estado', 1)->where('borrador', 1)->findOrFail($idHorario);

        // Validar los bloques recibidos desde el calendario.
        $datosValidados = $solicitud->validate([

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

        // Obtener el grupo correspondiente al horario.
        $grupoSeleccionado = $horario->grupo;

        // Comprobar que el grupo se encuentre activo.
        if (!$grupoSeleccionado || (int) $grupoSeleccionado->estado !== 1) {
            throw ValidationException::withMessages([
                'detalles' => 'El grupo correspondiente al horario se encuentra inactivo.'
            ]);
        }

        // Obtener el periodo correspondiente al horario.
        $idPeriodo = (int) $horario->id_periodo;

        // Comprobar que el periodo sea del año 2026 en adelante.
        if (!$horario->periodo || (int) $horario->periodo->anio < 2026) {
            throw ValidationException::withMessages([
                'detalles' => 'Solo se pueden modificar horarios de periodos del año 2026 en adelante.'
            ]);
        }

        // Validar las asignaciones con las reglas existentes.
        $detallesValidados = $this->validarAsignaciones( $datosValidados['detalles'], $grupoSeleccionado, $idPeriodo, $horario->id_horario );

        // Comprobar que los docentes no superen su carga académica.
        $this->validarCargaAcademicaDocentes( $detallesValidados, $idPeriodo, $horario->id_horario );

        // Reemplazar únicamente los detalles del horario seleccionado.
        DB::transaction(function () use ( $horario, $detallesValidados ) {

            // Eliminar los bloques anteriores de este horario.
            $horario->detalles()->delete();

            // Registrar los nuevos bloques.
            $this->guardarDetallesHorario( $horario, $detallesValidados );

            // Mantener el horario como finalizado.
            $horario->borrador = 1;
            $horario->save();
        });

        // Regresar al listado con el mensaje de confirmación.
        return redirect()->route('creacion-horario.index')->with('alerta_exito', 'El horario se actualizó correctamente.');
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
            return redirect()->route('creacion-horario.index')->with('alerta_error', 'El horario seleccionado ya se encuentra desactivado.');
        }

        // Cambia el estado.
        $horarioEncontrado->update([ 'estado' => 0, ]);

        // Regresa al listado y muestra el modal de confirmación.
        return redirect()->route('creacion-horario.index')->with('alerta_exito', 'Los datos generales del horario se desactivó correctamente.');
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
