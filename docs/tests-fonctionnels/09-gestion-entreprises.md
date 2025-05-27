# Fiche de Tests Fonctionnels : Gestion des Entreprises

## Description du Module
Le module de gestion des entreprises permet de créer et gérer les entreprises clientes, leurs filiales, leurs sites et leurs paramètres de configuration. Il constitue la structure organisationnelle de base du système.

## Prérequis
- Accès administrateur au système
- Plans d'abonnement configurés
- Paramètres régionaux configurés (pays, devises, etc.)

## Cas de Tests

### EN-001 : Création d'une nouvelle entreprise

**Objectif** : Vérifier qu'une nouvelle entreprise peut être créée avec toutes les informations requises.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Entreprises"
3. Cliquer sur "Ajouter une entreprise"
4. Remplir les informations obligatoires :
   - Raison sociale
   - Forme juridique
   - Numéro d'identification fiscale
   - Secteur d'activité
   - Coordonnées (adresse, téléphone, email)
   - Informations du responsable
5. Sélectionner un plan d'abonnement
6. Enregistrer l'entreprise

**Résultat attendu** :
- L'entreprise est créée avec succès
- Un compte administrateur pour l'entreprise est créé
- L'entreprise apparaît dans la liste des entreprises

**Critères de validation** :
- Vérifier que toutes les informations sont correctement enregistrées
- Vérifier que le plan d'abonnement est correctement associé
- Vérifier que les paramètres par défaut sont configurés

### EN-002 : Création d'une filiale

**Objectif** : Vérifier qu'une filiale peut être créée et rattachée à une entreprise principale.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Accéder au module "Structure organisationnelle"
3. Sélectionner "Filiales"
4. Cliquer sur "Ajouter une filiale"
5. Remplir les informations de la filiale :
   - Nom de la filiale
   - Adresse
   - Responsable
   - Paramètres spécifiques
6. Enregistrer la filiale

**Résultat attendu** :
- La filiale est créée avec succès
- La filiale est correctement rattachée à l'entreprise principale
- La filiale apparaît dans l'organigramme de l'entreprise

**Critères de validation** :
- Vérifier que la hiérarchie organisationnelle est correcte
- Vérifier que les utilisateurs peuvent être affectés à la filiale
- Vérifier que les paramètres spécifiques sont appliqués

### EN-003 : Création et gestion des départements

**Objectif** : Vérifier que des départements peuvent être créés et organisés au sein de l'entreprise.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Accéder au module "Structure organisationnelle"
3. Sélectionner "Départements"
4. Cliquer sur "Ajouter un département"
5. Remplir les informations du département :
   - Nom du département
   - Description
   - Responsable
   - Département parent (si applicable)
6. Enregistrer le département

**Résultat attendu** :
- Le département est créé avec succès
- Le département est correctement positionné dans la hiérarchie
- Le département apparaît dans l'organigramme de l'entreprise

**Critères de validation** :
- Vérifier que la structure hiérarchique des départements est respectée
- Vérifier que les employés peuvent être affectés au département
- Vérifier que les rapports peuvent être filtrés par département

### EN-004 : Configuration des sites de travail

**Objectif** : Vérifier que des sites de travail peuvent être configurés pour l'entreprise.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Accéder au module "Sites"
3. Cliquer sur "Ajouter un site"
4. Remplir les informations du site :
   - Nom du site
   - Adresse complète
   - Coordonnées GPS
   - Responsable du site
   - Horaires d'ouverture
5. Configurer les appareils de pointage associés au site
6. Enregistrer le site

**Résultat attendu** :
- Le site est créé avec succès
- Les appareils de pointage sont correctement associés
- Le site apparaît dans la liste des sites de l'entreprise

**Critères de validation** :
- Vérifier que les coordonnées GPS sont correctement enregistrées
- Vérifier que les employés peuvent être affectés au site
- Vérifier que les appareils de pointage fonctionnent sur le site

### EN-005 : Configuration des paramètres d'entreprise

**Objectif** : Vérifier que les paramètres spécifiques de l'entreprise peuvent être configurés.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Accéder au module "Paramètres d'entreprise"
3. Configurer les différents paramètres :
   - Jours travaillés
   - Horaires standard
   - Politique de retard/absence
   - Politique de congés
   - Paramètres de paie
4. Enregistrer les paramètres

**Résultat attendu** :
- Les paramètres sont enregistrés avec succès
- Les paramètres sont appliqués à toute l'entreprise
- Les modules concernés utilisent ces paramètres

**Critères de validation** :
- Vérifier que les jours travaillés sont correctement appliqués au calendrier
- Vérifier que les politiques de retard/absence sont appliquées
- Vérifier que les paramètres de paie sont utilisés dans les calculs

### EN-006 : Gestion des limites d'abonnement

**Objectif** : Vérifier que les limites liées au plan d'abonnement sont correctement appliquées.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Tenter de créer des employés jusqu'à atteindre la limite du plan
3. Tenter de créer un employé supplémentaire
4. Tenter d'accéder à une fonctionnalité non incluse dans le plan

**Résultat attendu** :
- Les employés peuvent être créés jusqu'à la limite
- Un message d'erreur s'affiche lors de la tentative de dépassement
- L'accès aux fonctionnalités non incluses est bloqué avec un message approprié

**Critères de validation** :
- Vérifier que les limites sont strictement appliquées
- Vérifier que les messages d'erreur sont clairs et suggèrent une mise à niveau
- Vérifier que le compteur d'utilisation est précis

## Dépendances
- Module Abonnement
- Module Utilisateur
- Module Paramètres système
- Module Géolocalisation
