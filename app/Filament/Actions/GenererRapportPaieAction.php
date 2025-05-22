<?php

namespace App\Filament\Actions;

use App\Models\Paie\BulletinPaie;
use App\Models\Employeur;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GenererRapportPaieAction
{
    // Format personnalisé pour la monnaie FCFA
    const FORMAT_CURRENCY_XOF = '#,##0 "FCFA"';
    /**
     * Crée une action Filament pour générer un rapport de paie avec statistiques
     *
     * @return \Filament\Actions\Action
     */
    public static function make(): Action
    {
        return Action::make('generer_rapport_paie')
            ->label('Rapport de paie')
            ->icon('heroicon-o-chart-bar')
            ->color('success')
            ->tooltip('Générer un rapport d\'analyse de la paie')
            ->form([
                Forms\Components\Select::make('periode')
                    ->label('Période d\'analyse')
                    ->options([
                        'mois_courant' => 'Mois courant',
                        'mois_precedent' => 'Mois précédent',
                        'trimestre' => 'Trimestre en cours',
                        'annee' => 'Année en cours',
                        'personnalise' => 'Période personnalisée',
                    ])
                    ->default('mois_courant')
                    ->required()
                    ->reactive(),
                    
                Forms\Components\DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->visible(fn (Forms\Get $get) => $get('periode') === 'personnalise')
                    ->required(fn (Forms\Get $get) => $get('periode') === 'personnalise'),
                    
                Forms\Components\DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->visible(fn (Forms\Get $get) => $get('periode') === 'personnalise')
                    ->required(fn (Forms\Get $get) => $get('periode') === 'personnalise'),
                    
                Forms\Components\Toggle::make('inclure_graphiques')
                    ->label('Inclure des graphiques')
                    ->helperText('Ajouter des graphiques visuels au rapport')
                    ->default(true),
                    
                Forms\Components\Toggle::make('details_par_employe')
                    ->label('Détails par employé')
                    ->helperText('Inclure une analyse détaillée par employé')
                    ->default(true),
                    
                Forms\Components\Toggle::make('comparaison_periodes')
                    ->label('Comparaison avec périodes précédentes')
                    ->helperText('Comparer avec les périodes précédentes')
                    ->default(false),
            ])
            ->requiresConfirmation()
            ->modalHeading('Générer un rapport d\'analyse de la paie')
            ->modalDescription('Ce rapport contiendra des statistiques détaillées sur la masse salariale, les indemnités, les primes et d\'autres indicateurs clés.')
            ->modalSubmitActionLabel('Générer le rapport')
            ->action(function (array $data) {
                try {
                    // Journaliser le début de l'opération
                    Log::info('Démarrage de la génération du rapport de paie', [
                        'periode' => $data['periode'],
                        'options' => $data,
                    ]);
                    
                    // Récupérer l'ID de l'entreprise
                    $entrepriseId = Auth::user()->entreprise_id;
                    
                    // Déterminer la période d'analyse
                    $now = Carbon::now();
                    $dateDebut = null;
                    $dateFin = null;
                    $titrePeriode = '';
                    
                    switch ($data['periode']) {
                        case 'mois_courant':
                            $dateDebut = $now->copy()->startOfMonth();
                            $dateFin = $now->copy()->endOfMonth();
                            $titrePeriode = 'Mois de ' . $dateDebut->translatedFormat('F Y');
                            break;
                        case 'mois_precedent':
                            $dateDebut = $now->copy()->subMonth()->startOfMonth();
                            $dateFin = $now->copy()->subMonth()->endOfMonth();
                            $titrePeriode = 'Mois de ' . $dateDebut->translatedFormat('F Y');
                            break;
                        case 'trimestre':
                            $dateDebut = $now->copy()->startOfQuarter();
                            $dateFin = $now->copy()->endOfQuarter();
                            $titrePeriode = $dateDebut->quarter . 'ème trimestre ' . $dateDebut->year;
                            break;
                        case 'annee':
                            $dateDebut = $now->copy()->startOfYear();
                            $dateFin = $now->copy()->endOfYear();
                            $titrePeriode = 'Année ' . $dateDebut->year;
                            break;
                        case 'personnalise':
                            $dateDebut = Carbon::parse($data['date_debut']);
                            $dateFin = Carbon::parse($data['date_fin']);
                            $titrePeriode = 'Du ' . $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y');
                            break;
                    }
                    
                    // Récupérer les bulletins de paie pour la période
                    $bulletins = BulletinPaie::where('entreprise_id', $entrepriseId)
                        ->where('statut', 'validé')
                        ->whereBetween('periode_fin', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
                        ->with('employeur')
                        ->get();
                    
                    // Récupérer tous les employés actifs
                    $employes = Employeur::where('entreprise_id', $entrepriseId)
                        ->where('statut', 'actif')
                        ->get();
                    
                    // Créer un nouveau spreadsheet
                    $spreadsheet = new Spreadsheet();
                    
                    // Configurer la feuille de résumé
                    $resumeSheet = $spreadsheet->getActiveSheet();
                    $resumeSheet->setTitle('Résumé');
                    
                    // Ajouter le titre du rapport
                    $resumeSheet->setCellValue('A1', 'RAPPORT D\'ANALYSE DE LA PAIE');
                    $resumeSheet->setCellValue('A2', $titrePeriode);
                    $resumeSheet->mergeCells('A1:H1');
                    $resumeSheet->mergeCells('A2:H2');
                    $resumeSheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                    $resumeSheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                    $resumeSheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Calculer les indicateurs clés
                    $totalSalairesBruts = $bulletins->sum('salaire_brut');
                    $totalSalairesNets = $bulletins->sum('salaire_net');
                    $totalIndemnites = $bulletins->sum('total_indemnites');
                    $totalPrimes = $bulletins->sum('total_primes');
                    $totalRetenues = $bulletins->sum('total_retenues');
                    $totalChargesPatronales = $bulletins->sum('charges_patronales');
                    $nombreBulletins = $bulletins->count();
                    $nombreEmployes = $employes->count();
                    $nombreEmployesPayes = $bulletins->pluck('employeur_id')->unique()->count();
                    
                    // Calculer les moyennes
                    $salaireBrutMoyen = $nombreBulletins > 0 ? $totalSalairesBruts / $nombreBulletins : 0;
                    $salaireNetMoyen = $nombreBulletins > 0 ? $totalSalairesNets / $nombreBulletins : 0;
                    $indemniteMoyenne = $nombreBulletins > 0 ? $totalIndemnites / $nombreBulletins : 0;
                    $primeMoyenne = $nombreBulletins > 0 ? $totalPrimes / $nombreBulletins : 0;
                    
                    // Ajouter les indicateurs clés
                    $resumeSheet->setCellValue('A4', 'INDICATEURS CLÉS');
                    $resumeSheet->getStyle('A4')->getFont()->setBold(true);
                    
                    $indicateurs = [
                        ['Masse salariale brute', $totalSalairesBruts, self::FORMAT_CURRENCY_XOF],
                        ['Masse salariale nette', $totalSalairesNets, self::FORMAT_CURRENCY_XOF],
                        ['Total indemnités', $totalIndemnites, self::FORMAT_CURRENCY_XOF],
                        ['Total primes', $totalPrimes, self::FORMAT_CURRENCY_XOF],
                        ['Total retenues', $totalRetenues, self::FORMAT_CURRENCY_XOF],
                        ['Total charges patronales', $totalChargesPatronales, self::FORMAT_CURRENCY_XOF],
                        ['Nombre de bulletins', $nombreBulletins, NumberFormat::FORMAT_NUMBER],
                        ['Nombre d\'employés payés', $nombreEmployesPayes, NumberFormat::FORMAT_NUMBER],
                        ['Nombre total d\'employés', $nombreEmployes, NumberFormat::FORMAT_NUMBER],
                        ['Taux de couverture', $nombreEmployes > 0 ? $nombreEmployesPayes / $nombreEmployes * 100 : 0, NumberFormat::FORMAT_PERCENTAGE_00],
                    ];
                    
                    $row = 5;
                    foreach ($indicateurs as $indicateur) {
                        $resumeSheet->setCellValue('A' . $row, $indicateur[0]);
                        $resumeSheet->setCellValue('B' . $row, $indicateur[1]);
                        $resumeSheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode($indicateur[2]);
                        $row++;
                    }
                    
                    // Ajouter les moyennes
                    $resumeSheet->setCellValue('A' . ($row + 1), 'MOYENNES');
                    $resumeSheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);
                    
                    $moyennes = [
                        ['Salaire brut moyen', $salaireBrutMoyen, self::FORMAT_CURRENCY_XOF],
                        ['Salaire net moyen', $salaireNetMoyen, self::FORMAT_CURRENCY_XOF],
                        ['Indemnité moyenne', $indemniteMoyenne, self::FORMAT_CURRENCY_XOF],
                        ['Prime moyenne', $primeMoyenne, self::FORMAT_CURRENCY_XOF],
                    ];
                    
                    $row += 2;
                    foreach ($moyennes as $moyenne) {
                        $resumeSheet->setCellValue('A' . $row, $moyenne[0]);
                        $resumeSheet->setCellValue('B' . $row, $moyenne[1]);
                        $resumeSheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode($moyenne[2]);
                        $row++;
                    }
                    
                    // Ajouter la répartition par département si disponible
                    $row += 2;
                    $resumeSheet->setCellValue('A' . $row, 'RÉPARTITION PAR DÉPARTEMENT');
                    $resumeSheet->getStyle('A' . $row)->getFont()->setBold(true);
                    $row++;
                    
                    $resumeSheet->setCellValue('A' . $row, 'Département');
                    $resumeSheet->setCellValue('B' . $row, 'Nombre d\'employés');
                    $resumeSheet->setCellValue('C' . $row, 'Masse salariale brute');
                    $resumeSheet->setCellValue('D' . $row, 'Masse salariale nette');
                    $resumeSheet->setCellValue('E' . $row, '% de la masse salariale');
                    $resumeSheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
                    
                    // Regrouper par département
                    $departements = [];
                    foreach ($bulletins as $bulletin) {
                        // Récupérer le département de manière sécurisée
                        $departementBrut = $bulletin->employeur->departement ?? null;
                        
                        // Traiter le département selon son type
                        if (is_object($departementBrut) && method_exists($departementBrut, 'getNom')) {
                            // Si c'est un objet avec une méthode getNom (comme un modèle Departement)
                            $departement = $departementBrut->getNom();
                        } elseif (is_object($departementBrut) && isset($departementBrut->nom)) {
                            // Si c'est un objet avec une propriété nom
                            $departement = $departementBrut->nom;
                        } else {
                            // Convertir en chaîne de caractères ou utiliser une valeur par défaut
                            $departement = $departementBrut ? (string)$departementBrut : 'Non spécifié';
                        }
                        
                        // Utiliser une clé de tableau sécurisée
                        $departementKey = trim($departement);
                        if (empty($departementKey)) {
                            $departementKey = 'Non spécifié';
                        }
                        
                        // Initialiser le tableau pour ce département s'il n'existe pas encore
                        if (!isset($departements[$departementKey])) {
                            $departements[$departementKey] = [
                                'count' => 0,
                                'brut' => 0,
                                'net' => 0,
                            ];
                        }
                        
                        // Ajouter les données du bulletin
                        $departements[$departementKey]['count']++;
                        $departements[$departementKey]['brut'] += $bulletin->salaire_brut;
                        $departements[$departementKey]['net'] += $bulletin->salaire_net;
                    }
                    
                    $row++;
                    $departementStartRow = $row;
                    foreach ($departements as $departement => $stats) {
                        $resumeSheet->setCellValue('A' . $row, $departement);
                        $resumeSheet->setCellValue('B' . $row, $stats['count']);
                        $resumeSheet->setCellValue('C' . $row, $stats['brut']);
                        $resumeSheet->setCellValue('D' . $row, $stats['net']);
                        $resumeSheet->setCellValue('E' . $row, $totalSalairesBruts > 0 ? $stats['brut'] / $totalSalairesBruts : 0);
                        
                        $resumeSheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode(self::FORMAT_CURRENCY_XOF);
                        $resumeSheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode(self::FORMAT_CURRENCY_XOF);
                        $resumeSheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
                        
                        $row++;
                    }
                    
                    // Ajouter une feuille pour les données de graphique (sans générer le graphique lui-même)
                    if ($data['inclure_graphiques'] && count($departements) > 0) {
                        // Créer une nouvelle feuille pour les données de graphique
                        $chartSheet = $spreadsheet->createSheet();
                        $chartSheet->setTitle('Données Graphiques');
                        
                        // Ajouter un message explicatif
                        $chartSheet->setCellValue('A1', 'DONNÉES POUR GRAPHIQUES');
                        $chartSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                        $chartSheet->mergeCells('A1:C1');
                        
                        // Ajouter les en-têtes
                        $chartSheet->setCellValue('A3', 'Département');
                        $chartSheet->setCellValue('B3', 'Masse salariale brute');
                        $chartSheet->setCellValue('C3', 'Pourcentage du total');
                        $chartSheet->getStyle('A3:C3')->getFont()->setBold(true);
                        
                        // Ajouter les données
                        $chartRow = 4;
                        foreach ($departements as $departement => $stats) {
                            $chartSheet->setCellValue('A' . $chartRow, $departement);
                            $chartSheet->setCellValue('B' . $chartRow, $stats['brut']);
                            $chartSheet->setCellValue('C' . $chartRow, $totalSalairesBruts > 0 ? $stats['brut'] / $totalSalairesBruts : 0);
                            
                            $chartSheet->getStyle('B' . $chartRow)->getNumberFormat()->setFormatCode(self::FORMAT_CURRENCY_XOF);
                            $chartSheet->getStyle('C' . $chartRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
                            
                            $chartRow++;
                        }
                        
                        // Auto-dimensionner les colonnes
                        foreach (range('A', 'C') as $col) {
                            $chartSheet->getColumnDimension($col)->setAutoSize(true);
                        }
                        
                        // Ajouter un message concernant les graphiques
                        $chartSheet->setCellValue('A' . ($chartRow + 2), 'Note: Les données sont présentées sous forme de tableau pour faciliter l\'analyse.');
                        $chartSheet->getStyle('A' . ($chartRow + 2))->getFont()->setItalic(true);
                        $chartSheet->mergeCells('A' . ($chartRow + 2) . ':C' . ($chartRow + 2));
                    }
                    
                    // Ajouter une feuille de détails par employé si demandé
                    if ($data['details_par_employe']) {
                        $detailsSheet = $spreadsheet->createSheet();
                        $detailsSheet->setTitle('Détails par employé');
                        
                        // En-têtes
                        $detailsSheet->setCellValue('A1', 'Employé');
                        $detailsSheet->setCellValue('B1', 'Département');
                        $detailsSheet->setCellValue('C1', 'Salaire de base');
                        $detailsSheet->setCellValue('D1', 'Indemnités');
                        $detailsSheet->setCellValue('E1', 'Primes');
                        $detailsSheet->setCellValue('F1', 'Salaire brut');
                        $detailsSheet->setCellValue('G1', 'Retenues');
                        $detailsSheet->setCellValue('H1', 'Salaire net');
                        $detailsSheet->getStyle('A1:H1')->getFont()->setBold(true);
                        
                        // Ajouter les données par employé
                        $row = 2;
                        foreach ($bulletins as $bulletin) {
                            // Récupérer le nom de l'employé de manière sécurisée
                            $nomEmploye = $bulletin->employeur->nom_complet ?? 'N/A';
                            $detailsSheet->setCellValue('A' . $row, $nomEmploye);
                            
                            // Récupérer le département de manière sécurisée
                            $departementBrut = $bulletin->employeur->departement ?? null;
                            
                            // Traiter le département selon son type
                            if (is_object($departementBrut) && method_exists($departementBrut, 'getNom')) {
                                // Si c'est un objet avec une méthode getNom (comme un modèle Departement)
                                $departement = $departementBrut->getNom();
                            } elseif (is_object($departementBrut) && isset($departementBrut->nom)) {
                                // Si c'est un objet avec une propriété nom
                                $departement = $departementBrut->nom;
                            } else {
                                // Convertir en chaîne de caractères ou utiliser une valeur par défaut
                                $departement = $departementBrut ? (string)$departementBrut : 'Non spécifié';
                            }
                            
                            $detailsSheet->setCellValue('B' . $row, $departement);
                            $detailsSheet->setCellValue('C' . $row, $bulletin->salaire_base);
                            $detailsSheet->setCellValue('D' . $row, $bulletin->total_indemnites);
                            $detailsSheet->setCellValue('E' . $row, $bulletin->total_primes);
                            $detailsSheet->setCellValue('F' . $row, $bulletin->salaire_brut);
                            $detailsSheet->setCellValue('G' . $row, $bulletin->total_retenues);
                            $detailsSheet->setCellValue('H' . $row, $bulletin->salaire_net);
                            
                            // Formatage monétaire
                            $detailsSheet->getStyle('C' . $row . ':H' . $row)->getNumberFormat()->setFormatCode(self::FORMAT_CURRENCY_XOF);
                            
                            $row++;
                        }
                        
                        // Auto-dimensionner les colonnes
                        foreach (range('A', 'H') as $column) {
                            $detailsSheet->getColumnDimension($column)->setAutoSize(true);
                        }
                    }
                    
                    // Ajouter une feuille de comparaison avec les périodes précédentes si demandé
                    if ($data['comparaison_periodes']) {
                        $comparisonSheet = $spreadsheet->createSheet();
                        $comparisonSheet->setTitle('Comparaison');
                        
                        // Déterminer les périodes de comparaison
                        $periodes = [];
                        
                        switch ($data['periode']) {
                            case 'mois_courant':
                            case 'mois_precedent':
                                // Comparer avec les 6 derniers mois
                                for ($i = 0; $i < 6; $i++) {
                                    $debut = $dateDebut->copy()->subMonths($i);
                                    $fin = $debut->copy()->endOfMonth();
                                    $periodes[] = [
                                        'nom' => $debut->translatedFormat('F Y'),
                                        'debut' => $debut,
                                        'fin' => $fin,
                                    ];
                                }
                                break;
                            case 'trimestre':
                                // Comparer avec les 4 derniers trimestres
                                for ($i = 0; $i < 4; $i++) {
                                    $debut = $dateDebut->copy()->subQuarters($i)->startOfQuarter();
                                    $fin = $debut->copy()->endOfQuarter();
                                    $periodes[] = [
                                        'nom' => 'T' . $debut->quarter . ' ' . $debut->year,
                                        'debut' => $debut,
                                        'fin' => $fin,
                                    ];
                                }
                                break;
                            case 'annee':
                            case 'personnalise':
                                // Comparer avec les 3 dernières années
                                for ($i = 0; $i < 3; $i++) {
                                    $debut = $dateDebut->copy()->subYears($i)->startOfYear();
                                    $fin = $debut->copy()->endOfYear();
                                    $periodes[] = [
                                        'nom' => $debut->year,
                                        'debut' => $debut,
                                        'fin' => $fin,
                                    ];
                                }
                                break;
                        }
                        
                        // En-têtes
                        $comparisonSheet->setCellValue('A1', 'Période');
                        $comparisonSheet->setCellValue('B1', 'Masse salariale brute');
                        $comparisonSheet->setCellValue('C1', 'Masse salariale nette');
                        $comparisonSheet->setCellValue('D1', 'Indemnités');
                        $comparisonSheet->setCellValue('E1', 'Primes');
                        $comparisonSheet->setCellValue('F1', 'Charges patronales');
                        $comparisonSheet->setCellValue('G1', 'Nombre d\'employés');
                        $comparisonSheet->getStyle('A1:G1')->getFont()->setBold(true);
                        
                        // Récupérer et ajouter les données pour chaque période
                        $row = 2;
                        foreach ($periodes as $periode) {
                            $bulletinsPeriode = BulletinPaie::where('entreprise_id', $entrepriseId)
                                ->where('statut', 'validé')
                                ->whereBetween('periode_fin', [$periode['debut']->format('Y-m-d'), $periode['fin']->format('Y-m-d')])
                                ->get();
                            
                            $comparisonSheet->setCellValue('A' . $row, $periode['nom']);
                            $comparisonSheet->setCellValue('B' . $row, $bulletinsPeriode->sum('salaire_brut'));
                            $comparisonSheet->setCellValue('C' . $row, $bulletinsPeriode->sum('salaire_net'));
                            $comparisonSheet->setCellValue('D' . $row, $bulletinsPeriode->sum('total_indemnites'));
                            $comparisonSheet->setCellValue('E' . $row, $bulletinsPeriode->sum('total_primes'));
                            $comparisonSheet->setCellValue('F' . $row, $bulletinsPeriode->sum('charges_patronales'));
                            $comparisonSheet->setCellValue('G' . $row, $bulletinsPeriode->pluck('employeur_id')->unique()->count());
                            
                            // Formatage monétaire
                            $comparisonSheet->getStyle('B' . $row . ':F' . $row)->getNumberFormat()->setFormatCode(self::FORMAT_CURRENCY_XOF);
                            
                            $row++;
                        }
                        
                        // Auto-dimensionner les colonnes
                        foreach (range('A', 'G') as $column) {
                            $comparisonSheet->getColumnDimension($column)->setAutoSize(true);
                        }
                    }
                    
                    // Finaliser la mise en forme de la feuille de résumé
                    foreach (range('A', 'H') as $column) {
                        $resumeSheet->getColumnDimension($column)->setAutoSize(true);
                    }
                    
                    // Générer le nom du fichier
                    $filename = 'rapport_paie_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.xlsx';
                    
                    // Créer une réponse HTTP en streaming
                    return new StreamedResponse(
                        function () use ($spreadsheet, $filename) {
                            $writer = new Xlsx($spreadsheet);
                            $writer->save('php://output');
                        },
                        200,
                        [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                            'Cache-Control' => 'max-age=0',
                        ]
                    );
                } catch (\Exception $e) {
                    // Journaliser l'erreur
                    Log::error('Erreur lors de la génération du rapport de paie', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Afficher une notification d'erreur
                    Notification::make()
                        ->title('Erreur lors de la génération du rapport')
                        ->body('Une erreur est survenue: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
