<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entreprise;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BackupEntrepriseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'entreprise:backup {entreprise_id? : ID de l\'entreprise} {--all : Sauvegarder toutes les entreprises} {--path= : Chemin personnalisé pour sauvegarder les fichiers} {--memory-only : Générer le SQL sans stocker sur disque} {--api : Retourne les données pour utilisation API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer une sauvegarde SQL des données spécifiques à une entreprise';

    /**
     * Tables qui contiennent une relation directe avec entreprise_id
     */
    protected $tablesMappings = [
        'users' => 'entreprise_id',
        'employeurs' => 'entreprise_id',
        'departements' => 'entreprise_id',
        'sites' => 'entreprise_id',
        'abonnements' => 'entreprise_id',
        'facturations' => 'entreprise_id',
        'frais_usages' => 'entreprise_id',
        'notifications' => 'entreprise_id',
        'jours' => 'entreprise_id',
        'type_conges' => 'entreprise_id',
    ];
    
    /**
     * Tables qui utilisent des relations polymorphiques
     */
    protected $polymorphicMappings = [
        'adresses' => ['adressable_type' => 'App\\Models\\Entreprise', 'adressable_id' => 'id'],
    ];
    
    /**
     * Relations indirectes via employeur_id
     */
    protected $employeurRelatedTables = [
        'presences' => 'employeur_id',
        'conges' => 'employeur_id',
        'supplementaires' => 'employeur_id',
    ];
    
    /**
     * Relations indirectes via user_id
     */
    protected $userRelatedTables = [
        'solde_conges' => 'user_id',
    ];
    
    /**
     * Relations indirectes via d'autres tables
     */
    protected $otherRelatedTables = [
        'plage_horaire_bases' => [
            'related_table' => 'sites',
            'related_column' => 'site_id',
            'parent_column' => 'id',
            'parent_filter' => 'entreprise_id'
        ],
    ];

    /**
     * Execute the console command.
     * 
     * @return int|array
     */
    public function handle()
    {
        // Option pour désactiver le stockage du fichier (utilisé pour le téléchargement direct)
        $storeFile = !$this->option('memory-only');
        
        if ($this->option('all')) {
            $this->backupAllEntreprises();
            return 0;
        }

        $entrepriseId = $this->argument('entreprise_id');

        if (!$entrepriseId) {
            $entreprises = Entreprise::where('statut', 'actif')->pluck('nom', 'id')->toArray();
            $entrepriseId = $this->choice(
                'Choisissez l\'entreprise à sauvegarder:',
                $entreprises,
                null,
                null,
                false
            );
        }

        $entreprise = Entreprise::find($entrepriseId);

        if (!$entreprise) {
            $this->error("Entreprise non trouvée avec l'ID: {$entrepriseId}");
            return 1;
        }

        $result = $this->backupEntreprise($entreprise, $storeFile);
        
        // Si appelé depuis l'API, retourner les données du backup
        if ($this->option('api')) {
            // Stocker le résultat dans une variable globale pour le récupérer depuis Artisan::call
            app()->instance('backup.result', $result);
            
            // Log pour débogage
            Log::debug('Backup API: Résultat stocké dans le conteneur', [
                'entreprise_id' => $entreprise->id,
                'success' => $result['success'],
                'filename' => $result['filename'],
                'size' => $result['size'],
                'content_length' => strlen($result['content'])
            ]);
            
            return 0;
        }
        
        return 0;
    }

    /**
     * Sauvegarde les données d'une entreprise spécifique
     * 
     * @param Entreprise $entreprise
     * @param bool $storeFile Si true, stocke le fichier sur le disque. Si false, retourne uniquement le contenu SQL.
     * @return array Informations sur la sauvegarde
     */
    protected function backupEntreprise(Entreprise $entreprise, bool $storeFile = true)
    {
        $this->info("Démarrage de la sauvegarde pour l'entreprise: {$entreprise->nom}");
        
        // Log pour débogage
        Log::info("Démarrage de la sauvegarde", [
            'entreprise_id' => $entreprise->id,
            'entreprise_nom' => $entreprise->nom,
            'storeFile' => $storeFile,
            'memory_only' => $this->option('memory-only'),
            'api' => $this->option('api')
        ]);
        
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = Str::slug($entreprise->nom) . "_{$timestamp}.sql";
        
        // Générer le contenu SQL
        $sqlContent = $this->generateSqlForEntreprise($entreprise);
        
        $contentSize = strlen($sqlContent);
        
        // Log pour débogage
        Log::info("Contenu SQL généré", [
            'entreprise_id' => $entreprise->id,
            'size_bytes' => $contentSize,
            'size_mb' => round($contentSize / 1024 / 1024, 2) . ' MB',
            'filename' => $filename
        ]);
        
        $result = [
            'success' => true,
            'filename' => $filename,
            'content' => $sqlContent,
            'size' => $contentSize,
            'file_path' => null
        ];
        
        // Stocker le fichier sur disque si demandé
        if ($storeFile) {
            $path = $this->option('path') ?: 'backups/entreprises/' . $entreprise->id;
            
            // Créer le répertoire s'il n'existe pas
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path);
            }
            
            // Sauvegarder dans le fichier
            Storage::put($path . '/' . $filename, $sqlContent);
            
            $fullPath = storage_path('app/' . $path . '/' . $filename);
            $result['file_path'] = $fullPath;
            
            $this->info("Sauvegarde terminée: {$fullPath}");
            $this->info("Taille du fichier: " . round(strlen($sqlContent) / 1024 / 1024, 2) . " MB");
        } else {
            $this->info("Sauvegarde générée en mémoire");
            $this->info("Taille: " . round(strlen($sqlContent) / 1024 / 1024, 2) . " MB");
        }
        
        return $result;
    }

    /**
     * Sauvegarde les données de toutes les entreprises actives
     */
    protected function backupAllEntreprises()
    {
        $entreprises = Entreprise::where('statut', 'actif')->get();
        
        $this->info("Démarrage de la sauvegarde pour {$entreprises->count()} entreprises");
        
        $bar = $this->output->createProgressBar($entreprises->count());
        $bar->start();
        
        foreach ($entreprises as $entreprise) {
            $this->backupEntreprise($entreprise);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Sauvegarde de toutes les entreprises terminée");
    }

    /**
     * Génère le contenu SQL pour une entreprise spécifique
     */
    protected function generateSqlForEntreprise(Entreprise $entreprise)
    {
        $sql = "-- Backup pour l'entreprise: {$entreprise->nom} (ID: {$entreprise->id})\n";
        $sql .= "-- Date: " . Carbon::now()->format('Y-m-d H:i:s') . "\n\n";
        
        // Ajouter l'entreprise elle-même
        $sql .= $this->generateTableInsert('entreprises', ['id' => $entreprise->id]);
        
        // Ajouter les données des tables avec relation directe
        foreach ($this->tablesMappings as $table => $foreignKey) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $foreignKey)) {
                $this->info("Traitement de la table {$table} avec la clé {$foreignKey}");
                $sql .= $this->generateTableInsert($table, [$foreignKey => $entreprise->id]);
            } else {
                $this->warn("Table {$table} ou colonne {$foreignKey} non trouvée, ignorée.");
            }
        }
        
        // Ajouter les données des tables avec relations polymorphiques
        foreach ($this->polymorphicMappings as $table => $conditions) {
            if (Schema::hasTable($table) && 
                Schema::hasColumn($table, $conditions['adressable_type']) && 
                Schema::hasColumn($table, $conditions['adressable_id'])) {
                
                $this->info("Traitement de la table polymorphique {$table}");
                $whereConditions = [
                    $conditions['adressable_type'] => 'App\\Models\\Entreprise',
                    $conditions['adressable_id'] => $entreprise->id
                ];
                $sql .= $this->generateTableInsert($table, $whereConditions);
            } else {
                $this->warn("Table polymorphique {$table} ou colonnes requises non trouvées, ignorée.");
            }
        }
        
        // 1. Traiter les relations via employeurs
        $employeurIds = DB::table('employeurs')
            ->where('entreprise_id', $entreprise->id)
            ->pluck('id')
            ->toArray();
            
        if (!empty($employeurIds)) {
            foreach ($this->employeurRelatedTables as $table => $foreignKey) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $foreignKey)) {
                    $this->info("Traitement de la table {$table} avec relation via employeur");
                    $sql .= $this->generateTableInsert($table, [$foreignKey => $employeurIds], true);
                } else {
                    $this->warn("Table {$table} ou colonne {$foreignKey} non trouvée, ignorée.");
                }
            }
        }
        
        // 2. Traiter les relations via utilisateurs
        $userIds = DB::table('users')
            ->where('entreprise_id', $entreprise->id)
            ->pluck('id')
            ->toArray();
            
        if (!empty($userIds)) {
            foreach ($this->userRelatedTables as $table => $foreignKey) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $foreignKey)) {
                    $this->info("Traitement de la table {$table} avec relation via utilisateur");
                    $sql .= $this->generateTableInsert($table, [$foreignKey => $userIds], true);
                } else {
                    $this->warn("Table {$table} ou colonne {$foreignKey} non trouvée, ignorée.");
                }
            }
        }
        
        // 3. Traiter les autres relations indirectes
        foreach ($this->otherRelatedTables as $table => $relation) {
            if (Schema::hasTable($relation['related_table']) && 
                Schema::hasTable($table) && 
                Schema::hasColumn($table, $relation['related_column']) &&
                Schema::hasColumn($relation['related_table'], $relation['parent_column']) &&
                Schema::hasColumn($relation['related_table'], $relation['parent_filter'])) {
                
                $this->info("Traitement de la table {$table} avec relation indirecte via {$relation['related_table']}");
                
                // Récupérer les IDs de la table intermédiaire
                $relatedIds = DB::table($relation['related_table'])
                    ->where($relation['parent_filter'], $entreprise->id)
                    ->pluck($relation['parent_column'])
                    ->toArray();
                    
                if (!empty($relatedIds)) {
                    $sql .= $this->generateTableInsert($table, [$relation['related_column'] => $relatedIds], true);
                }
            } else {
                $this->warn("Table {$table} ou relation indirecte non trouvée, ignorée.");
            }
        }
        
        return $sql;
    }

    /**
     * Génère les instructions INSERT pour une table et une condition
     */
    protected function generateTableInsert($table, $conditions, $isArray = false)
    {
        $sql = "\n-- Table: {$table}\n";
        
        try {
            $query = DB::table($table);
            
            foreach ($conditions as $column => $value) {
                // Vérifier que la colonne existe dans la table
                if (!Schema::hasColumn($table, $column)) {
                    return $sql . "-- Colonne {$column} non trouvée dans la table {$table}\n";
                }
                
                if ($isArray) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
            
            $records = $query->get();
            
            if ($records->isEmpty()) {
                return $sql . "-- Aucune donnée trouvée\n";
            }
            
            $columns = array_keys((array) $records->first());
            
            foreach ($records as $record) {
                $values = [];
                foreach ((array) $record as $value) {
                    if (is_null($value)) {
                        $values[] = 'NULL';
                    } elseif (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        $values[] = "'" . str_replace("'", "\\'", $value) . "'";
                    }
                }
                
                $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
            
            return $sql;
        } catch (\Exception $e) {
            Log::error("Erreur lors de la génération du SQL pour la table {$table}", [
                'error' => $e->getMessage(),
                'table' => $table,
                'conditions' => $conditions
            ]);
            
            return $sql . "-- Erreur: " . $e->getMessage() . "\n";
        }
    }
}
