# Fiche de Tests Fonctionnels : Gestion des Appareils Biométriques

## Description du Module
Le module de gestion des appareils biométriques permet de configurer, synchroniser et gérer les appareils de pointage biométrique utilisés pour enregistrer les entrées et sorties des employés. Il gère la communication avec les appareils, l'enregistrement des empreintes et la synchronisation des données.

## Prérequis
- Appareils biométriques compatibles installés
- Réseau configuré pour la communication avec les appareils
- Sites et employés enregistrés dans le système

## Cas de Tests

### BM-001 : Enregistrement d'un nouvel appareil biométrique

**Objectif** : Vérifier qu'un nouvel appareil biométrique peut être enregistré dans le système.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Appareils biométriques"
3. Cliquer sur "Ajouter un appareil"
4. Remplir les informations de l'appareil :
   - Nom de l'appareil
   - Modèle
   - Numéro de série
   - Adresse IP
   - Port de communication
   - Site d'installation
5. Tester la connexion
6. Enregistrer l'appareil

**Résultat attendu** :
- L'appareil est enregistré avec succès
- La connexion avec l'appareil est établie
- L'appareil apparaît dans la liste des appareils actifs

**Critères de validation** :
- Vérifier que toutes les informations sont correctement enregistrées
- Vérifier que la communication avec l'appareil fonctionne
- Vérifier que l'appareil est correctement associé au site

### BM-002 : Synchronisation des employés avec un appareil

**Objectif** : Vérifier que les données des employés peuvent être synchronisées avec un appareil biométrique.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Appareils biométriques"
3. Sélectionner un appareil
4. Cliquer sur "Synchroniser les employés"
5. Sélectionner les employés à synchroniser
6. Lancer la synchronisation

**Résultat attendu** :
- Les données des employés sont envoyées à l'appareil avec succès
- Un rapport de synchronisation s'affiche
- Les employés peuvent utiliser l'appareil pour pointer

**Critères de validation** :
- Vérifier que tous les employés sélectionnés sont correctement synchronisés
- Vérifier que les identifiants uniques sont correctement attribués
- Vérifier que les erreurs de synchronisation sont correctement gérées

### BM-003 : Enregistrement d'empreintes digitales

**Objectif** : Vérifier que les empreintes digitales des employés peuvent être enregistrées.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Empreintes biométriques"
3. Rechercher et sélectionner un employé
4. Cliquer sur "Enregistrer les empreintes"
5. Connecter l'appareil d'enregistrement
6. Guider l'employé pour l'enregistrement des empreintes
7. Enregistrer les données biométriques

**Résultat attendu** :
- Les empreintes sont enregistrées avec succès
- Les données biométriques sont sécurisées
- Les empreintes sont synchronisées avec les appareils concernés

**Critères de validation** :
- Vérifier que les empreintes sont correctement capturées
- Vérifier que les données sont stockées de manière sécurisée
- Vérifier que les empreintes fonctionnent sur les appareils après synchronisation

### BM-004 : Récupération des données de pointage

**Objectif** : Vérifier que les données de pointage peuvent être récupérées des appareils biométriques.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Appareils biométriques"
3. Sélectionner un appareil
4. Cliquer sur "Récupérer les pointages"
5. Définir la période de récupération
6. Lancer la récupération

**Résultat attendu** :
- Les données de pointage sont récupérées avec succès
- Un rapport de récupération s'affiche
- Les pointages sont intégrés au système de présence

**Critères de validation** :
- Vérifier que tous les pointages sont correctement récupérés
- Vérifier que les données sont correctement formatées et interprétées
- Vérifier que les doublons sont correctement gérés

### BM-005 : Configuration de la synchronisation automatique

**Objectif** : Vérifier que la synchronisation automatique peut être configurée.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Appareils biométriques"
3. Sélectionner un appareil
4. Cliquer sur "Configuration avancée"
5. Configurer la synchronisation automatique :
   - Fréquence de récupération des pointages
   - Horaires de synchronisation
   - Paramètres de notification
6. Enregistrer la configuration

**Résultat attendu** :
- La configuration est enregistrée avec succès
- La synchronisation automatique fonctionne selon les paramètres définis
- Les notifications sont envoyées en cas d'erreur

**Critères de validation** :
- Vérifier que la synchronisation s'exécute aux horaires définis
- Vérifier que les données sont correctement récupérées
- Vérifier que les erreurs de synchronisation sont notifiées

### BM-006 : Gestion des logs d'appareil

**Objectif** : Vérifier que les logs des appareils biométriques peuvent être consultés et analysés.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Appareils biométriques"
3. Sélectionner un appareil
4. Cliquer sur "Logs d'appareil"
5. Définir la période d'analyse
6. Consulter les logs

**Résultat attendu** :
- Les logs sont récupérés et affichés avec succès
- Les événements sont classés par type et gravité
- Les erreurs peuvent être filtrées et analysées

**Critères de validation** :
- Vérifier que tous les types d'événements sont correctement enregistrés
- Vérifier que les erreurs critiques sont mises en évidence
- Vérifier que les logs peuvent être exportés pour analyse approfondie

### BM-007 : Mise à jour du firmware des appareils

**Objectif** : Vérifier que le firmware des appareils biométriques peut être mis à jour.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Appareils biométriques"
3. Sélectionner un appareil
4. Cliquer sur "Mise à jour firmware"
5. Sélectionner le fichier de firmware
6. Lancer la mise à jour

**Résultat attendu** :
- Le firmware est mis à jour avec succès
- Un rapport de mise à jour s'affiche
- L'appareil redémarre et fonctionne avec la nouvelle version

**Critères de validation** :
- Vérifier que la mise à jour est sécurisée et ne corrompt pas l'appareil
- Vérifier que la version du firmware est correctement mise à jour
- Vérifier que l'appareil fonctionne normalement après la mise à jour

## Dépendances
- Module Employeur
- Module Présence
- Module Pointage
- Module Site
