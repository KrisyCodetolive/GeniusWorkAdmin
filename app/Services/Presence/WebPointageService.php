<?php

namespace App\Services\Presence;

use App\Models\Presence;
use App\Models\Site;
use App\Models\User;
use App\Models\Employeur;
use App\Models\PlageHoraire;
use App\Models\MethodePointage;
use App\Models\Supplementaire;
use App\Models\Politique;
use App\Services\QREncryptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class WebPointageService
{
    /**
     * Génère un QR code pour un site spécifique
     *
     * @param Site $site
     * @return string Le token du QR code
     */
    public function generateQrCodeForSite(Site $site)
    {
        // Utiliser la méthode du modèle Site pour générer le QR code
        return $site->generateQRCode();
    }

    /**
     * Traite une demande de pointage
     *
     * @param Request $request
     * @return array
     */
    public function processPointage(Request $request)
    {
        try {

            

            $idno = $request->idno;
            
            // D'abord essayer de trouver l'employeur avec l'identifiant non crypté
            $employeur = Employeur::where('qr_code_secret', $idno)->first();
            
            if (!$employeur) {
                // Vérifier si le QR code est crypté
                try {
                    // Vérifier si le QR code est valide et décrypter
                    if (QREncryptionService::verifyQRData($idno)) {
                        // Extraire l'ID de la carte décryptée
                        $decryptedId = QREncryptionService::extractCardId($idno);
                        
                        // Chercher l'employeur avec l'ID décrypté
                        $employeur = Employeur::where('id', $decryptedId)->first();
                        
                        if (!$employeur) {
                            return [
                                'status' => 'error',
                                'message' => 'Identifiant employé non reconnu après décryptage '. $idno . ' ' . $decryptedId
                            ];
                            Log::info('Employeur non trouvé après décryptage : ' . $decryptedId . ' ' . $idno);
                        }
                        Log::info('Employeur trouvé après décryptage : ' . $employeur->id);
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur lors du décryptage du QR code : ' . $e->getMessage());
                    return [
                        'status' => 'error',
                        'message' => 'QR code invalide ou expiré'
                    ];
                }
            }
            
            if (!$employeur) {
                return [
                    'status' => 'error',
                    'message' => 'Identifiant employé non reconnu'
                ];
            }
            
            // Récupérer le site
            $site = Site::where('qr_token', $request->token)->first();
            
            if (!$site) {
                return [
                    'status' => 'error',
                    'message' => 'QR code invalide ou expiré'
                ];
            }
            
            // Vérifier si l'employé appartient à l'entreprise du site
            if ($employeur->entreprise_id !== $site->entreprise_id) {
                return [
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à pointer sur ce site'
                ];
            }
            
            // Récupérer ou créer la présence du jour
            $today = Carbon::today();
            $presence = Presence::where('employeur_id', $employeur->id)
                ->whereDate('date_heure_entree', $today)
                ->where('type', 'entree')
                ->first();
                
            $isNewPresence = false;
            $previousType = null;
            
            if (!$presence) {
                // Aucune présence aujourd'hui, on en crée une nouvelle
                $presence = new Presence();
                $presence->employeur_id = $employeur->id;
                $presence->site_id = $site->id;
                $presence->date_heure = Carbon::now();
                $isNewPresence = true;
                $type = 'entree'; // Premier pointage de la journée = entrée
            } else {
                // On a déjà une présence aujourd'hui, on garde une trace du type précédent
                $previousType = $presence->type;
                
                // Déterminer le type de pointage (entrée, sortie, pause_debut, pause_fin)
                if ($request->filled('pause') && $request->pause) {
                    // Vérifier si l'entreprise autorise les pauses
                    $politique = Politique::where('entreprise_id', $employeur->entreprise_id)->first();
                    
                    if (!$politique || !$politique->activer_pauses) {
                        return [
                            'status' => 'error',
                            'message' => 'Les pauses ne sont pas autorisées dans votre entreprise'
                        ];
                    }
                    
                    // Déterminer le type de pause
                    if ($presence->type === 'entree' || $presence->type === 'pause_fin') {
                        $type = 'pause_debut';
                    } else if ($presence->type === 'pause_debut') {
                        $type = 'pause_fin';
                    } else if ($presence->type === 'sortie') {
                        // L'employé est déjà sorti, on ne peut pas commencer une pause
                        return [
                            'status' => 'error',
                            'message' => 'Vous avez déjà pointé votre sortie aujourd\'hui'
                        ];
                    }
                } else {
                    // Logique standard entrée/sortie
                    if ($presence->type === 'entree' || $presence->type === 'pause_fin') {
                        $type = 'sortie';
                    } else if ($presence->type === 'pause_debut') {
                        $type = 'pause_fin';
                    } else if ($presence->type === 'sortie') {
                        // Si l'employé est déjà sorti, c'est une nouvelle entrée (retour)
                        $type = 'entree';
                    }
                }
            }
            
            // Récupérer la méthode de pointage WebPointage
            $methodePointage = MethodePointage::where('code', $request->methode_pointage)
                ->where('entreprise_id', $employeur->entreprise_id)
                ->first();
            
            if (!$methodePointage) {
                // Fallback sur une méthode par défaut
                $methodePointage = MethodePointage::where('entreprise_id', $employeur->entreprise_id)
                    ->first();
                
                if (!$methodePointage) {
                    // Créer une méthode de pointage par défaut avec le code fourni dans la requête
                    $methodePointage = new MethodePointage([
                        'entreprise_id' => $employeur->entreprise_id,
                        'nom' => 'Pointage Web par défaut',
                        'code' => $request->methode_pointage,
                        'description' => 'Méthode de pointage web créée automatiquement',
                        'necessite_photo' => false,
                        'necessite_geolocalisation' => true,
                        'necessite_signature' => false,
                        'necessite_validation' => false,
                        'autoriser_hors_site' => false,
                        'rayon_geofencing' => 100, // 100 mètres par défaut
                        'statut' => 'actif'
                    ]);
                    $methodePointage->save();
                }
            }
            
            // Mettre à jour l'enregistrement de présence
            if ($isNewPresence) {
                // Pour une nouvelle présence, on initialise les champs
                $presence->source = 'webpointage:' . $request->methode_pointage;
                $presence->date_heure_entree = Carbon::now();
            }
            
            // Mettre à jour le type et l'heure actuelle
            $presence->type = $type;
            $presence->date_heure = Carbon::now();
            
            // Mettre à jour le site si différent
            if ($presence->site_id != $site->id) {
                $presence->site_id = $site->id;
            }
            
            // Traiter selon le type de pointage
            if ($type === 'entree') {
                if (!$isNewPresence) {
                    // Si c'est un retour après une sortie, on calcule le temps de pause
                    if ($previousType === 'sortie' && $presence->date_heure_sortie) {
                        $tempsPause = Carbon::parse($presence->date_heure_sortie)
                            ->diffInMinutes(Carbon::now());
                        
                        // Ajouter ce temps aux minutes de pause déjà enregistrées
                        $presence->minutes_pause = ($presence->minutes_pause ?? 0) + $tempsPause;
                        
                        Log::channel('presences')->debug('Temps de pause calculé pour retour', [
                            'temps_pause' => $tempsPause,
                            'total_pauses' => $presence->minutes_pause
                        ]);
                    }
                }
            } else if ($type === 'sortie') {
                $presence->date_heure_sortie = Carbon::now();
                
                // Calculer le temps de travail
                if ($presence->date_heure_entree) {
                    $tempsTotal = Carbon::parse($presence->date_heure_entree)
                        ->diffInMinutes(Carbon::now());
                    
                    // Soustraire le temps de pause du temps total
                    $tempsTravail = $tempsTotal - ($presence->minutes_pause ?? 0);
                    $presence->minutes_travaillees = $tempsTravail;
                    
                    Log::channel('presences')->debug('Temps de travail calculé', [
                        'temps_total' => $tempsTotal,
                        'temps_pause' => $presence->minutes_pause,
                        'temps_travail' => $tempsTravail
                    ]);
                }
            } else if ($type === 'pause_debut') {
                $presence->date_heure_pause_debut = Carbon::now();
            } else if ($type === 'pause_fin') {
                $presence->date_heure_pause_fin = Carbon::now();
                
                // Calculer la durée de cette pause
                if ($presence->date_heure_pause_debut) {
                    $tempsPause = Carbon::parse($presence->date_heure_pause_debut)
                        ->diffInMinutes(Carbon::now());
                    
                    // Ajouter ce temps aux minutes de pause déjà enregistrées
                    $presence->minutes_pause = ($presence->minutes_pause ?? 0) + $tempsPause;
                    
                    Log::channel('presences')->debug('Temps de pause calculé', [
                        'temps_pause' => $tempsPause,
                        'total_pauses' => $presence->minutes_pause
                    ]);
                }
            }
            
            // Attribuer le statut en fonction du type de pointage
            // Les valeurs possibles pour statut sont: 'present','absent','retard','sortie','conge'
            if ($type === 'entree') {
                $presence->statut = $presence->retard ? 'retard' : 'present';
            } else if ($type === 'sortie') {
                $presence->statut = 'sortie';
            } else {
                // Pour les pauses, on garde le statut 'present'
                $presence->statut = 'present';
            }
            
            // Ajouter les coordonnées géographiques si disponibles
            if ($request->filled(['lat', 'lng'])) {
                $presence->latitude = $request->lat;
                $presence->longitude = $request->lng;
                
                // Calculer la distance par rapport au site si le site a des coordonnées
                if ($site->latitude && $site->longitude) {
                    $distance = $this->calculateDistance(
                        $request->lat,
                        $request->lng,
                        $site->latitude,
                        $site->longitude
                    );
                    $presence->distance_site = round($distance);
                    
                    // Vérifier si la distance est trop grande (par défaut 100m)
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
            
            // Vérifier si c'est un retard (uniquement pour les entrées et nouvelles présences)
            Log::channel('presences')->debug('Vérification du retard pour le type: ' . $type);
            if ($type === 'entree' && $isNewPresence) {
                // Récupérer la plage horaire de l'employeur pour aujourd'hui
                $plageHoraire = $this->getPlageHoraireForEmployeur($employeur, Carbon::now());
                Log::channel('presences')->debug('Plage horaire récupérée', ['heure_debut' => $plageHoraire->heure_debut ?? null, 'heure_fin' => $plageHoraire->heure_fin ?? null]);
                
                if ($plageHoraire) {
                    $heureDebut = Carbon::parse($plageHoraire->heure_debut)->format('H:i');
                    $heureActuelle = Carbon::now()->format('H:i');
                    Log::channel('presences')->debug('Comparaison des heures', ['heure_actuelle' => $heureActuelle, 'heure_debut' => $heureDebut]);
                    
                    // Vérifier si l'employé est en retard
                    if ($heureActuelle > $heureDebut) {
                        $minutesRetard = Carbon::parse($heureActuelle)->diffInMinutes(Carbon::parse($heureDebut));
                        $presence->minutes_retard = $minutesRetard;
                        $presence->retard = true;
                        Log::channel('presences')->debug('Retard détecté', ['minutes_retard' => $minutesRetard]);
                    }
                }
            }
            
            // Calculer les heures supplémentaires (uniquement pour les sorties)
            if ($type === 'sortie') {
                // On a déjà calculé les minutes travaillées plus haut
                
                // Calculer les heures supplémentaires
                $plageHoraire = $this->getPlageHoraireForEmployeur($employeur, Carbon::now());
                
                if ($plageHoraire) {
                    $heureFin = Carbon::parse($plageHoraire->heure_fin);
                    $heureActuelle = Carbon::now();
                    
                    // Si l'employé travaille au-delà de sa plage horaire
                    if ($heureActuelle->gt($heureFin)) {
                        $minutesSupplementaires = $heureActuelle->diffInMinutes($heureFin);
                        $presence->minutes_supplementaires = $minutesSupplementaires;
                        
                        Log::channel('presences')->debug('Heures supplémentaires détectées', [
                            'minutes_supplementaires' => $minutesSupplementaires,
                            'heure_fin_prevue' => $heureFin->format('H:i'),
                            'heure_sortie' => $heureActuelle->format('H:i')
                        ]);
                        
                        // Créer un enregistrement d'heures supplémentaires
                        $supplementaire = $this->creerHeuresSupplementaires(
                            $employeur,
                            $heureFin,
                            $heureActuelle,
                            $minutesSupplementaires,
                            "Pointage web - Dépassement horaire"
                        );
                        
                        // Ajouter l'ID de l'enregistrement Supplementaire à la présence
                        $presence->supplementaire_id = $supplementaire->id;
                    }
                }
            }
            
            // Enregistrer la présence
            Log::channel('presences')->debug('Tentative d\'enregistrement de la présence', [
                'type' => $presence->type,
                'statut' => $presence->statut,
                'employeur_id' => $presence->employeur_id,
                'site_id' => $presence->site_id,
                'is_new' => $isNewPresence,
                'minutes_pause' => $presence->minutes_pause ?? 0,
                'minutes_travaillees' => $presence->minutes_travaillees ?? 0
            ]);
            $presence->save();
            Log::channel('presences')->debug('Présence enregistrée avec succès', ['presence_id' => $presence->id]);
            
            // Préparer le message de bienvenue/au revoir
            $message = $this->getWelcomeMessage($employeur, $type);
            
            // Préparer des informations supplémentaires pour l'interface utilisateur
            $infoSupplementaire = '';
            
            if ($type === 'sortie' && $presence->minutes_travaillees) {
                $heures = floor($presence->minutes_travaillees / 60);
                $minutes = $presence->minutes_travaillees % 60;
                $infoSupplementaire = "Temps de travail: {$heures}h{$minutes}min";
                
                if ($presence->minutes_pause > 0) {
                    $heuresPause = floor($presence->minutes_pause / 60);
                    $minutesPause = $presence->minutes_pause % 60;
                    $infoSupplementaire .= ", Temps de pause: {$heuresPause}h{$minutesPause}min";
                }
            } else if ($type === 'pause_fin' && $presence->minutes_pause) {
                $heuresPause = floor($presence->minutes_pause / 60);
                $minutesPause = $presence->minutes_pause % 60;
                $infoSupplementaire = "Temps de pause: {$heuresPause}h{$minutesPause}min";
            }
            
            Log::channel('presences')->debug('Préparation de la réponse');
            // Retourner les données
            return [
                'status' => 'success',
                'data' => [
                    'employee' => $employeur->nom_complet,
                    'type' => $type,
                    'time' => Carbon::now()->format('H:i:s'),
                    'date' => Carbon::now()->format('d/m/Y'),
                    'site' => $site->nom,
                    'voice' => $message,
                    'info_supplementaire' => $infoSupplementaire,
                    'retard' => $presence->retard ?? false,
                    'minutes_retard' => $presence->minutes_retard ?? 0,
                    'minutes_travaillees' => $presence->minutes_travaillees ?? 0,
                    'minutes_supplementaires' => $presence->minutes_supplementaires ?? 0,
                    'minutes_pause' => $presence->minutes_pause ?? 0
                ]
            ];
            
        } catch (\Exception $e) {
            Log::channel('presences')->debug('Erreur lors du traitement du pointage: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Une erreur est survenue lors du traitement de votre pointage'
            ];
        }
    }

    /**
     * Récupère la plage horaire applicable pour un employeur à une date donnée
     *
     * @param Employeur $employeur
     * @param Carbon $date
     * @return PlageHoraire|null
     */
    public function getPlageHoraireForEmployeur(Employeur $employeur, Carbon $date)
    {
        // Récupérer la plage horaire spécifique pour ce jour de la semaine
        $jourSemaine = strtolower($date->locale('fr')->dayName);
        
        try {
            Log::channel('presences')->debug('Recherche de plage horaire pour employeur', [
                'employeur_id' => $employeur->id,
                'jour_semaine' => $jourSemaine,
                'date' => $date->format('Y-m-d')
            ]);
            
            // D'après la structure des modèles, on cherche une plage horaire via la table Jour
            $plageHoraire = PlageHoraire::whereHas('jours', function($query) use ($employeur, $jourSemaine) {
                $query->where('employeur_id', $employeur->id);
                // Si la table Jour a une colonne jour_semaine, on l'utilise
                if (Schema::hasColumn('jours', 'jour_semaine')) {
                    $query->where('jour_semaine', $jourSemaine);
                }
            })
            ->where(function($query) {
                // Si la table PlageHoraire a une colonne actif, on l'utilise
                if (Schema::hasColumn('plage_horaires', 'actif')) {
                    $query->where('actif', true);
                } else if (Schema::hasColumn('plage_horaires', 'statut')) {
                    $query->where('statut', 'actif');
                }
            })
            ->first();
            
            if ($plageHoraire) {
                Log::channel('presences')->debug('Plage horaire trouvée', ['plage_horaire_id' => $plageHoraire->id]);
                return $plageHoraire;
            }
        } catch (\Exception $e) {
            Log::channel('presences')->debug('Erreur lors de la recherche de plage horaire: ' . $e->getMessage());
        }
        
        // Si aucune plage horaire n'est trouvée ou en cas d'erreur, utiliser les valeurs par défaut
        Log::channel('presences')->debug('Utilisation de la plage horaire par défaut');
        return (object)[
            'heure_debut' => '08:00',
            'heure_fin' => '18:00',
            'pause_debut' => '12:00',
            'pause_fin' => '13:00'
        ];
    }

    /**
     * Génère un message de bienvenue/au revoir personnalisé
     *
     * @param Employeur $employeur
     * @param string $type
     * @return string
     */
    protected function getWelcomeMessage(Employeur $employeur, $type)
    {
        $prenom = $employeur->prenom ?? explode(' ', $employeur->nom_complet)[0] ?? $employeur->nom_complet;
        
        if ($type === 'entree') {
            $heure = Carbon::now()->format('H');
            
            if ($heure < 12) {
                return "Bonjour {$prenom}, bonne journée !";
            } else if ($heure < 18) {
                return "Bon après-midi {$prenom} !";
            } else {
                return "Bonsoir {$prenom}, bonne soirée de travail !";
            }
        } else if ($type === 'sortie') {
            $heure = Carbon::now()->format('H');
            
            if ($heure < 12) {
                return "Au revoir {$prenom}, bonne journée !";
            } else if ($heure < 18) {
                return "Au revoir {$prenom}, bon après-midi !";
            } else {
                return "Au revoir {$prenom}, bonne soirée !";
            }
        } else if ($type === 'pause_debut') {
            return "Pause {$prenom} !";
        } else if ($type === 'pause_fin') {
            return "Retour au travail {$prenom} !";
        }
    }

    /**
     * Calcule la distance en mètres entre deux points géographiques
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Rayon de la Terre en mètres
        $earthRadius = 6371000;
        
        // Conversion des degrés en radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        
        // Formule de Haversine
        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($lat1) * cos($lat2) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        // Distance en mètres
        return $earthRadius * $c;
    }

    /**
     * Récupère l'historique des pointages
     *
     * @param Employeur $employeur
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getHistorique(Employeur $employeur, array $filters = [])
    {
        $query = Presence::query();
        
        // Filtrer par employeur directement (pas besoin de filtrer par entreprise_id qui n'existe pas dans la table presences)
        
        // Si un employeur spécifique est demandé
        if (isset($filters['employeur_id']) && !empty($filters['employeur_id'])) {
            $query->where('employeur_id', $filters['employeur_id']);
        } else if (isset($filters['user_id']) && !empty($filters['user_id'])) {
            // Compatibilité avec l'ancien système basé sur user_id
            $employeurFiltre = Employeur::whereHas('user', function($q) use ($filters) {
                $q->where('id', $filters['user_id']);
            })->first();
            
            if ($employeurFiltre) {
                $query->where('employeur_id', $employeurFiltre->id);
            }
        }
        
        // Filtrage par date de début
        if (isset($filters['date_debut']) && !empty($filters['date_debut'])) {
            $query->where(function($q) use ($filters) {
                $q->whereDate('date_heure', '>=', $filters['date_debut'])
                  ->orWhereDate('date_heure_entree', '>=', $filters['date_debut']);
            });
        }
        
        // Filtrage par date de fin
        if (isset($filters['date_fin']) && !empty($filters['date_fin'])) {
            $query->where(function($q) use ($filters) {
                $q->whereDate('date_heure', '<=', $filters['date_fin'])
                  ->orWhereDate('date_heure_sortie', '<=', $filters['date_fin']);
            });
        }
        
        // Filtrage par site
        if (isset($filters['site_id']) && !empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }
        
        // Filtrage par type de pointage
        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        
        // Tri par date décroissante (priorité aux nouveaux champs)
        $query->orderByRaw('COALESCE(date_heure_sortie, date_heure_entree, date_heure) DESC');
        
        // Limiter les résultats si demandé
        if (isset($filters['limit']) && is_numeric($filters['limit'])) {
            return $query->limit($filters['limit'])->get();
        }
        
        // Pagination par défaut
        return $query->paginate(20);
    }

    /**
     * Calcule les statistiques de pointage pour un employeur sur une période donnée
     *
     * @param Employeur $employeur L'employeur concerné
     * @param Carbon $dateDebut Date de début de la période
     * @param Carbon $dateFin Date de fin de la période
     * @return array Tableau contenant les statistiques calculées
     */
    public function calculateStatistics(Employeur $employeur, Carbon $dateDebut, Carbon $dateFin)
    {
        // S'assurer que les dates sont au bon format
        $dateDebut = $dateDebut->startOfDay();
        $dateFin = $dateFin->endOfDay();
        
        // Récupérer toutes les présences de l'employeur sur la période
        $presences = Presence::where('employeur_id', $employeur->id)
            ->whereBetween('date_heure', [$dateDebut, $dateFin])
            ->orderBy('date_heure')
            ->get();
        
        // Initialiser les compteurs
        $totalMinutes = 0;
        $joursTravailles = [];
        $nbRetards = 0;
        
        // Parcourir les présences pour calculer les statistiques
        foreach ($presences as $presence) {
            // Ajouter le jour à la liste des jours travaillés
            $jour = $presence->date_heure->format('Y-m-d');
            if (!in_array($jour, $joursTravailles)) {
                $joursTravailles[] = $jour;
            }
            
            // Compter les retards
            if ($presence->type === 'entree' && $presence->retard) {
                $nbRetards++;
            }
            
            // Calculer le temps travaillé
            if ($presence->type === 'sortie' && $presence->minutes_travaillees) {
                $totalMinutes += $presence->minutes_travaillees;
            }
        }
        
        // Calculer le total des heures
        $heures = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $totalHeures = sprintf('%02d:%02d', $heures, $minutes);
        
        // Calculer la moyenne par jour (si au moins un jour travaillé)
        $nbJoursTravailles = count($joursTravailles);
        if ($nbJoursTravailles > 0) {
            $moyenneMinutes = $totalMinutes / $nbJoursTravailles;
            $moyenneHeures = floor($moyenneMinutes / 60);
            $moyenneMinutesRestantes = round($moyenneMinutes % 60);
            $moyenneParJour = sprintf('%02d:%02d', $moyenneHeures, $moyenneMinutesRestantes);
        } else {
            $moyenneParJour = '00:00';
        }
        
        // Retourner les statistiques
        return [
            'totalHeures' => $totalHeures,
            'joursTravailles' => $nbJoursTravailles,
            'nbRetards' => $nbRetards,
            'moyenneParJour' => $moyenneParJour
        ];
    }

    /**
     * Crée un enregistrement d'heures supplémentaires
     *
     * @param Employeur $employeur
     * @param Carbon $heureDebut
     * @param Carbon $heureFin
     * @param int $minutesSupplementaires
     * @param string $motif
     * @return Supplementaire
     */
    protected function creerHeuresSupplementaires(Employeur $employeur, Carbon $heureDebut, Carbon $heureFin, int $minutesSupplementaires, string $motif)
    {
        // Convertir les minutes en heures (format décimal)
        $nombreHeures = round($minutesSupplementaires / 60, 2);
        
        // Créer l'enregistrement d'heures supplémentaires
        $supplementaire = new Supplementaire();
        $supplementaire->employeur_id = $employeur->id;
        $supplementaire->date = Carbon::today();
        $supplementaire->heure_debut = $heureDebut;
        $supplementaire->heure_fin = $heureFin;
        $supplementaire->nombre_heures = $nombreHeures;
        $supplementaire->taux_majoration = 25; // Taux par défaut de 25%
        $supplementaire->motif = $motif;
        $supplementaire->statut = 'en_attente';
        $supplementaire->montant = 0; // Initialiser le montant à 0 pour éviter l'erreur SQL
        
        $supplementaire->save();
        
        // Calculer le montant
        $supplementaire->calculerMontant();
        
        return $supplementaire;
    }
}
