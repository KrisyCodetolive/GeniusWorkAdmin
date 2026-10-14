<?php

namespace Database\Seeders;

use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\Paie\BulletinPaie;
use App\Models\Paie\ConfigurationPaie;
use App\Models\Paie\ElementPaie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Bulletin d'exemple reproduisant fidèlement le template bulletin_genius_work_squelette.html
 * (valeurs par défaut du modèle : salarié KOUASSI Jean, septembre 2026).
 *
 * Lancement manuel : php artisan db:seed --class=BulletinPaieTemplateSeeder
 * Relançable sans doublon : le bulletin est identifié par sa référence.
 */
class BulletinPaieTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $entreprise = Entreprise::firstOrCreate(
                ['email' => 'info@geniusgroups.ci'],
                [
                    'nom' => 'GENIUS GROUPS SAS',
                    'raison_sociale' => 'GENIUS GROUPS SAS',
                    'rccm' => 'CI-ABJ-03-2025-B13-00081',
                    'adresse' => "Abidjan — Cocody, Route d'Abatta",
                    'telephone' => '0704750465',
                    'pays' => "Côte d'Ivoire",
                    'devise' => 'FCFA',
                    'statut' => 'actif',
                ]
            );

            // Configuration de paie par défaut : alimente le champ « Configuration de paie » des formulaires de bulletin
            $this->creerConfigurationPaie($entreprise);

            // NCC et N° CNPS "À renseigner" dans le template : non renseignés
            $employeur = $this->creerSalarie($entreprise, [
                'matricule' => 'MAT-GEN-001',
                'nom' => 'KOUASSI',
                'prenom' => 'Jean',
                'poste' => 'Assistant administratif', // affiché dans « Fonction / poste »
                'type_contrat' => 'CDI', // non précisé dans le template : à confirmer
                'date_embauche' => '2026-09-01',
                'salaire_base' => 250000,
            ]);

            // Autres salariés de démonstration (sans bulletin) pour tester la génération de bulletins
            foreach (self::AUTRES_SALARIES as $donnees) {
                $this->creerSalarie($entreprise, $donnees);
            }

            // withTrashed : un bulletin supprimé (soft delete) garde sa référence, on le restaure
            $bulletin = BulletinPaie::withTrashed()->updateOrCreate(
                ['reference' => 'MA-202609-2001'],
                [
                    'employeur_id' => $employeur->id,
                    'entreprise_id' => $entreprise->id,
                    'periode_debut' => '2026-09-01',
                    'periode_fin' => '2026-09-30',
                    'date_paiement' => '2026-09-30',
                    'salaire_base' => 250000,
                    'total_indemnites' => 30000,
                    'total_primes' => 0,
                    'salaire_brut' => 280000,
                    'cnps_employe' => 17640,
                    'igr' => 34800,
                    // retraite 17 640 + CMU 500 + IGR 34 800
                    'total_retenues' => 52940,
                    'salaire_net' => 227060,
                    'cnps_employeur' => 27372.50,
                    // CNPS 27 372,50 + CMU 500
                    'charges_patronales' => 27872.50,
                    'statut' => 'brouillon',
                    // Champs du gabarit sans colonne dédiée (lus par la vue PDF)
                    'meta_donnees' => [
                        'jours_heures' => '22 jours / 176 h',
                        'reference_paiement' => 'PAY-202609-001',
                    ],
                ]
            );

            if ($bulletin->trashed()) {
                $bulletin->restore();
            }

            // Suppression définitive des anciennes lignes pour ne pas accumuler de lignes soft-deleted
            ElementPaie::withTrashed()->where('bulletin_paie_id', $bulletin->id)->forceDelete();
            $this->creerElements($bulletin);
        });

        $this->command?->info('Bulletin MA-202609-2001 créé (net à payer : 227 060 FCFA).');
        $this->command?->info('Configuration de paie par défaut et ' . (count(self::AUTRES_SALARIES) + 1) . ' salariés GENIUS GROUPS SAS en place.');
    }

    /**
     * Salariés de démonstration supplémentaires de GENIUS GROUPS SAS.
     */
    private const AUTRES_SALARIES = [
        ['matricule' => 'MAT-GEN-002', 'nom' => 'KONAN', 'prenom' => 'Aya Marie', 'genre' => 'F', 'date_naissance' => '1994-03-12', 'poste' => 'Comptable', 'type_contrat' => 'CDI', 'date_embauche' => '2025-02-03', 'salaire_base' => 350000],
        ['matricule' => 'MAT-GEN-003', 'nom' => 'TRAORÉ', 'prenom' => 'Moussa', 'genre' => 'M', 'date_naissance' => '1990-07-25', 'poste' => 'Développeur web', 'type_contrat' => 'CDI', 'date_embauche' => '2025-04-14', 'salaire_base' => 450000],
        ['matricule' => 'MAT-GEN-004', 'nom' => 'YAO', 'prenom' => 'Christelle', 'genre' => 'F', 'date_naissance' => '1997-11-02', 'poste' => 'Chargée de communication', 'type_contrat' => 'CDD', 'date_embauche' => '2026-01-05', 'date_fin_contrat' => '2026-12-31', 'salaire_base' => 220000],
        ['matricule' => 'MAT-GEN-005', 'nom' => 'COULIBALY', 'prenom' => 'Ibrahim', 'genre' => 'M', 'date_naissance' => '1985-05-18', 'poste' => 'Responsable des opérations', 'type_contrat' => 'CDI', 'date_embauche' => '2024-09-02', 'salaire_base' => 650000],
        ['matricule' => 'MAT-GEN-006', 'nom' => "N'GUESSAN", 'prenom' => 'Paul', 'genre' => 'M', 'date_naissance' => '2002-08-30', 'poste' => 'Stagiaire support client', 'type_contrat' => 'Stage', 'date_embauche' => '2026-07-01', 'date_fin_contrat' => '2026-12-31', 'salaire_base' => 75000],
    ];

    /**
     * Configuration de paie par défaut, alignée sur les taux du template.
     * Limite : le service de calcul applique un seul plafond à toutes les cotisations,
     * alors que le template plafonne prestations familiales, maternité et AT à 75 000.
     * Le calcul automatique ne reproduit donc pas exactement le bulletin du template.
     */
    private function creerConfigurationPaie(Entreprise $entreprise): void
    {
        ConfigurationPaie::withTrashed()->updateOrCreate(
            ['entreprise_id' => $entreprise->id, 'nom' => 'Configuration standard'],
            [
                'description' => 'Taux du bulletin modèle Genius Work (Côte d\'Ivoire)',
                'est_defaut' => true,
                'smig' => 75000,
                'plafond_cnps' => 3375000, // plafond retraite CNPS (45 × SMIG)
                'taux_cnps_employe' => 6.3,
                'taux_cnps_employeur' => 7.7,
                'taux_prestations_familiales' => 5,
                'taux_accident_travail' => 2,
                'taux_assurance_maladie' => 0.75, // assurance maternité
                'abattement_igr' => 20,
                'baremes_igr' => [
                    ['min' => 0, 'max' => 25000, 'taux' => 0],
                    ['min' => 25001, 'max' => 45000, 'taux' => 10],
                    ['min' => 45001, 'max' => 80000, 'taux' => 15],
                    ['min' => 80001, 'max' => 180000, 'taux' => 20],
                    ['min' => 180001, 'max' => 0, 'taux' => 25],
                ],
                // Mêmes lignes que la section « Rémunération » du template
                'parametres_indemnites' => [
                    ['nom' => 'Indemnité de transport', 'type' => 'montant_fixe', 'montant' => 30000, 'imposable' => true],
                    ['nom' => 'Indemnité de logement', 'type' => 'montant_fixe', 'montant' => 0, 'imposable' => true],
                ],
                'parametres_primes' => [
                    ['nom' => "Prime d'ancienneté", 'type' => 'montant_fixe', 'montant' => 0, 'imposable' => true],
                    ['nom' => 'Prime de rendement', 'type' => 'montant_fixe', 'montant' => 0, 'imposable' => true],
                ],
                'deleted_at' => null,
            ]
        );
    }

    /**
     * Crée ou met à jour un salarié de GENIUS GROUPS SAS.
     * Créé sans événements, car le hook "creating" du modèle remplacerait le matricule
     * et appellerait l'API GeniusTools pour le QR code. Le code employé et le secret QR sont donc posés ici.
     * Retrouvé par email (unique, dérivé du matricule) pour rester relançable sans doublon.
     */
    private function creerSalarie(Entreprise $entreprise, array $donnees): Employeur
    {
        $email = strtolower($donnees['matricule']) . '@geniusgroups.ci'; // placeholder : email requis et unique

        $attributs = $donnees + [
            'entreprise_id' => $entreprise->id,
            'statut' => 'actif',
        ];

        return Employeur::withoutEvents(function () use ($email, $attributs, $entreprise) {
            $employeur = Employeur::withTrashed()->where('email', $email)->first();

            if ($employeur) {
                $employeur->forceFill($attributs + ['deleted_at' => null])->save();

                return $employeur;
            }

            return Employeur::forceCreate($attributs + [
                'email' => $email,
                'code_employe' => Employeur::genererCodeEmploye($attributs['nom'], $attributs['prenom'], $entreprise->id),
                'qr_code_secret' => Str::random(32),
                'qr_code_expires_at' => now()->addDays(30),
                'qr_code_active' => true,
            ]);
        });
    }

    private function creerElements(BulletinPaie $bulletin): void
    {
        $elements = [
            // [code, libellé, type, catégorie, base, taux, montant, imposable, ordre]
            // Base 1 = quantité pour les montants forfaitaires, et nombre de personnes pour la CMU
            // Rémunération
            ['SAL_BASE', 'Salaire de base', ElementPaie::TYPE_SALAIRE, ElementPaie::CATEGORIE_SALAIRE_BASE, 250000, null, 250000, true, 10],
            ['IND_TRANS', 'Indemnité de transport', ElementPaie::TYPE_INDEMNITE, ElementPaie::CATEGORIE_INDEMNITE_TRANSPORT, 1, null, 30000, true, 20],
            ['IND_LOG', 'Indemnité de logement', ElementPaie::TYPE_INDEMNITE, ElementPaie::CATEGORIE_INDEMNITE_LOGEMENT, 1, null, 0, true, 21],
            ['PRIME_ANC', "Prime d'ancienneté", ElementPaie::TYPE_PRIME, ElementPaie::CATEGORIE_PRIME_ANCIENNETE, 1, null, 0, true, 30],
            ['PRIME_REND', 'Prime de rendement', ElementPaie::TYPE_PRIME, ElementPaie::CATEGORIE_PRIME_RENDEMENT, 1, null, 0, true, 31],
            ['PRIME_AUT', 'Heures supplémentaires / autres éléments', ElementPaie::TYPE_PRIME, ElementPaie::CATEGORIE_PRIME_AUTRE, null, null, 0, true, 32],

            // Cotisations salariales
            ['CNPS_EMP', 'Retraite (part salarié)', ElementPaie::TYPE_RETENUE_SALARIALE, ElementPaie::CATEGORIE_CNPS_EMPLOYE, 280000, 6.3, 17640, false, 40],
            ['CMU_EMP', 'CMU (part salarié)', ElementPaie::TYPE_RETENUE_SALARIALE, ElementPaie::CATEGORIE_ASSURANCE_MALADIE, 1, null, 500, false, 41],
            ['IGR', 'IGR (ITS)', ElementPaie::TYPE_RETENUE_SALARIALE, ElementPaie::CATEGORIE_IGR, 280000, null, 34800, false, 42],

            // Charges patronales
            ['CNPS_PAT', 'Retraite (part employeur)', ElementPaie::TYPE_CHARGE_PATRONALE, ElementPaie::CATEGORIE_CNPS_EMPLOYEUR, 280000, 7.7, 21560, false, 50],
            ['PF', 'Prestations familiales', ElementPaie::TYPE_CHARGE_PATRONALE, ElementPaie::CATEGORIE_PRESTATIONS_FAMILIALES, 75000, 5, 3750, false, 51],
            ['MAT', 'Assurance maternité', ElementPaie::TYPE_CHARGE_PATRONALE, ElementPaie::CATEGORIE_ASSURANCE_MALADIE, 75000, 0.75, 562.50, false, 52],
            ['AT', 'Accidents du travail / maladies professionnelles', ElementPaie::TYPE_CHARGE_PATRONALE, ElementPaie::CATEGORIE_ACCIDENT_TRAVAIL, 75000, 2, 1500, false, 53],
            ['CMU_PAT', 'CMU (part employeur)', ElementPaie::TYPE_CHARGE_PATRONALE, ElementPaie::CATEGORIE_ASSURANCE_MALADIE, 1, null, 500, false, 54],
        ];

        foreach ($elements as [$code, $libelle, $type, $categorie, $base, $taux, $montant, $imposable, $ordre]) {
            ElementPaie::create([
                'bulletin_paie_id' => $bulletin->id,
                'code' => $code,
                'libelle' => $libelle,
                'type' => $type,
                'categorie' => $categorie,
                'base' => $base,
                'taux' => $taux,
                'montant' => $montant,
                'imposable' => $imposable,
                'ordre' => $ordre,
            ]);
        }
    }
}
