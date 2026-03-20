<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Period::create([
            'name' => 'Trimestre 3 - 2025',
            'year' => 2025,
            'trimester' => 3,
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-31',
            'is_active' => true,
            'is_locked' => false,
        ]);
    }
}
