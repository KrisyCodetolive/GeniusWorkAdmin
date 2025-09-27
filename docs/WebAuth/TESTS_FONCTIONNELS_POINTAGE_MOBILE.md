# Tests Fonctionnels - Pointage Mobile avec WebAuthn

## Vue d'ensemble

Cette fiche décrit les tests fonctionnels à effectuer pour valider le système de pointage mobile avec authentification WebAuthn. Les tests couvrent l'ensemble du flux utilisateur, de la génération des QR codes jusqu'à l'enregistrement des pointages.

## Prérequis pour les tests

### Environnement de test
- [ ] Serveur Laravel configuré et fonctionnel
- [ ] Base de données avec données de test
- [ ] HTTPS activé (requis pour WebAuthn)
- [ ] Domaine configuré pour WebAuthn
- [ ] Navigateurs compatibles WebAuthn (Chrome, Firefox, Safari)

### Données de test requises
- [ ] Au moins 1 entreprise active
- [ ] Au moins 3 sites avec géolocalisation configurée
- [ ] Au moins 2 employés avec comptes Google
- [ ] Credentials WebAuthn enregistrés pour les employés de test

### Outils nécessaires
- [ ] Smartphone avec GPS et caméra
- [ ] Générateur de QR codes pour tests
- [ ] Outils de développement navigateur
- [ ] Simulateur de géolocalisation

---

## 1. Tests de génération et gestion des QR codes

### 1.1 Génération de QR code pour un site

**Objectif** : Vérifier la génération correcte des QR codes

**Prérequis** : Utilisateur administrateur connecté

**Étapes** :
1. Se connecter à l'interface d'administration
2. Naviguer vers la gestion des sites
3. Sélectionner un site actif
4. Cliquer sur "Générer QR Code"
5. Vérifier la génération du QR code

**Résultats attendus** :
- [ ] QR code généré avec succès
- [ ] Token unique créé
- [ ] Date d'expiration définie (24h par défaut)
- [ ] QR code scannable
- [ ] URL de pointage correcte dans le QR code

**Cas d'erreur à tester** :
- [ ] Site inexistant
- [ ] Site inactif
- [ ] Utilisateur non autorisé

---

### 1.2 Rafraîchissement de QR code

**Objectif** : Vérifier le rafraîchissement des QR codes

**Étapes** :
1. Générer un QR code pour un site
2. Noter le token généré
3. Rafraîchir le QR code
4. Vérifier le nouveau token

**Résultats attendus** :
- [ ] Nouveau token généré
- [ ] Ancien token invalidé
- [ ] Nouvelle date d'expiration
- [ ] Ancien QR code ne fonctionne plus

---

### 1.3 Expiration automatique des QR codes

**Objectif** : Vérifier l'expiration automatique après 24h

**Étapes** :
1. Générer un QR code
2. Modifier manuellement la date de génération (simulation)
3. Tenter de scanner le QR code expiré

**Résultats attendus** :
- [ ] QR code rejeté comme expiré
- [ ] Message d'erreur approprié
- [ ] Redirection vers page d'erreur

---

## 2. Tests de scan et validation des QR codes

### 2.1 Scan de QR code valide

**Objectif** : Vérifier le scan d'un QR code valide

**Prérequis** : QR code généré et valide

**Étapes** :
1. Ouvrir l'interface de scan sur mobile
2. Scanner le QR code valide
3. Vérifier la redirection

**Résultats attendus** :
- [ ] QR code détecté correctement
- [ ] Redirection vers la page de pointage
- [ ] Informations du site affichées
- [ ] Interface de pointage chargée

---

### 2.2 Scan de QR code invalide

**Objectif** : Tester la gestion des QR codes invalides

**Étapes** :
1. Scanner un QR code expiré
2. Scanner un QR code avec token incorrect
3. Scanner un QR code d'un autre système

**Résultats attendus** :
- [ ] Erreur détectée pour QR code expiré
- [ ] Erreur pour token incorrect
- [ ] Erreur pour QR code non reconnu
- [ ] Messages d'erreur clairs

---

### 2.3 Saisie manuelle de code

**Objectif** : Tester la saisie manuelle en cas de problème de scan

**Étapes** :
1. Accéder à l'option de saisie manuelle
2. Saisir un code valide
3. Saisir un code invalide

**Résultats attendus** :
- [ ] Code valide accepté
- [ ] Code invalide rejeté
- [ ] Validation en temps réel
- [ ] Messages d'aide appropriés

---

## 3. Tests d'authentification WebAuthn

### 3.1 Première authentification (enregistrement)

**Objectif** : Tester l'enregistrement initial WebAuthn

**Prérequis** : Employé sans credentials WebAuthn

**Étapes** :
1. Accéder à la page de pointage
2. Saisir l'email de l'employé
3. Suivre le processus d'enregistrement WebAuthn
4. Confirmer avec l'authentificateur

**Résultats attendus** :
- [ ] Options d'enregistrement générées
- [ ] Authentificateur sollicité (Touch ID, Face ID, etc.)
- [ ] Credential enregistré en base
- [ ] Authentification réussie
- [ ] Passage à l'étape suivante

---

### 3.2 Authentification avec credential existant

**Objectif** : Tester l'authentification avec un credential déjà enregistré

**Prérequis** : Employé avec credentials WebAuthn existants

**Étapes** :
1. Saisir l'email de l'employé
2. Déclencher l'authentification WebAuthn
3. Confirmer avec l'authentificateur

**Résultats attendus** :
- [ ] Options d'authentification générées
- [ ] Credential existant reconnu
- [ ] Authentification réussie
- [ ] Informations employé récupérées

---

### 3.3 Échec d'authentification WebAuthn

**Objectif** : Tester les cas d'échec d'authentification

**Étapes** :
1. Annuler l'authentification
2. Utiliser un mauvais authentificateur
3. Timeout de l'authentification

**Résultats attendus** :
- [ ] Erreur d'annulation gérée
- [ ] Erreur d'authentificateur incorrect
- [ ] Erreur de timeout
- [ ] Messages d'erreur clairs
- [ ] Possibilité de réessayer

---

## 4. Tests de géolocalisation

### 4.1 Géolocalisation dans le périmètre autorisé

**Objectif** : Vérifier l'acceptation d'une position valide

**Prérequis** : Site avec géofencing activé (rayon 100m)

**Étapes** :
1. Se positionner dans le rayon autorisé
2. Autoriser la géolocalisation
3. Procéder au pointage

**Résultats attendus** :
- [ ] Position GPS obtenue
- [ ] Distance calculée correctement
- [ ] Position acceptée (< 100m)
- [ ] Pointage autorisé

---

### 4.2 Géolocalisation hors périmètre

**Objectif** : Tester le rejet d'une position trop éloignée

**Étapes** :
1. Se positionner hors du rayon autorisé
2. Tenter le pointage

**Résultats attendus** :
- [ ] Position GPS obtenue
- [ ] Distance calculée (> 100m)
- [ ] Position rejetée
- [ ] Message d'erreur avec distance
- [ ] Pointage bloqué

---

### 4.3 Géolocalisation refusée par l'utilisateur

**Objectif** : Tester le cas où l'utilisateur refuse la géolocalisation

**Étapes** :
1. Refuser l'autorisation de géolocalisation
2. Tenter le pointage

**Résultats attendus** :
- [ ] Erreur de géolocalisation détectée
- [ ] Message d'aide affiché
- [ ] Instructions pour activer GPS
- [ ] Pointage bloqué

---

### 4.4 Site sans géofencing

**Objectif** : Tester un site sans restriction géographique

**Prérequis** : Site avec géofencing désactivé

**Étapes** :
1. Accéder au site sans géofencing
2. Procéder au pointage

**Résultats attendus** :
- [ ] Géolocalisation non requise
- [ ] Pointage autorisé sans vérification GPS
- [ ] Processus simplifié

---

## 5. Tests de pointage (entrée/sortie)

### 5.1 Premier pointage de la journée (entrée)

**Objectif** : Tester l'enregistrement d'une entrée

**Prérequis** : Employé sans pointage en cours

**Étapes** :
1. Compléter l'authentification
2. Valider la géolocalisation
3. Confirmer le pointage d'entrée

**Résultats attendus** :
- [ ] Pointage d'entrée enregistré
- [ ] Heure d'entrée correcte
- [ ] Statut calculé (à l'heure/en retard)
- [ ] Message de confirmation
- [ ] Redirection vers page de succès

---

### 5.2 Pointage de sortie

**Objectif** : Tester l'enregistrement d'une sortie

**Prérequis** : Employé avec pointage d'entrée en cours

**Étapes** :
1. Répéter le processus d'authentification
2. Confirmer le pointage de sortie

**Résultats attendus** :
- [ ] Pointage de sortie enregistré
- [ ] Temps de travail calculé
- [ ] Présence complétée
- [ ] Heures travaillées affichées

---

### 5.3 Double pointage (erreur)

**Objectif** : Tester la gestion des doubles pointages

**Étapes** :
1. Effectuer un pointage d'entrée
2. Tenter un second pointage d'entrée immédiat

**Résultats attendus** :
- [ ] Double pointage détecté
- [ ] Message d'erreur approprié
- [ ] Proposition de correction
- [ ] Aucun enregistrement en double

---

### 5.4 Pointage en retard

**Objectif** : Tester le calcul du retard

**Prérequis** : Horaires de travail définis pour l'employé

**Étapes** :
1. Pointer après l'heure prévue
2. Vérifier le calcul du retard

**Résultats attendus** :
- [ ] Retard calculé correctement
- [ ] Statut "en retard" assigné
- [ ] Minutes de retard enregistrées
- [ ] Notification du retard

---

## 6. Tests d'interface utilisateur mobile

### 6.1 Responsive design

**Objectif** : Vérifier l'adaptation mobile

**Étapes** :
1. Tester sur différentes tailles d'écran
2. Vérifier l'orientation portrait/paysage
3. Tester sur différents navigateurs mobiles

**Résultats attendus** :
- [ ] Interface adaptée aux mobiles
- [ ] Boutons facilement cliquables
- [ ] Texte lisible
- [ ] Navigation fluide

---

### 6.2 Accessibilité

**Objectif** : Vérifier l'accessibilité de l'interface

**Étapes** :
1. Tester avec lecteur d'écran
2. Vérifier la navigation au clavier
3. Contrôler les contrastes

**Résultats attendus** :
- [ ] Textes alternatifs présents
- [ ] Navigation clavier possible
- [ ] Contrastes suffisants
- [ ] Structure sémantique correcte

---

### 6.3 Performance

**Objectif** : Vérifier les performances sur mobile

**Étapes** :
1. Mesurer les temps de chargement
2. Tester sur connexion lente
3. Vérifier la consommation de données

**Résultats attendus** :
- [ ] Chargement < 3 secondes
- [ ] Fonctionnel sur 3G
- [ ] Consommation de données raisonnable
- [ ] Pas de blocages interface

---

## 7. Tests d'intégration et de bout en bout

### 7.1 Flux complet de pointage

**Objectif** : Tester le flux complet du début à la fin

**Étapes** :
1. Générer un QR code (admin)
2. Scanner le QR code (employé)
3. S'authentifier via WebAuthn
4. Valider la géolocalisation
5. Enregistrer le pointage
6. Vérifier en base de données

**Résultats attendus** :
- [ ] Flux complet sans erreur
- [ ] Données cohérentes en base
- [ ] Logs d'audit créés
- [ ] Notifications envoyées si configurées

---

### 7.2 Gestion des erreurs réseau

**Objectif** : Tester la robustesse face aux problèmes réseau

**Étapes** :
1. Simuler une perte de connexion
2. Tenter un pointage hors ligne
3. Rétablir la connexion

**Résultats attendus** :
- [ ] Erreur réseau détectée
- [ ] Message d'erreur approprié
- [ ] Possibilité de réessayer
- [ ] Pas de perte de données

---

### 7.3 Tests de charge

**Objectif** : Vérifier le comportement sous charge

**Étapes** :
1. Simuler plusieurs pointages simultanés
2. Tester avec de nombreux QR codes actifs
3. Vérifier les performances de l'API

**Résultats attendus** :
- [ ] Système stable sous charge
- [ ] Temps de réponse acceptables
- [ ] Pas de perte de données
- [ ] Gestion correcte de la concurrence

---

## 8. Tests de sécurité

### 8.1 Validation des tokens

**Objectif** : Vérifier la sécurité des tokens QR

**Étapes** :
1. Tenter d'utiliser un token expiré
2. Modifier un token valide
3. Réutiliser un token déjà utilisé

**Résultats attendus** :
- [ ] Token expiré rejeté
- [ ] Token modifié rejeté
- [ ] Réutilisation détectée et bloquée

---

### 8.2 Injection et manipulation

**Objectif** : Tester la résistance aux attaques

**Étapes** :
1. Tenter injection SQL dans les paramètres
2. Modifier les requêtes API
3. Tenter de contourner l'authentification

**Résultats attendus** :
- [ ] Injections bloquées
- [ ] Validation des paramètres
- [ ] Authentification obligatoire
- [ ] Logs de sécurité créés

---

## 9. Tests de régression

### 9.1 Fonctionnalités existantes

**Objectif** : Vérifier que les nouvelles fonctionnalités n'impactent pas l'existant

**Étapes** :
1. Tester les anciens systèmes de pointage
2. Vérifier les rapports existants
3. Contrôler les calculs de paie

**Résultats attendus** :
- [ ] Fonctionnalités existantes intactes
- [ ] Données cohérentes
- [ ] Pas de régression

---

## 10. Checklist de validation finale

### Avant mise en production

- [ ] Tous les tests fonctionnels passent
- [ ] Tests de sécurité validés
- [ ] Performance acceptable
- [ ] Documentation à jour
- [ ] Formation utilisateurs effectuée
- [ ] Plan de rollback préparé

### Critères d'acceptation

- [ ] QR codes générés et gérés correctement
- [ ] Authentification WebAuthn fonctionnelle
- [ ] Géolocalisation précise et sécurisée
- [ ] Pointages enregistrés correctement
- [ ] Interface mobile intuitive
- [ ] Sécurité renforcée
- [ ] Performance satisfaisante

---

## Rapport de tests

### Template de rapport

```markdown
# Rapport de Tests - Pointage Mobile WebAuthn

**Date** : [Date des tests]
**Testeur** : [Nom du testeur]
**Version** : [Version testée]
**Environnement** : [Environnement de test]

## Résumé exécutif
- Tests réalisés : X/Y
- Tests réussis : X
- Tests échoués : Y
- Bugs critiques : Z

## Détail des tests
[Pour chaque test : Statut, Commentaires, Captures d'écran]

## Bugs identifiés
[Liste des bugs avec priorité et description]

## Recommandations
[Recommandations pour la mise en production]
```

---

## Outils recommandés

### Tests manuels
- **Navigateurs** : Chrome, Firefox, Safari (mobile)
- **Appareils** : iPhone, Android (différentes versions)
- **Outils dev** : DevTools, Network tab, Console

### Tests automatisés
- **Laravel Dusk** : Tests E2E
- **PHPUnit** : Tests unitaires
- **Postman** : Tests API
- **Lighthouse** : Performance et accessibilité

### Monitoring
- **Laravel Telescope** : Debug et monitoring
- **Logs** : Surveillance des erreurs
- **Métriques** : Performance et utilisation

---

Cette fiche de tests fonctionnels couvre l'ensemble des aspects critiques du système de pointage mobile. Elle doit être adaptée selon les spécificités de votre environnement et complétée par des tests automatisés pour une couverture optimale.
