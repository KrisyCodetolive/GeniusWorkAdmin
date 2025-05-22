<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\Presence\TempsPresenceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class RapportHeuresTravailController extends Controller
{
    /**
     * Service de calcul du temps de présence
     *
     * @var TempsPresenceService
     */
    protected $tempsPresenceService;

    /**
     * Constructeur
     *
     * @param TempsPresenceService $tempsPresenceService
     */
    public function __construct(TempsPresenceService $tempsPresenceService)
    {
        $this->tempsPresenceService = $tempsPresenceService;
    }

    /**
     * Affiche le formulaire de génération de rapport d'heures de travail
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer les sites de l'entreprise de l'utilisateur
        $sites = Site::where('entreprise_id', $user->entreprise_id)->get();
        
        return view('rapports.heures-travail.index', compact('sites'));
    }

    /**
     * Génère et affiche le rapport d'heures de travail
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function generer(Request $request)
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;
        
        // Valider les données du formulaire
        $validatedData = $request->validate([
            'type_rapport' => 'required|in:journalier,hebdomadaire,mensuel,personnalise',
            'site_id' => 'nullable|exists:sites,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);
        
        // Préparer les dates
        $dateDebut = Carbon::parse($validatedData['date_debut'])->startOfDay();
        $dateFin = Carbon::parse($validatedData['date_fin'])->endOfDay();
        
        // Si le type de rapport est journalier, s'assurer que la date de fin est la même que la date de début
        if ($validatedData['type_rapport'] === 'journalier') {
            $dateFin = Carbon::parse($validatedData['date_debut'])->endOfDay();
        }
        
        // Si le type de rapport est hebdomadaire, ajuster les dates pour couvrir toute la semaine
        if ($validatedData['type_rapport'] === 'hebdomadaire') {
            $dateDebut = Carbon::parse($validatedData['date_debut'])->startOfWeek()->startOfDay();
            $dateFin = Carbon::parse($validatedData['date_debut'])->endOfWeek()->endOfDay();
        }
        
        // Si le type de rapport est mensuel, ajuster les dates pour couvrir tout le mois
        if ($validatedData['type_rapport'] === 'mensuel') {
            $dateDebut = Carbon::parse($validatedData['date_debut'])->startOfMonth()->startOfDay();
            $dateFin = Carbon::parse($validatedData['date_debut'])->endOfMonth()->endOfDay();
        }
        
        // Récupérer le site si spécifié
        $site = null;
        if (!empty($validatedData['site_id'])) {
            $site = Site::find($validatedData['site_id']);
        }
        
        // Générer le rapport
        $rapport = $this->tempsPresenceService->genererRapportHeuresTravail(
            $dateDebut,
            $dateFin,
            (int) $entreprise->id,
            $site ? (int) $site->id : null
        );
        
        // Ajouter des informations supplémentaires
        $data = array_merge($rapport, [
            'typeRapport' => $validatedData['type_rapport'],
            'entreprise' => $entreprise,
            'site' => $site,
        ]);
        
        // Stocker les données en session pour l'export PDF
        session(['rapport_heures_travail_data' => $data]);
        
        return view('rapports.heures-travail.rapport', $data);
    }

    /**
     * Exporte le rapport au format PDF
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exporterPdf(Request $request)
    {
        // Valider les paramètres
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'site_id' => 'nullable|exists:sites,id',
        ]);
        
        $user = Auth::user();
        $entreprise = $user->entreprise;
        
        // Préparer les dates
        $dateDebut = Carbon::parse($request->date_debut)->startOfDay();
        $dateFin = Carbon::parse($request->date_fin)->endOfDay();
        
        // Récupérer le site si spécifié
        $site = null;
        if ($request->site_id) {
            $site = Site::find($request->site_id);
        }
        
        // Générer le rapport
        $rapport = $this->tempsPresenceService->genererRapportHeuresTravail(
            $dateDebut,
            $dateFin,
            (int) $entreprise->id,
            $site ? (int) $site->id : null
        );
        
        
        // Générer le PDF
        $pdf = PDF::loadView('rapports.heures-travail.pdf', array_merge($rapport, [
            'entreprise' => $entreprise
        ]));
        
        // Définir le nom du fichier
        $fileName = 'rapport_heures_travail_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.pdf';
        
        // Retourner le PDF pour téléchargement
        return $pdf->download($fileName);
    }
}
