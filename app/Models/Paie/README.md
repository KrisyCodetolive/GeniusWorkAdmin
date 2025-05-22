# Module Paie - Genius Work

## Présentation
Ce module permet la gestion complète de la paie conformément à la législation ivoirienne. Il offre des fonctionnalités pour générer automatiquement des bulletins de paie, calculer les cotisations sociales, l'IGR et autres éléments de paie.

## Structure du module

### Modèles
- `BulletinPaie.php` : Gère les bulletins de paie
- `ElementPaie.php` : Gère les éléments de paie (salaires, primes, indemnités, retenues)
- `ConfigurationPaie.php` : Gère les configurations de paie (taux, barèmes, etc.)

### Interfaces
- `BulletinPaieRepositoryInterface.php` : Interface pour le repository des bulletins de paie
- `CalculPaieServiceInterface.php` : Interface pour le service de calcul de paie

### Repositories
- `BulletinPaieRepository.php` : Implémentation du repository des bulletins de paie

### Services
- `CalculPaieService.php` : Service pour calculer les éléments de paie

### Controllers
- `BulletinPaieController.php` : Gestion des bulletins de paie
- `ConfigurationPaieController.php` : Gestion des configurations de paie

### Ressources Filament
- `BulletinPaieResource.php` : Interface d'administration pour les bulletins de paie
- `ConfigurationPaieResource.php` : Interface d'administration pour les configurations de paie

## Utilisation

### Configuration
Avant d'utiliser le module, assurez-vous de configurer les paramètres de paie :
1. Accédez à "Paie > Configurations" dans le menu
2. Créez ou modifiez une configuration avec les taux appropriés

### Génération de bulletins
1. Accédez à "Paie > Bulletins de paie"
2. Cliquez sur "Nouveau bulletin"
3. Suivez les étapes pour générer un bulletin de paie

## Documentation complète
Pour une documentation plus détaillée, consultez le fichier `docs/releases/module-paie.md`.
