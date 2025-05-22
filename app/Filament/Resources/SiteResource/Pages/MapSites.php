<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use Filament\Resources\Pages\Page;
use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\View\View;

class MapSites extends Page
{
    use InteractsWithForms;
    
    protected static string $resource = SiteResource::class;

    // Modifier le chemin de la vue pour s'assurer qu'il correspond exactement à la structure des dossiers
    protected static string $view = 'filament.pages.map-sites';
    
    protected static ?string $navigationIcon = 'heroicon-o-map';
    
    protected static ?string $navigationLabel = 'Carte des sites';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'entreprise_id' => null,
            'show_inactive' => false,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('entreprise_id')
                    ->label('Filtrer par entreprise')
                    ->relationship('entreprise', 'nom')
                    ->searchable()
                    ->placeholder('Toutes les entreprises')
                    ->live(),
                Toggle::make('show_inactive')
                    ->label('Afficher les sites inactifs')
                    ->live(),
            ]);
    }
    
    public function getSites(): array
    {
        $query = Site::query()
            ->with('entreprise')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
        
        // Filtrer par entreprise si sélectionnée
        if ($this->form->getState()['entreprise_id']) {
            $query->where('entreprise_id', $this->form->getState()['entreprise_id']);
        }
        
        // Filtrer les sites inactifs si l'option n'est pas cochée
        if (!$this->form->getState()['show_inactive']) {
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
