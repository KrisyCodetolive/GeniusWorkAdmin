# Documentation Technique : Fonctionnalité d'Impersonation

## Vue d'ensemble

La fonctionnalité d'impersonation ("Se connecter en tant que") permet aux utilisateurs ayant les rôles SuperAdmin et Support de se connecter temporairement avec le compte d'un administrateur d'entreprise sans connaître ses identifiants. Cette fonctionnalité est essentielle pour le support technique et la résolution de problèmes spécifiques à une entreprise.

## Architecture

L'implémentation suit une architecture modulaire qui s'intègre parfaitement avec le système d'authentification de Laravel et l'interface d'administration Filament.

### Composants principaux

1. **Widgets Filament** : 
   - `ImpersonateWidget` : Interface utilisateur pour sélectionner l'entreprise et l'utilisateur à impersoner
   - `ImpersonationStatusWidget` : Widget affichant l'état d'impersonation et permettant de revenir au compte original
2. **Middleware** : Gestion de l'état d'impersonation et partage des informations avec les vues
3. **Routes** : Points d'entrée pour les actions d'impersonation
4. **Vues** : Affichage des widgets d'impersonation et de l'état d'impersonation

## Diagramme de flux

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  SuperAdmin ou  │     │   Sélection     │     │  Impersonation  │
│     Support     │────▶│  d'entreprise   │────▶│    activée      │
│                 │     │  et utilisateur │     │                 │
└─────────────────┘     └─────────────────┘     └────────┬────────┘
                                                          │
                                                          ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Retour au     │     │   Clic sur      │     │  Widget d'état  │
│  compte initial │◀────│  "Revenir à     │◀────│  d'impersonation│
│                 │     │   mon compte"   │     │                 │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

## Implémentation détaillée

### 1. Widget de sélection (`ImpersonateWidget`)

Le widget est le point d'entrée principal pour les utilisateurs SuperAdmin et Support. Il n'est visible que pour ces rôles spécifiques.

**Localisation** : `app/Filament/Widgets/ImpersonateWidget.php`

**Fonctionnalités clés** :
- Formulaire de sélection d'entreprise et d'utilisateur sur une même ligne (Grid layout)
- Filtrage des utilisateurs en fonction de l'entreprise sélectionnée
- Recherche d'utilisateurs avec fonctionnalité de recherche intégrée
- Bouton "Se connecter" centré pour une meilleure ergonomie
- Gestion robuste de la session avec `Session::flush()` et `Session::regenerate(true)`

**Méthodes principales** :
- `form()` : Définit le formulaire de sélection avec Grid layout
- `loadUsers()` : Récupère les utilisateurs filtrés par entreprise
- `impersonate()` : Démarre l'impersonation avec gestion sécurisée de la session
- `stopImpersonating()` : Arrête l'impersonation et restaure l'utilisateur original

### 2. Widget d'état d'impersonation (`ImpersonationStatusWidget`)

Ce widget s'affiche uniquement lorsqu'une session d'impersonation est active, permettant à l'utilisateur de voir son état d'impersonation et de revenir facilement à son compte original.

**Localisation** : `app/Filament/Widgets/ImpersonationStatusWidget.php`

**Fonctionnalités clés** :
- Affichage conditionnel uniquement pendant l'impersonation (`canView()`)
- Affichage des informations de l'utilisateur original (nom et rôle)
- Bouton "Revenir à mon compte" pour arrêter l'impersonation
- Gestion indépendante de la session pour une meilleure fiabilité

**Méthodes principales** :
- `canView()` : Détermine si le widget doit être affiché
- `getOriginalUserData()` : Récupère les informations de l'utilisateur original
- `stopImpersonating()` : Arrête l'impersonation avec gestion sécurisée de la session

### 3. Vues des widgets

Les vues gèrent l'affichage des widgets d'impersonation et d'état d'impersonation.

**Localisations** :
- `resources/views/filament/widgets/impersonate-widget.blade.php` : Vue du widget de sélection
- `resources/views/filament/widgets/impersonation-status-widget.blade.php` : Vue du widget d'état

**Caractéristiques** :
- Interface utilisateur intuitive et responsive
- Mise en page optimisée avec les sélections sur une même ligne
- Bouton "Se connecter" centré pour une meilleure ergonomie
- Indicateurs visuels clairs de l'état d'impersonation

### 4. Intégration avec Filament

Les widgets sont intégrés dans le tableau de bord Filament via le provider d'administration.

**Localisation** : `app/Providers/Filament/AdminPanelProvider.php`

**Configuration** :
- Enregistrement des widgets dans le tableau de bord
- Gestion des autorisations d'affichage des widgets

## Stockage des données

L'impersonation utilise la session Laravel pour stocker les informations suivantes :

- `impersonate_origin_id` : ID de l'utilisateur original
- `impersonate_origin_role` : Rôle de l'utilisateur original

Aucune donnée persistante n'est stockée en base de données.

## Sécurité

### Restrictions d'accès

- Seuls les utilisateurs avec les rôles SuperAdmin et Support peuvent utiliser cette fonctionnalité
- Vérification à plusieurs niveaux (widget, méthodes d'impersonation)
- Impossibilité de s'impersoner soi-même

### Gestion de session

- Utilisation de `Session::flush()` pour nettoyer complètement la session
- Régénération de session avec `Session::regenerate(true)` pour éviter les problèmes de sécurité
- Utilisation de `Auth::loginUsingId()` pour une connexion plus directe et sécurisée

### Journalisation

- Toutes les actions d'impersonation sont journalisées
- Les informations journalisées incluent l'ID de l'utilisateur original, l'ID de l'utilisateur impersoné, et les détails pertinents
- Journalisation du début et de la fin de l'impersonation

### Indicateurs visuels

- Widget d'état d'impersonation clairement visible sur le tableau de bord
- Informations sur l'utilisateur original toujours accessibles
- Bouton pour revenir à son compte original toujours disponible

## Interaction avec les autres fonctionnalités

### Compatibilité avec le scope EntrepriseScope

La fonctionnalité d'impersonation est compatible avec le scope global `EntrepriseScope` qui filtre les données par entreprise. Lorsqu'un SuperAdmin ou Support se connecte en tant qu'administrateur d'une entreprise, il est soumis aux mêmes restrictions que cet administrateur, assurant ainsi une expérience utilisateur authentique.

### Compatibilité avec le middleware HasEntreprise

Le middleware `HasEntreprise` continue de fonctionner normalement pendant l'impersonation. L'utilisateur impersoné est traité comme l'utilisateur authentifié, avec toutes les restrictions associées à son rôle et à son entreprise.

## Cas d'utilisation

1. **Support technique** : Un agent de support peut se connecter en tant qu'administrateur d'entreprise pour reproduire et résoudre un problème spécifique.
2. **Formation** : Un SuperAdmin peut se connecter en tant qu'administrateur d'entreprise pour former de nouveaux utilisateurs.
3. **Audit** : Un SuperAdmin peut vérifier que les restrictions d'accès fonctionnent correctement pour différents rôles.

## Limitations

- L'impersonation ne fonctionne que pour les utilisateurs avec les rôles 'admin' et 'entreprise'
- L'impersonation est temporaire et se termine à la fin de la session
- Les actions effectuées pendant l'impersonation sont attribuées à l'utilisateur impersoné, pas à l'utilisateur original

## Extension future

- Ajout d'une option pour impersoner des utilisateurs avec d'autres rôles
- Implémentation d'une durée maximale d'impersonation
- Ajout d'une notification à l'utilisateur impersoné
- Journalisation plus détaillée des actions effectuées pendant l'impersonation
