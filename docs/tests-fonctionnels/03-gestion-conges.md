# Fiche de Tests Fonctionnels : Gestion des Congés

## Description du Module
Le module de gestion des congés permet de gérer l'ensemble du processus de demande, validation et suivi des congés des employés. Il gère différents types de congés, les soldes disponibles, et le workflow d'approbation.

## Prérequis
- Employeurs actifs dans le système
- Types de congés configurés
- Workflow d'approbation configuré
- Soldes de congés initialisés

## Cas de Tests

### CG-001 : Demande de congé par un employé

**Objectif** : Vérifier qu'un employé peut soumettre une demande de congé.

**Étapes de test** :
1. Se connecter avec un compte employé
2. Accéder au module "Mes congés"
3. Cliquer sur "Nouvelle demande"
4. Sélectionner le type de congé
5. Définir les dates de début et de fin
6. Ajouter une justification si nécessaire
7. Soumettre la demande

**Résultat attendu** :
- La demande est enregistrée avec succès
- Un message de confirmation s'affiche
- La demande apparaît dans la liste des demandes en attente
- Une notification est envoyée au(x) approbateur(s)

**Critères de validation** :
- Vérifier que le solde disponible est correctement vérifié
- Vérifier que les jours non ouvrables sont exclus du calcul
- Vérifier que le statut initial est "En attente"

### CG-002 : Approbation d'une demande de congé

**Objectif** : Vérifier que le processus d'approbation des congés fonctionne correctement.

**Étapes de test** :
1. Se connecter avec un compte superviseur/manager
2. Accéder au module "Approbation des congés"
3. Consulter la liste des demandes en attente
4. Sélectionner une demande
5. Examiner les détails de la demande
6. Approuver la demande
7. Ajouter un commentaire si nécessaire

**Résultat attendu** :
- La demande est approuvée avec succès
- Le statut de la demande passe à "Approuvé"
- Une notification est envoyée à l'employé
- La demande apparaît dans le calendrier des congés

**Critères de validation** :
- Vérifier que l'historique d'approbation est enregistré
- Vérifier que le solde de congés est mis à jour
- Vérifier que les jours de congé sont marqués dans le planning

### CG-003 : Rejet d'une demande de congé

**Objectif** : Vérifier qu'une demande de congé peut être rejetée avec justification.

**Étapes de test** :
1. Se connecter avec un compte superviseur/manager
2. Accéder au module "Approbation des congés"
3. Consulter la liste des demandes en attente
4. Sélectionner une demande
5. Examiner les détails de la demande
6. Rejeter la demande
7. Saisir un motif de rejet obligatoire

**Résultat attendu** :
- La demande est rejetée avec succès
- Le statut de la demande passe à "Rejeté"
- Une notification est envoyée à l'employé avec le motif du rejet
- La demande n'apparaît pas dans le calendrier des congés

**Critères de validation** :
- Vérifier que l'historique de rejet est enregistré
- Vérifier que le solde de congés n'est pas impacté
- Vérifier que le motif de rejet est bien enregistré

### CG-004 : Gestion des soldes de congés

**Objectif** : Vérifier que les soldes de congés sont correctement gérés et mis à jour.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Soldes de congés"
3. Rechercher un employé
4. Consulter ses soldes par type de congé
5. Effectuer un ajustement manuel (ajout ou déduction)
6. Enregistrer l'ajustement avec un commentaire

**Résultat attendu** :
- L'ajustement est enregistré avec succès
- Le nouveau solde est calculé et affiché
- L'historique des ajustements est mis à jour

**Critères de validation** :
- Vérifier que le calcul du nouveau solde est correct
- Vérifier que l'historique contient tous les détails de l'ajustement
- Vérifier que l'employé peut voir son nouveau solde

### CG-005 : Génération de rapport de congés

**Objectif** : Vérifier que le système peut générer un rapport détaillé des congés.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Rapports de congés"
3. Définir la période (année, trimestre, mois)
4. Sélectionner les types de congés à inclure
5. Choisir le format de rapport (détaillé ou synthétique)
6. Générer le rapport

**Résultat attendu** :
- Le rapport est généré avec succès
- Le rapport contient toutes les informations demandées
- Le rapport peut être téléchargé ou imprimé

**Critères de validation** :
- Vérifier que le rapport inclut les congés selon les critères sélectionnés
- Vérifier l'exactitude des données (dates, durées, types)
- Vérifier que les statistiques globales sont correctes

### CG-006 : Annulation d'un congé approuvé

**Objectif** : Vérifier qu'un congé déjà approuvé peut être annulé.

**Étapes de test** :
1. Se connecter avec un compte employé
2. Accéder au module "Mes congés"
3. Sélectionner un congé approuvé non commencé
4. Cliquer sur "Annuler"
5. Fournir un motif d'annulation
6. Confirmer l'annulation

**Résultat attendu** :
- Le congé est annulé avec succès
- Le statut du congé passe à "Annulé"
- Le solde de congés est recrédité
- Une notification est envoyée au superviseur

**Critères de validation** :
- Vérifier que le solde est correctement restauré
- Vérifier que l'historique d'annulation est enregistré
- Vérifier que le congé n'apparaît plus dans le calendrier actif

## Dépendances
- Module Employeur
- Module Workflow
- Module Notification
- Module Calendrier
