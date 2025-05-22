<?php

namespace App\Services;

use App\Models\Conge;
use App\Models\TypeConge;
use App\Models\User;
use App\Models\Departement;
use App\Models\Site;
use App\Models\CongeAjustement;
use App\Models\CongeSolde;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CongeRapportService
{
    /**
     * Récupère les données pour le rapport de synthèse
     * 
     * @param array $filters Les filtres appliqués
     * @return array
     */
    public function getSummaryData(array $filters): array
    {
        // Appliquer les filtres à la requête de base
        $query = $this->applyFilters(Conge::query(), $filters);
        
        // Récupérer les statistiques de base
        $totalConges = $query->count();
        $joursPris = $query->sum('duree');
        $congesApprouves = $query->where('statut', 'approuve')->count();
        $congesEnAttente = $query->where('statut', 'en_attente')->count();
        
        // Récupérer la répartition par statut
        $repartitionStatut = [
            'approuve' => $query->where('statut', 'approuve')->count(),
            'en_attente' => $query->where('statut', 'en_attente')->count(),
            'refuse' => $query->where('statut', 'refuse')->count(),
            'annule' => $query->where('statut', 'annule')->count(),
        ];
        
        // Récupérer les congés récents
        $congesRecents = $this->getRecentLeaves($filters);
        
        return [
            'totalConges' => $totalConges,
            'joursPris' => $joursPris,
            'congesApprouves' => $congesApprouves,
            'congesEnAttente' => $congesEnAttente,
            'repartitionStatut' => $repartitionStatut,
            'congesRecents' => $congesRecents,
        ];
    }
    
    /**
     * Récupère les données pour le rapport par département
     * 
     * @param array $filters Les filtres appliqués
     * @return array
     */
    public function getDepartmentData(array $filters): array
    {
        // Récupérer les départements
        $departements = Departement::all();
        
        // Initialiser les tableaux de résultats
        $repartition = [];
        $details = [];
        
        // Pour chaque département, récupérer les statistiques
        foreach ($departements as $departement) {
            // Appliquer les filtres à la requête de base
            $filtersWithDept = array_merge($filters, ['departement_id' => $departement->id]);
            $query = $this->applyFilters(Conge::query(), $filtersWithDept);
            
            // Récupérer les statistiques
            $conges = $query->count();
            $jours = $query->sum('duree');
            $employes = User::whereHas('departements', function ($q) use ($departement) {
                $q->where('departements.id', $departement->id);
            })->count();
            
            // Calculer la moyenne
            $moyenne = $employes > 0 ? $jours / $employes : 0;
            
            // Ajouter au tableau de répartition
            $repartition[] = [
                'nom' => $departement->nom,
                'jours' => $jours,
            ];
            
            // Ajouter au tableau de détails
            $details[] = [
                'nom' => $departement->nom,
                'employes' => $employes,
                'conges' => $conges,
                'jours' => $jours,
                'moyenne' => $moyenne,
            ];
        }
        
        return [
            'repartition' => $repartition,
            'details' => $details,
        ];
    }
    
    /**
     * Récupère les données pour le rapport par type de congé
     * 
     * @param array $filters Les filtres appliqués
     * @return array
     */
    public function getTypeData(array $filters): array
    {
        // Récupérer les types de congés
        $typesConge = TypeConge::all();
        
        // Initialiser les tableaux de résultats
        $repartition = [];
        $details = [];
        
        // Récupérer le total des jours pour calculer les pourcentages
        $totalJours = $this->applyFilters(Conge::query(), $filters)->sum('duree');
        
        // Pour chaque type de congé, récupérer les statistiques
        foreach ($typesConge as $type) {
            // Appliquer les filtres à la requête de base
            $filtersWithType = array_merge($filters, ['type_conge_id' => $type->id]);
            $query = $this->applyFilters(Conge::query(), $filtersWithType);
            
            // Récupérer les statistiques
            $conges = $query->count();
            $jours = $query->sum('duree');
            
            // Calculer la durée moyenne et le pourcentage
            $dureeMoyenne = $conges > 0 ? $jours / $conges : 0;
            $pourcentage = $totalJours > 0 ? ($jours / $totalJours) * 100 : 0;
            
            // Ajouter au tableau de répartition
            $repartition[] = [
                'nom' => $type->libelle,
                'jours' => $jours,
            ];
            
            // Ajouter au tableau de détails
            $details[] = [
                'nom' => $type->libelle,
                'conges' => $conges,
                'jours' => $jours,
                'duree_moyenne' => $dureeMoyenne,
                'pourcentage' => $pourcentage,
            ];
        }
        
        return [
            'repartition' => $repartition,
            'details' => $details,
        ];
    }
    
    /**
     * Récupère les données pour le rapport par employé
     * 
     * @param array $filters Les filtres appliqués
     * @return array
     */
    public function getEmployeeData(array $filters): array
    {
        // Récupérer les employés
        $employes = User::whereHas('conges', function ($query) use ($filters) {
            $this->applyFilters($query, $filters);
        })->get();
        
        // Initialiser le tableau de détails
        $details = [];
        
        // Pour chaque employé, récupérer les statistiques
        foreach ($employes as $employe) {
            // Appliquer les filtres à la requête de base
            $filtersWithEmployee = array_merge($filters, ['employe_id' => $employe->id]);
            $query = $this->applyFilters(Conge::query(), $filtersWithEmployee);
            
            // Récupérer les statistiques
            $conges = $query->count();
            $jours = $query->sum('duree');
            
            // Récupérer le département principal
            $departement = $employe->departements->first();
            $departementNom = $departement ? $departement->nom : 'Non assigné';
            
            // Récupérer le solde de congés
            $soldeTotal = CongeSolde::where('user_id', $employe->id)->sum('solde');
            $soldeUtilise = Conge::where('user_id', $employe->id)
                ->where('statut', 'approuve')
                ->sum('duree');
            $soldeRestant = $soldeTotal - $soldeUtilise;
            
            // Calculer le taux d'utilisation
            $tauxUtilisation = $soldeTotal > 0 ? ($soldeUtilise / $soldeTotal) * 100 : 0;
            
            // Ajouter au tableau de détails
            $details[] = [
                'nom' => $employe->nom . ' ' . $employe->prenom,
                'departement' => $departementNom,
                'conges' => $conges,
                'jours' => $jours,
                'solde_restant' => $soldeRestant,
                'taux_utilisation' => $tauxUtilisation,
            ];
        }
        
        return [
            'details' => $details,
        ];
    }
    
    /**
     * Récupère les données pour le rapport de chronologie
     * 
     * @param array $filters Les filtres appliqués
     * @return array
     */
    public function getTimelineData(array $filters): array
    {
        // Déterminer la période de regroupement
        $periode = $filters['periode'] ?? 'mois';
        
        // Formater la date en fonction de la période
        $format = $this->getDateFormat($periode);
        
        // Récupérer les données regroupées par période
        $query = $this->applyFilters(Conge::query(), $filters);
        
        $repartition = $query
            ->select(DB::raw("DATE_FORMAT(date_debut, '{$format}') as periode"), DB::raw('SUM(duree) as jours'))
            ->groupBy('periode')
            ->orderBy('date_debut')
            ->get()
            ->map(function ($item) {
                return [
                    'periode' => $item->periode,
                    'jours' => (float) $item->jours,
                ];
            })
            ->toArray();
        
        // Récupérer la répartition mensuelle
        $repartitionMensuelle = $this->getMonthlyDistribution($filters);
        
        return [
            'repartition' => $repartition,
            'repartitionMensuelle' => $repartitionMensuelle,
        ];
    }
    
    /**
     * Récupère les congés récents
     * 
     * @param array $filters Les filtres appliqués
     * @return array
     */
    private function getRecentLeaves(array $filters): array
    {
        // Appliquer les filtres à la requête de base
        $query = $this->applyFilters(Conge::query(), $filters);
        
        // Récupérer les congés récents
        $conges = $query
            ->with(['user', 'typeConge'])
            ->orderBy('date_debut', 'desc')
            ->limit(5)
            ->get();
        
        // Formater les données
        return $conges->map(function ($conge) {
            return [
                'employe' => $conge->user->nom . ' ' . $conge->user->prenom,
                'type' => $conge->typeConge->libelle,
                'periode' => Carbon::parse($conge->date_debut)->format('d/m/Y') . ' - ' . Carbon::parse($conge->date_fin)->format('d/m/Y'),
                'duree' => $conge->duree,
                'statut' => $conge->statut,
            ];
        })->toArray();
    }
    
    /**
     * Récupère la répartition mensuelle des congés
     * 
     * @param array $filters Les filtres appliqués
     * @return array
     */
    private function getMonthlyDistribution(array $filters): array
    {
        // Initialiser le tableau de résultats
        $distribution = [
            'jours' => array_fill(1, 12, 0),
            'conges' => array_fill(1, 12, 0),
        ];
        
        // Appliquer les filtres à la requête de base
        $query = $this->applyFilters(Conge::query(), $filters);
        
        // Récupérer les données par mois
        $conges = $query->get();
        
        // Pour chaque congé, ajouter les jours aux mois correspondants
        foreach ($conges as $conge) {
            $dateDebut = Carbon::parse($conge->date_debut);
            $dateFin = Carbon::parse($conge->date_fin);
            
            // Si le congé est sur un seul mois
            if ($dateDebut->month === $dateFin->month && $dateDebut->year === $dateFin->year) {
                $distribution['jours'][$dateDebut->month] += $conge->duree;
                $distribution['conges'][$dateDebut->month] += 1;
            } else {
                // Si le congé s'étend sur plusieurs mois, répartir les jours
                $currentDate = $dateDebut->copy();
                
                while ($currentDate->lte($dateFin)) {
                    $month = $currentDate->month;
                    $daysInMonth = $currentDate->daysInMonth;
                    
                    // Calculer le nombre de jours dans ce mois
                    $startDay = $currentDate->day;
                    $endDay = min($daysInMonth, $dateFin->day);
                    $daysCount = $endDay - $startDay + 1;
                    
                    // Ajouter les jours au mois
                    $distribution['jours'][$month] += $daysCount;
                    
                    // Incrémenter le nombre de congés pour ce mois
                    $distribution['conges'][$month] += 1;
                    
                    // Passer au mois suivant
                    $currentDate->addMonth()->startOfMonth();
                }
            }
        }
        
        return $distribution;
    }
    
    /**
     * Applique les filtres à la requête
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query La requête à filtrer
     * @param array $filters Les filtres à appliquer
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyFilters($query, array $filters)
    {
        // Filtre par date
        if (isset($filters['date_debut']) && !empty($filters['date_debut'])) {
            $query->where('date_debut', '>=', $filters['date_debut']);
        }
        
        if (isset($filters['date_fin']) && !empty($filters['date_fin'])) {
            $query->where('date_fin', '<=', $filters['date_fin']);
        }
        
        // Filtre par département
        if (isset($filters['departement_id']) && !empty($filters['departement_id'])) {
            $query->whereHas('user.departements', function ($q) use ($filters) {
                $q->where('departements.id', $filters['departement_id']);
            });
        }
        
        // Filtre par site
        if (isset($filters['site_id']) && !empty($filters['site_id'])) {
            $query->whereHas('user.sites', function ($q) use ($filters) {
                $q->where('sites.id', $filters['site_id']);
            });
        }
        
        // Filtre par type de congé
        if (isset($filters['type_conge_id']) && !empty($filters['type_conge_id'])) {
            $query->where('type_conge_id', $filters['type_conge_id']);
        }
        
        // Filtre par statut
        if (isset($filters['statut']) && !empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }
        
        // Filtre par employé
        if (isset($filters['employe_id']) && !empty($filters['employe_id'])) {
            $query->where('user_id', $filters['employe_id']);
        }
        
        return $query;
    }
    
    /**
     * Récupère le format de date en fonction de la période
     * 
     * @param string $periode La période de regroupement
     * @return string
     */
    private function getDateFormat(string $periode): string
    {
        switch ($periode) {
            case 'semaine':
                return '%Y-%u'; // Année-Semaine
            case 'mois':
                return '%Y-%m'; // Année-Mois
            case 'trimestre':
                return '%Y-%c'; // Année-Trimestre
            case 'annee':
                return '%Y'; // Année
            default:
                return '%Y-%m'; // Par défaut: Année-Mois
        }
    }
    
    /**
     * Exporte les données du rapport au format spécifié
     * 
     * @param array $data Les données à exporter
     * @param string $format Le format d'export (pdf, excel, csv)
     * @param array $options Options supplémentaires
     * @return mixed
     */
    public function exportReport(array $data, string $format, array $options = [])
    {
        // Implémenter l'export en fonction du format
        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($data, $options);
            case 'excel':
                return $this->exportToExcel($data, $options);
            case 'csv':
                return $this->exportToCsv($data, $options);
            default:
                throw new \InvalidArgumentException("Format d'export non supporté: {$format}");
        }
    }
    
    /**
     * Exporte les données au format PDF
     * 
     * @param array $data Les données à exporter
     * @param array $options Options supplémentaires
     * @return mixed
     */
    private function exportToPdf(array $data, array $options = [])
    {
        // Implémenter l'export PDF
        // Cette méthode serait implémentée avec une bibliothèque comme DOMPDF
        
        // Exemple d'implémentation à compléter
        /*
        $pdf = PDF::loadView('app.conge.rapports.export.pdf', [
            'data' => $data,
            'options' => $options,
        ]);
        
        return $pdf->download('rapport-conges.pdf');
        */
        
        // Pour le moment, retourner un message
        return "Export PDF non implémenté";
    }
    
    /**
     * Exporte les données au format Excel
     * 
     * @param array $data Les données à exporter
     * @param array $options Options supplémentaires
     * @return mixed
     */
    private function exportToExcel(array $data, array $options = [])
    {
        // Implémenter l'export Excel
        // Cette méthode serait implémentée avec une bibliothèque comme Maatwebsite/Laravel-Excel
        
        // Exemple d'implémentation à compléter
        /*
        return Excel::download(new CongesExport($data), 'rapport-conges.xlsx');
        */
        
        // Pour le moment, retourner un message
        return "Export Excel non implémenté";
    }
    
    /**
     * Exporte les données au format CSV
     * 
     * @param array $data Les données à exporter
     * @param array $options Options supplémentaires
     * @return mixed
     */
    private function exportToCsv(array $data, array $options = [])
    {
        // Implémenter l'export CSV
        // Cette méthode serait implémentée avec une bibliothèque comme Maatwebsite/Laravel-Excel
        
        // Exemple d'implémentation à compléter
        /*
        return Excel::download(new CongesExport($data), 'rapport-conges.csv', \Maatwebsite\Excel\Excel::CSV);
        */
        
        // Pour le moment, retourner un message
        return "Export CSV non implémenté";
    }
    
    /**
     * Récupère toutes les données pour tous les types de rapports
     * 
     * @param array $filters Les filtres appliqués
     * @return array
     */
    public function getAllReportData(array $filters): array
    {
        return [
            'summary' => $this->getSummaryData($filters),
            'byDepartment' => $this->getDepartmentData($filters),
            'byType' => $this->getTypeData($filters),
            'byEmployee' => $this->getEmployeeData($filters),
            'timeline' => $this->getTimelineData($filters),
        ];
    }
}
