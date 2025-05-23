<?php

namespace App\Filament\Actions;

use App\Models\Presence;
use Filament\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Filament\Support\Concerns\HasColor;
use Filament\Support\Concerns\HasIcon;

class ExporterRetardAbsenceAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'exporter_retard_absence';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Exporter')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->size(ActionSize::Large)
            ->form([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date de début')
                            ->default(Carbon::now()->startOfMonth())
                            ->required(),
                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date de fin')
                            ->default(Carbon::now())
                            ->required(),
                    ]),
                Forms\Components\Select::make('statut')
                    ->label('Type')
                    ->options([
                        'retard' => 'Retards',
                        'absent' => 'Absences',
                        'tous' => 'Tous',
                    ])
                    ->default('tous')
                    ->required(),
                Forms\Components\Select::make('statut_validation')
                    ->label('Statut de validation')
                    ->options([
                        'en_attente' => 'En attente',
                        'approve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        'tous' => 'Tous',
                    ])
                    ->default('tous')
                    ->required(),
                Forms\Components\Toggle::make('inclure_commentaires')
                    ->label('Inclure les commentaires')
                    ->default(true),
            ])
            ->action(function (array $data) {
                $query = Presence::query()
                    ->whereIn('statut', ['retard', 'absent'])
                    ->whereDate('date_heure_entree', '>=', $data['date_debut'])
                    ->whereDate('date_heure_entree', '<=', $data['date_fin']);

                if ($data['statut'] !== 'tous') {
                    $query->where('statut', $data['statut']);
                }

                if ($data['statut_validation'] !== 'tous') {
                    $query->where('statut_validation', $data['statut_validation']);
                }

                $presences = $query->with(['user', 'employeur', 'validateur'])->get();
                
                // Générer le fichier Excel
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                
                // Définir les en-têtes
                $headers = [
                    'Date', 'Employé', 'Employeur', 'Type', 'Durée (min)', 
                    'Statut validation', 'Validé par', 'Date validation'
                ];
                
                if ($data['inclure_commentaires']) {
                    $headers[] = 'Commentaire';
                }
                
                foreach ($headers as $index => $header) {
                    $sheet->setCellValue(chr(65 + $index) . '1', $header);
                    $sheet->getStyle(chr(65 + $index) . '1')->getFont()->setBold(true);
                }
                
                // Remplir les données
                $row = 2;
                foreach ($presences as $presence) {
                    $columnIndex = 0;
                    
                    $sheet->setCellValue(chr(65 + $columnIndex++) . $row, $presence->date_heure_entree->format('d/m/Y H:i'));
                    $sheet->setCellValue(chr(65 + $columnIndex++) . $row, $presence->user ? $presence->user->nom . ' ' . $presence->user->prenom : 'Non défini');
                    $sheet->setCellValue(chr(65 + $columnIndex++) . $row, $presence->employeur ? $presence->employeur->nom : 'Non défini');
                    $sheet->setCellValue(chr(65 + $columnIndex++) . $row, $presence->statut === 'retard' ? 'Retard' : 'Absence');
                    $sheet->setCellValue(chr(65 + $columnIndex++) . $row, $presence->statut === 'retard' ? $presence->retard : 'N/A');
                    
                    $statut_validation = match ($presence->statut_validation) {
                        'en_attente' => 'En attente',
                        'approve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        default => $presence->statut_validation,
                    };
                    $sheet->setCellValue(chr(65 + $columnIndex++) . $row, $statut_validation);
                    
                    $sheet->setCellValue(chr(65 + $columnIndex++) . $row, $presence->validateur ? $presence->validateur->nom . ' ' . $presence->validateur->prenom : 'Non validé');
                    $sheet->setCellValue(chr(65 + $columnIndex++) . $row, $presence->date_validation ? $presence->date_validation->format('d/m/Y H:i') : 'N/A');
                    
                    if ($data['inclure_commentaires']) {
                        $sheet->setCellValue(chr(65 + $columnIndex++) . $row, $presence->commentaire ?? '');
                    }
                    
                    $row++;
                }
                
                // Ajuster la largeur des colonnes
                foreach (range('A', chr(65 + count($headers) - 1)) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                // Créer le fichier temporaire
                $tempFile = tempnam(sys_get_temp_dir(), 'export_');
                $writer = new Xlsx($spreadsheet);
                $writer->save($tempFile);
                
                // Envoyer le fichier au navigateur
                $fileName = 'retards-absences_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';
                
                return response()->download($tempFile, $fileName, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])->deleteFileAfterSend(true);
            });
    }



    public static function make(?string $name = null): static
    {
        $action = parent::make($name);
        $action->setUp();
        return $action;
    }
}
