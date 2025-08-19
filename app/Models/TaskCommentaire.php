<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class TaskCommentaire extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'task_id',
        'user_id',
        'contenu',
        'est_prive',
        'parent_id',
    ];

    protected $casts = [
        'est_prive' => 'boolean',
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

    public function parent()
    {
        return $this->belongsTo(TaskCommentaire::class, 'parent_id');
    }

    public function reponses()
    {
        return $this->hasMany(TaskCommentaire::class, 'parent_id');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('est_prive', false);
    }

    public function scopePrive($query)
    {
        return $query->where('est_prive', true);
    }

    public function scopeParentSeulement($query)
    {
        return $query->whereNull('parent_id');
    }

    // Méthodes
    public function getEstParentAttribute()
    {
        return $this->parent_id === null;
    }

    public function getNombreReponsesAttribute()
    {
        return $this->reponses()->count();
    }
}
