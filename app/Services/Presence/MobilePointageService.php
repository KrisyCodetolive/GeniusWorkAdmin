<?php

namespace App\Services\Presence;

use App\Models\Presence;
use App\Models\Site;
use App\Models\Employeur;
use App\Models\PlageHoraire;
use App\Models\MethodePointage;
use App\Models\Supplementaire;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class MobilePointageService
{
    /**
     * Traite une demande de pointage mobile
     *
     * @param Request $request
     * @return array
     */
    public function processPointage(Request $request)
    {
        try {
            // Récupérer l'employé à partir de l'utilisateur authentifié
            $user = $request->user();
            $employeur = $user->employeur;
            
            if (!$employeur) {
                return [
                    'status' => 'error',
                    'message' => 'Compte employé non trouvé'
                ];
            }
            
            // Récupérer le site (pour le mobile, on utilise un token spécial)
            $site = Site::where('entreprise_id', $employeur->entreprise_id)
                ->where(function($query) use ($request) {
                    $query->where('id', $request->token);
                })
                ->first();
            
            if (!$site) {
                // Si aucun site spécifique n'est trouvé, on cherche le site principal de l'entreprise
                $site = Site::where('entreprise_id', $employeur->entreprise_id)
                    ->where('principal', true)
                    ->first();
                
                if (!$site) {
                    return [
                        'status' => 'error',
                        'message' => 'Aucun site valide trouvé pour votre entreprise'
                    ];
                }
            }
            
            // Récupérer ou créer la présence du jour
            $today = Carbon::today();
            $lastPresence = Presence::where('employeur_id', $employeur->id)
                ->whereDate('date_heure_entree', $today)
                ->where('type', 'entree')
                ->first();
                
            $isNewPresence = false;
            $previousType = null;
            $type = $request->type;
            
            if (!$lastPresence) {
                // Aucune présence aujourd'hui, on en crée une nouvelle
                $isNewPresence = true;
                $type = 'entree'; // Premier pointage de la journée = entrée
            } else {
                // On a déjà une présence aujourd'hui, on garde une trace du type précédent
                $previousType = $lastPresence->type;
                
                // Déterminer le type de pointage (entrée, sortie, pause_debut, pause_fin)
                $isPause = $request->filled('isPause') && $request->isPause;
                
                if ($isPause) {
                    // Vérifier si l'entreprise autorise les pauses
                    $politique = \App\Models\Politique::where('entreprise_id', $employeur->entreprise_id)->first();
                    
                    if (!$politique || !$politique->activer_pauses) {
                        return [
                            'status' => 'error',
                            'message' => 'Les pauses ne sont pas autorisées dans votre entreprise'
                        ];
                    }
                    
                    // Si on a déjà un pointage d'entrée aujourd'hui et qu'il n'est pas en pause
                    if ($lastPresence && 
                        ($lastPresence->type === 'entree' || $lastPresence->type === 'pause_fin')) {
                        $type = 'pause_debut';
                    }
                    // Si on est déjà en pause, c'est une fin de pause
                    else if ($lastPresence && $lastPresence->type === 'pause_debut') {
                        $type = 'pause_fin';
                    }
                }
                // Si pas de demande de pause, on utilise le type fourni ou on déduit
                else if (!$request->filled('type')) {
                    // Si on a déjà un pointage d'entrée aujourd'hui, c'est une sortie
                    if ($lastPresence && 
                        ($lastPresence->type === 'entree' || 
                         $lastPresence->type === 'pause_fin' ||
                         ($lastPresence->date_heure_entree && !$lastPresence->date_heure_sortie))) {
                        $type = 'sortie';
                    }
                    // Si on est en pause, c'est une fin de pause
                    else if ($lastPresence && $lastPresence->type === 'pause_debut') {
                        $type = 'pause_fin';
                    }
                }
            }
            
            // Récupérer la méthode de pointage Mobile
            $methodePointage = MethodePointage::where('code', $request->methode_pointage ?? 'mobile_app')
                ->where('entreprise_id', $employeur->entreprise_id)
                ->first();
            
            if (!$methodePointage) {
                // Fallback sur une méthode par défaut
                $methodePointage = MethodePointage::where('entreprise_id', $employeur->entreprise_id)
                    ->first();
                
                if (!$methodePointage) {
                    return [
                        'status' => 'error',
                        'message' => 'Aucune méthode de pointage configurée pour votre entreprise'
                    ];
                }
            }
            
            // Créer ou mettre à jour l'enregistrement de présence
            if ($isNewPresence) {
                $presence = new Presence();
                $presence->employeur_id = $employeur->id;
                $presence->site_id = $site->id;
                $presence->source = 'mobile_app';
                $presence->date_heure_entree = Carbon::now();
            } else {
                $presence = $lastPresence;
            }
            
            $presence->type = $type;
            $presence->date_heure = Carbon::now();
            
            // Enregistrer les dates d'entrée/sortie selon le type
            if ($type === 'entree') {
                $presence->date_heure_entree = Carbon::now();
            } else if ($type === 'sortie') {
                $presence->date_heure_sortie = Carbon::now();
                
                // Si on a une entrée aujourd'hui, récupérer sa date pour calculer la durée
                if ($lastPresence && $lastPresence->date_heure_entree) {
                    $presence->date_heure_entree = $lastPresence->date_heure_entree;
                }
            } else if ($type === 'pause_debut') {
                // Pour un début de pause, on enregistre l'heure de début de pause
                $presence->date_heure_pause_debut = Carbon::now();
            } else if ($type === 'pause_fin') {
                // Pour une fin de pause, on enregistre l'heure de fin de pause
                $presence->date_heure_pause_fin = Carbon::now();
                
                // Si on a un début de pause aujourd'hui, récupérer sa date
                if ($lastPresence && $lastPresence->date_heure_pause_debut) {
                    $presence->date_heure_pause_debut = $lastPresence->date_heure_pause_debut;
                    
                    // Calculer la durée de la pause
                    $minutesPause = Carbon::parse($presence->date_heure_pause_debut)
                        ->diffInMinutes(Carbon::parse($presence->date_heure_pause_fin));
                    $presence->minutes_pause = $minutesPause;
                }
            }
            
            $presence->source = 'mobile_app';
            
            // Ajouter les coordonnées géographiques si disponibles
            if ($request->filled(['lat', 'lng'])) {
                if ($type === 'entree') {
                    $presence->latitude_entree = $request->lat;
                    $presence->longitude_entree = $request->lng;
                    
                } else if ($type === 'sortie') {
                    $presence->latitude_sortie = $request->lat;
                    $presence->longitude_sortie = $request->lng;
                }
                
                // Calculer la distance par rapport au site si le site a des coordonnées
                if ($site->latitude && $site->longitude) {
                    $webPointageService = new WebPointageService();
                    $distance = $webPointageService->calculateDistance(
                        $request->lat,
                        $request->lng,
                        $site->latitude,
                        $site->longitude
                    );
                    $presence->distance_site = round($distance);
                    
                    // Vérifier si la distance est trop grande
                    $distanceMax = $site->rayon_geofencing ?? 100;
                    if ($presence->distance_site > $distanceMax) {
                        return [
                            'status' => 'error',
                            'message' => 'Vous êtes trop éloigné du site pour pointer. Distance: ' . 
                                round($presence->distance_site) . 'm (max: ' . $distanceMax . 'm)'
                        ];
                    }
                }
            }
            
            // Vérifier si c'est un retard (uniquement pour les entrées)
            if ($type === 'entree') {
                $webPointageService = new WebPointageService();
                // Récupérer la plage horaire de l'employeur pour aujourd'hui
                $plageHoraire = $webPointageService->getPlageHoraireForEmployeur($employeur, Carbon::now());
                
                if ($plageHoraire) {
                    $heureDebut = Carbon::parse($plageHoraire->heure_debut)->format('H:i');
                    $heureActuelle = Carbon::now()->format('H:i');
                    
                    // Vérifier si l'employé est en retard
                    if ($heureActuelle > $heureDebut) {
                        $minutesRetard = Carbon::parse($heureActuelle)->diffInMinutes(Carbon::parse($heureDebut));
                        $presence->minutes_retard = $minutesRetard;
                        $presence->retard = true;
                        $presence->statut = 'retard';
                    }
                }
            } else if ($type === 'sortie') {
                // Pour une sortie, calculer les heures travaillées
                $entree = Presence::where('employeur_id', $employeur->id)
                    ->whereDate('date_heure', $today)
                    ->where('type', 'entree')
                    ->latest('date_heure')
                    ->first();
                
                if ($entree) {
                    $minutesTravaillees = Carbon::parse($entree->date_heure)->diffInMinutes(Carbon::now());
                    $presence->minutes_travaillees = $minutesTravaillees;
                    
                    // Calculer les heures supplémentaires
                    $webPointageService = new WebPointageService();
                    $plageHoraire = $webPointageService->getPlageHoraireForEmployeur($employeur, Carbon::now());
                    
                    if ($plageHoraire) {
                        $heureFin = Carbon::parse($plageHoraire->heure_fin);
                        $heureActuelle = Carbon::now();
                        
                        // Si l'employé travaille au-delà de sa plage horaire
                        if ($heureActuelle->gt($heureFin)) {
                            $minutesSupplementaires = $heureActuelle->diffInMinutes($heureFin);
                            $presence->minutes_supplementaires = $minutesSupplementaires;
                            
                            // Créer un enregistrement d'heures supplémentaires
                            $supplementaire = $webPointageService->creerHeuresSupplementaires(
                                $employeur,
                                $heureFin,
                                $heureActuelle,
                                $minutesSupplementaires,
                                "Pointage mobile - Dépassement horaire"
                            );
                            
                            // Ajouter l'ID de l'enregistrement Supplementaire à la présence
                            $presence->supplementaire_id = $supplementaire->id;
                        }
                    }
                }
            }
            
            // Mettre à jour le statut de la présence en suivant les règles de validation strict
            $presence->statut_validation = 'approuve';

            // Enregistrer la présence
            $presence->save();
            
            // Retourner les données
            return [
                'status' => 'success',
                'data' => [
                    'id' => $presence->id,
                    'employee' => $employeur->nom_complet,
                    'type' => $type,
                    'time' => Carbon::now()->format('H:i:s'),
                    'date' => Carbon::now()->format('d/m/Y'),
                    'site' => $site->nom,
                    'retard' => $presence->retard ?? false,
                    'minutes_retard' => $presence->minutes_retard ?? 0,
                    'minutes_travaillees' => $presence->minutes_travaillees ?? 0,
                    'minutes_supplementaires' => $presence->minutes_supplementaires ?? 0,
                    'minutes_pause' => $presence->minutes_pause ?? 0,
                    'distance' => $presence->distance_site ?? 0
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement du pointage mobile: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Une erreur est survenue lors du traitement de votre pointage ' . $e->getMessage()
            ];
        }
    }

    /**
     * Récupère l'historique des pointages pour un employeur
     *
     * @param Request $request
     * @return array
     */
    public function getHistorique(Request $request)
    {
        try {
            // Récupérer l'employeur
            $employeur = Employeur::where('qr_code_secret', $request->idno)->first();
            
            if (!$employeur) {
                return [
                    'status' => 'error',
                    'message' => 'Identifiant employé non reconnu'
                ];
            }
            
            // Préparer les filtres
            $filters = [
                'employeur_id' => $employeur->id,
                'limit' => $request->limit ?? 50
            ];
            
            // Ajouter les filtres de date si présents
            if ($request->filled('date_debut')) {
                $filters['date_debut'] = Carbon::parse($request->date_debut)->startOfDay();
            }
            
            if ($request->filled('date_fin')) {
                $filters['date_fin'] = Carbon::parse($request->date_fin)->endOfDay();
            }
            
            // Utiliser le service WebPointage pour récupérer l'historique
            $webPointageService = new WebPointageService();
            $historique = $webPointageService->getHistorique($employeur, $filters);
            
            // Formater les données pour l'API mobile
            $result = [];
            foreach ($historique as $presence) {
                $result[] = [
                    'id' => $presence->id,
                    'type' => $presence->type,
                    'date' => $presence->date_heure ? $presence->date_heure->format('Y-m-d') : null,
                    'heure_entree' => $presence->date_heure_entree ? $presence->date_heure_entree->format('H:i:s') : null,
                    'heure_sortie' => $presence->date_heure_sortie ? $presence->date_heure_sortie->format('H:i:s') : null,
                    'heure_pause_debut' => $presence->date_heure_pause_debut ? $presence->date_heure_pause_debut->format('H:i:s') : null,
                    'heure_pause_fin' => $presence->date_heure_pause_fin ? $presence->date_heure_pause_fin->format('H:i:s') : null,
                    'site' => $presence->site ? $presence->site->nom : null,
                    'retard' => $presence->retard ?? false,
                    'minutes_retard' => $presence->minutes_retard ?? 0,
                    'minutes_travaillees' => $presence->minutes_travaillees ?? 0,
                    'minutes_supplementaires' => $presence->minutes_supplementaires ?? 0,
                    'minutes_pause' => $presence->minutes_pause ?? 0,
                    'distance_site' => $presence->distance_site ?? 0,
                    'latitude' => $presence->latitude,
                    'longitude' => $presence->longitude
                ];
            }
            
            return [
                'status' => 'success',
                'data' => $result
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'historique: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération de l\'historique'
            ];
        }
    }

    /**
     * Récupère les statistiques de pointage pour un employeur
     *
     * @param Request $request
     * @return array
     */
    public function getStatistiques(Request $request)
    {
        try {
            // Récupérer l'employeur
            $employeur = Employeur::where('qr_code_secret', $request->idno)->first();
            
            if (!$employeur) {
                return [
                    'status' => 'error',
                    'message' => 'Identifiant employé non reconnu'
                ];
            }
            
            // Déterminer la période (par défaut le mois en cours)
            $dateDebut = $request->filled('date_debut') 
                ? Carbon::parse($request->date_debut)->startOfDay()
                : Carbon::now()->startOfMonth();
                
            $dateFin = $request->filled('date_fin')
                ? Carbon::parse($request->date_fin)->endOfDay()
                : Carbon::now()->endOfDay();
            
            // Utiliser le service WebPointage pour calculer les statistiques
            $webPointageService = new WebPointageService();
            $stats = $webPointageService->calculateStatistics($employeur, $dateDebut, $dateFin);
            
            // Ajouter des informations supplémentaires
            $stats['periode'] = [
                'debut' => $dateDebut->format('Y-m-d'),
                'fin' => $dateFin->format('Y-m-d')
            ];
            
            $stats['employe'] = [
                'id' => $employeur->id,
                'nom' => $employeur->nom_complet
            ];
            
            return [
                'status' => 'success',
                'data' => $stats
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des statistiques'
            ];
        }
    }

    /**
     * Formate une durée en minutes en format heures:minutes
     *
     * @param int $minutes
     * @return string
     */
    public function formatDuree(int $minutes)
    {
        $heures = floor($minutes / 60);
        $minutesRestantes = $minutes % 60;
        
        return sprintf('%02d:%02d', $heures, $minutesRestantes);
    }
}
