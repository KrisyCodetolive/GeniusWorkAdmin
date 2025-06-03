<?php

namespace App\Services;

use App\Models\Conge;
use App\Models\TypeConge;
use App\Models\SoldeConge;
use App\Models\User;
use App\Notifications\CongeStatusNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\CongePdfService;
use Illuminate\Support\Facades\Log;

class CongeService
{
    /**
     * Créer une nouvelle demande de congé
     *
     * @param array $data Les données de la demande
     * @param User $user L'utilisateur qui fait la demande
     * @return Conge
     */
    public function creerDemande(array $data, User $user)
    {
        // Vérifier si l'utilisateur a un employeur associé
        if (!$user->employeur) {
            throw new \Exception("L'utilisateur n'a pas d'employeur associé");
        }

        $typeConge = TypeConge::findOrFail($data['type_conge_id']);
        
        // Vérifier si le type de congé est actif
        if (!$typeConge->isActif()) {
            throw new \Exception("Ce type de congé n'est pas disponible actuellement");
        }
        
        // Calculer la durée en jours
        $dateDebut = Carbon::parse($data['date_debut']);
        $dateFin = Carbon::parse($data['date_fin']);
        $dureeJours = $this->calculerJoursOuvrables($dateDebut, $dateFin);
        
        // Vérifier le délai de demande préalable
        $delaiDemande = $typeConge->getDelaiDemande();
        if ($delaiDemande > 0 && $dateDebut->diffInDays(Carbon::now()) < $delaiDemande) {
            throw new \Exception("La demande doit être faite au moins {$delaiDemande} jours à l'avance");
        }
        
        // Vérifier s'il y a chevauchement avec d'autres congés
        if ($this->aChevauchementConges($user->employeur->id, $dateDebut, $dateFin)) {
            throw new \Exception("Cette période chevauche un congé déjà demandé");
        }
        
        // Vérifier le solde disponible si le congé est déductible
        if ($typeConge->estDeductible()) {
            $annee = $dateDebut->year;
            $soldeConge = $this->getSoldeConge($user->id, $typeConge->id, $annee);
            
            if (!$soldeConge || !$soldeConge->verifierDisponibilite($dureeJours)) {
                throw new \Exception("Solde de congés insuffisant pour cette demande");
            }
        }
        
        // Traiter le justificatif si nécessaire
        $justificatif = null;
        if ($typeConge->necessiteJustificatif() && isset($data['justificatif'])) {
            $justificatif = $this->enregistrerJustificatif($data['justificatif']);
        }
        
        // Créer la demande de congé
        return DB::transaction(function () use ($data, $user, $dureeJours, $justificatif) {
            return Conge::create([
                'employeur_id' => $user->employeur->id,
                'type_conge_id' => $data['type_conge_id'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'duree_jours' => $dureeJours,
                'motif' => $data['motif'] ?? null,
                'justificatif' => $justificatif,
                'statut' => 'en_attente',
                'est_paye' => TypeConge::find($data['type_conge_id'])->est_paye,
                'meta_donnees' => [
                    'ip_demande' => request()->ip(),
                    'date_demande' => now()->toDateTimeString(),
                    'navigateur' => request()->userAgent()
                ]
            ]);
        });
    }
    
    /**
     * Approuver une demande de congé
     *
     * @param Conge $conge La demande de congé
     * @param User $validateur L'utilisateur qui valide
     * @param string|null $commentaire Commentaire de validation
     * @return Conge
     */
    public function approuverDemande(Conge $conge, User $validateur, $commentaire = null)
    {
        if (!$conge->estEnAttente()) {
            throw new \Exception("Cette demande ne peut plus être approuvée");
        }
        
        return DB::transaction(function () use ($conge, $validateur, $commentaire) {
            // Approuver la demande
            $conge->approuver($validateur, $commentaire);
            
            // Mettre à jour le solde si le congé est déductible
            $typeConge = $conge->typeConge;
            if ($typeConge->estDeductible()) {
                $dateDebut = Carbon::parse($conge->date_debut);
                $soldeConge = $this->getSoldeConge($conge->employeur->user_id, $typeConge->id, $dateDebut->year);
                
                if ($soldeConge) {
                    $soldeConge->deduireSolde(
                        $conge->duree_jours,
                        "Congé #{$conge->id} approuvé par {$validateur->name}"
                    );
                }
            }
            
            // Générer le PDF d'attestation
            $pdfPath = null;
            try {
                $pdfService = app(CongePdfService::class);
                $pdfPath = $pdfService->generatePdf($conge);
            } catch (\Exception $e) {
                // Logger l'erreur mais continuer le processus
                Log::error('Erreur lors de la génération du PDF de congé: ' . $e->getMessage());
            }
            
            // Envoyer une notification à l'employé
            try {
                $user = User::find($conge->employeur->user_id);
                if ($user) {
                    $user->notify(new CongeStatusNotification($conge, $pdfPath));
                }
            } catch (\Exception $e) {
                // Logger l'erreur mais continuer le processus
                Log::error('Erreur lors de l\'envoi de la notification de congé: ' . $e->getMessage());
            }
            
            return $conge;
        });
    }
    
    /**
     * Rejeter une demande de congé
     *
     * @param Conge $conge La demande de congé
     * @param User $validateur L'utilisateur qui rejette
     * @param string $commentaire Commentaire de rejet (obligatoire)
     * @return Conge
     */
    public function rejeterDemande(Conge $conge, User $validateur, $commentaire)
    {
        if (!$conge->estEnAttente()) {
            throw new \Exception("Cette demande ne peut plus être rejetée");
        }
        
        if (empty($commentaire)) {
            throw new \Exception("Un commentaire est requis pour rejeter une demande");
        }
        
        $conge = $conge->rejeter($validateur, $commentaire);
        
        // Envoyer une notification à l'employé
        try {
            $user = User::find($conge->employeur->user_id);
            if ($user) {
                $user->notify(new CongeStatusNotification($conge));
            }
        } catch (\Exception $e) {
            // Logger l'erreur mais continuer le processus
            Log::error('Erreur lors de l\'envoi de la notification de congé: ' . $e->getMessage());
        }
        
        return $conge;
    }
    
    /**
     * Annuler une demande de congé
     *
     * @param Conge $conge La demande de congé
     * @param string|null $commentaire Commentaire d'annulation
     * @return Conge
     */
    public function annulerDemande(Conge $conge, $commentaire = null)
    {
        if (!$conge->estEnAttente() && !$conge->estApprouve()) {
            throw new \Exception("Cette demande ne peut plus être annulée");
        }
        
        return DB::transaction(function () use ($conge, $commentaire) {
            // Si le congé était approuvé et déductible, restaurer le solde
            if ($conge->estApprouve() && $conge->typeConge->estDeductible()) {
                $dateDebut = Carbon::parse($conge->date_debut);
                $soldeConge = $this->getSoldeConge($conge->employeur->user_id, $conge->typeConge->id, $dateDebut->year);
                
                if ($soldeConge) {
                    $soldeConge->ajouterSolde(
                        $conge->duree_jours,
                        "Annulation du congé #{$conge->id}"
                    );
                }
            }
            
            $conge = $conge->annuler($commentaire);
            
            // Envoyer une notification à l'employé
            try {
                $user = User::find($conge->employeur->user_id);
                if ($user) {
                    $user->notify(new CongeStatusNotification($conge));
                }
            } catch (\Exception $e) {
                // Logger l'erreur mais continuer le processus
                Log::error('Erreur lors de l\'envoi de la notification de congé: ' . $e->getMessage());
            }
            
            return $conge;
        });
    }
    
    /**
     * Obtenir le solde de congé pour un utilisateur, un type et une année
     *
     * @param int $userId ID de l'utilisateur
     * @param int $typeCongeId ID du type de congé
     * @param int $annee Année concernée
     * @return SoldeConge|null
     */
    public function getSoldeConge($userId, $typeCongeId, $annee)
    {
        return SoldeConge::where('user_id', $userId)
            ->where('type_conge_id', $typeCongeId)
            ->where('annee', $annee)
            ->first();
    }
    
    /**
     * Initialiser ou mettre à jour le solde de congé d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param int $typeCongeId ID du type de congé
     * @param int $annee Année concernée
     * @param float $solde Solde à attribuer
     * @param string|null $commentaire Commentaire sur l'opération
     * @return SoldeConge
     */
    public function initialiserSoldeConge($userId, $typeCongeId, $annee, $solde, $commentaire = null)
    {
        $soldeConge = SoldeConge::firstOrNew([
            'user_id' => $userId,
            'type_conge_id' => $typeCongeId,
            'annee' => $annee
        ]);
        
        if ($soldeConge->exists) {
            return $soldeConge->reinitialiserSolde($solde, $commentaire);
        } else {
            $soldeConge->fill([
                'solde_initial' => $solde,
                'solde_acquis' => $solde,
                'solde_pris' => 0,
                'solde_restant' => $solde,
                'date_derniere_maj' => now(),
                'commentaire' => $commentaire
            ]);
            $soldeConge->save();
            
            return $soldeConge;
        }
    }
    
    /**
     * Ajouter des jours au solde de congé d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param int $typeCongeId ID du type de congé
     * @param int $annee Année concernée
     * @param float $jours Nombre de jours à ajouter
     * @param string|null $commentaire Commentaire sur l'opération
     * @return SoldeConge
     */
    public function ajouterJoursSoldeConge($userId, $typeCongeId, $annee, $jours, $commentaire = null)
    {
        $soldeConge = $this->getSoldeConge($userId, $typeCongeId, $annee);
        
        if (!$soldeConge) {
            return $this->initialiserSoldeConge($userId, $typeCongeId, $annee, $jours, $commentaire);
        }
        
        return $soldeConge->ajouterSolde($jours, $commentaire);
    }
    
    /**
     * Vérifier s'il y a chevauchement avec d'autres congés
     *
     * @param int $employeurId ID de l'employeur
     * @param Carbon $dateDebut Date de début
     * @param Carbon $dateFin Date de fin
     * @return bool
     */
    protected function aChevauchementConges($employeurId, Carbon $dateDebut, Carbon $dateFin)
    {
        return Conge::where('employeur_id', $employeurId)
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                    ->orWhere(function ($q) use ($dateDebut, $dateFin) {
                        $q->where('date_debut', '<=', $dateDebut)
                            ->where('date_fin', '>=', $dateFin);
                    });
            })
            ->whereIn('statut', ['en_attente', 'approuve'])
            ->exists();
    }
    
    /**
     * Calculer le nombre de jours ouvrables entre deux dates
     *
     * @param Carbon $dateDebut Date de début
     * @param Carbon $dateFin Date de fin
     * @return int
     */
    protected function calculerJoursOuvrables(Carbon $dateDebut, Carbon $dateFin)
    {
        $joursOuvrables = 0;
        $date = clone $dateDebut;
        
        while ($date->lte($dateFin)) {
            // Si ce n'est pas un weekend (6 = samedi, 0 = dimanche)
            if (!in_array($date->dayOfWeek, [0, 6])) {
                $joursOuvrables++;
            }
            $date->addDay();
        }
        
        return $joursOuvrables;
    }
    
    /**
     * Enregistrer un justificatif
     *
     * @param mixed $fichier Fichier uploadé
     * @return string Chemin du fichier
     */
    protected function enregistrerJustificatif($fichier)
    {
        $path = $fichier->store('justificatifs/conges', 'public');
        return $path;
    }
    
    /**
     * Obtenir les statistiques de congés pour un utilisateur
     *
     * @param User $user L'utilisateur
     * @param int $annee Année concernée
     * @return array
     */
    public function getStatistiquesUtilisateur(User $user, $annee = null)
    {
        $annee = $annee ?? date('Y');
        
        $soldes = SoldeConge::where('user_id', $user->id)
            ->where('annee', $annee)
            ->with('typeConge')
            ->get();
            
        $conges = Conge::where('employeur_id', $user->employeur->id)
            ->whereYear('date_debut', $annee)
            ->with('typeConge')
            ->get();
            
        $stats = [
            'soldes' => $soldes->map(function ($solde) {
                return [
                    'type' => $solde->typeConge->nom,
                    'solde_initial' => $solde->solde_initial,
                    'solde_acquis' => $solde->solde_acquis,
                    'solde_pris' => $solde->solde_pris,
                    'solde_restant' => $solde->solde_restant
                ];
            }),
            'conges' => [
                'en_attente' => $conges->where('statut', 'en_attente')->count(),
                'approuves' => $conges->where('statut', 'approuve')->count(),
                'rejetes' => $conges->where('statut', 'rejete')->count(),
                'annules' => $conges->where('statut', 'annule')->count(),
                'total_jours_pris' => $conges->where('statut', 'approuve')->sum('duree_jours')
            ],
            'repartition_mensuelle' => $this->getRepartitionMensuelle($conges->where('statut', 'approuve'))
        ];
        
        return $stats;
    }
    
    /**
     * Obtenir la répartition mensuelle des congés
     *
     * @param \Illuminate\Support\Collection $conges Collection de congés
     * @return array
     */
    protected function getRepartitionMensuelle($conges)
    {
        $repartition = array_fill(1, 12, 0);
        
        foreach ($conges as $conge) {
            $dateDebut = Carbon::parse($conge->date_debut);
            $dateFin = Carbon::parse($conge->date_fin);
            
            // Si le congé s'étend sur plusieurs mois, répartir les jours
            if ($dateDebut->month == $dateFin->month) {
                $repartition[$dateDebut->month] += $conge->duree_jours;
            } else {
                $date = clone $dateDebut;
                while ($date->lte($dateFin)) {
                    if (!in_array($date->dayOfWeek, [0, 6])) { // Jour ouvrable
                        $repartition[$date->month]++;
                    }
                    $date->addDay();
                }
            }
        }
        
        return $repartition;
    }
}
