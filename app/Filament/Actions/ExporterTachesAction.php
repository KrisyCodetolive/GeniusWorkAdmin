<?php

namespace App\Filament\Actions;

use App\Models\Task;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExporterTachesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'exporter_taches';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Exporter les tâches')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->form([
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        '' => 'Tous les statuts',
                        Task::STATUT_EN_ATTENTE => 'En attente',
                        Task::STATUT_EN_COURS => 'En cours',
                        Task::STATUT_TERMINE => 'Terminée',
                        Task::STATUT_EN_RETARD => 'En retard',
                        Task::STATUT_ANNULE => 'Annulée',
                    ])
                    ->default(''),
                
                Forms\Components\Select::make('priorite')
                    ->label('Priorité')
                    ->options([
                        '' => 'Toutes les priorités',
                        Task::PRIORITE_BASSE => 'Basse',
                        Task::PRIORITE_BASSE => 'Normale',
                        Task::PRIORITE_HAUTE => 'Haute',
                        Task::PRIORITE_URGENTE => 'Urgente',
                    ])
                    ->default(''),
                
                Forms\Components\DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->default(Carbon::now()->subMonth()),
                
                Forms\Components\DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->default(Carbon::now())
                    ->afterOrEqual('date_debut'),
                
                Forms\Components\Toggle::make('inclure_routines')
                    ->label('Inclure les tâches routinières')
                    ->default(true),
                
                Forms\Components\Toggle::make('inclure_assignations')
                    ->label('Inclure les détails des assignations')
                    ->default(true),
                
                Forms\Components\Toggle::make('inclure_commentaires')
                    ->label('Inclure les commentaires')
                    ->default(false),
                
                Forms\Components\Toggle::make('inclure_fichiers')
                    ->label('Inclure la liste des fichiers')
                    ->default(false),
            ])
            ->action(function (array $data): void {
                // Construire la requête en fonction des filtres
                $query = Task::query()
                    ->with(['entreprise', 'createur']);
                
                if (!empty($data['statut'])) {
                    $query->where('statut', $data['statut']);
                }
                
                if (!empty($data['priorite'])) {
                    $query->where('priorite', $data['priorite']);
                }
                
                if (!empty($data['date_debut'])) {
                    $query->where('date_debut', '>=', $data['date_debut']);
                }
                
                if (!empty($data['date_fin'])) {
                    $query->where('date_fin', '<=', $data['date_fin']);
                }
                
                if (!$data['inclure_routines']) {
                    $query->where('est_routine', false);
                }
                
                // Charger les relations si nécessaire
                if ($data['inclure_assignations']) {
                    $query->with('assignations.employeur', 'assignations.assignable');
                }
                
                if ($data['inclure_commentaires']) {
                    $query->with('commentaires.user');
                }
                
                if ($data['inclure_fichiers']) {
                    $query->with('fichiers');
                }
                
                // Récupérer les tâches
                $taches = $query->get();
                
                // Générer et télécharger le fichier Excel
                $this->exporterTachesExcel($taches, $data);
                
                Notification::make()
                    ->title('Export terminé avec succès')
                    ->success()
                    ->send();
            });
    }
    
    protected function exporterTachesExcel($taches, $options): void
    {
        // Créer un nouveau document Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tâches');
        
        // Définir les en-têtes
        $headers = [
            'ID',
            'Titre',
            'Entreprise',
            'Date début',
            'Date fin',
            'Statut',
            'Priorité',
            'Type',
            'Routine',
            'Créateur',
            'Date création',
        ];
        
        // Ajouter les en-têtes
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }
        
        // Style pour les en-têtes
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A1:' . $this->getColumnLetter(count($headers)) . '1')->applyFromArray($headerStyle);
        
        // Remplir les données
        $row = 2;
        foreach ($taches as $tache) {
            $col = 1;
            
            $sheet->setCellValueByColumnAndRow($col++, $row, $tache->id);
            $sheet->setCellValueByColumnAndRow($col++, $row, $tache->titre);
            $sheet->setCellValueByColumnAndRow($col++, $row, $tache->entreprise->nom ?? '');
            $sheet->setCellValueByColumnAndRow($col++, $row, $tache->date_debut);
            $sheet->setCellValueByColumnAndRow($col++, $row, $tache->date_fin);
            
            // Formater le statut
            $statut = match ($tache->statut) {
                Task::STATUT_EN_ATTENTE => 'En attente',
                Task::STATUT_EN_COURS => 'En cours',
                Task::STATUT_TERMINE => 'Terminée',
                Task::STATUT_EN_RETARD => 'En retard',
                Task::STATUT_ANNULE => 'Annulée',
                default => $tache->statut,
            };
            $sheet->setCellValueByColumnAndRow($col++, $row, $statut);
            
            // Formater la priorité
            $priorite = match ($tache->priorite) {
                Task::PRIORITE_BASSE => 'Basse',
                Task::PRIORITE_BASSE => 'Normale',
                Task::PRIORITE_HAUTE => 'Haute',
                Task::PRIORITE_URGENTE => 'Urgente',
                default => $tache->priorite,
            };
            $sheet->setCellValueByColumnAndRow($col++, $row, $priorite);
            
            // Formater le type
            $type = match ($tache->type) {
                Task::TYPE_STANDARD => 'Standard',
                Task::TYPE_PROJET => 'Projet',
                Task::TYPE_REUNION => 'Réunion',
                Task::TYPE_FORMATION => 'Formation',
                Task::TYPE_AUTRE => 'Autre',
                default => $tache->type,
            };
            $sheet->setCellValueByColumnAndRow($col++, $row, $type);
            
            $sheet->setCellValueByColumnAndRow($col++, $row, $tache->est_routine ? 'Oui' : 'Non');
            $sheet->setCellValueByColumnAndRow($col++, $row, $tache->createur->name ?? '');
            $sheet->setCellValueByColumnAndRow($col++, $row, $tache->created_at);
            
            $row++;
        }
        
        // Ajouter des feuilles supplémentaires si nécessaire
        if ($options['inclure_assignations'] && $taches->isNotEmpty()) {
            $this->ajouterFeuilleAssignations($spreadsheet, $taches);
        }
        
        if ($options['inclure_commentaires'] && $taches->isNotEmpty()) {
            $this->ajouterFeuilleCommentaires($spreadsheet, $taches);
        }
        
        if ($options['inclure_fichiers'] && $taches->isNotEmpty()) {
            $this->ajouterFeuillesFichiers($spreadsheet, $taches);
        }
        
        // Auto-dimensionner les colonnes
        foreach (range('A', $this->getColumnLetter(count($headers))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Configurer les en-têtes pour le téléchargement
        $filename = 'export_taches_' . date('Y-m-d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Créer l'objet Writer et envoyer le fichier au navigateur
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    protected function ajouterFeuilleAssignations(Spreadsheet $spreadsheet, $taches): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Assignations');
        
        // En-têtes
        $headers = [
            'ID Tâche',
            'Titre Tâche',
            'Employé',
            'Type Assignation',
            'Entité',
            'Statut',
            'Progression',
            'Date début réelle',
            'Date fin réelle',
            'Commentaire',
        ];
        
        // Ajouter les en-têtes
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }
        
        // Style pour les en-têtes
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A1:' . $this->getColumnLetter(count($headers)) . '1')->applyFromArray($headerStyle);
        
        // Remplir les données
        $row = 2;
        foreach ($taches as $tache) {
            if ($tache->assignations->isEmpty()) {
                continue;
            }
            
            foreach ($tache->assignations as $assignation) {
                $col = 1;
                
                $sheet->setCellValueByColumnAndRow($col++, $row, $tache->id);
                $sheet->setCellValueByColumnAndRow($col++, $row, $tache->titre);
                $sheet->setCellValueByColumnAndRow($col++, $row, $assignation->employeur->nom_complet ?? '');
                
                // Type d'assignation
                $typeAssignation = 'Individuelle';
                if ($assignation->assignable_type === 'App\\Models\\Departement') {
                    $typeAssignation = 'Département';
                } elseif ($assignation->assignable_type === 'App\\Models\\Equipe') {
                    $typeAssignation = 'Équipe';
                } elseif ($assignation->assignable_type === null && $assignation->assignable_id === null) {
                    $typeAssignation = 'Globale';
                }
                $sheet->setCellValueByColumnAndRow($col++, $row, $typeAssignation);
                
                // Entité
                $entite = $assignation->assignable ? $assignation->assignable->nom : 'N/A';
                $sheet->setCellValueByColumnAndRow($col++, $row, $entite);
                
                // Statut
                $statut = match ($assignation->statut) {
                    Task::STATUT_EN_ATTENTE => 'En attente',
                    Task::STATUT_EN_COURS => 'En cours',
                    Task::STATUT_TERMINE => 'Terminée',
                    Task::STATUT_EN_RETARD => 'En retard',
                    Task::STATUT_ANNULE => 'Annulée',
                    default => $assignation->statut,
                };
                $sheet->setCellValueByColumnAndRow($col++, $row, $statut);
                
                $sheet->setCellValueByColumnAndRow($col++, $row, $assignation->progression . '%');
                $sheet->setCellValueByColumnAndRow($col++, $row, $assignation->date_debut_reelle);
                $sheet->setCellValueByColumnAndRow($col++, $row, $assignation->date_fin_reelle);
                $sheet->setCellValueByColumnAndRow($col++, $row, $assignation->commentaire);
                
                $row++;
            }
        }
        
        // Auto-dimensionner les colonnes
        foreach (range('A', $this->getColumnLetter(count($headers))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    protected function ajouterFeuilleCommentaires(Spreadsheet $spreadsheet, $taches): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Commentaires');
        
        // En-têtes
        $headers = [
            'ID Tâche',
            'Titre Tâche',
            'Auteur',
            'Date',
            'Privé',
            'Contenu',
        ];
        
        // Ajouter les en-têtes
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }
        
        // Style pour les en-têtes
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A1:' . $this->getColumnLetter(count($headers)) . '1')->applyFromArray($headerStyle);
        
        // Remplir les données
        $row = 2;
        foreach ($taches as $tache) {
            if ($tache->commentaires->isEmpty()) {
                continue;
            }
            
            foreach ($tache->commentaires as $commentaire) {
                $col = 1;
                
                $sheet->setCellValueByColumnAndRow($col++, $row, $tache->id);
                $sheet->setCellValueByColumnAndRow($col++, $row, $tache->titre);
                $sheet->setCellValueByColumnAndRow($col++, $row, $commentaire->user->name ?? '');
                $sheet->setCellValueByColumnAndRow($col++, $row, $commentaire->created_at);
                $sheet->setCellValueByColumnAndRow($col++, $row, $commentaire->prive ? 'Oui' : 'Non');
                
                // Nettoyer le contenu HTML
                $contenu = strip_tags($commentaire->contenu);
                $sheet->setCellValueByColumnAndRow($col++, $row, $contenu);
                
                $row++;
            }
        }
        
        // Auto-dimensionner les colonnes
        foreach (range('A', $this->getColumnLetter(count($headers))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    protected function ajouterFeuillesFichiers(Spreadsheet $spreadsheet, $taches): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Fichiers');
        
        // En-têtes
        $headers = [
            'ID Tâche',
            'Titre Tâche',
            'Nom fichier',
            'Taille',
            'Type',
            'Livrable',
            'Téléchargé par',
            'Date',
            'Description',
        ];
        
        // Ajouter les en-têtes
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }
        
        // Style pour les en-têtes
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A1:' . $this->getColumnLetter(count($headers)) . '1')->applyFromArray($headerStyle);
        
        // Remplir les données
        $row = 2;
        foreach ($taches as $tache) {
            if ($tache->fichiers->isEmpty()) {
                continue;
            }
            
            foreach ($tache->fichiers as $fichier) {
                $col = 1;
                
                $sheet->setCellValueByColumnAndRow($col++, $row, $tache->id);
                $sheet->setCellValueByColumnAndRow($col++, $row, $tache->titre);
                $sheet->setCellValueByColumnAndRow($col++, $row, $fichier->nom);
                $sheet->setCellValueByColumnAndRow($col++, $row, $fichier->taille_formatee);
                $sheet->setCellValueByColumnAndRow($col++, $row, $fichier->type_mime);
                $sheet->setCellValueByColumnAndRow($col++, $row, $fichier->est_livrable ? 'Oui' : 'Non');
                $sheet->setCellValueByColumnAndRow($col++, $row, $fichier->user->name ?? '');
                $sheet->setCellValueByColumnAndRow($col++, $row, $fichier->created_at);
                $sheet->setCellValueByColumnAndRow($col++, $row, $fichier->description);
                
                $row++;
            }
        }
        
        // Auto-dimensionner les colonnes
        foreach (range('A', $this->getColumnLetter(count($headers))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    protected function getColumnLetter(int $columnNumber): string
    {
        $columnLetter = '';
        while ($columnNumber > 0) {
            $modulo = ($columnNumber - 1) % 26;
            $columnLetter = chr(65 + $modulo) . $columnLetter;
            $columnNumber = (int)(($columnNumber - $modulo) / 26);
        }
        return $columnLetter;
    }
}
