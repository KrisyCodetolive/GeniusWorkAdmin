<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Entreprise;
use App\Traits\BelongsToEntreprise;

class ParametresNotification extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'notifier_absences',
        'notifier_retards',
        'notifier_conges',
        'notifier_heures_supplementaires',
        'notifier_permutations',
        'activer_notifications_email',
        'activer_notifications_sms',
        'activer_notifications_push',
        'modeles_email',
        'modeles_sms',
        'configuration_email',
        'configuration_sms',
        'destinataires_supplementaires',
        'regles_notification'
    ];

    protected $casts = [
        'notifier_absences' => 'boolean',
        'notifier_retards' => 'boolean',
        'notifier_conges' => 'boolean',
        'notifier_heures_supplementaires' => 'boolean',
        'notifier_permutations' => 'boolean',
        'activer_notifications_email' => 'boolean',
        'activer_notifications_sms' => 'boolean',
        'activer_notifications_push' => 'boolean',
        'modeles_email' => 'json',
        'modeles_sms' => 'json',
        'configuration_email' => 'json',
        'configuration_sms' => 'json',
        'destinataires_supplementaires' => 'json',
        'regles_notification' => 'json'
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Helpers
    public function doitNotifier($type)
    {
        $mapping = [
            'absence' => 'notifier_absences',
            'retard' => 'notifier_retards',
            'conge' => 'notifier_conges',
            'supplementaire' => 'notifier_heures_supplementaires',
            'permutation' => 'notifier_permutations'
        ];

        return $mapping[$type] ? $this->{$mapping[$type]} : false;
    }

    public function canauxActifs()
    {
        $canaux = [];
        if ($this->activer_notifications_email) $canaux[] = 'email';
        if ($this->activer_notifications_sms) $canaux[] = 'sms';
        if ($this->activer_notifications_push) $canaux[] = 'push';
        return $canaux;
    }

    public function getModeleEmail($type)
    {
        $modeles = $this->modeles_email ?? [];
        return $modeles[$type] ?? null;
    }

    public function getModeleSms($type)
    {
        $modeles = $this->modeles_sms ?? [];
        return $modeles[$type] ?? null;
    }

    public function getDestinatairesSupplementaires($type)
    {
        $destinataires = $this->destinataires_supplementaires ?? [];
        return $destinataires[$type] ?? [];
    }

    public function verifierRegleNotification($type, $donnees)
    {
        $regles = $this->regles_notification ?? [];
        if (!isset($regles[$type])) return true;

        // Logique de vérification des règles
        return true;
    }

    public function setModeleEmail($type, $contenu)
    {
        $modeles = $this->modeles_email ?? [];
        $modeles[$type] = $contenu;
        $this->modeles_email = $modeles;
        $this->save();
    }

    public function setModeleSms($type, $contenu)
    {
        $modeles = $this->modeles_sms ?? [];
        $modeles[$type] = $contenu;
        $this->modeles_sms = $modeles;
        $this->save();
    }
}
