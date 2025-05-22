<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Cette migration est maintenant inutile car les tables visiteurs et visites
        // sont déjà créées avec des UUIDs comme clés primaires dans leurs migrations respectives.
        // Cependant, nous la conservons pour gérer les données existantes si nécessaire.
        
        // Vérifier si nous avons des données existantes à migrer
        if (Schema::hasTable('visiteurs') && DB::table('visiteurs')->count() > 0) {
            // 1. Sauvegarder les données existantes
            $visiteurs = DB::table('visiteurs')->get();
            $visites = Schema::hasTable('visites') ? DB::table('visites')->get() : collect([]);
            
            // 2. Supprimer les tables existantes
            Schema::dropIfExists('visites');
            Schema::dropIfExists('visiteurs');
            
            // 3. Recréer les tables avec la bonne structure (les migrations originales seront exécutées)
            
            // 4. Réinsérer les données avec des UUIDs
            $visiteurMapping = [];
            
            foreach ($visiteurs as $visiteur) {
                $uuid = (string) Str::uuid();
                
                // Insérer le visiteur avec un nouvel UUID comme ID
                DB::table('visiteurs')->insert([
                    'id' => $uuid,
                    'entreprise_id' => $visiteur->entreprise_id ?? null,
                    'nom' => $visiteur->nom,
                    'prenom' => $visiteur->prenom,
                    'telephone' => $visiteur->telephone,
                    'email' => $visiteur->email,
                    'code_visiteur' => $visiteur->code_visiteur,
                    'organisation' => $visiteur->organisation,
                    'fonction' => $visiteur->fonction,
                    'notes' => $visiteur->notes,
                    'photo' => $visiteur->photo,
                    'piece_identite' => $visiteur->piece_identite,
                    'statut' => $visiteur->statut,
                    'created_at' => $visiteur->created_at,
                    'updated_at' => $visiteur->updated_at,
                    'deleted_at' => $visiteur->deleted_at
                ]);
                
                // Stocker le mapping entre l'ancien ID et le nouvel UUID
                $visiteurMapping[$visiteur->id] = $uuid;
            }
            
            // Réinsérer les visites en utilisant les nouveaux UUIDs comme clés étrangères
            foreach ($visites as $visite) {
                $uuid = (string) Str::uuid();
                
                // Insérer la visite avec un nouvel UUID comme ID
                DB::table('visites')->insert([
                    'id' => $uuid,
                    'visiteur_id' => isset($visite->visiteur_id) && isset($visiteurMapping[$visite->visiteur_id]) ? $visiteurMapping[$visite->visiteur_id] : null,
                    'entreprise_id' => $visite->entreprise_id,
                    'site_id' => $visite->site_id,
                    'date_arrivee' => $visite->date_arrivee,
                    'date_depart' => $visite->date_depart,
                    'motif_visite' => $visite->motif_visite,
                    'personne_a_rencontrer' => $visite->personne_a_rencontrer,
                    'departement_a_visiter' => $visite->departement_a_visiter,
                    'commentaires' => $visite->commentaires,
                    'statut' => $visite->statut,
                    'badge_visiteur' => $visite->badge_visiteur,
                    'created_at' => $visite->created_at,
                    'updated_at' => $visite->updated_at,
                    'deleted_at' => $visite->deleted_at
                ]);
            }
        }
    }
    
    public function down(): void
    {
        // Cette méthode est vide car nous ne pouvons pas revenir en arrière après avoir supprimé et recréé les tables.
        // Si nécessaire, utilisez une sauvegarde de la base de données pour restaurer les données.
    }
};