<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Product 1',
            'description' => 'Description 1',
            'prijs' => 102,
            'created_date' => '2025-03-23',
        ]);
        Product::create([
            'name' => 'Product 2',
            'description' => 'Description 2',
            'prijs' => 103,
            'created_date' => '2025-03-23',
        ]);
        Product::create([
            'name' => 'Product 3',  
            'description' => 'Description 3',
            'prijs' => 104,
            'created_date' => '2025-03-23',
        ]);
    }   
}
