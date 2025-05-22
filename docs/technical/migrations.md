# Documentation des Migrations - GeniusWork

## Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Migrations Principales](#migrations-principales)
3. [Schéma de Base de Données](#schéma-de-base-de-données)
4. [Conventions](#conventions)

## Vue d'ensemble

Les migrations de GeniusWork suivent une structure chronologique et sont conçues pour supporter :
- Architecture multi-tenant
- Traçabilité complète
- Flexibilité des configurations
- Sécurité des données

### Ordre des Migrations
1. Tables de base (users, entreprises)
2. Tables de configuration (methode_pointages, politiques)
3. Tables opérationnelles (presences, trackings)
4. Tables de support (notifications, conges)

## Migrations Principales

### 1. Users Table
```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['super_admin', 'admin', 'entreprise', 'employeur']);
    $table->foreignUuid('entreprise_id')->nullable()->constrained();
    $table->foreignUuid('employeur_id')->nullable()->constrained();
    $table->enum('statut', ['actif', 'inactif'])->default('actif');
    $table->timestamp('last_login_at')->nullable();
    $table->json('settings')->nullable();
    $table->json('preferences')->nullable();
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
});
```

### 2. Entreprises Table
```php
Schema::create('entreprises', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('nom');
    $table->string('code')->unique();
    $table->string('email')->unique();
    $table->string('telephone');
    $table->enum('statut', ['actif', 'inactif'])->default('actif');
    $table->json('configuration')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### 3. Employeurs Table
```php
Schema::create('employeurs', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('entreprise_id')->constrained();
    $table->foreignUuid('departement_id')->constrained();
    $table->string('code_employe')->unique();
    $table->string('qr_code_secret')->unique();
    $table->timestamp('qr_code_expires_at')->nullable();
    $table->boolean('qr_code_active')->default(true);
    $table->string('nom');
    $table->string('prenom');
    $table->string('email')->unique();
    $table->string('telephone');
    $table->date('date_naissance');
    $table->date('date_embauche');
    $table->string('type_contrat');
    $table->enum('statut', ['actif', 'inactif'])->default('actif');
    $table->json('meta_donnees')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### 4. Methode Pointages Table
```php
Schema::create('methode_pointages', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('entreprise_id')->constrained();
    $table->string('nom');
    $table->string('code')->unique();
    $table->text('description')->nullable();
    $table->boolean('necessite_photo')->default(false);
    $table->boolean('necessite_geolocalisation')->default(true);
    $table->boolean('necessite_signature')->default(false);
    $table->boolean('necessite_validation')->default(false);
    $table->boolean('autoriser_hors_site')->default(false);
    $table->integer('rayon_geofencing')->default(100);
    $table->json('configuration')->nullable();
    $table->json('validation_regles')->nullable();
    $table->enum('statut', ['actif', 'inactif'])->default('actif');
    $table->timestamps();
    $table->softDeletes();
});
```

### 5. Trackings Table
```php
Schema::create('trackings', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('employeur_id')->constrained();
    $table->foreignUuid('presence_id')->nullable()->constrained();
    $table->enum('type', ['entree', 'sortie', 'pause_debut', 'pause_fin']);
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->string('adresse')->nullable();
    $table->datetime('date_heure');
    $table->string('methode_pointage');
    $table->string('appareil')->nullable();
    $table->string('adresse_ip')->nullable();
    $table->json('meta_donnees')->nullable();
    $table->enum('statut', ['valide', 'invalide', 'suspect'])->default('valide');
    $table->text('commentaire')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

## Schéma de Base de Données

### Indexes
```php
// Exemple d'indexes importants
$table->index(['entreprise_id', 'statut']);
$table->index(['employeur_id', 'date_heure']);
$table->index(['code_employe', 'qr_code_secret']);
```

### Contraintes
```php
// Exemple de contraintes
$table->unique(['entreprise_id', 'code']);
$table->foreign('validateur_id')->references('id')->on('users');
```

### Types de Données Communs
- UUID pour les IDs
- JSON pour les configurations flexibles
- ENUM pour les statuts fixes
- DECIMAL(10,8) pour latitude
- DECIMAL(11,8) pour longitude

## Conventions

### Nommage
- Tables : pluriel, snake_case
- Colonnes : snake_case
- Clés étrangères : singulier_id
- Index : idx_[table]_[colonne]

### Colonnes Standards
Chaque table inclut :
```php
$table->uuid('id')->primary();
$table->timestamps();
$table->softDeletes();
```

### Colonnes Communes
Plusieurs tables partagent :
```php
$table->enum('statut', ['actif', 'inactif']);
$table->json('configuration')->nullable();
$table->foreignUuid('entreprise_id')->constrained();
```

### Bonnes Pratiques
1. **Sécurité**
   - Utilisation systématique d'UUID
   - Contraintes référentielles
   - Soft deletes

2. **Performance**
   - Indexes appropriés
   - Types de données optimisés
   - Contraintes bien définies

3. **Maintenance**
   - Migrations réversibles
   - Documentation claire
   - Conventions cohérentes
