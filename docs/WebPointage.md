# Documentation WebPointage

## Table des matières

1. [Introduction](#introduction)
2. [Architecture](#architecture)
3. [Modèles de données](#modèles-de-données)
4. [Fonctionnalités](#fonctionnalités)
5. [Flux d'utilisation](#flux-dutilisation)
6. [Sécurité](#sécurité)
7. [API](#api)
8. [Interface utilisateur](#interface-utilisateur)
9. [Configuration](#configuration)
10. [Dépannage](#dépannage)

## Introduction

WebPointage est une fonctionnalité avancée du système GENIUS WORK qui permet aux employés d'enregistrer leur présence de manière sécurisée et précise via une interface web. Cette solution combine plusieurs technologies modernes pour offrir une expérience utilisateur fluide tout en garantissant l'intégrité des données de pointage.

### Objectifs

- Simplifier le processus de pointage des employés
- Garantir l'authenticité des pointages grâce à la biométrie (WebAuthn)
- Vérifier la localisation des employés lors du pointage
- Fournir un historique détaillé des présences
- Permettre différents types de pointages (entrée, sortie, pause)
- Générer des statistiques sur le temps de travail

### Avantages

- **Sécurité renforcée** : Authentification biométrique via WebAuthn
- **Précision géographique** : Vérification de la position par géolocalisation
- **Flexibilité** : Plusieurs types de pointages (entrée, sortie, pause)
- **Traçabilité** : Enregistrement détaillé des informations de pointage
- **Accessibilité** : Fonctionne sur tous les appareils modernes

## Architecture

WebPointage s'intègre au sein de l'architecture GENIUS WORK et utilise le modèle MVC (Modèle-Vue-Contrôleur) de Laravel.

### Composants principaux

- **Modèles** : `Presence`, `WebAuthnCredential`, `Site`, `User`
- **Contrôleurs** : `WebPointageController`, `WebAuthnController`
- **Vues** : Interface utilisateur pour le pointage et la consultation de l'historique

### Diagramme de flux

```
┌─────────────┐      ┌────────────────┐      ┌─────────────────┐
│ Utilisateur │ ──▶ │ WebPointage UI │ ──▶ │ WebAuthn (FIDO2) │
└─────────────┘      └────────────────┘      └─────────────────┘
                            │                          │
                            ▼                          ▼
                    ┌────────────────┐      ┌─────────────────┐
                    │ Géolocalisation│ ──▶ │ Enregistrement  │
                    └────────────────┘      │   Présence      │
                                            └─────────────────┘
```

## Modèles de données

### Presence

Le modèle `Presence` est au cœur du système WebPointage. Il remplace l'ancien modèle `Pointage` et offre des fonctionnalités étendues.

#### Attributs principaux

| Attribut | Type | Description |
|----------|------|-------------|
| `user_id` | UUID | Identifiant de l'utilisateur |
| `site_id` | UUID | Identifiant du site |
| `methode_pointage_id` | UUID | Méthode de pointage utilisée |
| `type` | Enum | Type de pointage (entrée, sortie, pause_debut, pause_fin) |
| `date_heure` | DateTime | Date et heure du pointage |
| `latitude` | Decimal | Latitude de l'emplacement |
| `longitude` | Decimal | Longitude de l'emplacement |
| `precision_geo` | Decimal | Précision de la géolocalisation |
| `adresse_ip` | String | Adresse IP de l'utilisateur |
| `appareil` | String | Appareil utilisé |
| `navigateur` | String | Navigateur utilisé |
| `distance_site` | Decimal | Distance par rapport au site (en mètres) |
| `verification_data` | JSON | Données de vérification du pointage |
| `webauthn_credential_id` | UUID | Identifiant de l'authentification WebAuthn |

#### Relations

- `user()` : Relation avec l'utilisateur
- `site()` : Relation avec le site
- `methodePointage()` : Relation avec la méthode de pointage
- `webAuthnCredential()` : Relation avec les informations d'identification WebAuthn

#### Scopes

- `webPointage()` : Filtre les pointages effectués via WebPointage
- `entree()`, `sortie()` : Filtre par type de pointage
- `pause()`, `pauseDebut()`, `pauseFin()` : Filtre les pointages de pause
- `parMethode()`, `parSite()` : Filtre par méthode ou site

#### Méthodes utilitaires

- `getTempsPause()` : Calcule le temps de pause
- `verifierGeolocalisation()` : Vérifie si l'utilisateur est dans le périmètre du site
- `calculerDistance()` : Calcule la distance entre deux points géographiques
- `isHorsSite()` : Vérifie si l'utilisateur est hors du site

### WebAuthnCredential

Ce modèle gère les informations d'identification WebAuthn pour l'authentification biométrique.

#### Attributs principaux

| Attribut | Type | Description |
|----------|------|-------------|
| `user_id` | UUID | Identifiant de l'utilisateur |
| `credential_id` | String | Identifiant de l'information d'identification |
| `public_key` | String | Clé publique |
| `counter` | Integer | Compteur d'utilisation |
| `name` | String | Nom de l'appareil |
| `type` | String | Type d'authentification (platform, cross-platform) |
| `device_type` | String | Type d'appareil |
| `is_active` | Boolean | État d'activation |
| `last_used_at` | DateTime | Date de dernière utilisation |

#### Méthodes

- `markAsUsed()` : Marque l'information d'identification comme utilisée
- `disable()`, `enable()` : Désactive ou active l'information d'identification
- `isActive()` : Vérifie si l'information d'identification est active

## Fonctionnalités

### 1. Pointage par QR Code

Les utilisateurs peuvent scanner un QR code spécifique à un site pour accéder à l'interface de pointage.

#### Processus

1. Un administrateur génère un QR code pour un site spécifique
2. L'employé scanne le QR code avec son appareil
3. L'interface de pointage s'affiche avec les informations du site
4. L'employé s'authentifie via WebAuthn et effectue son pointage

### 2. Authentification biométrique (WebAuthn)

WebPointage utilise la norme WebAuthn (FIDO2) pour l'authentification biométrique, offrant une sécurité renforcée.

#### Caractéristiques

- Support des authentificateurs intégrés (Touch ID, Face ID, Windows Hello)
- Support des authentificateurs externes (clés de sécurité USB)
- Vérification de l'authenticité des informations d'identification
- Protection contre le phishing et les attaques par rejeu

### 3. Vérification de la géolocalisation

Le système vérifie la position géographique de l'utilisateur lors du pointage pour s'assurer qu'il se trouve bien sur le site.

#### Fonctionnement

1. Récupération des coordonnées GPS de l'utilisateur
2. Calcul de la distance entre l'utilisateur et le site
3. Vérification que la distance est inférieure au rayon de geofencing défini pour le site
4. Enregistrement de la distance et de la précision dans les données de pointage

### 4. Types de pointages

WebPointage prend en charge plusieurs types de pointages pour une gestion complète du temps de travail.

#### Types disponibles

- **Entrée** : Début de la journée de travail
- **Sortie** : Fin de la journée de travail
- **Début de pause** : Début d'une période de pause
- **Fin de pause** : Fin d'une période de pause

### 5. Historique et statistiques

L'interface d'historique permet aux utilisateurs de consulter leurs pointages et d'obtenir des statistiques sur leur temps de travail.

#### Statistiques disponibles

- Nombre total d'heures travaillées
- Nombre de jours travaillés
- Temps total de pause
- Moyenne d'heures par jour

## Flux d'utilisation

### Enregistrement d'un appareil WebAuthn

1. L'utilisateur accède à la page de gestion des appareils WebAuthn
2. Il clique sur "Ajouter un nouvel appareil"
3. Il saisit un nom pour l'appareil
4. Le système génère des options d'enregistrement
5. L'utilisateur confirme avec son authentificateur (empreinte, visage, etc.)
6. L'appareil est enregistré et peut être utilisé pour les pointages

### Pointage d'entrée

1. L'utilisateur scanne le QR code du site
2. Il accède à l'interface de pointage
3. Le système récupère sa position géographique
4. L'utilisateur s'authentifie via WebAuthn
5. Il clique sur le bouton "Entrée"
6. Le système vérifie l'authentification et la position
7. Le pointage est enregistré et confirmé

### Consultation de l'historique

1. L'utilisateur accède à la page d'historique
2. Il peut filtrer par date et type de pointage
3. Le système affiche la liste des pointages correspondants
4. Si des filtres de date sont appliqués, des statistiques sont calculées et affichées

## Sécurité

WebPointage intègre plusieurs mécanismes de sécurité pour garantir l'authenticité des pointages.

### Authentification WebAuthn

- Utilisation de clés cryptographiques asymétriques
- Vérification de l'origine de la requête
- Protection contre les attaques par rejeu grâce au compteur
- Stockage sécurisé des informations d'identification

### Vérification de la géolocalisation

- Calcul précis de la distance entre l'utilisateur et le site
- Prise en compte de la précision de la géolocalisation
- Configuration du rayon de geofencing par site

### Journalisation

- Enregistrement de l'adresse IP
- Enregistrement de l'appareil et du navigateur
- Stockage des données de vérification pour audit

## API

### Endpoints principaux

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/webPointage/show/{token}` | GET | Affiche l'interface de pointage pour un QR code |
| `/webPointage/pointage` | POST | Enregistre un pointage |
| `/webPointage/historique` | GET | Affiche l'historique des pointages |
| `/webPointage/generate-qr` | POST | Génère un QR code pour un site |
| `/webPointage/download-qr/{token}` | GET | Télécharge un QR code au format PNG |
| `/webPointage/print-qr/{token}` | GET | Affiche une version imprimable du QR code |

### Endpoints WebAuthn

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/webauthn` | GET | Affiche la page de gestion des appareils WebAuthn |
| `/webauthn/register/options` | POST | Génère les options d'enregistrement |
| `/webauthn/register` | POST | Enregistre une nouvelle information d'identification |
| `/webauthn/authenticate/options` | POST | Génère les options d'authentification |
| `/webauthn/authenticate` | POST | Vérifie l'authentification |
| `/webauthn/rename/{id}` | PUT | Renomme une information d'identification |
| `/webauthn/delete/{id}` | DELETE | Supprime une information d'identification |

## Interface utilisateur

### Page de pointage

L'interface de pointage est conçue pour être intuitive et réactive, offrant une expérience utilisateur optimale sur tous les appareils.

#### Composants

- **En-tête** : Affiche les informations de l'entreprise et du site
- **Horloge** : Affiche l'heure actuelle
- **Statut** : Indique le statut actuel de l'utilisateur (présent, absent, en pause)
- **Boutons de pointage** : Permettent d'effectuer les différents types de pointages
- **Informations** : Affichent le statut de la géolocalisation et de l'authentification

### Page d'historique

L'interface d'historique permet de consulter et de filtrer les pointages, ainsi que de visualiser des statistiques.

#### Composants

- **Filtres** : Permettent de filtrer par date et type de pointage
- **Statistiques** : Affichent des statistiques sur le temps de travail
- **Tableau des pointages** : Liste les pointages avec leurs détails
- **Pagination** : Permet de naviguer entre les pages de résultats

## Configuration

### Paramètres de WebPointage

Les paramètres de WebPointage sont configurés dans la méthode de pointage associée à l'entreprise.

#### Options configurables

- **max_devices_per_user** : Nombre maximum d'appareils WebAuthn par utilisateur
- **geofencing_enabled** : Activation de la vérification de la géolocalisation
- **default_radius** : Rayon de geofencing par défaut (en mètres)
- **webauthn_required** : Obligation d'utiliser WebAuthn pour le pointage

### Configuration par site

Chaque site peut avoir ses propres paramètres de géolocalisation.

#### Options configurables

- **has_geofencing** : Activation du geofencing pour le site
- **latitude**, **longitude** : Coordonnées géographiques du site
- **rayon_geofencing** : Rayon de geofencing spécifique au site (en mètres)

## Dépannage

### Problèmes courants

#### Erreur de géolocalisation

- Vérifier que la géolocalisation est activée sur l'appareil
- Vérifier que l'autorisation de géolocalisation est accordée au navigateur
- S'assurer que l'utilisateur se trouve à proximité du site

#### Erreur d'authentification WebAuthn

- Vérifier que l'appareil est compatible avec WebAuthn
- S'assurer que l'utilisateur a enregistré au moins un appareil
- Vérifier que l'appareil utilisé est actif

#### QR code invalide ou expiré

- Générer un nouveau QR code
- Vérifier que la date d'expiration n'est pas dépassée
- S'assurer que le site est toujours actif

### Logs et débogage

Les informations de débogage sont enregistrées dans les logs Laravel et dans les données de vérification des pointages.

#### Informations disponibles

- Erreurs d'authentification WebAuthn
- Erreurs de géolocalisation
- Détails des requêtes de pointage
- Données de vérification complètes
