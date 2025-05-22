<?php

namespace App\Filament\Actions;

use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\SoldeConge;
use App\Models\TypeConge;
use App\Traits\HasEntrepriseScope;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class GenerateSoldesCongesAction extends Action
{
    use HasEntrepriseScope;
    public static function getDefaultName(): ?string
    {
        return 'genererSoldesConges';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Générer des soldes de congés')
            ->icon('heroicon-o-calculator')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Générer des soldes de congés')
            ->modalDescription('Cette action va créer des soldes de congés pour tous les employés actifs de votre entreprise.')
            ->form(function () {
                $components = [
                    Forms\Components\Select::make('annee')
                        ->label('Année')
                        ->options(function () {
                            $anneeActuelle = Carbon::now()->year;
                            return [
                                $anneeActuelle - 1 => (string)($anneeActuelle - 1),
                                $anneeActuelle => (string)$anneeActuelle,
                                $anneeActuelle + 1 => (string)($anneeActuelle + 1),
                            ];
                        })
                        ->default(Carbon::now()->year)
                        ->required(),
                    Forms\Components\TextInput::make('solde_initial')
                        ->label('Solde initial (jours)')
                        ->numeric()
                        ->default(30)
                        ->required(),
                    Forms\Components\Toggle::make('reinitialiser')
                        ->label('Réinitialiser les soldes existants')
                        ->helperText('Si activé, les soldes existants pour l\'année sélectionnée seront supprimés et recréés.')
                        ->default(false),
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
                        ->body('Vous devez sélectionner une entreprise pour générer des soldes de congés.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Récupérer les employés actifs de l'entreprise
                $employes = Employeur::where('entreprise_id', $entrepriseId)
                    ->where('statut', 'actif')
                    ->get();
                
                // Journaliser l'opération
                \Illuminate\Support\Facades\Log::info('Génération de soldes de congés', [
                    'user_id' => $user->id,
                    'entreprise_id' => $entrepriseId,
                    'nombre_employes' => $employes->count(),
                    'annee' => $data['annee'],
                    'solde_initial' => $data['solde_initial']
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
                    // Créer un type de congé par défaut si aucun n'existe
                    $typeConge = TypeConge::create([
                        'nom' => 'Congé payé',
                        'code' => 'CP',
                        'description' => 'Congé payé standard',
                        'couleur' => '#4CAF50',
                        'est_paye' => true,
                        'duree_max' => 30,
                    ]);
                    $typesConges = collect([$typeConge]);
                }
                
                // Commencer une transaction
                DB::beginTransaction();
                
                try {
                    $annee = $data['annee'];
                    $soldeInitial = $data['solde_initial'];
                    $reinitialiser = $data['reinitialiser'];
                    
                    $created = 0;
                    $updated = 0;
                    $skipped = 0;
                    
                    // Si réinitialisation demandée, supprimer les soldes existants
                    if ($reinitialiser) {
                        // Récupérer les IDs des employés de l'entreprise
                        $employeIds = $employes->pluck('id')->toArray();
                        
                        // Supprimer les soldes existants pour ces employés et l'année sélectionnée
                        SoldeConge::whereIn('employeur_id', $employeIds)
                            ->where('annee', $annee)
                            ->delete();
                    }
                    
                    // Pour chaque employé, créer ou mettre à jour les soldes de congés
                    foreach ($employes as $employe) {
                        foreach ($typesConges as $typeConge) {
                            // Vérifier si un solde existe déjà
                            $solde = SoldeConge::where('employeur_id', $employe->id)
                                ->where('type_conge_id', $typeConge->id)
                                ->where('annee', $annee)
                                ->first();
                            
                            if ($solde && !$reinitialiser) {
                                // Le solde existe déjà et on ne veut pas le réinitialiser
                                $skipped++;
                                continue;
                            }
                            
                            if (!$solde) {
                                // Créer un nouveau solde
                                SoldeConge::create([
                                    'employeur_id' => $employe->id,
                                    'type_conge_id' => $typeConge->id,
                                    'annee' => $annee,
                                    'solde_initial' => $soldeInitial,
                                    'solde_acquis' => $soldeInitial,
                                    'solde_pris' => 0,
                                    'solde_restant' => $soldeInitial,
                                    'date_derniere_maj' => Carbon::now(),
                                    'commentaire' => 'Solde généré automatiquement',
                                ]);
                                $created++;
                            } else {
                                // Mettre à jour le solde existant
                                $solde->update([
                                    'solde_initial' => $soldeInitial,
                                    'solde_acquis' => $soldeInitial,
                                    'solde_pris' => 0,
                                    'solde_restant' => $soldeInitial,
                                    'date_derniere_maj' => Carbon::now(),
                                    'commentaire' => $solde->commentaire . "\nSolde réinitialisé automatiquement",
                                ]);
                                $updated++;
                            }
                        }
                    }
                    
                    DB::commit();
                    
                    // Créer le message de notification
                    $message = "Opération terminée avec succès. ";
                    if ($created > 0) {
                        $message .= "{$created} soldes créés. ";
                    }
                    if ($updated > 0) {
                        $message .= "{$updated} soldes mis à jour. ";
                    }
                    if ($skipped > 0) {
                        $message .= "{$skipped} soldes existants ignorés.";
                    }
                    
                    Notification::make()
                        ->title('Soldes de congés générés')
                        ->body($message)
                        ->success()
                        ->send();
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    
                    Notification::make()
                        ->title('Erreur')
                        ->body('Une erreur est survenue lors de la génération des soldes de congés : ' . $e->getMessage())
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
