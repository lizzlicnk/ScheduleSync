<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Descripción: Ejecuta los seeders iniciales del sistema.
     * Entradas: Ninguna.
     * Salidas: Registros iniciales de los periodos académicos.
     */
    public function run(): void
    {
        // Registrar los periodos académicos.
        $this->call([
            PeriodoSeeder::class,
        ]);
    }
}
