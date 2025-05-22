<?php

namespace App\Services\Visite;

use App\Models\Visiteur;
use App\Models\Visite;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class VisiteService
{
    protected $visiteurService;
    protected $ticketService;

    public function __construct(VisiteurService $visiteurService, TicketService $ticketService)
    {
        $this->visiteurService = $visiteurService;
        $this->ticketService = $ticketService;
    }

    /**
     * Crée une nouvelle visite.
     *
     * @param array $visiteurData Données du visiteur
     * @param array $visiteData Données de la visite
     * @return Visite
     */
    public function createVisite(array $visiteurData, array $visiteData)
    {
        try {
            DB::beginTransaction();

            // Les IDs sont maintenant des UUIDs, nous n'avons plus besoin de les convertir en entiers

            // Vérifier si le visiteur existe déjà dans cette entreprise
            $visiteur = null;
            if (!empty($visiteurData['telephone']) && !empty($visiteurData['entreprise_id'])) {
                $visiteur = $this->visiteurService->findVisiteurByTelephone($visiteurData['telephone'], $visiteurData['entreprise_id']);
            }
            
            if (!$visiteur && !empty($visiteurData)) {
                // Si le visiteur n'existe pas, le créer
                Log::info('Création d\'un nouveau visiteur pour la visite: ' . json_encode($visiteurData));
                $visiteur = $this->visiteurService->createVisiteur($visiteurData);
            }

            // Créer la visite associée au visiteur
            if ($visiteur) {
                $visiteData['visiteur_id'] = $visiteur->id;
            }
            
            $visiteData['date_arrivee'] = $visiteData['date_arrivee'] ?? now();
            $visiteData['statut'] = $visiteData['statut'] ?? 'en_cours';
            
            // La génération d'UUID est maintenant gérée automatiquement par le trait HasUuids
            
            Log::info('Création de la visite: ' . json_encode($visiteData));
            $visite = Visite::create($visiteData);
            Log::info('Visite créée avec succès: ' . $visite->id);
            
            DB::commit();
            
            // Générer et imprimer automatiquement le ticket de visite
            try {
                $printSuccess = $this->ticketService->generateAndPrintTicket($visite);
                Log::info('Ticket généré et impression lancée', [
                    'visite_id' => $visite->id,
                    'print_success' => $printSuccess
                ]);
            } catch (\Exception $e) {
                // Ne pas bloquer le processus si l'impression échoue
                Log::error('Erreur lors de la génération ou impression du ticket: ' . $e->getMessage());
            }
            
            return $visite;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la visite: ' . $e->getMessage() . '\nTrace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Met à jour une visite existante.
     *
     * @param Visite $visite
     * @param array $data
     * @return Visite
     */
    public function updateVisite(Visite $visite, array $data)
    {
        try {
            DB::beginTransaction();
            
            $visite->update($data);
            
            DB::commit();
            return $visite;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la visite: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Termine une visite.
     *
     * @param Visite $visite
     * @param \DateTime|null $dateDepart
     * @return Visite
     */
    public function terminerVisite(Visite $visite, $dateDepart = null)
    {
        $data = [
            'statut' => 'terminee',
            'date_depart' => $dateDepart ?? now()
        ];
        
        return $this->updateVisite($visite, $data);
    }

    /**
     * Annule une visite.
     *
     * @param Visite $visite
     * @param string|null $commentaires
     * @return Visite
     */
    public function annulerVisite(Visite $visite, $commentaires = null)
    {
        $data = [
            'statut' => 'annulee'
        ];
        
        if ($commentaires) {
            $data['commentaires'] = $commentaires;
        }
        
        return $this->updateVisite($visite, $data);
    }

    /**
     * Récupère les visites en cours pour un site.
     *
     * @param Site $site
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVisitesEnCoursPourSite(Site $site)
    {
        return Visite::enCours()->parSite($site->id)->with('visiteur')->get();
    }

    /**
     * Récupère les visites pour un jour spécifique pour un site.
     *
     * @param Site $site
     * @param string $date Format Y-m-d
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVisitesPourJourEtSite(Site $site, $date)
    {
        return Visite::parSite($site->id)
            ->parDate($date)
            ->with('visiteur')
            ->orderBy('date_arrivee')
            ->get();
    }

    /**
     * Récupère l'historique des visites d'un visiteur.
     *
     * @param Visiteur $visiteur
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistoriqueVisitesVisiteur(Visiteur $visiteur)
    {
        return Visite::parVisiteur($visiteur->id)
            ->with(['site'])
            ->orderBy('date_arrivee', 'desc')
            ->get();
    }

    /**
     * Récupère les statistiques des visites pour une entreprise.
     *
     * @param int $entrepriseId
     * @param string $debut Format Y-m-d
     * @param string $fin Format Y-m-d
     * @return array
     */
    public function getStatistiquesVisites($entrepriseId, $debut, $fin)
    {
        $visites = Visite::parEntreprise($entrepriseId)
            ->parPeriode($debut, $fin)
            ->get();
        
        $stats = [
            'total' => $visites->count(),
            'en_cours' => $visites->where('statut', 'en_cours')->count(),
            'terminees' => $visites->where('statut', 'terminee')->count(),
            'annulees' => $visites->where('statut', 'annulee')->count(),
            'par_site' => [],
            'par_jour' => [],
        ];
        
        // Statistiques par site
        $parSite = $visites->groupBy('site_id');
        foreach ($parSite as $siteId => $siteVisites) {
            $site = Site::find($siteId);
            $stats['par_site'][$siteId] = [
                'nom' => $site ? $site->nom : 'Site inconnu',
                'total' => $siteVisites->count()
            ];
        }
        
        // Statistiques par jour
        $parJour = $visites->groupBy(function($visite) {
            return $visite->date_arrivee->format('Y-m-d');
        });
        
        foreach ($parJour as $jour => $jourVisites) {
            $stats['par_jour'][$jour] = $jourVisites->count();
        }
        
        return $stats;
    }
}
