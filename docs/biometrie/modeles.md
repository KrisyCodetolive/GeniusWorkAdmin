# Modèles de données du Module Biométrie

## AppareilBiometrique

Le modèle `AppareilBiometrique` représente un appareil biométrique physique dans le système.

### Attributs

| Attribut | Type | Description |
|----------|------|-------------|
| `id` | UUID | Identifiant unique de l'appareil |
| `entreprise_id` | UUID | Référence à l'entreprise propriétaire |
| `site_id` | UUID | Référence au site où est installé l'appareil |
| `nom` | string | Nom convivial de l'appareil |
| `modele` | string | Modèle de l'appareil |
| `fabricant` | string | Fabricant de l'appareil |
| `numero_serie` | string | Numéro de série |
| `adresse_ip` | string | Adresse IP de l'appareil |
| `port` | integer | Port de communication |
| `protocole` | string | Protocole de communication (TCP, UDP, HTTP, etc.) |
| `identifiant_connexion` | string | Identifiant pour la connexion à l'appareil |
| `mot_de_passe` | string | Mot de passe pour la connexion (chiffré) |
| `cle_api` | string | Clé API pour les appareils utilisant une API REST |
| `configuration` | json | Configuration spécifique au modèle (JSON) |
| `dernier_sync` | datetime | Date de la dernière synchronisation manuelle |
| `derniere_sync_auto` | datetime | Date de la dernière synchronisation automatique |
| `statut` | enum | Statut de l'appareil (actif, inactif, maintenance, erreur) |
| `sync_auto_enabled` | boolean | Activation de la synchronisation automatique |
| `sync_logs_interval` | integer | Intervalle de synchronisation des logs (minutes) |
| `sync_users_interval` | integer | Intervalle de synchronisation des utilisateurs (minutes) |
| `sync_time_interval` | integer | Intervalle de synchronisation de l'heure (minutes) |
| `sync_options` | json | Options avancées de synchronisation (JSON) |
| `version_firmware` | string | Version du firmware de l'appareil |
| `capacite_empreintes` | integer | Capacité de stockage d'empreintes |
| `capacite_visages` | integer | Capacité de stockage de visages |
| `capacite_cartes` | integer | Capacité de stockage de cartes |
| `capacite_logs` | integer | Capacité de stockage de logs |
| `type_authentification` | string | Types d'authentification supportés |
| `options_disponibles` | json | Options disponibles sur l'appareil (JSON) |
| `parametres_avances` | json | Paramètres avancés (JSON) |
| `notes` | text | Notes et commentaires |

### Relations

- `entreprise()`: Relation BelongsTo avec le modèle `Entreprise`
- `site()`: Relation BelongsTo avec le modèle `Site`
- `logs()`: Relation HasMany avec le modèle `LogAppareilBiometrique`
- `pointages()`: Relation HasMany avec le modèle `Presence`
- `utilisateursEnregistres()`: Relation BelongsToMany avec le modèle `User`

### Scopes

- `scopeActif($query)`: Filtre les appareils actifs
- `scopeInactif($query)`: Filtre les appareils inactifs
- `scopeEnMaintenance($query)`: Filtre les appareils en maintenance
- `scopeEnErreur($query)`: Filtre les appareils en erreur
- `scopeParSite($query, $siteId)`: Filtre par site
- `scopeParModele($query, $modele)`: Filtre par modèle
- `scopeParFabricant($query, $fabricant)`: Filtre par fabricant
- `scopeSyncAuto($query)`: Filtre les appareils avec synchronisation automatique activée

### Méthodes

- `estActif()`: Vérifie si l'appareil est actif
- `estConnecte()`: Vérifie si l'appareil est connecté (synchronisé récemment)
- `supporteEmpreintes()`: Vérifie si l'appareil supporte les empreintes
- `supporteVisage()`: Vérifie si l'appareil supporte la reconnaissance faciale
- `supporteCarte()`: Vérifie si l'appareil supporte les cartes RFID
- `supporteCode()`: Vérifie si l'appareil supporte les codes PIN
- `getParametre($cle, $defaut)`: Récupère un paramètre avancé
- `setParametre($cle, $valeur)`: Définit un paramètre avancé
- `getAdresseComplete()`: Retourne l'adresse IP et le port
- `getUrlApi()`: Retourne l'URL de l'API pour les appareils HTTP
- `doitEtreSynchronise($type)`: Vérifie si l'appareil doit être synchronisé

## LogAppareilBiometrique

Le modèle `LogAppareilBiometrique` représente un événement ou une opération liée à un appareil biométrique.

### Attributs

| Attribut | Type | Description |
|----------|------|-------------|
| `id` | UUID | Identifiant unique du log |
| `appareil_biometrique_id` | UUID | Référence à l'appareil |
| `user_id` | UUID | Référence à l'utilisateur concerné (optionnel) |
| `type` | string | Type de log (connexion, pointage, erreur, etc.) |
| `donnees` | json | Données détaillées du log (JSON) |
| `statut` | string | Statut du log (success, error, warning, info) |
| `date_evenement` | datetime | Date et heure de l'événement |
| `traite` | boolean | Indique si le log a été traité |
| `date_traitement` | datetime | Date et heure du traitement |

### Relations

- `appareil()`: Relation BelongsTo avec le modèle `AppareilBiometrique`
- `user()`: Relation BelongsTo avec le modèle `User`
- `presence()`: Relation HasOne avec le modèle `Presence`

### Scopes

- `scopeNonTraite($query)`: Filtre les logs non traités
- `scopeParType($query, $type)`: Filtre par type de log
- `scopeParStatut($query, $statut)`: Filtre par statut
- `scopeParPeriode($query, $debut, $fin)`: Filtre par période

### Méthodes

- `marquerCommeTraite()`: Marque le log comme traité
- `convertirEnPointage()`: Convertit un log de pointage en entrée Presence
- `getDetailsFormates()`: Retourne les détails formatés pour affichage
