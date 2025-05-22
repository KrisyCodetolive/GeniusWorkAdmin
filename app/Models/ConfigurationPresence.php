<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class ConfigurationPresence extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'configurations_presence';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'entreprise_id',
        'heures_supplementaires_actives',
        'nombre_pointages_par_jour',
        'pauses_actives',
        'annuler_presence_sans_sortie',
        'delai_annulation_heures',
        'notifications_actives',
        'notification_absence',
        'notification_retard',
        'notification_conge',
        'message_absence',
        'message_retard',
        'message_conge',
        'configuration_avancee',
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'heures_supplementaires_actives' => 'boolean',
        'pauses_actives' => 'boolean',
        'annuler_presence_sans_sortie' => 'boolean',
        'notifications_actives' => 'boolean',
        'notification_absence' => 'boolean',
        'notification_retard' => 'boolean',
        'notification_conge' => 'boolean',
        'configuration_avancee' => 'json',
    ];

    /**
     * Les attributs qui doivent être cachés pour les tableaux.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Obtient l'entreprise associée à cette configuration.
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Obtient la valeur d'une configuration avancée spécifique.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfigurationAvancee($key, $default = null)
    {
        $config = $this->configuration_avancee;
        
        if (is_array($config) && array_key_exists($key, $config)) {
            return $config[$key];
        }
        
        return $default;
    }

    /**
     * Définit la valeur d'une configuration avancée spécifique.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setConfigurationAvancee($key, $value)
    {
        $config = $this->configuration_avancee ?: [];
        $config[$key] = $value;
        $this->configuration_avancee = $config;
        
        return $this;
    }
}
