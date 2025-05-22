<?php

namespace App\Filament\Resources\VisiteResource\Pages;

use App\Filament\Resources\VisiteResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Visiteur;
use App\Models\Entreprise;
use App\Models\Site;
use App\Services\Visite\VisiteurService;
use App\Services\Visite\VisiteService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;

class CreateVisiteFromVisiteur extends CreateRecord
{
    protected static string $resource = VisiteResource::class;

    /**
     * @var VisiteurService
     */
    protected $visiteurService;

    /**
     * @var VisiteService
     */
    protected $visiteService;
    
    /**
     * @var Visiteur|null
     */
    protected $visiteur = null;

    public function __construct()
    {
        // Initialize services in the constructor
        $this->visiteurService = app(VisiteurService::class);
        $this->visiteService = app(VisiteService::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure services are initialized
        if (!$this->visiteurService) {
            $this->visiteurService = app(VisiteurService::class);
        }
        
        if (!$this->visiteService) {
            $this->visiteService = app(VisiteService::class);
        }
    }

    public function mount($visiteur_id = null): void
    {
        parent::mount();

        if ($visiteur_id) {
            $this->visiteur = Visiteur::find($visiteur_id);

            if ($this->visiteur) {
                // Récupérer l'entreprise et le site par défaut de l'utilisateur connecté
                $entrepriseId = Auth::user()->entreprise_id;
                $defaultSite = Site::where('entreprise_id', $entrepriseId)->first();
                
                // Pré-remplir le formulaire avec les informations du visiteur et les valeurs par défaut
                $this->form->fill([
                    // Informations du visiteur
                    'visiteur_id' => $this->visiteur->id,
                    'entreprise_id' => $this->visiteur->entreprise_id,
                    'telephone' => $this->visiteur->telephone,
                    'visiteur_existe' => true,
                    'visiteur_info' => $this->visiteur->nom_complet . ' - ' . $this->visiteur->organisation,
                    
                    // Valeurs par défaut pour la visite
                    'date_arrivee' => now(),
                    'statut' => 'en_cours',
                    'site_id' => $defaultSite ? $defaultSite->id : null,
                    'motif_visite' => 'Rendez-vous avec ' . Auth::user()->name,
                ]);
                
                // Log pour débogage
                Log::info('Création de visite pour le visiteur: ' . $this->visiteur->nom_complet);
            }
        }
    }
    
    // Nous n'utilisons plus cette méthode car nous utilisons le formulaire défini dans VisiteResource
    protected function getSteps(): array
    {
        return [];
    }

    // Nous utilisons le formulaire défini dans VisiteResource
    protected function getFormSchema(): array
    {
        return parent::getFormSchema();
    }

    public function create(bool $another = false): void
    {
        try {
            // Ensure VisiteService is available
            if (!$this->visiteService) {
                $this->visiteService = app(VisiteService::class);
            }
            
            // Préparer les données avec le hook beforeCreate
            $data = $this->getResource()::beforeCreate($this->data);
            
            // Log data before creating visite
            Log::info('Création de visite depuis visiteur avec les données: ' . json_encode($data));
            
            // Créer la visite en utilisant le service
            $visite = $this->visiteService->createVisite(
                [], // Pas besoin de données visiteur car déjà traité dans beforeCreate
                $data
            );

            // Notification de succès
            Notification::make()
                ->title('Visite créée avec succès')
                ->body('La visite pour le visiteur a été créée avec succès.')
                ->success()
                ->send();

            // Redirection
            if ($another) {
                $this->redirect($this->getResource()::getUrl('create'));
            } else {
                $this->redirect($this->getRedirectUrl());
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la visite: ' . $e->getMessage());

            Notification::make()
                ->title('Erreur lors de la création de la visite')
                ->body('Une erreur est survenue lors de la création de la visite : ' . $e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
