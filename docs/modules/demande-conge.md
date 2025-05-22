# Documentation du Module Demande de Congés

## Table des matières

1. [Introduction](#introduction)
2. [Structure de données](#structure-de-données)
   - [Modèle Conge](#modèle-conge)
   - [Attributs](#attributs)
   - [Relations](#relations)
   - [États et transitions](#états-et-transitions)
3. [Fonctionnalités](#fonctionnalités)
   - [Création de demandes](#création-de-demandes)
   - [Processus de validation](#processus-de-validation)
   - [Gestion des soldes](#gestion-des-soldes)
   - [Notifications](#notifications)
4. [Interface utilisateur](#interface-utilisateur)
   - [Formulaire de demande](#formulaire-de-demande)
   - [Calendrier des congés](#calendrier-des-congés)
   - [Liste des demandes](#liste-des-demandes)
   - [Détails d'une demande](#détails-dune-demande)
5. [Intégration avec d'autres modules](#intégration-avec-dautres-modules)
6. [Bonnes pratiques](#bonnes-pratiques)
7. [Cas d'utilisation](#cas-dutilisation)

## Introduction

Le module Demande de Congés est le cœur du système de gestion des absences de GENIUS WORK. Il permet aux employés de soumettre leurs demandes de congés, aux managers de les valider, et au service RH de suivre l'ensemble des absences au sein de l'entreprise.

**Objectifs du module :**
- Simplifier le processus de demande de congés
- Automatiser le workflow de validation
- Assurer le suivi précis des soldes
- Offrir une visibilité complète sur les absences
- Faciliter la planification des ressources

## Structure de données

### Modèle Conge

Le modèle `Conge` représente une demande de congé dans le système.

### Attributs

| Attribut | Type | Description | Exemple |
|----------|------|-------------|---------|
| id | UUID | Identifiant unique | `550e8400-e29b-41d4-a716-446655440000` |
| employeur_id | UUID | Identifiant de l'employeur | `550e8400-e29b-41d4-a716-446655440001` |
| user_id | UUID | Identifiant de l'employé | `550e8400-e29b-41d4-a716-446655440002` |
| type_conge_id | UUID | Identifiant du type de congé | `550e8400-e29b-41d4-a716-446655440003` |
| date_debut | DateTime | Date et heure de début | `2025-04-15 08:00:00` |
| date_fin | DateTime | Date et heure de fin | `2025-04-22 18:00:00` |
| duree | Decimal | Durée en jours | `5.5` |
| motif | Text | Motif de la demande | `Vacances familiales` |
| commentaire | Text | Commentaires supplémentaires | `Voyage prévu à l'étranger` |
| statut | String | Statut de la demande | `en_attente` |
| justificatif_url | String | URL du justificatif | `uploads/conges/justificatif_123.pdf` |
| metadata | JSON | Métadonnées supplémentaires | `{"priorite": "normale", "rappel": true}` |
| created_at | DateTime | Date de création | `2025-03-01 10:30:00` |
| updated_at | DateTime | Date de dernière modification | `2025-03-02 14:45:00` |
| deleted_at | DateTime | Date de suppression (soft delete) | `null` |

### Relations

- `employeur()` : Appartient à un employeur
  ```php
  public function employeur()
  {
      return $this->belongsTo(Employeur::class);
  }
  ```

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

- `validations()` : Possède plusieurs validations
  ```php
  public function validations()
  {
      return $this->hasMany(CongeValidation::class);
  }
  ```

- `solde()` : Associé à un solde
  ```php
  public function solde()
  {
      return $this->belongsTo(CongeSolde::class);
  }
  ```

### États et transitions

Le modèle `Conge` suit un workflow d'états avec les transitions suivantes :

1. **Brouillon**
   - État initial lors de la création
   - Peut être modifié librement
   - Transitions possibles : Soumission

2. **En attente**
   - Demande soumise, en attente de validation
   - Modifications limitées
   - Transitions possibles : Validation, Refus, Annulation

3. **Validé**
   - Demande approuvée
   - Modifications interdites
   - Transitions possibles : Annulation (avec restrictions)

4. **Refusé**
   - Demande rejetée
   - Modifications interdites
   - Transitions possibles : Soumission (nouvelle demande)

5. **Annulé**
   - Demande annulée par l'employé ou le manager
   - Modifications interdites
   - Transitions possibles : Soumission (nouvelle demande)

## Fonctionnalités

### Création de demandes

Le module permet aux employés de créer des demandes de congés avec les fonctionnalités suivantes :

1. **Sélection du type de congé**
   - Liste des types disponibles
   - Informations sur les soldes
   - Règles spécifiques au type

2. **Définition de la période**
   - Sélection des dates de début et fin
   - Calcul automatique de la durée
   - Prise en compte des jours non travaillés
   - Vérification des chevauchements

3. **Informations complémentaires**
   - Motif de la demande
   - Commentaires
   - Upload de justificatifs
   - Paramètres spécifiques

4. **Vérifications automatiques**
   - Contrôle du solde disponible
   - Respect du délai de prévenance
   - Vérification des règles spécifiques
   - Détection des conflits

### Processus de validation

Le module gère le workflow de validation des demandes :

1. **Niveaux de validation**
   - Configuration flexible (1 à n niveaux)
   - Validation séquentielle ou parallèle
   - Règles de validation conditionnelles

2. **Actions des validateurs**
   - Approbation simple
   - Refus avec motif
   - Demande d'informations complémentaires
   - Transfert à un autre validateur

3. **Suivi du processus**
   - Historique des validations
   - Notifications automatiques
   - Rappels pour les validations en attente
   - Escalade en cas de non-réponse

4. **Règles spéciales**
   - Validation automatique après délai
   - Validation par défaut pour certains types
   - Règles de délégation

### Gestion des soldes

Le module assure le suivi précis des soldes de congés :

1. **Calcul des soldes**
   - Acquisition selon les règles du type
   - Déduction lors de la validation
   - Réattribution en cas d'annulation
   - Gestion des reports

2. **Vérification des droits**
   - Contrôle à la soumission
   - Alerte en cas de solde insuffisant
   - Possibilité de découvert (paramétrable)
   - Anticipation des acquisitions futures

3. **Historique des transactions**
   - Suivi de toutes les opérations
   - Justification des modifications
   - Audit complet des changements
   - Réconciliation périodique

### Notifications

Le module intègre un système complet de notifications :

1. **Types de notifications**
   - Création d'une demande
   - Demande à valider
   - Validation/refus
   - Rappels de validation
   - Annulation
   - Soldes faibles

2. **Canaux de communication**
   - Notifications in-app
   - Emails
   - SMS (optionnel)
   - Intégration calendrier

3. **Personnalisation**
   - Configuration par utilisateur
   - Fréquence des rappels
   - Regroupement des notifications
   - Templates personnalisables

## Interface utilisateur

### Formulaire de demande

L'interface de création de demande offre une expérience utilisateur optimale :

1. **Design et ergonomie**
   - Interface intuitive en étapes
   - Indications contextuelles
   - Validation en temps réel
   - Responsive design

2. **Fonctionnalités avancées**
   - Suggestions intelligentes
   - Prévisualisation du calendrier
   - Calcul dynamique de la durée
   - Estimation de l'impact sur le solde

3. **Accessibilité**
   - Compatibilité mobile
   - Respect des normes d'accessibilité
   - Raccourcis clavier
   - Mode hors ligne (PWA)

### Calendrier des congés

Le calendrier offre une vue d'ensemble des absences :

1. **Vues disponibles**
   - Vue mensuelle
   - Vue hebdomadaire
   - Vue par équipe/département
   - Vue personnelle

2. **Fonctionnalités**
   - Filtrage multi-critères
   - Création rapide depuis le calendrier
   - Affichage des jours fériés
   - Indication des périodes chargées

3. **Interactions**
   - Drag & drop pour modification
   - Infobulles détaillées
   - Zoom sur périodes
   - Export et partage

### Liste des demandes

L'interface de liste des demandes permet un suivi efficace :

1. **Organisation**
   - Tri multi-colonnes
   - Filtrage avancé
   - Recherche textuelle
   - Pagination optimisée

2. **Informations affichées**
   - Informations essentielles
   - Indicateurs de statut
   - Actions contextuelles
   - Alertes et notifications

3. **Actions en masse**
   - Sélection multiple
   - Validation groupée
   - Export de sélection
   - Actions par lot

### Détails d'une demande

La vue détaillée d'une demande présente toutes les informations pertinentes :

1. **Informations principales**
   - Détails de la demande
   - Statut et historique
   - Documents associés
   - Commentaires et échanges

2. **Actions disponibles**
   - Actions selon le rôle et le statut
   - Validation/refus
   - Modification (si autorisée)
   - Annulation

3. **Informations contextuelles**
   - Autres absences de l'équipe
   - Impact sur les projets
   - Solde avant/après
   - Recommandations

## Intégration avec d'autres modules

### Module Types de Congé

- Utilisation des types définis
- Application des règles spécifiques
- Calcul des soldes selon les règles d'acquisition

### Module Rapports de Congés

- Alimentation des données pour les rapports
- Statistiques d'utilisation
- Analyse des tendances

### Module de Présence

- Synchronisation avec le calendrier de présence
- Impact sur le calcul du temps de travail
- Affichage cohérent des absences

### Module de Paie

- Transmission des informations pour le calcul de la paie
- Prise en compte des congés sans solde
- Calcul des indemnités de congés payés

## Bonnes pratiques

### Pour les employés

1. **Planification anticipée**
   - Anticiper les demandes selon le délai de prévenance
   - Vérifier les soldes disponibles
   - Consulter le calendrier d'équipe
   - Coordonner avec les collègues

2. **Soumission efficace**
   - Fournir toutes les informations nécessaires
   - Joindre les justificatifs requis
   - Indiquer clairement le motif
   - Mentionner les informations importantes

3. **Suivi des demandes**
   - Vérifier régulièrement le statut
   - Répondre rapidement aux demandes d'information
   - Anticiper les refus potentiels
   - Planifier des alternatives

### Pour les managers

1. **Validation efficace**
   - Traiter les demandes rapidement
   - Fournir des motifs clairs en cas de refus
   - Considérer l'équité entre employés
   - Anticiper les impacts sur les projets

2. **Planification d'équipe**
   - Encourager la coordination
   - Définir des périodes de blackout si nécessaire
   - Assurer une couverture minimale
   - Communiquer les règles clairement

3. **Suivi des soldes**
   - Surveiller les soldes élevés
   - Encourager l'utilisation régulière
   - Planifier les reports
   - Anticiper les pics saisonniers

## Cas d'utilisation

### Demande de congés payés standard

1. L'employé crée une demande de type "Congés Payés"
2. Il sélectionne la période du 15 au 22 avril 2025
3. Le système calcule automatiquement 5,5 jours ouvrés
4. L'employé ajoute un motif "Vacances familiales"
5. Le système vérifie le solde disponible (suffisant)
6. La demande est soumise au manager direct
7. Le manager reçoit une notification
8. Le manager approuve la demande
9. L'employé est notifié de l'approbation
10. Le solde de congés payés est mis à jour

### Demande avec justificatif

1. L'employé crée une demande de type "Congé Maladie"
2. Il sélectionne la période du 3 au 5 mars 2025
3. Le système indique que ce type nécessite un justificatif
4. L'employé télécharge son arrêt maladie
5. La demande est soumise au service RH
6. Le RH vérifie le justificatif
7. Le RH approuve la demande
8. Le système enregistre l'absence sans déduction de solde

### Demande avec validation multi-niveaux

1. L'employé crée une demande de type "Congé Sans Solde"
2. Il sélectionne une période de 2 semaines
3. Il ajoute un motif détaillé et des commentaires
4. La demande est soumise au manager direct (niveau 1)
5. Le manager approuve avec commentaire
6. La demande est transmise au RH (niveau 2)
7. Le RH approuve la demande
8. L'employé est notifié de l'approbation finale
9. L'absence est enregistrée sans impact sur le solde
