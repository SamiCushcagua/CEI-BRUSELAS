<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FAQController extends Controller
{
    public function index()
    {
        $faqs = FAQ::all();
        $categories = FAQ::distinct()->pluck('category');
        
        return view('FAQ', [
            'faqs' => $faqs,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('FAQ')->with('error', 'No tienes permiso para realizar esta acción.');
        }

        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'required|string|max:255'
        ]);

        FAQ::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category
        ]);

        return redirect()->route('FAQ')->with('success', 'Pregunta agregada correctamente.');
    }

    public function edit($id)
    {
        $faq = FAQ::findOrFail($id);
        $categories = FAQ::distinct()->pluck('category');
        
        return view('edit_FAQ', [
            'faq' => $faq,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('FAQ')->with('error', 'No tienes permiso para realizar esta acción.');
        }

        $faq = FAQ::findOrFail($id);
        
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'required|string|max:255'
        ]);

        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category
        ]);

        return redirect()->route('FAQ')->with('success', 'Pregunta actualizada correctamente.');
    }

    public function destroy($id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('FAQ')->with('error', 'No tienes permiso para realizar esta acción.');
        }

        $faq = FAQ::findOrFail($id);
        $faq->delete();

        return redirect()->route('FAQ')->with('success', 'Pregunta eliminada correctamente.');
    }
} 