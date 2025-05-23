<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class GuideUtilisationWidget extends Widget
{
    protected static string $view = 'filament.widgets.guide-utilisation-widget';
    
    // Position du widget dans le tableau de bord
    protected int $sortOrder = 2;
    
    // Guides disponibles avec leurs icônes et descriptions
    public array $guides = [];
    
    public function mount(): void
    {
        $this->chargerGuides();
    }
    
    public static function canView(): bool
    {
        // Afficher pour tous les utilisateurs connectés
        return auth()->check();
    }
    
    protected function chargerGuides(): void
    {
        // Guides généraux pour tous les utilisateurs
        $this->guides = [
            [
                'id' => 'dashboard',
                'titre' => 'Tableau de bord',
                'description' => 'Découvrez les fonctionnalités du tableau de bord',
                'icone' => 'heroicon-o-home',
                'categorie' => 'général',
                'url' => route('filament.admin.pages.dashboard') . '?guide=dashboard',
                'video' => 'https://example.com/videos/dashboard-guide.mp4',
            ],
            [
                'id' => 'gestion_employes',
                'titre' => 'Gestion des employés',
                'description' => 'Apprenez à gérer vos employés efficacement',
                'icone' => 'heroicon-o-users',
                'categorie' => 'ressources humaines',
                'url' => route('filament.admin.resources.employeurs.index') . '?guide=employes',
                'video' => 'https://example.com/videos/employee-management.mp4',
            ],
            [
                'id' => 'presence',
                'titre' => 'Suivi des présences',
                'description' => 'Maîtrisez le système de pointage et de présence',
                'icone' => 'heroicon-o-clock',
                'categorie' => 'présence',
                'url' => route('filament.admin.resources.presences.index') . '?guide=presence',
                'video' => 'https://example.com/videos/attendance-tracking.mp4',
            ],
            [
                'id' => 'conges',
                'titre' => 'Gestion des congés',
                'description' => 'Gérez les demandes et les soldes de congés',
                'icone' => 'heroicon-o-calendar',
                'categorie' => 'congés',
                'url' => route('filament.admin.resources.conges.index') . '?guide=conges',
                'video' => 'https://example.com/videos/leave-management.mp4',
            ],
            [
                'id' => 'rapports',
                'titre' => 'Rapports et statistiques',
                'description' => 'Analysez les données et générez des rapports',
                'icone' => 'heroicon-o-chart-bar',
                'categorie' => 'rapports',
                'url' => route('filament.admin.pages.dashboard') . '?guide=rapports',
                'video' => 'https://example.com/videos/reports-analytics.mp4',
            ],
        ];
        
        // Guides spécifiques pour les administrateurs
        if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()) {
            $this->guides[] = [
                'id' => 'parametres_systeme',
                'titre' => 'Paramètres système',
                'description' => 'Configurez les paramètres avancés du système',
                'icone' => 'heroicon-o-cog',
                'categorie' => 'administration',
                'url' => route('filament.admin.pages.dashboard') . '?guide=parametres',
                'video' => 'https://example.com/videos/system-settings.mp4',
            ];
            
            $this->guides[] = [
                'id' => 'gestion_utilisateurs',
                'titre' => 'Gestion des utilisateurs',
                'description' => 'Gérez les comptes et les permissions des utilisateurs',
                'icone' => 'heroicon-o-user-group',
                'categorie' => 'administration',
                'url' => route('filament.admin.resources.users.index') . '?guide=utilisateurs',
                'video' => 'https://example.com/videos/user-management.mp4',
            ];
        }
    }
    
    public function ouvrirGuide(string $guideId): void
    {
        $guide = collect($this->guides)->firstWhere('id', $guideId);
        
        if ($guide) {
            // Rediriger vers l'URL du guide
            redirect()->to($guide['url']);
        }
    }
}
