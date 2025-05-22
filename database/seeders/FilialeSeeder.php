<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Filiale;
use App\Models\Entreprise;
use App\Models\Employeur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FilialeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();

            $entreprise = Entreprise::first();
            if (!$entreprise) {
                throw new \RuntimeException('Aucune entreprise trouvée. Veuillez exécuter EntrepriseSeeder d\'abord.');
            }

            $employeur = Employeur::first();
            if (!$employeur) {
                throw new \RuntimeException('Aucun employeur trouvé. Veuillez exécuter EmployeurSeeder d\'abord.');
            }

            // Création de la filiale principale
            $filialePrincipale = Filiale::create([
                'entreprise_id' => $entreprise->id,
                'nom' => 'Siège Social',
                'code' => 'SIEGE-001',
                'ville' => 'Abidjan',
                'pays' => 'Côte d\'Ivoire',
                'telephone' => '+225 27 20 00 00 00',
                'email' => 'siege@techcorp.com',
                'description' => 'Siège social de l\'entreprise',
                'configuration' => [
                    'type' => 'siege_social',
                    'devise' => 'XOF',
                    'fuseau_horaire' => 'Africa/Abidjan',
                    'limite_employes' => 500
                ],
                'statut' => 'actif'
            ]);

            // Ajout du responsable principal
            $filialePrincipale->responsables()->attach($employeur->id, [
                'id' => \Illuminate\Support\Str::uuid(), // Ajout de l'UUID pour la table pivot
                'role' => 'responsable_principal',
                'date_debut' => now(),
            ]);

            // Création d'une filiale secondaire
            $filialeSecondaire = Filiale::create([
                'entreprise_id' => $entreprise->id,
                'nom' => 'Filiale San Pedro',
                'code' => 'SANP-001',
                'ville' => 'San Pedro',
                'pays' => 'Côte d\'Ivoire',
                'telephone' => '+225 27 34 00 00 00',
                'email' => 'sanpedro@techcorp.com',
                'description' => 'Filiale régionale de San Pedro',
                'configuration' => [
                    'type' => 'filiale_regionale',
                    'devise' => 'XOF',
                    'fuseau_horaire' => 'Africa/Abidjan',
                    'limite_employes' => 200
                ],
                'statut' => 'actif'
            ]);

            // Ajout du responsable principal pour la filiale secondaire
            $filialeSecondaire->responsables()->attach($employeur->id, [
                'id' => \Illuminate\Support\Str::uuid(), // Ajout de l'UUID pour la table pivot
                'role' => 'responsable_principal',
                'date_debut' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du seeding des filiales: ' . $e->getMessage());
            throw $e;
        }
    }
}
