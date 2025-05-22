<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departement;
use App\Models\Entreprise;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $entreprise = Entreprise::first();
            
            if (!$entreprise) {
                throw new \RuntimeException('Aucune entreprise trouvée. Veuillez exécuter EntrepriseSeeder d\'abord.');
            }

            // Liste des départements à créer
            $departements = [
                [
                    'nom' => 'Direction Générale',
                    'code' => 'DG-' . Str::upper(Str::random(4)),
                    'description' => 'Direction et administration',
                ],
                [
                    'nom' => 'Ressources Humaines',
                    'code' => 'RH-' . Str::upper(Str::random(4)),
                    'description' => 'Gestion des ressources humaines',
                ],
                [
                    'nom' => 'Finance et Comptabilité',
                    'code' => 'FIN-' . Str::upper(Str::random(4)),
                    'description' => 'Gestion financière et comptable',
                ],
                [
                    'nom' => 'Technologies',
                    'code' => 'TECH-' . Str::upper(Str::random(4)),
                    'description' => 'Développement et maintenance des systèmes',
                ],
            ];

            foreach ($departements as $dept) {
                Departement::create([
                    'entreprise_id' => $entreprise->id,
                    'nom' => $dept['nom'],
                    'code' => $dept['code'],
                    'description' => $dept['description'],
                    'statut' => 'actif'
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
