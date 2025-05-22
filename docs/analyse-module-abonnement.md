# Analyse du Module Abonnement

Après avoir examiné les fichiers partagés, voici une analyse complète du module Abonnement de l'application GENIUS WORK.

## Structure du Module

Le module Abonnement est composé de quatre ressources principales dans Filament, organisées dans un groupe de navigation "Abonnements" :

1. **AbonnementResource** - Gestion des abonnements (priorité de navigation: 1)
2. **FacturationResource** - Gestion des facturations (priorité de navigation: 2)
3. **PaiementResource** - Gestion des paiements (priorité de navigation: 3)
4. **FraisUsageResource** - Gestion des frais d'usage (visible dans le menu)

## Modèles et Relations

### 1. Modèle Abonnement
- Utilise les traits `HasFactory`, `SoftDeletes`, `HasUuids` et `BelongsToEntreprise`
- Relations avec:
  - Entreprise (belongsTo)
  - PlanAbonnement (belongsTo)
  - CodePromo (belongsTo)
  - Facturations (hasMany)
  - Paiements (hasMany)
- Scopes: actif, inactif, expire, enEssai
- Méthodes utilitaires: isActif, isExpire, getDureeRestante, renouveler, etc.

### 2. Modèle Facturation
- Utilise les traits `HasFactory`, `SoftDeletes`, `HasUuids` et `BelongsToEntreprise`
- Relations avec:
  - Entreprise (belongsTo)
  - Abonnement (belongsTo)
  - FraisUsages (hasMany)
- Scopes: paye, impaye, echu
- Méthodes utilitaires: isPaye, isEchu, calculerMontants, etc.

### 3. Modèle Paiement
- Utilise les traits `HasFactory`, `SoftDeletes`, `HasUuids` et `BelongsToEntreprise`
- Relations avec:
  - Facturation (belongsTo)
  - Abonnement (belongsTo)
  - Entreprise (belongsTo)
  - Initiateur (User, belongsTo)
  - Validateur (User, belongsTo)
- Constantes pour les statuts, méthodes et passerelles de paiement
- Méthodes importantes: valider, rejeter, annuler, genererReference

### 4. Modèle FraisUsage
- Utilise les traits `HasFactory`, `SoftDeletes`, `HasUuids` et `BelongsToEntreprise`
- Relations avec:
  - Entreprise (belongsTo)
  - Facturation (belongsTo)
- Scopes: facture, nonFacture, parType, periode
- Méthodes utilitaires: calculerMontantTotal, getMontantTotalFormate, etc.

## Fonctionnalités Clés des Resources

### 1. AbonnementResource
- **Badges de navigation**: Affiche le nombre d'abonnements expirant dans les 7 prochains jours
- **Formulaire**: Sections pour informations de l'abonnement, détails financiers, paramètres et notes
- **Table**: Colonnes pour entreprise, plan, dates, montant, statut, etc.
- **Actions**:
  - Renouveler un abonnement
  - Activer/désactiver un abonnement
  - Changer de plan
- **Filtrage par entreprise**: Restreint les données aux abonnements de l'entreprise de l'utilisateur (sauf pour SuperAdmin)

### 2. FacturationResource
- **Badges de navigation**: Affiche le nombre de facturations impayées
- **Formulaire**: Sections pour informations de facturation, dates, montants et paiement
- **Table**: Colonnes pour numéro de facture, entreprise, dates, montants, statut, etc.
- **Actions**:
  - Marquer comme payé
  - Générer un paiement
- **Filtrage par entreprise**: Restreint les données aux facturations de l'entreprise de l'utilisateur (sauf pour SuperAdmin)

### 3. PaiementResource
- **Badges de navigation**: Affiche le nombre de paiements en attente de validation
- **Formulaire**: Sections pour informations de paiement, montant et méthode
- **Table**: Colonnes pour référence, entreprise, montant, statut, etc.
- **Actions**:
  - Valider un paiement
  - Rejeter un paiement
- **Filtrage par entreprise**: Restreint les données aux paiements de l'entreprise de l'utilisateur (sauf pour SuperAdmin)

## Aspects Sécurité et Autorisation

1. **Filtrage des données par entreprise**:
   - Toutes les ressources utilisent `getEloquentQuery()` pour filtrer les données par entreprise de l'utilisateur connecté
   - Exception pour les SuperAdmin qui peuvent voir toutes les données

2. **Visibilité des actions**:
   - Certaines actions comme la suppression sont limitées aux SuperAdmin
   - D'autres actions sont conditionnelles selon l'état de l'enregistrement

3. **Trait BelongsToEntreprise**:
   - Tous les modèles utilisent ce trait qui implémente probablement un scope global pour le filtrage par entreprise
   - Selon la mémoire partagée, les rôles SuperAdmin et Support sont exemptés de ces restrictions

## Workflow et Automatisation

1. **Service AbonnementService**:
   - Gère les opérations complexes comme le renouvellement d'abonnement
   - Change le statut des abonnements
   - Change le plan d'abonnement

2. **Notifications**:
   - Le modèle Paiement inclut des méthodes pour envoyer des notifications lors des changements de statut

3. **Automatisation**:
   - Renouvellement automatique des abonnements
   - Génération automatique de factures

## Recommandations pour les Policies

Basé sur l'analyse et en tenant compte des policies déjà implémentées pour User, CodePromo et PlanAbonnement, je recommande de créer les policies suivantes:

1. **AbonnementPolicy**:
   - Restreindre l'accès complet aux SuperAdmin et Support
   - Permettre aux utilisateurs réguliers de voir uniquement les abonnements de leur entreprise
   - Limiter les actions sensibles (renouvellement, changement de plan) aux rôles appropriés

2. **FacturationPolicy**:
   - Restreindre l'accès complet aux SuperAdmin et Support
   - Permettre aux utilisateurs réguliers de voir uniquement les facturations de leur entreprise
   - Limiter les actions de suppression et de modification du statut de paiement aux rôles appropriés

3. **PaiementPolicy**:
   - Restreindre l'accès complet aux SuperAdmin et Support
   - Limiter la validation/rejet des paiements aux rôles appropriés

4. **FraisUsagePolicy**:
   - Restreindre l'accès complet aux SuperAdmin et Support
   - Permettre aux utilisateurs réguliers de voir uniquement les frais d'usage de leur entreprise

Ces policies devraient suivre le même modèle que celles déjà implémentées, en respectant la structure existante de l'application où les rôles SuperAdmin et Support ont un accès privilégié aux fonctionnalités administratives.
