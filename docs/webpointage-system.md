# Documentation du Système WebPointage

## Vue d'ensemble

Le système WebPointage est une solution complète de gestion des présences pour les entreprises, intégrant des technologies modernes comme la géolocalisation et l'authentification biométrique (WebAuthn) pour assurer la sécurité et la fiabilité des pointages.

## Fonctionnalités principales

1. **Pointage par QR Code**
   - Génération de QR codes uniques pour chaque site
   - Expiration automatique des QR codes après 24 heures
   - Options de téléchargement et d'impression des QR codes

2. **Authentification sécurisée**
   - Intégration de WebAuthn pour l'authentification biométrique
   - Vérification de l'identité de l'utilisateur à chaque pointage

3. **Géolocalisation**
   - Vérification de la position de l'utilisateur lors du pointage
   - Système de geofencing pour s'assurer que l'utilisateur est bien sur le site

4. **Types de pointages**
   - Entrée
   - Sortie
   - Début de pause
   - Fin de pause

5. **Historique et rapports**
   - Vue détaillée de l'historique des pointages
   - Filtrage par date et type de pointage
   - Statistiques sur les heures travaillées, les pauses, etc.

## Architecture technique

### Modèles

1. **Pointage**
   - `user_id`: ID de l'utilisateur
   - `entreprise_id`: ID de l'entreprise
   - `site_id`: ID du site
   - `type`: Type de pointage (entrée, sortie, pause_debut, pause_fin)
   - `latitude`: Latitude de l'utilisateur lors du pointage
   - `longitude`: Longitude de l'utilisateur lors du pointage
   - `date_heure`: Date et heure du pointage

2. **WebAuthnCredential**
   - `user_id`: ID de l'utilisateur
   - `credential_id`: ID de la clé d'authentification
   - `public_key`: Clé publique
   - `counter`: Compteur d'utilisation
   - `last_used_at`: Date de dernière utilisation

### Contrôleurs

1. **WebPointageController**
   - `show()`: Affiche la page de pointage basée sur le QR code scanné
   - `pointage()`: Traite la demande de pointage
   - `generateQrCode()`: Génère un QR code pour un site spécifique
   - `downloadQrCode()`: Télécharge le QR code au format PNG
   - `printQrCode()`: Affiche une version imprimable du QR code
   - `historique()`: Affiche l'historique des pointages avec filtres
   - `calculateStatistics()`: Calcule les statistiques à partir des pointages
   - `verifyWebAuthnCredential()`: Vérifie les informations d'identification WebAuthn
   - `calculateDistance()`: Calcule la distance entre deux points géographiques

### Vues

1. **index.blade.php**
   - Interface principale de pointage
   - Intégration de la géolocalisation et de WebAuthn
   - Boutons pour les différents types de pointage

2. **historique.blade.php**
   - Affichage de l'historique des pointages
   - Filtres par date et type
   - Statistiques sur les heures travaillées

3. **qrcode.blade.php**
   - Affichage du QR code généré
   - Options pour télécharger ou imprimer

4. **print.blade.php**
   - Version imprimable du QR code
   - Instructions d'utilisation

5. **error.blade.php**
   - Page d'erreur pour les problèmes de pointage

### Routes

```php
Route::prefix('webPointage')->name('webPointage.')->group(function () {
    Route::get('/pointage/{token}', [WebPointageController::class, 'show'])->name('show');
    Route::post('/pointage', [WebPointageController::class, 'pointage'])->name('process');
    Route::get('/generate-qr', [WebPointageController::class, 'generateQrCode'])->name('generate-qr');
    Route::get('/download-qr/{token}', [WebPointageController::class, 'downloadQrCode'])->name('download-qr');
    Route::get('/print-qr/{token}', [WebPointageController::class, 'printQrCode'])->name('print-qr');
    Route::get('/historique', [WebPointageController::class, 'historique'])->name('historique');
});
```

## Flux de travail

### Génération de QR code

1. L'administrateur se connecte à l'application
2. Il accède à la section de gestion des sites
3. Il sélectionne un site et demande la génération d'un QR code
4. Le système génère un QR code unique contenant les informations du site
5. L'administrateur peut télécharger ou imprimer le QR code pour l'afficher sur le site

### Pointage

1. L'employé se connecte à l'application sur son appareil mobile
2. Il scanne le QR code affiché sur le site
3. L'application vérifie la validité du QR code
4. L'employé sélectionne le type de pointage (entrée, sortie, pause)
5. L'application demande une authentification biométrique via WebAuthn
6. L'application vérifie la position géographique de l'employé
7. Si toutes les vérifications sont réussies, le pointage est enregistré

### Consultation de l'historique

1. L'employé ou l'administrateur se connecte à l'application
2. Il accède à la section "Historique des pointages"
3. Il peut filtrer les résultats par date et type de pointage
4. Le système affiche les pointages correspondants et calcule des statistiques

## Considérations de sécurité

1. **Protection des données**
   - Les QR codes contiennent uniquement des identifiants et non des données sensibles
   - Les QR codes expirent après 24 heures

2. **Prévention de la fraude**
   - Authentification biométrique obligatoire
   - Vérification de la géolocalisation
   - Enregistrement des coordonnées GPS pour chaque pointage

3. **Confidentialité**
   - Les employés ne peuvent voir que leur propre historique
   - Les administrateurs ont accès aux données de leur entreprise uniquement

## Intégration avec d'autres systèmes

Le système WebPointage peut être intégré avec d'autres modules de GENIUS WORK :

1. **Gestion des ressources humaines**
   - Calcul automatique des heures travaillées
   - Détection des retards et absences

2. **Paie**
   - Exportation des données de pointage pour le calcul des salaires
   - Gestion des heures supplémentaires

3. **Planification**
   - Comparaison entre les horaires prévus et les pointages réels
   - Ajustement automatique des plannings

## Évolutions futures

1. **Application mobile dédiée**
   - Version native pour iOS et Android
   - Notifications pour rappeler les pointages

2. **Reconnaissance faciale**
   - Option supplémentaire d'authentification
   - Prise de photo lors du pointage

3. **Intelligence artificielle**
   - Détection des anomalies dans les habitudes de pointage
   - Prédiction des retards basée sur les données historiques

4. **Intégration IoT**
   - Connexion avec des dispositifs de contrôle d'accès physique
   - Badges NFC comme alternative aux QR codes
