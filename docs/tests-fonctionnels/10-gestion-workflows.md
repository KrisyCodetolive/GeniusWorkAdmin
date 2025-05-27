# Fiche de Tests Fonctionnels : Gestion des Workflows

## Description du Module
Le module de gestion des workflows permet de configurer et gérer les processus d'approbation pour différentes opérations comme les demandes de congés, les permutations, les demandes de matériel, etc. Il définit les étapes, les approbateurs et les conditions de validation.

## Prérequis
- Structure organisationnelle définie (départements, hiérarchie)
- Utilisateurs avec différents rôles créés
- Types de demandes configurés

## Cas de Tests

### WF-001 : Création d'un workflow d'approbation de congés

**Objectif** : Vérifier qu'un workflow d'approbation pour les congés peut être créé et configuré.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Accéder au module "Workflows"
3. Cliquer sur "Nouveau workflow"
4. Sélectionner le type "Approbation de congés"
5. Configurer les étapes du workflow :
   - Étape 1 : Approbation par le responsable direct
   - Étape 2 : Approbation par le responsable RH
6. Définir les conditions de validation pour chaque étape
7. Activer le workflow

**Résultat attendu** :
- Le workflow est créé avec succès
- Les étapes sont correctement configurées
- Le workflow est actif et prêt à être utilisé

**Critères de validation** :
- Vérifier que les approbateurs sont correctement définis
- Vérifier que l'ordre des étapes est respecté
- Vérifier que les conditions de validation fonctionnent

### WF-002 : Test d'un workflow de congés

**Objectif** : Vérifier que le workflow d'approbation de congés fonctionne correctement de bout en bout.

**Étapes de test** :
1. Se connecter avec un compte employé
2. Créer une demande de congé
3. Soumettre la demande
4. Se connecter avec le compte du responsable direct
5. Approuver la demande
6. Se connecter avec le compte du responsable RH
7. Approuver la demande

**Résultat attendu** :
- La demande passe par toutes les étapes du workflow
- Les notifications sont envoyées aux approbateurs
- La demande est finalement approuvée et le statut est mis à jour

**Critères de validation** :
- Vérifier que chaque approbateur ne voit que les demandes qui le concernent
- Vérifier que le statut de la demande est mis à jour à chaque étape
- Vérifier que l'employé est notifié de l'avancement de sa demande

### WF-003 : Configuration d'un workflow avec conditions

**Objectif** : Vérifier qu'un workflow peut être configuré avec des conditions spécifiques.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Accéder au module "Workflows"
3. Créer un nouveau workflow pour les heures supplémentaires
4. Configurer une condition : "Si durée > 8 heures, approbation directeur requise"
5. Configurer les étapes avec cette condition
6. Activer le workflow

**Résultat attendu** :
- Le workflow avec conditions est créé avec succès
- Les conditions sont correctement enregistrées
- Le workflow est actif et prêt à être utilisé

**Critères de validation** :
- Vérifier que les conditions sont correctement évaluées
- Vérifier que le chemin d'approbation change en fonction des conditions
- Vérifier que les approbateurs sont notifiés selon les conditions

### WF-004 : Test d'un workflow avec délégation

**Objectif** : Vérifier que la délégation d'approbation fonctionne correctement dans un workflow.

**Étapes de test** :
1. Se connecter avec un compte approbateur
2. Configurer une délégation d'approbation pour une période donnée
3. Se déconnecter
4. Se connecter avec un compte employé
5. Soumettre une demande nécessitant l'approbation du premier approbateur
6. Vérifier que la demande est dirigée vers le délégué

**Résultat attendu** :
- La délégation est configurée avec succès
- La demande est automatiquement dirigée vers le délégué
- Le délégué peut approuver la demande

**Critères de validation** :
- Vérifier que la délégation est active uniquement pendant la période définie
- Vérifier que l'approbateur original est informé de l'action du délégué
- Vérifier que l'historique d'approbation mentionne la délégation

### WF-005 : Modification d'un workflow existant

**Objectif** : Vérifier qu'un workflow existant peut être modifié sans affecter les demandes en cours.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Accéder au module "Workflows"
3. Sélectionner un workflow existant
4. Modifier une étape ou ajouter une nouvelle étape
5. Enregistrer les modifications

**Résultat attendu** :
- Les modifications sont enregistrées avec succès
- Les demandes déjà en cours suivent l'ancien workflow
- Les nouvelles demandes suivent le workflow modifié

**Critères de validation** :
- Vérifier que les demandes en cours ne sont pas perturbées
- Vérifier que les nouvelles demandes suivent le nouveau chemin
- Vérifier que l'historique des versions du workflow est conservé

### WF-006 : Rapports sur les workflows

**Objectif** : Vérifier que des rapports sur l'efficacité des workflows peuvent être générés.

**Étapes de test** :
1. Se connecter avec un compte administrateur d'entreprise
2. Accéder au module "Rapports de workflow"
3. Définir la période d'analyse
4. Sélectionner le type de workflow à analyser
5. Générer le rapport

**Résultat attendu** :
- Le rapport est généré avec succès
- Le rapport contient des statistiques sur les temps d'approbation, les taux d'approbation/rejet
- Des graphiques illustrent les performances du workflow

**Critères de validation** :
- Vérifier que les temps moyens d'approbation sont correctement calculés
- Vérifier que les goulots d'étranglement sont identifiés
- Vérifier que les recommandations d'optimisation sont pertinentes

## Dépendances
- Module Utilisateur
- Module Notification
- Module Congés/Demandes
- Module Rapports
