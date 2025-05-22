# Plan d'implémentation du Wizard de génération des bulletins de paie

## Introduction

Ce document détaille les étapes techniques pour implémenter un formulaire de type wizard pour la génération des bulletins de paie, à la fois en mode individuel et en masse. L'implémentation utilisera Filament PHP et suivra les bonnes pratiques de développement Laravel.

## Architecture

### Composants principaux

1. **Pages Filament**
   - `GenerateBulletinPaie` : Page pour la génération individuelle
   - `GenerateBulletinPaieMasse` : Page pour la génération en masse

2. **Services**
   - `CalculPaieService` : Service existant pour les calculs de paie
   - `GenerationBulletinService` : Nouveau service pour la génération des bulletins

3. **Modèles**
   - `BulletinPaie` : Modèle existant
   - `ElementPaie` : Modèle existant
   - `ConfigurationPaie` : Modèle existant

## Étapes d'implémentation

### 1. Création des pages Filament pour le wizard

#### 1.1 Génération individuelle

Créer une nouvelle page Filament qui étend `Filament\Resources\Pages\Page` et implémente le wizard :

```php
// app/Filament/Resources/Paie/BulletinPaieResource/Pages/GenerateBulletinPaie.php

namespace App\Filament\Resources\Paie\BulletinPaieResource\Pages;

use App\Filament\Resources\Paie\BulletinPaieResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;

class GenerateBulletinPaie extends Page
{
    protected static string $resource = BulletinPaieResource::class;
    
    protected static string $view = 'filament.resources.paie.bulletin-paie-resource.pages.generate-bulletin-paie';
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Étapes du wizard
                ])
                ->skippable()
                ->persistStepInQueryString()
            ]);
    }
}
```

#### 1.2 Génération en masse

Créer une page similaire pour la génération en masse :

```php
// app/Filament/Resources/Paie/BulletinPaieResource/Pages/GenerateBulletinPaieMasse.php

namespace App\Filament\Resources\Paie\BulletinPaieResource\Pages;

use App\Filament\Resources\Paie\BulletinPaieResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;

class GenerateBulletinPaieMasse extends Page
{
    protected static string $resource = BulletinPaieResource::class;
    
    protected static string $view = 'filament.resources.paie.bulletin-paie-resource.pages.generate-bulletin-paie-masse';
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Étapes du wizard pour la génération en masse
                ])
                ->skippable()
                ->persistStepInQueryString()
            ]);
    }
}
```

### 2. Définition des étapes du wizard

#### 2.1 Génération individuelle

```php
// Dans GenerateBulletinPaie.php

Wizard::make([
    Step::make('Informations générales')
        ->schema([
            // Champs pour l'étape 1
            Forms\Components\Select::make('employeur_id')
                ->label('Employé')
                ->options(function () {
                    return Employeur::where('entreprise_id', Auth::user()->entreprise_id)
                        ->where('statut', 'actif')
                        ->get()
                        ->pluck('nom_complet', 'id');
                })
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $employe = Employeur::find($state);
                        if ($employe) {
                            $set('salaire_base', $employe->salaire_base ?? 0);
                        }
                    }
                }),
                
            Forms\Components\Select::make('configuration_paie_id')
                ->label('Configuration de paie')
                ->options(function () {
                    return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                        ->get()
                        ->pluck('nom', 'id');
                })
                ->default(function () {
                    return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                        ->where('est_defaut', true)
                        ->first()?->id;
                })
                ->searchable()
                ->required(),
                
            Forms\Components\DatePicker::make('periode_debut')
                ->label('Début de période')
                ->default(Carbon::now()->startOfMonth()->format('Y-m-d'))
                ->required(),
                
            Forms\Components\DatePicker::make('periode_fin')
                ->label('Fin de période')
                ->default(Carbon::now()->endOfMonth()->format('Y-m-d'))
                ->required()
                ->after('periode_debut'),
                
            Forms\Components\DatePicker::make('date_paiement')
                ->label('Date de paiement')
                ->default(Carbon::now()->format('Y-m-d'))
                ->required(),
        ]),
        
    Step::make('Éléments de rémunération')
        ->schema([
            // Champs pour l'étape 2
            Forms\Components\TextInput::make('salaire_base')
                ->label('Salaire de base')
                ->numeric()
                ->required()
                ->default(0),
                
            Forms\Components\Repeater::make('indemnites')
                ->label('Indemnités')
                ->schema([
                    // Schéma des indemnités
                ])
                ->columns(2)
                ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                ->collapsible(),
                
            Forms\Components\Repeater::make('primes')
                ->label('Primes')
                ->schema([
                    // Schéma des primes
                ])
                ->columns(2)
                ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                ->collapsible(),
                
            Forms\Components\Repeater::make('retenues')
                ->label('Retenues supplémentaires')
                ->schema([
                    // Schéma des retenues
                ])
                ->columns(2)
                ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                ->collapsible(),
        ]),
        
    Step::make('Aperçu et validation')
        ->schema([
            // Champs pour l'étape 3
            Forms\Components\Placeholder::make('apercu_titre')
                ->label('Aperçu du bulletin de paie')
                ->content(function (Forms\Get $get) {
                    $employeur = Employeur::find($get('employeur_id'));
                    return 'Bulletin de paie pour ' . ($employeur ? $employeur->nom_complet : 'Employé inconnu');
                }),
                
            Forms\Components\Grid::make(2)
                ->schema([
                    // Affichage des résultats calculés
                ]),
                
            Forms\Components\Checkbox::make('valider_directement')
                ->label('Valider directement le bulletin')
                ->helperText('Si coché, le bulletin sera directement validé après sa génération. Sinon, il sera enregistré en brouillon.'),
        ]),
])
->skippable()
->persistStepInQueryString()
```

#### 2.2 Génération en masse

```php
// Dans GenerateBulletinPaieMasse.php

Wizard::make([
    Step::make('Sélection des employés')
        ->schema([
            // Champs pour l'étape 1
            Forms\Components\CheckboxList::make('employeur_ids')
                ->label('Employés')
                ->options(function () {
                    return Employeur::where('entreprise_id', Auth::user()->entreprise_id)
                        ->where('statut', 'actif')
                        ->get()
                        ->pluck('nom_complet', 'id');
                })
                ->searchable()
                ->bulkToggleable()
                ->required()
                ->columns(2),
        ]),
        
    Step::make('Paramètres communs')
        ->schema([
            // Champs pour l'étape 2
            Forms\Components\Select::make('configuration_paie_id')
                ->label('Configuration de paie')
                ->options(function () {
                    return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                        ->get()
                        ->pluck('nom', 'id');
                })
                ->default(function () {
                    return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                        ->where('est_defaut', true)
                        ->first()?->id;
                })
                ->searchable()
                ->required(),
                
            Forms\Components\DatePicker::make('periode_debut')
                ->label('Début de période')
                ->default(Carbon::now()->startOfMonth()->format('Y-m-d'))
                ->required(),
                
            Forms\Components\DatePicker::make('periode_fin')
                ->label('Fin de période')
                ->default(Carbon::now()->endOfMonth()->format('Y-m-d'))
                ->required()
                ->after('periode_debut'),
                
            Forms\Components\DatePicker::make('date_paiement')
                ->label('Date de paiement')
                ->default(Carbon::now()->format('Y-m-d'))
                ->required(),
                
            Forms\Components\Toggle::make('utiliser_salaire_base_employe')
                ->label('Utiliser le salaire de base de chaque employé')
                ->default(true)
                ->reactive(),
                
            Forms\Components\TextInput::make('salaire_base_commun')
                ->label('Salaire de base commun')
                ->numeric()
                ->visible(fn (Forms\Get $get) => !$get('utiliser_salaire_base_employe'))
                ->required(fn (Forms\Get $get) => !$get('utiliser_salaire_base_employe')),
                
            Forms\Components\Toggle::make('appliquer_indemnites_communes')
                ->label('Appliquer des indemnités communes')
                ->default(false)
                ->reactive(),
                
            Forms\Components\Repeater::make('indemnites_communes')
                ->label('Indemnités communes')
                ->schema([
                    // Schéma des indemnités
                ])
                ->visible(fn (Forms\Get $get) => $get('appliquer_indemnites_communes'))
                ->columns(2),
                
            Forms\Components\Toggle::make('appliquer_primes_communes')
                ->label('Appliquer des primes communes')
                ->default(false)
                ->reactive(),
                
            Forms\Components\Repeater::make('primes_communes')
                ->label('Primes communes')
                ->schema([
                    // Schéma des primes
                ])
                ->visible(fn (Forms\Get $get) => $get('appliquer_primes_communes'))
                ->columns(2),
        ]),
        
    Step::make('Aperçu et validation')
        ->schema([
            // Champs pour l'étape 3
            Forms\Components\Placeholder::make('apercu_titre')
                ->label('Aperçu des bulletins à générer')
                ->content(function (Forms\Get $get) {
                    $nbEmployes = count($get('employeur_ids') ?? []);
                    return 'Génération de ' . $nbEmployes . ' bulletin(s) de paie';
                }),
                
            Forms\Components\Placeholder::make('apercu_tableau')
                ->label('')
                ->content(function (Forms\Get $get) {
                    // Logique pour afficher un tableau récapitulatif
                }),
                
            Forms\Components\Checkbox::make('valider_directement')
                ->label('Valider directement les bulletins')
                ->helperText('Si coché, les bulletins seront directement validés après leur génération. Sinon, ils seront enregistrés en brouillon.'),
        ]),
])
->skippable()
->persistStepInQueryString()
```

### 3. Création des vues Blade

#### 3.1 Vue pour la génération individuelle

```blade
<!-- resources/views/filament/resources/paie/bulletin-paie-resource/pages/generate-bulletin-paie.blade.php -->
<x-filament-panels::page>
    <form wire:submit.prevent="generate">
        {{ $this->form }}
        
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                Générer le bulletin
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
```

#### 3.2 Vue pour la génération en masse

```blade
<!-- resources/views/filament/resources/paie/bulletin-paie-resource/pages/generate-bulletin-paie-masse.blade.php -->
<x-filament-panels::page>
    <form wire:submit.prevent="generateMasse">
        {{ $this->form }}
        
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                Générer les bulletins
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
```

### 4. Implémentation du service de génération

```php
// app/Services/Paie/GenerationBulletinService.php

namespace App\Services\Paie;

use App\Models\Employeur;
use App\Models\Paie\BulletinPaie;
use App\Models\Paie\ConfigurationPaie;
use App\Interfaces\Paie\CalculPaieServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GenerationBulletinService
{
    protected $calculPaieService;
    
    public function __construct(CalculPaieServiceInterface $calculPaieService)
    {
        $this->calculPaieService = $calculPaieService;
    }
    
    /**
     * Génère un bulletin de paie individuel
     */
    public function genererBulletinIndividuel(array $data): BulletinPaie
    {
        // Logique de génération individuelle
    }
    
    /**
     * Génère des bulletins de paie en masse
     */
    public function genererBulletinsMasse(array $data): array
    {
        // Logique de génération en masse
    }
    
    /**
     * Vérifie si un bulletin existe déjà pour l'employé et la période
     */
    protected function bulletinExiste(string $employeurId, string $periodeDebut, string $periodeFin): bool
    {
        // Logique de vérification
    }
}
```

### 5. Ajout des méthodes dans les pages Filament

#### 5.1 Génération individuelle

```php
// Dans GenerateBulletinPaie.php

public function generate()
{
    $data = $this->form->getState();
    
    try {
        $generationService = app(GenerationBulletinService::class);
        $bulletin = $generationService->genererBulletinIndividuel($data);
        
        if ($data['valider_directement'] ?? false) {
            $bulletin->update([
                'statut' => 'validé',
                'valide_par' => Auth::id(),
                'date_validation' => now(),
            ]);
            
            Notification::make()
                ->title('Bulletin généré et validé')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Bulletin généré')
                ->success()
                ->send();
        }
        
        return redirect()->route('filament.admin.resources.paie.bulletin-paies.view', ['record' => $bulletin->id]);
    } catch (\Exception $e) {
        Notification::make()
            ->title('Erreur lors de la génération du bulletin')
            ->body($e->getMessage())
            ->danger()
            ->send();
    }
}
```

#### 5.2 Génération en masse

```php
// Dans GenerateBulletinPaieMasse.php

public function generateMasse()
{
    $data = $this->form->getState();
    
    try {
        $generationService = app(GenerationBulletinService::class);
        $bulletins = $generationService->genererBulletinsMasse($data);
        
        if ($data['valider_directement'] ?? false) {
            foreach ($bulletins as $bulletin) {
                $bulletin->update([
                    'statut' => 'validé',
                    'valide_par' => Auth::id(),
                    'date_validation' => now(),
                ]);
            }
            
            Notification::make()
                ->title(count($bulletins) . ' bulletins générés et validés')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(count($bulletins) . ' bulletins générés')
                ->success()
                ->send();
        }
        
        return redirect()->route('filament.admin.resources.paie.bulletin-paies.index');
    } catch (\Exception $e) {
        Notification::make()
            ->title('Erreur lors de la génération des bulletins')
            ->body($e->getMessage())
            ->danger()
            ->send();
    }
}
```

### 6. Enregistrement des routes

```php
// Dans app/Filament/Resources/Paie/BulletinPaieResource.php

public static function getPages(): array
{
    return [
        'index' => Pages\ListBulletinPaies::route('/'),
        'create' => Pages\CreateBulletinPaie::route('/create'),
        'edit' => Pages\EditBulletinPaie::route('/{record}/edit'),
        'view' => Pages\ViewBulletinPaie::route('/{record}'),
        'generate' => Pages\GenerateBulletinPaie::route('/generate'),
        'generate-masse' => Pages\GenerateBulletinPaieMasse::route('/generate-masse'),
    ];
}
```

### 7. Ajout des boutons dans la liste des bulletins

```php
// Dans app/Filament/Resources/Paie/BulletinPaieResource/Pages/ListBulletinPaies.php

protected function getHeaderActions(): array
{
    return [
        Actions\CreateAction::make()
            ->label('Nouveau bulletin'),
            
        Action::make('generer_bulletin')
            ->label('Générer un bulletin')
            ->icon('heroicon-o-document-plus')
            ->url(fn (): string => BulletinPaieResource::getUrl('generate')),
            
        Action::make('generer_bulletins_masse')
            ->label('Génération en masse')
            ->icon('heroicon-o-document-duplicate')
            ->url(fn (): string => BulletinPaieResource::getUrl('generate-masse')),
    ];
}
```

## Tests et validation

### Tests unitaires

Créer des tests unitaires pour le service de génération :

```php
// tests/Unit/Services/Paie/GenerationBulletinServiceTest.php

namespace Tests\Unit\Services\Paie;

use App\Services\Paie\GenerationBulletinService;
use App\Interfaces\Paie\CalculPaieServiceInterface;
use App\Models\Employeur;
use App\Models\Paie\BulletinPaie;
use App\Models\Paie\ConfigurationPaie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerationBulletinServiceTest extends TestCase
{
    use RefreshDatabase;
    
    // Tests pour la génération individuelle
    
    // Tests pour la génération en masse
    
    // Tests pour la vérification de l'existence d'un bulletin
}
```

### Tests fonctionnels

Créer des tests fonctionnels pour les pages de génération :

```php
// tests/Feature/Filament/BulletinPaieGenerationTest.php

namespace Tests\Feature\Filament;

use App\Models\User;
use App\Models\Employeur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulletinPaieGenerationTest extends TestCase
{
    use RefreshDatabase;
    
    // Tests pour la page de génération individuelle
    
    // Tests pour la page de génération en masse
}
```

## Déploiement et documentation

1. Mettre à jour la documentation du module Paie pour inclure les nouvelles fonctionnalités
2. Ajouter des captures d'écran du wizard dans la documentation
3. Mettre à jour le changelog pour mentionner les nouvelles fonctionnalités
4. Déployer les modifications

## Conclusion

Cette implémentation permettra aux utilisateurs de générer des bulletins de paie de manière plus intuitive et efficace, à la fois individuellement et en masse. L'approche par wizard guidera les utilisateurs à travers les différentes étapes du processus, tout en offrant la flexibilité nécessaire pour personnaliser les bulletins selon les besoins spécifiques de l'entreprise.
