<?php

namespace App\Traits;

use App\Services\Sync\SyncService;
use Illuminate\Database\Eloquent\Model;

/**
 * Envoie chaque création, modification ou suppression du modèle à l'autre instance (voir config/sync.php).
 */
trait Synchronisable
{
    protected static function bootSynchronisable(): void
    {
        $enregistrer = fn (Model $model) => app(SyncService::class)
            ->enregistrerChangement($model->getTable(), (string) $model->getKey(), $model->getAttributes());

        static::saved($enregistrer);
        static::deleted($enregistrer);
    }
}
