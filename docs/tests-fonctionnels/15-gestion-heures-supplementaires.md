# Fiche de Tests Fonctionnels : Gestion des Heures Supplémentaires

## Description du Module
Le module de gestion des heures supplémentaires permet de suivre, valider et rémunérer les heures travaillées au-delà des horaires normaux. Il gère les demandes d'heures supplémentaires, leur approbation, leur calcul et leur intégration dans la paie.

## Prérequis
- Employeurs actifs dans le système
- Plages horaires standard définies
- Workflow d'approbation configuré
- Configuration des taux de majoration

## Cas de Tests

### HS-001 : Détection automatique des heures supplémentaires

**Objectif** : Vérifier que le système détecte automatiquement les heures supplémentaires basées sur les pointages.

**Étapes de test** :
1. Configurer les plages horaires standard pour un employé
2. Simuler des pointages dépassant les horaires standards
3. Exécuter le traitement de détection des heures supplémentaires
4. Vérifier les heures supplémentaires détectées

**Résultat attendu** :
- Les heures supplémentaires sont correctement détectées
- Le calcul de la durée est exact
- Les heures sont classées selon le type (jour ouvré, nuit, week-end, férié)

**Critères de validation** :
- Vérifier que seules les heures au-delà de l'horaire standard sont comptabilisées
- Vérifier que les différents taux de majoration sont correctement appliqués
- Vérifier que les heures sont associées au bon employé et à la bonne date

### HS-002 : Demande d'heures supplémentaires planifiées

**Objectif** : Vérifier qu'un employé peut demander à effectuer des heures supplémentaires planifiées.

**Étapes de test** :
1. Se connecter avec un compte employé
2. Accéder au module "Heures supplémentaires"
3. Cliquer sur "Nouvelle demande"
4. Remplir les informations :
   - Date et plage horaire
   - Motif
   - Tâches prévues
5. Soumettre la demande

**Résultat attendu** :
- La demande est enregistrée avec succès
- Un message de confirmation s'affiche
- La demande apparaît dans la liste des demandes en attente
- Une notification est envoyée au(x) approbateur(s)

**Critères de validation** :
- Vérifier que toutes les informations sont correctement enregistrées
- Vérifier que le workflow d'approbation est déclenché
- Vérifier que l'employé peut suivre le statut de sa demande

### HS-003 : Approbation d'une demande d'heures supplémentaires

**Objectif** : Vérifier que le processus d'approbation des heures supplémentaires fonctionne correctement.

**Étapes de test** :
1. Se connecter avec un compte superviseur/manager
2. Accéder au module "Approbation des heures supplémentaires"
3. Consulter la liste des demandes en attente
4. Sélectionner une demande
5. Examiner les détails de la demande
6. Approuver la demande
7. Ajouter un commentaire si nécessaire

**Résultat attendu** :
- La demande est approuvée avec succès
- Le statut de la demande passe à "Approuvé"
- Une notification est envoyée à l'employé
- Les heures supplémentaires sont planifiées dans le système

**Critères de validation** :
- Vérifier que l'historique d'approbation est enregistré
- Vérifier que les heures approuvées sont marquées comme autorisées
- Vérifier que les notifications sont envoyées aux personnes concernées

### HS-004 : Validation des heures supplémentaires effectuées

**Objectif** : Vérifier que les heures supplémentaires effectuées peuvent être validées.

**Étapes de test** :
1. Se connecter avec un compte superviseur/manager
2. Accéder au module "Validation des heures supplémentaires"
3. Consulter la liste des heures supplémentaires en attente de validation
4. Sélectionner une entrée
5. Vérifier les heures réellement effectuées (basées sur les pointages)
6. Ajuster si nécessaire
7. Valider les heures

**Résultat attendu** :
- Les heures supplémentaires sont validées avec succès
- Le statut passe à "Validé pour paiement"
- Les heures sont prêtes à être intégrées dans la paie

**Critères de validation** :
- Vérifier que les ajustements sont correctement enregistrés
- Vérifier que l'historique de validation est conservé
- Vérifier que les heures validées sont correctement marquées pour la paie

### HS-005 : Intégration des heures supplémentaires dans la paie

**Objectif** : Vérifier que les heures supplémentaires validées sont correctement intégrées dans la paie.

**Étapes de test** :
1. Se connecter avec un compte RH ou paie
2. Accéder au module "Préparation de la paie"
3. Sélectionner la période de paie
4. Vérifier que les heures supplémentaires validées sont incluses
5. Générer un bulletin de paie test

**Résultat attendu** :
- Les heures supplémentaires sont correctement intégrées dans le calcul de la paie
- Les différents taux de majoration sont appliqués
- Les montants sont correctement calculés

**Critères de validation** :
- Vérifier que toutes les heures validées sont prises en compte
- Vérifier que les calculs de majoration sont exacts
- Vérifier que les montants apparaissent correctement sur le bulletin

### HS-006 : Génération de rapport d'heures supplémentaires

**Objectif** : Vérifier que des rapports sur les heures supplémentaires peuvent être générés.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Rapports d'heures supplémentaires"
3. Définir la période d'analyse
4. Sélectionner les employés ou départements concernés
5. Choisir le type de rapport (détaillé ou synthétique)
6. Générer le rapport

**Résultat attendu** :
- Le rapport est généré avec succès
- Le rapport contient toutes les informations demandées
- Le rapport peut être téléchargé ou imprimé

**Critères de validation** :
- Vérifier que le rapport inclut toutes les heures supplémentaires de la période
- Vérifier l'exactitude des données (durées, taux, montants)
- Vérifier que les statistiques par employé et département sont correctes

### HS-007 : Configuration des règles de calcul des heures supplémentaires

**Objectif** : Vérifier que les règles de calcul des heures supplémentaires peuvent être configurées.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Accéder au module "Paramètres de paie"
3. Sélectionner "Configuration des heures supplémentaires"
4. Configurer les différents taux de majoration :
   - Heures supplémentaires normales
   - Heures de nuit
   - Heures du week-end
   - Heures des jours fériés
5. Enregistrer la configuration

**Résultat attendu** :
- La configuration est enregistrée avec succès
- Les nouveaux taux sont appliqués aux calculs futurs
- Un historique des modifications est conservé

**Critères de validation** :
- Vérifier que les taux sont correctement enregistrés
- Vérifier que les calculs utilisent les nouveaux taux
- Vérifier que les modifications n'affectent pas les calculs déjà validés

## Dépendances
- Module Employeur
- Module Présence
- Module Pointage
- Module Paie
- Module Workflow
