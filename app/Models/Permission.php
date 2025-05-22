<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends SpatiePermission
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'groupe',
        'meta_data',
        'is_system',
        'statut'
    ];

    protected $casts = [
        'meta_data' => 'json',
        'is_system' => 'boolean'
    ];

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    public function scopeParGroupe($query, $groupe)
    {
        return $query->where('groupe', $groupe);
    }

    // Helpers
    public function isSystem()
    {
        return $this->is_system;
    }

    public function isCustom()
    {
        return !$this->is_system;
    }

    public function estActif()
    {
        return $this->statut === 'actif';
    }

    public function getMeta($key, $default = null)
    {
        return ($this->meta_data ?? [])[$key] ?? $default;
    }

    public function setMeta($key, $value)
    {
        $meta = $this->meta_data ?? [];
        $meta[$key] = $value;
        $this->meta_data = $meta;
        $this->save();
    }
}
