<?php

namespace App\Filament\Actions;

use App\Models\Entreprise;
use App\Models\Equipe;
use App\Models\Employeur;
use App\Models\PlageHoraire;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GenerateEquipesExemplesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'genererEquipesExemples';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Générer des équipes exemples')
            ->icon('heroicon-o-user-group')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Générer des équipes exemples')
            ->modalDescription('Cette action va créer des équipes exemples pour votre entreprise avec des membres et des plages horaires. Vous pourrez les modifier ou les supprimer par la suite.')
            ->modalSubmitActionLabel('Générer')
            ->action(function (): void {
                $user = auth()->user();
                $entreprise = $user->entreprise;
                
                if (!$entreprise) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Vous devez être associé à une entreprise pour générer des exemples d\'équipes.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Vérifier si l'entreprise a un abonnement actif
                if (!$entreprise->abonnementActif) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Votre entreprise n\'a pas d\'abonnement actif. Veuillez souscrire à un abonnement avant de générer des équipes exemples.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Générer les exemples d'équipes
                $result = self::createForEntreprise($entreprise);
                
                // Déterminer le type de notification en fonction du résultat
                $notificationType = count($result['created']) > 0 ? 'success' : 'warning';
                
                // Créer la notification avec le message du résultat
                $notification = Notification::make()
                    ->title(count($result['created']) > 0 ? 'Équipes exemples générées' : 'Information')
                    ->body($result['message'])
                    ->$notificationType();
                
                $notification->send();
            });
    }

    public static function make(?string $name = null): static
    {
        $action = parent::make($name);
        
        // Vérifier si l'utilisateur a le droit d'utiliser cette action
        $user = auth()->user();
        if (!$user || !$user->entreprise_id || $user->isSuperAdmin() || $user->isSupport()) {
            $action->hidden();
        }
        
        return $action;
    }
    
    /**
     * Crée des équipes exemples pour une entreprise
     */
    public static function createForEntreprise(Entreprise $entreprise): array
    {
        $result = [
            'created' => [],
            'existants' => 0,
            'message' => ''
        ];
        
        // Vérifier s'il y a des employés et des plages horaires
        $employeursCount = Employeur::where('entreprise_id', $entreprise->id)->count();
        $plagesHorairesCount = PlageHoraire::where('entreprise_id', $entreprise->id)->count();
        
        if ($employeursCount < 5) {
            $result['message'] = 'Vous devez avoir au moins 5 employés pour générer des équipes exemples.';
            return $result;
        }
        
        if ($plagesHorairesCount < 2) {
            $result['message'] = 'Vous devez avoir au moins 2 plages horaires pour générer des équipes exemples.';
            return $result;
        }
        
        // Récupérer les employés et les plages horaires
        $employeurs = Employeur::where('entreprise_id', $entreprise->id)->get();
        $plagesHoraires = PlageHoraire::where('entreprise_id', $entreprise->id)->get();
        
        // Exemples d'équipes à créer
        $equipesExemples = [
            [
                'nom' => 'Équipe de jour',
                'description' => 'Personnel travaillant principalement en journée',
            ],
            [
                'nom' => 'Équipe de nuit',
                'description' => 'Personnel travaillant principalement la nuit',
            ],
            [
                'nom' => 'Équipe de week-end',
                'description' => 'Personnel travaillant les week-ends',
            ],
        ];
        
        // Créer les équipes
        foreach ($equipesExemples as $equipeData) {
            // Vérifier si l'équipe existe déjà
            $equipeExistante = Equipe::where('entreprise_id', $entreprise->id)
                ->where('nom', $equipeData['nom'])
                ->first();
                
            if ($equipeExistante) {
                $result['existants']++;
                continue;
            }
            
            // Filtrer les employés qui ne sont pas déjà dans une équipe active
            // Utiliser une approche différente pour éviter les problèmes avec la relation
            $employeursDejaEnEquipe = \App\Models\Equipe::where('entreprise_id', $entreprise->id)
                ->with(['membres' => function($query) {
                    $query->where('equipe_employeur.est_actif', true);
                }])
                ->get()
                ->pluck('membres')
                ->flatten()
                ->pluck('id')
                ->toArray();
                
            $employeursDisponibles = $employeurs->filter(function ($employeur) use ($employeursDejaEnEquipe) {
                return !in_array($employeur->id, $employeursDejaEnEquipe);
            });
            
            // Choisir un responsable parmi les employés disponibles si possible, sinon parmi tous les employés
            $responsable = $employeursDisponibles->count() > 0 
                ? $employeursDisponibles->random() 
                : $employeurs->random();
            
            // Créer l'équipe
            $equipe = Equipe::create([
                'entreprise_id' => $entreprise->id,
                'nom' => $equipeData['nom'],
                'description' => $equipeData['description'],
                'responsable_id' => $responsable->id,
                'statut' => 'actif',
            ]);
            
            // On a déjà filtré les employés disponibles avant la création de l'équipe
            
            // S'il n'y a pas assez d'employés disponibles, prendre ce qui est disponible
            $membresCount = rand(3, min(5, $employeursDisponibles->count()));
            
            // Si aucun employé n'est disponible, en prendre au moins un pour l'exemple
            if ($membresCount === 0 && $employeurs->count() > 0) {
                $membresCount = 1;
                $employeursDisponibles = $employeurs;
            }
            
            // Ajouter d'abord le responsable comme membre de l'équipe
            // Désactiver le responsable dans toutes les autres équipes s'il y en a
            $autresEquipesResponsable = \App\Models\Equipe::whereHas('membres', function($query) use ($responsable) {
                $query->where('employeur_id', $responsable->id);
            })->get();
            
            foreach ($autresEquipesResponsable as $autreEquipe) {
                $autreEquipe->membres()->updateExistingPivot($responsable->id, ['est_actif' => false]);
            }
            
            // Ajouter le responsable à l'équipe
            $equipe->membres()->attach($responsable->id, [
                'est_actif' => true,
                'date_debut' => Carbon::today(),
                'date_fin' => null,
            ]);
            
            // Retirer le responsable des employés disponibles pour éviter de l'ajouter deux fois
            $employeursDisponibles = $employeursDisponibles->reject(function ($employeur) use ($responsable) {
                return $employeur->id === $responsable->id;
            });
            
            // Sélectionner des membres aléatoires parmi les employés disponibles restants
            $membresCount = rand(2, min(4, $employeursDisponibles->count())); // Réduit de 1 car le responsable est déjà membre
            $membres = $employeursDisponibles->count() > 0 ? $employeursDisponibles->random($membresCount) : collect();
            
            foreach ($membres as $membre) {
                // Désactiver l'employé dans toutes les autres équipes s'il y en a
                // Récupérer toutes les équipes où l'employé est membre
                $autresEquipes = \App\Models\Equipe::whereHas('membres', function($query) use ($membre) {
                    $query->where('employeur_id', $membre->id);
                })->get();
                
                // Désactiver l'employé dans ces équipes
                foreach ($autresEquipes as $autreEquipe) {
                    $autreEquipe->membres()->updateExistingPivot($membre->id, ['est_actif' => false]);
                }
                
                // Ajouter l'employé à la nouvelle équipe
                $equipe->membres()->attach($membre->id, [
                    'est_actif' => true,
                    'date_debut' => Carbon::today(),
                    'date_fin' => null,
                ]);
            }
            
            // Ajouter une plage horaire active à l'équipe
            $plage = $plagesHoraires->random();
            $equipe->plagesHoraires()->attach($plage->id, [
                'est_actif' => true,
            ]);
            
            // Ajouter éventuellement d'autres plages horaires inactives
            $autresPlages = $plagesHoraires->where('id', '!=', $plage->id)->take(1);
            foreach ($autresPlages as $autrePlage) {
                $equipe->plagesHoraires()->attach($autrePlage->id, [
                    'est_actif' => false,
                ]);
            }
            
            // Synchroniser les horaires pour tous les membres
            $equipe->synchroniserHoraires();
            
            $result['created'][] = $equipe->nom;
        }
        
        // Préparer le message de retour
        if (count($result['created']) > 0) {
            $result['message'] = count($result['created']) . ' équipes exemples ont été créées pour votre entreprise : ' . 
                                implode(', ', $result['created']) . '.';
            
            if ($result['existants'] > 0) {
                $result['message'] .= ' ' . $result['existants'] . ' équipes existaient déjà.';
            }
        } else {
            $result['message'] = 'Aucune nouvelle équipe n\'a été créée. ' . $result['existants'] . ' équipes existaient déjà.';
        }
        
        return $result;
    }
}
