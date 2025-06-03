<?php

namespace App\Filament\Pages;

use App\Models\Departement;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\Filiale;
use App\Models\PlageHoraire;
use App\Models\Site;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\IconPosition;
use Filament\Navigation\NavigationItem;

class Onboarding extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Configuration';
    protected static ?string $title = 'Configuration de votre compte';
    protected static ?string $slug = 'onboarding';
    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.onboarding';
    
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
    
    public static function shouldRegisterNavigation(): bool
    {
        // Ne jamais afficher dans le menu de navigation
        return false;
    }
    
    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.onboarding'))
                ->sort(static::getNavigationSort())
                ->badge(static::getNavigationBadge(), static::getNavigationBadgeColor())
                ->url(static::getNavigationUrl()),
        ];
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
                'conseils' => [
                    'Assurez-vous que votre logo est de bonne qualité',
                    'Renseignez vos coordonnées complètes',
                    'Précisez votre secteur d\'activité'
                ]
            ],
            [
                'id' => 'sites',
                'titre' => 'Sites',
                'description' => 'Ajoutez les sites de votre entreprise',
                'icone' => 'heroicon-o-map-pin',
                'url' => route('filament.admin.resources.sites.index'),
                'complete' => $this->verifierSites(),
                'statistiques' => [
                    'total' => $this->statistiques['sites'] ?? 0,
                    'objectif' => 'Au moins 1 site'
                ],
                'conseils' => [
                    'Ajoutez l\'adresse complète de chaque site',
                    'Précisez les coordonnées GPS pour faciliter la géolocalisation',
                    'Associez chaque site à un responsable'
                ]
            ],
            [
                'id' => 'filiales',
                'titre' => 'Filiales',
                'description' => 'Ajoutez les filiales de votre entreprise (si applicable)',
                'icone' => 'heroicon-o-building-storefront',
                'url' => route('filament.admin.resources.filiales.index'),
                'complete' => $this->verifierFiliales(),
                'statistiques' => [
                    'total' => $this->statistiques['filiales'] ?? 0,
                    'objectif' => 'Optionnel'
                ],
                'conseils' => [
                    'Précisez la participation de votre entreprise dans chaque filiale',
                    'Ajoutez les coordonnées complètes de chaque filiale',
                    'Définissez les relations entre l\'entreprise mère et ses filiales'
                ]
            ],
            [
                'id' => 'departements',
                'titre' => 'Départements',
                'description' => 'Créez les départements de votre entreprise',
                'icone' => 'heroicon-o-user-group',
                'url' => route('filament.admin.resources.departements.index'),
                'complete' => $this->verifierDepartements(),
                'statistiques' => [
                    'total' => $this->statistiques['departements'] ?? 0,
                    'objectif' => 'Au moins 1 département'
                ],
                'conseils' => [
                    'Structurez vos départements selon votre organigramme',
                    'Désignez un responsable pour chaque département',
                    'Définissez clairement les missions de chaque département'
                ]
            ],
        
            [
                'id' => 'plages_horaires',
                'titre' => 'Plages horaires',
                'description' => 'Définissez les horaires de travail',
                'icone' => 'heroicon-o-clock',
                'url' => route('filament.admin.resources.plage-horaire-bases.index'),
                'complete' => $this->verifierPlagesHoraires(),
                'statistiques' => [
                    'total' => $this->statistiques['plages_horaires'] ?? 0,
                    'objectif' => 'Au moins 1 plage horaire'
                ],
                'conseils' => [
                    'Créez des plages horaires adaptées à chaque type de poste',
                    'N\'oubliez pas de définir les pauses',
                    'Paramétrez les marges de retard autorisées'
                ]
            ],
            [
                'id' => 'employes',
                'titre' => 'Employés',
                'description' => 'Ajoutez vos employés',
                'icone' => 'heroicon-o-users',
                'url' => route('filament.admin.resources.employeurs.index'),
                'complete' => $this->verifierEmployes(),
                'statistiques' => [
                    'total' => $this->statistiques['employes'] ?? 0,
                    'objectif' => 'Au moins 1 employé'
                ],
                'conseils' => [
                    'Importez vos employés via un fichier Excel pour gagner du temps',
                    'Associez chaque employé à un département',
                    'Attribuez les plages horaires appropriées à chaque employé'
                ]
            ],
        ];
    }
    
    protected function calculerStatistiques(): void
    {
        $entrepriseId = Auth::user()->entreprise_id;
        
        $this->statistiques = [
            'sites' => Site::where('entreprise_id', $entrepriseId)->count(),
            'departements' => Departement::where('entreprise_id', $entrepriseId)->count(),
            'filiales' => Filiale::where('entreprise_id', $entrepriseId)->count(),
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
}
