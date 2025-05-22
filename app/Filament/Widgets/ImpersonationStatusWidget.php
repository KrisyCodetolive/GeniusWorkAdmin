<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Livewire\Redirector;
use Illuminate\Support\Facades\Log;

class ImpersonationStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.impersonation-status-widget';
    
    // Définir la position du widget sur le tableau de bord
    protected int | string | array $columnSpan = 'full';
    
    // Définir si le widget doit être affiché
    public static function canView(): bool
    {
        // N'afficher le widget que si une session d'impersonation est en cours
        return Session::has('impersonate_origin_id');
    }
    
    // Récupérer les informations sur l'utilisateur original
    public function getOriginalUserData()
    {
        if (!Session::has('impersonate_origin_id')) {
            return null;
        }
        
        $originalUserId = Session::get('impersonate_origin_id');
        $originalUser = User::find($originalUserId);
        
        if (!$originalUser) {
            return [
                'name' => 'Utilisateur inconnu',
                'role' => Session::get('impersonate_origin_role', 'Rôle inconnu'),
            ];
        }
        
        return [
            'name' => $originalUser->name,
            'role' => $originalUser->role,
        ];
    }
    
    // Arrêter l'impersonation
    public function stopImpersonating()
    {
        if (!Session::has('impersonate_origin_id')) {
            return redirect()->route('filament.admin.pages.dashboard');
        }
        
        // Récupérer l'ID de l'utilisateur original
        $originalUserId = Session::get('impersonate_origin_id');
        
        // Récupérer l'utilisateur original
        $originalUser = User::find($originalUserId);
        
        if (!$originalUser) {
            // Si l'utilisateur original n'existe plus, déconnecter simplement
            Auth::logout();
            Session::flush();
            
            return redirect()->route('filament.admin.auth.login');
        }
        
        // Nettoyer la session actuelle
        Session::flush();
        
        // Créer une nouvelle session
        Session::regenerate(true);
        
        // Se reconnecter en tant qu'utilisateur original
        Auth::loginUsingId($originalUserId);
        
        // Journaliser l'action pour l'audit
        Log::info('Fin d\'impersonation: Retour à l\'utilisateur ' . $originalUserId);
        
        return redirect()->route('filament.admin.pages.dashboard');
    }
}
