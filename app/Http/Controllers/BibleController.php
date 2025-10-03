<?php

namespace App\Http\Controllers;

use App\Models\BibleBook;
use App\Models\BibleChapter;
use App\Models\BibleVerse;
use App\Models\UserBibleReading;
use App\Models\UserFavoriteVerse;
use Illuminate\Http\Request;

class BibleController extends Controller
{
    public function index()
    {
        $books = BibleBook::with('chapters')->orderBy('order')->get();
        $oldTestament = $books->where('testament', 'old');
        $newTestament = $books->where('testament', 'new');
        
        return view('bible.index', compact('oldTestament', 'newTestament'));
    }

    public function books()
    {
        $books = BibleBook::with('chapters')->orderBy('order')->get();
        return view('bible.books', compact('books'));
    }

    public function book(BibleBook $book)
    {
        $chapters = $book->chapters()->orderBy('chapter_number')->get();
        
        // Verificar qué capítulos ha leído el usuario
        $readChapterIds = [];
        if (auth()->check()) {
            $readChapterIds = auth()->user()->bibleReadings()
                ->whereIn('chapter_id', $chapters->pluck('id'))
                ->pluck('chapter_id')
                ->toArray();
        }
        
        return view('bible.book', compact('book', 'chapters', 'readChapterIds'));
    }

    public function chapter(BibleChapter $chapter)
    {
        $verses = $chapter->verses()->orderBy('verse_number')->get();
        $isRead = auth()->check() ? auth()->user()->hasReadChapter($chapter->id) : false;
        
        // Obtener capítulo anterior
        $previousChapter = BibleChapter::where('book_id', $chapter->book_id)
            ->where('chapter_number', '<', $chapter->chapter_number)
            ->orderBy('chapter_number', 'desc')
            ->first();
        
        // Si no hay capítulo anterior en el mismo libro, buscar el último capítulo del libro anterior
        if (!$previousChapter) {
            $previousBook = BibleBook::where('order', '<', $chapter->book->order)
                ->orderBy('order', 'desc')
                ->first();
            if ($previousBook) {
                $previousChapter = $previousBook->chapters()
                    ->orderBy('chapter_number', 'desc')
                    ->first();
            }
        }
        
        // Obtener capítulo siguiente
        $nextChapter = BibleChapter::where('book_id', $chapter->book_id)
            ->where('chapter_number', '>', $chapter->chapter_number)
            ->orderBy('chapter_number', 'asc')
            ->first();
        
        // Si no hay capítulo siguiente en el mismo libro, buscar el primer capítulo del libro siguiente
        if (!$nextChapter) {
            $nextBook = BibleBook::where('order', '>', $chapter->book->order)
                ->orderBy('order', 'asc')
                ->first();
            if ($nextBook) {
                $nextChapter = $nextBook->chapters()
                    ->orderBy('chapter_number', 'asc')
                    ->first();
            }
        }
        
        return view('bible.chapter', compact('chapter', 'verses', 'isRead', 'previousChapter', 'nextChapter'));
    }

    public function markChapterAsRead(BibleChapter $chapter)
    {
        auth()->user()->markChapterAsRead($chapter->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Capítulo marcado como leído'
        ]);
    }

    public function unmarkChapterAsRead(BibleChapter $chapter)
    {
        auth()->user()->bibleReadings()->where('chapter_id', $chapter->id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Capítulo desmarcado como leído'
        ]);
    }

    public function favorites()
    {
        $favorites = auth()->user()->favoriteVerses()->with('verse.chapter.book')->get();
        return view('bible.favorites', compact('favorites'));
    }

    public function addToFavorites(BibleVerse $verse)
    {
        auth()->user()->favoriteVerses()->updateOrCreate(
            ['verse_id' => $verse->id],
            ['note' => request('note')]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Versículo agregado a favoritos'
        ]);
    }

    public function removeFromFavorites(BibleVerse $verse)
    {
        auth()->user()->favoriteVerses()->where('verse_id', $verse->id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Versículo removido de favoritos'
        ]);
    }
    public function progress()
    {
        $user = auth()->user();
        $totalChapters = BibleChapter::count();
        $readChapters = $user->bibleReadings()->count();
        $progress = $totalChapters > 0 ? ($readChapters / $totalChapters) * 100 : 0;
        
        // Obtener todos los libros con sus capítulos
        $books = BibleBook::with('chapters')->get();
        
        // Obtener los capítulos leídos por el usuario
        $readChapterIds = $user->bibleReadings()->pluck('chapter_id')->toArray();
        
        return view('bible.progress', compact('books', 'totalChapters', 'readChapters', 'progress', 'readChapterIds'));
    }
}