<?php

namespace App\Models;

use App\Traits\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorieDepense extends Model
{
    use BelongsToEntreprise, HasUuids, SoftDeletes;

    protected $table = 'categories_depense';

    protected $fillable = [
        'entreprise_id',
        'nom',
        'code_comptable',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function demandes()
    {
        return $this->hasMany(DemandeDepense::class);
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
