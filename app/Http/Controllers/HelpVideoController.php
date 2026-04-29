<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpVideoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $commonVideos = [
      /*      [
                'title' => 'Como actualizar mi perfil',
                'description' => 'Edita tu perfil, imagen y datos basicos del sistema.',
                'url' => 'https://www.youtube.com/embed/aqz-KE-bpKQ',
            ],
            [
                'title' => 'Navegacion general de la plataforma',
                'description' => 'Recorrido rapido por los modulos y accesos principales.',
                'url' => 'https://www.youtube.com/embed/ysz5S6PUM-U',
            ], */
        ];

        $roleLabel = 'Usuario';
        $roleVideos = [];

        if ($user->is_admin) {
            $roleLabel = 'Administrador';
            $roleVideos = [
           /*     [
                    'title' => 'Gestion de periodos y configuracion',
                    'description' => 'Aprende a crear, activar y bloquear periodos.',
                    'url' => 'https://www.youtube.com/embed/jfKfPfyJRdk',
                ],
                [
                    'title' => 'Tablero admin y seguimiento',
                    'description' => 'Uso del tablero administrativo y resumen general.',
                    'url' => 'https://www.youtube.com/embed/kXYiU_JCYtU',
                ],*/
            ];
        } elseif ($user->isProfessor()) {
            $roleLabel = 'Profesor';
            $roleVideos = [
                [
                    'title' => 'Registro de asistencia',
                    'description' => 'Como registrar la asistencia de los estudiantes.',
                    'url' => 'https://www.youtube.com/embed/DKHraeF9uVI',
                ],
         /*       [
                    'title' => 'Cargar calificaciones',
                    'description' => 'Guia para registrar notas de forma correcta.',
                    'url' => 'https://www.youtube.com/embed/tgbNymZ7vqY',
                ],*/
            ];
        } elseif ($user->isStudent()) {
            $roleLabel = 'Estudiante';
            $roleVideos = [
        /*        [
                    'title' => 'Consultar asistencia personal',
                    'description' => 'Revisa tus registros de asistencia paso a paso.',
                    'url' => 'https://www.youtube.com/embed/aqz-KE-bpKQ',
                ],
                [
                    'title' => 'Consultar mis calificaciones',
                    'description' => 'Como revisar tus puntos por materia y periodo.',
                    'url' => 'https://www.youtube.com/embed/ysz5S6PUM-U',
                ],*/
            ]; 
        }

        return view('help.videos', [
            'commonVideos' => $commonVideos,
            'roleVideos' => $roleVideos,
            'roleLabel' => $roleLabel,
        ]);
    }
}
