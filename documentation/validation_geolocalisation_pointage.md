# Documentation Validation de Géolocalisation pour Pointage Mobile

## Table des matières
1. [Introduction](#introduction)
2. [Architecture](#architecture)
3. [Services](#services)
   - [LocationService](#locationservice)
   - [ConfigService](#configservice)
4. [Intégration avec le Pointage Mobile](#intégration-avec-le-pointage-mobile)
5. [Flux de Validation](#flux-de-validation)
6. [Considérations Techniques](#considérations-techniques)
7. [Interfaces Utilisateur](#interfaces-utilisateur)
8. [Mode Hors Ligne](#mode-hors-ligne)
9. [Intégration Backend-Frontend](#intégration-backend-frontend)

## Introduction

La validation de géolocalisation est une fonctionnalité essentielle du système de pointage mobile GENIUS WORK. Elle permet de s'assurer que les employés sont physiquement présents sur le site de l'entreprise lorsqu'ils effectuent un pointage. Cette documentation décrit l'implémentation technique de cette fonctionnalité et son intégration avec le service de pointage mobile.

## Architecture

L'architecture de la validation de géolocalisation s'articule autour de trois composants principaux :

1. **LocationService** : Service responsable de l'obtention de la position de l'utilisateur et du calcul de la distance par rapport à l'entreprise.
2. **ConfigService** : Service de gestion des paramètres de configuration, notamment les coordonnées de l'entreprise et le rayon maximum autorisé.
3. **MobilePointageService** : Service de pointage qui utilise les services précédents pour valider la position de l'utilisateur avant d'enregistrer un pointage.

Cette architecture modulaire facilite la maintenance et l'évolution du système.

## Services

### LocationService

Le `LocationService` est responsable de toutes les opérations liées à la géolocalisation.

#### Fonctionnalités principales

- Initialisation du service de localisation et vérification des permissions
- Obtention de la position actuelle de l'utilisateur
- Calcul de la distance entre deux points géographiques (formule de Haversine)
- Vérification si l'utilisateur est dans la zone autorisée
- Calcul de la distance entre l'utilisateur et l'entreprise

#### Exemple d'utilisation

```dart
// Vérifier si l'utilisateur est dans la zone autorisée
final bool isInZone = await LocationService.isUserInAllowedZone();

// Obtenir la distance par rapport à l'entreprise
final double distance = await LocationService.getDistanceFromEntreprise();
```

### ConfigService

Le `ConfigService` gère les paramètres de configuration liés à la géolocalisation.

#### Fonctionnalités principales

- Stockage et récupération des coordonnées de l'entreprise
- Gestion du rayon maximum autorisé pour le pointage
- Sauvegarde et récupération des configurations via SharedPreferences
- Valeurs par défaut pour une première utilisation

#### Exemple d'utilisation

```dart
// Obtenir les coordonnées de l'entreprise
final Map<String, double> coordinates = await ConfigService.getEntrepriseCoordinates();

// Obtenir le rayon maximum autorisé
final double rayonMaximum = await ConfigService.getRayonMaximum();

// Obtenir toutes les configurations
final Map<String, dynamic> config = await ConfigService.getConfig();

// Sauvegarder une nouvelle configuration
await ConfigService.saveConfig({
  'entrepriseLatitude': 5.3484235,
  'entrepriseLongitude': -4.0123600,
  'rayonMaximum': 150.0,
});
```

## Intégration avec le Pointage Mobile

Le service de pointage mobile (`MobilePointageService`) intègre la validation de géolocalisation à plusieurs niveaux :

1. **Lors de l'enregistrement d'un pointage** : Vérification de la position de l'utilisateur avant d'autoriser le pointage.
2. **Lors de la vérification d'autorisation** : Calcul de la distance et vérification de la zone autorisée.
3. **Dans le mode hors ligne** : Validation locale de la position même sans connexion au serveur.

### Exemple d'intégration dans le MobilePointageService

```dart
// Extrait de la méthode enregistrerPointage
final position = await LocationService.getCurrentLocation();
      
if (position == null) {
  return {
    'status': 'error',
    'message': 'Impossible d\'obtenir votre position'
  };
}

// Vérifier si l'utilisateur est dans la zone autorisée
final bool isInZone = await LocationService.isUserInAllowedZone();

if (!isInZone) {
  return {
    'status': 'error',
    'message': 'Vous êtes en dehors de la zone autorisée pour le pointage'
  };
}
```

## Flux de Validation

Le processus complet de validation de géolocalisation pour un pointage suit les étapes suivantes :

1. **Initialisation** : Vérification des permissions et activation du service de localisation.
2. **Obtention de la position** : Récupération des coordonnées GPS de l'utilisateur.
3. **Récupération des paramètres** : Obtention des coordonnées de l'entreprise et du rayon maximum autorisé.
4. **Calcul de la distance** : Utilisation de la formule de Haversine pour calculer la distance entre l'utilisateur et l'entreprise.
5. **Validation** : Comparaison de la distance calculée avec le rayon maximum autorisé.
6. **Décision** : Autorisation ou refus du pointage en fonction du résultat de la validation.
7. **Enregistrement** : Sauvegarde des informations de localisation avec le pointage pour référence future.

## Considérations Techniques

### Précision de la Géolocalisation

La précision de la géolocalisation dépend de plusieurs facteurs :

- **Environnement** : La précision est généralement meilleure en extérieur qu'en intérieur.
- **Appareil** : Les capteurs GPS varient en qualité selon les appareils.
- **Paramètres** : L'application utilise `LocationAccuracy.high` pour maximiser la précision.

Pour compenser ces variations, le système permet de configurer un rayon de tolérance adapté à chaque site.

### Calcul de Distance

La distance entre deux points géographiques est calculée à l'aide de la formule de Haversine, qui prend en compte la courbure de la Terre :

```dart
static double _calculateDistance(double lat1, double lon1, double lat2, double lon2) {
  const double earthRadius = 6371000; // Rayon de la Terre en mètres
  final double latDistance = _toRadians(lat2 - lat1);
  final double lonDistance = _toRadians(lon2 - lon1);
  
  final double a = 
      (sin(latDistance / 2) * sin(latDistance / 2)) +
      (cos(_toRadians(lat1)) * cos(_toRadians(lat2)) * 
      sin(lonDistance / 2) * sin(lonDistance / 2));
  
  final double c = 2 * atan2(sqrt(a), sqrt(1 - a));
  final double distance = earthRadius * c;
  
  return distance;
}
```

### Gestion des Permissions

L'application demande les permissions de localisation nécessaires au démarrage :

```dart
permissionGranted = await _location.hasPermission();
if (permissionGranted == PermissionStatus.denied) {
  permissionGranted = await _location.requestPermission();
  if (permissionGranted != PermissionStatus.granted) {
    return false;
  }
}
```

## Interfaces Utilisateur

### ConfigPage

La page de configuration (`ConfigPage`) permet aux administrateurs de modifier les paramètres de géolocalisation :

- Coordonnées de l'entreprise (latitude et longitude)
- Rayon maximum autorisé pour le pointage
- Nom et adresse de l'entreprise

### LocationDetailsPage

La page de détails de localisation (`LocationDetailsPage`) affiche des informations sur la position actuelle de l'utilisateur :

- Position actuelle (latitude et longitude)
- Distance par rapport à l'entreprise
- Statut (dans/hors zone autorisée)
- Carte visuelle montrant la position relative

## Mode Hors Ligne

Le système de validation de géolocalisation fonctionne également en mode hors ligne :

1. **Stockage local des paramètres** : Les coordonnées de l'entreprise et le rayon maximum sont stockés localement via `ConfigService`.
2. **Validation locale** : Le `LocationService` peut calculer la distance et valider la position sans connexion au serveur.
3. **Enregistrement local** : Les pointages sont enregistrés localement avec les informations de géolocalisation.
4. **Synchronisation différée** : Les pointages sont synchronisés avec le serveur lorsque la connexion est rétablie.

### Exemple de gestion du mode hors ligne

```dart
// En cas d'erreur de connexion, enregistrer localement avec statut "non synchronisé"
final Pointage pointage = Pointage(
  id: DateTime.now().millisecondsSinceEpoch.toString(),
  userId: employeurId,
  type: type,
  dateHeure: DateTime.now(),
  latitude: position.latitude ?? 0,
  longitude: position.longitude ?? 0,
  distance: await LocationService.getDistanceFromEntreprise(),
  isInZone: isInZone,
  isPause: isPause,
  syncStatus: 'not_synced',
);

// Sauvegarder le pointage localement
await _savePointageLocally(pointage);

return {
  'status': 'warning',
  'data': pointage.toJson(),
  'message': 'Pointage enregistré localement (mode hors ligne). La synchronisation sera effectuée automatiquement.'
};
```

## Intégration Backend-Frontend

L'intégration entre le backend Laravel et l'application mobile Flutter pour la validation de géolocalisation est assurée par plusieurs services spécialisés.

### Backend Laravel

#### SiteLocationService

Le service `SiteLocationService` est responsable de la récupération des coordonnées du site associé à un employé et de la vérification de sa position.

```php
namespace App\Services\Presence;

class SiteLocationService
{
    /**
     * Récupère les coordonnées du site principal associé à un employé
     *
     * @param string $employeId ID ou QR code secret de l'employé
     * @return array Coordonnées du site et configuration de géolocalisation
     */
    public function getEmployeSiteCoordinates($employeId) { ... }

    /**
     * Vérifie si un point GPS est dans le rayon autorisé pour un employé
     *
     * @param string $employeId ID ou QR code secret de l'employé
     * @param float $latitude Latitude du point à vérifier
     * @param float $longitude Longitude du point à vérifier
     * @return array Résultat de la vérification
     */
    public function verifierPositionEmploye($employeId, $latitude, $longitude) { ... }
}
```

Le service implémente une logique de recherche hiérarchique pour trouver les coordonnées du site approprié pour un employé :

1. Vérification d'un site spécifique dans les métadonnées de l'employé
2. Recherche d'un site associé à la filiale de l'employé
3. Utilisation du site principal de l'entreprise
4. Utilisation des coordonnées de l'entreprise en dernier recours

#### Routes API

Les routes API suivantes sont exposées pour la validation de géolocalisation :

```php
// Récupération des coordonnées du site
Route::post('/pointage/site-coordinates', [MobilePointageController::class, 'getSiteCoordinates'])
    ->name('api.pointage.site-coordinates');

// Vérification de la position
Route::post('/pointage/verifier-position', [MobilePointageController::class, 'verifierPosition'])
    ->name('api.pointage.verifier-position');
```

### Frontend Flutter

#### SiteLocationService

Le service `SiteLocationService` côté Flutter communique avec les API du backend et gère la mise en cache des données pour une utilisation hors ligne.

```dart
class SiteLocationService {
  // Récupère les coordonnées du site associé à l'employé connecté
  static Future<Map<String, dynamic>> getSiteCoordinates({bool forceRefresh = false}) { ... }
  
  // Vérifie si la position actuelle est dans la zone autorisée
  static Future<Map<String, dynamic>> verifierPosition(double latitude, double longitude) { ... }
  
  // Vérifie localement si la position est dans la zone autorisée (fallback)
  static Future<Map<String, dynamic>> _verifierPositionLocale(double latitude, double longitude) { ... }
}
```

Le service implémente plusieurs fonctionnalités clés :

1. **Mise en cache** : Les coordonnées du site sont mises en cache localement pour une utilisation hors ligne
2. **Synchronisation avec ConfigService** : Les coordonnées récupérées sont automatiquement synchronisées avec le `ConfigService`
3. **Vérification locale** : En cas d'échec de l'API, une vérification locale est effectuée comme solution de secours
4. **Implémentation mathématique** : Le service inclut une implémentation complète des fonctions mathématiques nécessaires au calcul de distance

### Flux de données

Le flux de données entre le backend et le frontend pour la validation de géolocalisation est le suivant :

1. L'application mobile demande les coordonnées du site via `SiteLocationService.getSiteCoordinates()`
2. Le backend récupère les coordonnées du site approprié via `SiteLocationService.getEmployeSiteCoordinates()`
3. Les coordonnées sont renvoyées à l'application mobile et mises en cache
4. Lors d'un pointage, l'application mobile vérifie la position via `SiteLocationService.verifierPosition()`
5. Le backend valide la position via `SiteLocationService.verifierPositionEmploye()`
6. Le résultat de la validation est utilisé pour autoriser ou refuser le pointage

Ce flux garantit une validation cohérente de la géolocalisation, même en cas de problèmes de connectivité temporaires.

## Considérations de sécurité

Cette documentation fournit une vue d'ensemble complète de l'implémentation de la validation de géolocalisation dans le système de pointage mobile GENIUS WORK. Elle servira de référence pour l'équipe de développement et les futurs mainteneurs du code.
