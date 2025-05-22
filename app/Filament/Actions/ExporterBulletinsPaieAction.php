<?php

namespace App\Filament\Actions;

use App\Models\Paie\BulletinPaie;
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

class ExporterBulletinsPaieAction
{
    /**
     * Crée une action Filament pour exporter les bulletins de paie en Excel
     *
     * @return \Filament\Actions\Action
     */
    public static function make(): Action
    {
        return Action::make('exporter_bulletins_paie')
            ->label('Exporter les bulletins')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->tooltip('Exporter les bulletins en Excel')
            ->form([
                Forms\Components\Select::make('statut')
                    ->label('Statut des bulletins')
                    ->options([
                        'tous' => 'Tous les bulletins',
                        'brouillon' => 'Brouillons',
                        'validé' => 'Validés',
                        'annulé' => 'Annulés',
                    ])
                    ->default('tous')
                    ->required(),
                    
                Forms\Components\Select::make('periode')
                    ->label('Période')
                    ->options([
                        'tous' => 'Toutes les périodes',
                        'mois_courant' => 'Mois courant',
                        'mois_precedent' => 'Mois précédent',
                        'trimestre' => 'Trimestre en cours',
                        'annee' => 'Année en cours',
                    ])
                    ->default('tous')
                    ->required(),
                    
                Forms\Components\DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->visible(fn (Forms\Get $get) => $get('periode') === 'personnalise'),
                    
                Forms\Components\DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->visible(fn (Forms\Get $get) => $get('periode') === 'personnalise'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Exporter les bulletins de paie')
            ->modalDescription('Sélectionnez les options pour l\'export des bulletins de paie au format Excel.')
            ->modalSubmitActionLabel('Exporter')
            ->action(function (array $data) {
                try {
                    // Journaliser le début de l'opération
                    Log::info('Démarrage de l\'export des bulletins de paie en Excel', [
                        'statut' => $data['statut'],
                        'periode' => $data['periode'],
                    ]);
                    
                    // Récupérer l'ID de l'entreprise
                    $entrepriseId = Auth::user()->entreprise_id;
                    
                    // Préparer la requête de base
                    $query = BulletinPaie::where('entreprise_id', $entrepriseId)
                        ->with('employeur')
                        ->orderBy('created_at', 'desc');
                    
                    // Filtrer par statut
                    if ($data['statut'] !== 'tous') {
                        $query->where('statut', $data['statut']);
                    }
                    
                    // Filtrer par période
                    $now = Carbon::now();
                    switch ($data['periode']) {
                        case 'mois_courant':
                            $query->whereMonth('periode_fin', $now->month)
                                ->whereYear('periode_fin', $now->year);
                            break;
                        case 'mois_precedent':
                            $previousMonth = $now->copy()->subMonth();
                            $query->whereMonth('periode_fin', $previousMonth->month)
                                ->whereYear('periode_fin', $previousMonth->year);
                            break;
                        case 'trimestre':
                            $query->whereBetween('periode_fin', [
                                $now->copy()->startOfQuarter(),
                                $now->copy()->endOfQuarter()
                            ]);
                            break;
                        case 'annee':
                            $query->whereYear('periode_fin', $now->year);
                            break;
                        case 'personnalise':
                            if (!empty($data['date_debut']) && !empty($data['date_fin'])) {
                                $query->whereBetween('periode_fin', [$data['date_debut'], $data['date_fin']]);
                            }
                            break;
                    }
                    
                    // Exécuter la requête
                    $bulletins = $query->get();
                    
                    // Créer un nouveau spreadsheet
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle('Bulletins de paie');
                    
                    // Définir les en-têtes
                    $headers = [
                        'Référence', 'Employé', 'Début période', 'Fin période',
                        'Salaire de base', 'Indemnités', 'Primes', 'Salaire brut',
                        'CNPS employé', 'IGR', 'Total retenues', 'Salaire net',
                        'Charges patronales', 'Statut', 'Date de création'
                    ];
                    
                    // Ajouter les en-têtes
                    foreach ($headers as $key => $header) {
                        $sheet->setCellValueByColumnAndRow($key + 1, 1, $header);
                    }
                    
                    // Styliser les en-têtes
                    $headerRange = 'A1:O1';
                    $sheet->getStyle($headerRange)->getFont()->setBold(true);
                    $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
                    $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
                    $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Ajouter les données
                    $row = 2;
                    foreach ($bulletins as $bulletin) {
                        $sheet->setCellValueByColumnAndRow(1, $row, $bulletin->reference);
                        $sheet->setCellValueByColumnAndRow(2, $row, $bulletin->employeur->nom_complet ?? 'N/A');
                        $sheet->setCellValueByColumnAndRow(3, $row, Carbon::parse($bulletin->periode_debut)->format('d/m/Y'));
                        $sheet->setCellValueByColumnAndRow(4, $row, Carbon::parse($bulletin->periode_fin)->format('d/m/Y'));
                        $sheet->setCellValueByColumnAndRow(5, $row, $bulletin->salaire_base);
                        $sheet->setCellValueByColumnAndRow(6, $row, $bulletin->total_indemnites);
                        $sheet->setCellValueByColumnAndRow(7, $row, $bulletin->total_primes);
                        $sheet->setCellValueByColumnAndRow(8, $row, $bulletin->salaire_brut);
                        $sheet->setCellValueByColumnAndRow(9, $row, $bulletin->cnps_employe);
                        $sheet->setCellValueByColumnAndRow(10, $row, $bulletin->igr);
                        $sheet->setCellValueByColumnAndRow(11, $row, $bulletin->total_retenues);
                        $sheet->setCellValueByColumnAndRow(12, $row, $bulletin->salaire_net);
                        $sheet->setCellValueByColumnAndRow(13, $row, $bulletin->charges_patronales);
                        $sheet->setCellValueByColumnAndRow(14, $row, $bulletin->statut);
                        $sheet->setCellValueByColumnAndRow(15, $row, Carbon::parse($bulletin->created_at)->format('d/m/Y H:i'));
                        
                        // Styliser les cellules de montant
                        for ($col = 5; $col <= 13; $col++) {
                            $sheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('#,##0 FCFA');
                            $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        }
                        
                        // Styliser la cellule de statut
                        $statusCell = $sheet->getCellByColumnAndRow(14, $row);
                        switch ($bulletin->statut) {
                            case 'brouillon':
                                $statusCell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
                                break;
                            case 'validé':
                                $statusCell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                                break;
                            case 'annulé':
                                $statusCell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
                                break;
                        }
                        
                        $row++;
                    }
                    
                    // Auto-dimensionner les colonnes
                    foreach (range('A', 'O') as $column) {
                        $sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                    
                    // Ajouter une bordure à toutes les cellules
                    $lastRow = $row - 1;
                    $sheet->getStyle('A1:O' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    
                    // Générer le nom du fichier
                    $filename = 'bulletins_paie_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';
                    
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
                    Log::error('Erreur lors de l\'export des bulletins de paie en Excel', [
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
