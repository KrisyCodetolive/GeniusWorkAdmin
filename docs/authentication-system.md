# Documentation Technique du Système d'Authentification GENIUS WORK

## Vue d'ensemble

Le système d'authentification GENIUS WORK est une solution complète et flexible qui offre plusieurs méthodes d'authentification, incluant l'email traditionnel avec mot de passe et l'authentification par téléphone avec code OTP (One-Time Password). Cette documentation détaille l'architecture, les composants et le flux de travail du système.

## Architecture

Le système d'authentification suit une architecture MVC (Modèle-Vue-Contrôleur) et utilise des traits PHP pour la réutilisation du code. Il est construit sur les fondations suivantes :

### Structure des dossiers

```
app/
├── Foundation/
│   └── Auth/                  # Traits d'authentification personnalisés
├── Http/
│   ├── Controllers/
│   │   └── Auth/              # Contrôleurs d'authentification
│   ├── Middleware/            # Middleware d'authentification
│   └── Requests/
│       └── Auth/              # Classes de requêtes de validation
├── Services/
│   └── OtpService.php         # Service de gestion des OTP
└── Models/
    └── User.php               # Modèle utilisateur
resources/
└── views/
    └── app/
        └── auth/              # Vues d'authentification
```

## Composants principaux

### Traits d'authentification (app/Foundation/Auth/)

Les traits fournissent des fonctionnalités réutilisables pour les différents aspects de l'authentification :

1. **AuthenticatesUsers.php** : Gère le processus de connexion des utilisateurs
2. **RegistersUsers.php** : Gère le processus d'inscription
3. **SendsPasswordResetEmails.php** : Gère l'envoi d'emails de réinitialisation de mot de passe
4. **ResetsPasswords.php** : Gère le processus de réinitialisation de mot de passe
5. **VerifiesEmails.php** : Gère la vérification des adresses email
6. **ConfirmsPasswords.php** : Gère la confirmation de mot de passe pour les actions sensibles
7. **RedirectsUsers.php** : Gère les redirections après authentification
8. **ThrottlesLogins.php** : Limite les tentatives de connexion pour prévenir les attaques par force brute

### Contrôleurs d'authentification (app/Http/Controllers/Auth/)

Les contrôleurs utilisent les traits pour implémenter les fonctionnalités d'authentification :

1. **LoginController.php** : Gère la connexion par email/mot de passe et par téléphone/OTP
2. **RegisterController.php** : Gère l'inscription par email et par téléphone
3. **ForgotPasswordController.php** : Gère les demandes de réinitialisation de mot de passe
4. **ResetPasswordController.php** : Gère la réinitialisation de mot de passe
5. **EmailVerificationController.php** : Gère la vérification des adresses email
6. **ConfirmPasswordController.php** : Gère la confirmation de mot de passe
7. **PhoneVerificationController.php** : Gère la vérification des numéros de téléphone

### Service OTP (app/Services/OtpService.php)

Le service OTP fournit des fonctionnalités pour générer, stocker et vérifier les codes OTP :

```php
class OtpService
{
    // Génère un OTP pour un identifiant donné
    public function generateOtp(string $identifier): string;
    
    // Vérifie si l'OTP fourni correspond à celui stocké
    public function verifyOtp(string $identifier, string $otp): bool;
    
    // Envoie l'OTP à l'utilisateur
    public function sendOtp(string $recipient, string $otp): bool;
}
```

### Middleware (app/Http/Middleware/)

Les middleware contrôlent l'accès aux routes en fonction de l'état d'authentification :

1. **Authenticate.php** : Vérifie si l'utilisateur est authentifié
2. **RedirectIfAuthenticated.php** : Redirige les utilisateurs déjà authentifiés
3. **PhoneVerification.php** : Vérifie si le téléphone de l'utilisateur est vérifié

### Vues (resources/views/app/auth/)

Les vues fournissent l'interface utilisateur pour les différentes fonctionnalités d'authentification :

1. **login.blade.php** : Formulaire de connexion par email
2. **phone-login.blade.php** : Formulaire de connexion par téléphone
3. **register.blade.php** : Formulaire d'inscription
4. **forgot-password.blade.php** : Formulaire de demande de réinitialisation de mot de passe
5. **reset-password.blade.php** : Formulaire de réinitialisation de mot de passe
6. **verify-email.blade.php** : Page de vérification d'email
7. **verify-phone.blade.php** : Page de vérification de téléphone
8. **confirm-password.blade.php** : Formulaire de confirmation de mot de passe

## Routes d'authentification

Les routes sont organisées en deux groupes principaux : celles accessibles aux invités et celles accessibles aux utilisateurs authentifiés.

### Routes pour les invités (middleware 'guest')

```php
// Routes de connexion
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/send-otp', [LoginController::class, 'sendOtp'])->name('login.send-otp');
Route::post('/verify-otp', [LoginController::class, 'verifyOtp'])->name('login.verify-otp');

// Routes de connexion par téléphone
Route::get('/login/phone', [LoginController::class, 'showPhoneLoginForm'])->name('login.phone');
Route::post('/login/phone/send-otp', [LoginController::class, 'sendOtp'])->name('login.phone.send-otp');
Route::post('/login/phone/verify-otp', [LoginController::class, 'loginWithOtp'])->name('login.phone.verify-otp');

// Routes d'inscription
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);
Route::get('/register-phone', [RegisterController::class, 'createWithPhone'])->name('register.phone');
Route::post('/register-phone/send-otp', [RegisterController::class, 'sendRegistrationOtp'])->name('register.send-otp');
Route::post('/register-phone/verify-otp', [RegisterController::class, 'verifyRegistrationOtp'])->name('register.verify-otp');

// Routes de réinitialisation de mot de passe
Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
```

### Routes pour les utilisateurs authentifiés (middleware 'auth')

```php
// Route de déconnexion
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes de vérification d'email
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
    ->middleware('throttle:6,1')
    ->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
    ->middleware('throttle:6,1')
    ->name('verification.send');

// Routes de confirmation de mot de passe
Route::get('/confirm-password', [ConfirmPasswordController::class, 'show'])
    ->name('password.confirm');
Route::post('/confirm-password', [ConfirmPasswordController::class, 'store']);

// Routes de vérification de téléphone
Route::get('/verify-phone', [PhoneVerificationController::class, 'show'])
    ->name('phone.verification.notice');
Route::post('/verify-phone', [PhoneVerificationController::class, 'send'])
    ->name('phone.verification.send');
Route::post('/verify-phone/verify', [PhoneVerificationController::class, 'verify'])
    ->name('phone.verification.verify');
```

## Flux d'authentification

### Connexion par email

1. L'utilisateur accède à la page de connexion (`/login`)
2. L'utilisateur saisit son email et son mot de passe
3. Le système vérifie les identifiants
4. Si les identifiants sont valides, l'utilisateur est connecté et redirigé vers le tableau de bord
5. Si les identifiants sont invalides, l'utilisateur est redirigé vers la page de connexion avec un message d'erreur

### Connexion par téléphone

1. L'utilisateur accède à la page de connexion par téléphone (`/login/phone`)
2. L'utilisateur saisit son numéro de téléphone
3. Le système génère un OTP et l'envoie au numéro de téléphone
4. L'utilisateur saisit l'OTP reçu
5. Le système vérifie l'OTP
6. Si l'OTP est valide, l'utilisateur est connecté et redirigé vers le tableau de bord
7. Si l'OTP est invalide, l'utilisateur est redirigé vers la page de connexion par téléphone avec un message d'erreur

### Inscription

1. L'utilisateur accède à la page d'inscription (`/register`)
2. L'utilisateur saisit ses informations (nom, email, mot de passe, etc.)
3. Le système vérifie les informations et crée un nouveau compte
4. L'utilisateur est connecté et redirigé vers le tableau de bord
5. Un email de vérification est envoyé à l'adresse email de l'utilisateur

### Inscription par téléphone

1. L'utilisateur accède à la page d'inscription par téléphone (`/register-phone`)
2. L'utilisateur saisit ses informations, y compris son numéro de téléphone
3. Le système génère un OTP et l'envoie au numéro de téléphone
4. L'utilisateur saisit l'OTP reçu
5. Le système vérifie l'OTP
6. Si l'OTP est valide, le compte est créé, l'utilisateur est connecté et redirigé vers le tableau de bord

### Réinitialisation de mot de passe

1. L'utilisateur accède à la page de mot de passe oublié (`/forgot-password`)
2. L'utilisateur saisit son adresse email
3. Le système envoie un email avec un lien de réinitialisation de mot de passe
4. L'utilisateur clique sur le lien dans l'email
5. L'utilisateur est redirigé vers la page de réinitialisation de mot de passe (`/reset-password/{token}`)
6. L'utilisateur saisit un nouveau mot de passe
7. Le système met à jour le mot de passe et connecte l'utilisateur

### Vérification d'email

1. L'utilisateur reçoit un email avec un lien de vérification
2. L'utilisateur clique sur le lien dans l'email
3. Le système vérifie le lien et marque l'email comme vérifié
4. L'utilisateur est redirigé vers le tableau de bord avec un message de confirmation

### Vérification de téléphone

1. L'utilisateur accède à la page de vérification de téléphone (`/verify-phone`)
2. L'utilisateur saisit son numéro de téléphone
3. Le système génère un OTP et l'envoie au numéro de téléphone
4. L'utilisateur saisit l'OTP reçu
5. Le système vérifie l'OTP et marque le téléphone comme vérifié
6. L'utilisateur est redirigé vers le tableau de bord avec un message de confirmation

## Sécurité

Le système d'authentification implémente plusieurs mesures de sécurité :

1. **Hachage des mots de passe** : Les mots de passe sont hachés à l'aide de l'algorithme bcrypt
2. **Protection CSRF** : Toutes les requêtes POST sont protégées contre les attaques CSRF
3. **Limitation de débit** : Les tentatives de connexion sont limitées pour prévenir les attaques par force brute
4. **Validation des entrées** : Toutes les entrées utilisateur sont validées
5. **Jetons signés** : Les emails de vérification et de réinitialisation de mot de passe utilisent des jetons signés
6. **Sessions sécurisées** : Les sessions sont régénérées après la connexion pour prévenir la fixation de session
7. **OTP à usage unique** : Les codes OTP sont à usage unique et ont une durée de validité limitée

## Personnalisation

Le système d'authentification est conçu pour être facilement personnalisable :

1. **Redirection après authentification** : La propriété `$redirectTo` dans les contrôleurs peut être modifiée pour changer la destination après authentification
2. **Validation** : Les règles de validation peuvent être personnalisées dans les contrôleurs
3. **Vues** : Les vues peuvent être personnalisées pour correspondre au design de l'application
4. **OTP** : La longueur et la durée de validité des OTP peuvent être configurées dans le service OTP

## Conclusion

Le système d'authentification GENIUS WORK offre une solution complète et flexible pour l'authentification des utilisateurs. Il prend en charge plusieurs méthodes d'authentification, implémente des mesures de sécurité robustes et est facilement personnalisable pour répondre aux besoins spécifiques de l'application.
