# Documentation du Module de Gestion des Congés

## Table des matières

1. [Introduction](#introduction)
2. [Modèles de données](#modèles-de-données)
   - [Congé](#congé)
   - [Type de Congé](#type-de-congé)
   - [Solde de Congé](#solde-de-congé)
   - [Ajustement de Congé](#ajustement-de-congé)
3. [Fonctionnalités](#fonctionnalités)
   - [Demande de congé](#demande-de-congé)
   - [Validation des congés](#validation-des-congés)
   - [Gestion des soldes](#gestion-des-soldes)
   - [Rapports et statistiques](#rapports-et-statistiques)
4. [API et Endpoints](#api-et-endpoints)
5. [Intégrations](#intégrations)
6. [Bonnes pratiques](#bonnes-pratiques)
7. [Troubleshooting](#troubleshooting)

## Introduction

Le module de gestion des congés de GENIUS WORK permet aux entreprises de gérer efficacement les absences de leurs employés. Il offre des fonctionnalités complètes pour la demande, la validation, le suivi et l'analyse des congés.

**Fonctionnalités principales :**
- Demande et validation de congés avec workflow configurable
- Gestion des types de congés personnalisables
- Suivi des soldes de congés avec historique des ajustements
- Calendrier des absences avec vue d'équipe
- Rapports et statistiques avancés
- Notifications automatiques
- Intégration avec les autres modules (présence, paie, etc.)

## Modèles de données

### Congé

Le modèle `Conge` représente une demande de congé d'un employé.

#### Attributs

| Attribut | Type | Description |
|----------|------|-------------|
| id | UUID | Identifiant unique du congé |
| user_id | UUID | Identifiant de l'employé |
| type_conge_id | UUID | Identifiant du type de congé |
| date_debut | Date | Date de début du congé |
| date_fin | Date | Date de fin du congé |
| heure_debut | Time | Heure de début (optionnel) |
| heure_fin | Time | Heure de fin (optionnel) |
| duree | Decimal | Durée en jours |
| motif | Text | Motif du congé |
| commentaire | Text | Commentaires additionnels |
| statut | Enum | Statut (en_attente, approuve, refuse, annule) |
| validateur_id | UUID | Identifiant du validateur |
| date_validation | DateTime | Date et heure de validation |
| documents | JSON | Liste des documents justificatifs |
| metadata | JSON | Métadonnées additionnelles |
| created_at | DateTime | Date de création |
| updated_at | DateTime | Date de dernière modification |
| deleted_at | DateTime | Date de suppression (soft delete) |

#### Relations

- `user()` : Appartient à un utilisateur (employé)
- `typeConge()` : Appartient à un type de congé
- `validateur()` : Appartient à un validateur (utilisateur)
- `ajustements()` : Possède plusieurs ajustements
- `commentaires()` : Possède plusieurs commentaires

#### Méthodes

- `calculerDuree()` : Calcule la durée du congé en jours
- `verifierChevauchement()` : Vérifie si le congé chevauche d'autres congés
- `verifierSolde()` : Vérifie si l'employé a un solde suffisant
- `approuver($validateur_id, $commentaire = null)` : Approuve la demande
- `refuser($validateur_id, $commentaire = null)` : Refuse la demande
- `annuler($commentaire = null)` : Annule la demande

### Type de Congé

Le modèle `TypeConge` représente un type de congé configurable.

#### Attributs

| Attribut | Type | Description |
|----------|------|-------------|
| id | UUID | Identifiant unique du type de congé |
| employeur_id | UUID | Identifiant de l'employeur |
| code | String | Code unique du type de congé |
| libelle | String | Libellé du type de congé |
| description | Text | Description détaillée |
| couleur | String | Couleur pour l'affichage |
| icone | String | Icône pour l'affichage |
| duree_max | Decimal | Durée maximale autorisée par période |
| duree_min | Decimal | Durée minimale autorisée par demande |
| delai_prevenance | Integer | Délai de prévenance en jours |
| justificatif_requis | Boolean | Indique si un justificatif est requis |
| deductible | Boolean | Indique si le congé est déductible du solde |
| fractionnable | Boolean | Indique si le congé peut être fractionné |
| report_autorise | Boolean | Indique si le report est autorisé |
| workflow_validation | JSON | Configuration du workflow de validation |
| restrictions | JSON | Restrictions d'utilisation |
| created_at | DateTime | Date de création |
| updated_at | DateTime | Date de dernière modification |
| deleted_at | DateTime | Date de suppression (soft delete) |

#### Relations

- `employeur()` : Appartient à un employeur
- `conges()` : Possède plusieurs congés
- `soldes()` : Possède plusieurs soldes

#### Méthodes

- `estDisponible($user_id)` : Vérifie si le type est disponible pour un utilisateur
- `getSoldeUtilisateur($user_id)` : Récupère le solde d'un utilisateur pour ce type
- `getStatistiques($periode = 'annee')` : Récupère les statistiques d'utilisation

### Solde de Congé

Le modèle `CongeSolde` représente le solde de congés d'un employé pour un type de congé spécifique.

#### Attributs

| Attribut | Type | Description |
|----------|------|-------------|
| id | UUID | Identifiant unique du solde |
| user_id | UUID | Identifiant de l'employé |
| type_conge_id | UUID | Identifiant du type de congé |
| periode_debut | Date | Date de début de la période |
| periode_fin | Date | Date de fin de la période |
| solde_initial | Decimal | Solde initial |
| solde | Decimal | Solde actuel |
| acquis | Decimal | Jours acquis |
| pris | Decimal | Jours pris |
| ajuste | Decimal | Jours ajustés |
| metadata | JSON | Métadonnées additionnelles |
| created_at | DateTime | Date de création |
| updated_at | DateTime | Date de dernière modification |
| deleted_at | DateTime | Date de suppression (soft delete) |

#### Relations

- `user()` : Appartient à un utilisateur (employé)
- `typeConge()` : Appartient à un type de congé
- `ajustements()` : Possède plusieurs ajustements

#### Méthodes

- `ajuster($valeur, $motif, $operateur_id)` : Ajuste le solde
- `calculerSoldeRestant()` : Calcule le solde restant
- `verifierDisponibilite($duree)` : Vérifie si le solde est suffisant
- `getHistorique()` : Récupère l'historique des ajustements

### Ajustement de Congé

Le modèle `CongeAjustement` représente un ajustement de solde de congés.

#### Attributs

| Attribut | Type | Description |
|----------|------|-------------|
| id | UUID | Identifiant unique de l'ajustement |
| conge_solde_id | UUID | Identifiant du solde |
| operateur_id | UUID | Identifiant de l'opérateur |
| valeur | Decimal | Valeur de l'ajustement |
| type | Enum | Type d'ajustement (ajout, deduction, correction) |
| motif | String | Motif de l'ajustement |
| commentaire | Text | Commentaires additionnels |
| date_effet | Date | Date d'effet de l'ajustement |
| metadata | JSON | Métadonnées additionnelles |
| created_at | DateTime | Date de création |
| updated_at | DateTime | Date de dernière modification |

#### Relations

- `congeSolde()` : Appartient à un solde de congé
- `operateur()` : Appartient à un opérateur (utilisateur)

## Fonctionnalités

### Demande de congé

Le processus de demande de congé permet aux employés de soumettre leurs demandes d'absence.

**Étapes du processus :**
1. L'employé sélectionne le type de congé
2. Il spécifie les dates et durées
3. Il fournit un motif et des justificatifs si nécessaire
4. La demande est soumise pour validation
5. Des notifications sont envoyées aux validateurs

**Vérifications automatiques :**
- Disponibilité du solde
- Chevauchement avec d'autres congés
- Respect des délais de prévenance
- Conformité avec les règles du type de congé

### Validation des congés

Le processus de validation permet aux managers de traiter les demandes de congés.

**Fonctionnalités :**
- Tableau de bord des demandes en attente
- Validation individuelle ou en masse
- Ajout de commentaires
- Historique des validations
- Notifications automatiques

**Workflow configurable :**
- Validation simple ou multi-niveaux
- Règles de validation conditionnelles
- Validation automatique sous conditions
- Délégation de validation

### Gestion des soldes

La gestion des soldes permet de suivre et d'ajuster les droits à congés des employés.

**Fonctionnalités :**
- Attribution automatique ou manuelle de soldes
- Ajustements individuels ou en masse
- Historique des modifications
- Calcul automatique des soldes
- Règles de report et d'expiration

**Types d'ajustements :**
- Acquisition périodique
- Ajustements manuels
- Déductions automatiques
- Reports de solde

### Rapports et statistiques

Le module de rapports fournit des analyses détaillées sur les congés.

**Types de rapports :**
- Synthèse globale
- Analyse par département
- Analyse par type de congé
- Analyse par employé
- Analyse chronologique

**Fonctionnalités :**
- Filtrage multi-critères
- Visualisations graphiques
- Export en différents formats
- Tableaux de données détaillés
- Analyses tendancielles

## API et Endpoints

Le module expose plusieurs endpoints API pour l'intégration avec d'autres systèmes.

### API REST

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/conges` | GET | Liste des congés |
| `/api/conges/{id}` | GET | Détails d'un congé |
| `/api/conges` | POST | Création d'un congé |
| `/api/conges/{id}` | PUT | Mise à jour d'un congé |
| `/api/conges/{id}` | DELETE | Suppression d'un congé |
| `/api/conges/{id}/approuver` | POST | Approbation d'un congé |
| `/api/conges/{id}/refuser` | POST | Refus d'un congé |
| `/api/conges/{id}/annuler` | POST | Annulation d'un congé |
| `/api/types-conge` | GET | Liste des types de congé |
| `/api/soldes/{user_id}` | GET | Soldes d'un utilisateur |
| `/api/rapports/conges` | POST | Génération de rapports |

### Webhooks

Le module peut envoyer des notifications via webhooks pour les événements suivants :
- Création d'une demande de congé
- Validation d'une demande
- Refus d'une demande
- Annulation d'une demande
- Modification d'un solde

## Intégrations

Le module s'intègre avec d'autres composants du système GENIUS WORK.

### Module de présence

- Synchronisation des absences avec le calendrier de présence
- Vérification des conflits entre congés et pointages
- Calcul automatique des jours ouvrés

### Module de paie

- Transmission des congés pour le calcul de la paie
- Synchronisation des soldes de congés
- Prise en compte des congés sans solde

### Calendrier

- Affichage des congés dans le calendrier d'équipe
- Synchronisation avec les calendriers externes (iCal, Google Calendar)
- Vue consolidée des absences par équipe ou département

## Bonnes pratiques

### Configuration des types de congé

- Créer des types distincts pour chaque catégorie de congé
- Définir clairement les règles d'acquisition et d'utilisation
- Configurer des couleurs distinctes pour faciliter la visualisation
- Documenter les spécificités de chaque type

### Gestion des soldes

- Effectuer une vérification périodique des soldes
- Documenter tous les ajustements manuels
- Mettre en place des alertes pour les soldes faibles ou élevés
- Planifier les reports de solde en fin de période

### Validation des congés

- Définir clairement la chaîne de validation
- Former les validateurs aux règles spécifiques
- Mettre en place des suppléants pour éviter les blocages
- Vérifier régulièrement les demandes en attente

## Troubleshooting

### Problèmes courants

| Problème | Cause possible | Solution |
|----------|----------------|----------|
| Solde incorrect | Ajustement manquant | Vérifier l'historique des ajustements et ajouter les corrections nécessaires |
| Demande bloquée | Validateur absent | Vérifier la chaîne de validation et réaffecter si nécessaire |
| Chevauchement non détecté | Configuration incorrecte | Vérifier les paramètres de détection de chevauchement |
| Erreur de calcul de durée | Jours fériés non configurés | Mettre à jour le calendrier des jours fériés |
| Notification non reçue | Configuration email incorrecte | Vérifier les paramètres de notification et les adresses email |
