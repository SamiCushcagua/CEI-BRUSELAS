<?php

namespace Database\Seeders;

use App\Models\ContactForum as ContactForumModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactForumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContactForumModel::create([
            'email' => 'test@test.com',
            'message' => 'test message'
        ]);

        ContactForumModel::create([
            'email' => 'test2@test.com',
            'message' => 'test message 2'
        ]);
    }
} 