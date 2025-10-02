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
        $books = BibleBook::orderBy('order')->get();
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
        
        return view('bible.chapter', compact('chapter', 'verses', 'isRead'));
    }

    public function markChapterAsRead(BibleChapter $chapter)
    {
        auth()->user()->markChapterAsRead($chapter->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Capítulo marcado como leído'
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
        
        $books = BibleBook::with(['chapters.userReadings' => function($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->get();
        
        return view('bible.progress', compact('books', 'totalChapters', 'readChapters', 'progress'));
    }
}