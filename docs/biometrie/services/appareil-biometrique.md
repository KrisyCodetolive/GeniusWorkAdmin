# Service de Gestion des Appareils Biométriques

Le service `AppareilBiometriqueService` est responsable de la gestion des appareils biométriques et de la communication avec ces appareils via les protocoles appropriés.

## Fonctionnalités principales

- Création, mise à jour et suppression d'appareils
- Communication avec les appareils via les protocoles spécifiques
- Gestion des opérations de base (redémarrage, test de connexion)
- Configuration des paramètres des appareils
- Gestion des utilisateurs sur les appareils

## Interface du service

```php
class AppareilBiometriqueService
{
    public function __construct(
        ProtocolFactory $protocolFactory,
        LogAppareilBiometriqueService $logService
    );
    
    public function creerAppareil(array $data): AppareilBiometrique;
    public function mettreAJourAppareil(AppareilBiometrique $appareil, array $data): AppareilBiometrique;
    public function supprimerAppareil(AppareilBiometrique $appareil): bool;
    
    public function testerConnexion(AppareilBiometrique $appareil): array;
    public function redemarrerAppareil(AppareilBiometrique $appareil): array;
    public function obtenirInformations(AppareilBiometrique $appareil): array;
    
    public function ajouterUtilisateur(AppareilBiometrique $appareil, User $user, array $options): array;
    public function supprimerUtilisateur(AppareilBiometrique $appareil, User $user): array;
    public function synchroniserUtilisateurs(AppareilBiometrique $appareil, Collection $users): array;
    
    public function configurerParametres(AppareilBiometrique $appareil, array $parametres): array;
    public function obtenirLogs(AppareilBiometrique $appareil, ?Carbon $depuis = null): array;
    public function effacerLogs(AppareilBiometrique $appareil): array;
    
    protected function getProtocol(AppareilBiometrique $appareil): BiometriqueProtocolInterface;
}
```

## Méthodes détaillées

### creerAppareil

Crée un nouvel appareil biométrique dans le système.

**Paramètres:**
- `$data` (array): Données de l'appareil

**Retourne:**
- `AppareilBiometrique`: Instance de l'appareil créé

### mettreAJourAppareil

Met à jour un appareil existant.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil
- `$data` (array): Nouvelles données

**Retourne:**
- `AppareilBiometrique`: Instance de l'appareil mis à jour

### supprimerAppareil

Supprime un appareil du système.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `bool`: Succès de l'opération

### testerConnexion

Teste la connexion avec un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Résultat du test de connexion

### redemarrerAppareil

Redémarre un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Résultat de l'opération

### obtenirInformations

Récupère les informations détaillées d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Informations de l'appareil

### ajouterUtilisateur

Ajoute un utilisateur à un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil
- `$user` (User): Utilisateur à ajouter
- `$options` (array): Options d'enregistrement (empreinte, visage, etc.)

**Retourne:**
- `array`: Résultat de l'opération

### supprimerUtilisateur

Supprime un utilisateur d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil
- `$user` (User): Utilisateur à supprimer

**Retourne:**
- `array`: Résultat de l'opération

### synchroniserUtilisateurs

Synchronise une liste d'utilisateurs avec un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil
- `$users` (Collection): Collection d'utilisateurs

**Retourne:**
- `array`: Résultat de la synchronisation

### configurerParametres

Configure les paramètres d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil
- `$parametres` (array): Paramètres à configurer

**Retourne:**
- `array`: Résultat de l'opération

### obtenirLogs

Récupère les logs d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil
- `$depuis` (Carbon|null): Date de début pour les logs

**Retourne:**
- `array`: Logs récupérés

### effacerLogs

Efface les logs d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Résultat de l'opération

### getProtocol

Obtient l'instance du protocole approprié pour un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `BiometriqueProtocolInterface`: Instance du protocole

## Exemples d'utilisation

### Création d'un appareil

```php
$appareilService = app(AppareilBiometriqueService::class);

$data = [
    'entreprise_id' => 'uuid-entreprise',
    'site_id' => 'uuid-site',
    'nom' => 'Pointeuse Entrée Principale',
    'modele' => 'ZK-F18',
    'fabricant' => 'ZKTeco',
    'adresse_ip' => '192.168.1.100',
    'port' => 4370,
    'protocole' => 'TCP',
    'identifiant_connexion' => 'admin',
    'mot_de_passe' => 'password123',
    'statut' => 'actif'
];

$appareil = $appareilService->creerAppareil($data);
```

### Test de connexion

```php
$appareilService = app(AppareilBiometriqueService::class);
$appareil = AppareilBiometrique::find('uuid-appareil');

$result = $appareilService->testerConnexion($appareil);

if ($result['success']) {
    echo "Connexion réussie!";
} else {
    echo "Erreur de connexion: " . $result['message'];
}
```

### Récupération des logs

```php
$appareilService = app(AppareilBiometriqueService::class);
$appareil = AppareilBiometrique::find('uuid-appareil');

// Récupérer les logs depuis hier
$depuis = now()->subDay();
$logs = $appareilService->obtenirLogs($appareil, $depuis);

echo "Nombre de logs récupérés: " . count($logs['logs']);
```

## Gestion des erreurs

Le service utilise un système de retour standardisé pour les opérations:

```php
[
    'success' => true|false,
    'message' => 'Message descriptif',
    'data' => [] // Données supplémentaires spécifiques à l'opération
]
```

En cas d'erreur de communication avec l'appareil, le service journalise l'erreur et met à jour le statut de l'appareil si nécessaire.
