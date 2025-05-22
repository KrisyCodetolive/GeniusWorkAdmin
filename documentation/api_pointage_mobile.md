# Documentation API Pointage Mobile - GENIUS WORK

## Table des matières
1. [Introduction](#introduction)
2. [Architecture](#architecture)
3. [Services](#services)
   - [MobilePointageService](#mobilepointageservice)
4. [Contrôleurs](#contrôleurs)
   - [MobilePointageController](#mobilepointagecontroller)
5. [Routes API](#routes-api)
6. [Intégration avec l'Application Mobile](#intégration-avec-lapplication-mobile)
7. [Validation de Géolocalisation](#validation-de-géolocalisation)
8. [Considérations Techniques](#considérations-techniques)

## Introduction

L'API de pointage mobile de GENIUS WORK permet aux employés d'enregistrer leur présence via l'application mobile. Cette API gère l'ensemble du processus de pointage, y compris la validation de la géolocalisation, le calcul des heures travaillées, des retards et des heures supplémentaires.

L'API est conçue pour fonctionner de manière sécurisée et efficace, en s'intégrant parfaitement avec l'application mobile Flutter tout en maintenant la cohérence avec le système de pointage web existant.

## Architecture

L'architecture de l'API de pointage mobile s'articule autour de trois composants principaux :

1. **Services** : Contiennent la logique métier pour traiter les pointages et interagir avec la base de données.
2. **Contrôleurs** : Gèrent les requêtes HTTP, valident les données entrantes et renvoient les réponses appropriées.
3. **Routes** : Définissent les points d'entrée de l'API.

Cette architecture respecte les principes SOLID et facilite la maintenance et l'évolution du système.

## Services

### MobilePointageService

Le `MobilePointageService` est responsable de la logique métier liée aux pointages mobiles. Il s'appuie sur le `WebPointageService` existant pour certaines fonctionnalités communes.

#### Méthodes principales

| Méthode | Description | Paramètres | Retour |
|---------|-------------|------------|--------|
| `processPointage` | Traite une demande de pointage mobile | `Request $request` | `array` |
| `getHistorique` | Récupère l'historique des pointages | `Request $request` | `array` |
| `getStatistiques` | Récupère les statistiques de pointage | `Request $request` | `array` |
| `formatDuree` | Formate une durée en minutes en format heures:minutes | `int $minutes` | `string` |

#### Exemple d'utilisation

```php
// Dans un contrôleur
$mobilePointageService = new MobilePointageService();
$result = $mobilePointageService->processPointage($request);

// Résultat
[
    'status' => 'success',
    'data' => [
        'id' => 123,
        'employee' => 'John Doe',
        'type' => 'entree',
        'time' => '08:30:00',
        'date' => '2023-03-26',
        'site' => 'Siège social',
        'message' => 'Bonjour John, bonne journée !',
        'retard' => false,
        'minutes_retard' => 0,
        'minutes_travaillees' => 0,
        'minutes_supplementaires' => 0,
        'minutes_pause' => 0,
        'distance' => 25
    ]
]
```

## Contrôleurs

### MobilePointageController

Le `MobilePointageController` gère les requêtes HTTP liées aux pointages mobiles. Il valide les données entrantes et utilise le `MobilePointageService` pour traiter les demandes.

#### Méthodes principales

| Méthode | Description | Route | Méthode HTTP |
|---------|-------------|-------|-------------|
| `enregistrerPointage` | Enregistre un pointage | `/api/pointage/mobile` | POST |
| `getHistorique` | Récupère l'historique des pointages | `/api/pointage/historique` | GET |
| `getStatistiques` | Récupère les statistiques de pointage | `/api/pointage/statistiques` | GET |
| `verifierAutorisation` | Vérifie si un employé est autorisé à pointer | `/api/pointage/verifier-autorisation` | POST |

## Routes API

Les routes suivantes sont disponibles pour l'API de pointage mobile :

| Route | Méthode | Description | Paramètres requis |
|-------|---------|-------------|-------------------|
| `/api/pointage/mobile` | POST | Enregistre un pointage | `idno`, `token`, `lat`, `lng`, `type` (optionnel), `isPause` (optionnel) |
| `/api/pointage/historique` | GET | Récupère l'historique des pointages | `idno`, `date_debut` (optionnel), `date_fin` (optionnel), `limit` (optionnel) |
| `/api/pointage/statistiques` | GET | Récupère les statistiques de pointage | `idno`, `date_debut` (optionnel), `date_fin` (optionnel) |
| `/api/pointage/verifier-autorisation` | POST | Vérifie si un employé est autorisé à pointer | `idno`, `token`, `lat` (optionnel), `lng` (optionnel) |
| `/api/pointage/site-coordinates` | POST | Récupère les coordonnées du site associé à un employé pour la validation de géolocalisation | `idno`, `token` |
| `/api/pointage/verifier-position` | POST | Vérifie si la position actuelle de l'employé est dans la zone autorisée pour le pointage | `idno`, `token`, `lat`, `lng` |

## Endpoints API

### 1. Enregistrer un pointage

**Endpoint:** `/api/pointage/mobile`

**Méthode:** POST

**Description:** Enregistre un nouveau pointage pour un employé.

**Paramètres:**

| Nom | Type | Requis | Description |
|-----|------|--------|-------------|
| idno | string | Oui | Identifiant unique de l'employé (QR code secret) |
| token | string | Oui | Token d'authentification |
| lat | numeric | Oui | Latitude de la position actuelle |
| lng | numeric | Oui | Longitude de la position actuelle |
| type | string | Non | Type de pointage (entrée ou sortie) |
| isPause | boolean | Non | Indique si le pointage est une pause |

### 2. Récupérer l'historique des pointages

**Endpoint:** `/api/pointage/historique`

**Méthode:** GET

**Description:** Récupère l'historique des pointages pour un employé.

**Paramètres:**

| Nom | Type | Requis | Description |
|-----|------|--------|-------------|
| idno | string | Oui | Identifiant unique de l'employé (QR code secret) |
| date_debut | date | Non | Date de début pour l'historique |
| date_fin | date | Non | Date de fin pour l'historique |
| limit | integer | Non | Nombre maximum de pointages à récupérer |

### 3. Récupérer les statistiques de pointage

**Endpoint:** `/api/pointage/statistiques`

**Méthode:** GET

**Description:** Récupère les statistiques de pointage pour un employé.

**Paramètres:**

| Nom | Type | Requis | Description |
|-----|------|--------|-------------|
| idno | string | Oui | Identifiant unique de l'employé (QR code secret) |
| date_debut | date | Non | Date de début pour les statistiques |
| date_fin | date | Non | Date de fin pour les statistiques |

### 4. Vérifier l'autorisation de pointage

**Endpoint:** `/api/pointage/verifier-autorisation`

**Méthode:** POST

**Description:** Vérifie si un employé est autorisé à pointer.

**Paramètres:**

| Nom | Type | Requis | Description |
|-----|------|--------|-------------|
| idno | string | Oui | Identifiant unique de l'employé (QR code secret) |
| token | string | Oui | Token d'authentification |
| lat | numeric | Non | Latitude de la position actuelle |
| lng | numeric | Non | Longitude de la position actuelle |

### 5. Récupérer les coordonnées du site

**Endpoint:** `/api/pointage/site-coordinates`

**Méthode:** POST

**Description:** Récupère les coordonnées du site associé à un employé pour la validation de géolocalisation.

**Paramètres:**

| Nom | Type | Requis | Description |
|-----|------|--------|-------------|
| idno | string | Oui | Identifiant unique de l'employé (QR code secret) |
| token | string | Oui | Token d'authentification |

**Exemple de requête:**
```json
{
  "idno": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

**Exemple de réponse:**
```json
{
  "status": "success",
  "site": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "nom": "Siège Social",
    "adresse": "123 Rue Principale, Abidjan, Côte d'Ivoire",
    "latitude": 5.3484235,
    "longitude": -4.0123600,
    "rayon_geofencing": 100,
    "has_geofencing": true
  }
}
```

**Codes de statut:**
- 200 OK: Les coordonnées ont été récupérées avec succès
- 404 Not Found: Employé non trouvé ou aucune coordonnée disponible
- 422 Unprocessable Entity: Paramètres invalides

### 6. Vérifier la position

**Endpoint:** `/api/pointage/verifier-position`

**Méthode:** POST

**Description:** Vérifie si la position actuelle de l'employé est dans la zone autorisée pour le pointage.

**Paramètres:**

| Nom | Type | Requis | Description |
|-----|------|--------|-------------|
| idno | string | Oui | Identifiant unique de l'employé (QR code secret) |
| token | string | Oui | Token d'authentification |
| lat | numeric | Oui | Latitude de la position actuelle |
| lng | numeric | Oui | Longitude de la position actuelle |

**Exemple de requête:**
```json
{
  "idno": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "lat": 5.3482000,
  "lng": -4.0121500
}
```

**Exemple de réponse:**
```json
{
  "status": "success",
  "in_zone": true,
  "distance": 25,
  "rayon_maximum": 100,
  "message": "Position valide dans la zone autorisée"
}
```

**Codes de statut:**
- 200 OK: La vérification a été effectuée avec succès
- 404 Not Found: Employé non trouvé
- 422 Unprocessable Entity: Paramètres invalides

## Intégration avec l'Application Mobile

L'API est conçue pour s'intégrer parfaitement avec l'application mobile Flutter. Voici comment l'application mobile interagit avec l'API :

### Enregistrement d'un pointage

```dart
// Exemple d'appel API depuis le MobilePointageService Flutter
Future<Map<String, dynamic>> enregistrerPointage({
  required String type,
  bool isPause = false,
}) async {
  try {
    // Obtenir la position actuelle
    final position = await LocationService.getCurrentLocation();
    
    if (position == null) {
      return {
        'status': 'error',
        'message': 'Impossible d\'obtenir votre position'
      };
    }
    
    // Construire les données à envoyer
    final Map<String, dynamic> data = {
      'idno': employeurQrCode,
      'token': config['siteToken'] ?? 'mobile_app',
      'lat': position.latitude,
      'lng': position.longitude,
      'methode_pointage': 'mobile_app',
      'isPause': isPause,
      'type': type
    };
    
    // Envoyer la requête au serveur
    final response = await http.post(
      Uri.parse('$_baseUrl$_pointageEndpoint'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(data),
    );
    
    // Traiter la réponse
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      return {
        'status': 'error',
        'message': 'Erreur serveur: ${response.statusCode}'
      };
    }
  } catch (e) {
    return {
      'status': 'error',
      'message': 'Exception: $e'
    };
  }
}
```

### Récupération de l'historique des pointages

```dart
// Exemple d'appel API pour récupérer l'historique
Future<Map<String, dynamic>> getHistoriquePointages({
  DateTime? dateDebut,
  DateTime? dateFin,
  int limit = 50,
}) async {
  try {
    // Formater les dates
    final DateFormat dateFormat = DateFormat('yyyy-MM-dd');
    final String dateDebutStr = dateDebut != null ? dateFormat.format(dateDebut) : '';
    final String dateFinStr = dateFin != null ? dateFormat.format(dateFin) : '';
    
    // Construire les paramètres de la requête
    final Map<String, String> queryParams = {
      'idno': employeurQrCode,
      'limit': limit.toString(),
    };
    
    if (dateDebutStr.isNotEmpty) {
      queryParams['date_debut'] = dateDebutStr;
    }
    
    if (dateFinStr.isNotEmpty) {
      queryParams['date_fin'] = dateFinStr;
    }
    
    // Envoyer la requête au serveur
    final response = await http.get(
      Uri.parse('$_baseUrl$_historiqueEndpoint').replace(queryParameters: queryParams),
      headers: {
        'Accept': 'application/json',
      },
    );
    
    // Traiter la réponse
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      return {
        'status': 'error',
        'message': 'Erreur serveur: ${response.statusCode}'
      };
    }
  } catch (e) {
    return {
      'status': 'error',
      'message': 'Exception: $e'
    };
  }
}
```

### Vérification d'autorisation

```dart
// Exemple d'appel API pour vérifier l'autorisation de pointage
Future<Map<String, dynamic>> verifierAutorisation() async {
  try {
    // Obtenir la position actuelle
    final position = await LocationService.getCurrentLocation();
    
    if (position == null) {
      return {
        'status': 'error',
        'message': 'Impossible d\'obtenir votre position'
      };
    }
    
    // Construire les données à envoyer
    final Map<String, dynamic> data = {
      'idno': employeurQrCode,
      'token': config['siteToken'] ?? 'mobile_app',
      'lat': position.latitude,
      'lng': position.longitude,
    };
    
    // Envoyer la requête au serveur
    final response = await http.post(
      Uri.parse('$_baseUrl$_verifierAutorisationEndpoint'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(data),
    );
    
    // Traiter la réponse
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      return {
        'status': 'error',
        'message': 'Erreur serveur: ${response.statusCode}'
      };
    }
  } catch (e) {
    return {
      'status': 'error',
      'message': 'Exception: $e'
    };
  }
}
```

## Validation de Géolocalisation

L'API intègre une validation de géolocalisation pour s'assurer que les employés sont physiquement présents sur le site lors du pointage.

### Processus de validation

1. L'application mobile récupère la position actuelle de l'utilisateur via le `LocationService`
2. Les coordonnées sont envoyées à l'API lors de la demande de pointage
3. L'API calcule la distance entre l'employé et le site via la formule de Haversine
4. Si la distance est supérieure au rayon autorisé, le pointage est refusé

### Configuration du rayon

Chaque site peut avoir son propre rayon de géofencing configuré dans la base de données. Si aucun rayon n'est défini, une valeur par défaut de 100 mètres est utilisée.

### Fonctionnement en mode hors ligne

L'application mobile peut également valider la position en mode hors ligne :
1. Les coordonnées du site sont stockées localement via `ConfigService`
2. Le `LocationService` calcule la distance par rapport au site
3. Si l'utilisateur est dans la zone autorisée, le pointage est enregistré localement
4. Les pointages sont synchronisés avec le serveur lorsque la connexion est rétablie

## Considérations Techniques

### Sécurité

L'API utilise plusieurs mécanismes pour garantir la sécurité :
- Validation des données entrantes via le système de validation de Laravel
- Vérification des identifiants employeur et des tokens de site
- Validation de la position géographique

### Performances

Pour optimiser les performances :
- Les requêtes sont traitées de manière efficace avec des requêtes SQL optimisées
- Les calculs complexes (comme la distance géographique) sont mis en cache lorsque possible
- Les réponses sont formatées pour minimiser la taille des données transférées

### Gestion des erreurs

L'API implémente une gestion robuste des erreurs :
- Toutes les exceptions sont capturées et journalisées
- Des messages d'erreur clairs sont renvoyés au client
- Les codes HTTP appropriés sont utilisés pour indiquer le type d'erreur

### Mode hors ligne

L'application mobile peut fonctionner en mode hors ligne :
- Les pointages sont enregistrés localement dans `SharedPreferences`
- Un statut de synchronisation est maintenu pour chaque pointage
- Les pointages sont automatiquement synchronisés lorsque la connexion est rétablie
- Les statistiques peuvent être calculées localement en attendant la synchronisation

### Compatibilité

L'API est compatible avec :
- Laravel 8.x et versions ultérieures
- PHP 7.4 et versions ultérieures
- MySQL 5.7 et versions ultérieures
- Flutter 2.x et versions ultérieures
