<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use App\Traits\BelongsToEntreprise;
use App\Services\GeniusToolsService;
use Illuminate\Support\Facades\Log;

class Employeur extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'departement_id',
        'filiale_id',
        'matricule',
        'code_employe',
        'qr_code_secret',
        'qr_code_expires_at',
        'qr_code_active',
        'qr_code_id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'date_naissance',
        'lieu_naissance',
        'genre',
        'photo',
        'date_embauche',
        'type_contrat',
        'statut',
        'meta_donnees',
        'poste',
        'salaire_base',
        'configuration'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_embauche' => 'date',
        'qr_code_expires_at' => 'datetime',
        'qr_code_active' => 'boolean',
        'meta_donnees' => 'json',
        'configuration' => 'json',
    ];

    public function getNomCompletAttribute()
    {
        return "{$this->prenom} {$this->nom}";
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employeur) {
            $employeur->code_employe = $employeur->generateEmployeeCode();
            $employeur->matricule = $employeur->generateMatricule();
            $employeur->generateQRCodeSecret();
        });
    }

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    /**
     * Relation avec la filiale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function filiale()
    {
        return $this->belongsTo(Filiale::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function supplementaires()
    {
        return $this->hasMany(Supplementaire::class);
    }

    public function permutations()
    {
        return $this->hasMany(Permutation::class);
    }

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }

    public function trackings()
    {
        return $this->hasMany(Tracking::class);
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    public function scopeParDepartement($query, $departement_id)
    {
        return $query->where('departement_id', $departement_id);
    }

    public function scopeParEntreprise($query, $entreprise_id)
    {
        return $query->where('entreprise_id', $entreprise_id);
    }

    // Code Generation Methods
    protected function generateEmployeeCode()
    {
        return self::genererCodeEmploye($this->nom, $this->prenom, $this->entreprise_id);
    }

    /**
     * Génère un code employé unique
     * 
     * @param string $nom Nom de l'employé
     * @param string $prenom Prénom de l'employé
     * @param int $entrepriseId ID de l'entreprise
     * @return string Code unique généré
     */
    public static function genererCodeEmploye($nom, $prenom, $entrepriseId)
    {
        // Obtenir le code de l'entreprise
        $entreprise = Entreprise::find($entrepriseId);
        
        if (!$entreprise) {
            Log::error('Entreprise not found for employee code generation', [
                'entreprise_id' => $entrepriseId
            ]);
            throw new \RuntimeException('Cannot generate employee code: Entreprise not found');
        }

        $entrepriseCode = $entreprise->code;
        if (empty($entrepriseCode)) {
            Log::error('Entreprise code is empty', [
                'entreprise_id' => $entrepriseId,
                'entreprise_name' => $entreprise->nom
            ]);
            throw new \RuntimeException('Cannot generate employee code: Entreprise code is empty');
        }
        
        // Créer un préfixe à partir des initiales du nom et prénom
        $prefix = 'EMP';
        $initiales = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        
        // Trouver le dernier code utilisé avec ce préfixe dans cette entreprise
        $dernierCode = self::where('entreprise_id', $entrepriseId)
            ->where('code_employe', 'LIKE', "{$prefix}-{$entrepriseCode}-{$initiales}%")
            ->orderBy('code_employe', 'desc')
            ->value('code_employe');
        
        $year = date('y');
        
        if ($dernierCode) {
            // Extraire le numéro séquentiel et l'incrémenter
            $pattern = "/{$prefix}-{$entrepriseCode}-{$initiales}{$year}(\d+)/";
            if (preg_match($pattern, $dernierCode, $matches)) {
                $sequence = (int)$matches[1] + 1;
            } else {
                $sequence = 1;
            }
        } else {
            $sequence = 1;
        }
        
        // Formater le numéro séquentiel sur 4 chiffres
        $sequenceFormatted = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$entrepriseCode}-{$initiales}{$year}{$sequenceFormatted}";
    }

    /**
     * Génère un matricule unique
     * 
     * @param string $nom Nom de l'employé
     * @param string $prenom Prénom de l'employé
     * @param int $entrepriseId ID de l'entreprise
     * @return string Matricule unique généré
     */
    public static function genererMatricule($nom, $prenom, $entrepriseId)
    {
        // Obtenir le code de l'entreprise
        $entreprise = Entreprise::find($entrepriseId);
        
        if (!$entreprise) {
            return 'MAT-' . strtoupper(Str::random(8));
        }

        $entrepriseCode = $entreprise->code ?? 'ENT';
        
        // Créer un préfixe à partir des initiales du nom et prénom
        $initiales = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        
        // Trouver le dernier matricule utilisé avec ce préfixe dans cette entreprise
        $dernierMatricule = self::where('entreprise_id', $entrepriseId)
            ->where('matricule', 'LIKE', "MAT-{$entrepriseCode}-{$initiales}%")
            ->orderBy('matricule', 'desc')
            ->value('matricule');
        
        if ($dernierMatricule) {
            // Extraire le numéro séquentiel et l'incrémenter
            $pattern = "/MAT-{$entrepriseCode}-{$initiales}(\d+)/";
            if (preg_match($pattern, $dernierMatricule, $matches)) {
                $sequence = (int)$matches[1] + 1;
            } else {
                $sequence = 1;
            }
        } else {
            $sequence = 1;
        }
        
        // Formater le numéro séquentiel sur 3 chiffres
        $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        return "MAT-{$entrepriseCode}-{$initiales}{$sequenceFormatted}";
    }

    protected function generateMatricule()
    {
        return self::genererMatricule($this->nom, $this->prenom, $this->entreprise_id);
    }

    public function generateQRCodeSecret()
    {
        $this->qr_code_secret = Str::random(32);
        $this->qr_code_expires_at = now()->addDays(30);
        $this->qr_code_active = true;

        // Générer le QR code via GeniusTools si l'employé a un ID
        if ($this->id) {
            try {
                $geniusToolsService = app(GeniusToolsService::class);
                $verificationUrl = route('employe.verification', ['code' => $this->qr_code_secret]);
                $qrName = "QR Code - {$this->nom_complet}";
                
                // Options de style standardisées
                $styleOptions = [
                    'foreground_gradient_one' => '#3a5faa',
                    'foreground_gradient_two' => '#324f88',
                    'eyes_inner_color' => '#3a5faa',
                    'eyes_outer_color' => '#324f88',
                ];
                
                // Utiliser la méthode getOrCreateQrCode pour récupérer ou créer un QR code
                $qrCodeId = $geniusToolsService->getOrCreateQrCode($verificationUrl, $qrName, $styleOptions);
                
                if ($qrCodeId) {
                    $this->qr_code_id = $qrCodeId;
                    Log::info('Employeur - QR code généré avec succès', [
                        'employeur_id' => $this->id,
                        'qr_code_id' => $qrCodeId
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erreur lors de la génération du QR code via GeniusTools', [
                    'message' => $e->getMessage(),
                    'employeur_id' => $this->id
                ]);
                // On continue même en cas d'erreur, le QR code sera généré à la volée lors de l'affichage
            }
        }

        return $this->qr_code_secret;
    }

    public function rotateQRCode()
    {
        // Générer un nouveau secret
        $this->qr_code_secret = Str::random(32);
        $this->qr_code_expires_at = now()->addDays(30);
        $this->qr_code_active = true;

        // Mettre à jour le QR code via GeniusTools
        try {
            $geniusToolsService = app(GeniusToolsService::class);
            $verificationUrl = route('employe.verification', ['code' => $this->qr_code_secret]);
            $qrName = "QR Code - {$this->nom_complet}";
            
            // Options de style standardisées
            $styleOptions = [
                'foreground_gradient_one' => '#3a5faa',
                'foreground_gradient_two' => '#324f88',
                'eyes_inner_color' => '#3a5faa',
                'eyes_outer_color' => '#324f88',
            ];
            
            // Utiliser la méthode getOrCreateQrCode pour récupérer ou créer un QR code
            $qrCodeId = $geniusToolsService->getOrCreateQrCode($verificationUrl, $qrName, $styleOptions);
            
            if ($qrCodeId) {
                $this->qr_code_id = $qrCodeId;
                Log::info('Employeur - QR code mis à jour avec succès', [
                    'employeur_id' => $this->id,
                    'qr_code_id' => $qrCodeId
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du QR code via GeniusTools', [
                'message' => $e->getMessage(),
                'employeur_id' => $this->id
            ]);
            // On continue même en cas d'erreur, le QR code sera généré à la volée lors de l'affichage
        }

        return $this->qr_code_secret;
    }

    public function getQRCodeData()
    {
        if (!$this->qr_code_active || 
            ($this->qr_code_expires_at && $this->qr_code_expires_at->isPast())) {
            return null;
        }

        return [
            'type' => 'employee_qr',
            'code_employe' => $this->code_employe,
            'secret' => $this->qr_code_secret,
            'expires_at' => $this->qr_code_expires_at?->toIso8601String()
        ];
    }

    public function validateQRCode($secret)
    {
        if (!$this->qr_code_active || 
            $this->qr_code_secret !== $secret || 
            ($this->qr_code_expires_at && $this->qr_code_expires_at->isPast())) {
            return false;
        }

        return true;
    }

    public function deactivateQRCode()
    {
        $this->qr_code_active = false;
        $this->save();
    }

    // Helpers
    public function getNomComplet()
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function estActif()
    {
        return $this->statut === 'actif';
    }

    public function getAnciennete()
    {
        return $this->date_embauche?->diffInYears(now());
    }

    public function getMeta($key, $default = null)
    {
        return ($this->meta_donnees ?? [])[$key] ?? $default;
    }

    public function setMeta($key, $value)
    {
        $meta = $this->meta_donnees ?? [];
        $meta[$key] = $value;
        $this->meta_donnees = $meta;
        $this->save();
    }
}
