# Module Biométrie - GENIUS WORK

Documentation complète du module de gestion des appareils biométriques pour le système GENIUS WORK Employee Management System.

## Vue d'ensemble

Le module Biométrie permet la gestion complète des appareils biométriques au sein du système GENIUS WORK, offrant des fonctionnalités de synchronisation automatique, de gestion des utilisateurs, de suivi des logs et d'intégration avec le système de pointage.

## Table des matières

1. [Architecture du module](./architecture.md)
2. [Modèles de données](./modeles.md)
3. [Services](./services.md)
   - [Service de synchronisation automatique](./services/synchronisation-automatique.md)
   - [Service de gestion des appareils](./services/appareil-biometrique.md)
   - [Service de statistiques](./services/statistiques.md)
   - [Service de gestion des logs](./services/logs.md)
   - [Service de pointage biométrique](./services/pointage.md)
   - [Service de maintenance](./services/maintenance.md)
   - [Protocoles et adaptateurs](./services/protocoles.md)
4. [Contrôleurs](./controleurs.md)
5. [Vues et composants](./vues.md)
6. [Commandes Artisan](./commandes.md)
7. [Planification des tâches](./planification.md)
8. [Guide d'utilisation](./guide-utilisation.md)
9. [Guide de développement](./guide-developpement.md)

## Fonctionnalités principales

- Gestion complète des appareils biométriques
- Synchronisation automatique des logs, utilisateurs et heure
- Interface d'administration intuitive
- Support de multiples protocoles et fabricants
- Intégration avec le système de pointage
- Planification des tâches de synchronisation
- Journalisation des opérations
- Statistiques et rapports

## Prérequis

- PHP 8.2+
- Laravel 11+
- Base de données MySQL/PostgreSQL
- Accès réseau aux appareils biométriques

## Installation

Le module est intégré au système GENIUS WORK et ne nécessite pas d'installation séparée.

## Licence

Propriétaire - Genius Groups 2025
