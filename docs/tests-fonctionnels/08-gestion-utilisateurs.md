# Fiche de Tests Fonctionnels : Gestion des Utilisateurs

## Description du Module
Le module de gestion des utilisateurs permet de créer, modifier et gérer les comptes utilisateurs du système, leurs rôles, permissions et accès aux différentes fonctionnalités de l'application.

## Prérequis
- Accès administrateur au système
- Rôles et permissions configurés
- Entreprises et départements créés

## Cas de Tests

### UT-001 : Création d'un nouvel utilisateur

**Objectif** : Vérifier qu'un nouvel utilisateur peut être créé avec les informations et permissions appropriées.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Utilisateurs"
3. Cliquer sur "Ajouter un utilisateur"
4. Remplir les informations obligatoires :
   - Nom et prénom
   - Email
   - Téléphone
   - Entreprise/département
   - Rôle
5. Définir les permissions spécifiques si nécessaire
6. Enregistrer l'utilisateur

**Résultat attendu** :
- L'utilisateur est créé avec succès
- Un email d'invitation avec instructions de connexion est envoyé
- L'utilisateur apparaît dans la liste des utilisateurs

**Critères de validation** :
- Vérifier que toutes les informations sont correctement enregistrées
- Vérifier que les permissions sont correctement attribuées
- Vérifier que l'email d'invitation contient les bonnes informations

### UT-002 : Modification des rôles et permissions

**Objectif** : Vérifier qu'il est possible de modifier les rôles et permissions d'un utilisateur existant.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Utilisateurs"
3. Rechercher un utilisateur existant
4. Cliquer sur "Modifier"
5. Changer le rôle de l'utilisateur
6. Ajuster les permissions spécifiques
7. Enregistrer les modifications

**Résultat attendu** :
- Les modifications sont enregistrées avec succès
- Un message de confirmation s'affiche
- Les nouvelles permissions sont immédiatement appliquées

**Critères de validation** :
- Vérifier que le changement de rôle modifie correctement les permissions de base
- Vérifier que les permissions spécifiques sont correctement ajustées
- Vérifier que l'utilisateur a accès aux fonctionnalités correspondant à son nouveau rôle

### UT-003 : Désactivation d'un compte utilisateur

**Objectif** : Vérifier qu'il est possible de désactiver temporairement un compte utilisateur.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Utilisateurs"
3. Rechercher un utilisateur actif
4. Cliquer sur "Désactiver le compte"
5. Confirmer la désactivation

**Résultat attendu** :
- Le compte est désactivé avec succès
- L'utilisateur est marqué comme inactif dans la liste
- L'utilisateur ne peut plus se connecter au système

**Critères de validation** :
- Vérifier que l'utilisateur est bien marqué comme inactif
- Vérifier que toute tentative de connexion est refusée
- Vérifier que les sessions actives de l'utilisateur sont terminées

### UT-004 : Réinitialisation du mot de passe

**Objectif** : Vérifier que le processus de réinitialisation de mot de passe fonctionne correctement.

**Étapes de test** :
1. Accéder à la page de connexion
2. Cliquer sur "Mot de passe oublié"
3. Saisir l'email d'un utilisateur existant
4. Soumettre la demande
5. Vérifier la réception de l'email de réinitialisation
6. Cliquer sur le lien de réinitialisation
7. Définir un nouveau mot de passe
8. Se connecter avec le nouveau mot de passe

**Résultat attendu** :
- L'email de réinitialisation est envoyé avec succès
- Le lien de réinitialisation fonctionne correctement
- Le nouveau mot de passe est accepté
- La connexion avec le nouveau mot de passe réussit

**Critères de validation** :
- Vérifier que l'email est envoyé rapidement
- Vérifier que le lien de réinitialisation a une durée de validité limitée
- Vérifier que les règles de complexité du mot de passe sont appliquées

### UT-005 : Authentification à deux facteurs

**Objectif** : Vérifier que l'authentification à deux facteurs fonctionne correctement.

**Étapes de test** :
1. Se connecter avec un compte utilisateur
2. Accéder aux paramètres de sécurité
3. Activer l'authentification à deux facteurs
4. Configurer la méthode (application, SMS, email)
5. Se déconnecter
6. Se reconnecter avec les identifiants
7. Saisir le code de vérification

**Résultat attendu** :
- L'authentification à deux facteurs est activée avec succès
- Lors de la reconnexion, un code de vérification est demandé
- La connexion réussit après saisie du bon code

**Critères de validation** :
- Vérifier que le code est correctement envoyé/généré
- Vérifier que la connexion échoue avec un code incorrect
- Vérifier que les codes de secours fonctionnent en cas de besoin

### UT-006 : Gestion des sessions utilisateur

**Objectif** : Vérifier que le système permet de gérer les sessions utilisateur.

**Étapes de test** :
1. Se connecter avec un compte administrateur
2. Accéder au module "Sessions utilisateurs"
3. Visualiser les sessions actives
4. Sélectionner une session spécifique
5. Forcer la déconnexion de cette session

**Résultat attendu** :
- La liste des sessions actives s'affiche correctement
- La session sélectionnée est terminée avec succès
- L'utilisateur concerné est déconnecté immédiatement

**Critères de validation** :
- Vérifier que toutes les sessions actives sont affichées
- Vérifier que la déconnexion forcée est effective
- Vérifier que l'utilisateur reçoit une notification appropriée

## Dépendances
- Module Entreprise
- Module Rôles et permissions
- Module Notification
- Module Sécurité
