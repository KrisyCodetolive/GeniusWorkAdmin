<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Site;
use App\Models\Entreprise;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Resources\SiteResource;
use Illuminate\Support\Facades\Auth;

class SiteMapPage extends Page
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-map';
    
    protected static string $view = 'filament.pages.site-map-page';
    
    protected static ?string $title = 'Carte des sites';
    
    protected static ?string $navigationLabel = 'Carte des sites';
    
    protected static ?string $slug = 'site-map';
    
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Structure Organisationnelle';
    
    protected static ?int $navigationSort = 2;
    
    // Propriétés pour stocker les filtres
    public $showInactive = false;
    public $entrepriseId = null;
    
    public function mount(): void
    {
        // Récupérer l'entreprise de l'utilisateur connecté
        $user = Auth::user();
        
        if ($user && $user->entreprise_id) {
            $this->entrepriseId = $user->entreprise_id;
        }
        
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        // Déterminer si l'utilisateur est un administrateur ou un super-admin
        $user = Auth::user();
        $isAdmin = $user && ($user->isSuperAdmin() );
        
        $formSchema = [];
        
        // Afficher le sélecteur d'entreprise uniquement pour les administrateurs
        if ($isAdmin) {
            $formSchema[] = Select::make('entrepriseId')
                ->label('Filtrer par entreprise')
                ->options(Entreprise::pluck('nom', 'id'))
                ->searchable()
                ->placeholder('Toutes les entreprises')
                ->live()
                ->afterStateUpdated(function ($state) {
                    $this->entrepriseId = $state;
                });
        }
        
        $formSchema[] = Toggle::make('showInactive')
            ->label('Afficher les sites inactifs')
            ->live()
            ->afterStateUpdated(function ($state) {
                $this->showInactive = $state;
            });
        
        return $form->schema($formSchema);
    }
    
    public function getSites(): array
    {
        $user = Auth::user();
        $query = Site::query()
            ->with('entreprise')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
        
        // Si l'utilisateur n'est pas admin, filtrer par son entreprise
        if ($user && !($user->isSuperAdmin())) {
            if ($user->entreprise_id) {
                $query->where('entreprise_id', $user->entreprise_id);
            }
        } 
        // Sinon, utiliser le filtre sélectionné (pour les admins)
        else if ($this->entrepriseId) {
            $query->where('entreprise_id', $this->entrepriseId);
        }
        
        // Filtrer les sites inactifs si l'option n'est pas cochée
        if (!$this->showInactive) {
            $query->where('statut', 'actif');
        }
        
        return $query->get()
            ->map(function ($site) {
                return [
                    'id' => $site->id,
                    'nom' => $site->nom,
                    'entreprise' => $site->entreprise->nom,
                    'adresse' => $site->adresse,
                    'ville' => $site->ville,
                    'pays' => $site->pays,
                    'latitude' => (float) $site->latitude,
                    'longitude' => (float) $site->longitude,
                    'rayon' => (int) $site->rayon_geofencing,
                    'has_geofencing' => $site->has_geofencing,
                    'statut' => $site->statut,
                    'url' => SiteResource::getUrl('view', ['record' => $site->id]),
                ];
            })
            ->toArray();
    }
}
