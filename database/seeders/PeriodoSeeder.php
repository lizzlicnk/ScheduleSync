<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Periodo;

class PeriodoSeeder extends Seeder
{
    /**
     * Descripción: Registra los periodos académicos del sistema.
     * Entradas: Ninguna.
     * Salidas: Periodos de Primavera, Otoño e Invierno para los años configurados.
     */
    public function run(): void
    {
        // Estaciones permitidas
        $estaciones = [
            'Otoño',
            'Invierno',
            'Primavera',
        ];

        // Rango de años disponibles.
        $anioInicial = 2026;
        $anioFinal = 2100;

        for ($anio = $anioInicial; $anio <= $anioFinal; $anio++) {
            foreach ($estaciones as $estacion) {

                // Crear el periodo solo si todavía no existe.
                Periodo::firstOrCreate([
                    'estacion' => $estacion,
                    'anio' => $anio,
                ]);
            }
        }
    }
}
