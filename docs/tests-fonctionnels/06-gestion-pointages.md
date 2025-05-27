# Fiche de Tests Fonctionnels : Gestion des Pointages

## Description du Module
Le module de gestion des pointages permet d'enregistrer et de gérer les entrées et sorties des employés via différentes méthodes (biométrique, QR code, web, etc.). Il constitue la base du suivi de présence et alimente les rapports de présence.

## Prérequis
- Employeurs actifs dans le système
- Méthodes de pointage configurées
- Appareils biométriques enregistrés (si utilisés)
- Plages horaires définies

## Cas de Tests

### PT-001 : Pointage via interface web

**Objectif** : Vérifier que les employés peuvent pointer via l'interface web.

**Étapes de test** :
1. Se connecter avec un compte employé
2. Accéder à l'interface de pointage web
3. Effectuer un pointage d'entrée
4. Vérifier la confirmation du pointage
5. Attendre quelques heures
6. Effectuer un pointage de sortie
7. Vérifier la confirmation du pointage

**Résultat attendu** :
- Les pointages d'entrée et de sortie sont enregistrés avec succès
- Des confirmations visuelles s'affichent à chaque pointage
- Les données de pointage sont visibles dans l'historique de l'employé

**Critères de validation** :
- Vérifier que les heures exactes sont enregistrées
- Vérifier que le système détecte correctement s'il s'agit d'une entrée ou d'une sortie
- Vérifier que les retards sont correctement signalés selon les plages horaires

### PT-002 : Pointage via appareil biométrique

**Objectif** : Vérifier que les pointages via appareils biométriques sont correctement enregistrés et synchronisés.

**Étapes de test** :
1. Configurer un appareil biométrique de test
2. Enregistrer les empreintes d'un employé test
3. Effectuer un pointage d'entrée sur l'appareil
4. Vérifier la synchronisation avec le système central
5. Attendre quelques heures
6. Effectuer un pointage de sortie sur l'appareil
7. Vérifier la synchronisation avec le système central

**Résultat attendu** :
- Les pointages sont correctement enregistrés sur l'appareil
- Les données sont synchronisées avec le système central
- Les pointages apparaissent dans l'historique de l'employé

**Critères de validation** :
- Vérifier que la synchronisation fonctionne en temps réel ou selon la fréquence configurée
- Vérifier que les données biométriques sont sécurisées
- Vérifier que le système gère correctement les cas de connexion intermittente

### PT-003 : Pointage via QR Code

**Objectif** : Vérifier que le pointage via QR Code fonctionne correctement.

**Étapes de test** :
1. Générer un QR Code pour un employé test
2. Accéder à l'interface de scan QR Code
3. Scanner le QR Code pour un pointage d'entrée
4. Vérifier la confirmation du pointage
5. Attendre quelques heures
6. Scanner à nouveau le QR Code pour un pointage de sortie
7. Vérifier la confirmation du pointage

**Résultat attendu** :
- Les pointages d'entrée et de sortie sont enregistrés avec succès
- Des confirmations visuelles s'affichent à chaque scan
- Les données de pointage sont visibles dans l'historique de l'employé

**Critères de validation** :
- Vérifier que le QR Code est correctement chiffré et sécurisé
- Vérifier que le système détecte automatiquement s'il s'agit d'une entrée ou d'une sortie
- Vérifier que les informations de géolocalisation sont enregistrées si configurées

### PT-004 : Gestion des pointages multiples

**Objectif** : Vérifier que le système gère correctement les pointages multiples dans une même journée.

**Étapes de test** :
1. Configurer une plage horaire avec pause déjeuner
2. Se connecter avec un compte employé
3. Effectuer un pointage d'entrée le matin
4. Effectuer un pointage de sortie pour la pause déjeuner
5. Effectuer un pointage d'entrée après la pause
6. Effectuer un pointage de sortie en fin de journée

**Résultat attendu** :
- Les quatre pointages sont enregistrés correctement
- Le système calcule correctement le temps de travail (excluant la pause)
- Les données sont correctement reflétées dans le rapport de présence

**Critères de validation** :
- Vérifier que le système identifie correctement la nature de chaque pointage
- Vérifier que le calcul du temps de travail exclut les pauses
- Vérifier que les anomalies (oubli de pointage) sont correctement détectées

### PT-005 : Correction manuelle de pointage

**Objectif** : Vérifier qu'un superviseur peut corriger manuellement les pointages.

**Étapes de test** :
1. Se connecter avec un compte superviseur
2. Accéder au module "Gestion des pointages"
3. Rechercher un employé spécifique
4. Sélectionner une journée avec des pointages
5. Modifier l'heure d'un pointage existant
6. Ajouter un pointage manquant
7. Enregistrer les modifications

**Résultat attendu** :
- Les modifications sont enregistrées avec succès
- Un historique des corrections est conservé
- Les données de présence sont recalculées en fonction des corrections

**Critères de validation** :
- Vérifier que les modifications sont clairement identifiées comme des corrections manuelles
- Vérifier que l'historique contient l'auteur et le motif de la correction
- Vérifier que les calculs de temps de travail sont mis à jour

### PT-006 : Export des données de pointage

**Objectif** : Vérifier que les données de pointage peuvent être exportées pour analyse.

**Étapes de test** :
1. Se connecter avec un compte administrateur ou RH
2. Accéder au module "Pointages"
3. Définir une période d'export
4. Sélectionner les employés ou départements concernés
5. Lancer l'export des données

**Résultat attendu** :
- Un fichier d'export est généré avec succès
- Le fichier contient toutes les données de pointage pour la période
- Le format du fichier est conforme aux spécifications

**Critères de validation** :
- Vérifier que toutes les données sont présentes dans l'export
- Vérifier que le format permet une analyse facile (Excel, CSV)
- Vérifier que les données sensibles sont correctement traitées

## Dépendances
- Module Employeur
- Module Présence
- Module Appareils biométriques
- Module Plages horaires
