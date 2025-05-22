# Module Paie - Documentation de Release

**Version:** 1.0.0  
**Date de release:** 19 Mai 2025  
**Auteur:** Équipe Genius Work

## Présentation

Le module Paie est une extension du système Genius Work qui permet la génération automatique des bulletins de paie conformément à la législation ivoirienne. Il offre une solution complète pour gérer les salaires, les indemnités, les primes, les retenues et les charges patronales.

## Fonctionnalités principales

### Gestion des bulletins de paie
- Génération automatique des bulletins de paie
- Calcul des salaires bruts, nets et des retenues
- Gestion des indemnités et primes
- Calcul automatique des cotisations sociales (CNPS)
- Calcul de l'Impôt Général sur le Revenu (IGR)
- Gestion des charges patronales
- Workflow de validation des bulletins (brouillon, validé, annulé)
- Export des bulletins en PDF

### Configuration flexible
- Configurations de paie personnalisables par entreprise
- Paramétrage des taux de cotisations sociales
- Configuration des barèmes IGR
- Paramétrage des indemnités et primes
- Possibilité de définir plusieurs configurations et d'en choisir une par défaut

## Architecture technique

Le module Paie a été développé selon les principes SOLID et utilise une architecture MVC pour assurer la maintenabilité et la scalabilité.

### Modèles de données
- `BulletinPaie`: Stocke les informations principales des bulletins de paie
- `ElementPaie`: Stocke les différents éléments composant un bulletin (salaire, indemnités, primes, retenues)
- `ConfigurationPaie`: Stocke les configurations de calcul de paie

### Services et Repositories
- `CalculPaieService`: Service responsable des calculs de paie
- `BulletinPaieRepository`: Repository pour la gestion des bulletins de paie

### Interface utilisateur
- Interface utilisateur basée sur Filament
- Tableaux de bord intuitifs pour la gestion des bulletins et configurations
- Formulaires de création et d'édition avec validation des données
- Visualisation détaillée des bulletins de paie

## Installation

### Prérequis
- Genius Work v3.0 ou supérieur
- PHP 8.1 ou supérieur
- Laravel 10.0 ou supérieur
- Base de données MySQL 8.0 ou supérieur

### Étapes d'installation
1. Le module est intégré dans la dernière version de Genius Work
2. Exécuter les migrations de base de données:
   ```bash
   php artisan migrate
   ```
3. Publier les assets (si nécessaire):
   ```bash
   php artisan vendor:publish --tag=genius-paie-assets
   ```

## Configuration initiale

Après l'installation, une configuration de paie par défaut est automatiquement créée avec les paramètres suivants:

- SMIG: 75 000 FCFA
- Plafond CNPS: 225 000 FCFA
- Taux CNPS employé: 6,3%
- Taux CNPS employeur: 7,7%
- Taux prestations familiales: 5,75%
- Taux accident du travail: 2,0%
- Taux assurance maladie: 0,75%
- Abattement IGR: 20%
- Barèmes IGR par défaut

Ces paramètres peuvent être modifiés à tout moment via l'interface d'administration.

## Guide d'utilisation

### Création d'une configuration de paie
1. Accédez à la section "Paie > Configurations" dans le menu de navigation
2. Cliquez sur "Nouvelle configuration"
3. Remplissez les informations générales (nom, description)
4. Configurez les paramètres de base (SMIG, plafond CNPS)
5. Définissez les taux de cotisations
6. Configurez les paramètres IGR
7. Ajoutez les indemnités et primes standard
8. Enregistrez la configuration

### Génération d'un bulletin de paie
1. Accédez à la section "Paie > Bulletins de paie" dans le menu de navigation
2. Cliquez sur "Nouveau bulletin"
3. Sélectionnez l'employé concerné
4. Définissez la période de paie et la date de paiement
5. Le système calculera automatiquement tous les éléments du bulletin
6. Vérifiez les informations et enregistrez le bulletin

### Validation d'un bulletin de paie
1. Accédez au bulletin de paie en question
2. Vérifiez toutes les informations
3. Cliquez sur le bouton "Valider"
4. Une fois validé, le bulletin ne peut plus être modifié
5. Vous pouvez télécharger le PDF du bulletin validé

### Annulation d'un bulletin de paie
1. Accédez au bulletin de paie en question
2. Cliquez sur le bouton "Annuler"
3. Indiquez le motif d'annulation
4. Le bulletin sera marqué comme annulé et ne sera plus pris en compte dans les statistiques

## Formules de calcul

### Salaire brut
```
Salaire brut = Salaire de base + Total indemnités + Total primes
```

### Retenues salariales
```
CNPS employé = min(Salaire brut, Plafond CNPS) * Taux CNPS employé
Base imposable = Salaire brut - Abattement IGR
IGR = Base imposable * Taux IGR (selon barème)
Total retenues = CNPS employé + IGR + Autres retenues
```

### Salaire net
```
Salaire net = Salaire brut - Total retenues
```

### Charges patronales
```
CNPS employeur = min(Salaire brut, Plafond CNPS) * Taux CNPS employeur
Prestations familiales = min(Salaire brut, Plafond CNPS) * Taux prestations familiales
Accident du travail = min(Salaire brut, Plafond CNPS) * Taux accident travail
Assurance maladie = min(Salaire brut, Plafond CNPS) * Taux assurance maladie
Total charges patronales = CNPS employeur + Prestations familiales + Accident du travail + Assurance maladie
```

## Intégration avec les autres modules

Le module Paie s'intègre parfaitement avec les autres modules de Genius Work:

- **Module Employés**: Utilise les informations des employés pour générer les bulletins de paie
- **Module Entreprise**: Utilise les informations de l'entreprise pour les bulletins de paie
- **Module Rapports**: Permet de générer des rapports sur les salaires et charges

## Sécurité et permissions

Le module Paie respecte le système de permissions de Genius Work. Les permissions suivantes ont été ajoutées:

- `view_bulletins`: Voir les bulletins de paie
- `create_bulletins`: Créer des bulletins de paie
- `edit_bulletins`: Modifier des bulletins de paie
- `delete_bulletins`: Supprimer des bulletins de paie
- `validate_bulletins`: Valider des bulletins de paie
- `view_configurations`: Voir les configurations de paie
- `manage_configurations`: Gérer les configurations de paie

## Limitations connues

- Le module ne gère pas encore les avances sur salaire
- Les prêts employés ne sont pas encore intégrés
- La génération en masse de bulletins de paie n'est pas encore disponible

## Roadmap

### Version 1.1.0 (Prévue pour Juin 2025)
- Gestion des avances sur salaire
- Intégration des prêts employés
- Génération en masse de bulletins de paie

### Version 1.2.0 (Prévue pour Août 2025)
- Historique des modifications de salaire
- Simulation de bulletins de paie
- Tableau de bord analytique pour la masse salariale

### Version 2.0.0 (Prévue pour Décembre 2025)
- Intégration avec les systèmes bancaires pour les virements de salaire
- Application mobile pour les employés (consultation des bulletins)
- Signature électronique des bulletins de paie

## Support et contact

Pour toute question ou assistance concernant le module Paie, veuillez contacter:
- Support technique: support@genius.ci
- Documentation complète: https://docs.genius.ci/modules/paie

## Changelog

### v1.0.0 (19 Mai 2025)
- Version initiale du module Paie
- Génération automatique des bulletins de paie
- Calcul des cotisations sociales et de l'IGR
- Configuration flexible des paramètres de paie
- Interface utilisateur Filament
- Export PDF des bulletins de paie
