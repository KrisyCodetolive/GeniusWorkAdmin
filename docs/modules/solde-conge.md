# Documentation du Module Solde de Congés

## Table des matières

1. [Introduction](#introduction)
2. [Structure de données](#structure-de-données)
   - [Modèle SoldeConge](#modèle-soldeconge)
   - [Attributs](#attributs)
   - [Relations](#relations)
   - [Scopes](#scopes)
3. [Fonctionnalités](#fonctionnalités)
   - [Gestion des soldes](#gestion-des-soldes)
   - [Calcul et suivi](#calcul-et-suivi)
   - [Vérifications et contrôles](#vérifications-et-contrôles)
4. [Intégration avec d'autres modules](#intégration-avec-dautres-modules)
5. [Cas d'utilisation](#cas-dutilisation)
6. [Bonnes pratiques](#bonnes-pratiques)
7. [Évolutions futures](#évolutions-futures)

## Introduction

Le module Solde de Congés est un composant critique du système de gestion des congés de GENIUS WORK. Il assure le suivi précis des droits à congés de chaque employé, permettant une gestion transparente et équitable des absences au sein de l'entreprise.

**Objectifs du module :**
- Maintenir un registre précis des soldes de congés par employé et par type
- Automatiser les calculs d'acquisition et de consommation
- Assurer la traçabilité des modifications de solde
- Fournir une base fiable pour les demandes de congés
- Faciliter le reporting et l'analyse des tendances

## Structure de données

### Modèle SoldeConge

Le modèle `SoldeConge` représente le compteur de jours disponibles pour un employé et un type de congé spécifique.

### Attributs

| Attribut | Type | Description | Exemple |
|----------|------|-------------|---------|
| id | UUID | Identifiant unique | `550e8400-e29b-41d4-a716-446655440000` |
| user_id | UUID | Identifiant de l'employé | `550e8400-e29b-41d4-a716-446655440001` |
| type_conge_id | UUID | Identifiant du type de congé | `550e8400-e29b-41d4-a716-446655440002` |
| annee | Integer | Année de référence | `2025` |
| solde_initial | Decimal | Solde de départ | `25.0` |
| solde_acquis | Decimal | Jours acquis en cours d'année | `15.0` |
| solde_pris | Decimal | Jours consommés | `12.5` |
| solde_restant | Decimal | Solde disponible | `27.5` |
| date_derniere_maj | DateTime | Date de dernière mise à jour | `2025-03-01 10:30:00` |
| commentaire | Text | Historique des modifications | `Acquisition mensuelle: +2.5j\nCongé du 15/04: -5j` |
| meta_donnees | JSON | Métadonnées supplémentaires | `{"report_precedent": 5, "limite_report": 10}` |
| created_at | DateTime | Date de création | `2025-01-01 00:00:00` |
| updated_at | DateTime | Date de dernière modification | `2025-03-01 10:30:00` |
| deleted_at | DateTime | Date de suppression (soft delete) | `null` |

### Relations

- `user()` : Appartient à un utilisateur
  ```php
  public function user()
  {
      return $this->belongsTo(User::class);
  }
  ```

- `typeConge()` : Appartient à un type de congé
  ```php
  public function typeConge()
  {
      return $this->belongsTo(TypeConge::class);
  }
  ```

### Scopes

Le modèle définit plusieurs scopes pour faciliter les requêtes courantes :

- `scopeParAnnee($query, $annee)` : Filtre par année
  ```php
  public function scopeParAnnee($query, $annee)
  {
      return $query->where('annee', $annee);
  }
  ```

- `scopeParTypeConge($query, $typeCongeId)` : Filtre par type de congé
  ```php
  public function scopeParTypeConge($query, $typeCongeId)
  {
      return $query->where('type_conge_id', $typeCongeId);
  }
  ```

- `scopeAvecSoldePositif($query)` : Filtre les soldes positifs
  ```php
  public function scopeAvecSoldePositif($query)
  {
      return $query->where('solde_restant', '>', 0);
  }
  ```

## Fonctionnalités

### Gestion des soldes

Le modèle `SoldeConge` offre plusieurs méthodes pour gérer les soldes :

1. **Ajout de solde**
   ```php
   public function ajouterSolde($jours, $commentaire = null)
   {
       $this->solde_acquis += $jours;
       $this->solde_restant += $jours;
       $this->date_derniere_maj = now();
       $this->commentaire = $commentaire ? $this->commentaire . "\n" . $commentaire : $this->commentaire;
       $this->save();

       return $this;
   }
   ```
   - Incrémente le solde acquis et le solde restant
   - Met à jour la date de dernière modification
   - Ajoute un commentaire explicatif
   - Utilisé pour l'acquisition périodique ou les ajustements manuels

2. **Déduction de solde**
   ```php
   public function deduireSolde($jours, $commentaire = null)
   {
       if ($this->solde_restant < $jours) {
           throw new \Exception("Solde insuffisant pour ce type de congé");
       }

       $this->solde_pris += $jours;
       $this->solde_restant -= $jours;
       $this->date_derniere_maj = now();
       $this->commentaire = $commentaire ? $this->commentaire . "\n" . $commentaire : $this->commentaire;
       $this->save();

       return $this;
   }
   ```
   - Vérifie la disponibilité du solde
   - Incrémente le solde pris et décrémente le solde restant
   - Met à jour la date de dernière modification
   - Ajoute un commentaire explicatif
   - Utilisé lors de la validation des demandes de congés

3. **Réinitialisation de solde**
   ```php
   public function reinitialiserSolde($nouveauSolde, $commentaire = null)
   {
       $this->solde_initial = $nouveauSolde;
       $this->solde_acquis = $nouveauSolde;
       $this->solde_pris = 0;
       $this->solde_restant = $nouveauSolde;
       $this->date_derniere_maj = now();
       $this->commentaire = $commentaire ? $commentaire . "\n" . $this->commentaire : $this->commentaire;
       $this->save();

       return $this;
   }
   ```
   - Réinitialise tous les compteurs
   - Définit un nouveau solde initial
   - Met à jour la date de dernière modification
   - Ajoute un commentaire explicatif
   - Utilisé en début d'année ou lors de changements importants

### Calcul et suivi

Le modèle offre plusieurs méthodes pour calculer et suivre les soldes :

1. **Obtention du solde disponible**
   ```php
   public function getSoldeDisponible()
   {
       return $this->solde_restant;
   }
   ```
   - Retourne le solde actuellement disponible

2. **Obtention du solde pris**
   ```php
   public function getSoldePris()
   {
       return $this->solde_pris;
   }
   ```
   - Retourne le nombre de jours déjà consommés

3. **Calcul du solde total**
   ```php
   public function getSoldeTotal()
   {
       return $this->solde_initial + $this->solde_acquis;
   }
   ```
   - Calcule le total des droits (initial + acquis)

### Vérifications et contrôles

Le modèle intègre des mécanismes de vérification :

1. **Vérification de disponibilité**
   ```php
   public function verifierDisponibilite($jours)
   {
       return $this->solde_restant >= $jours;
   }
   ```
   - Vérifie si le solde est suffisant pour une demande
   - Retourne un booléen

## Intégration avec d'autres modules

### Module de Demande de Congés

- Vérification de la disponibilité lors de la création d'une demande
- Déduction automatique du solde lors de la validation
- Réattribution en cas d'annulation
- Affichage des soldes disponibles dans l'interface

### Module Types de Congé

- Configuration des règles d'acquisition par type
- Paramètres de déductibilité
- Règles de report et de plafonnement

### Module Rapports de Congés

- Analyse des soldes par employé, département, type
- Identification des soldes élevés ou faibles
- Prévisions et tendances

### Module de Paie

- Transmission des informations pour le calcul des indemnités
- Synchronisation avec les compteurs de paie
- Régularisations en fin de période

## Cas d'utilisation

### Acquisition mensuelle de congés payés

1. Le 1er de chaque mois, un job planifié s'exécute
2. Pour chaque employé actif :
   - Le système récupère le solde de congés payés de l'année en cours
   - Il calcule l'acquisition mensuelle (généralement 2,5 jours)
   - Il appelle la méthode `ajouterSolde()` avec le commentaire approprié
3. Les employés peuvent consulter leur nouveau solde

### Validation d'une demande de congés

1. Un employé soumet une demande de 5 jours de congés payés
2. Le manager approuve la demande
3. Le système :
   - Récupère le solde correspondant
   - Vérifie la disponibilité avec `verifierDisponibilite()`
   - Déduit le solde avec `deduireSolde()`
   - Enregistre la demande comme validée
4. L'employé est notifié et peut voir son solde mis à jour

### Réinitialisation annuelle

1. Au début de la nouvelle année fiscale
2. Pour chaque employé et type de congé :
   - Le système calcule le report selon les règles configurées
   - Il crée un nouveau solde pour la nouvelle année
   - Il initialise le solde avec la valeur du report
   - Il ajoute un commentaire explicatif
3. Les anciens soldes sont archivés mais restent consultables

## Bonnes pratiques

### Gestion des soldes

1. **Traçabilité**
   - Toujours documenter les modifications avec des commentaires clairs
   - Conserver l'historique complet des transactions
   - Utiliser les métadonnées pour les informations supplémentaires

2. **Cohérence**
   - Vérifier régulièrement la cohérence des soldes
   - Mettre en place des contrôles automatiques
   - Réconcilier les données en cas d'écart

3. **Transparence**
   - Rendre les soldes facilement accessibles aux employés
   - Expliquer clairement les règles d'acquisition et de consommation
   - Notifier les changements importants

### Développement et maintenance

1. **Extensibilité**
   - Concevoir le modèle pour accommoder différentes règles
   - Prévoir des champs pour les besoins futurs
   - Utiliser les métadonnées pour la flexibilité

2. **Performance**
   - Indexer les champs fréquemment utilisés dans les requêtes
   - Optimiser les requêtes sur de grands volumes
   - Mettre en cache les calculs complexes

3. **Sécurité**
   - Restreindre les accès en modification
   - Journaliser toutes les opérations sensibles
   - Valider rigoureusement les entrées

## Évolutions futures

### Améliorations potentielles

1. **Fonctionnalités avancées**
   - Prévisions d'acquisition future
   - Simulation de scénarios
   - Alertes automatiques sur soldes élevés
   - Recommandations personnalisées

2. **Intégrations supplémentaires**
   - Synchronisation avec des systèmes externes
   - API dédiée pour les applications tierces
   - Webhooks pour les événements importants

3. **Améliorations techniques**
   - Optimisation pour de très grands volumes
   - Historique complet avec versioning
   - Audit trail avancé
