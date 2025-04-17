<?php

namespace Database\Seeders;

use App\Models\modelCalificaciones;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CalificacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        modelCalificaciones::create([  //agregamos "use App\Models\modelCalificaciones;" esto al comienzo para q podamos llamar al modelo y crear un instancia
            'Examen1' => '10',
            'Examen2' => '10',
            'participacion' => '10',
            'puntualidad' => '10',
            'Material' => '10',
            'VersiculoBiblico' => '10',
            'tarea' => '10',
            'total' => '10',
        ]); 


        //
    }
}
