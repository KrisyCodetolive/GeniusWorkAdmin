<?php

namespace App\Filament\Resources\CongeResource\Pages;

use App\Filament\Resources\CongeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Services\CongePdfService;
use Illuminate\Support\Facades\Storage;

class ViewConge extends ViewRecord
{
    protected static string $resource = CongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadPdf')
                ->label('Télécharger l\'attestation')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->visible(fn () => $this->record->statut === 'approuve')
                ->action(function () {
                    $pdfService = app(CongePdfService::class);
                    $pdfPath = $pdfService->generatePdf($this->record);
                    
                    return response()->download(
                        Storage::disk('public')->path($pdfPath),
                        'attestation_conge_' . $this->record->id . '.pdf'
                    );
                }),
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->hasRole('admin')),
            Actions\Action::make('approve')
                ->label('Approuver')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => $this->record->statut === 'en_attente' && auth()->user()->hasRole(['admin', 'manager']))
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('commentaire')
                        ->label('Commentaire (optionnel)')
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $congeService = app(\App\Services\CongeService::class);
                    $congeService->approuverDemande($this->record, auth()->user(), $data['commentaire'] ?? null);
                    
                    $this->redirect(CongeResource::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('reject')
                ->label('Rejeter')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => $this->record->statut === 'en_attente' && auth()->user()->hasRole(['admin', 'manager']))
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('commentaire')
                        ->label('Motif du rejet')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $congeService = app(\App\Services\CongeService::class);
                    $congeService->rejeterDemande($this->record, auth()->user(), $data['commentaire']);
                    
                    $this->redirect(CongeResource::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('cancel')
                ->label('Annuler')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->visible(fn () => ($this->record->statut === 'en_attente' || $this->record->statut === 'approuve') && 
                    (auth()->user()->hasRole('admin') || auth()->id() === $this->record->employeur->user_id))
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('commentaire')
                        ->label('Motif de l\'annulation (optionnel)')
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $congeService = app(\App\Services\CongeService::class);
                    $congeService->annulerDemande($this->record, $data['commentaire'] ?? null);
                    
                    $this->redirect(CongeResource::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
