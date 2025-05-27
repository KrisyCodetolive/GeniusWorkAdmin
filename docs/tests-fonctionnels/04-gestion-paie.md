# Fiche de Tests Fonctionnels : Gestion de la Paie

## Description du Module
Le module de gestion de la paie permet de calculer, générer et gérer les bulletins de paie des employés. Il prend en compte les salaires de base, les indemnités, les primes, les retenues et génère les documents officiels de paie.

## Prérequis
- Employeurs actifs dans le système
- Configuration des éléments de paie (indemnités, primes, retenues)
- Configuration des paramètres de calcul (CNPS, IGR, etc.)
- Données de présence à jour

## Cas de Tests

### PA-001 : Génération d'un bulletin de paie individuel

**Objectif** : Vérifier qu'un bulletin de paie peut être généré pour un employé spécifique.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Paie"
3. Sélectionner "Nouveau bulletin"
4. Rechercher et sélectionner un employé
5. Définir la période de paie (mois et année)
6. Vérifier les éléments de paie pré-calculés
7. Ajuster manuellement certains éléments si nécessaire
8. Générer le bulletin

**Résultat attendu** :
- Le bulletin est généré avec succès au statut "brouillon"
- Tous les calculs sont effectués correctement
- Un aperçu du bulletin est disponible

**Critères de validation** :
- Vérifier l'exactitude des calculs (salaire brut, retenues, salaire net)
- Vérifier que les données de présence sont correctement intégrées
- Vérifier que la référence du bulletin est générée selon le format attendu

### PA-002 : Génération de bulletins de paie en masse

**Objectif** : Vérifier que des bulletins de paie peuvent être générés en masse pour plusieurs employés.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Paie"
3. Sélectionner "Génération en masse"
4. Définir la période de paie (mois et année)
5. Sélectionner un département ou une liste d'employés
6. Lancer la génération en masse
7. Suivre la progression du traitement

**Résultat attendu** :
- Les bulletins sont générés avec succès pour tous les employés sélectionnés
- Un rapport de génération s'affiche (nombre de bulletins générés, erreurs éventuelles)
- Les bulletins sont disponibles dans la liste des bulletins en statut "brouillon"

**Critères de validation** :
- Vérifier que tous les employés sélectionnés ont un bulletin
- Vérifier la gestion des erreurs (données manquantes, configurations incomplètes)
- Vérifier la performance du système lors de la génération en masse

### PA-003 : Validation et finalisation des bulletins de paie

**Objectif** : Vérifier que les bulletins de paie peuvent être validés et finalisés.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Paie"
3. Consulter la liste des bulletins en statut "brouillon"
4. Sélectionner un ou plusieurs bulletins
5. Vérifier les détails de chaque bulletin
6. Cliquer sur "Valider et finaliser"
7. Confirmer la validation

**Résultat attendu** :
- Les bulletins sélectionnés passent au statut "validé"
- Les fichiers PDF des bulletins sont générés
- Les bulletins ne peuvent plus être modifiés

**Critères de validation** :
- Vérifier que les PDF sont correctement générés et accessibles
- Vérifier que l'historique de validation est enregistré
- Vérifier que les bulletins validés ne peuvent plus être modifiés

### PA-004 : Exportation des bulletins de paie en Excel

**Objectif** : Vérifier que les bulletins de paie peuvent être exportés au format Excel pour analyse.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Paie"
3. Utiliser l'action "ExporterBulletinsPaieAction"
4. Définir les filtres (période, statut)
5. Lancer l'exportation

**Résultat attendu** :
- Un fichier Excel est généré avec succès
- Le fichier contient tous les bulletins correspondant aux critères
- Le fichier est structuré selon le format attendu

**Critères de validation** :
- Vérifier que toutes les colonnes requises sont présentes
- Vérifier l'exactitude des données exportées
- Vérifier que le streaming direct fonctionne correctement

### PA-005 : Génération d'un rapport d'analyse de paie

**Objectif** : Vérifier que le système peut générer un rapport d'analyse complet sur la paie.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Paie"
3. Utiliser l'action "GenererRapportPaieAction"
4. Définir la période d'analyse
5. Sélectionner les options d'analyse souhaitées
6. Générer le rapport

**Résultat attendu** :
- Le rapport est généré avec succès
- Le rapport contient les statistiques sur la masse salariale, indemnités, primes, etc.
- Le rapport peut être téléchargé ou imprimé

**Critères de validation** :
- Vérifier que toutes les sections du rapport sont présentes
- Vérifier l'exactitude des calculs statistiques
- Vérifier que les graphiques sont correctement générés

### PA-006 : Annulation d'un bulletin de paie

**Objectif** : Vérifier qu'un bulletin de paie validé peut être annulé si nécessaire.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Paie"
3. Rechercher un bulletin validé
4. Cliquer sur "Annuler"
5. Saisir un motif d'annulation
6. Confirmer l'annulation

**Résultat attendu** :
- Le bulletin passe au statut "annulé"
- Le motif d'annulation est enregistré
- Une trace de l'annulation est conservée dans l'historique

**Critères de validation** :
- Vérifier que le bulletin annulé est clairement identifié comme tel
- Vérifier que l'historique contient les détails de l'annulation
- Vérifier que le bulletin annulé n'est pas pris en compte dans les rapports standards

## Dépendances
- Module Employeur
- Module Présence
- Module Heures supplémentaires
- Module Configuration de paie
