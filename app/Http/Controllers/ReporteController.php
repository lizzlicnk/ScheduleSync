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
     * Vista principal de reportes con los cuatro tipos disponibles.
     */
    public function index(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado.
        $this->verificarRol([1, 2]);

        // Mostrar la pantalla principal de reportes.
        return view('Reporte.GestionReportes');
    }


    /**
     * Descripción: Muestra el reporte de materias con docente asignado
     * por carrera y cuatrimestre.
     *
     * Entradas:
     * $solicitud: solicitud HTTP con los filtros del reporte.
     *
     * Salidas:
     * Vista individual del reporte con indicadores, tabla y datos
     * para generar la gráfica de materias asignadas y pendientes.
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

        // Consultar el total de materias activas por carrera y cuatrimestre.
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

        // Obtener el catálogo de materias agrupado.
        $consultaMaterias = $consultaMaterias
            ->select(
                'c.id_carrera',
                'c.nombreCarrera',
                'c.claveCarrera',
                'a.cuatrimestre'
            )
            ->selectRaw('COUNT(DISTINCT a.id_asignatura) as totalMaterias')
            ->groupBy(
                'c.id_carrera',
                'c.nombreCarrera',
                'c.claveCarrera',
                'a.cuatrimestre'
            );

        // Consultar las materias que tienen docente asignado
        // en horarios que ya fueron finalizados.
        $consultaAsignadas = DB::table('detalle_horario as d')
            ->join('horario as h', 'd.id_horario', '=', 'h.id_horario')
            ->join('asignatura as a', 'd.id_asignatura', '=', 'a.id_asignatura')
            ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->join('periodo as p', 'h.id_periodo', '=', 'p.id_periodo')
            ->where('h.borrador', 1)
            ->where('a.estado', 1)
            ->where('c.estado', 1);

        // Aplicar el filtro de carrera.
        if (!empty($carrera)) {
            $consultaAsignadas->where('c.claveCarrera', $carrera);
        }

        // Aplicar el filtro de cuatrimestre.
        if (!empty($cuatrimestre)) {
            $consultaAsignadas->where('a.cuatrimestre', $cuatrimestre);
        }

        // Aplicar el filtro de periodo.
        if (!empty($periodo)) {
            $consultaAsignadas->where('p.estacion', $periodo);
        }

        // Aplicar el filtro de año.
        if (!empty($anio)) {
            $consultaAsignadas->where('p.anio', $anio);
        }

        // Contar cada asignatura una sola vez por carrera y cuatrimestre.
        $consultaAsignadas = $consultaAsignadas
            ->select(
                'a.id_carrera',
                'a.cuatrimestre'
            )
            ->selectRaw('COUNT(DISTINCT d.id_asignatura) as materiasAsignadas')
            ->groupBy(
                'a.id_carrera',
                'a.cuatrimestre'
            );

        // Relacionar el catálogo de materias con las materias asignadas.
        $datosReporte = DB::query()
            ->fromSub($consultaMaterias, 'materias')
            ->leftJoinSub($consultaAsignadas, 'asignadas', function ($union) {
                $union->on(
                    'materias.id_carrera',
                    '=',
                    'asignadas.id_carrera'
                )->on(
                    'materias.cuatrimestre',
                    '=',
                    'asignadas.cuatrimestre'
                );
            })
            ->select(
                'materias.id_carrera',
                'materias.nombreCarrera',
                'materias.claveCarrera',
                'materias.cuatrimestre',
                'materias.totalMaterias'
            )
            ->selectRaw('COALESCE(asignadas.materiasAsignadas, 0) as materiasAsignadas')
            ->orderBy('materias.nombreCarrera')
            ->orderBy('materias.cuatrimestre')
            ->get();

        // Calcular las materias pendientes y el porcentaje de asignación.
        $datosReporte = $datosReporte->map(function ($dato) {

            // Obtener el total de materias del cuatrimestre.
            $total = (int) $dato->totalMaterias;

            // Obtener las materias asignadas.
            $asignadas = (int) $dato->materiasAsignadas;

            // Calcular las materias pendientes.
            $pendientes = max(0, $total - $asignadas);

            // Calcular el porcentaje de asignación.
            $porcentaje = $total > 0
                ? ($asignadas / $total) * 100
                : 0;

            // Agregar los resultados al registro.
            $dato->materiasAsignadas = $asignadas;
            $dato->materiasPendientes = $pendientes;
            $dato->porcentajeAsignacion = $porcentaje;

            return $dato;
        });

        // Calcular los indicadores generales del reporte.
        $totalMaterias = $datosReporte->sum('totalMaterias');

        $totalAsignadas = $datosReporte->sum('materiasAsignadas');

        $totalPendientes = $datosReporte->sum('materiasPendientes');

        // Calcular el porcentaje general de asignación.
        $porcentajeAsignacion = $totalMaterias > 0
            ? ($totalAsignadas / $totalMaterias) * 100
            : 0;

        // Preparar los datos que utilizará Chart.js.
        $datosGrafica = $datosReporte->map(function ($dato) {

            return [
                'carrera' => $dato->claveCarrera,
                'cuatrimestre' => (int) $dato->cuatrimestre,
                'total' => (int) $dato->totalMaterias,
                'materiasAsignadas' => (int) $dato->materiasAsignadas,
                'materiasPendientes' => (int) $dato->materiasPendientes,
            ];

        })->values();

        // Enviar toda la información a la vista del reporte.
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
            'porcentajeAsignacion'
        ));
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

