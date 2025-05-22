# Protocoles et Adaptateurs Biométriques

Le module Biométrie utilise une architecture flexible pour communiquer avec différents types d'appareils biométriques via divers protocoles de communication.

## Architecture des protocoles

```
Protocols/
├── AbstractBiometriqueProtocol.php
├── ProtocolFactory.php
├── Adapters/
│   ├── HttpAdapter.php
│   └── SocketAdapter.php
├── Interfaces/
│   ├── BiometriqueProtocolInterface.php
│   └── ConnectionAdapterInterface.php
└── Implementations/
    ├── AnvizProtocol.php
    ├── GenericHttpProtocol.php
    ├── HikVisionProtocol.php
    └── ZKTecoProtocol.php
```

## Interfaces

### BiometriqueProtocolInterface

Interface principale que tous les protocoles doivent implémenter.

```php
interface BiometriqueProtocolInterface
{
    public function connect(): bool;
    public function disconnect(): bool;
    public function isConnected(): bool;
    
    public function getDeviceInfo(): array;
    public function setDeviceTime(Carbon $time): bool;
    public function restart(): bool;
    
    public function getUsers(): array;
    public function addUser(array $userData): bool;
    public function deleteUser(string $userId): bool;
    
    public function getLogs(?Carbon $fromDate = null): array;
    public function clearLogs(): bool;
    
    public function getAttendance(?Carbon $fromDate = null): array;
    public function clearAttendance(): bool;
    
    public function setParameter(string $name, $value): bool;
    public function getParameter(string $name);
}
```

### ConnectionAdapterInterface

Interface pour les adaptateurs de connexion.

```php
interface ConnectionAdapterInterface
{
    public function connect(string $host, int $port, array $options = []): bool;
    public function disconnect(): bool;
    public function isConnected(): bool;
    public function send($data);
    public function receive();
    public function setTimeout(int $seconds): void;
}
```

## Classe abstraite

### AbstractBiometriqueProtocol

Classe abstraite fournissant des fonctionnalités communes à tous les protocoles.

```php
abstract class AbstractBiometriqueProtocol implements BiometriqueProtocolInterface
{
    protected $adapter;
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $options;
    protected $connected = false;
    
    public function __construct(
        ConnectionAdapterInterface $adapter,
        string $host,
        int $port,
        string $username = '',
        string $password = '',
        array $options = []
    );
    
    public function connect(): bool;
    public function disconnect(): bool;
    public function isConnected(): bool;
    
    // Méthodes communes...
    
    // Méthodes abstraites à implémenter par les protocoles spécifiques
    abstract protected function executeConnect(): bool;
    abstract protected function executeDisconnect(): bool;
    // ...
}
```

## Adaptateurs de connexion

### HttpAdapter

Adaptateur pour la communication via HTTP/HTTPS.

```php
class HttpAdapter implements ConnectionAdapterInterface
{
    protected $client;
    protected $connected = false;
    protected $baseUrl;
    
    public function __construct(?Client $client = null);
    public function connect(string $host, int $port, array $options = []): bool;
    public function disconnect(): bool;
    public function isConnected(): bool;
    public function send($data);
    public function receive();
    public function setTimeout(int $seconds): void;
}
```

### SocketAdapter

Adaptateur pour la communication via sockets TCP/UDP.

```php
class SocketAdapter implements ConnectionAdapterInterface
{
    protected $socket;
    protected $connected = false;
    protected $protocol;
    
    public function __construct(string $protocol = 'tcp');
    public function connect(string $host, int $port, array $options = []): bool;
    public function disconnect(): bool;
    public function isConnected(): bool;
    public function send($data);
    public function receive();
    public function setTimeout(int $seconds): void;
}
```

## Implémentations de protocoles

### ZKTecoProtocol

Implémentation du protocole pour les appareils ZKTeco.

```php
class ZKTecoProtocol extends AbstractBiometriqueProtocol
{
    // Constantes spécifiques au protocole ZKTeco
    const CMD_CONNECT = 1000;
    const CMD_EXIT = 1001;
    const CMD_ENABLEDEVICE = 1002;
    // ...
    
    protected function executeConnect(): bool;
    protected function executeDisconnect(): bool;
    
    public function getDeviceInfo(): array;
    public function setDeviceTime(Carbon $time): bool;
    public function restart(): bool;
    
    public function getUsers(): array;
    public function addUser(array $userData): bool;
    public function deleteUser(string $userId): bool;
    
    public function getLogs(?Carbon $fromDate = null): array;
    public function clearLogs(): bool;
    
    // Méthodes utilitaires spécifiques à ZKTeco
    protected function createHeader($command, $sessionId, $replyId);
    protected function checksum($buffer);
    protected function parseRecordData($data);
    // ...
}
```

### HikVisionProtocol

Implémentation du protocole pour les appareils HikVision.

```php
class HikVisionProtocol extends AbstractBiometriqueProtocol
{
    // Implémentation spécifique pour HikVision...
}
```

### AnvizProtocol

Implémentation du protocole pour les appareils Anviz.

```php
class AnvizProtocol extends AbstractBiometriqueProtocol
{
    // Implémentation spécifique pour Anviz...
}
```

### GenericHttpProtocol

Implémentation générique pour les appareils exposant une API HTTP.

```php
class GenericHttpProtocol extends AbstractBiometriqueProtocol
{
    // Implémentation générique basée sur HTTP...
}
```

## Factory de protocoles

### ProtocolFactory

Factory pour créer l'instance de protocole appropriée selon le type d'appareil.

```php
class ProtocolFactory
{
    public function createForDevice(AppareilBiometrique $appareil): BiometriqueProtocolInterface
    {
        $adapter = $this->createAdapter($appareil->protocole);
        
        switch ($appareil->fabricant) {
            case 'ZKTeco':
                return new ZKTecoProtocol(
                    $adapter,
                    $appareil->adresse_ip,
                    $appareil->port,
                    $appareil->identifiant_connexion,
                    $appareil->mot_de_passe,
                    $appareil->configuration ?? []
                );
                
            case 'HikVision':
                return new HikVisionProtocol(
                    $adapter,
                    $appareil->adresse_ip,
                    $appareil->port,
                    $appareil->identifiant_connexion,
                    $appareil->mot_de_passe,
                    $appareil->configuration ?? []
                );
                
            case 'Anviz':
                return new AnvizProtocol(
                    $adapter,
                    $appareil->adresse_ip,
                    $appareil->port,
                    $appareil->identifiant_connexion,
                    $appareil->mot_de_passe,
                    $appareil->configuration ?? []
                );
                
            default:
                if ($appareil->protocole === 'HTTP' || $appareil->protocole === 'HTTPS') {
                    return new GenericHttpProtocol(
                        $adapter,
                        $appareil->adresse_ip,
                        $appareil->port,
                        $appareil->identifiant_connexion,
                        $appareil->mot_de_passe,
                        $appareil->configuration ?? []
                    );
                }
                
                throw new \Exception("Protocole non supporté pour le fabricant: {$appareil->fabricant}");
        }
    }
    
    protected function createAdapter(string $protocole): ConnectionAdapterInterface
    {
        switch ($protocole) {
            case 'HTTP':
            case 'HTTPS':
                return new HttpAdapter();
                
            case 'TCP':
            case 'UDP':
                return new SocketAdapter($protocole === 'TCP' ? 'tcp' : 'udp');
                
            default:
                throw new \Exception("Type d'adaptateur non supporté: {$protocole}");
        }
    }
}
```

## Exemples d'utilisation

### Utilisation via le service AppareilBiometriqueService

```php
$appareilService = app(AppareilBiometriqueService::class);
$appareil = AppareilBiometrique::find('uuid-appareil');

// Le service utilise la factory en interne
$result = $appareilService->testerConnexion($appareil);
```

### Utilisation directe

```php
$factory = app(ProtocolFactory::class);
$appareil = AppareilBiometrique::find('uuid-appareil');

$protocol = $factory->createForDevice($appareil);

try {
    if ($protocol->connect()) {
        $deviceInfo = $protocol->getDeviceInfo();
        $users = $protocol->getUsers();
        $logs = $protocol->getLogs(now()->subDays(7));
        
        $protocol->disconnect();
        
        return [
            'success' => true,
            'deviceInfo' => $deviceInfo,
            'userCount' => count($users),
            'logCount' => count($logs)
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Impossible de se connecter à l\'appareil'
    ];
} catch (\Exception $e) {
    return [
        'success' => false,
        'message' => $e->getMessage()
    ];
}
```

## Extension du système

Pour ajouter le support d'un nouveau fabricant d'appareils:

1. Créer une nouvelle classe implémentant `BiometriqueProtocolInterface` ou étendant `AbstractBiometriqueProtocol`
2. Implémenter les méthodes spécifiques au protocole
3. Ajouter le nouveau protocole à la factory
