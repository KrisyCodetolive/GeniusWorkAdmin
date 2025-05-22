<?php

namespace App\Filament\Actions;

use App\Models\Conge;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\SoldeConge;
use App\Models\TypeConge;
use App\Models\User;
use App\Traits\HasEntrepriseScope;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateDemandesCongesAction extends Action
{
    use HasEntrepriseScope;
    public static function getDefaultName(): ?string
    {
        return 'genererDemandesConges';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Générer des demandes de congés')
            ->icon('heroicon-o-calendar')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Générer des demandes de congés')
            ->modalDescription('Cette action va créer des demandes de congés aléatoires pour les employés actifs de votre entreprise.')
            ->form(function () {
                $components = [
                Forms\Components\Select::make('nombre_demandes')
                    ->label('Nombre de demandes par employé')
                    ->options([
                        1 => '1 demande',
                        2 => '2 demandes',
                        3 => '3 demandes',
                    ])
                    ->default(2)
                    ->required(),
                Forms\Components\Select::make('statut')
                    ->label('Statut des demandes')
                    ->options([
                        'tous' => 'Tous les statuts (aléatoire)',
                        'en_attente' => 'En attente',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                    ])
                    ->default('tous')
                    ->required(),
                Forms\Components\DatePicker::make('date_debut_min')
                    ->label('Date de début minimale')
                    ->default(Carbon::now()->subMonths(1))
                    ->required(),
                Forms\Components\DatePicker::make('date_debut_max')
                    ->label('Date de début maximale')
                    ->default(Carbon::now()->addMonths(2))
                    ->required(),
                Forms\Components\Toggle::make('utiliser_soldes')
                    ->label('Respecter les soldes disponibles')
                    ->helperText('Si activé, les demandes seront créées en fonction des soldes disponibles.')
                    ->default(true),
                ];
                
                // Ajouter un sélecteur d'entreprise pour les super admin et support
                $user = auth()->user();
                if ($user->isSuperAdmin() || $user->isSupport()) {
                    array_unshift($components, 
                        Forms\Components\Select::make('entreprise_id')
                            ->label('Entreprise')
                            ->options(Entreprise::pluck('nom', 'id'))
                            ->searchable()
                            ->required()
                    );
                }
                
                return $components;
            })
            ->modalSubmitActionLabel('Générer')
            ->action(function (array $data): void {
                $user = auth()->user();
                
                // Déterminer l'entreprise à utiliser
                $entrepriseId = $user->entreprise_id;
                
                // Si l'utilisateur est SuperAdmin ou Support, utiliser l'entreprise sélectionnée
                if (($user->isSuperAdmin() || $user->isSupport()) && isset($data['entreprise_id'])) {
                    $entrepriseId = $data['entreprise_id'];
                }
                
                if (!$entrepriseId) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Vous devez sélectionner une entreprise pour générer des demandes de congés.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Récupérer les employés actifs de l'entreprise
                $employes = Employeur::where('entreprise_id', $entrepriseId)
                    ->where('statut', 'actif')
                    ->get();
                
                // Journaliser l'opération
                Log::info('Génération de demandes de congés', [
                    'user_id' => $user->id,
                    'entreprise_id' => $entrepriseId,
                    'nombre_employes' => $employes->count(),
                    'nombre_demandes' => $data['nombre_demandes'],
                    'statut' => $data['statut'],
                    'utiliser_soldes' => $data['utiliser_soldes']
                ]);
                
                if ($employes->isEmpty()) {
                    Notification::make()
                        ->title('Aucun employé')
                        ->body('Aucun employé actif n\'a été trouvé pour votre entreprise.')
                        ->warning()
                        ->send();
                    return;
                }
                
                // Récupérer les types de congés
                $typesConges = TypeConge::all();
                
                if ($typesConges->isEmpty()) {
                    Notification::make()
                        ->title('Aucun type de congé')
                        ->body('Aucun type de congé n\'a été trouvé. Veuillez d\'abord créer des types de congés.')
                        ->warning()
                        ->send();
                    return;
                }
                
                // Récupérer un validateur (admin ou super admin)
                $validateur = User::where('entreprise_id', $entrepriseId)
                    ->whereIn('role', ['admin', 'super_admin'])
                    ->first();
                
                if (!$validateur) {
                    $validateur = $user; // Utiliser l'utilisateur courant si aucun admin n'est trouvé
                }
                
                // Commencer une transaction
                DB::beginTransaction();
                
                try {
                    $nombreDemandes = $data['nombre_demandes'];
                    $statut = $data['statut'];
                    $dateDebutMin = Carbon::parse($data['date_debut_min']);
                    $dateDebutMax = Carbon::parse($data['date_debut_max']);
                    $utiliserSoldes = $data['utiliser_soldes'];
                    
                    $created = 0;
                    $skipped = 0;
                    
                    // Pour chaque employé, créer des demandes de congés
                    foreach ($employes as $employe) {
                        // Récupérer les soldes de congés de l'employé
                        $soldes = [];
                        if ($utiliserSoldes) {
                            $soldes = SoldeConge::where('employeur_id', $employe->id)
                                ->where('annee', Carbon::now()->year)
                                ->get()
                                ->keyBy('type_conge_id');
                        }
                        
                        for ($i = 0; $i < $nombreDemandes; $i++) {
                            // Sélectionner un type de congé aléatoire
                            $typeConge = $typesConges->random();
                            
                            // Vérifier si l'employé a un solde suffisant pour ce type de congé
                            if ($utiliserSoldes && isset($soldes[$typeConge->id])) {
                                $solde = $soldes[$typeConge->id];
                                if ($solde->solde_restant <= 0) {
                                    $skipped++;
                                    continue;
                                }
                            }
                            
                            // Générer des dates aléatoires
                            $dateDebut = Carbon::parse($dateDebutMin)->addDays(rand(0, $dateDebutMax->diffInDays($dateDebutMin)));
                            
                            // Durée aléatoire entre 1 et 5 jours (ou moins si solde insuffisant)
                            $dureeMax = 5;
                            if ($utiliserSoldes && isset($soldes[$typeConge->id])) {
                                $dureeMax = min(5, $soldes[$typeConge->id]->solde_restant);
                            }
                            $duree = rand(1, max(1, $dureeMax));
                            
                            // Calculer la date de fin
                            $dateFin = (clone $dateDebut)->addDays($duree - 1);
                            
                            // Déterminer le statut de la demande
                            $demandeStatut = $statut;
                            if ($statut === 'tous') {
                                $statuts = ['en_attente', 'approuve', 'rejete'];
                                $demandeStatut = $statuts[array_rand($statuts)];
                            }
                            
                            // Créer la demande de congé
                            $conge = Conge::create([
                                'employeur_id' => $employe->id,
                                'type_conge_id' => $typeConge->id,
                                'date_debut' => $dateDebut,
                                'date_fin' => $dateFin,
                                'duree_jours' => $duree,
                                'motif' => 'Demande générée automatiquement',
                                'statut' => $demandeStatut,
                                'est_paye' => $typeConge->est_paye,
                                'meta_donnees' => [
                                    'genere_automatiquement' => true,
                                    'date_generation' => Carbon::now()->toDateTimeString(),
                                ],
                            ]);
                            
                            // Si la demande est approuvée ou rejetée, ajouter les informations de validation
                            if ($demandeStatut === 'approuve' || $demandeStatut === 'rejete') {
                                $conge->update([
                                    'validateur_id' => $validateur->id,
                                    'date_validation' => Carbon::now(),
                                    'commentaire_validation' => 'Validation automatique',
                                ]);
                                
                                // Si la demande est approuvée, mettre à jour le solde de congés
                                if ($demandeStatut === 'approuve' && $utiliserSoldes && isset($soldes[$typeConge->id])) {
                                    $solde = $soldes[$typeConge->id];
                                    $solde->solde_pris += $duree;
                                    $solde->solde_restant -= $duree;
                                    $solde->date_derniere_maj = Carbon::now();
                                    $solde->save();
                                }
                            }
                            
                            $created++;
                        }
                    }
                    
                    DB::commit();
                    
                    // Créer le message de notification
                    $message = "Opération terminée avec succès. ";
                    if ($created > 0) {
                        $message .= "{$created} demandes de congés créées. ";
                    }
                    if ($skipped > 0) {
                        $message .= "{$skipped} demandes ignorées (solde insuffisant).";
                    }
                    
                    Notification::make()
                        ->title('Demandes de congés générées')
                        ->body($message)
                        ->success()
                        ->send();
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    
                    Notification::make()
                        ->title('Erreur')
                        ->body('Une erreur est survenue lors de la génération des demandes de congés : ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function make(?string $name = null): static
    {
        $action = parent::make($name);
        
        // Vérifier si l'utilisateur a le droit d'utiliser cette action
        $user = auth()->user();
        
        // Les super admin et support peuvent toujours utiliser cette action
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return $action;
        }
        
        // Pour les autres, il faut être admin et avoir une entreprise
        if (!$user || !$user->entreprise_id || !$user->isAdmin()) {
            $action->hidden();
        }
        
        return $action;
    }
}
