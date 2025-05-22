<?php

namespace App\Services\Biometrique\Protocols\Adapters;

use App\Services\Biometrique\Protocols\Interfaces\ConnectionAdapterInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Adaptateur pour les connexions HTTP/HTTPS
 */
class HttpAdapter implements ConnectionAdapterInterface
{
    /**
     * @var string
     */
    protected $baseUrl;
    
    /**
     * @var int
     */
    protected $timeout;
    
    /**
     * @var array
     */
    protected $headers = [];
    
    /**
     * @var array
     */
    protected $auth = [];
    
    /**
     * @var string
     */
    protected $lastError = '';
    
    /**
     * @var bool
     */
    protected $connected = false;
    
    /**
     * Constructeur
     *
     * @param string $baseUrl
     * @param int $timeout
     */
    public function __construct(string $baseUrl, int $timeout = 10)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }
    
    /**
     * Définit les en-têtes HTTP
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }
    
    /**
     * Ajoute un en-tête HTTP
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Définit l'authentification
     *
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function setAuth(string $username, string $password): self
    {
        $this->auth = [$username, $password];
        return $this;
    }
    
    /**
     * Établit une connexion
     *
     * @return bool
     */
    public function connect(): bool
    {
        try {
            // Vérifier que l'URL de base est accessible
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers)
                ->get($this->baseUrl);
            
            $this->connected = $response->successful();
            
            if (!$this->connected) {
                $this->lastError = "Connexion échouée: " . $response->status() . " " . $response->body();
            }
            
            return $this->connected;
        } catch (\Exception $e) {
            $this->lastError = "Exception lors de la connexion: " . $e->getMessage();
            Log::error('Erreur de connexion HTTP', [
                'url' => $this->baseUrl,
                'error' => $e->getMessage()
            ]);
            
            $this->connected = false;
            return false;
        }
    }
    
    /**
     * Ferme la connexion
     *
     * @return bool
     */
    public function disconnect(): bool
    {
        $this->connected = false;
        return true;
    }
    
    /**
     * Vérifie si la connexion est établie
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }
    
    /**
     * Envoie des données (requête POST)
     *
     * @param string $endpoint
     * @param array $data
     * @return string|null
     */
    public function post(string $endpoint, array $data): ?string
    {
        try {
            $url = $this->buildUrl($endpoint);
            
            $request = Http::timeout($this->timeout)
                ->withHeaders($this->headers);
            
            if (!empty($this->auth)) {
                $request = $request->withBasicAuth($this->auth[0], $this->auth[1]);
            }
            
            $response = $request->post($url, $data);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            $this->lastError = "Erreur HTTP: " . $response->status() . " " . $response->body();
            return null;
        } catch (\Exception $e) {
            $this->lastError = "Exception lors de la requête POST: " . $e->getMessage();
            Log::error('Erreur de requête HTTP POST', [
                'url' => $this->baseUrl . '/' . $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Envoie des données (requête GET)
     *
     * @param string $endpoint
     * @param array $params
     * @return string|null
     */
    public function get(string $endpoint, array $params = []): ?string
    {
        try {
            $url = $this->buildUrl($endpoint);
            
            $request = Http::timeout($this->timeout)
                ->withHeaders($this->headers);
            
            if (!empty($this->auth)) {
                $request = $request->withBasicAuth($this->auth[0], $this->auth[1]);
            }
            
            $response = $request->get($url, $params);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            $this->lastError = "Erreur HTTP: " . $response->status() . " " . $response->body();
            return null;
        } catch (\Exception $e) {
            $this->lastError = "Exception lors de la requête GET: " . $e->getMessage();
            Log::error('Erreur de requête HTTP GET', [
                'url' => $this->baseUrl . '/' . $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Envoie des données (requête PUT)
     *
     * @param string $endpoint
     * @param array $data
     * @return string|null
     */
    public function put(string $endpoint, array $data): ?string
    {
        try {
            $url = $this->buildUrl($endpoint);
            
            $request = Http::timeout($this->timeout)
                ->withHeaders($this->headers);
            
            if (!empty($this->auth)) {
                $request = $request->withBasicAuth($this->auth[0], $this->auth[1]);
            }
            
            $response = $request->put($url, $data);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            $this->lastError = "Erreur HTTP: " . $response->status() . " " . $response->body();
            return null;
        } catch (\Exception $e) {
            $this->lastError = "Exception lors de la requête PUT: " . $e->getMessage();
            Log::error('Erreur de requête HTTP PUT', [
                'url' => $this->baseUrl . '/' . $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Envoie des données (requête DELETE)
     *
     * @param string $endpoint
     * @param array $params
     * @return string|null
     */
    public function delete(string $endpoint, array $params = []): ?string
    {
        try {
            $url = $this->buildUrl($endpoint);
            
            $request = Http::timeout($this->timeout)
                ->withHeaders($this->headers);
            
            if (!empty($this->auth)) {
                $request = $request->withBasicAuth($this->auth[0], $this->auth[1]);
            }
            
            $response = $request->delete($url, $params);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            $this->lastError = "Erreur HTTP: " . $response->status() . " " . $response->body();
            return null;
        } catch (\Exception $e) {
            $this->lastError = "Exception lors de la requête DELETE: " . $e->getMessage();
            Log::error('Erreur de requête HTTP DELETE', [
                'url' => $this->baseUrl . '/' . $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Envoie des données
     * 
     * Note: Cette méthode est implémentée pour respecter l'interface ConnectionAdapterInterface
     * mais elle n'est pas utilisée directement. Utilisez plutôt les méthodes get(), post(), etc.
     *
     * @param string $data
     * @return bool
     */
    public function send(string $data): bool
    {
        $this->lastError = "Méthode send() non supportée pour l'adaptateur HTTP. Utilisez get(), post(), etc.";
        return false;
    }
    
    /**
     * Reçoit des données
     * 
     * Note: Cette méthode est implémentée pour respecter l'interface ConnectionAdapterInterface
     * mais elle n'est pas utilisée directement. Utilisez plutôt les méthodes get(), post(), etc.
     *
     * @param int $length
     * @param bool $waitForComplete
     * @return string|null
     */
    public function receive(int $length = 1024, bool $waitForComplete = false): ?string
    {
        $this->lastError = "Méthode receive() non supportée pour l'adaptateur HTTP. Utilisez get(), post(), etc.";
        return null;
    }
    
    /**
     * Construit une URL complète
     *
     * @param string $endpoint
     * @return string
     */
    protected function buildUrl(string $endpoint): string
    {
        return $this->baseUrl . '/' . ltrim($endpoint, '/');
    }
    
    /**
     * Récupère la dernière erreur
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }
}
