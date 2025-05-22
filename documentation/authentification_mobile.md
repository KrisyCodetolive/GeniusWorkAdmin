# Authentification Mobile par QR Code - GENIUS WORK

Ce document décrit l'implémentation de l'authentification par QR code dans l'application mobile GENIUS WORK, permettant aux employés de se connecter facilement en scannant leur badge.

## Architecture

L'authentification par QR code est composée de plusieurs éléments :

1. **Backend Laravel** : API RESTful pour la validation des QR codes et la gestion des tokens
2. **Application Mobile Flutter** : Interface utilisateur pour scanner les QR codes et gérer la session
3. **Intégration avec la géolocalisation** : Vérification de la position de l'employé lors du pointage

## Flux d'authentification

1. L'employé ouvre l'application mobile
2. L'application vérifie si un token d'authentification valide existe déjà
3. Si non, l'écran de scan de QR code est affiché
4. L'employé scanne son badge QR code
5. L'application envoie le code au serveur pour validation
6. Si le QR code est valide, un token d'authentification est généré et stocké
7. L'employé est redirigé vers l'écran d'accueil

## Composants principaux

### Services

#### AuthService

Le service `AuthService` gère toutes les opérations liées à l'authentification :

- `loginWithQrCode(String qrCode)` : Authentifie un utilisateur avec son QR code
- `logout()` : Déconnecte l'utilisateur
- `isAuthenticated()` : Vérifie si l'utilisateur est authentifié
- `getAuthToken()` : Récupère le token d'authentification
- `getEmployeData()` : Récupère les données de l'employé

#### SiteLocationService

Le service `SiteLocationService` gère la validation de la position géographique :

- `isInAllowedZone(Position position)` : Vérifie si la position est dans la zone autorisée
- `getDistanceFromEntreprise(Position position)` : Calcule la distance entre l'employé et l'entreprise

#### ConfigService

Le service `ConfigService` gère les paramètres de configuration :

- `getConfig()` : Récupère toutes les configurations
- `saveConfig(Map<String, dynamic> config)` : Sauvegarde les configurations
- `resetConfig()` : Réinitialise les configurations aux valeurs par défaut

### Écrans

#### QRScannerScreen

Écran permettant de scanner un QR code pour s'authentifier.

```dart
QRScannerScreen(
  onScanSuccess: (String qrCode) {
    // Traitement du QR code scanné
  },
)
```

#### LoginResultScreen

Écran affichant le résultat de l'authentification.

```dart
LoginResultScreen(
  result: {
    'status': 'success',
    'message': 'Authentification réussie',
    'employe': {
      'nom': 'Dupont',
      'prenom': 'Jean',
      // ...
    }
  },
)
```

#### HomeScreen

Écran principal affiché après une authentification réussie.

#### PointageScreen

Écran permettant d'enregistrer les pointages d'entrée et de sortie.

#### LocationDetailsScreen

Écran affichant les détails de la position géographique de l'employé.

## Sécurité

### Côté serveur

- Les QR codes ont une date d'expiration
- Les QR codes peuvent être désactivés à tout moment
- Vérification de l'état actif de l'employé
- Utilisation de Laravel Sanctum pour la gestion des tokens

### Côté client

- Stockage sécurisé des tokens dans les préférences partagées
- Vérification régulière de la validité du token
- Déconnexion automatique en cas d'expiration du token

## Intégration avec la géolocalisation

L'authentification par QR code est intégrée avec le système de validation de géolocalisation :

1. L'employé s'authentifie avec son QR code
2. Lors du pointage, sa position géographique est vérifiée
3. Le pointage n'est validé que si l'employé se trouve dans la zone autorisée

## Dépendances

- `qr_code_scanner` : Pour scanner les QR codes
- `shared_preferences` : Pour stocker les informations d'authentification
- `http` : Pour les requêtes API
- `geolocator` : Pour la géolocalisation

## Exemple d'utilisation

```dart
// Authentification avec un QR code
final result = await AuthService.loginWithQrCode('ABC123XYZ');

if (result['status'] == 'success') {
  // Authentification réussie
  Navigator.pushReplacement(
    context,
    MaterialPageRoute(builder: (context) => HomeScreen()),
  );
} else {
  // Échec de l'authentification
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(content: Text(result['message'])),
  );
}
```

## Tests

Des tests unitaires et d'intégration sont disponibles pour vérifier le bon fonctionnement de l'authentification par QR code :

- `EmployeAuthTest` : Tests pour les endpoints d'API
- Tests Flutter pour les écrans et services

## Prochaines améliorations

1. **Rotation automatique des QR codes** : Renouvellement périodique des QR codes pour plus de sécurité
2. **Authentification biométrique** : Ajout d'une couche supplémentaire de sécurité avec empreinte digitale ou reconnaissance faciale
3. **Mode hors ligne** : Permettre l'authentification en mode hors ligne avec synchronisation ultérieure
4. **Notifications push** : Alerter l'employé en cas de tentative d'authentification suspecte
