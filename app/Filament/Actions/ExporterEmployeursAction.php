<?php

namespace App\Filament\Actions;

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
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterEmployeursAction
{
    /**
     * Crée une action Filament pour exporter les données des employés en Excel
     *
     * @return \Filament\Actions\Action
     */
    public static function make(): Action
    {
        return Action::make('exporter_employeurs')
            ->label('Exporter les employés')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->tooltip('Exporter les données des employés en Excel')
            ->form([
                Forms\Components\Select::make('statut')
                    ->label('Statut des employés')
                    ->options([
                        'tous' => 'Tous les employés',
                        'actif' => 'Actifs',
                        'inactif' => 'Inactifs',
                    ])
                    ->default('tous')
                    ->required(),
                    
                Forms\Components\Select::make('departement')
                    ->label('Département')
                    ->relationship('departement', 'nom')
                    ->searchable()
                    ->preload()
                    ->placeholder('Tous les départements'),
                    
                Forms\Components\Select::make('filiale')
                    ->label('Filiale')
                    ->relationship('filiale', 'nom')
                    ->searchable()
                    ->preload()
                    ->placeholder('Toutes les filiales'),
                    
                Forms\Components\Select::make('type_contrat')
                    ->label('Type de contrat')
                    ->options([
                        'tous' => 'Tous les types',
                        'CDI' => 'CDI',
                        'CDD' => 'CDD',
                        'Stage' => 'Stage',
                        'Prestation' => 'Prestation',
                    ])
                    ->default('tous')
                    ->required(),
                    
                Forms\Components\Select::make('anciennete')
                    ->label('Ancienneté')
                    ->options([
                        'tous' => 'Tous',
                        'moins_1_an' => 'Moins d\'un an',
                        '1_3_ans' => 'Entre 1 et 3 ans',
                        '3_5_ans' => 'Entre 3 et 5 ans',
                        'plus_5_ans' => 'Plus de 5 ans',
                    ])
                    ->default('tous')
                    ->required(),
                    
                Forms\Components\Toggle::make('inclure_donnees_contact')
                    ->label('Inclure les données de contact')
                    ->helperText('Email, téléphone, etc.')
                    ->default(true),
                    
                Forms\Components\Toggle::make('inclure_donnees_salaire')
                    ->label('Inclure les données de salaire')
                    ->helperText('Salaire de base, etc.')
                    ->default(false),
            ])
            ->requiresConfirmation()
            ->modalHeading('Exporter les données des employés')
            ->modalDescription('Sélectionnez les options pour l\'export des données des employés au format Excel.')
            ->modalSubmitActionLabel('Exporter')
            ->action(function (array $data) {
                try {
                    // Journaliser le début de l'opération
                    Log::info('Démarrage de l\'export des données employés en Excel', [
                        'statut' => $data['statut'],
                        'type_contrat' => $data['type_contrat'],
                        'inclure_donnees_contact' => $data['inclure_donnees_contact'],
                        'inclure_donnees_salaire' => $data['inclure_donnees_salaire'],
                    ]);
                    
                    // Récupérer l'ID de l'entreprise
                    $entrepriseId = Auth::user()->entreprise_id;
                    
                    // Préparer la requête de base
                    $query = Employeur::where('entreprise_id', $entrepriseId)
                        ->with(['departement', 'filiale'])
                        ->orderBy('nom', 'asc');
                    
                    // Filtrer par statut
                    if ($data['statut'] !== 'tous') {
                        $query->where('statut', $data['statut']);
                    }
                    
                    // Filtrer par département
                    if (!empty($data['departement'])) {
                        $query->where('departement_id', $data['departement']);
                    }
                    
                    // Filtrer par filiale
                    if (!empty($data['filiale'])) {
                        $query->where('filiale_id', $data['filiale']);
                    }
                    
                    // Filtrer par type de contrat
                    if ($data['type_contrat'] !== 'tous') {
                        $query->where('type_contrat', $data['type_contrat']);
                    }
                    
                    // Filtrer par ancienneté
                    if ($data['anciennete'] !== 'tous') {
                        $now = Carbon::now();
                        switch ($data['anciennete']) {
                            case 'moins_1_an':
                                $query->where('date_embauche', '>=', $now->copy()->subYear());
                                break;
                            case '1_3_ans':
                                $query->whereBetween('date_embauche', [
                                    $now->copy()->subYears(3),
                                    $now->copy()->subYear()
                                ]);
                                break;
                            case '3_5_ans':
                                $query->whereBetween('date_embauche', [
                                    $now->copy()->subYears(5),
                                    $now->copy()->subYears(3)
                                ]);
                                break;
                            case 'plus_5_ans':
                                $query->where('date_embauche', '<=', $now->copy()->subYears(5));
                                break;
                        }
                    }
                    
                    // Exécuter la requête
                    $employes = $query->get();
                    
                    // Créer un nouveau spreadsheet
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle('Employés');
                    
                    // Définir les en-têtes de base
                    $headers = [
                        'Code Employé', 'Matricule', 'Nom', 'Prénom', 'Poste',
                        'Département', 'Filiale', 'Date d\'embauche', 'Type de contrat',
                        'Statut', 'Genre', 'Ancienneté (années)'
                    ];
                    
                    // Ajouter les en-têtes pour les données de contact si demandé
                    if ($data['inclure_donnees_contact']) {
                        $headers = array_merge($headers, [
                            'Email', 'Téléphone', 'Date de naissance', 'Lieu de naissance'
                        ]);
                    }
                    
                    // Ajouter les en-têtes pour les données de salaire si demandé
                    if ($data['inclure_donnees_salaire']) {
                        $headers = array_merge($headers, [
                            'Salaire de base'
                        ]);
                    }
                    
                    // Ajouter les en-têtes
                    foreach ($headers as $key => $header) {
                        $sheet->setCellValueByColumnAndRow($key + 1, 1, $header);
                    }
                    
                    // Styliser les en-têtes
                    $lastColumn = count($headers);
                    $headerRange = 'A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumn) . '1';
                    $sheet->getStyle($headerRange)->getFont()->setBold(true);
                    $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
                    $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
                    $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Ajouter les données
                    $row = 2;
                    foreach ($employes as $employe) {
                        $col = 1;
                        
                        // Données de base
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->code_employe);
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->matricule);
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->nom);
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->prenom);
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->poste);
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->departement->nom ?? 'N/A');
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->filiale->nom ?? 'N/A');
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->date_embauche ? Carbon::parse($employe->date_embauche)->format('d/m/Y') : 'N/A');
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->type_contrat);
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->statut);
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->genre);
                        $sheet->setCellValueByColumnAndRow($col++, $row, $employe->getAnciennete() ?? 'N/A');
                        
                        // Données de contact si demandé
                        if ($data['inclure_donnees_contact']) {
                            $sheet->setCellValueByColumnAndRow($col++, $row, $employe->email);
                            $sheet->setCellValueByColumnAndRow($col++, $row, $employe->telephone);
                            $sheet->setCellValueByColumnAndRow($col++, $row, $employe->date_naissance ? Carbon::parse($employe->date_naissance)->format('d/m/Y') : 'N/A');
                            $sheet->setCellValueByColumnAndRow($col++, $row, $employe->lieu_naissance ?? 'N/A');
                        }
                        
                        // Données de salaire si demandé
                        if ($data['inclure_donnees_salaire']) {
                            $sheet->setCellValueByColumnAndRow($col++, $row, $employe->salaire_base);
                            
                            // Formater la cellule de salaire
                            $salaryColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);
                            $sheet->getStyle($salaryColumn . $row)->getNumberFormat()->setFormatCode('#,##0 FCFA');
                            $sheet->getStyle($salaryColumn . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        }
                        
                        // Styliser la cellule de statut
                        $statusColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(10);
                        $statusCell = $sheet->getCell($statusColumn . $row);
                        switch ($employe->statut) {
                            case 'actif':
                                $statusCell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                                break;
                            case 'inactif':
                                $statusCell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
                                break;
                        }
                        
                        $row++;
                    }
                    
                    // Auto-dimensionner les colonnes
                    for ($i = 1; $i <= $lastColumn; $i++) {
                        $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
                    }
                    
                    // Ajouter une bordure à toutes les cellules
                    $lastRow = $row - 1;
                    $dataRange = 'A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumn) . $lastRow;
                    $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    
                    // Générer le nom du fichier
                    $filename = 'employes_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';
                    
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
                    Log::error('Erreur lors de l\'export des données employés en Excel', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Afficher une notification d'erreur
                    Notification::make()
                        ->title('Erreur lors de l\'export')
                        ->body('Une erreur est survenue: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
