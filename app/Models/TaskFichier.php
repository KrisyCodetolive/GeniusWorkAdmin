<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class TaskFichier extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'task_id',
        'user_id',
        'nom',
        'chemin',
        'taille',
        'type_mime',
        'description',
        'est_livrable',
        'meta_donnees',
    ];

    protected $casts = [
        'taille' => 'integer',
        'est_livrable' => 'boolean',
        'meta_donnees' => 'json',
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeLivrable($query)
    {
        return $query->where('est_livrable', true);
    }

    public function scopeNonLivrable($query)
    {
        return $query->where('est_livrable', false);
    }

    public function scopeParType($query, $typeMime)
    {
        return $query->where('type_mime', 'LIKE', $typeMime . '%');
    }

    // Méthodes
    public function getTailleFormateeAttribute()
    {
        $taille = $this->taille;
        
        if ($taille < 1024) {
            return $taille . ' B';
        } elseif ($taille < 1048576) {
            return round($taille / 1024, 2) . ' KB';
        } elseif ($taille < 1073741824) {
            return round($taille / 1048576, 2) . ' MB';
        } else {
            return round($taille / 1073741824, 2) . ' GB';
        }
    }

    public function getEstImageAttribute()
    {
        return strpos($this->type_mime, 'image/') === 0;
    }

    public function getEstDocumentAttribute()
    {
        $docTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        
        return in_array($this->type_mime, $docTypes);
    }

    public function marquerCommeLivrable()
    {
        $this->est_livrable = true;
        $this->save();
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
