# Documentation Technique - GeniusToolsService

## Description
Le `GeniusToolsService` est un service Laravel qui permet d'interagir avec l'API Genius Tools pour la génération et la gestion de QR codes. Ce service est principalement utilisé pour générer des QR codes pour les pages de donation.

## Configuration

### Prérequis
- Une clé API Genius Tools valide
- PHP 8.0 ou supérieur
- Extension PHP cURL activée

### Configuration dans .env
```env
GENIUS_TOOLS_API_KEY=votre_clé_api
GENIUS_TOOLS_BASE_URL=https://linkqr.genius.ci/api
```

### Configuration dans config/services.php
```php
'genius_tools' => [
    'api_key' => env('GENIUS_TOOLS_API_KEY'),
    'base_url' => env('GENIUS_TOOLS_BASE_URL', 'https://linkqr.genius.ci/api'),
],
```

## Utilisation

### Initialisation
```php
use App\Services\GeniusToolsService;

$geniusTools = new GeniusToolsService();
```

### Méthodes Disponibles

#### 1. Création d'un QR Code
```php
$data = [
    'name' => 'Nom du QR Code',
    'type' => 'url',
    'url' => 'https://votre-url.com'
];

$response = $geniusTools->createQrCode($data);
```

#### 2. Téléchargement d'un QR Code
```php
$qrCodeId = 123;
$qrCodeContent = $geniusTools->downloadQrCode($qrCodeId);
```

#### 3. Génération d'un QR Code pour une Donation
```php
$url = 'https://votre-url-de-donation.com';
$name = 'QR Code Don';
$qrCodeContent = $geniusTools->generateDonationQrCode($url, $name);
```

#### 4. Génération et Sauvegarde d'un QR Code
```php
$url = 'https://votre-url-de-donation.com';
$name = 'QR Code Don';
$savePath = 'qrcodes/donation.svg';
$filePath = $geniusTools->generateAndSaveDonationQrCode($url, $name, $savePath);
```

#### 5. Génération et Téléchargement Direct
```php
$url = 'https://votre-url-de-donation.com';
$name = 'QR Code Don';
$response = $geniusTools->generateDonationQrCodeDownload($url, $name);
```

## Intégration avec Filament

### Dans un Formulaire
```php
use App\Services\GeniusToolsService;
use Filament\Actions;

protected function getActions(): array
{
    return [
        Actions\Action::make('qrcode')
            ->label('Télécharger QR Code')
            ->icon('heroicon-o-qr-code')
            ->action(function ($record) {
                return redirect()->route('qrcode.download', [
                    'url' => $record->getShareableUrl(),
                    'name' => "QR Code Don - {$record->title}"
                ]);
            })
    ];
}
```

### Routes Disponibles
```php
// Téléchargement direct du QR code
Route::get('/qrcode/download', [QrCodeController::class, 'downloadQrCode'])
    ->name('qrcode.download');

// Affichage du QR code dans le navigateur
Route::get('/qrcode/show', [QrCodeController::class, 'showQrCode'])
    ->name('qrcode.show');
```

## Personnalisation des QR Codes

### Options de Style Disponibles
```php
$styleOptions = [
    'style' => 'square',              // Style du QR code
    'inner_eye_style' => 'square',    // Style des yeux intérieurs
    'outer_eye_style' => 'square',    // Style des yeux extérieurs
    'foreground_type' => 'gradient',  // Type de premier plan
    'foreground_gradient_style' => 'diagonal',
    'foreground_gradient_one' => '#10B981',
    'foreground_gradient_two' => '#059669',
    'background_color' => '#FFFFFF',
    'background_color_transparency' => 0,
    'custom_eyes_color' => true,
    'eyes_inner_color' => '#10B981',
    'eyes_outer_color' => '#059669',
    'size' => 500,                    // Taille en pixels
    'margin' => 10,                   // Marge en pixels
    'ecc' => 'H'                      // Niveau de correction d'erreur
];
```

## Système de Cache

### Configuration
Le service implémente un système de cache pour optimiser les performances et réduire les appels API :

```php
protected const CACHE_PREFIX = 'qrcode_';
protected const CACHE_TTL = 86400; // 24 heures
```

### Fonctionnement
1. **Génération de la clé de cache** :
   ```php
   $cacheKey = $this->generateCacheKey($url, $name);
   // Génère une clé unique basée sur l'URL et le nom du QR code
   ```

2. **Stockage en cache** :
   - Lors de la création d'un QR code, son ID est automatiquement mis en cache
   - La durée de vie du cache est de 24 heures
   - La clé de cache est basée sur une combinaison de l'URL et du nom

3. **Récupération depuis le cache** :
   ```php
   $existingQrCodeId = Cache::get($cacheKey);
   if ($existingQrCodeId) {
       return $this->downloadQrCode($existingQrCodeId);
   }
   ```

4. **Gestion des erreurs** :
   - Si la récupération d'un QR code en cache échoue, la clé est supprimée
   - Un nouveau QR code est généré automatiquement
   - Le nouvel ID est mis en cache

### Avantages
- Réduction significative des appels API
- Amélioration des performances
- Économie de ressources
- Cohérence des QR codes pour une même URL

### Méthodes Liées au Cache

#### 1. Récupération d'un QR Code
```php
$qrCodeData = $geniusTools->getQrCode($qrCodeId);
```

#### 2. Génération de Clé de Cache
```php
protected function generateCacheKey(string $url, string $name): string
{
    return self::CACHE_PREFIX . md5($url . $name);
}
```

### Exemple d'Utilisation avec Cache
```php
// Le premier appel crée et met en cache le QR code
$qrCode1 = $geniusTools->generateDonationQrCode($url, $name);

// Les appels suivants (dans les 24h) réutilisent le QR code en cache
$qrCode2 = $geniusTools->generateDonationQrCode($url, $name);
```

## Gestion des Erreurs

Le service inclut une gestion complète des erreurs avec logging :

```php
try {
    $qrCode = $geniusTools->generateDonationQrCode($url, $name);
} catch (\Exception $e) {
    Log::error('GeniusToolsService - Error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    throw $e;
}
```

## Logs

Le service enregistre automatiquement les événements suivants :
- Création de QR code
- Récupération des informations du QR code
- Téléchargement du QR code
- Erreurs éventuelles

Les logs peuvent être consultés dans `storage/logs/laravel.log`.

## Format des Fichiers

Les QR codes sont générés au format SVG pour une qualité optimale. Le format SVG offre plusieurs avantages :
- Mise à l'échelle sans perte de qualité
- Taille de fichier réduite
- Support des gradients et effets avancés
- Compatibilité avec tous les navigateurs modernes

## Sécurité

Le service utilise :
- Authentication par token API
- HTTPS pour toutes les requêtes
- Validation des entrées
- Gestion sécurisée des fichiers temporaires

## Maintenance

### Gestion du Cache
Pour vider le cache des QR codes :
```bash
php artisan cache:clear
```

Pour mettre à jour un QR code spécifique :
1. Supprimez-le du cache
2. Régénérez-le

```php
$cacheKey = $geniusTools->generateCacheKey($url, $name);
Cache::forget($cacheKey);
$newQrCode = $geniusTools->generateDonationQrCode($url, $name);
```

### Mise à Jour de la Clé API
Pour mettre à jour la clé API :
1. Modifier la valeur dans le fichier `.env`
2. Vider le cache de configuration :
```bash
php artisan config:clear
