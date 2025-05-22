<?php

namespace App\Services\Biometrique;

use App\Models\AppareilBiometrique;
use App\Models\LogAppareilBiometrique;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service pour la gestion des logs des appareils biométriques
 */
class LogAppareilBiometriqueService
{
    /**
     * Crée un nouveau log pour un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param User|null $user
     * @param string $typeEvenement
     * @param array $details
     * @param string $statut
     * @param array|null $donneesBrutes
     * @return LogAppareilBiometrique
     */
    public function creerLog(
        AppareilBiometrique $appareil,
        ?User $user,
        string $typeEvenement,
        array $details = [],
        string $statut = 'info',
        ?array $donneesBrutes = null
    ): LogAppareilBiometrique {
        $log = new LogAppareilBiometrique();
        $log->appareil_biometrique_id = $appareil->id;
        $log->user_id = $user ? $user->id : null;
        $log->type_evenement = $typeEvenement;
        $log->details = $details;
        $log->statut = $statut;
        $log->date_evenement = Carbon::now();
        $log->donnees_brutes = $donneesBrutes;
        $log->save();
        
        return $log;
    }

    /**
     * Récupère les logs d'un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param int $limit
     * @param string|null $typeEvenement
     * @param string|null $statut
     * @return Collection
     */
    public function getLogsAppareil(
        AppareilBiometrique $appareil,
        int $limit = 100,
        ?string $typeEvenement = null,
        ?string $statut = null
    ): Collection {
        $query = LogAppareilBiometrique::where('appareil_biometrique_id', $appareil->id)
            ->orderBy('date_evenement', 'desc');
            
        if ($typeEvenement) {
            $query->where('type_evenement', $typeEvenement);
        }
        
        if ($statut) {
            $query->where('statut', $statut);
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * Récupère les logs d'un utilisateur sur un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getLogsUtilisateur(
        AppareilBiometrique $appareil,
        User $user,
        int $limit = 100
    ): Collection {
        return LogAppareilBiometrique::where('appareil_biometrique_id', $appareil->id)
            ->where('user_id', $user->id)
            ->orderBy('date_evenement', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Récupère les logs d'erreur d'un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param int $limit
     * @return Collection
     */
    public function getLogsErreur(AppareilBiometrique $appareil, int $limit = 100): Collection
    {
        return LogAppareilBiometrique::where('appareil_biometrique_id', $appareil->id)
            ->where('statut', 'error')
            ->orderBy('date_evenement', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Récupère les logs récents d'un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param int $heures
     * @return Collection
     */
    public function getLogsRecents(AppareilBiometrique $appareil, int $heures = 24): Collection
    {
        return LogAppareilBiometrique::where('appareil_biometrique_id', $appareil->id)
            ->where('date_evenement', '>=', Carbon::now()->subHours($heures))
            ->orderBy('date_evenement', 'desc')
            ->get();
    }

    /**
     * Récupère les statistiques des logs d'un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param int $jours
     * @return array
     */
    public function getStatistiquesLogs(AppareilBiometrique $appareil, int $jours = 30): array
    {
        $dateDebut = Carbon::now()->subDays($jours)->startOfDay();
        
        $logs = LogAppareilBiometrique::where('appareil_biometrique_id', $appareil->id)
            ->where('date_evenement', '>=', $dateDebut)
            ->get();
            
        $totalLogs = $logs->count();
        $logsParType = $logs->groupBy('type_evenement')
            ->map(function ($items) {
                return $items->count();
            });
            
        $logsParStatut = $logs->groupBy('statut')
            ->map(function ($items) {
                return $items->count();
            });
            
        $logsParJour = $logs->groupBy(function ($log) {
                return $log->date_evenement->format('Y-m-d');
            })
            ->map(function ($items) {
                return $items->count();
            });
            
        return [
            'total' => $totalLogs,
            'par_type' => $logsParType,
            'par_statut' => $logsParStatut,
            'par_jour' => $logsParJour,
            'erreurs' => $logs->where('statut', 'error')->count(),
            'succes' => $logs->where('statut', 'success')->count(),
        ];
    }

    /**
     * Purge les logs anciens d'un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param int $jours
     * @return int
     */
    public function purgerLogsAnciens(AppareilBiometrique $appareil, int $jours = 90): int
    {
        $dateLimite = Carbon::now()->subDays($jours);
        
        return LogAppareilBiometrique::where('appareil_biometrique_id', $appareil->id)
            ->where('date_evenement', '<', $dateLimite)
            ->delete();
    }
}
