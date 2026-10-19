<?php

namespace App\Http\Controllers;

use App\Models\Periodo;

class PeriodoController extends Controller
{
    /**
     * Descripción: Obtiene los periodos registrados en el sistema.
     *
     * Entradas: No recibe parámetros.
     *
     * Salidas: Retorna los periodos registrados.
     */
    public function index()
    {
        // Obtener todos los periodos registrados.
        $periodosRegistrados = Periodo::orderBy('anio', 'asc')
            ->orderBy('id_periodo', 'asc')
            ->get();

        // Retornar los periodos encontrados.
        return $periodosRegistrados;
    }
}