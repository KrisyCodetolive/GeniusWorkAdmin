<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RessourcesExternesWidget extends Widget
{
    protected static string $view = 'filament.widgets.ressources-externes-widget';
    
    // Position du widget dans le tableau de bord
    protected int $sortOrder = 4;
    
    // Largeur du widget (1/4 de la largeur)
    protected int | string | array $columnSpan = [
        'default' => 1,
        'sm' => 1,
        'md' => 1,
        'lg' => 1,
        'xl' => 1,
        '2xl' => 1,
    ];
    
    // Ressources externes
    public array $ressources = [];
    
    public function mount(): void
    {
        $this->chargerRessources();
    }
    
    public static function canView(): bool
    {
        // Afficher pour tous les utilisateurs connectés
        return auth()->check();
    }
    
    protected function chargerRessources(): void
    {
        // Liste des ressources externes
        $this->ressources = [
            [
                'titre' => 'Documentation API',
                'description' => 'Documentation complète de notre API REST',
                'url' => 'https://api.geniuswork.com/docs',
                'icone' => 'heroicon-o-code-bracket',
                'couleur' => 'indigo',
                'categorie' => 'développement',
            ],
            [
                'titre' => 'Centre de formation',
                'description' => 'Vidéos et tutoriels pour maîtriser l\'application',
                'url' => 'https://formation.geniuswork.com',
                'icone' => 'heroicon-o-academic-cap',
                'couleur' => 'amber',
                'categorie' => 'formation',
            ],
            [
                'titre' => 'Blog RH',
                'description' => 'Articles et conseils sur la gestion RH',
                'url' => 'https://blog.geniuswork.com',
                'icone' => 'heroicon-o-newspaper',
                'couleur' => 'emerald',
                'categorie' => 'ressources',
            ],
            [
                'titre' => 'Modèles de documents',
                'description' => 'Téléchargez des modèles de documents RH',
                'url' => 'https://templates.geniuswork.com',
                'icone' => 'heroicon-o-document',
                'couleur' => 'blue',
                'categorie' => 'outils',
            ],
            [
                'titre' => 'Communauté',
                'description' => 'Rejoignez notre communauté d\'utilisateurs',
                'url' => 'https://community.geniuswork.com',
                'icone' => 'heroicon-o-user-group',
                'couleur' => 'purple',
                'categorie' => 'communauté',
            ],
        ];
    }
    
    public function ouvrirRessource(string $url): void
    {
        // Rediriger vers la ressource externe
        redirect()->away($url);
    }
}
