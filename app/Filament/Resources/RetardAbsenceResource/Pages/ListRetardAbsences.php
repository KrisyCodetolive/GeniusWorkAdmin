<?php

namespace App\Filament\Resources\RetardAbsenceResource\Pages;

use App\Filament\Resources\RetardAbsenceResource;
use App\Services\Presence\RetardAbsenceService;
use App\Filament\Widgets\RetardAbsenceStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use App\Models\Presence;
use League\Csv\Writer;
use App\Filament\Resources\SupplementaireResource;

class ListRetardAbsences extends ListRecords
{
    protected static string $resource = RetardAbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [

            \Filament\Actions\Action::make('heuresSupplementaires')
                ->label('Heures Supplémentaires')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(fn (): string => SupplementaireResource::getUrl())
                ->visible(fn (): bool => auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),

            Actions\Action::make('verifierRetards')
                ->label('Vérifier les retards')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->action(function () {
                    $retardService = app(RetardAbsenceService::class);
                    $stats = $retardService->verifierRetards();
                    
                    $retardsDetectes = isset($stats['retards_detectes']) ? (is_array($stats['retards_detectes']) ? count($stats['retards_detectes']) : $stats['retards_detectes']) : 0;
                    $notificationsEnvoyees = isset($stats['notifications_envoyees']) ? (is_array($stats['notifications_envoyees']) ? count($stats['notifications_envoyees']) : $stats['notifications_envoyees']) : 0;
                    
                    Notification::make()
                        ->title('Vérification des retards terminée')
                        ->body("Retards détectés: {$retardsDetectes}, Notifications envoyées: {$notificationsEnvoyees}")
                        ->success()
                        ->send();
                    
                }),
                
            Actions\Action::make('verifierAbsences')
                ->label('Vérifier les absences')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function () {
                    $retardService = app(RetardAbsenceService::class);
                    $stats = $retardService->verifierAbsences();
                    
                    $absencesDetectees = isset($stats['absences_detectees']) ? (is_array($stats['absences_detectees']) ? count($stats['absences_detectees']) : $stats['absences_detectees']) : 0;
                    $notificationsEnvoyees = isset($stats['notifications_envoyees']) ? (is_array($stats['notifications_envoyees']) ? count($stats['notifications_envoyees']) : $stats['notifications_envoyees']) : 0;
                    
                    Notification::make()
                        ->title('Vérification des absences terminée')
                        ->body("Absences détectées: {$absencesDetectees}, Notifications envoyées: {$notificationsEnvoyees}")
                        ->success()
                        ->send();
                    
                }),
                
            Actions\Action::make('verifierTout')
                ->label('Vérifier tout')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(function () {
                    $retardService = app(RetardAbsenceService::class);
                    $stats = $retardService->executerToutesVerifications();
                    
                    $retardsDetectes = isset($stats['retards_detectes']) ? (is_array($stats['retards_detectes']) ? count($stats['retards_detectes']) : $stats['retards_detectes']) : 0;
                    $absencesDetectees = isset($stats['absences_detectees']) ? (is_array($stats['absences_detectees']) ? count($stats['absences_detectees']) : $stats['absences_detectees']) : 0;
                    $sortiesManquantes = isset($stats['sorties_manquantes']) ? (is_array($stats['sorties_manquantes']) ? count($stats['sorties_manquantes']) : $stats['sorties_manquantes']) : 0;
                    
                    Notification::make()
                        ->title('Vérification complète terminée')
                        ->body("Retards: {$retardsDetectes}, Absences: {$absencesDetectees}, Sorties manquantes: {$sortiesManquantes}")
                        ->success()
                        ->send();
                    
                }),
                
            Actions\Action::make('rapportRetardsAbsences')
                ->label('Générer rapport')
                ->icon('heroicon-o-document-chart-bar')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('date_debut')
                        ->label('Date de début')
                        ->default(Carbon::now()->startOfMonth())
                        ->required(),
                    \Filament\Forms\Components\DatePicker::make('date_fin')
                        ->label('Date de fin')
                        ->default(Carbon::now())
                        ->required(),
                    \Filament\Forms\Components\Select::make('type')
                        ->label('Type de rapport')
                        ->options([
                            'tous' => 'Tous',
                            'retards' => 'Retards uniquement',
                            'absences' => 'Absences uniquement',
                        ])
                        ->default('tous')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $dateDebut = Carbon::parse($data['date_debut'])->startOfDay();
                    $dateFin = Carbon::parse($data['date_fin'])->endOfDay();
                    $type = $data['type'];
                    
                    $query = Presence::query()
                        ->whereDate('date_heure', '>=', $dateDebut)
                        ->whereDate('date_heure', '<=', $dateFin)
                        ->with(['employeur', 'employeur.entreprise', 'site']);
                    
                    // Filtrer selon le type de rapport
                    if ($type === 'retards') {
                        $query->where('minutes_retard', '>', 0);
                    } elseif ($type === 'absences') {
                        // Pour les absences, nous devons vérifier les enregistrements marqués comme absences
                        // Cela peut être un champ spécifique ou une combinaison de conditions
                        // Adaptons la requête selon la structure réelle
                        $query->where(function($q) {
                            // Essayons différentes approches possibles pour identifier les absences
                            $q->where('type', 'absence')
                              ->orWhere('statut', 'absence');
                        });
                    } else {
                        // Pour 'tous', nous combinons les deux requêtes
                        $query->where(function ($q) {
                            $q->where('minutes_retard', '>', 0)
                              ->orWhere('type', 'absence')
                              ->orWhere('statut', 'absence');
                        });
                    }
                    
                    $user = auth()->user();
                    if (!$user->isSuperAdmin()) {
                        $entrepriseIds = [$user->entreprise->id]; // Convertir en tableau
                        $query->whereHas('employeur', function ($q) use ($entrepriseIds) {
                            $q->whereIn('entreprise_id', $entrepriseIds);
                        });
                    }
                    
                    $presences = $query->get();
                    
                    $typeRapport = match($type) {
                        'retards' => 'retards',
                        'absences' => 'absences',
                        default => 'retards-et-absences',
                    };
                    
                    $nomFichier = "rapport-{$typeRapport}-" . $dateDebut->format('Y-m-d') . "-au-" . $dateFin->format('Y-m-d');
                    
                    $donnees = $presences->map(function ($presence) {
                        $type = 'N/A';
                        if ($presence->minutes_retard > 0) {
                            $type = 'Retard';
                        } elseif ($presence->type === 'absence' || $presence->statut === 'absence') {
                            $type = 'Absence';
                        }
                        
                        return [
                            'Date' => $presence->date_heure ? $presence->date_heure->format('d/m/Y') : ($presence->date_heure_entree ? $presence->date_heure_entree->format('d/m/Y') : 'N/A'),
                            'Heure d\'entrée' => $presence->date_heure_entree ? $presence->date_heure_entree->format('H:i') : 'N/A',
                            'Heure de sortie' => $presence->date_heure_sortie ? $presence->date_heure_sortie->format('H:i') : 'N/A',
                            'Employé' => $presence->employeur ? $presence->employeur->nom_complet : 'N/A',
                            'Entreprise' => $presence->employeur && $presence->employeur->entreprise ? $presence->employeur->entreprise->nom : 'N/A',
                            'Site' => $presence->site ? $presence->site->nom : 'N/A',
                            'Type' => $type,
                            'Minutes de retard' => $presence->minutes_retard ?? 0,
                            'Statut' => $presence->statut ?? 'N/A',
                        ];
                    });
                    
                    // Vérifier si des données ont été trouvées
                    if ($donnees->isEmpty()) {
                        Notification::make()
                            ->title('Aucune donnée')
                            ->body("Aucune donnée n'a été trouvée pour la période et le type sélectionnés.")
                            ->warning()
                            ->send();
                        return;
                    }
                    
                    $csv = Writer::createFromFileObject(new \SplTempFileObject());
                    
                    // Ajouter les en-têtes (premier élément du tableau)
                    $csv->insertOne(array_keys($donnees->first()));
                    
                    $csv->insertAll($donnees->toArray());
                    
                    $csv->output($nomFichier . '.csv');
                    
                    $typeRapportAffichage = match($data['type']) {
                        'retards' => 'retards',
                        'absences' => 'absences',
                        default => 'retards et absences',
                    };
                    
                    Notification::make()
                        ->title('Rapport généré')
                        ->body("Le rapport des {$typeRapportAffichage} du {$dateDebut->format('d/m/Y')} au {$dateFin->format('d/m/Y')} a été généré.")
                        ->success()
                        ->send();
                }),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            RetardAbsenceStatsWidget::class,
        ];
    }
    
    protected function getDefaultTableSortColumn(): ?string
    {
        return 'date_heure_entree';
    }
    
    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }
}
