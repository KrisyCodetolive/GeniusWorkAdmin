# API Documentation - Pointage Mobile avec WebAuthn

## Vue d'ensemble

Cette documentation décrit les endpoints API pour le système de pointage mobile avec authentification WebAuthn. Le système permet aux employés de pointer sur les sites de leur entreprise via un navigateur web mobile sans installation d'application.

## Base URL

```
https://votre-domaine.com/api
```

## Authentification

La plupart des endpoints utilisent l'authentification Sanctum. Les endpoints publics sont marqués comme tels.

### Headers requis

```http
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: {csrf_token}
Authorization: Bearer {token} # Pour les endpoints authentifiés
```

## Endpoints API

### 1. Validation de QR Code

**POST** `/mobile/pointage/qr/validate` *(Public)*

Valide un QR code de site et retourne les informations du site.

#### Paramètres

```json
{
  "token": "string (requis) - Token du QR code",
  "site_id": "string (requis) - ID du site"
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "QR code valide",
  "data": {
    "site": {
      "id": "uuid",
      "nom": "Nom du site",
      "adresse": "Adresse complète",
      "ville": "Ville",
      "has_geofencing": true,
      "rayon_geofencing": 100,
      "latitude": 5.316667,
      "longitude": -4.033333
    },
    "qr_info": {
      "generated_at": "2025-09-27T20:00:00Z",
      "expires_at": "2025-09-28T20:00:00Z",
      "is_valid": true
    }
  }
}
```

#### Réponse d'erreur (400)

```json
{
  "success": false,
  "message": "QR code invalide ou expiré"
}
```

---

### 2. Options d'authentification WebAuthn

**POST** `/mobile/pointage/webauthn/auth-options` *(Public)*

Génère les options d'authentification WebAuthn pour un utilisateur.

#### Paramètres

```json
{
  "email": "string (requis) - Adresse email de l'employé"
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "Options d'authentification générées",
  "data": {
    "challenge": "base64url_encoded_challenge",
    "timeout": 60000,
    "rpId": "votre-domaine.com",
    "allowCredentials": [
      {
        "id": "credential_id",
        "type": "public-key",
        "transports": ["internal", "hybrid"]
      }
    ],
    "userVerification": "required"
  }
}
```

---

### 3. Vérification d'authentification WebAuthn

**POST** `/mobile/pointage/webauthn/verify-auth` *(Public)*

Vérifie l'authentification WebAuthn et retourne les informations de l'employé.

#### Paramètres

```json
{
  "email": "string (requis) - Adresse email",
  "webauthn_response": {
    "id": "string",
    "rawId": "string",
    "response": {
      "authenticatorData": "string",
      "clientDataJSON": "string",
      "signature": "string",
      "userHandle": "string|null"
    },
    "type": "public-key"
  }
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "Authentification réussie",
  "data": {
    "employeur": {
      "id": "uuid",
      "nom_complet": "Prénom Nom",
      "email": "email@entreprise.com",
      "matricule": "EMP001",
      "entreprise": {
        "id": "uuid",
        "nom": "Nom de l'entreprise"
      }
    }
  }
}
```

---

### 4. Validation de géolocalisation

**POST** `/mobile/pointage/location/validate` *(Public)*

Valide la position GPS de l'employé par rapport au site.

#### Paramètres

```json
{
  "latitude": "number (requis) - Latitude GPS (-90 à 90)",
  "longitude": "number (requis) - Longitude GPS (-180 à 180)",
  "site_id": "string (requis) - ID du site"
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "Position validée",
  "data": {
    "distance": 45.67,
    "rayon_autorise": 100,
    "geofencing_enabled": true
  }
}
```

#### Réponse d'erreur (400)

```json
{
  "success": false,
  "message": "Vous êtes à 150.5m du site (rayon autorisé: 100m)",
  "data": {
    "distance": 150.5,
    "rayon_autorise": 100
  }
}
```

---

### 5. Enregistrement de pointage

**POST** `/mobile/pointage/record` *(Public)*

Enregistre un pointage d'entrée ou de sortie.

#### Paramètres

```json
{
  "employeur_id": "string (requis) - ID de l'employé",
  "site_id": "string (requis) - ID du site",
  "latitude": "number (requis) - Latitude GPS",
  "longitude": "number (requis) - Longitude GPS",
  "webauthn_verified": "boolean (requis) - Statut de vérification WebAuthn",
  "webauthn_credential_id": "string|null - ID du credential WebAuthn"
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "Pointage d'entrée enregistré",
  "data": {
    "presence_id": "uuid",
    "type": "entree",
    "date_heure": "2025-09-27T08:00:00Z",
    "statut": "present",
    "minutes_retard": 0,
    "minutes_travaillees": null,
    "site": {
      "nom": "Site Principal",
      "adresse": "123 Rue Example"
    }
  }
}
```

---

### 6. Historique des pointages

**POST** `/mobile/pointage/history` *(Public)*

Récupère l'historique des pointages d'un employé.

#### Paramètres

```json
{
  "employeur_id": "string (requis) - ID de l'employé",
  "limit": "integer (optionnel) - Nombre de résultats (1-50, défaut: 10)"
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "Historique récupéré",
  "data": {
    "presences": [
      {
        "id": "uuid",
        "date_entree": "2025-09-27T08:00:00Z",
        "date_sortie": "2025-09-27T17:00:00Z",
        "statut": "present",
        "minutes_travaillees": 480,
        "minutes_retard": 0,
        "site": {
          "nom": "Site Principal",
          "adresse": "123 Rue Example"
        }
      }
    ],
    "total": 1
  }
}
```

---

### 7. Statut de pointage actuel

**POST** `/mobile/pointage/status` *(Public)*

Récupère le statut de pointage actuel d'un employé.

#### Paramètres

```json
{
  "employeur_id": "string (requis) - ID de l'employé"
}
```

#### Réponse - Pointage en cours (200)

```json
{
  "success": true,
  "message": "Pointage en cours",
  "data": {
    "is_checked_in": true,
    "presence_id": "uuid",
    "heure_entree": "2025-09-27T08:00:00Z",
    "temps_ecoule_minutes": 120,
    "statut": "present",
    "site": {
      "nom": "Site Principal",
      "adresse": "123 Rue Example"
    }
  }
}
```

#### Réponse - Aucun pointage (200)

```json
{
  "success": true,
  "message": "Aucun pointage en cours",
  "data": {
    "is_checked_in": false
  }
}
```

---

### 8. Calcul des heures de travail

**POST** `/mobile/pointage/working-hours` *(Public)*

Calcule les heures de travail pour une période donnée.

#### Paramètres

```json
{
  "employeur_id": "string (requis) - ID de l'employé",
  "date_debut": "string (requis) - Date de début (ISO 8601)",
  "date_fin": "string (requis) - Date de fin (ISO 8601)"
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "Heures de travail calculées",
  "data": {
    "total_heures": 40.5,
    "heures_supplementaires": 2.5,
    "minutes_retard": 15,
    "jours_travailles": 5,
    "presences": [
      // Array des présences détaillées
    ]
  }
}
```

---

## Endpoints de gestion des QR codes (Authentifiés)

### 9. Génération de QR code pour un site

**POST** `/qr-codes/sites/{siteId}/generate` *(Authentifié)*

Génère un nouveau QR code pour un site.

#### Paramètres

```json
{
  "expiration_hours": "integer (optionnel) - Durée de validité en heures (1-168, défaut: 24)"
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "QR code généré avec succès",
  "data": {
    "qr_data": "{\"site_id\":\"uuid\",\"token\":\"...\",\"url\":\"...\"}",
    "qr_url": "https://domaine.com/mobile/pointage/uuid?token=...",
    "expires_at": "2025-09-28T20:00:00Z",
    "site": {
      "id": "uuid",
      "nom": "Site Principal",
      "adresse": "123 Rue Example"
    }
  }
}
```

---

### 10. Rafraîchissement de QR code

**POST** `/qr-codes/sites/{siteId}/refresh` *(Authentifié)*

Rafraîchit le QR code d'un site (invalide l'ancien et génère un nouveau).

#### Paramètres

```json
{
  "expiration_hours": "integer (optionnel) - Durée de validité en heures (1-168, défaut: 24)"
}
```

#### Réponse

Identique à la génération de QR code.

---

### 11. Invalidation de QR code

**DELETE** `/qr-codes/sites/{siteId}/invalidate` *(Authentifié)*

Invalide immédiatement le QR code d'un site.

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "QR code invalidé avec succès"
}
```

---

### 12. Informations d'un QR code

**POST** `/qr-codes/info` *(Public)*

Récupère les informations d'un QR code via son token.

#### Paramètres

```json
{
  "token": "string (requis) - Token du QR code"
}
```

#### Réponse

Identique à la validation de QR code.

---

### 13. Génération de QR codes pour toute l'entreprise

**POST** `/qr-codes/entreprise/generate-all` *(Authentifié)*

Génère des QR codes pour tous les sites actifs de l'entreprise.

#### Paramètres

```json
{
  "expiration_hours": "integer (optionnel) - Durée de validité en heures (1-168, défaut: 24)"
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "QR codes générés pour l'entreprise",
  "data": {
    "entreprise_id": "uuid",
    "sites": [
      {
        "site_id": "uuid",
        "site_nom": "Site 1",
        "qr_result": {
          "success": true,
          "qr_url": "...",
          "expires_at": "..."
        }
      }
    ],
    "total_sites": 3
  }
}
```

---

### 14. Statistiques des QR codes

**POST** `/qr-codes/stats` *(Authentifié)*

Récupère les statistiques d'utilisation des QR codes.

#### Paramètres

```json
{
  "date_debut": "string (requis) - Date de début (ISO 8601)",
  "date_fin": "string (requis) - Date de fin (ISO 8601)"
}
```

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "Statistiques récupérées",
  "data": {
    "total_sites": 5,
    "sites_with_qr": 3,
    "active_qr_codes": 2,
    "expired_qr_codes": 1,
    "usage_by_site": [
      {
        "site_id": "uuid",
        "site_nom": "Site Principal",
        "usage_count": 25
      }
    ]
  }
}
```

---

### 15. Liste des sites avec statut QR

**GET** `/qr-codes/sites/status` *(Authentifié)*

Liste tous les sites avec leur statut de QR code.

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "Liste des sites récupérée",
  "data": {
    "sites": [
      {
        "id": "uuid",
        "nom": "Site Principal",
        "adresse": "123 Rue Example",
        "ville": "Abidjan",
        "statut": "actif",
        "qr_status": {
          "has_qr": true,
          "is_expired": false,
          "generated_at": "2025-09-27T20:00:00Z",
          "expires_at": "2025-09-28T20:00:00Z"
        }
      }
    ],
    "total": 5,
    "with_qr": 3,
    "expired": 1
  }
}
```

---

### 16. Nettoyage des QR codes expirés

**POST** `/qr-codes/cleanup` *(Authentifié)*

Nettoie tous les QR codes expirés.

#### Réponse de succès (200)

```json
{
  "success": true,
  "message": "Nettoyage terminé. 3 QR codes expirés supprimés.",
  "data": {
    "cleaned_count": 3
  }
}
```

---

## Codes d'erreur

| Code | Description |
|------|-------------|
| 200 | Succès |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Accès refusé |
| 404 | Ressource non trouvée |
| 422 | Erreur de validation |
| 500 | Erreur serveur |

## Exemples d'utilisation

### Flux complet de pointage

```javascript
// 1. Valider le QR code
const qrValidation = await fetch('/api/mobile/pointage/qr/validate', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    token: 'qr_token_from_scan',
    site_id: 'site_uuid'
  })
});

// 2. Obtenir les options WebAuthn
const authOptions = await fetch('/api/mobile/pointage/webauthn/auth-options', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'employe@entreprise.com'
  })
});

// 3. Authentification WebAuthn (côté client)
const credential = await navigator.credentials.get({
  publicKey: authOptions.data
});

// 4. Vérifier l'authentification
const authResult = await fetch('/api/mobile/pointage/webauthn/verify-auth', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'employe@entreprise.com',
    webauthn_response: credential
  })
});

// 5. Valider la géolocalisation
const locationValidation = await fetch('/api/mobile/pointage/location/validate', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    latitude: 5.316667,
    longitude: -4.033333,
    site_id: 'site_uuid'
  })
});

// 6. Enregistrer le pointage
const pointageResult = await fetch('/api/mobile/pointage/record', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    employeur_id: 'employeur_uuid',
    site_id: 'site_uuid',
    latitude: 5.316667,
    longitude: -4.033333,
    webauthn_verified: true,
    webauthn_credential_id: 'credential_id'
  })
});
```

## Notes importantes

1. **Sécurité** : Tous les endpoints publics sont conçus pour être sécurisés via WebAuthn et validation géographique.

2. **Expiration** : Les QR codes expirent automatiquement après 24h par défaut pour des raisons de sécurité.

3. **Géolocalisation** : La validation GPS est obligatoire si le géofencing est activé pour le site.

4. **Logs** : Toutes les actions sont loggées pour audit et traçabilité.

5. **Rate Limiting** : Les endpoints peuvent être soumis à des limitations de taux selon la configuration.

6. **CORS** : Assurez-vous que les domaines autorisés sont configurés pour les requêtes cross-origin.

## Support

Pour toute question ou problème avec l'API, contactez l'équipe de développement ou consultez les logs d'erreur pour plus de détails.
