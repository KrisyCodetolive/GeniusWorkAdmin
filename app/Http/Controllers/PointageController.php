<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Site;
use App\Models\MethodePointage;
use App\Models\RaisonSortie;
use App\Services\PresenceService;
use App\Services\HeuresSupplementairesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PointageController extends Controller
{
    protected $presenceService;
    protected $heuresSupplementairesService;

    public function __construct(PresenceService $presenceService, HeuresSupplementairesService $heuresSupplementairesService)
    {
        $this->presenceService = $presenceService;
        $this->heuresSupplementairesService = $heuresSupplementairesService;
        $this->middleware('auth');
        $this->authorizeResource(Presence::class, 'presence');
    }

    /**
     * Affiche la page principale de pointage
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $sites = Site::where('employeur_id', $user->employeur_id)
            ->orderBy('nom')
            ->get();
            
        $methodesPointage = MethodePointage::where('actif', true)
            ->where(function($query) {
                $query->whereNull('employeur_id')
                    ->orWhere('employeur_id', Auth::user()->employeur_id);
            })
            ->orderBy('nom')
            ->get();
            
        $raisonsSortie = RaisonSortie::where('actif', true)
            ->where(function($query) {
                $query->whereNull('employeur_id')
                    ->orWhere('employeur_id', Auth::user()->employeur_id);
            })
            ->orderBy('nom')
            ->get();
            
        $estPresent = $this->presenceService->estPresent($user);
        $estEnPause = $this->presenceService->estEnPause($user);
        
        $dernierSite = null;
        $dernierePresence = Presence::where('user_id', $user->id)
            ->orderBy('date_heure', 'desc')
            ->first();
            
        if ($dernierePresence && $dernierePresence->site_id) {
            $dernierSite = Site::find($dernierePresence->site_id);
        }
        
        return view('app.presence.index', compact(
            'sites', 
            'methodesPointage', 
            'raisonsSortie', 
            'estPresent', 
            'estEnPause',
            'dernierSite'
        ));
    }

    /**
     * Enregistre un nouveau pointage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:entree,sortie,pause_debut,pause_fin',
            'site_id' => 'required|exists:sites,id',
            'methode_pointage_id' => 'required|exists:methode_pointages,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'precision_geo' => 'nullable|numeric',
            'photo' => 'nullable|image|max:5120', // 5MB max
            'signature' => 'nullable|string',
            'raison_sortie_id' => 'nullable|required_if:type,sortie|exists:raison_sorties,id',
            'commentaire' => 'nullable|string|max:500',
        ]);
        
        $user = Auth::user();
        $methodePointage = MethodePointage::findOrFail($request->methode_pointage_id);
        
        $data = $request->all();
        $data['employeur_id'] = $user->employeur_id;
        $data['date_heure'] = Carbon::now();
        
        // Traitement de la photo si présente
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = 'presence_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            
            // Redimensionner et sauvegarder l'image
            $img = Image::make($photo->getRealPath());
            $img->fit(800, 600, function ($constraint) {
                $constraint->upsize();
            });
            
            $path = 'presences/photos/' . $filename;
            Storage::disk('public')->put($path, $img->encode());
            
            $data['photo_url'] = Storage::disk('public')->url($path);
        }
        
        // Traitement de la signature si présente
        if ($request->filled('signature')) {
            $signature = $request->input('signature');
            $signatureImage = Image::make($signature);
            
            $filename = 'signature_' . uniqid() . '.png';
            $path = 'presences/signatures/' . $filename;
            
            Storage::disk('public')->put($path, $signatureImage->encode('png'));
            
            $data['signature_url'] = Storage::disk('public')->url($path);
        }
        
        // Vérification du geofencing si coordonnées GPS présentes
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $site = Site::findOrFail($request->site_id);
            
            if ($site->has_geofencing && $site->latitude && $site->longitude) {
                $distance = (new Presence())->calculerDistance(
                    $request->latitude,
                    $request->longitude,
                    $site->latitude,
                    $site->longitude
                );
                
                $data['distance_site'] = $distance;
                
                if ($distance > $site->rayon_geofencing) {
                    $data['verification_data'] = [
                        'hors_site' => true,
                        'distance' => $distance,
                        'rayon_autorise' => $site->rayon_geofencing
                    ];
                }
            }
        }
        
        $presence = $this->presenceService->enregistrerPresence($data, $user, $methodePointage);
        
        // Vérifier si c'est un pointage de sortie pour détecter les heures supplémentaires
        if ($request->type === 'sortie' && $presence) {
            // Détecter et enregistrer les heures supplémentaires si nécessaire
            $supplementaire = $this->heuresSupplementairesService->traiterHeuresSupplementaires($presence);
            
            if ($supplementaire) {
                return redirect()->route('presence.index')
                    ->with('success', 'Votre pointage a été enregistré avec succès. Des heures supplémentaires ont été détectées et seront soumises à validation.');
            }
        }
        
        return redirect()->route('presence.index')
            ->with('success', 'Votre pointage a été enregistré avec succès.');
    }

    /**
     * Affiche l'historique des pointages
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function historique(Request $request)
    {
        $user = Auth::user();
        $dateDebut = $request->input('date_debut') 
            ? Carbon::parse($request->input('date_debut')) 
            : Carbon::now()->startOfMonth();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::parse($request->input('date_fin'))->endOfDay() 
            : Carbon::now()->endOfDay();
            
        $heuresPresence = $this->presenceService->calculerHeuresPresence($user, $dateDebut, $dateFin);
        $retards = $this->presenceService->calculerRetards($user, $dateDebut, $dateFin);
        
        $presences = Presence::where('user_id', $user->id)
            ->whereBetween('date_heure', [$dateDebut, $dateFin])
            ->orderBy('date_heure', 'desc')
            ->paginate(15);
            
        return view('app.presence.historique', compact(
            'presences', 
            'dateDebut', 
            'dateFin', 
            'heuresPresence',
            'retards'
        ));
    }

    /**
     * Affiche le tableau de bord des présences
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        $this->authorize('viewDashboard', Presence::class);
        
        $user = Auth::user();
        $employeur = $user->employeur;
        
        $date = $request->input('date') 
            ? Carbon::parse($request->input('date')) 
            : Carbon::today();
            
        $siteId = $request->input('site_id');
        $departementId = $request->input('departement_id');
        
        $sites = Site::where('employeur_id', $employeur->id)
            ->orderBy('nom')
            ->get();
            
        $departements = $employeur->departements()
            ->orderBy('nom')
            ->get();
            
        $presencesJour = Presence::where('employeur_id', $employeur->id)
            ->whereDate('date_heure', $date)
            ->when($siteId, function($query) use ($siteId) {
                return $query->where('site_id', $siteId);
            })
            ->when($departementId, function($query) use ($departementId) {
                return $query->whereHas('user', function($q) use ($departementId) {
                    $q->where('departement_id', $departementId);
                });
            })
            ->orderBy('date_heure', 'desc')
            ->get();
            
        $presencesParEmploye = $presencesJour->groupBy('user_id');
        
        $statistiques = [];
        if ($departementId) {
            $statistiques = $this->presenceService->getStatistiquesDepartement($departementId, $date);
        } else {
            // Statistiques globales
            $totalEmployes = $employeur->users()->count();
            $presents = 0;
            $absents = 0;
            $enPause = 0;
            $retards = 0;
            
            foreach ($employeur->users as $employe) {
                if ($this->presenceService->estPresent($employe)) {
                    $presents++;
                } elseif ($this->presenceService->estEnPause($employe)) {
                    $enPause++;
                } else {
                    $absents++;
                }
                
                // Vérifier les retards
                $retardInfo = $this->presenceService->calculerRetards($employe, $date->copy()->startOfDay(), $date->copy()->endOfDay());
                if (!empty($retardInfo['details_jours'][$date->format('Y-m-d')])) {
                    $retards++;
                }
            }
            
            $statistiques = [
                'total_employes' => $totalEmployes,
                'presents' => $presents,
                'absents' => $absents,
                'en_pause' => $enPause,
                'retards' => $retards,
                'taux_presence' => $totalEmployes > 0 ? round(($presents + $enPause) / $totalEmployes * 100, 2) : 0,
                'taux_retard' => $totalEmployes > 0 ? round($retards / $totalEmployes * 100, 2) : 0
            ];
        }
        
        return view('app.presence.dashboard', compact(
            'presencesParEmploye',
            'date',
            'sites',
            'departements',
            'siteId',
            'departementId',
            'statistiques'
        ));
    }

    /**
     * Affiche la page de validation des pointages
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function validation(Request $request)
    {
        $this->authorize('valider', Presence::class);
        
        $user = Auth::user();
        $employeur = $user->employeur;
        
        $dateDebut = $request->input('date_debut') 
            ? Carbon::parse($request->input('date_debut')) 
            : Carbon::now()->subDays(7);
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::parse($request->input('date_fin'))->endOfDay() 
            : Carbon::now()->endOfDay();
            
        $siteId = $request->input('site_id');
        $userId = $request->input('user_id');
        $statut = $request->input('statut');
        
        $sites = Site::where('employeur_id', $employeur->id)
            ->orderBy('nom')
            ->get();
            
        $employes = $employeur->users()
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();
            
        $presences = Presence::where('employeur_id', $employeur->id)
            ->whereBetween('date_heure', [$dateDebut, $dateFin])
            ->when($siteId, function($query) use ($siteId) {
                return $query->where('site_id', $siteId);
            })
            ->when($userId, function($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->when($statut, function($query) use ($statut) {
                if ($statut === 'valide') {
                    return $query->whereNotNull('validateur_id');
                } elseif ($statut === 'non_valide') {
                    return $query->whereNull('validateur_id')->where('statut', '!=', 'annule');
                } elseif ($statut === 'annule') {
                    return $query->where('statut', 'annule');
                }
            })
            ->orderBy('date_heure', 'desc')
            ->paginate(20);
            
        return view('app.presence.validation', compact(
            'presences',
            'dateDebut',
            'dateFin',
            'sites',
            'employes',
            'siteId',
            'userId',
            'statut'
        ));
    }

    /**
     * Affiche la carte des présences
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function carte(Request $request)
    {
        $this->authorize('viewCarte', Presence::class);
        
        $user = Auth::user();
        $employeur = $user->employeur;
        
        $siteId = $request->input('site_id');
        $departementId = $request->input('departement_id');
        
        $sites = Site::where('employeur_id', $employeur->id)
            ->orderBy('nom')
            ->get();
            
        $departements = $employeur->departements()
            ->orderBy('nom')
            ->get();
            
        // Récupérer les dernières présences de tous les employés
        $dernieresPresences = $this->presenceService->getDernieresPresencesEmployes(
            $employeur,
            $siteId
        );
        
        if ($departementId) {
            $dernieresPresences = $dernieresPresences->filter(function($presence) use ($departementId) {
                return $presence->user->departement_id == $departementId;
            });
        }
        
        // Filtrer pour n'avoir que les présences avec géolocalisation
        $presencesAvecGeo = $dernieresPresences->filter(function($presence) {
            return $presence->latitude && $presence->longitude;
        });
        
        return view('app.presence.carte', compact(
            'presencesAvecGeo',
            'sites',
            'departements',
            'siteId',
            'departementId'
        ));
    }

    /**
     * Affiche les détails d'une présence
     *
     * @param  \App\Models\Presence  $presence
     * @return \Illuminate\View\View
     */
    public function show(Presence $presence)
    {
        return view('app.presence.show', compact('presence'));
    }

    /**
     * Valide une présence
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Presence  $presence
     * @return \Illuminate\Http\Response
     */
    public function valider(Request $request, Presence $presence)
    {
        $this->authorize('valider', $presence);
        
        $request->validate([
            'commentaire' => 'nullable|string|max:500',
        ]);
        
        $this->presenceService->validerPresence(
            $presence, 
            Auth::user(), 
            $request->input('commentaire')
        );
        
        return redirect()->back()
            ->with('success', 'La présence a été validée avec succès.');
    }

    /**
     * Annule une présence
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Presence  $presence
     * @return \Illuminate\Http\Response
     */
    public function annuler(Request $request, Presence $presence)
    {
        $this->authorize('annuler', $presence);
        
        $request->validate([
            'commentaire' => 'required|string|max:500',
        ]);
        
        $this->presenceService->annulerPresence(
            $presence, 
            $request->input('commentaire')
        );
        
        return redirect()->back()
            ->with('success', 'La présence a été annulée avec succès.');
    }
}
