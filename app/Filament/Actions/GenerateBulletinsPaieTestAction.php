<?php

namespace App\Filament\Actions;

use App\Models\Employeur;
use App\Models\Paie\BulletinPaie;
use App\Models\Paie\ElementPaie;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateBulletinsPaieTestAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'genererBulletinsPaieTest';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Générer des bulletins test')
            ->icon('heroicon-o-document-plus')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Générer des bulletins de paie test')
            ->modalDescription('Cette action va créer des bulletins de paie test pour les employés de votre entreprise. Ces bulletins seront en statut brouillon et pourront être modifiés ou supprimés.')
            ->form([
                Forms\Components\Select::make('nombre_bulletins')
                    ->label('Nombre de bulletins à générer')
                    ->options([
                        5 => '5 bulletins',
                        10 => '10 bulletins',
                        20 => '20 bulletins',
                    ])
                    ->default(5)
                    ->required(),
                
                Forms\Components\DatePicker::make('periode_debut')
                    ->label('Début de période')
                    ->default(fn () => Carbon::now()->startOfMonth())
                    ->required(),
                
                Forms\Components\DatePicker::make('periode_fin')
                    ->label('Fin de période')
                    ->default(fn () => Carbon::now()->endOfMonth())
                    ->required(),
                
                Forms\Components\DatePicker::make('date_paiement')
                    ->label('Date de paiement')
                    ->default(fn () => Carbon::now()->addDays(5))
                    ->required(),
            ])
            ->modalSubmitActionLabel('Générer')
            ->action(function (array $data): void {
                $user = auth()->user();
                $entreprise = $user->entreprise;
                
                if (!$entreprise) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Vous devez être associé à une entreprise pour générer des bulletins de paie test.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Récupérer les employés de l'entreprise
                $employeurs = Employeur::where('entreprise_id', $entreprise->id)
                    ->where('statut', 'actif')
                    ->limit($data['nombre_bulletins'])
                    ->get();
                
                if ($employeurs->isEmpty()) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Aucun employé actif trouvé pour votre entreprise. Veuillez d\'abord créer des employés.')
                        ->warning()
                        ->send();
                    return;
                }
                
                $bulletinsCreated = 0;
                $errors = [];
                
                DB::beginTransaction();
                
                try {
                    foreach ($employeurs as $employeur) {
                        // Générer des montants aléatoires réalistes
                        $salaireBase = rand(150000, 1000000);
                        $totalIndemnites = rand(10000, 100000);
                        $totalPrimes = rand(5000, 50000);
                        $salaireBrut = $salaireBase + $totalIndemnites + $totalPrimes;
                        
                        // Calculer les retenues
                        $cnpsEmploye = round($salaireBrut * 0.0415, 2); // 4.15% pour CNPS employé
                        $igr = round($salaireBrut * 0.018, 2); // 1.8% pour IGR (simplifié)
                        $totalRetenues = $cnpsEmploye + $igr;
                        
                        // Calculer le net et les charges patronales
                        $salaireNet = $salaireBrut - $totalRetenues;
                        $cnpsEmployeur = round($salaireBrut * 0.0725, 2); // 7.25% pour CNPS employeur
                        $chargesPatronales = $cnpsEmployeur;
                        
                        // Générer une référence unique avec un suffixe aléatoire pour éviter les doublons
                        $dateRef = Carbon::parse($data['periode_fin'])->format('Ym');
                        $matriculePrefix = substr($employeur->matricule, 0, 2);
                        $prefix = strtoupper($matriculePrefix ?: 'BP');
                        $randomSuffix = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        $reference = $prefix . '-' . $dateRef . '-' . $randomSuffix;
                        
                        // S'assurer que la référence est unique
                        while (BulletinPaie::where('reference', $reference)->exists()) {
                            $randomSuffix = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                            $reference = $prefix . '-' . $dateRef . '-' . $randomSuffix;
                        }
                        
                        // Créer le bulletin avec la référence unique
                        $bulletin = BulletinPaie::create([
                            'reference' => $reference,
                            'employeur_id' => $employeur->id,
                            'entreprise_id' => $entreprise->id,
                            'periode_debut' => $data['periode_debut'],
                            'periode_fin' => $data['periode_fin'],
                            'date_paiement' => $data['date_paiement'],
                            'salaire_base' => $salaireBase,
                            'total_indemnites' => $totalIndemnites,
                            'total_primes' => $totalPrimes,
                            'salaire_brut' => $salaireBrut,
                            'cnps_employe' => $cnpsEmploye,
                            'igr' => $igr,
                            'total_retenues' => $totalRetenues,
                            'salaire_net' => $salaireNet,
                            'cnps_employeur' => $cnpsEmployeur,
                            'charges_patronales' => $chargesPatronales,
                            'statut' => 'brouillon',
                            'genere_par' => $user->id,
                            'commentaire' => 'Bulletin test généré automatiquement',
                        ]);
                        
                        // Créer les éléments de paie
                        $this->createElementsPaie($bulletin);
                        
                        $bulletinsCreated++;
                    }
                    
                    DB::commit();
                    
                    Notification::make()
                        ->title('Bulletins de paie test générés')
                        ->body("$bulletinsCreated bulletins de paie test ont été générés avec succès.")
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    DB::rollBack();
                    
                    Notification::make()
                        ->title('Erreur')
                        ->body('Une erreur est survenue lors de la génération des bulletins de paie test: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
    
    /**
     * Crée les éléments de paie pour un bulletin
     */
    private function createElementsPaie(BulletinPaie $bulletin): void
    {
        // Créer les indemnités
        $indemnites = [
            ['code' => 'IND_TRANS', 'libelle' => 'Indemnité de transport', 'montant' => rand(25000, 50000), 'categorie' => ElementPaie::CATEGORIE_INDEMNITE_TRANSPORT],
            ['code' => 'IND_LOG', 'libelle' => 'Indemnité de logement', 'montant' => rand(50000, 100000), 'categorie' => ElementPaie::CATEGORIE_INDEMNITE_LOGEMENT],
        ];
        
        foreach ($indemnites as $indemnite) {
            ElementPaie::create([
                'bulletin_paie_id' => $bulletin->id,
                'code' => $indemnite['code'],
                'type' => ElementPaie::TYPE_INDEMNITE,
                'categorie' => $indemnite['categorie'],
                'libelle' => $indemnite['libelle'],
                'montant' => $indemnite['montant'],
                'imposable' => true,
                'ordre' => 10,
            ]);
        }
        
        // Créer les primes
        $primes = [
            ['code' => 'PRIME_ANC', 'libelle' => 'Prime d\'ancienneté', 'montant' => rand(10000, 30000), 'categorie' => ElementPaie::CATEGORIE_PRIME_ANCIENNETE],
            ['code' => 'PRIME_REND', 'libelle' => 'Prime de rendement', 'montant' => rand(15000, 40000), 'categorie' => ElementPaie::CATEGORIE_PRIME_RENDEMENT],
        ];
        
        foreach ($primes as $prime) {
            ElementPaie::create([
                'bulletin_paie_id' => $bulletin->id,
                'code' => $prime['code'],
                'type' => ElementPaie::TYPE_PRIME,
                'categorie' => $prime['categorie'],
                'libelle' => $prime['libelle'],
                'montant' => $prime['montant'],
                'imposable' => true,
                'ordre' => 20,
            ]);
        }
        
        // Créer les retenues
        $retenues = [
            ['code' => 'CNPS_EMP', 'libelle' => 'CNPS (part employé)', 'montant' => $bulletin->cnps_employe, 'categorie' => ElementPaie::CATEGORIE_CNPS_EMPLOYE],
            ['code' => 'IGR', 'libelle' => 'IGR', 'montant' => $bulletin->igr, 'categorie' => ElementPaie::CATEGORIE_IGR],
        ];
        
        foreach ($retenues as $retenue) {
            ElementPaie::create([
                'bulletin_paie_id' => $bulletin->id,
                'code' => $retenue['code'],
                'type' => ElementPaie::TYPE_RETENUE_SALARIALE,
                'categorie' => $retenue['categorie'],
                'libelle' => $retenue['libelle'],
                'montant' => $retenue['montant'],
                'imposable' => false,
                'ordre' => 30,
            ]);
        }
        
        // Créer les charges patronales
        $charges = [
            ['code' => 'CNPS_PAT', 'libelle' => 'CNPS (part employeur)', 'montant' => $bulletin->cnps_employeur, 'categorie' => ElementPaie::CATEGORIE_CNPS_EMPLOYEUR],
        ];
        
        foreach ($charges as $charge) {
            ElementPaie::create([
                'bulletin_paie_id' => $bulletin->id,
                'code' => $charge['code'],
                'type' => ElementPaie::TYPE_CHARGE_PATRONALE,
                'categorie' => $charge['categorie'],
                'libelle' => $charge['libelle'],
                'montant' => $charge['montant'],
                'imposable' => false,
                'ordre' => 40,
            ]);
        }
    }

    public static function make(?string $name = null): static
    {
        $action = parent::make($name);
        
        // Vérifier si l'utilisateur a le droit d'utiliser cette action
        $user = auth()->user();
        if (!$user || !$user->entreprise_id || (!$user->isAdmin() && !$user->isSuperAdmin() && !$user->isSupport())) {
            $action->hidden();
        }
        
        return $action;
    }
}
