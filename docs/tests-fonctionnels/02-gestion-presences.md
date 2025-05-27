# Fiche de Tests Fonctionnels : Gestion des Présences

## Description du Module
Le module de gestion des présences permet de suivre, enregistrer et analyser la présence des employés au sein de l'entreprise. Il gère les entrées/sorties, les retards, les absences et génère des rapports détaillés de présence.

## Prérequis
- Employeurs actifs dans le système
- Configuration des horaires de travail
- Méthodes de pointage configurées (biométrique, QR code, etc.)

## Cas de Tests

### PR-001 : Enregistrement manuel d'une présence

**Objectif** : Vérifier qu'un administrateur ou superviseur peut enregistrer manuellement la présence d'un employé.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou superviseur
2. Accéder au module "Présences"
3. Sélectionner "Enregistrement manuel"
4. Rechercher et sélectionner un employé
5. Saisir la date, l'heure d'entrée et l'heure de sortie
6. Ajouter un commentaire si nécessaire
7. Enregistrer la présence

**Résultat attendu** :
- La présence est enregistrée avec succès
- Un message de confirmation s'affiche
- La présence apparaît dans le journal des présences de l'employé

**Critères de validation** :
- Vérifier que les heures sont correctement enregistrées
- Vérifier que le calcul des heures travaillées est correct
- Vérifier que le statut de présence est correctement attribué (normal, retard, etc.)

### PR-002 : Pointage par QR Code

**Objectif** : Vérifier que le système permet le pointage via QR Code.

**Étapes de test** :
1. Accéder à l'interface de pointage QR Code
2. Scanner le QR Code d'un employé (entrée)
3. Vérifier la confirmation de pointage
4. Attendre quelques heures
5. Scanner à nouveau le QR Code du même employé (sortie)
6. Vérifier la confirmation de pointage

**Résultat attendu** :
- Les pointages d'entrée et de sortie sont enregistrés avec succès
- Des confirmations visuelles s'affichent à chaque scan
- Les données de présence sont mises à jour en temps réel

**Critères de validation** :
- Vérifier que les heures exactes sont enregistrées
- Vérifier que le système détecte correctement s'il s'agit d'une entrée ou d'une sortie
- Vérifier que les données sont synchronisées avec le journal des présences

### PR-003 : Génération de rapport de présence

**Objectif** : Vérifier que le système peut générer un rapport détaillé des présences.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Rapports de présence"
3. Définir la période (mois, semaine ou dates personnalisées)
4. Sélectionner les employés ou départements concernés
5. Choisir le format de rapport (détaillé ou synthétique)
6. Générer le rapport

**Résultat attendu** :
- Le rapport est généré avec succès au format Excel
- Le rapport contient toutes les informations demandées
- Le rapport respecte la structure définie dans les mémoires (5 onglets)

**Critères de validation** :
- Vérifier que le rapport contient tous les onglets requis
- Vérifier l'exactitude des données (heures travaillées, retards, absences)
- Vérifier que les calculs de statistiques sont corrects

### PR-004 : Gestion des anomalies de pointage

**Objectif** : Vérifier que le système détecte et permet de gérer les anomalies de pointage.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou superviseur
2. Accéder au module "Anomalies de pointage"
3. Consulter la liste des anomalies détectées
4. Sélectionner une anomalie (ex: entrée sans sortie)
5. Corriger l'anomalie en saisissant les informations manquantes
6. Enregistrer la correction

**Résultat attendu** :
- L'anomalie est corrigée avec succès
- Un historique de la correction est conservé
- L'anomalie disparaît de la liste des anomalies actives

**Critères de validation** :
- Vérifier que toutes les anomalies sont correctement détectées
- Vérifier que la correction est appliquée aux données de présence
- Vérifier que l'historique des modifications est conservé

### PR-005 : Analyse statistique des présences

**Objectif** : Vérifier que le système permet d'analyser les statistiques de présence.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Statistiques de présence"
3. Définir la période d'analyse
4. Sélectionner les indicateurs à analyser (taux de présence, retards, absences)
5. Générer l'analyse

**Résultat attendu** :
- Les statistiques sont générées avec succès
- Les graphiques et tableaux sont affichés correctement
- Les données sont cohérentes avec les enregistrements de présence

**Critères de validation** :
- Vérifier l'exactitude des calculs statistiques
- Vérifier que les tendances sont correctement identifiées
- Vérifier que les comparaisons entre périodes fonctionnent

## Dépendances
- Module Employeur
- Module Horaires de travail
- Module Pointage
- Module Rapports
