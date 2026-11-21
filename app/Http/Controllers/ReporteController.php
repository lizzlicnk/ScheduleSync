<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Descripción: Muestra la pantalla principal de reportes disponibles
     * para consultar la información de la planeación académica.
     *
     * Entradas:
     * $solicitud: solicitud HTTP del usuario autenticado.
     *
     * Salidas:
     * Vista principal de reportes con los cuatro tipos disponibles
     * y los periodos escolares registrados.
     */
    public function index(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Consultar los periodos registrados en la base de datos.
        $periodos = DB::table('periodo')
            ->orderByDesc('anio')
            ->orderBy('id_periodo')
            ->get();

        // Mostrar la pantalla principal de reportes.
        return view('Reporte.GestionReportes', compact('periodos'));
    }

    //REPORTE 3

    /**
     * Descripción: Muestra el reporte de disponibilidad docente,
     * identificando a los docentes que entregaron su disponibilidad
     * y aquellos que todavía tienen pendiente este proceso.
     *
     * Entradas:
     * $solicitud: solicitud HTTP con el filtro de periodo escolar.
     *
     * Salidas:
     * Vista individual con indicadores, tabla de docentes y gráfica
     * de pastel con las disponibilidades registradas y pendientes.
     */
    public function disponibilidadDocente(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Validar el periodo escolar recibido.
        $datosValidados = $solicitud->validate([
            'id_periodo' => ['required', 'integer', 'exists:periodo,id_periodo'],
        ]);

        // Obtener el periodo seleccionado.
        $idPeriodo = $datosValidados['id_periodo'];

        // Consultar los periodos registrados en la base de datos.
        $periodos = DB::table('periodo')
            ->orderByDesc('anio')
            ->orderBy('id_periodo')
            ->get();

        // Obtener los datos del periodo seleccionado.
        $periodoSeleccionado = !empty($idPeriodo)
            ? $periodos->firstWhere('id_periodo', $idPeriodo)
            : null;

        // ==========================================
        // 1. CONSULTAR LAS DISPONIBILIDADES ENTREGADAS
        // ==========================================

        // Consultar las disponibilidades que fueron confirmadas
        // por los docentes, agrupándolas para evitar duplicados.
        $consultaDisponibilidad = DB::table('disponibilidadProfesor as dp')
            ->select(
                'dp.id_docente',
                DB::raw('COUNT(*) as totalRegistrosConfirmados'),
                DB::raw('MAX(dp.fechaConfirmacion) as fechaRegistro')
            )
            ->where('dp.estadoConfirmacion', 1);

        // Aplicar el filtro de periodo.
        if (!empty($idPeriodo)) {
            $consultaDisponibilidad->where('dp.id_periodo', $idPeriodo);
        }

        // Agrupar los registros por docente.
        $consultaDisponibilidad->groupBy('dp.id_docente');

        // ==========================================
        // 2. CONSULTAR LOS DOCENTES
        // ==========================================

        // Consultar los docentes activos y relacionarlos con sus
        // disponibilidades entregadas, si existen.
        $consultaDocentes = DB::table('docente as d')
            ->join('usuario as u', 'd.id_usuario', '=', 'u.id_usuario')
            ->leftJoinSub(
                $consultaDisponibilidad,
                'disponibilidad',
                function ($join) {
                    $join->on(
                        'd.id_docente',
                        '=',
                        'disponibilidad.id_docente'
                    );
                }
            )
            ->where('u.rol', 3)
            ->where('u.estado', 1)
            ->whereNull('u.deleted_at');

        // Obtener la información de cada docente.
        $docentes = $consultaDocentes
            ->select(
                'd.id_docente',
                DB::raw("
                    CONCAT_WS(
                        ' ',
                        u.nombreUsuario,
                        u.apellidoPaterno,
                        u.apellidoMaterno
                    ) as nombreDocente
                "),
                'd.tipoDocente',
                'disponibilidad.totalRegistrosConfirmados',
                'disponibilidad.fechaRegistro'
            )
            ->orderBy('u.apellidoPaterno')
            ->orderBy('u.apellidoMaterno')
            ->orderBy('u.nombreUsuario')
            ->get();

        // ==========================================
        // 3. IDENTIFICAR EL ESTADO DE CADA DOCENTE
        // ==========================================

        // Determinar si cada docente entregó su disponibilidad.
        $datosReporte = $docentes->map(function ($docente) {

            // Comprobar si tiene al menos un registro confirmado.
            $registrada = (int) ($docente->totalRegistrosConfirmados ?? 0) > 0;

            // Obtener el tipo de docente.
            $tipoDocente = match ((int) $docente->tipoDocente) {
                1 => 'PTC',
                2 => 'PA',
                default => 'No especificado',
            };

            // Agregar el estado de disponibilidad.
            return (object) [
                'id_docente' => $docente->id_docente,
                'nombreDocente' => $docente->nombreDocente,
                'tipoDocente' => $tipoDocente,
                'estadoDisponibilidad' => $registrada
                    ? 'Registrada'
                    : 'Pendiente',
                'fechaRegistro' => $registrada
                    ? $docente->fechaRegistro
                    : null,
            ];
        });

        // ==========================================
        // 4. CALCULAR LOS INDICADORES GENERALES
        // ==========================================

        // Contabilizar los docentes que registraron su disponibilidad.
        $totalRegistrados = $datosReporte
            ->where('estadoDisponibilidad', 'Registrada')
            ->count();

        // Contabilizar los docentes que todavía tienen pendiente
        // entregar su disponibilidad.
        $totalPendientes = $datosReporte
            ->where('estadoDisponibilidad', 'Pendiente')
            ->count();

        // Obtener el total de docentes consultados.
        $totalDocentes = $datosReporte->count();

        // Calcular el porcentaje de disponibilidades registradas.
        $porcentajeRegistrados = $totalDocentes > 0
            ? ($totalRegistrados / $totalDocentes) * 100
            : 0;

        // ==========================================
        // 5. PREPARAR LOS DATOS DE LA GRÁFICA
        // ==========================================

        // Preparar las cantidades que utilizará la gráfica de pastel.
        $datosGrafica = [
            'etiquetas' => [
                'Registrada',
                'Pendiente',
            ],
            'valores' => [
                $totalRegistrados,
                $totalPendientes,
            ],
        ];

        // Enviar la información a la vista.
        return view('Reporte.DisponibilidadDocente', compact(
            'periodos',
            'idPeriodo',
            'periodoSeleccionado',
            'datosReporte',
            'totalDocentes',
            'totalRegistrados',
            'totalPendientes',
            'porcentajeRegistrados',
            'datosGrafica'
        ));
    }

    /**
     * Descripción: Genera y descarga un archivo PDF con la captura visual
     * del reporte de disponibilidad académica docente.
     *
     * Entradas:
     * $solicitud: solicitud HTTP que contiene la imagen PNG del reporte.
     *
     * Salidas:
     * Archivo PDF generado con FPDF.
     */
    public function descargarDisponibilidadDocente(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Validar que se haya enviado una imagen.
        $datosValidados = $solicitud->validate([
            'imagen' => [
                'required',
                'string',
                'max:30000000',
                'regex:/^data:image\/png;base64,/',
            ],
        ]);

        // Obtener el contenido Base64 de la imagen.
        $imagenBase64 = substr(
            $datosValidados['imagen'],
            strlen('data:image/png;base64,')
        );

        // Decodificar la imagen.
        $contenidoImagen = base64_decode($imagenBase64, true);

        if ($contenidoImagen === false) {
            return response()->json([
                'mensaje' => 'No fue posible procesar la imagen del reporte.'
            ], 422);
        }

        // Comprobar que el contenido recibido sea una imagen PNG válida.
        $informacionImagen = getimagesizefromstring($contenidoImagen);

        if (
            $informacionImagen === false ||
            $informacionImagen['mime'] !== 'image/png'
        ) {
            return response()->json([
                'mensaje' => 'El archivo enviado no es una imagen PNG válida.'
            ], 422);
        }

        // Crear una imagen a partir del contenido recibido.
        $imagenOriginal = imagecreatefromstring($contenidoImagen);

        if ($imagenOriginal === false) {
            return response()->json([
                'mensaje' => 'No fue posible generar la imagen del reporte.'
            ], 422);
        }

        // Obtener las dimensiones de la imagen.
        $anchoImagen = imagesx($imagenOriginal);
        $altoImagen = imagesy($imagenOriginal);

        // Configurar las dimensiones útiles de una página A4.
        $anchoPagina = 190;
        $altoPagina = 277;

        // Calcular la cantidad de píxeles que corresponden a una página.
        $altoFragmento = (int) floor(
            $anchoImagen * ($altoPagina / $anchoPagina)
        );

        // Evitar que la altura del fragmento sea cero.
        if ($altoFragmento < 1) {
            imagedestroy($imagenOriginal);

            return response()->json([
                'mensaje' => 'Las dimensiones de la imagen no son válidas.'
            ], 422);
        }

        // Inicializar FPDF.
        $pdf = new \FPDF('P', 'mm', 'A4');

        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(false);

        try {

            // Crear las páginas necesarias para mostrar todo el reporte.
            for (
                $posicionY = 0;
                $posicionY < $altoImagen;
                $posicionY += $altoFragmento
            ) {

                // Calcular la altura del fragmento actual.
                $altoActual = min(
                    $altoFragmento,
                    $altoImagen - $posicionY
                );

                // Crear una imagen temporal para esta página.
                $imagenPagina = imagecreatetruecolor(
                    $anchoImagen,
                    $altoActual
                );

                // Establecer un fondo blanco.
                $fondoBlanco = imagecolorallocate(
                    $imagenPagina,
                    255,
                    255,
                    255
                );

                imagefill($imagenPagina, 0, 0, $fondoBlanco);

                // Copiar la sección correspondiente del reporte.
                imagecopy(
                    $imagenPagina,
                    $imagenOriginal,
                    0,
                    0,
                    0,
                    $posicionY,
                    $anchoImagen,
                    $altoActual
                );

                // Preparar el archivo temporal del fragmento.
                $rutaImagenPagina = tempnam(
                    sys_get_temp_dir(),
                    'disponibilidad_'
                );

                try {

                    // Guardar el fragmento como imagen PNG.
                    imagepng($imagenPagina, $rutaImagenPagina);

                    // Agregar una página al documento PDF.
                    $pdf->AddPage();

                    // Calcular la altura proporcional de la imagen.
                    $altoPDF = $altoActual * ($anchoPagina / $anchoImagen);

                    // Colocar la imagen en la página.
                    $pdf->Image(
                        $rutaImagenPagina,
                        10,
                        10,
                        $anchoPagina,
                        $altoPDF,
                        'PNG'
                    );

                } finally {

                    // Liberar los recursos de la página.
                    imagedestroy($imagenPagina);

                    if (file_exists($rutaImagenPagina)) {
                        unlink($rutaImagenPagina);
                    }
                }
            }

            // Obtener el contenido final del PDF.
            $contenidoPDF = $pdf->Output('S');

        } finally {

            // Liberar la imagen original.
            imagedestroy($imagenOriginal);
        }

        // Devolver el PDF para su descarga.
        return response($contenidoPDF, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reporte-disponibilidad-docente.pdf"',
        ]);
    }

    //REPORTE 4
    /**
     * Descripción: Muestra el reporte de materias con docente asignado
     * por carrera y cuatrimestre, verificando que cada materia esté
     * presente en todos los grupos correspondientes.
     *
     * Entradas:
     * $solicitud: solicitud HTTP con los filtros del reporte.
     *
     * Salidas:
     * Vista individual con indicadores, tabla, gráfica y materias
     * pendientes de asignación.
     */
    public function materiasAsignadasCuatrimestre(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Validar los filtros recibidos.
        $datosValidados = $solicitud->validate([
            'carrera' => ['nullable', 'string', 'max:20'],
            'cuatrimestre' => ['nullable', 'integer', 'between:1,10'],
            'periodo' => ['nullable', 'in:Invierno,Primavera,Otoño'],
            'anio' => ['nullable', 'integer', 'between:2026,2100'],
        ]);

        // Obtener los filtros seleccionados.
        $carrera = $datosValidados['carrera'] ?? null;
        $cuatrimestre = $datosValidados['cuatrimestre'] ?? null;
        $periodo = $datosValidados['periodo'] ?? null;
        $anio = $datosValidados['anio'] ?? null;

        // Consultar las carreras activas.
        $carreras = \App\Models\Carrera::where('estado', 1)
            ->orderBy('nombreCarrera')
            ->get();

        // Consultar los periodos registrados.
        $periodos = \App\Models\Periodo::orderBy('anio', 'desc')
            ->orderBy('estacion')
            ->get();

        // ==========================================
        // 1. CONSULTAR LAS MATERIAS ACTIVAS
        // ==========================================

        $consultaMaterias = DB::table('asignatura as a')
            ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->where('a.estado', 1)
            ->where('c.estado', 1);

        // Aplicar el filtro de carrera.
        if (!empty($carrera)) {
            $consultaMaterias->where('c.claveCarrera', $carrera);
        }

        // Aplicar el filtro de cuatrimestre.
        if (!empty($cuatrimestre)) {
            $consultaMaterias->where('a.cuatrimestre', $cuatrimestre);
        }

        // Obtener las materias del catálogo.
        $materiasCatalogo = $consultaMaterias
            ->select(
                'a.id_asignatura',
                'a.nombreAsignatura',
                'a.clave',
                'a.cuatrimestre',
                'c.id_carrera',
                'c.nombreCarrera',
                'c.claveCarrera'
            )
            ->orderBy('c.nombreCarrera')
            ->orderBy('a.cuatrimestre')
            ->orderBy('a.nombreAsignatura')
            ->get();

        // ==========================================
        // 2. CONSULTAR LOS GRUPOS ACTIVOS
        // QUE YA TIENEN DETALLES DE HORARIO
        // ==========================================

        $consultaGrupos = DB::table('grupo as g')
            ->join('carrera as c', 'g.id_carrera', '=', 'c.id_carrera')
            ->join('periodo as p', 'g.id_periodo', '=', 'p.id_periodo')
            ->where('g.estado', 1)
            ->where('c.estado', 1)
            ->whereExists(function ($consulta) {

                $consulta->select(DB::raw(1))
                    ->from('horario as h')
                    ->join('detalle_horario as d', 'h.id_horario', '=', 'd.id_horario')
                    ->whereColumn('h.id_grupo', 'g.id_grupo');

            });

        // Aplicar el filtro de carrera.
        if (!empty($carrera)) {
            $consultaGrupos->where('c.claveCarrera', $carrera);
        }

        // Aplicar el filtro de cuatrimestre.
        if (!empty($cuatrimestre)) {
            $consultaGrupos->where('g.cuatrimestre', $cuatrimestre);
        }

        // Aplicar el filtro de periodo.
        if (!empty($periodo)) {
            $consultaGrupos->where('p.estacion', $periodo);
        }

        // Aplicar el filtro de año.
        if (!empty($anio)) {
            $consultaGrupos->where('p.anio', $anio);
        }

        // Obtener los grupos que ya tienen detalles de horario.
        $grupos = $consultaGrupos
            ->select(
                'g.id_grupo',
                'g.nombreGrupo',
                'g.id_carrera',
                'g.cuatrimestre'
            )
            ->orderBy('g.nombreGrupo')
            ->get();

        // ==========================================
        // 3. CONSULTAR LAS MATERIAS PRESENTES
        // EN LOS HORARIOS DE CADA GRUPO
        // ==========================================

        $consultaPresencias = DB::table('detalle_horario as d')
            ->join('horario as h', 'd.id_horario', '=', 'h.id_horario')
            ->join('grupo as g', 'h.id_grupo', '=', 'g.id_grupo')
            ->join('periodo as p', 'g.id_periodo', '=', 'p.id_periodo')
            ->join('asignatura as a', 'd.id_asignatura', '=', 'a.id_asignatura')
            ->where('g.estado', 1)
            ->where('a.estado', 1)
            ->whereColumn('h.id_periodo', 'g.id_periodo')
            ->whereColumn('a.id_carrera', 'g.id_carrera')
            ->whereColumn('a.cuatrimestre', 'g.cuatrimestre');

        // Aplicar el filtro de carrera.
        if (!empty($carrera)) {
            $consultaPresencias->where('a.id_carrera', function ($consulta) use ($carrera) {
                $consulta->select('id_carrera')
                    ->from('carrera')
                    ->where('claveCarrera', $carrera);
            });
        }

        // Aplicar el filtro de cuatrimestre.
        if (!empty($cuatrimestre)) {
            $consultaPresencias->where('g.cuatrimestre', $cuatrimestre);
        }

        // Aplicar el filtro de periodo.
        if (!empty($periodo)) {
            $consultaPresencias->where('p.estacion', $periodo);
        }

        // Aplicar el filtro de año.
        if (!empty($anio)) {
            $consultaPresencias->where('p.anio', $anio);
        }

        // Obtener las materias registradas en cada grupo.
        // No se filtra por borrador: también se consideran los horarios
        // que todavía no han sido finalizados.
        $presencias = $consultaPresencias
            ->select(
                'd.id_asignatura',
                'g.id_grupo'
            )
            ->distinct()
            ->get();

        // Preparar una estructura para comprobar rápidamente
        // si una materia está presente en determinado grupo.
        $materiasPorGrupo = [];

        foreach ($presencias as $presencia) {
            $clavePresencia = $presencia->id_asignatura
                . '-'
                . $presencia->id_grupo;

            $materiasPorGrupo[$clavePresencia] = true;
        }

        // ==========================================
        // 4. COMPARAR LAS MATERIAS CON TODOS
        // LOS GRUPOS DE SU CARRERA Y CUATRIMESTRE
        // ==========================================

        // Agrupar las materias por carrera y cuatrimestre.
        $materiasAgrupadas = $materiasCatalogo->groupBy(function ($materia) {
            return $materia->id_carrera . '-' . $materia->cuatrimestre;
        });

        // Preparar los resultados del reporte.
        $datosReporte = collect();

        foreach ($materiasAgrupadas as $materias) {

            // Obtener los datos de la carrera y cuatrimestre.
            $primeraMateria = $materias->first();

            $idCarrera = $primeraMateria->id_carrera;
            $cuatrimestreActual = $primeraMateria->cuatrimestre;

            // Obtener todos los grupos activos correspondientes.
            $gruposCorrespondientes = $grupos
                ->where('id_carrera', $idCarrera)
                ->where('cuatrimestre', $cuatrimestreActual)
                ->values();

            // Contabilizar las materias.
            $totalMaterias = $materias->count();
            $totalAsignadas = 0;
            $totalPendientes = 0;

            // Guardar los detalles de las materias pendientes.
            $detallePendientes = [];

            foreach ($materias as $materia) {

                // Identificar los grupos donde falta la materia.
                $gruposFaltantes = $gruposCorrespondientes
                    ->filter(function ($grupo) use ($materia, $materiasPorGrupo) {

                        $clavePresencia = $materia->id_asignatura
                            . '-'
                            . $grupo->id_grupo;

                        return !isset($materiasPorGrupo[$clavePresencia]);
                    })
                    ->values();

                // Comprobar si existen grupos activos.
                $hayGrupos = $gruposCorrespondientes->isNotEmpty();

                // La materia está asignada solamente si aparece
                // en todos los grupos activos correspondientes.
                if ($hayGrupos && $gruposFaltantes->isEmpty()) {

                    $totalAsignadas++;

                } else {

                    $totalPendientes++;

                    // Guardar el nombre y los grupos donde falta.
                    $detallePendientes[] = [
                        'nombreAsignatura' => $materia->nombreAsignatura,
                        'clave' => $materia->clave,
                        'cantidadGruposFaltantes' => $gruposFaltantes->count(),
                        'gruposFaltantes' => $gruposFaltantes
                            ->pluck('nombreGrupo')
                            ->values()
                            ->all(),
                        'sinGrupos' => !$hayGrupos,
                    ];
                }
            }

            // Calcular el porcentaje de asignación.
            $porcentaje = $totalMaterias > 0
                ? ($totalAsignadas / $totalMaterias) * 100
                : 0;

            // Agregar los resultados de la carrera y cuatrimestre.
            $datosReporte->push((object) [
                'id_carrera' => $idCarrera,
                'nombreCarrera' => $primeraMateria->nombreCarrera,
                'claveCarrera' => $primeraMateria->claveCarrera,
                'cuatrimestre' => $cuatrimestreActual,
                'totalMaterias' => $totalMaterias,
                'materiasAsignadas' => $totalAsignadas,
                'materiasPendientes' => $totalPendientes,
                'porcentajeAsignacion' => $porcentaje,
                'totalGrupos' => $gruposCorrespondientes->count(),
                'detallePendientes' => $detallePendientes,
            ]);
        }

        // ==========================================
        // 5. CALCULAR LOS INDICADORES GENERALES
        // ==========================================

        $totalMaterias = $datosReporte->sum('totalMaterias');

        $totalAsignadas = $datosReporte->sum('materiasAsignadas');

        $totalPendientes = $datosReporte->sum('materiasPendientes');

        // Calcular el porcentaje general de asignación.
        $porcentajeAsignacion = $totalMaterias > 0
            ? ($totalAsignadas / $totalMaterias) * 100
            : 0;

        // ==========================================
        // 6. PREPARAR LOS DATOS DE LA GRÁFICA
        // ==========================================

        $datosGrafica = $datosReporte->map(function ($dato) {

            return [
                'carrera' => $dato->claveCarrera,
                'cuatrimestre' => (int) $dato->cuatrimestre,
                'total' => (int) $dato->totalMaterias,
                'materiasAsignadas' => (int) $dato->materiasAsignadas,
                'materiasPendientes' => (int) $dato->materiasPendientes,
            ];

        })->values();

        // ==========================================
        // 7. PREPARAR LA LISTA GENERAL DE PENDIENTES
        // ==========================================

        $materiasPendientesDetalle = $datosReporte->flatMap(function ($dato) {

            return collect($dato->detallePendientes)->map(function ($materia) use ($dato) {

                return [
                    'carrera' => $dato->claveCarrera,
                    'cuatrimestre' => $dato->cuatrimestre,
                    'nombreAsignatura' => $materia['nombreAsignatura'],
                    'clave' => $materia['clave'],
                    'cantidadGruposFaltantes' => $materia['cantidadGruposFaltantes'],
                    'gruposFaltantes' => $materia['gruposFaltantes'],
                    'sinGrupos' => $materia['sinGrupos'],
                ];

            });

        })->values();

        // Enviar la información a la vista.
        return view('Reporte.MateriasAsignadasCuatrimestre', compact(
            'carreras',
            'periodos',
            'carrera',
            'cuatrimestre',
            'periodo',
            'anio',
            'datosReporte',
            'datosGrafica',
            'totalMaterias',
            'totalAsignadas',
            'totalPendientes',
            'porcentajeAsignacion',
            'materiasPendientesDetalle'
        ));
    }
    

/**
 * Descripción: Genera y descarga un archivo PDF con la captura visual
 * del reporte de materias asignadas por cuatrimestre.
 *
 * Entradas:
 * $solicitud: solicitud HTTP que contiene la imagen PNG del reporte.
 *
 * Salidas:
 * Archivo PDF generado con FPDF.
 */
public function descargarMateriasAsignadasCuatrimestre(Request $solicitud)
{
    // Validar que se haya enviado una imagen.
    $datosValidados = $solicitud->validate([
        'imagen' => 'required|string|max:30000000|regex:/^data:image\/png;base64,/',
    ]);

    // Obtener el contenido Base64 de la imagen.
    $imagenBase64 = substr(
        $datosValidados['imagen'],
        strlen('data:image/png;base64,')
    );

    // Decodificar la imagen.
    $contenidoImagen = base64_decode($imagenBase64, true);

    if ($contenidoImagen === false) {
        return response()->json([
            'mensaje' => 'No fue posible procesar la imagen del reporte.'
        ], 422);
    }

    // Comprobar que el contenido recibido sea una imagen PNG válida.
    $informacionImagen = getimagesizefromstring($contenidoImagen);

    if (
        $informacionImagen === false ||
        $informacionImagen['mime'] !== 'image/png'
    ) {
        return response()->json([
            'mensaje' => 'El archivo enviado no es una imagen PNG válida.'
        ], 422);
    }

    // Crear una imagen a partir del contenido recibido.
    $imagenOriginal = imagecreatefromstring($contenidoImagen);

    if ($imagenOriginal === false) {
        return response()->json([
            'mensaje' => 'No fue posible generar la imagen del reporte.'
        ], 422);
    }

    // Obtener las dimensiones de la imagen.
    $anchoImagen = imagesx($imagenOriginal);
    $altoImagen = imagesy($imagenOriginal);

    // Configurar las dimensiones útiles de una página A4.
    $anchoPagina = 190;
    $altoPagina = 277;

    // Calcular la cantidad de píxeles que corresponden a una página.
    $altoFragmento = (int) floor(
        $anchoImagen * ($altoPagina / $anchoPagina)
    );

    // Preparar el archivo temporal de la imagen original.
    $rutaImagenOriginal = tempnam(sys_get_temp_dir(), 'reporte_');

    file_put_contents($rutaImagenOriginal, $contenidoImagen);

    // Inicializar FPDF.
    $pdf = new \FPDF('P', 'mm', 'A4');

    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(false);

    // Crear las páginas necesarias para mostrar todo el reporte.
    try {

        for (
            $posicionY = 0;
            $posicionY < $altoImagen;
            $posicionY += $altoFragmento
        ) {

            // Calcular la altura del fragmento actual.
            $altoActual = min(
                $altoFragmento,
                $altoImagen - $posicionY
            );

            // Crear una imagen temporal para esta página.
            $imagenPagina = imagecreatetruecolor(
                $anchoImagen,
                $altoActual
            );

            // Establecer un fondo blanco.
            $fondoBlanco = imagecolorallocate(
                $imagenPagina,
                255,
                255,
                255
            );

            imagefill($imagenPagina, 0, 0, $fondoBlanco);

            // Copiar la sección correspondiente del reporte.
            imagecopy(
                $imagenPagina,
                $imagenOriginal,
                0,
                0,
                0,
                $posicionY,
                $anchoImagen,
                $altoActual
            );

            // Guardar temporalmente el fragmento.
            $rutaImagenPagina = tempnam(
                sys_get_temp_dir(),
                'pagina_'
            );

            imagepng($imagenPagina, $rutaImagenPagina);

            // Agregar una página al documento PDF.
            $pdf->AddPage();

            // Calcular la altura proporcional de la imagen.
            $altoPDF = $altoActual * ($anchoPagina / $anchoImagen);

            // Colocar la imagen en la página.
            $pdf->Image(
                $rutaImagenPagina,
                10,
                10,
                $anchoPagina,
                $altoPDF,
                'PNG'
            );

            // Liberar los recursos de la página.
            imagedestroy($imagenPagina);
            unlink($rutaImagenPagina);
        }

        // Obtener el contenido final del PDF.
        $contenidoPDF = $pdf->Output('S');

    } finally {

        // Liberar los recursos utilizados.
        imagedestroy($imagenOriginal);

        if (file_exists($rutaImagenOriginal)) {
            unlink($rutaImagenOriginal);
        }
    }

    // Devolver el PDF para su descarga.
    return response($contenidoPDF, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="reporte-materias-asignadas.pdf"',
    ]);
}

    /**
     * Descripción: Comprueba que el usuario autenticado tenga uno de los roles permitidos.
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

