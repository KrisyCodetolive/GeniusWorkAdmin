# Fiche de Tests Fonctionnels : Gestion des Visites

## Description du Module
Le module de gestion des visites permet d'enregistrer, suivre et gérer les visiteurs externes dans les locaux de l'entreprise. Il gère les rendez-vous, les badges visiteurs, les enregistrements d'entrée/sortie et les rapports de visite.

## Prérequis
- Sites configurés dans le système
- Utilisateurs avec rôle d'accueil/sécurité créés
- Méthodes d'identification configurées

## Cas de Tests

### VS-001 : Enregistrement d'un nouveau visiteur

**Objectif** : Vérifier qu'un nouveau visiteur peut être enregistré dans le système.

**Étapes de test** :
1. Se connecter avec un compte accueil/sécurité
2. Accéder au module "Visiteurs"
3. Cliquer sur "Nouveau visiteur"
4. Remplir les informations du visiteur :
   - Nom et prénom
   - Société/Organisation
   - Pièce d'identité
   - Photo (si nécessaire)
   - Coordonnées (téléphone, email)
5. Enregistrer le visiteur

**Résultat attendu** :
- Le visiteur est créé avec succès
- Un message de confirmation s'affiche
- Le visiteur apparaît dans la base de données des visiteurs

**Critères de validation** :
- Vérifier que toutes les informations sont correctement enregistrées
- Vérifier que la photo est correctement stockée si fournie
- Vérifier que le visiteur peut être retrouvé par recherche

### VS-002 : Planification d'une visite

**Objectif** : Vérifier qu'une visite peut être planifiée à l'avance.

**Étapes de test** :
1. Se connecter avec un compte utilisateur autorisé
2. Accéder au module "Visites"
3. Cliquer sur "Planifier une visite"
4. Remplir les informations de la visite :
   - Sélectionner un visiteur existant ou créer un nouveau
   - Date et heure prévues
   - Durée estimée
   - Motif de la visite
   - Personne à rencontrer
   - Site/Bâtiment/Salle
5. Enregistrer la visite planifiée

**Résultat attendu** :
- La visite est planifiée avec succès
- Une notification est envoyée à la personne à rencontrer
- La visite apparaît dans le calendrier des visites

**Critères de validation** :
- Vérifier que la planification est correctement enregistrée
- Vérifier que les notifications sont envoyées aux personnes concernées
- Vérifier que la visite est visible dans le planning du jour prévu

### VS-003 : Enregistrement d'une entrée de visiteur

**Objectif** : Vérifier que l'entrée d'un visiteur peut être enregistrée.

**Étapes de test** :
1. Se connecter avec un compte accueil/sécurité
2. Accéder au module "Visites du jour"
3. Rechercher une visite planifiée ou un visiteur
4. Cliquer sur "Enregistrer l'entrée"
5. Vérifier les informations du visiteur
6. Attribuer un badge visiteur
7. Confirmer l'entrée

**Résultat attendu** :
- L'entrée est enregistrée avec succès avec l'horodatage
- Un badge visiteur est attribué et imprimé si configuré
- Une notification est envoyée à la personne à rencontrer
- Le statut de la visite passe à "En cours"

**Critères de validation** :
- Vérifier que l'heure d'entrée est correctement enregistrée
- Vérifier que le badge est correctement attribué
- Vérifier que la personne à rencontrer est notifiée de l'arrivée

### VS-004 : Enregistrement d'une sortie de visiteur

**Objectif** : Vérifier que la sortie d'un visiteur peut être enregistrée.

**Étapes de test** :
1. Se connecter avec un compte accueil/sécurité
2. Accéder au module "Visites en cours"
3. Sélectionner un visiteur présent
4. Cliquer sur "Enregistrer la sortie"
5. Récupérer le badge visiteur
6. Confirmer la sortie

**Résultat attendu** :
- La sortie est enregistrée avec succès avec l'horodatage
- Le badge visiteur est marqué comme retourné
- Le statut de la visite passe à "Terminée"
- La durée totale de la visite est calculée

**Critères de validation** :
- Vérifier que l'heure de sortie est correctement enregistrée
- Vérifier que le badge est marqué comme disponible pour réutilisation
- Vérifier que la durée de la visite est correctement calculée

### VS-005 : Génération d'un rapport de visites

**Objectif** : Vérifier que des rapports sur les visites peuvent être générés.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou sécurité
2. Accéder au module "Rapports de visites"
3. Définir la période d'analyse
4. Sélectionner le type de rapport (détaillé, synthétique)
5. Filtrer par site, motif ou personne visitée si nécessaire
6. Générer le rapport

**Résultat attendu** :
- Le rapport est généré avec succès
- Le rapport contient les informations demandées
- Le rapport peut être téléchargé ou imprimé

**Critères de validation** :
- Vérifier que toutes les visites de la période sont incluses
- Vérifier l'exactitude des données (visiteurs, durées, motifs)
- Vérifier que les statistiques sont correctement calculées

### VS-006 : Gestion des visiteurs récurrents

**Objectif** : Vérifier que les visiteurs récurrents peuvent être gérés efficacement.

**Étapes de test** :
1. Se connecter avec un compte accueil/sécurité
2. Accéder au module "Visiteurs récurrents"
3. Sélectionner un visiteur existant
4. Attribuer le statut de "Visiteur récurrent"
5. Configurer les paramètres spécifiques :
   - Période de validité
   - Sites autorisés
   - Procédure d'accès simplifiée
6. Enregistrer la configuration

**Résultat attendu** :
- Le visiteur est marqué comme récurrent
- Les paramètres spécifiques sont enregistrés
- La procédure d'entrée est simplifiée lors des prochaines visites

**Critères de validation** :
- Vérifier que le statut de visiteur récurrent est correctement appliqué
- Vérifier que les accès sont limités aux sites autorisés
- Vérifier que la procédure simplifiée fonctionne correctement

## Dépendances
- Module Site
- Module Notification
- Module Impression de badges
- Module Rapports
