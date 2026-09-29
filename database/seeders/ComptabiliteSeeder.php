<?php

namespace Database\Seeders;

use App\Models\Entreprise;
use App\Models\ParametreDepense;
use Illuminate\Database\Seeder;

/**
 * Paramètres de dépenses (seuil CEO par défaut) et catégories de départ pour chaque entreprise.
 * Idempotent : peut être relancé sans créer de doublons.
 */
class ComptabiliteSeeder extends Seeder
{
    public function run(): void
    {
        Entreprise::query()->each(fn (Entreprise $entreprise) => ParametreDepense::pour($entreprise->id));
    }
}
