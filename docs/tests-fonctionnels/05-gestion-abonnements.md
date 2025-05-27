# Fiche de Tests Fonctionnels : Gestion des Abonnements

## Description du Module
Le module de gestion des abonnements permet de gérer les différentes offres d'abonnement au service, les souscriptions des entreprises, les renouvellements, les paiements et les changements de plan.

## Prérequis
- Plans d'abonnement configurés dans le système
- Passerelles de paiement configurées
- Entreprises enregistrées

## Cas de Tests

### AB-001 : Création d'un nouveau plan d'abonnement

**Objectif** : Vérifier qu'un administrateur peut créer un nouveau plan d'abonnement.

**Étapes de test** :
1. Se connecter avec un compte administrateur système
2. Accéder au module "Plans d'abonnement"
3. Cliquer sur "Ajouter un plan"
4. Remplir les informations du plan :
   - Nom du plan
   - Description
   - Prix mensuel et annuel
   - Fonctionnalités incluses
   - Limites (nombre d'employés, de sites, etc.)
5. Enregistrer le plan

**Résultat attendu** :
- Le plan est créé avec succès
- Un message de confirmation s'affiche
- Le plan apparaît dans la liste des plans disponibles

**Critères de validation** :
- Vérifier que toutes les informations sont correctement enregistrées
- Vérifier que le plan est disponible pour les nouvelles souscriptions
- Vérifier que les limites sont correctement configurées

### AB-002 : Souscription à un abonnement

**Objectif** : Vérifier qu'une entreprise peut souscrire à un plan d'abonnement.

**Étapes de test** :
1. Se connecter avec un compte entreprise
2. Accéder au module "Abonnements"
3. Consulter les plans disponibles
4. Sélectionner un plan
5. Choisir la durée (mensuel ou annuel)
6. Appliquer un code promo si disponible
7. Procéder au paiement
8. Compléter les informations de facturation
9. Confirmer la souscription

**Résultat attendu** :
- L'abonnement est créé avec succès
- Une facture est générée
- L'entreprise a accès aux fonctionnalités du plan souscrit

**Critères de validation** :
- Vérifier que le paiement est correctement traité
- Vérifier que les dates de début et de fin d'abonnement sont correctes
- Vérifier que les limites du plan sont appliquées à l'entreprise

### AB-003 : Renouvellement automatique d'un abonnement

**Objectif** : Vérifier que le renouvellement automatique d'un abonnement fonctionne correctement.

**Étapes de test** :
1. Configurer un abonnement avec renouvellement automatique
2. Simuler l'approche de la date d'expiration
3. Vérifier que le système envoie des notifications de rappel
4. Simuler la date d'expiration
5. Vérifier le processus de renouvellement automatique

**Résultat attendu** :
- Le système tente de renouveler automatiquement l'abonnement
- Une nouvelle période d'abonnement est créée
- Une nouvelle facture est générée
- L'entreprise conserve l'accès aux fonctionnalités

**Critères de validation** :
- Vérifier que les notifications sont envoyées aux bonnes échéances
- Vérifier que le paiement automatique est correctement traité
- Vérifier que les dates de la nouvelle période sont correctes

### AB-004 : Changement de plan d'abonnement

**Objectif** : Vérifier qu'une entreprise peut changer de plan d'abonnement.

**Étapes de test** :
1. Se connecter avec un compte entreprise ayant un abonnement actif
2. Accéder au module "Mon abonnement"
3. Cliquer sur "Changer de plan"
4. Sélectionner un nouveau plan
5. Consulter le récapitulatif des changements (prorata, différence de prix)
6. Confirmer le changement

**Résultat attendu** :
- Le changement de plan est effectué avec succès
- Le calcul du prorata est correct
- Une facture d'ajustement est générée si nécessaire
- L'entreprise a accès aux fonctionnalités du nouveau plan

**Critères de validation** :
- Vérifier que le calcul du prorata est exact
- Vérifier que les nouvelles limites sont appliquées immédiatement
- Vérifier que l'historique des changements est conservé

### AB-005 : Gestion des codes promotionnels

**Objectif** : Vérifier que les codes promotionnels fonctionnent correctement.

**Étapes de test** :
1. Se connecter avec un compte administrateur système
2. Accéder au module "Codes promotionnels"
3. Créer un nouveau code promo avec :
   - Code unique
   - Pourcentage ou montant de réduction
   - Date de validité
   - Nombre d'utilisations maximum
4. Enregistrer le code promo
5. Tester l'utilisation du code lors d'une souscription

**Résultat attendu** :
- Le code promo est créé avec succès
- La réduction est correctement appliquée lors de la souscription
- Le nombre d'utilisations est incrémenté

**Critères de validation** :
- Vérifier que la réduction est calculée correctement
- Vérifier que le code expire à la date prévue
- Vérifier que le code ne peut plus être utilisé après avoir atteint le maximum

### AB-006 : Annulation d'un abonnement

**Objectif** : Vérifier qu'une entreprise peut annuler son abonnement.

**Étapes de test** :
1. Se connecter avec un compte entreprise ayant un abonnement actif
2. Accéder au module "Mon abonnement"
3. Cliquer sur "Annuler l'abonnement"
4. Sélectionner un motif d'annulation
5. Confirmer l'annulation

**Résultat attendu** :
- L'annulation est enregistrée avec succès
- L'abonnement reste actif jusqu'à la fin de la période payée
- Le renouvellement automatique est désactivé
- Une notification de confirmation est envoyée

**Critères de validation** :
- Vérifier que l'entreprise conserve l'accès jusqu'à la fin de la période
- Vérifier que le motif d'annulation est enregistré
- Vérifier qu'aucun renouvellement n'est effectué après l'annulation

## Dépendances
- Module Entreprise
- Module Facturation
- Module Paiement
- Module Notification
