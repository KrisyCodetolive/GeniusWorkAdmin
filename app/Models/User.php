<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
class User extends Authenticatable implements HasAvatar, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasUuids, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pin',
        'telephone',
        'photo',
        'role',
        'statut',
        'langue',
        'fuseau_horaire',
        'settings',
        'preferences',
        'employeur_id',
        'entreprise_id',
        'email_verified_at',
        'telephone_verified_at',
        'pin_changed_at',
        'last_login_at',
        'last_login_ip',
        'two_factor_enabled',
        'two_factor_verified'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'telephone_verified_at' => 'datetime',
        'pin_changed_at' => 'datetime',
        'last_login_at' => 'datetime',
        'settings' => 'json',
        'preferences' => 'json',
        'two_factor_enabled' => 'boolean',
        'two_factor_verified' => 'boolean',
    ];

    /**
     * Retourne l'URL de l'avatar pour Filament
     * 
     * @return string|null
     */
    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'photo');
        
        if (!$this->$avatarColumn) {
            return null;
        }
        
        // Si l'URL commence par http, c'est déjà une URL complète
        if (str_starts_with($this->$avatarColumn, 'http')) {
            return $this->$avatarColumn;
        }
        
        // Sinon, on construit l'URL à partir du stockage
        return Storage::disk(config('filament-edit-profile.disk', 'public'))->url($this->$avatarColumn);
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (!empty($user->pin)) {
                $user->pin = Hash::make($user->pin);
                $user->pin_changed_at = now();
            }
        });
    }

    // Relations
    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function validations()
    {
        return $this->hasMany(Presence::class, 'validateur_id')
            ->union($this->hasMany(Supplementaire::class, 'validateur_id'))
            ->union($this->hasMany(Permutation::class, 'validateur_id'));
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    /**
     * Relation avec les notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Relation avec les notifications non lues
     */
    public function unreadNotifications()
    {
        return $this->notifications()->where('lu', false)->whereNull('date_lecture');
    }

    /**
     * Accesseur pour obtenir les notifications non lues comme une collection
     * Ceci permet d'utiliser auth()->user()->unreadNotifications->count()
     */
    public function getUnreadNotificationsAttribute()
    {
        return $this->unreadNotifications()->get();
    }

    /**
     * Relation avec les informations d'identification WebAuthn.
     */
    public function webAuthnCredentials()
    {
        return $this->hasMany(WebAuthnCredential::class);
    }

    // Phone & OTP Methods
    public function routeNotificationForVonage()
    {
        return $this->telephone;
    }

    public function hasVerifiedPhone()
    {
        return !is_null($this->telephone_verified_at);
    }

    public function markPhoneAsVerified()
    {
        return $this->forceFill([
            'telephone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    // PIN Management Methods
    protected function pin(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Hash::make($value),
        );
    }

    public function setPin($pin)
    {
        $this->pin = $pin;
        $this->pin_changed_at = now();
        $this->require_pin_change = false;
        $this->pin_attempts = 0;
        $this->pin_locked_until = null;
        $this->save();
    }

    public function verifyPin($pin)
    {
        if ($this->isPinLocked()) {
            return false;
        }

        if (!Hash::check($pin, $this->pin)) {
            $this->incrementPinAttempts();
            return false;
        }

        $this->resetPinAttempts();
        return true;
    }

    public function isPinLocked()
    {
        if ($this->pin_locked_until && $this->pin_locked_until->isFuture()) {
            return true;
        }

        if ($this->pin_locked_until && $this->pin_locked_until->isPast()) {
            $this->resetPinAttempts();
        }

        return false;
    }

    protected function incrementPinAttempts()
    {
        $this->increment('pin_attempts');

        if ($this->pin_attempts >= 5) {
            $this->pin_locked_until = now()->addMinutes(30);
            $this->save();
        }
    }

    protected function resetPinAttempts()
    {
        $this->pin_attempts = 0;
        $this->pin_locked_until = null;
        $this->save();
    }

    public function requirePinChange()
    {
        return $this->require_pin_change;
    }

    public function forcePinChange()
    {
        $this->require_pin_change = true;
        $this->save();
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

    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeParEntreprise($query, $entreprise_id)
    {
        return $query->where('entreprise_id', $entreprise_id);
    }

    public function scopeRecentsConnectes($query, $days = 30)
    {
        return $query->whereNotNull('derniere_connexion')
                    ->where('derniere_connexion', '>=', now()->subDays($days));
    }

    // Helpers
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isEmployeur()
    {
        return $this->role === 'employeur';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isEntreprise()
    {
        return $this->role === 'entreprise';
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isSupport()
    {
        return $this->role === 'support';
    }

    public function canValidate()
    {
        return in_array($this->role, ['admin', 'entreprise', 'super_admin']);
    }

    public function hasPermissionFor($action)
    {
        $permissions = [
            'admin' => ['manage_users', 'validate_presence', 'manage_settings'],
            'employeur' => ['view_schedule', 'request_permutation'],
            'entreprise' => ['manage_employees', 'validate_presence', 'manage_settings'],
            'super_admin' => ['*']
        ];

        return $this->role === 'super_admin' || 
               (isset($permissions[$this->role]) && in_array($action, $permissions[$this->role]));
    }

    public function updateLastLogin()
    {
        $this->update(['derniere_connexion' => now()]);
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    public function getSetting($key, $default = null)
    {
        return ($this->settings ?? [])[$key] ?? $default;
    }

    public function setPreference($key, $value)
    {
        $preferences = $this->preferences ?? [];
        $preferences[$key] = $value;
        $this->preferences = $preferences;
        $this->save();
    }

    public function getPreference($key, $default = null)
    {
        return ($this->preferences ?? [])[$key] ?? $default;
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isSuperAdmin() || $this->isSupport() || $this->isAdmin() ;
    }
}
