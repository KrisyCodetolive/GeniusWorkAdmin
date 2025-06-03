<?php

namespace App\Filament\Widgets;

use App\Models\Departement;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\Filiale;
use App\Models\PlageHoraire;
use App\Models\Site;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class OnboardingWidget extends Widget
{
    protected static string $view = 'filament.widgets.onboarding-widget';
    
    protected int $sortOrder = 3;
    
    // Faire en sorte que le widget occupe toute la largeur
    protected int | string | array $columnSpan = 'full';
    
    public array $etapes = [];
    public array $statistiques = [
        'sites' => 0,
        'departements' => 0,
        'filiales' => 0,
        'plages_horaires' => 0,
        'employes' => 0,
    ];
    public int $progression = 0;
    
    public function mount(): void
    {
        $this->calculerStatistiques();
        $this->chargerEtapes();
        $this->calculerProgression();
    }
    
    public static function canView(): bool
    {
        // Vérifier si l'utilisateur est connecté et a un rôle approprié
        if (!auth()->check() || !(auth()->user()->isAdmin() || auth()->user()->isEntreprise())) {
            return false;
        }
        
        // Ne pas afficher le widget si toutes les étapes sont complétées
        $widget = new static();
        $widget->calculerStatistiques();
        $widget->chargerEtapes();
        $widget->calculerProgression();
        
        // Si la progression est à 100%, ne pas afficher le widget
        return $widget->progression < 100;
    }
    
    protected function chargerEtapes(): void
    {
        $this->etapes = [
            [
                'id' => 'profil_entreprise',
                'titre' => 'Profil de l\'entreprise',
                'description' => 'Complétez les informations de base de votre entreprise',
                'icone' => 'heroicon-o-building-office',
                'url' => route('filament.admin.resources.entreprises.edit', ['record' => Auth::user()->entreprise_id]),
                'complete' => $this->verifierProfilEntreprise(),
            ],
            [
                'id' => 'sites',
                'titre' => 'Sites',
                'description' => 'Ajoutez les sites de votre entreprise',
                'icone' => 'heroicon-o-map-pin',
                'url' => route('filament.admin.resources.sites.index'),
                'complete' => $this->verifierSites(),
            ],
            [
                'id' => 'filiales',
                'titre' => 'Filiales',
                'description' => 'Ajoutez les filiales de votre entreprise (si applicable)',
                'icone' => 'heroicon-o-building-storefront',
                'url' => route('filament.admin.resources.filiales.index'),
                'complete' => $this->verifierFiliales(),
            ],
            [
                'id' => 'departements',
                'titre' => 'Départements',
                'description' => 'Créez les départements de votre entreprise',
                'icone' => 'heroicon-o-user-group',
                'url' => route('filament.admin.resources.departements.index'),
                'complete' => $this->verifierDepartements(),
            ],
       
            [
                'id' => 'plages_horaires',
                'titre' => 'Plages horaires',
                'description' => 'Définissez les horaires de travail',
                'icone' => 'heroicon-o-clock',
                'url' => route('filament.admin.resources.plage-horaire-bases.index'),
                'complete' => $this->verifierPlagesHoraires(),
            ],
            [
                'id' => 'employes',
                'titre' => 'Employés',
                'description' => 'Ajoutez vos employés',
                'icone' => 'heroicon-o-users',
                'url' => route('filament.admin.resources.employeurs.index'),
                'complete' => $this->verifierEmployes(),
            ],
        ];
    }
    
    protected function calculerStatistiques(): void
    {
        $entrepriseId = Auth::user()->entreprise_id;
        
        $this->statistiques = [
            'sites' => Site::where('entreprise_id', $entrepriseId)->count(),
            'filiales' => Filiale::where('entreprise_id', $entrepriseId)->count(),
            'departements' => Departement::where('entreprise_id', $entrepriseId)->count(),
            'plages_horaires' => PlageHoraire::where('entreprise_id', $entrepriseId)->count(),
            'employes' => Employeur::where('entreprise_id', $entrepriseId)->count(),
        ];
    }
    
    protected function calculerProgression(): void
    {
        $etapesCompletes = array_filter($this->etapes, fn ($etape) => $etape['complete']);
        $this->progression = count($etapesCompletes) > 0 
            ? round((count($etapesCompletes) / count($this->etapes)) * 100) 
            : 0;
    }
    
    protected function verifierProfilEntreprise(): bool
    {
        $entreprise = Entreprise::find(Auth::user()->entreprise_id);
        
        // Vérifier si les champs essentiels sont remplis
        return $entreprise && 
               !empty($entreprise->nom) && 
               !empty($entreprise->email) && 
               !empty($entreprise->telephone) && 
               !empty($entreprise->secteur_activite);
    }
    
    protected function verifierSites(): bool
    {
        return $this->statistiques['sites'] > 0;
    }
    
    protected function verifierDepartements(): bool
    {
        return $this->statistiques['departements'] > 0;
    }
    
    protected function verifierFiliales(): bool
    {
        // Les filiales sont optionnelles, donc cette étape est considérée comme complète
        // si au moins une filiale est ajoutée ou si l'entreprise n'en a pas besoin
        return true;
    }
    
    protected function verifierPlagesHoraires(): bool
    {
        return $this->statistiques['plages_horaires'] > 0;
    }
    
    protected function verifierEmployes(): bool
    {
        return $this->statistiques['employes'] > 0;
    }
    
    public function naviguerVersEtape(string $etapeId): void
    {
        $etape = collect($this->etapes)->firstWhere('id', $etapeId);
        
        if ($etape) {
            // Rediriger vers l'URL de l'étape
            redirect()->to($etape['url']);
        }
    }
}
