<?php

namespace App\Filament\Resources\Concerns;

use App\Models\Entreprise;
use Filament\Forms;

/**
 * Champ « Entreprise » des ressources de comptabilité : le super admin et le support la choisissent,
 * les autres utilisateurs travaillent toujours dans leur propre entreprise.
 */
trait AvecEntreprise
{
    public static function choisitEntreprise(): bool
    {
        $user = auth()->user();

        return $user->isSuperAdmin() || $user->isSupport();
    }

    public static function champEntreprise(): Forms\Components\Select
    {
        return Forms\Components\Select::make('entreprise_id')
            ->label('Entreprise')
            ->options(fn () => Entreprise::orderBy('nom')->pluck('nom', 'id'))
            ->searchable()
            ->required()
            ->live()
            ->default(fn () => auth()->user()->entreprise_id)
            ->visible(fn () => static::choisitEntreprise())
            ->disabledOn('edit');
    }

    /**
     * Entreprise du formulaire en cours : celle choisie, sinon celle de l'utilisateur.
     */
    public static function entrepriseCourante(Forms\Get $get): ?string
    {
        return $get('entreprise_id') ?: auth()->user()->entreprise_id;
    }

    public static function forcerEntreprise(array $data): array
    {
        if (! static::choisitEntreprise() || empty($data['entreprise_id'])) {
            $data['entreprise_id'] = auth()->user()->entreprise_id;
        }

        return $data;
    }
}
