<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    /**
     * Télécharge le fichier de sauvegarde SQL stocké en session
     *
     * @return \Illuminate\Http\Response
     */
    public function download()
    {
        // Log pour débogage - vérifier les clés de session disponibles
        Log::debug('Clés de session disponibles', [
            'has_backup_content' => session()->has('backup_content'),
            'has_backup_filename' => session()->has('backup_filename'),
            'has_backup_size' => session()->has('backup_size'),
            'session_keys' => array_keys(session()->all())
        ]);
        
        // Vérifier si les données de sauvegarde sont présentes en session
        if (!session()->has('backup_content')) {
            Log::error('Tentative de téléchargement de backup sans contenu en session');
            return redirect()->back()->with('error', 'Aucune sauvegarde disponible pour le téléchargement.');
        }

        $content = session('backup_content');
        $filename = session('backup_filename', 'backup_' . now()->format('Y-m-d_His') . '.sql');
        $size = session('backup_size', strlen($content));

        // Log avant téléchargement
        Log::info('Préparation du téléchargement du fichier de sauvegarde', [
            'filename' => $filename,
            'size' => round($size / 1024 / 1024, 2) . ' MB',
            'content_length' => strlen($content)
        ]);

        // Nettoyer la session après récupération des données
        session()->forget(['backup_content', 'backup_filename', 'backup_size']);

        // Retourner la réponse avec le contenu SQL
        return response($content)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($content));
    }
}
