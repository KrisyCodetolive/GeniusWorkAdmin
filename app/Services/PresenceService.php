<?php

namespace App\Services;

use App\Models\Presence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PresenceService
{
    /**
     * Récupère les présences pour l'entreprise de l'utilisateur connecté
     *
     * @param array $filters Filtres à appliquer
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPresencesForEntreprise(array $filters = [])
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise->id;
        
        $query = Presence::query()
            ->whereHas('user', function ($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            });
            
        // Appliquer les filtres
        if (!empty($filters['date_debut']) && !empty($filters['date_fin'])) {
            $query->whereBetween('date', [$filters['date_debut'], $filters['date_fin']]);
        } elseif (!empty($filters['date_debut'])) {
            $query->where('date', '>=', $filters['date_debut']);
        } elseif (!empty($filters['date_fin'])) {
            $query->where('date', '<=', $filters['date_fin']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['departement_id'])) {
            $query->whereHas('user', function ($query) use ($filters) {
                $query->where('departement_id', $filters['departement_id']);
            });
        }
        
        return $query->with(['user'])->orderBy('date', 'desc')->get();
    }
    
    /**
     * Valide une présence
     *
     * @param int $presenceId ID de la présence
     * @return Presence
     */
    public function validerPresence(int $presenceId)
    {
        $presence = Presence::findOrFail($presenceId);
        $presence->status = 'validé';
        $presence->validated_at = Carbon::now();
        $presence->validated_by = Auth::id();
        $presence->save();
        
        return $presence;
    }
    
    /**
     * Rejette une présence
     *
     * @param int $presenceId ID de la présence
     * @param string $motif Motif du rejet
     * @return Presence
     */
    public function rejeterPresence(int $presenceId, string $motif)
    {
        $presence = Presence::findOrFail($presenceId);
        $presence->status = 'rejeté';
        $presence->rejection_reason = $motif;
        $presence->rejected_at = Carbon::now();
        $presence->rejected_by = Auth::id();
        $presence->save();
        
        return $presence;
    }
}
