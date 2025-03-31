<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use App\Models\ContactForum;

class ContactController extends Controller
{
    public function show($name = 'Default value')
    {
        return view('Contact', ['name' => $name]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'message' => 'required|string|min:10'
        ]);

        try {
            // 1. Guardar en la base de datos
            ContactForum::create([
                'email' => $request->email,
                'message' => $request->message
            ]);

            // 2. Enviar correo al administrador
            Mail::to('thecpatelier@gmail.com')->send(new ContactFormMail($request->email, $request->message));

            return redirect()->back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'There was an error sending your message: ' . $e->getMessage())
                ->withInput();
        }
    }
} 