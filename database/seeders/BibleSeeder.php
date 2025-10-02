<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BibleBook;
use App\Models\BibleChapter;
use App\Models\BibleVerse;

class BibleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear algunos libros de ejemplo
        $genesis = BibleBook::create([
            'name' => 'Génesis',
            'testament' => 'old',
            'order' => 1,
            'chapters_count' => 50
        ]);

        $exodo = BibleBook::create([
            'name' => 'Éxodo',
            'testament' => 'old',
            'order' => 2,
            'chapters_count' => 40
        ]);

        $mateo = BibleBook::create([
            'name' => 'Mateo',
            'testament' => 'new',
            'order' => 40,
            'chapters_count' => 28
        ]);

        $juan = BibleBook::create([
            'name' => 'Juan',
            'testament' => 'new',
            'order' => 43,
            'chapters_count' => 21
        ]);

        // Crear capítulos de ejemplo
        $genesis1 = BibleChapter::create([
            'book_id' => $genesis->id,
            'chapter_number' => 1,
            'title' => 'La creación',
            'verses_count' => 31
        ]);

        $juan3 = BibleChapter::create([
            'book_id' => $juan->id,
            'chapter_number' => 3,
            'title' => 'Nicodemo',
            'verses_count' => 36
        ]);

        // Crear versículos de ejemplo
        BibleVerse::create([
            'chapter_id' => $genesis1->id,
            'verse_number' => 1,
            'text' => 'En el principio creó Dios los cielos y la tierra.'
        ]);

        BibleVerse::create([
            'chapter_id' => $genesis1->id,
            'verse_number' => 2,
            'text' => 'Y la tierra estaba desordenada y vacía, y las tinieblas estaban sobre la faz del abismo, y el Espíritu de Dios se movía sobre la faz de las aguas.'
        ]);

        BibleVerse::create([
            'chapter_id' => $juan3->id,
            'verse_number' => 16,
            'text' => 'Porque de tal manera amó Dios al mundo, que ha dado a su Hijo unigénito, para que todo aquel que en él cree, no se pierda, mas tenga vida eterna.'
        ]);

        BibleVerse::create([
            'chapter_id' => $juan3->id,
            'verse_number' => 17,
            'text' => 'Porque no envió Dios a su Hijo al mundo para condenar al mundo, sino para que el mundo sea salvo por él.'
        ]);
    }
}