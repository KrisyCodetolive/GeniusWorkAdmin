<?php

namespace App\Filament\Actions;

use App\Models\Task;
use App\Services\Tasks\TaskRapportService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenererRapportTachesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'generer_rapport_taches';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Générer un rapport')
            ->icon('heroicon-o-document-chart-bar')
            ->color('success')
            ->form([
                Forms\Components\Select::make('type_rapport')
                    ->label('Type de rapport')
                    ->options([
                        'global' => 'Rapport global',
                        'departement' => 'Par département',
                        'equipe' => 'Par équipe',
                        'employe' => 'Par employé',
                    ])
                    ->default('global')
                    ->required()
                    ->reactive(),
                
                Forms\Components\Select::make('departement_id')
                    ->label('Département')
                    ->options(function () {
                        return \App\Models\Departement::pluck('nom', 'id');
                    })
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => $get('type_rapport') === 'departement'),
                
                Forms\Components\Select::make('equipe_id')
                    ->label('Équipe')
                    ->options(function () {
                        return \App\Models\Equipe::pluck('nom', 'id');
                    })
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => $get('type_rapport') === 'equipe'),
                
                Forms\Components\Select::make('employe_id')
                    ->label('Employé')
                    ->options(function () {
                        return \App\Models\Employeur::pluck('nom_complet', 'id');
                    })
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => $get('type_rapport') === 'employe'),
                
                Forms\Components\DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->default(Carbon::now()->startOfMonth())
                    ->required(),
                
                Forms\Components\DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->default(Carbon::now()->endOfMonth())
                    ->required()
                    ->afterOrEqual('date_debut'),
                
                Forms\Components\Toggle::make('inclure_routines')
                    ->label('Inclure les tâches routinières')
                    ->default(true),
                
                Forms\Components\Toggle::make('inclure_statistiques')
                    ->label('Inclure les statistiques détaillées')
                    ->default(true),
                
                Forms\Components\Toggle::make('inclure_graphiques')
                    ->label('Inclure les graphiques')
                    ->default(true),
                
                Forms\Components\Select::make('format')
                    ->label('Format du rapport')
                    ->options([
                        'xlsx' => 'Excel (.xlsx)',
                        'pdf' => 'PDF (.pdf)',
                    ])
                    ->default('xlsx')
                    ->required(),
            ])
            ->action(function (array $data): void {
                $taskRapportService = app(TaskRapportService::class);
                
                // Préparer les paramètres pour le service
                $params = [
                    'date_debut' => $data['date_debut'],
                    'date_fin' => $data['date_fin'],
                    'inclure_routines' => $data['inclure_routines'],
                    'inclure_statistiques' => $data['inclure_statistiques'],
                    'inclure_graphiques' => $data['inclure_graphiques'],
                    'format' => $data['format'],
                ];
                
                // Préparer les dates
                $dateDebut = Carbon::parse($params['date_debut']);
                $dateFin = Carbon::parse($params['date_fin']);
                $entrepriseId = auth()->user()->entreprise_id;
                
                // Générer le rapport selon le type
                try {
                    switch ($data['type_rapport']) {
                        case 'departement':
                            if (!isset($data['departement_id'])) {
                                throw new \Exception('ID du département manquant');
                            }
                            $departement = \App\Models\Departement::findOrFail($data['departement_id']);
                            $rapport = $taskRapportService->genererRapportDepartement($departement, $dateDebut, $dateFin);
                            break;
                        case 'equipe':
                            if (!isset($data['equipe_id'])) {
                                throw new \Exception('ID de l\'\u00e9quipe manquant');
                            }
                            $equipe = \App\Models\Equipe::findOrFail($data['equipe_id']);
                            $rapport = $taskRapportService->genererRapportEquipe($equipe, $dateDebut, $dateFin);
                            break;
                        case 'employe':
                            if (!isset($data['employe_id'])) {
                                throw new \Exception('ID de l\'employé manquant');
                            }
                            $employe = \App\Models\Employeur::findOrFail($data['employe_id']);
                            $rapport = $taskRapportService->genererRapportEmploye($employe, $dateDebut, $dateFin);
                            break;
                        default:
                            $rapport = $taskRapportService->genererRapportGlobal($entrepriseId, $dateDebut, $dateFin);
                            break;
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erreur lors de la génération du rapport')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                    return;
                }
                
                // Générer le rapport et le télécharger directement (streaming)
                $this->streamRapport($rapport, $data['format'], $data['type_rapport']);
                
                Notification::make()
                    ->title('Rapport généré avec succès')
                    ->success()
                    ->send();
            });
    }
    
    protected function streamRapport($rapport, $format, $type): void
    {
        $filename = 'rapport_taches_' . $type . '_' . date('Y-m-d_His');
        
        if ($format === 'xlsx') {
            // Créer un objet Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Ajouter les données du rapport
            $this->populateSpreadsheet($sheet, $rapport);
            
            // Configurer les en-têtes pour le téléchargement
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            
            // Créer l'objet Writer et envoyer le fichier au navigateur
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } elseif ($format === 'pdf') {
            // Logique pour générer un PDF
            // Cette partie nécessiterait une bibliothèque comme TCPDF ou MPDF
            // Pour simplifier, nous utilisons une notification
            Notification::make()
                ->title('Génération de PDF')
                ->body('La fonctionnalité de génération de PDF sera implémentée prochainement.')
                ->warning()
                ->send();
        }
    }
    
    protected function populateSpreadsheet($sheet, $rapport): void
    {
        if (!is_array($rapport)) {
            $sheet->setCellValue('A1', 'ERREUR: Format de rapport invalide');
            return;
        }
        
        // En-tête du rapport
        $sheet->setCellValue('A1', 'RAPPORT DES TÂCHES');
        
        // Formater correctement la période qui est un tableau associatif
        $periodeFormatee = 'Période non spécifiée';
        if (isset($rapport['periode'])) {
            if (is_array($rapport['periode'])) {
                if (isset($rapport['periode']['debut']) && isset($rapport['periode']['fin'])) {
                    $periodeFormatee = 'du ' . $rapport['periode']['debut'] . ' au ' . $rapport['periode']['fin'];
                }
            } else {
                $periodeFormatee = $rapport['periode'];
            }
        }
        $sheet->setCellValue('A2', 'Période: ' . $periodeFormatee);
        
        // Informations spécifiques selon le type de rapport
        if (isset($rapport['departement'])) {
            $sheet->setCellValue('A3', 'Département: ' . ($rapport['departement']['nom'] ?? 'Non spécifié'));
        } elseif (isset($rapport['equipe'])) {
            $sheet->setCellValue('A3', 'Équipe: ' . ($rapport['equipe']['nom'] ?? 'Non spécifiée'));
        } elseif (isset($rapport['employe'])) {
            $sheet->setCellValue('A3', 'Employé: ' . ($rapport['employe']['nom'] ?? 'Non spécifié'));
        } else {
            $sheet->setCellValue('A3', 'Rapport global');
        }
        
        // En-têtes des colonnes pour les statistiques
        $row = 5;
        $sheet->setCellValue('A' . $row, 'STATISTIQUES GLOBALES');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $row += 2;
        
        // Statistiques globales
        if (isset($rapport['statistiques_globales']) && is_array($rapport['statistiques_globales'])) {
            $stats = $rapport['statistiques_globales'];
            $this->ajouterLigneStatistique($sheet, $row++, 'Total des tâches', $stats['total'] ?? 0);
            $this->ajouterLigneStatistique($sheet, $row++, 'Tâches terminées', $stats['terminees'] ?? 0);
            $this->ajouterLigneStatistique($sheet, $row++, 'Tâches en cours', $stats['en_cours'] ?? 0);
            $this->ajouterLigneStatistique($sheet, $row++, 'Tâches en attente', $stats['en_attente'] ?? 0);
            $this->ajouterLigneStatistique($sheet, $row++, 'Tâches en retard', $stats['en_retard'] ?? 0);
            $this->ajouterLigneStatistique($sheet, $row++, 'Tâches annulées', $stats['annulees'] ?? 0);
            $this->ajouterLigneStatistique($sheet, $row++, 'Taux de complétion', ($stats['taux_completion'] ?? 0) . '%');
        } else {
            $sheet->setCellValue('A' . $row, 'Aucune statistique globale disponible');
            $row++;
        }
        
        // Statistiques par type
        $row += 2;
        if (isset($rapport['statistiques_par_type']) && is_array($rapport['statistiques_par_type']) && !empty($rapport['statistiques_par_type'])) {
            $sheet->setCellValue('A' . $row, 'STATISTIQUES PAR TYPE');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $row += 2;
            
            // En-têtes
            $sheet->setCellValue('A' . $row, 'Type');
            $sheet->setCellValue('B' . $row, 'Total');
            $sheet->setCellValue('C' . $row, 'Terminées');
            $sheet->setCellValue('D' . $row, 'En cours');
            $sheet->setCellValue('E' . $row, 'En attente');
            $sheet->setCellValue('F' . $row, 'En retard');
            $sheet->setCellValue('G' . $row, 'Taux');
            $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
            $row++;
            
            foreach ($rapport['statistiques_par_type'] as $type => $stats) {
                if (!is_array($stats)) continue;
                
                $sheet->setCellValue('A' . $row, $this->formatType($type));
                $sheet->setCellValue('B' . $row, $stats['total'] ?? 0);
                $sheet->setCellValue('C' . $row, $stats['terminees'] ?? 0);
                $sheet->setCellValue('D' . $row, $stats['en_cours'] ?? 0);
                $sheet->setCellValue('E' . $row, $stats['en_attente'] ?? 0);
                $sheet->setCellValue('F' . $row, $stats['en_retard'] ?? 0);
                $sheet->setCellValue('G' . $row, ($stats['taux_completion'] ?? 0) . '%');
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'Aucune statistique par type disponible');
            $row++;
        }
        
        // Statistiques par priorité
        $row += 2;
        if (isset($rapport['statistiques_par_priorite']) && is_array($rapport['statistiques_par_priorite']) && !empty($rapport['statistiques_par_priorite'])) {
            $sheet->setCellValue('A' . $row, 'STATISTIQUES PAR PRIORITÉ');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $row += 2;
            
            // En-têtes
            $sheet->setCellValue('A' . $row, 'Priorité');
            $sheet->setCellValue('B' . $row, 'Total');
            $sheet->setCellValue('C' . $row, 'Terminées');
            $sheet->setCellValue('D' . $row, 'En cours');
            $sheet->setCellValue('E' . $row, 'En attente');
            $sheet->setCellValue('F' . $row, 'En retard');
            $sheet->setCellValue('G' . $row, 'Taux');
            $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
            $row++;
            
            foreach ($rapport['statistiques_par_priorite'] as $priorite => $stats) {
                if (!is_array($stats)) continue;
                
                $sheet->setCellValue('A' . $row, $this->formatPriorite($priorite));
                $sheet->setCellValue('B' . $row, $stats['total'] ?? 0);
                $sheet->setCellValue('C' . $row, $stats['terminees'] ?? 0);
                $sheet->setCellValue('D' . $row, $stats['en_cours'] ?? 0);
                $sheet->setCellValue('E' . $row, $stats['en_attente'] ?? 0);
                $sheet->setCellValue('F' . $row, $stats['en_retard'] ?? 0);
                $sheet->setCellValue('G' . $row, ($stats['taux_completion'] ?? 0) . '%');
                $row++;
            }
        }
        
        // Mise en forme
        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getColumnDimension('B')->setWidth(40);
    }
    
    /**
     * Ajoute une ligne de statistique au rapport Excel
     *
     * @param object $sheet La feuille Excel
     * @param int $row Le numéro de ligne
     * @param string $label Le libellé de la statistique
     * @param mixed $valeur La valeur de la statistique
     * @return void
     */
    protected function ajouterLigneStatistique($sheet, int $row, string $label, $valeur): void
    {
        $sheet->setCellValue('A' . $row, $label . ':');
        $sheet->setCellValue('B' . $row, $valeur);
    }

    /**
     * Formate la priorité d'une tâche pour l'affichage
     *
     * @param string $priorite La priorité de la tâche
     * @return string La priorité formatée
     */
    protected function formatPriorite(string $priorite): string
    {
        return match ($priorite) {
            Task::PRIORITE_BASSE => 'Basse',
            Task::PRIORITE_MOYENNE => 'Moyenne',
            Task::PRIORITE_HAUTE => 'Haute',
            Task::PRIORITE_URGENTE => 'Urgente',
            default => ucfirst($priorite),
        };
    }

    /**
     * Formate le type de tâche pour l'affichage
     *
     * @param string $type Le type de tâche
     * @return string Le type formaté
     */
    protected function formatType(string $type): string
    {
        return match ($type) {
            Task::TYPE_STANDARD => 'Standard',
            Task::TYPE_PROJET => 'Projet',
            Task::TYPE_ROUTINE => 'Routine',
            Task::TYPE_FORMATION => 'Formation',
            Task::TYPE_REUNION => 'Réunion',
            Task::TYPE_AUTRE => 'Autre',
            default => ucfirst($type),
        };
    }
}
