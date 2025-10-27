<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvaluationType;

class EvaluationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $evaluationTypes = [
            ['name' => 'Tareas', 'description' => 'Evaluación de tareas y trabajos asignados', 'is_active' => true],
            ['name' => 'Exámenes', 'description' => 'Evaluación mediante exámenes escritos', 'is_active' => true],
            ['name' => 'Participación', 'description' => 'Evaluación de participación en clase', 'is_active' => true],
            ['name' => 'Biblia', 'description' => 'Evaluación de conocimiento bíblico', 'is_active' => true],
            ['name' => 'Texto', 'description' => 'Evaluación del libro de texto', 'is_active' => true],
            ['name' => 'Otro', 'description' => 'Otras formas de evaluación', 'is_active' => true],
        ];

        foreach ($evaluationTypes as $type) {
            EvaluationType::create($type);
        }
    }
}