# Fiche de Tests Fonctionnels : Gestion des Employeurs

## Description du Module
Le module de gestion des employeurs permet de gérer les informations relatives aux employés de l'entreprise, incluant leurs données personnelles, professionnelles et administratives. Il constitue la base de données RH du système.

## Prérequis
- Accès administrateur ou RH au système
- Entreprise configurée dans le système
- Départements et postes créés

## Cas de Tests

### EM-001 : Création d'un nouvel employeur

**Objectif** : Vérifier qu'un nouvel employeur peut être créé avec toutes les informations requises.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Employeurs"
3. Cliquer sur "Ajouter un employeur"
4. Remplir tous les champs obligatoires :
   - Informations personnelles (nom, prénom, date de naissance, etc.)
   - Informations professionnelles (matricule, département, poste, etc.)
   - Informations de contact (téléphone, email, adresse)
5. Cliquer sur "Enregistrer"

**Résultat attendu** :
- L'employeur est créé avec succès
- Un message de confirmation s'affiche
- L'employeur apparaît dans la liste des employeurs

**Critères de validation** :
- Vérifier que toutes les informations saisies sont correctement enregistrées
- Vérifier que le matricule est unique
- Vérifier que l'employeur est associé au bon département

### EM-002 : Modification des informations d'un employeur

**Objectif** : Vérifier qu'il est possible de modifier les informations d'un employeur existant.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Employeurs"
3. Rechercher un employeur existant
4. Cliquer sur "Modifier"
5. Modifier plusieurs champs (ex: numéro de téléphone, adresse, poste)
6. Cliquer sur "Enregistrer"

**Résultat attendu** :
- Les modifications sont enregistrées avec succès
- Un message de confirmation s'affiche
- Les nouvelles informations sont visibles dans la fiche de l'employeur

**Critères de validation** :
- Vérifier que seules les informations modifiées ont été mises à jour
- Vérifier que l'historique des modifications est enregistré

### EM-003 : Désactivation d'un employeur

**Objectif** : Vérifier qu'il est possible de désactiver un employeur (fin de contrat, départ).

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Employeurs"
3. Rechercher un employeur existant
4. Cliquer sur "Désactiver"
5. Renseigner la date et le motif de désactivation
6. Confirmer la désactivation

**Résultat attendu** :
- L'employeur est marqué comme inactif
- La date de fin et le motif sont enregistrés
- L'employeur n'apparaît plus dans la liste des employeurs actifs

**Critères de validation** :
- Vérifier que l'employeur est bien marqué comme inactif
- Vérifier que ses accès au système sont révoqués
- Vérifier qu'il apparaît dans la liste des employeurs inactifs

### EM-004 : Génération de la carte d'employé

**Objectif** : Vérifier qu'il est possible de générer une carte d'employé.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Employeurs"
3. Rechercher un employeur existant
4. Cliquer sur "Générer carte d'employé"
5. Vérifier l'aperçu de la carte
6. Confirmer la génération

**Résultat attendu** :
- La carte d'employé est générée au format PDF
- La carte contient les informations correctes (nom, photo, matricule, QR code)
- Le fichier peut être téléchargé ou imprimé

**Critères de validation** :
- Vérifier que le QR code est fonctionnel et contient les bonnes informations
- Vérifier que la mise en page est correcte
- Vérifier que la photo est bien intégrée si disponible

### EM-005 : Import en masse d'employeurs

**Objectif** : Vérifier qu'il est possible d'importer plusieurs employeurs à partir d'un fichier Excel.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Employeurs"
3. Cliquer sur "Importer"
4. Télécharger le modèle de fichier Excel
5. Remplir le modèle avec des données de test
6. Téléverser le fichier rempli
7. Confirmer l'import

**Résultat attendu** :
- Les employeurs sont importés avec succès
- Un rapport d'import s'affiche (nombre d'employeurs importés, erreurs éventuelles)
- Les nouveaux employeurs apparaissent dans la liste

**Critères de validation** :
- Vérifier que toutes les données sont correctement importées
- Vérifier la gestion des erreurs (doublons, données manquantes)
- Vérifier que les relations (département, poste) sont correctement établies

## Dépendances
- Module Entreprise
- Module Département
- Module Utilisateur (pour les accès)
