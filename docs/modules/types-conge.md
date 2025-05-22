# Documentation du Module Types de Congé

## Table des matières

1. [Introduction](#introduction)
2. [Structure de données](#structure-de-données)
   - [Modèle TypeConge](#modèle-typeconge)
   - [Attributs](#attributs)
   - [Relations](#relations)
3. [Fonctionnalités](#fonctionnalités)
   - [Création et configuration](#création-et-configuration)
   - [Paramètres avancés](#paramètres-avancés)
   - [Règles d'acquisition](#règles-dacquisition)
   - [Workflow de validation](#workflow-de-validation)
4. [Interface utilisateur](#interface-utilisateur)
   - [Liste des types de congé](#liste-des-types-de-congé)
   - [Création et édition](#création-et-édition)
   - [Détails et statistiques](#détails-et-statistiques)
5. [Bonnes pratiques](#bonnes-pratiques)
6. [Cas d'utilisation](#cas-dutilisation)
7. [Intégration avec d'autres modules](#intégration-avec-dautres-modules)

## Introduction

Le module Types de Congé permet de configurer et gérer les différentes catégories d'absences dans le système GENIUS WORK. Il constitue la base du système de gestion des congés en définissant les règles, droits et workflows pour chaque type d'absence.

**Objectifs du module :**
- Permettre une configuration flexible des types de congés adaptée aux besoins de l'entreprise
- Définir les règles d'acquisition, d'utilisation et de validation pour chaque type
- Fournir une base pour le calcul des droits et le suivi des soldes
- Faciliter la gestion des absences selon leur nature et leurs spécificités

## Structure de données

### Modèle TypeConge

Le modèle `TypeConge` est la pierre angulaire du système de gestion des congés. Il définit les caractéristiques et comportements de chaque type d'absence.

### Attributs

| Attribut | Type | Description | Exemple |
|----------|------|-------------|---------|
| id | UUID | Identifiant unique | `550e8400-e29b-41d4-a716-446655440000` |
| employeur_id | UUID | Identifiant de l'employeur | `550e8400-e29b-41d4-a716-446655440001` |
| code | String | Code unique du type de congé | `CP` |
| libelle | String | Libellé du type de congé | `Congés Payés` |
| description | Text | Description détaillée | `Congés payés annuels selon la législation` |
| couleur | String | Couleur pour l'affichage | `#4CAF50` |
| icone | String | Icône pour l'affichage | `fa-umbrella-beach` |
| duree_max | Decimal | Durée maximale autorisée par période | `30.0` |
| duree_min | Decimal | Durée minimale autorisée par demande | `0.5` |
| delai_prevenance | Integer | Délai de prévenance en jours | `15` |
| justificatif_requis | Boolean | Indique si un justificatif est requis | `false` |
| deductible | Boolean | Indique si le congé est déductible du solde | `true` |
| fractionnable | Boolean | Indique si le congé peut être fractionné | `true` |
| report_autorise | Boolean | Indique si le report est autorisé | `true` |
| workflow_validation | JSON | Configuration du workflow de validation | `{"niveaux": 1, "validateurs": ["manager"]}` |
| restrictions | JSON | Restrictions d'utilisation | `{"max_consecutif": 25, "periode_blacklist": ["12-20", "12-31"]}` |
| regle_acquisition | JSON | Règles d'acquisition des droits | `{"type": "mensuel", "valeur": 2.5}` |
| actif | Boolean | Indique si le type est actif | `true` |
| created_at | DateTime | Date de création | `2025-01-15 10:30:00` |
| updated_at | DateTime | Date de dernière modification | `2025-02-20 14:45:00` |
| deleted_at | DateTime | Date de suppression (soft delete) | `null` |

### Relations

- `employeur()` : Appartient à un employeur
  ```php
  public function employeur()
  {
      return $this->belongsTo(Employeur::class);
  }
  ```

- `conges()` : Possède plusieurs congés
  ```php
  public function conges()
  {
      return $this->hasMany(Conge::class);
  }
  ```

- `soldes()` : Possède plusieurs soldes
  ```php
  public function soldes()
  {
      return $this->hasMany(CongeSolde::class);
  }
  ```

## Fonctionnalités

### Création et configuration

Le module permet de créer et configurer des types de congés avec une grande flexibilité :

1. **Informations de base**
   - Définition du code et du libellé
   - Description détaillée
   - Personnalisation visuelle (couleur, icône)

2. **Paramètres généraux**
   - Activation/désactivation
   - Déductibilité du solde
   - Fractionnabilité
   - Possibilité de report

3. **Contraintes d'utilisation**
   - Durée minimale et maximale
   - Délai de prévenance
   - Exigence de justificatif
   - Périodes d'exclusion

### Paramètres avancés

Le module offre des options avancées pour une gestion fine des types de congés :

1. **Restrictions temporelles**
   - Périodes de blacklist (dates où le congé ne peut être pris)
   - Durée maximale consécutive
   - Nombre maximal de demandes par période

2. **Paramètres de calcul**
   - Prise en compte des jours fériés
   - Prise en compte des week-ends
   - Calcul en jours ouvrés ou calendaires
   - Règles de calcul spécifiques pour les temps partiels

3. **Options de report**
   - Limite de report (en jours ou pourcentage)
   - Date limite de report
   - Règles de péremption

### Règles d'acquisition

Le module permet de définir comment les droits sont acquis pour chaque type de congé :

1. **Modes d'acquisition**
   - Acquisition mensuelle (ex: 2,5 jours par mois)
   - Acquisition annuelle (ex: 30 jours par an)
   - Acquisition basée sur l'ancienneté
   - Acquisition personnalisée

2. **Paramètres d'acquisition**
   - Date d'acquisition
   - Proratisation (temps partiel, arrivée en cours d'année)
   - Plafonnement
   - Règles spéciales (maladie, maternité, etc.)

3. **Initialisation des soldes**
   - Attribution initiale
   - Reprise d'historique
   - Règles de transition

### Workflow de validation

Le module permet de configurer le processus de validation pour chaque type de congé :

1. **Niveaux de validation**
   - Validation simple
   - Validation multi-niveaux
   - Validation conditionnelle

2. **Rôles de validation**
   - Par hiérarchie (manager direct, N+2)
   - Par fonction (RH, direction)
   - Par département

3. **Règles spéciales**
   - Validation automatique sous conditions
   - Escalade en cas de non-réponse
   - Délégation de validation

## Interface utilisateur

### Liste des types de congé

L'interface de liste des types de congé offre une vue d'ensemble et des fonctionnalités de gestion :

1. **Fonctionnalités principales**
   - Affichage tabulaire avec pagination
   - Tri par différentes colonnes
   - Filtrage par statut, catégorie, etc.
   - Recherche textuelle

2. **Actions disponibles**
   - Création d'un nouveau type
   - Édition d'un type existant
   - Activation/désactivation
   - Suppression (avec confirmation)

3. **Informations affichées**
   - Code et libellé
   - Statut (actif/inactif)
   - Indicateurs visuels (couleur, icône)
   - Statistiques d'utilisation

### Création et édition

L'interface de création et d'édition permet une configuration complète :

1. **Organisation**
   - Interface par onglets pour une meilleure organisation
   - Formulaire progressif avec validation
   - Prévisualisation des paramètres

2. **Sections principales**
   - Informations générales
   - Paramètres d'utilisation
   - Règles d'acquisition
   - Workflow de validation
   - Restrictions et conditions

3. **Fonctionnalités avancées**
   - Duplication d'un type existant
   - Import/export de configuration
   - Prévisualisation des impacts

### Détails et statistiques

La vue détaillée d'un type de congé fournit des informations complètes :

1. **Informations détaillées**
   - Tous les paramètres configurés
   - Historique des modifications
   - Documentation spécifique

2. **Statistiques d'utilisation**
   - Nombre de demandes par période
   - Jours consommés
   - Répartition par département/service
   - Évolution temporelle

3. **Actions contextuelles**
   - Édition des paramètres
   - Gestion des soldes associés
   - Export des statistiques

## Bonnes pratiques

### Configuration initiale

1. **Analyse préalable**
   - Recenser tous les types d'absence existants
   - Identifier les règles légales et conventionnelles
   - Documenter les processus de validation

2. **Structure cohérente**
   - Utiliser des codes courts mais explicites
   - Adopter une nomenclature cohérente
   - Choisir des couleurs distinctives
   - Documenter chaque type de congé

3. **Paramétrage progressif**
   - Commencer par les types essentiels
   - Tester chaque configuration
   - Impliquer les RH dans la validation
   - Former les utilisateurs clés

### Maintenance et évolution

1. **Revue périodique**
   - Vérifier la conformité légale
   - Analyser l'utilisation effective
   - Recueillir les retours utilisateurs
   - Optimiser les workflows

2. **Gestion des changements**
   - Planifier les modifications importantes
   - Communiquer les changements aux utilisateurs
   - Prévoir des périodes de transition
   - Documenter les évolutions

3. **Optimisation continue**
   - Simplifier les processus complexes
   - Automatiser les tâches répétitives
   - Améliorer l'expérience utilisateur
   - Enrichir les fonctionnalités analytiques

## Cas d'utilisation

### Types de congés standards

1. **Congés payés légaux**
   ```
   Code: CP
   Libellé: Congés Payés
   Acquisition: 2,5 jours par mois
   Déductible: Oui
   Workflow: Validation par le manager
   ```

2. **RTT (Réduction du Temps de Travail)**
   ```
   Code: RTT
   Libellé: Réduction du Temps de Travail
   Acquisition: Attribution annuelle (forfait)
   Déductible: Oui
   Workflow: Validation simple
   ```

3. **Congé maladie**
   ```
   Code: MAL
   Libellé: Congé Maladie
   Acquisition: Aucune (non limité)
   Déductible: Non
   Justificatif: Obligatoire
   Workflow: Validation RH
   ```

### Types de congés spécifiques

1. **Congé sans solde**
   ```
   Code: CSS
   Libellé: Congé Sans Solde
   Acquisition: Aucune
   Déductible: Non
   Workflow: Validation multi-niveaux (manager + RH)
   ```

2. **Congé maternité**
   ```
   Code: MAT
   Libellé: Congé Maternité
   Acquisition: Légale (selon durée légale)
   Déductible: Non
   Justificatif: Obligatoire
   Workflow: Validation RH
   ```

3. **Congé formation**
   ```
   Code: FOR
   Libellé: Congé Formation
   Acquisition: Selon plan de formation
   Déductible: Variable
   Justificatif: Obligatoire
   Workflow: Validation manager + formation
   ```

## Intégration avec d'autres modules

### Module de congés

- Utilisation des types pour la création des demandes
- Application des règles de validation
- Calcul des soldes selon les règles d'acquisition

### Module de présence

- Synchronisation avec le calendrier de présence
- Affichage des absences avec le code couleur approprié
- Prise en compte dans le calcul du temps de travail

### Module de paie

- Transmission des informations pour le calcul de la paie
- Prise en compte des congés sans solde
- Calcul des indemnités de congés payés

### Module de reporting

- Statistiques d'utilisation par type
- Analyse des tendances
- Tableaux de bord RH
