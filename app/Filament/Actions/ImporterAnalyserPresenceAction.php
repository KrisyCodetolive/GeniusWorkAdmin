<?php

namespace App\Filament\Actions;

use App\Models\Site;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Radio;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImporterAnalyserPresenceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'importerAnalyserPresence';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Importer & Analyser')
            ->icon('heroicon-o-document-arrow-up')
            ->color('primary')
            ->form([
                Section::make('Importation du rapport de présence')
                    ->description('Importez un fichier Excel de rapport de présence pour analyse')
                    ->schema([
                        FileUpload::make('fichier_presence')
                            ->label('Fichier de présence')
                            ->helperText('Formats acceptés: .xls, .xlsx')
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->maxSize(10240) // 10MB
                            ->required()
                            ->disk('local')
                            ->directory('temp/imports'),
                            
                        Radio::make('type_analyse')
                            ->label('Type d\'analyse')
                            ->options([
                                'basique' => 'Analyse basique (présences, absences, retards)',
                                'complete' => 'Analyse complète (statistiques détaillées)',
                                'integration' => 'Intégration au système (import des données)',
                            ])
                            ->default('basique')
                            ->required(),
                            
                        Toggle::make('generer_rapport')
                            ->label('Générer un rapport PDF')
                            ->helperText('Créer un rapport PDF avec les résultats de l\'analyse')
                            ->default(false),
                            
                        Select::make('site_id')
                            ->label('Site')
                            ->options(function () {
                                $user = Auth::user();
                                $query = Site::query();
                                
                                if (!$user->isSuperAdmin() && !$user->isSupport()) {
                                    $query->where('entreprise_id', $user->entreprise_id);
                                }
                                
                                return $query->pluck('nom', 'id')->prepend('Tous les sites', '');
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('type_analyse') === 'integration'),
                    ]),
            ])
            ->action(function (array $data) {
                // Récupérer le chemin du fichier téléchargé
                $filePath = Storage::disk('local')->path($data['fichier_presence']);
                
                try {
                    // Charger le fichier Excel
                    $spreadsheet = IOFactory::load($filePath);
                    
                    // Analyser les données selon le type d'analyse sélectionné
                    $resultats = $this->analyserFichierPresence($spreadsheet, $data['type_analyse']);
                    
                    // Générer un rapport PDF si demandé
                    if ($data['generer_rapport'] ?? false) {
                        $pdfPath = $this->genererRapportPDF($resultats);
                        // Télécharger le rapport PDF
                        return response()->download($pdfPath, 'rapport_analyse_presence.pdf')->deleteFileAfterSend();
                    }
                    
                    // Intégrer les données au système si demandé
                    if ($data['type_analyse'] === 'integration') {
                        $this->integrerDonneesSysteme($spreadsheet, $data['site_id'] ?? null);
                        
                        Notification::make()
                            ->title('Données intégrées avec succès')
                            ->success()
                            ->send();
                    } else {
                        // Afficher une notification avec un résumé des résultats
                        Notification::make()
                            ->title('Analyse terminée')
                            ->body($this->genererResumeResultats($resultats))
                            ->success()
                            ->send();
                    }
                    
                    // Supprimer le fichier temporaire
                    Storage::disk('local')->delete($data['fichier_presence']);
                    
                    return redirect()->back();
                } catch (\Exception $e) {
                    // Supprimer le fichier temporaire en cas d'erreur
                    Storage::disk('local')->delete($data['fichier_presence']);
                    
                    Notification::make()
                        ->title('Erreur lors de l\'analyse')
                        ->body('Une erreur est survenue: ' . $e->getMessage())
                        ->danger()
                        ->send();
                        
                    return redirect()->back();
                }
            });
    }
    
    /**
     * Analyser le fichier Excel de présence
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @param string $typeAnalyse
     * @return array
     */
    protected function analyserFichierPresence($spreadsheet, $typeAnalyse)
    {
        $resultats = [
            'presences' => 0,
            'absences' => 0,
            'retards' => 0,
            'conges' => 0,
            'heures_supp' => 0,
            'periode' => [
                'debut' => null,
                'fin' => null,
            ],
            'departements' => [],
            'anomalies' => [],
            'statistiques' => [],
        ];
        
        // Parcourir chaque feuille du classeur
        foreach ($spreadsheet->getAllSheets() as $worksheet) {
            $sheetName = $worksheet->getTitle();
            
            // Obtenir les dimensions de la feuille
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            
            // Extraire les en-têtes (première ligne)
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                $cellValue = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                if (!empty($cellValue)) {
                    $headers[$col] = $cellValue;
                }
            }
            
            // Analyser selon le nom de la feuille
            if (stripos($sheetName, 'planification') !== false) {
                $this->analyserFeuillePlanification($worksheet, $headers, $resultats);
            } elseif (stripos($sheetName, 'récapitulatif') !== false || stripos($sheetName, 'recapitulatif') !== false) {
                $this->analyserFeuilleRecapitulatif($worksheet, $headers, $resultats);
            } elseif (stripos($sheetName, 'record') !== false || stripos($sheetName, 'présence') !== false) {
                $this->analyserFeuilleRecordPresence($worksheet, $headers, $resultats);
            } elseif (stripos($sheetName, 'anomalies') !== false || stripos($sheetName, 'statistiques') !== false) {
                $this->analyserFeuilleAnomalies($worksheet, $headers, $resultats);
            }
        }
        
        // Analyse complète: statistiques supplémentaires
        if ($typeAnalyse === 'complete' || $typeAnalyse === 'integration') {
            $this->calculerStatistiquesAvancees($resultats);
        }
        
        return $resultats;
    }
    
    /**
     * Analyser la feuille de planification
     */
    protected function analyserFeuillePlanification($worksheet, $headers, &$resultats)
    {
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        
        // Rechercher les colonnes de dates et de département
        $dateColumns = [];
        $deptColumnIndex = null;
        $nomColumnIndex = null;
        
        foreach ($headers as $index => $header) {
            if (is_numeric($header) || preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $header) || preg_match('/^\d{1,2}\/\d{1,2}$/', $header)) {
                $dateColumns[$index] = $header;
            } elseif (stripos($header, 'dépt') !== false || stripos($header, 'dept') !== false || stripos($header, 'département') !== false) {
                $deptColumnIndex = $index;
            } elseif (stripos($header, 'nom') !== false || stripos($header, 'employé') !== false) {
                $nomColumnIndex = $index;
            }
        }
        
        // Extraire la période de planification
        if (!empty($dateColumns)) {
            $dates = array_values($dateColumns);
            
            // Convertir les dates au format standard si nécessaire
            $formattedDates = [];
            foreach ($dates as $date) {
                if (is_numeric($date)) {
                    // Convertir un numéro Excel en date
                    $formattedDates[] = Date::excelToDateTimeObject($date)->format('Y-m-d');
                } elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date)) {
                    // Format JJ/MM/AAAA
                    $parts = explode('/', $date);
                    $formattedDates[] = "$parts[2]-$parts[1]-$parts[0]";
                } elseif (preg_match('/^\d{1,2}\/\d{1,2}$/', $date)) {
                    // Format JJ/MM (année courante)
                    $parts = explode('/', $date);
                    $year = date('Y');
                    $formattedDates[] = "$year-$parts[1]-$parts[0]";
                }
            }
            
            if (!empty($formattedDates)) {
                $resultats['periode']['debut'] = min($formattedDates);
                $resultats['periode']['fin'] = max($formattedDates);
            }
        }
        
        // Analyser les départements et les présences planifiées
        if ($deptColumnIndex && $nomColumnIndex) {
            $departements = [];
            $presencesPlanifiees = 0;
            
            for ($row = 2; $row <= $highestRow; ++$row) {
                $dept = $worksheet->getCellByColumnAndRow($deptColumnIndex, $row)->getValue();
                $nom = $worksheet->getCellByColumnAndRow($nomColumnIndex, $row)->getValue();
                
                if (!empty($dept) && !empty($nom)) {
                    if (!isset($departements[$dept])) {
                        $departements[$dept] = 0;
                    }
                    $departements[$dept]++;
                    
                    // Compter les présences planifiées (valeurs non vides dans les colonnes de dates)
                    foreach ($dateColumns as $index => $date) {
                        $value = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                        if (!empty($value)) {
                            $presencesPlanifiees++;
                        }
                    }
                }
            }
            
            $resultats['departements'] = $departements;
            $resultats['presences_planifiees'] = $presencesPlanifiees;
        }
    }
    
    /**
     * Analyser la feuille de récapitulatif
     */
    protected function analyserFeuilleRecapitulatif($worksheet, $headers, &$resultats)
    {
        $highestRow = $worksheet->getHighestRow();
        
        // Rechercher les colonnes pertinentes
        $colonnes = [
            'retard' => null,
            'absence' => null,
            'conge' => null,
            'heures_supp' => null,
        ];
        
        foreach ($headers as $index => $header) {
            $header = strtolower($header);
            if (stripos($header, 'retard') !== false) {
                $colonnes['retard'] = $index;
            } elseif (stripos($header, 'absence') !== false || stripos($header, 'absent') !== false) {
                $colonnes['absence'] = $index;
            } elseif (stripos($header, 'congé') !== false || stripos($header, 'conge') !== false) {
                $colonnes['conge'] = $index;
            } elseif (stripos($header, 'supp') !== false || stripos($header, 'supplémentaire') !== false) {
                $colonnes['heures_supp'] = $index;
            }
        }
        
        // Compter les occurrences
        foreach ($colonnes as $type => $index) {
            if ($index) {
                $count = 0;
                for ($row = 2; $row <= $highestRow; ++$row) {
                    $value = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                    if (!empty($value) && is_numeric($value) && $value > 0) {
                        $count += (int)$value;
                    }
                }
                
                switch ($type) {
                    case 'retard':
                        $resultats['retards'] = $count;
                        break;
                    case 'absence':
                        $resultats['absences'] = $count;
                        break;
                    case 'conge':
                        $resultats['conges'] = $count;
                        break;
                    case 'heures_supp':
                        $resultats['heures_supp'] = $count;
                        break;
                }
            }
        }
    }
    
    /**
     * Analyser la feuille de record de présence
     */
    protected function analyserFeuilleRecordPresence($worksheet, $headers, &$resultats)
    {
        $highestRow = $worksheet->getHighestRow();
        
        // Rechercher les colonnes d'entrée/sortie
        $entreeColumns = [];
        $sortieColumns = [];
        
        foreach ($headers as $index => $header) {
            $header = strtolower($header);
            if (stripos($header, 'entrée') !== false || stripos($header, 'entree') !== false || stripos($header, 'arrivée') !== false) {
                $entreeColumns[] = $index;
            } elseif (stripos($header, 'sortie') !== false || stripos($header, 'départ') !== false) {
                $sortieColumns[] = $index;
            }
        }
        
        // Compter les présences (entrées et sorties valides)
        $presences = 0;
        $retards = 0;
        
        for ($row = 2; $row <= $highestRow; ++$row) {
            $hasEntree = false;
            $hasSortie = false;
            
            foreach ($entreeColumns as $index) {
                $value = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                if (!empty($value)) {
                    $hasEntree = true;
                    // Vérifier si c'est un retard (format heure attendu: HH:MM)
                    if (preg_match('/(\d{1,2}):(\d{2})/', $value, $matches)) {
                        $heure = (int)$matches[1];
                        if ($heure >= 9) { // Supposons que 9h00 est l'heure limite
                            $retards++;
                        }
                    }
                    break;
                }
            }
            
            foreach ($sortieColumns as $index) {
                $value = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                if (!empty($value)) {
                    $hasSortie = true;
                    break;
                }
            }
            
            if ($hasEntree && $hasSortie) {
                $presences++;
            }
        }
        
        $resultats['presences'] = $presences;
        if ($retards > 0) {
            $resultats['retards'] = ($resultats['retards'] ?? 0) + $retards;
        }
    }
    
    /**
     * Analyser la feuille d'anomalies
     */
    protected function analyserFeuilleAnomalies($worksheet, $headers, &$resultats)
    {
        $highestRow = $worksheet->getHighestRow();
        
        // Rechercher les colonnes pertinentes
        $dateColumnIndex = null;
        $typeAnomalieColumnIndex = null;
        
        foreach ($headers as $index => $header) {
            $header = strtolower($header);
            if (stripos($header, 'date') !== false || stripos($header, 'jour') !== false) {
                $dateColumnIndex = $index;
            } elseif (stripos($header, 'anomalie') !== false || stripos($header, 'type') !== false) {
                $typeAnomalieColumnIndex = $index;
            }
        }
        
        // Collecter les anomalies
        if ($dateColumnIndex && $typeAnomalieColumnIndex) {
            $anomalies = [];
            
            for ($row = 2; $row <= $highestRow; ++$row) {
                $date = $worksheet->getCellByColumnAndRow($dateColumnIndex, $row)->getValue();
                $type = $worksheet->getCellByColumnAndRow($typeAnomalieColumnIndex, $row)->getValue();
                
                if (!empty($date) && !empty($type)) {
                    // Convertir la date si nécessaire
                    if (is_numeric($date)) {
                        $date = Date::excelToDateTimeObject($date)->format('Y-m-d');
                    }
                    
                    if (!isset($anomalies[$type])) {
                        $anomalies[$type] = 0;
                    }
                    $anomalies[$type]++;
                    
                    // Compter les absences
                    if (stripos($type, 'absence') !== false || stripos($type, 'absent') !== false) {
                        $resultats['absences'] = ($resultats['absences'] ?? 0) + 1;
                    }
                }
            }
            
            $resultats['anomalies'] = $anomalies;
        }
    }
    
    /**
     * Calculer des statistiques avancées
     */
    protected function calculerStatistiquesAvancees(&$resultats)
    {
        // Calculer le taux de présence
        $totalJours = 0;
        if (!empty($resultats['periode']['debut']) && !empty($resultats['periode']['fin'])) {
            $debut = new Carbon($resultats['periode']['debut']);
            $fin = new Carbon($resultats['periode']['fin']);
            $totalJours = $debut->diffInDays($fin) + 1;
        }
        
        $totalEmployes = array_sum($resultats['departements'] ?? []);
        
        if ($totalJours > 0 && $totalEmployes > 0) {
            $joursOuvrables = $totalJours * $totalEmployes;
            $tauxPresence = ($resultats['presences'] / $joursOuvrables) * 100;
            $tauxAbsence = ($resultats['absences'] / $joursOuvrables) * 100;
            $tauxRetard = ($resultats['retards'] / $joursOuvrables) * 100;
            
            $resultats['statistiques'] = [
                'jours_periode' => $totalJours,
                'total_employes' => $totalEmployes,
                'jours_ouvrables' => $joursOuvrables,
                'taux_presence' => round($tauxPresence, 2),
                'taux_absence' => round($tauxAbsence, 2),
                'taux_retard' => round($tauxRetard, 2),
            ];
        }
    }
    
    /**
     * Générer un résumé des résultats pour la notification
     */
    protected function genererResumeResultats($resultats)
    {
        $resume = "Période: ";
        if (!empty($resultats['periode']['debut']) && !empty($resultats['periode']['fin'])) {
            $resume .= $resultats['periode']['debut'] . " au " . $resultats['periode']['fin'] . "\n";
        } else {
            $resume .= "Non spécifiée\n";
        }
        
        $resume .= "Présences: " . $resultats['presences'] . " | ";
        $resume .= "Absences: " . $resultats['absences'] . " | ";
        $resume .= "Retards: " . $resultats['retards'] . "\n";
        
        if (!empty($resultats['statistiques'])) {
            $resume .= "Taux de présence: " . $resultats['statistiques']['taux_presence'] . "%\n";
        }
        
        return $resume;
    }
    
    /**
     * Générer un rapport PDF avec les résultats
     */
    protected function genererRapportPDF($resultats)
    {
        // Cette méthode devrait utiliser une bibliothèque comme DOMPDF ou TCPDF
        // pour générer un rapport PDF avec les résultats de l'analyse
        
        // Pour l'exemple, nous allons simplement créer un fichier texte
        $contenu = "RAPPORT D'ANALYSE DE PRÉSENCE\n\n";
        $contenu .= "Période: " . ($resultats['periode']['debut'] ?? 'N/A') . " au " . ($resultats['periode']['fin'] ?? 'N/A') . "\n\n";
        
        $contenu .= "RÉSUMÉ:\n";
        $contenu .= "- Présences: " . $resultats['presences'] . "\n";
        $contenu .= "- Absences: " . $resultats['absences'] . "\n";
        $contenu .= "- Retards: " . $resultats['retards'] . "\n";
        $contenu .= "- Congés: " . $resultats['conges'] . "\n";
        $contenu .= "- Heures supplémentaires: " . $resultats['heures_supp'] . "\n\n";
        
        if (!empty($resultats['departements'])) {
            $contenu .= "RÉPARTITION PAR DÉPARTEMENT:\n";
            foreach ($resultats['departements'] as $dept => $count) {
                $contenu .= "- $dept: $count employés\n";
            }
            $contenu .= "\n";
        }
        
        if (!empty($resultats['anomalies'])) {
            $contenu .= "ANOMALIES:\n";
            foreach ($resultats['anomalies'] as $type => $count) {
                $contenu .= "- $type: $count occurrences\n";
            }
            $contenu .= "\n";
        }
        
        if (!empty($resultats['statistiques'])) {
            $contenu .= "STATISTIQUES:\n";
            $contenu .= "- Jours dans la période: " . $resultats['statistiques']['jours_periode'] . "\n";
            $contenu .= "- Total employés: " . $resultats['statistiques']['total_employes'] . "\n";
            $contenu .= "- Jours ouvrables: " . $resultats['statistiques']['jours_ouvrables'] . "\n";
            $contenu .= "- Taux de présence: " . $resultats['statistiques']['taux_presence'] . "%\n";
            $contenu .= "- Taux d'absence: " . $resultats['statistiques']['taux_absence'] . "%\n";
            $contenu .= "- Taux de retard: " . $resultats['statistiques']['taux_retard'] . "%\n";
        }
        
        // Créer un fichier temporaire
        $tempFile = storage_path('app/temp/rapport_' . time() . '.txt');
        file_put_contents($tempFile, $contenu);
        
        return $tempFile;
    }
    
    /**
     * Intégrer les données au système
     */
    protected function integrerDonneesSysteme($spreadsheet, $siteId)
    {
        // Cette méthode devrait intégrer les données du fichier Excel
        // dans les tables de la base de données du système
        
        // Pour l'exemple, nous allons simplement logger l'opération
        \Illuminate\Support\Facades\Log::info('Intégration des données de présence', [
            'site_id' => $siteId,
            'date' => now()->format('Y-m-d H:i:s'),
            'user_id' => Auth::id(),
        ]);
        
        // L'implémentation réelle dépendra des modèles et de la structure de la base de données
    }
}
