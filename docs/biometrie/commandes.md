# Commandes Artisan du Module Biométrie

Le module Biométrie fournit des commandes Artisan personnalisées pour faciliter l'administration et l'automatisation des tâches.

## SynchroniserAppareilsBiometriques

Commande principale pour synchroniser les appareils biométriques.

### Signature

```
biometrique:sync 
    {--type=all : Type de synchronisation (all, logs, users, time)}
    {--id= : ID spécifique d'un appareil à synchroniser}
    {--site= : ID d'un site pour synchroniser tous ses appareils}
    {--statut=actif : Statut des appareils à synchroniser (actif, inactif, maintenance, erreur, all)}
```

### Description

Cette commande permet de synchroniser les appareils biométriques avec le système GENIUS WORK. Elle peut synchroniser les logs, les utilisateurs et l'heure des appareils.

### Options

- `--type`: Type de synchronisation à effectuer
  - `all`: Synchronise tout (logs, utilisateurs, heure)
  - `logs`: Synchronise uniquement les logs
  - `users`: Synchronise uniquement les utilisateurs
  - `time`: Synchronise uniquement l'heure
- `--id`: ID spécifique d'un appareil à synchroniser
- `--site`: ID d'un site pour synchroniser tous les appareils de ce site
- `--statut`: Statut des appareils à synchroniser
  - `actif`: Appareils actifs (par défaut)
  - `inactif`: Appareils inactifs
  - `maintenance`: Appareils en maintenance
  - `erreur`: Appareils en erreur
  - `all`: Tous les appareils

### Implémentation

```php
namespace App\Console\Commands;

use App\Models\AppareilBiometrique;
use App\Services\Biometrique\AppareilBiometriqueStatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SynchroniserAppareilsBiometriques extends Command
{
    protected $signature = 'biometrique:sync 
                            {--type=all : Type de synchronisation (all, logs, users, time)}
                            {--id= : ID spécifique d\'un appareil à synchroniser}
                            {--site= : ID d\'un site pour synchroniser tous ses appareils}
                            {--statut=actif : Statut des appareils à synchroniser (actif, inactif, maintenance, erreur, all)}';

    protected $description = 'Synchronise les appareils biométriques (logs, utilisateurs, heure)';

    protected $statsService;

    public function __construct(AppareilBiometriqueStatsService $statsService)
    {
        parent::__construct();
        $this->statsService = $statsService;
    }

    public function handle()
    {
        $type = $this->option('type');
        $id = $this->option('id');
        $siteId = $this->option('site');
        $statut = $this->option('statut');
        
        $this->info("Démarrage de la synchronisation des appareils biométriques");
        $this->info("Type: {$type}");
        
        if ($id) {
            $this->info("Synchronisation de l'appareil ID: {$id}");
            $appareil = AppareilBiometrique::findOrFail($id);
            $this->synchroniserAppareil($appareil, $type);
        } else {
            $query = AppareilBiometrique::query();
            
            if ($siteId) {
                $this->info("Filtrage par site ID: {$siteId}");
                $query->where('site_id', $siteId);
            }
            
            if ($statut !== 'all') {
                $this->info("Filtrage par statut: {$statut}");
                $query->where('statut', $statut);
            }
            
            $appareils = $query->get();
            $this->info("Nombre d'appareils à synchroniser: " . $appareils->count());
            
            $bar = $this->output->createProgressBar($appareils->count());
            $bar->start();
            
            $success = 0;
            $errors = 0;
            
            foreach ($appareils as $appareil) {
                $result = $this->synchroniserAppareil($appareil, $type, false);
                
                if ($result['success']) {
                    $success++;
                } else {
                    $errors++;
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            $this->info("Synchronisation terminée");
            $this->info("Succès: {$success}");
            $this->info("Erreurs: {$errors}");
        }
        
        return Command::SUCCESS;
    }
    
    protected function synchroniserAppareil(AppareilBiometrique $appareil, string $type, bool $verbose = true)
    {
        if ($verbose) {
            $this->info("Synchronisation de l'appareil: {$appareil->nom} ({$appareil->adresse_ip})");
        }
        
        $result = [
            'success' => true,
            'messages' => []
        ];
        
        try {
            if ($type === 'all' || $type === 'logs') {
                if ($verbose) {
                    $this->info("Synchronisation des logs...");
                }
                
                $syncResult = $this->statsService->synchroniserLogs($appareil);
                
                if ($syncResult['success']) {
                    $result['messages'][] = "Logs: {$syncResult['count']} logs synchronisés";
                } else {
                    $result['success'] = false;
                    $result['messages'][] = "Erreur logs: {$syncResult['message']}";
                }
            }
            
            if ($type === 'all' || $type === 'users') {
                if ($verbose) {
                    $this->info("Synchronisation des utilisateurs...");
                }
                
                $syncResult = $this->statsService->synchroniserUtilisateurs($appareil);
                
                if ($syncResult['success']) {
                    $result['messages'][] = "Utilisateurs: {$syncResult['count']} utilisateurs synchronisés";
                } else {
                    $result['success'] = false;
                    $result['messages'][] = "Erreur utilisateurs: {$syncResult['message']}";
                }
            }
            
            if ($type === 'all' || $type === 'time') {
                if ($verbose) {
                    $this->info("Synchronisation de l'heure...");
                }
                
                $syncResult = $this->statsService->synchroniserHeure($appareil);
                
                if ($syncResult['success']) {
                    $result['messages'][] = "Heure: Synchronisée avec succès";
                } else {
                    $result['success'] = false;
                    $result['messages'][] = "Erreur heure: {$syncResult['message']}";
                }
            }
            
            if ($verbose) {
                foreach ($result['messages'] as $message) {
                    $this->info($message);
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            $errorMessage = "Erreur lors de la synchronisation: " . $e->getMessage();
            
            if ($verbose) {
                $this->error($errorMessage);
            }
            
            Log::error($errorMessage, [
                'appareil_id' => $appareil->id,
                'type' => $type,
                'exception' => $e
            ]);
            
            return [
                'success' => false,
                'messages' => [$errorMessage]
            ];
        }
    }
}
```

### Exemples d'utilisation

#### Synchroniser tous les appareils actifs

```bash
php artisan biometrique:sync
```

#### Synchroniser uniquement les logs

```bash
php artisan biometrique:sync --type=logs
```

#### Synchroniser un appareil spécifique

```bash
php artisan biometrique:sync --id=uuid-appareil
```

#### Synchroniser tous les appareils d'un site

```bash
php artisan biometrique:sync --site=5
```

#### Synchroniser tous les appareils, quel que soit leur statut

```bash
php artisan biometrique:sync --statut=all
```

## Planification des commandes

Les commandes sont planifiées dans `App\Console\Kernel` pour s'exécuter automatiquement:

```php
protected function schedule(Schedule $schedule)
{
    // Synchronisation horaire des logs
    $schedule->command('biometrique:sync --type=logs')
             ->hourly();
             
    // Synchronisation quotidienne des utilisateurs
    $schedule->command('biometrique:sync --type=users')
             ->dailyAt('01:00');
             
    // Synchronisation quotidienne de l'heure
    $schedule->command('biometrique:sync --type=time')
             ->dailyAt('00:30');
             
    // Synchronisation complète hebdomadaire
    $schedule->command('biometrique:sync')
             ->weekly()->sundays()->at('02:00');
}
```

## Création d'une nouvelle commande

Pour créer une nouvelle commande pour le module Biométrie:

```bash
php artisan make:command Biometrique/NomDeLaCommande
```

Puis, modifier le fichier généré dans `app/Console/Commands/Biometrique/` pour implémenter la fonctionnalité souhaitée.
