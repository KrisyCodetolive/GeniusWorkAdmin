<?php

namespace App\Services;

use App\Models\Presence;
use App\Models\User;
use App\Models\Employeur;
use App\Models\Site;
use App\Models\MethodePointage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PointageService
{
    /**
     * Enregistre une nouvelle présence
     *
     * @param array $data Les données de la présence
     * @param User $user L'utilisateur qui pointe
     * @param MethodePointage $methodePointage La méthode de pointage utilisée
     * @return Presence
     */
    public function enregistrerPresence(array $data, User $user, MethodePointage $methodePointage): Presence
    {
        $presence = new Presence();
        $presence->user_id = $user->id;
        $presence->employeur_id = $data['employeur_id'] ?? $user->employeur_id;
        $presence->site_id = $data['site_id'] ?? null;
        $presence->methode_pointage_id = $methodePointage->id;
        $presence->type = $data['type'];
        $presence->latitude = $data['latitude'] ?? null;
        $presence->longitude = $data['longitude'] ?? null;
        $presence->precision_geo = $data['precision_geo'] ?? null;
        $presence->date_heure = $data['date_heure'] ?? Carbon::now();
        $presence->adresse_ip = $data['adresse_ip'] ?? request()->ip();
        $presence->appareil = $data['appareil'] ?? null;
        $presence->navigateur = $data['navigateur'] ?? request()->header('User-Agent');
        $presence->source = $data['source'] ?? 'manuel';
        $presence->statut = $data['statut'] ?? 'enregistre';
        $presence->commentaire = $data['commentaire'] ?? null;
        
        if (isset($data['photo_url'])) {
            $presence->photo_url = $data['photo_url'];
        }
        
        if (isset($data['signature_url'])) {
            $presence->signature_url = $data['signature_url'];
        }
        
        if (isset($data['qr_code'])) {
            $presence->qr_code = $data['qr_code'];
        }
        
        if (isset($data['nfc_tag'])) {
            $presence->nfc_tag = $data['nfc_tag'];
        }
        
        if (isset($data['webauthn_credential_id'])) {
            $presence->webauthn_credential_id = $data['webauthn_credential_id'];
        }
        
        // Calculer la distance par rapport au site si les coordonnées sont disponibles
        if ($presence->latitude && $presence->longitude && $presence->site_id) {
            $site = Site::find($presence->site_id);
            if ($site && $site->latitude && $site->longitude) {
                $presence->distance_site = $presence->calculerDistance(
                    $presence->latitude, 
                    $presence->longitude, 
                    $site->latitude, 
                    $site->longitude
                );
            }
        }
        
        $presence->save();
        
        return $presence;
    }
    
    /**
     * Valide une présence existante
     *
     * @param Presence $presence La présence à valider
     * @param User $validateur L'utilisateur qui valide
     * @param string|null $commentaire Un commentaire optionnel
     * @return Presence
     */
    public function validerPresence(Presence $presence, User $validateur, ?string $commentaire = null): Presence
    {
        if ($commentaire) {
            $presence->commentaire = $commentaire;
        }
        
        $presence->valider($validateur);
        
        return $presence;
    }
    
    /**
     * Annule une présence existante
     *
     * @param Presence $presence La présence à annuler
     * @param string|null $commentaire Raison de l'annulation
     * @return Presence
     */
    public function annulerPresence(Presence $presence, ?string $commentaire = null): Presence
    {
        $presence->annuler($commentaire);
        
        return $presence;
    }
    
    /**
     * Récupère les présences d'un utilisateur pour une période donnée
     *
     * @param User $user L'utilisateur
     * @param Carbon $debut Date de début
     * @param Carbon $fin Date de fin
     * @return Collection
     */
    public function getPresencesUtilisateur(User $user, Carbon $debut, Carbon $fin): Collection
    {
        return Presence::where('user_id', $user->id)
            ->whereBetween('date_heure', [$debut, $fin])
            ->orderBy('date_heure', 'asc')
            ->get();
    }
    
    /**
     * Récupère les présences pour un employeur et une période donnée
     *
     * @param Employeur $employeur L'employeur
     * @param Carbon $debut Date de début
     * @param Carbon $fin Date de fin
     * @param int|null $site_id ID du site (optionnel)
     * @return Collection
     */
    public function getPresencesEmployeur(Employeur $employeur, Carbon $debut, Carbon $fin, ?int $site_id = null): Collection
    {
        $query = Presence::where('employeur_id', $employeur->id)
            ->whereBetween('date_heure', [$debut, $fin]);
            
        if ($site_id) {
            $query->where('site_id', $site_id);
        }
        
        return $query->orderBy('date_heure', 'asc')->get();
    }
    
    /**
     * Récupère les dernières présences de tous les employés d'un employeur
     *
     * @param Employeur $employeur L'employeur
     * @param int|null $site_id ID du site (optionnel)
     * @return Collection
     */
    public function getDernieresPresencesEmployes(Employeur $employeur, ?int $site_id = null): Collection
    {
        $subQuery = Presence::select('user_id', DB::raw('MAX(date_heure) as max_date'))
            ->where('employeur_id', $employeur->id)
            ->groupBy('user_id');
            
        if ($site_id) {
            $subQuery->where('site_id', $site_id);
        }
        
        $latestPresences = DB::table(DB::raw("({$subQuery->toSql()}) as latest_presences"))
            ->mergeBindings($subQuery->getQuery())
            ->get();
            
        $presences = collect();
        
        foreach ($latestPresences as $latest) {
            $presence = Presence::where('user_id', $latest->user_id)
                ->where('date_heure', $latest->max_date)
                ->first();
                
            if ($presence) {
                $presences->push($presence);
            }
        }
        
        return $presences;
    }
    
    /**
     * Calcule les heures de présence pour un utilisateur sur une période donnée
     *
     * @param User $user L'utilisateur
     * @param Carbon $debut Date de début
     * @param Carbon $fin Date de fin
     * @return array Tableau avec 'total_minutes' et 'details_jours'
     */
    public function calculerHeuresPresence(User $user, Carbon $debut, Carbon $fin): array
    {
        $presences = $this->getPresencesUtilisateur($user, $debut, $fin);
        $totalMinutes = 0;
        $detailsJours = [];
        
        // Regrouper les présences par jour
        $presencesParJour = $presences->groupBy(function ($presence) {
            return $presence->date_heure->format('Y-m-d');
        });
        
        foreach ($presencesParJour as $date => $presencesJour) {
            $minutesJour = 0;
            $entrees = $presencesJour->where('type', 'entree')->sortBy('date_heure');
            $sorties = $presencesJour->where('type', 'sortie')->sortBy('date_heure');
            $pausesDebut = $presencesJour->where('type', 'pause_debut')->sortBy('date_heure');
            $pausesFin = $presencesJour->where('type', 'pause_fin')->sortBy('date_heure');
            
            // Traiter les entrées et sorties
            $dernierPointage = null;
            foreach ($presencesJour->sortBy('date_heure') as $presence) {
                if ($presence->type === 'entree') {
                    $dernierPointage = $presence;
                } elseif ($presence->type === 'sortie' && $dernierPointage && $dernierPointage->type === 'entree') {
                    $minutesJour += $presence->date_heure->diffInMinutes($dernierPointage->date_heure);
                    $dernierPointage = $presence;
                } elseif ($presence->type === 'pause_debut' && $dernierPointage && $dernierPointage->type === 'entree') {
                    $minutesJour += $presence->date_heure->diffInMinutes($dernierPointage->date_heure);
                    $dernierPointage = $presence;
                } elseif ($presence->type === 'pause_fin' && $dernierPointage && $dernierPointage->type === 'pause_debut') {
                    $dernierPointage = $presence;
                }
            }
            
            // Si le dernier pointage est une pause_fin, on compte jusqu'à la sortie
            if ($dernierPointage && $dernierPointage->type === 'pause_fin') {
                $sortie = $sorties->last();
                if ($sortie && $sortie->date_heure > $dernierPointage->date_heure) {
                    $minutesJour += $sortie->date_heure->diffInMinutes($dernierPointage->date_heure);
                }
            }
            
            $totalMinutes += $minutesJour;
            $detailsJours[$date] = [
                'minutes' => $minutesJour,
                'heures_formatees' => $this->formatMinutesEnHeures($minutesJour),
                'presences' => $presencesJour
            ];
        }
        
        return [
            'total_minutes' => $totalMinutes,
            'total_heures_formatees' => $this->formatMinutesEnHeures($totalMinutes),
            'details_jours' => $detailsJours
        ];
    }
    
    /**
     * Calcule le temps de retard d'un utilisateur sur une période donnée
     *
     * @param User $user L'utilisateur
     * @param Carbon $debut Date de début
     * @param Carbon $fin Date de fin
     * @return array Tableau avec 'total_minutes' et 'details_jours'
     */
    public function calculerRetards(User $user, Carbon $debut, Carbon $fin): array
    {
        $presences = $this->getPresencesUtilisateur($user, $debut, $fin);
        $totalMinutes = 0;
        $detailsJours = [];
        
        // Récupérer les plages horaires de l'utilisateur
        $joursTravail = $user->joursTravail()->with('plagesHoraires')->get();
        
        // Regrouper les présences par jour
        $presencesParJour = $presences->groupBy(function ($presence) {
            return $presence->date_heure->format('Y-m-d');
        });
        
        foreach ($presencesParJour as $date => $presencesJour) {
            $dateCarbon = Carbon::parse($date);
            $jourSemaine = $dateCarbon->dayOfWeekIso; // 1 (lundi) à 7 (dimanche)
            
            // Trouver le jour de travail correspondant
            $jourTravail = $joursTravail->firstWhere('jour', $jourSemaine);
            
            if (!$jourTravail) {
                continue; // Pas de jour de travail défini pour ce jour
            }
            
            // Trouver la première plage horaire de la journée
            $premierePlage = $jourTravail->plagesHoraires()
                ->where('est_pause', false)
                ->orderBy('heure_debut')
                ->first();
                
            if (!$premierePlage) {
                continue; // Pas de plage horaire définie
            }
            
            // Trouver le premier pointage d'entrée de la journée
            $premierPointage = $presencesJour->where('type', 'entree')->sortBy('date_heure')->first();
            
            if (!$premierPointage) {
                continue; // Pas de pointage d'entrée ce jour-là
            }
            
            // Calculer l'heure de début prévue
            $heureDebutPrevue = Carbon::parse($date . ' ' . $premierePlage->heure_debut);
            
            // Calculer le retard en minutes
            if ($premierPointage->date_heure > $heureDebutPrevue) {
                $retardMinutes = $premierPointage->date_heure->diffInMinutes($heureDebutPrevue);
                $totalMinutes += $retardMinutes;
                
                $detailsJours[$date] = [
                    'minutes' => $retardMinutes,
                    'heures_formatees' => $this->formatMinutesEnHeures($retardMinutes),
                    'heure_prevue' => $heureDebutPrevue->format('H:i'),
                    'heure_pointage' => $premierPointage->date_heure->format('H:i')
                ];
            }
        }
        
        return [
            'total_minutes' => $totalMinutes,
            'total_heures_formatees' => $this->formatMinutesEnHeures($totalMinutes),
            'details_jours' => $detailsJours
        ];
    }
    
    /**
     * Vérifie si un utilisateur est actuellement présent
     *
     * @param User $user L'utilisateur
     * @return bool
     */
    public function estPresent(User $user): bool
    {
        $dernierePresence = Presence::where('user_id', $user->id)
            ->orderBy('date_heure', 'desc')
            ->first();
            
        if (!$dernierePresence) {
            return false;
        }
        
        return in_array($dernierePresence->type, ['entree', 'pause_fin']);
    }
    
    /**
     * Vérifie si un utilisateur est actuellement en pause
     *
     * @param User $user L'utilisateur
     * @return bool
     */
    public function estEnPause(User $user): bool
    {
        $dernierePresence = Presence::where('user_id', $user->id)
            ->orderBy('date_heure', 'desc')
            ->first();
            
        if (!$dernierePresence) {
            return false;
        }
        
        return $dernierePresence->type === 'pause_debut';
    }
    
    /**
     * Récupère les statistiques de présence pour un département
     *
     * @param int $departementId ID du département
     * @param Carbon $date Date du jour
     * @return array
     */
    public function getStatistiquesDepartement(int $departementId, Carbon $date): array
    {
        $debut = $date->copy()->startOfDay();
        $fin = $date->copy()->endOfDay();
        
        // Récupérer tous les utilisateurs du département
        $users = User::where('departement_id', $departementId)->get();
        $userIds = $users->pluck('id')->toArray();
        
        // Récupérer toutes les présences du jour pour ces utilisateurs
        $presences = Presence::whereIn('user_id', $userIds)
            ->whereBetween('date_heure', [$debut, $fin])
            ->get();
            
        $presents = 0;
        $absents = 0;
        $enPause = 0;
        $retards = 0;
        
        foreach ($users as $user) {
            if ($this->estPresent($user)) {
                $presents++;
            } elseif ($this->estEnPause($user)) {
                $enPause++;
            } else {
                $absents++;
            }
            
            // Vérifier les retards
            $retardInfo = $this->calculerRetards($user, $debut, $fin);
            if (!empty($retardInfo['details_jours'][$date->format('Y-m-d')])) {
                $retards++;
            }
        }
        
        return [
            'total_employes' => $users->count(),
            'presents' => $presents,
            'absents' => $absents,
            'en_pause' => $enPause,
            'retards' => $retards,
            'taux_presence' => $users->count() > 0 ? round(($presents + $enPause) / $users->count() * 100, 2) : 0,
            'taux_retard' => $users->count() > 0 ? round($retards / $users->count() * 100, 2) : 0
        ];
    }
    
    /**
     * Formate un nombre de minutes en format heures:minutes
     *
     * @param int $minutes Nombre de minutes
     * @return string
     */
    private function formatMinutesEnHeures(int $minutes): string
    {
        $heures = floor($minutes / 60);
        $minutesRestantes = $minutes % 60;
        
        return sprintf('%02d:%02d', $heures, $minutesRestantes);
    }
}
