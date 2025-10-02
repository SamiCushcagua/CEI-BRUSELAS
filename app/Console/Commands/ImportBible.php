<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BibleBook;
use App\Models\BibleChapter;
use App\Models\BibleVerse;
use Illuminate\Support\Facades\DB;

class ImportBible extends Command
{
    protected $signature = 'bible:import {file}';
    protected $description = 'Import Bible from SQL file';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info('Starting Bible import...');
        
        // Desactivar verificaciones de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpiar datos existentes
        $this->info('Clearing existing data...');
        BibleVerse::truncate();
        BibleChapter::truncate();
        BibleBook::truncate();
        
        // Reactivar verificaciones de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Procesar archivo
        $this->processFile($filePath);
        
        $this->info('Bible import completed successfully!');
        return 0;
    }

    private function processFile($filePath)
    {
        $handle = fopen($filePath, 'r');
        $bookData = [];
        $chapterData = [];
        $verseData = [];
        
        while (($line = fgets($handle)) !== false) {
            if (strpos($line, 'INSERT INTO') !== false) {
                $this->processInsertLine($line, $bookData, $chapterData, $verseData);
            }
        }
        
        fclose($handle);
        
        // Crear libros
        $this->createBooks($bookData);
        
        // Crear capítulos
        $this->createChapters($chapterData);
        
        // Crear versículos
        $this->createVerses($verseData);
    }

    private function processInsertLine($line, &$bookData, &$chapterData, &$verseData)
    {
        // Extraer datos de la línea INSERT
        preg_match("/VALUES \('(\d+)', '(\d+)', '(\d+)', '(\d+)', '(.*)'\)/", $line, $matches);
        
        if (count($matches) === 6) {
            $id = $matches[1];
            $bookId = $matches[2];
            $chapter = $matches[3];
            $verse = $matches[4];
            $text = str_replace("''", "'", $matches[5]);
            
            // Agregar datos del libro
            if (!isset($bookData[$bookId])) {
                $bookData[$bookId] = [
                    'name' => $this->getBookName($bookId),
                    'testament' => $bookId <= 39 ? 'old' : 'new',
                    'order' => $bookId,
                    'chapters_count' => 0
                ];
            }
            
            // Agregar datos del capítulo
            $chapterKey = "{$bookId}_{$chapter}";
            if (!isset($chapterData[$chapterKey])) {
                $chapterData[$chapterKey] = [
                    'book_id' => $bookId,
                    'chapter_number' => $chapter,
                    'verses_count' => 0
                ];
            }
            $chapterData[$chapterKey]['verses_count']++;
            
            // Agregar datos del versículo
            $verseData[] = [
                'book_id' => $bookId,
                'chapter_number' => $chapter,
                'verse_number' => $verse,
                'text' => $text
            ];
        }
    }

    private function getBookName($bookId)
    {
        $books = [
            1 => 'Génesis', 2 => 'Éxodo', 3 => 'Levítico', 4 => 'Números', 5 => 'Deuteronomio',
            6 => 'Josué', 7 => 'Jueces', 8 => 'Rut', 9 => '1 Samuel', 10 => '2 Samuel',
            11 => '1 Reyes', 12 => '2 Reyes', 13 => '1 Crónicas', 14 => '2 Crónicas', 15 => 'Esdras',
            16 => 'Nehemías', 17 => 'Ester', 18 => 'Job', 19 => 'Salmos', 20 => 'Proverbios',
            21 => 'Eclesiastés', 22 => 'Cantares', 23 => 'Isaías', 24 => 'Jeremías', 25 => 'Lamentaciones',
            26 => 'Ezequiel', 27 => 'Daniel', 28 => 'Oseas', 29 => 'Joel', 30 => 'Amós',
            31 => 'Abdías', 32 => 'Jonás', 33 => 'Miqueas', 34 => 'Nahum', 35 => 'Habacuc',
            36 => 'Sofonías', 37 => 'Hageo', 38 => 'Zacarías', 39 => 'Malaquías', 40 => 'Mateo',
            41 => 'Marcos', 42 => 'Lucas', 43 => 'Juan', 44 => 'Hechos', 45 => 'Romanos',
            46 => '1 Corintios', 47 => '2 Corintios', 48 => 'Gálatas', 49 => 'Efesios', 50 => 'Filipenses',
            51 => 'Colosenses', 52 => '1 Tesalonicenses', 53 => '2 Tesalonicenses', 54 => '1 Timoteo', 55 => '2 Timoteo',
            56 => 'Tito', 57 => 'Filemón', 58 => 'Hebreos', 59 => 'Santiago', 60 => '1 Pedro',
            61 => '2 Pedro', 62 => '1 Juan', 63 => '2 Juan', 64 => '3 Juan', 65 => 'Judas', 66 => 'Apocalipsis'
        ];
        
        return $books[$bookId] ?? "Libro {$bookId}";
    }

    private function createBooks($bookData)
    {
        $this->info('Creating books...');
        
        foreach ($bookData as $bookId => $data) {
            BibleBook::create($data);
        }
        
        $this->info('Books created: ' . count($bookData));
    }

    private function createChapters($chapterData)
    {
        $this->info('Creating chapters...');
        
        foreach ($chapterData as $chapterKey => $data) {
            BibleChapter::create($data);
        }
        
        $this->info('Chapters created: ' . count($chapterData));
    }

    private function createVerses($verseData)
    {
        $this->info('Creating verses...');
        
        // Obtener todos los capítulos
        $chapters = BibleChapter::all();
        
        $versesToCreate = [];
        foreach ($verseData as $verse) {
            // Buscar el capítulo por book_id y chapter_number
            $chapter = $chapters->where('book_id', $verse['book_id'])
                               ->where('chapter_number', $verse['chapter_number'])
                               ->first();
            
            if ($chapter) {
                $versesToCreate[] = [
                    'chapter_id' => $chapter->id,
                    'verse_number' => $verse['verse_number'],
                    'text' => $verse['text'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        // Insertar en lotes
        $chunks = array_chunk($versesToCreate, 1000);
        foreach ($chunks as $chunk) {
            BibleVerse::insert($chunk);
        }
        
        $this->info('Verses created: ' . count($versesToCreate));
    }
}