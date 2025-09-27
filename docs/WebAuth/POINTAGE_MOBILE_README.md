# Module de Pointage Mobile avec WebAuthn

## Vue d'ensemble

Ce module permet aux employés de pointer sur les différents sites de leur entreprise directement depuis leur téléphone via un navigateur web, sans nécessiter l'installation d'une application mobile. La solution utilise la technologie WebAuthn (Web Authentication) avec l'authentification Google pour une expérience utilisateur fluide et sécurisée.

## Fonctionnalités principales

### 1. Génération de QR Codes par Site
- Chaque site de l'entreprise peut générer un QR code unique
- Le QR code contient les informations du site et un token de sécurité
- Expiration automatique des QR codes pour la sécurité (24h par défaut)
- Régénération possible des QR codes par les administrateurs

### 2. Authentification WebAuthn avec Google
- Connexion automatique via compte Google
- Utilisation de la clé privée de l'utilisateur pour l'authentification
- Vérification de l'identité via WebAuthn API
- Stockage sécurisé des credentials WebAuthn

### 3. Vérification de Géolocalisation
- Validation de la position GPS de l'employé
- Vérification que l'employé se trouve dans le rayon de géofencing du site
- Calcul de la distance entre la position de l'employé et le site
- Gestion des cas d'erreur de géolocalisation

### 4. Gestion du Pointage
- Enregistrement automatique de l'heure de pointage
- Détection du statut : présent à temps, en retard, ou absent
- Calcul automatique des heures de travail
- Gestion des pauses et heures supplémentaires

### 5. Interface Utilisateur Mobile
- Interface web responsive optimisée pour mobile
- Scan de QR code via la caméra du téléphone
- Messages de confirmation et d'erreur
- Affichage du statut de pointage et des heures travaillées

## Architecture technique

### Modèles de données impliqués

#### Site
- `qr_token` : Token unique pour le QR code
- `qr_generated_at` : Date de génération du QR code
- `latitude`, `longitude` : Coordonnées GPS du site
- `rayon_geofencing` : Rayon de validation en mètres
- `has_geofencing` : Activation du géofencing

#### Employeur
- `qr_code_secret` : Secret pour la validation QR
- `qr_code_expires_at` : Expiration du QR code
- `qr_code_active` : Statut d'activation du QR code

#### Presence
- `webauthn_credential_id` : ID du credential WebAuthn
- `latitude_entree`, `longitude_entree` : Position GPS d'entrée
- `distance_site` : Distance calculée par rapport au site
- `verification_data` : Données de vérification WebAuthn

### Services à développer

#### PresenceService
```php
class PresenceService
{
    public function validateQRCode(string $token, string $siteId): bool
    public function authenticateWithWebAuthn(array $credentials): User
    public function validateGeolocation(float $lat, float $lng, Site $site): bool
    public function recordPresence(Employeur $employeur, Site $site, array $data): Presence
    public function calculateWorkingHours(Employeur $employeur, Carbon $date): array
}
```

#### WebAuthnService
```php
class WebAuthnService
{
    public function generateRegistrationOptions(User $user): array
    public function verifyRegistration(array $response, User $user): bool
    public function generateAuthenticationOptions(User $user): array
    public function verifyAuthentication(array $response, User $user): bool
}
```

#### QRCodeService
```php
class QRCodeService
{
    public function generateSiteQRCode(Site $site): string
    public function validateQRCode(string $token): ?Site
    public function refreshQRCode(Site $site): string
}
```

## Flux utilisateur

### 1. Génération du QR Code
1. L'administrateur génère un QR code pour un site spécifique
2. Le QR code est affiché et peut être imprimé ou partagé
3. Le token est stocké avec une date d'expiration

### 2. Processus de Pointage
1. **Scan du QR Code**
   - L'employé scanne le QR code avec son téléphone
   - Validation du token et vérification de l'expiration
   - Redirection vers l'interface de pointage

2. **Authentification WebAuthn**
   - Demande d'authentification via Google
   - Vérification des credentials WebAuthn
   - Validation de l'identité de l'employé

3. **Vérification de Géolocalisation**
   - Demande d'accès à la géolocalisation
   - Calcul de la distance par rapport au site
   - Validation du rayon de géofencing

4. **Enregistrement du Pointage**
   - Création d'un enregistrement de présence
   - Calcul du statut (à temps, retard, etc.)
   - Mise à jour des heures de travail

5. **Confirmation**
   - Affichage du message de confirmation
   - Résumé des heures travaillées
   - Options pour pointer la sortie

## Sécurité

### Mesures de sécurité implémentées
- **Expiration des QR codes** : Limitation dans le temps
- **Validation géographique** : Vérification de la position
- **WebAuthn** : Authentification forte sans mot de passe
- **Chiffrement des données** : Protection des informations sensibles
- **Logs d'audit** : Traçabilité des actions

### Gestion des erreurs
- QR code expiré ou invalide
- Échec de l'authentification WebAuthn
- Position GPS hors du rayon autorisé
- Problèmes de connectivité réseau
- Employé non autorisé pour le site

## API Endpoints

### Authentification et QR Code
```
POST /api/mobile/qr/validate
POST /api/mobile/auth/webauthn/register
POST /api/mobile/auth/webauthn/authenticate
```

### Pointage
```
POST /api/mobile/presence/checkin
POST /api/mobile/presence/checkout
GET /api/mobile/presence/status
GET /api/mobile/presence/history
```

### Géolocalisation
```
POST /api/mobile/location/validate
GET /api/mobile/site/{id}/info
```

## Configuration requise

### Côté serveur
- Laravel 10+
- Extension PHP GMP pour WebAuthn
- Base de données MySQL/PostgreSQL
- HTTPS obligatoire pour WebAuthn

### Côté client
- Navigateur compatible WebAuthn (Chrome 67+, Firefox 60+, Safari 14+)
- Géolocalisation activée
- Caméra pour le scan QR code
- Connexion Internet stable

## Installation et déploiement

### 1. Installation des dépendances
```bash
composer require web-auth/webauthn-lib
composer require endroid/qr-code
npm install @simplewebauthn/browser
```

### 2. Migration de base de données
```bash
php artisan migrate
php artisan db:seed --class=WebAuthnSeeder
```

### 3. Configuration
```env
WEBAUTHN_NAME="GeniusWork"
WEBAUTHN_ID="geniuswork.com"
WEBAUTHN_ICON="https://geniuswork.com/logo.png"
```

## Tests

### Tests unitaires
- Validation des QR codes
- Authentification WebAuthn
- Calculs de géolocalisation
- Logique de pointage

### Tests d'intégration
- Flux complet de pointage
- Gestion des erreurs
- Performance des API

### Tests utilisateur
- Interface mobile responsive
- Compatibilité navigateurs
- Expérience utilisateur

## Roadmap

### Phase 1 (MVP)
- [x] Spécification fonctionnelle
- [ ] Développement du service de base
- [ ] Interface de pointage simple
- [ ] Tests de base

### Phase 2 (Améliorations)
- [ ] Interface administrateur pour QR codes
- [ ] Notifications push
- [ ] Rapports de présence avancés
- [ ] Support multi-langues

### Phase 3 (Fonctionnalités avancées)
- [ ] Reconnaissance faciale
- [ ] Intégration avec systèmes RH
- [ ] Analytics et tableaux de bord
- [ ] API pour applications tierces

## Support et maintenance

### Monitoring
- Logs d'erreurs WebAuthn
- Métriques de performance
- Alertes de sécurité

### Maintenance
- Mise à jour des dépendances
- Renouvellement des certificats
- Optimisation des performances

---

**Auteur** : Équipe de développement GeniusWork  
**Version** : 1.0  
**Date** : 2025-09-27
