<?php

namespace App\Filament\Resources\DemandeDepenseResource\Actions;

use App\Filament\Resources\DemandeDepenseResource;
use App\Filament\Resources\DemandeDepenseResource\Pages\ViewDemandeDepense;
use App\Models\DemandeDepense;
use App\Services\DepenseWorkflowService;
use Filament\Actions\Action as PageAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

/**
 * Actions qui font avancer une demande dans le circuit, partagées entre la liste (lignes du tableau)
 * et la page de détail. Chaque méthode reçoit la classe d'action à construire :
 * TableAction::class pour le tableau, PageAction::class pour l'en-tête de page.
 * La visibilité suit DemandeDepensePolicy : chaque utilisateur ne voit que ce qu'il a le droit de faire.
 *
 * @template T of PageAction|TableAction
 */
class CircuitActions
{
    /**
     * @param  class-string<T>  $classe
     * @return array<T>
     */
    public static function toutes(string $classe): array
    {
        return [
            static::soumettre($classe),
            static::valider($classe),
            static::decaisser($classe),
            static::validerJustification($classe),
            static::renvoyerJustification($classe),
            static::renvoyer($classe),
            static::rejeter($classe),
            static::bonSortie($classe),
            static::annuler($classe),
        ];
    }

    /** @param  class-string<T>  $classe */
    public static function soumettre(string $classe): PageAction|TableAction
    {
        return $classe::make('soumettre')
            ->label('Soumettre')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->visible(fn (DemandeDepense $record) => auth()->user()->can('soumettre', $record))
            ->requiresConfirmation()
            ->modalHeading(fn (DemandeDepense $record) => "Soumettre la demande {$record->reference}")
            ->modalDescription('Une fois soumise, la demande ne pourra plus être modifiée. Elle part en validation à la comptabilité.')
            ->modalSubmitActionLabel('Soumettre')
            ->action(fn (DemandeDepense $record, $livewire) => static::executer(
                $livewire,
                fn (DepenseWorkflowService $service) => $service->soumettre($record, auth()->user()),
                'Demande soumise à la comptabilité.'
            ));
    }

    /** @param  class-string<T>  $classe */
    public static function valider(string $classe): PageAction|TableAction
    {
        return $classe::make('valider')
            ->label('Valider et signer')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn (DemandeDepense $record) => auth()->user()->can('validerComptabilite', $record)
                || auth()->user()->can('validerCeo', $record))
            ->modalHeading(fn (DemandeDepense $record) => $record->statut === DemandeDepense::STATUT_EN_ATTENTE_CEO
                ? "Validation du CEO — {$record->reference}"
                : "Validation de la comptabilité — {$record->reference}")
            ->modalDescription(fn (DemandeDepense $record) => static::resumeValidation($record))
            ->modalSubmitActionLabel('Valider et signer')
            ->form([
                SignaturePad::make('signature')
                    ->label('Votre signature')
                    ->required(),
                Forms\Components\Textarea::make('commentaire')
                    ->label('Commentaire (facultatif)')
                    ->rows(2),
            ])
            ->action(fn (DemandeDepense $record, array $data, $livewire) => static::executer(
                $livewire,
                fn (DepenseWorkflowService $service) => $record->statut === DemandeDepense::STATUT_EN_ATTENTE_CEO
                    ? $service->validerCeo($record, auth()->user(), $data['signature'], $data['commentaire'] ?? null)
                    : $service->validerComptabilite($record, auth()->user(), $data['signature'], $data['commentaire'] ?? null),
                'Demande validée et signée.'
            ));
    }

    /** @param  class-string<T>  $classe */
    public static function decaisser(string $classe): PageAction|TableAction
    {
        return $classe::make('decaisser')
            ->label('Enregistrer le paiement')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->visible(fn (DemandeDepense $record) => auth()->user()->can('decaisser', $record))
            ->modalHeading(fn (DemandeDepense $record) => "Paiement de {$record->reference}")
            ->modalDescription(fn (DemandeDepense $record) => static::montant($record).' à '.$record->beneficiaire.'.')
            ->modalSubmitActionLabel('Enregistrer le paiement')
            ->form([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('mode_paiement')
                            ->label('Mode de paiement')
                            ->options(DemandeDepense::MODES_PAIEMENT)
                            ->required(),
                        Forms\Components\TextInput::make('reference_paiement')
                            ->label('Référence du paiement')
                            ->helperText('N° de chèque, de virement ou de transaction.'),
                        Forms\Components\DatePicker::make('date_paiement')
                            ->label('Date du paiement')
                            ->default(now())
                            ->maxDate(now())
                            ->required(),
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Commentaire (facultatif)')
                            ->rows(1)
                            ->autosize(),
                        Forms\Components\FileUpload::make('preuve_paiement')
                            ->label('Preuve de paiement')
                            ->disk(DepenseWorkflowService::DISQUE)
                            ->directory('depenses/paiements')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->columnSpanFull(),
                    ]),
            ])
            ->action(fn (DemandeDepense $record, array $data, $livewire) => static::executer(
                $livewire,
                fn (DepenseWorkflowService $service) => $service->decaisser($record, auth()->user(), $data),
                'Paiement enregistré. Le bon de sortie est archivé.'
            ));
    }

    /** @param  class-string<T>  $classe */
    public static function validerJustification(string $classe): PageAction|TableAction
    {
        return $classe::make('validerJustification')
            ->label('Clôturer la demande')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (DemandeDepense $record) => auth()->user()->can('validerJustification', $record))
            ->modalHeading(fn (DemandeDepense $record) => "Vérification des justificatifs — {$record->reference}")
            ->modalDescription('Les reçus joints par le demandeur sont conformes : la demande est clôturée.')
            ->modalSubmitActionLabel('Clôturer')
            ->form([
                Forms\Components\Textarea::make('commentaire')
                    ->label('Commentaire (facultatif)')
                    ->rows(2),
            ])
            ->action(fn (DemandeDepense $record, array $data, $livewire) => static::executer(
                $livewire,
                fn (DepenseWorkflowService $service) => $service->validerJustification($record, auth()->user(), $data['commentaire'] ?? null),
                'Demande clôturée.'
            ));
    }

    /** @param  class-string<T>  $classe */
    public static function renvoyerJustification(string $classe): PageAction|TableAction
    {
        return $classe::make('renvoyerJustification')
            ->label('Renvoyer les justificatifs')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->visible(fn (DemandeDepense $record) => auth()->user()->can('validerJustification', $record))
            ->modalHeading(fn (DemandeDepense $record) => "Renvoyer les justificatifs de {$record->reference}")
            ->modalDescription('La demande retourne à « payée » : le demandeur devra compléter ses reçus et soumettre à nouveau.')
            ->modalSubmitActionLabel('Renvoyer')
            ->form([
                Forms\Components\Textarea::make('motif')
                    ->label('Ce qui manque ou doit être corrigé')
                    ->required(),
            ])
            ->action(fn (DemandeDepense $record, array $data, $livewire) => static::executer(
                $livewire,
                fn (DepenseWorkflowService $service) => $service->renvoyerJustification($record, auth()->user(), $data['motif']),
                'Justificatifs renvoyés au demandeur.'
            ));
    }

    /** @param  class-string<T>  $classe */
    public static function renvoyer(string $classe): PageAction|TableAction
    {
        return $classe::make('renvoyer')
            ->label('Renvoyer pour correction')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->visible(fn (DemandeDepense $record) => auth()->user()->can('rejeter', $record))
            ->modalHeading(fn (DemandeDepense $record) => "Renvoyer {$record->reference} au demandeur")
            ->modalSubmitActionLabel('Renvoyer')
            ->form([
                Forms\Components\Textarea::make('motif')
                    ->label('Ce qu\'il faut corriger')
                    ->required(),
            ])
            ->action(fn (DemandeDepense $record, array $data, $livewire) => static::executer(
                $livewire,
                fn (DepenseWorkflowService $service) => $service->renvoyer($record, auth()->user(), $data['motif']),
                'Demande renvoyée au demandeur.'
            ));
    }

    /** @param  class-string<T>  $classe */
    public static function rejeter(string $classe): PageAction|TableAction
    {
        return $classe::make('rejeter')
            ->label('Rejeter')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (DemandeDepense $record) => auth()->user()->can('rejeter', $record))
            ->modalHeading(fn (DemandeDepense $record) => "Rejeter {$record->reference}")
            ->modalSubmitActionLabel('Rejeter')
            ->form([
                Forms\Components\Textarea::make('motif')
                    ->label('Motif du rejet')
                    ->required(),
            ])
            ->action(fn (DemandeDepense $record, array $data, $livewire) => static::executer(
                $livewire,
                fn (DepenseWorkflowService $service) => $service->rejeter($record, auth()->user(), $data['motif']),
                'Demande rejetée.'
            ));
    }

    /** @param  class-string<T>  $classe */
    public static function bonSortie(string $classe): PageAction|TableAction
    {
        return $classe::make('bonSortie')
            ->label('Bon de sortie (PDF)')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->visible(fn (DemandeDepense $record) => auth()->user()->can('telechargerBon', $record))
            ->url(fn (DemandeDepense $record) => route('depenses.bon-sortie', $record))
            ->openUrlInNewTab();
    }

    /** @param  class-string<T>  $classe */
    public static function annuler(string $classe): PageAction|TableAction
    {
        return $classe::make('annuler')
            ->label('Annuler la demande')
            ->icon('heroicon-o-no-symbol')
            ->color('gray')
            ->visible(fn (DemandeDepense $record) => auth()->user()->can('annuler', $record))
            ->requiresConfirmation()
            ->modalHeading(fn (DemandeDepense $record) => "Annuler la demande {$record->reference}")
            ->modalSubmitActionLabel('Annuler la demande')
            ->action(fn (DemandeDepense $record, $livewire) => static::executer(
                $livewire,
                fn (DepenseWorkflowService $service) => $service->annuler($record, auth()->user()),
                'Demande annulée.'
            ));
    }

    private static function resumeValidation(DemandeDepense $record): string
    {
        $suite = $record->statut === DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE && $record->necessiteValidationCeo()
            ? 'Montant au-dessus du seuil : la demande partira ensuite chez le CEO.'
            : 'Après votre signature, la demande sera approuvée et pourra être payée.';

        return "{$record->objet} — ".static::montant($record).". {$suite}";
    }

    private static function montant(DemandeDepense $record): string
    {
        return number_format((float) $record->montant, 0, ',', ' ').' '.$record->devise;
    }

    /**
     * Exécute une étape du circuit et affiche le résultat ; les refus (droits, données invalides)
     * s'affichent en notification au lieu d'une erreur. Sur la page de détail, on recharge la page
     * pour mettre à jour la fiche et les onglets ; dans la liste, le tableau se rafraîchit seul.
     */
    private static function executer($livewire, callable $etape, string $succes): void
    {
        try {
            $demande = $etape(app(DepenseWorkflowService::class));
        } catch (AuthorizationException) {
            Notification::make()
                ->danger()
                ->title('Action impossible')
                ->body("Vous ne pouvez pas effectuer cette action sur cette demande (droits insuffisants, ou la demande a déjà changé d'étape).")
                ->send();

            return;
        } catch (InvalidArgumentException $e) {
            Notification::make()
                ->danger()
                ->title('Action impossible')
                ->body($e->getMessage())
                ->send();

            return;
        }

        Notification::make()->success()->title($succes)->send();

        if ($livewire instanceof ViewDemandeDepense) {
            $livewire->redirect(DemandeDepenseResource::getUrl('view', ['record' => $demande]));
        }
    }
}
