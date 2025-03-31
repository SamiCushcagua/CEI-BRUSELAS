<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Crear usuario admin por defecto
        User::firstOrCreate(
            ['email' => 'admin@ehb.be'],
            [
                'name' => 'admin',
                'email' => 'admin@ehb.be',
                'password' => Hash::make('Password!321'),
                'is_admin' => true
            ]
        );

        // Crear usuario normal
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Normal User',
                'email' => 'user@example.com',
                'password' => Hash::make('user123'),
                'is_admin' => false
            ]
        );

        $this->call([
            ProductSeeder::class,
            FAQSeeder::class,
            ContactForumSeeder::class
        ]);
    }
}
