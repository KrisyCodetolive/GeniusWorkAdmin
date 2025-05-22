-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 27 fév. 2025 à 04:44
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `geniuswork`
--

-- --------------------------------------------------------

--
-- Structure de la table `abonnements`
--

CREATE TABLE `abonnements` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `plan_abonnement_id` char(36) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `date_renouvellement` date DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `periodicite` enum('mensuel','annuel') NOT NULL DEFAULT 'mensuel',
  `mode_paiement` enum('carte','virement','especes') NOT NULL DEFAULT 'carte',
  `statut` enum('actif','inactif','essai','expire','resilie') NOT NULL DEFAULT 'actif',
  `fonctionnalites_activees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fonctionnalites_activees`)),
  `limitations_specifiques` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`limitations_specifiques`)),
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `notes` text DEFAULT NULL,
  `renouvellement_auto` tinyint(1) NOT NULL DEFAULT 1,
  `date_resiliation` date DEFAULT NULL,
  `motif_resiliation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `adresses`
--

CREATE TABLE `adresses` (
  `id` char(36) NOT NULL,
  `adressable_type` varchar(255) NOT NULL,
  `adressable_id` char(36) NOT NULL,
  `type_adresse` varchar(255) NOT NULL DEFAULT 'principale',
  `nom_rue` varchar(255) DEFAULT NULL,
  `numero_rue` varchar(255) DEFAULT NULL,
  `complement` varchar(255) DEFAULT NULL,
  `quartier` varchar(255) DEFAULT NULL,
  `ville` varchar(255) NOT NULL,
  `code_postal` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `pays` varchar(255) NOT NULL DEFAULT 'Cameroun',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `est_principale` tinyint(1) NOT NULL DEFAULT 0,
  `est_facturation` tinyint(1) NOT NULL DEFAULT 0,
  `est_livraison` tinyint(1) NOT NULL DEFAULT 0,
  `meta_donnees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_donnees`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conges`
--

CREATE TABLE `conges` (
  `id` char(36) NOT NULL,
  `employeur_id` char(36) NOT NULL,
  `type_conge_id` char(36) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `duree_jours` int(11) NOT NULL,
  `motif` text DEFAULT NULL,
  `justificatif` varchar(255) DEFAULT NULL,
  `statut` enum('en_attente','approuve','rejete','annule') NOT NULL DEFAULT 'en_attente',
  `validateur_id` char(36) DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `commentaire_validation` text DEFAULT NULL,
  `est_paye` tinyint(1) NOT NULL DEFAULT 1,
  `meta_donnees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_donnees`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

CREATE TABLE `departements` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `objectifs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`objectifs`)),
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `niveau` int(11) NOT NULL DEFAULT 0,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `responsable_id` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `employeurs`
--

CREATE TABLE `employeurs` (
  `id` char(36) NOT NULL,
  `code_employe` varchar(255) NOT NULL,
  `qr_code_secret` varchar(255) NOT NULL,
  `qr_code_expires_at` timestamp NULL DEFAULT NULL,
  `qr_code_active` tinyint(1) NOT NULL DEFAULT 1,
  `entreprise_id` char(36) NOT NULL,
  `departement_id` char(36) DEFAULT NULL,
  `matricule` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(255) DEFAULT NULL,
  `sexe` enum('M','F') DEFAULT NULL,
  `nationalite` varchar(255) DEFAULT NULL,
  `type_piece` varchar(255) DEFAULT NULL,
  `numero_piece` varchar(255) DEFAULT NULL,
  `date_embauche` date NOT NULL,
  `type_contrat` varchar(255) NOT NULL,
  `date_fin_contrat` date DEFAULT NULL,
  `salaire_base` decimal(10,2) NOT NULL,
  `fonction` varchar(255) NOT NULL,
  `competences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`competences`)),
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `statut` enum('actif','inactif','conge','suspendu') NOT NULL DEFAULT 'actif',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprises`
--

CREATE TABLE `entreprises` (
  `id` char(36) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `devise` varchar(255) NOT NULL DEFAULT 'FCFA',
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `parametres_presence` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parametres_presence`)),
  `parametres_notification` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parametres_notification`)),
  `statut` enum('actif','inactif','suspendu') NOT NULL DEFAULT 'actif',
  `raison_sociale` varchar(255) DEFAULT NULL,
  `rccm` varchar(255) DEFAULT NULL,
  `nif` varchar(255) DEFAULT NULL,
  `secteur_activite` varchar(255) DEFAULT NULL,
  `nombre_employes` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `facturations`
--

CREATE TABLE `facturations` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `abonnement_id` char(36) NOT NULL,
  `numero_facture` varchar(255) NOT NULL,
  `date_facturation` date NOT NULL,
  `date_echeance` date NOT NULL,
  `montant_ht` decimal(10,2) NOT NULL,
  `taux_tva` decimal(5,2) NOT NULL DEFAULT 18.00,
  `montant_tva` decimal(10,2) NOT NULL,
  `montant_ttc` decimal(10,2) NOT NULL,
  `montant_paye` decimal(10,2) NOT NULL DEFAULT 0.00,
  `statut` enum('en_attente','payee','partielle','retard','annulee') NOT NULL DEFAULT 'en_attente',
  `mode_paiement` enum('carte','virement','especes') DEFAULT NULL,
  `reference_paiement` varchar(255) DEFAULT NULL,
  `date_paiement` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `frais_usages`
--

CREATE TABLE `frais_usages` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `abonnement_id` char(36) NOT NULL,
  `type_frais` varchar(255) NOT NULL,
  `date_debut_periode` date NOT NULL,
  `date_fin_periode` date NOT NULL,
  `quantite` int(11) NOT NULL,
  `cout_unitaire` decimal(10,2) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `facture` tinyint(1) NOT NULL DEFAULT 0,
  `facturation_id` char(36) DEFAULT NULL,
  `statut` enum('en_attente','facture','annule') NOT NULL DEFAULT 'en_attente',
  `description` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jours`
--

CREATE TABLE `jours` (
  `id` char(36) NOT NULL,
  `jour_travail_id` char(36) NOT NULL,
  `employeur_id` char(36) NOT NULL,
  `plage_horaire_id` char(36) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jour_travails`
--

CREATE TABLE `jour_travails` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `jour_semaine` varchar(255) NOT NULL,
  `est_travaille` tinyint(1) NOT NULL DEFAULT 1,
  `est_ferie` tinyint(1) NOT NULL DEFAULT 0,
  `heure_debut_standard` time DEFAULT NULL,
  `heure_fin_standard` time DEFAULT NULL,
  `duree_pause_standard` int(11) DEFAULT NULL,
  `plages_horaires` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`plages_horaires`)),
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `methode_pointages`
--

CREATE TABLE `methode_pointages` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `necessite_photo` tinyint(1) NOT NULL DEFAULT 0,
  `necessite_geolocalisation` tinyint(1) NOT NULL DEFAULT 1,
  `necessite_signature` tinyint(1) NOT NULL DEFAULT 0,
  `necessite_validation` tinyint(1) NOT NULL DEFAULT 0,
  `autoriser_hors_site` tinyint(1) NOT NULL DEFAULT 0,
  `rayon_geofencing` int(11) NOT NULL DEFAULT 100,
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `validation_regles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`validation_regles`)),
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_02_27_021106_create_entreprises_table', 1),
(4, '2025_02_27_021107_create_users_table', 1),
(5, '2025_02_27_021124_create_departements_table', 1),
(6, '2025_02_27_021125_create_employeurs_table', 1),
(7, '2025_02_27_021126_add_employeur_to_users_table', 1),
(8, '2025_02_27_021146_add_foreign_keys_to_departements', 1),
(9, '2025_02_27_021205_create_plan_abonnements_table', 1),
(10, '2025_02_27_021225_create_abonnements_table', 1),
(11, '2025_02_27_021226_create_facturations_table', 1),
(12, '2025_02_27_021235_create_frais_usages_table', 1),
(13, '2025_02_27_021430_create_adresses_table', 1),
(14, '2025_02_27_021502_create_jour_travails_table', 1),
(15, '2025_02_27_021510_create_plage_horaires_table', 1),
(16, '2025_02_27_021516_create_jours_table', 1),
(17, '2025_02_27_021519_create_raison_sorties_table', 1),
(18, '2025_02_27_021524_create_presences_table', 1),
(19, '2025_02_27_021656_create_supplementaires_table', 1),
(20, '2025_02_27_021712_create_permutations_table', 1),
(21, '2025_02_27_025210_create_type_conges_table', 1),
(22, '2025_02_27_025211_create_conges_table', 1),
(23, '2025_02_27_025212_create_notifications_table', 1),
(24, '2025_02_27_025213_create_parametres_notifications_table', 1),
(25, '2025_02_27_025214_create_politiques_table', 1),
(26, '2025_02_27_025215_create_trackings_table', 1),
(27, '2025_02_27_025216_create_methode_pointages_table', 1),
(28, '2025_02_27_025217_add_unique_codes_to_employeurs_table', 1),
(29, '2025_02_27_025218_add_phone_and_pin_to_users_table', 1),
(30, '2025_02_27_031155_create_permission_tables', 1),
(31, '2025_02_27_035219_add_custom_fields_to_roles_and_permissions_tables', 1);

-- --------------------------------------------------------

--
-- Structure de la table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `notifiable_type` varchar(255) DEFAULT NULL,
  `notifiable_id` char(36) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `date_envoi` datetime DEFAULT NULL,
  `date_lecture` datetime DEFAULT NULL,
  `canal` enum('email','sms','push','interne') NOT NULL DEFAULT 'interne',
  `priorite` enum('basse','normale','haute') NOT NULL DEFAULT 'normale',
  `statut` enum('en_attente','envoye','echec','lu') NOT NULL DEFAULT 'en_attente',
  `erreur` text DEFAULT NULL,
  `tentatives` int(11) NOT NULL DEFAULT 0,
  `prochaine_tentative` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parametres_notifications`
--

CREATE TABLE `parametres_notifications` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `notifier_absences` tinyint(1) NOT NULL DEFAULT 1,
  `notifier_retards` tinyint(1) NOT NULL DEFAULT 1,
  `notifier_conges` tinyint(1) NOT NULL DEFAULT 1,
  `notifier_heures_supplementaires` tinyint(1) NOT NULL DEFAULT 1,
  `notifier_permutations` tinyint(1) NOT NULL DEFAULT 1,
  `activer_notifications_email` tinyint(1) NOT NULL DEFAULT 1,
  `activer_notifications_sms` tinyint(1) NOT NULL DEFAULT 0,
  `activer_notifications_push` tinyint(1) NOT NULL DEFAULT 1,
  `modeles_email` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`modeles_email`)),
  `modeles_sms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`modeles_sms`)),
  `configuration_email` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration_email`)),
  `configuration_sms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration_sms`)),
  `destinataires_supplementaires` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`destinataires_supplementaires`)),
  `regles_notification` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`regles_notification`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `groupe` varchar(255) DEFAULT NULL,
  `meta_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_data`)),
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permutations`
--

CREATE TABLE `permutations` (
  `id` char(36) NOT NULL,
  `employeur_1_id` char(36) NOT NULL,
  `employeur_2_id` char(36) NOT NULL,
  `plage_horaire_1_id` char(36) NOT NULL,
  `plage_horaire_2_id` char(36) NOT NULL,
  `date` date NOT NULL,
  `motif` varchar(255) NOT NULL,
  `statut` enum('en_attente','approuve','rejete','annule') NOT NULL DEFAULT 'en_attente',
  `commentaire` text DEFAULT NULL,
  `validateur_id` char(36) DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` char(36) NOT NULL,
  `tokenable_id` char(36) NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plage_horaires`
--

CREATE TABLE `plage_horaires` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `duree_pause` int(11) DEFAULT NULL,
  `est_standard` tinyint(1) NOT NULL DEFAULT 0,
  `est_flexible` tinyint(1) NOT NULL DEFAULT 0,
  `marge_retard` int(11) NOT NULL DEFAULT 0,
  `marge_depart` int(11) NOT NULL DEFAULT 0,
  `pauses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pauses`)),
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plan_abonnements`
--

CREATE TABLE `plan_abonnements` (
  `id` char(36) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix_mensuel` decimal(10,2) NOT NULL,
  `prix_annuel` decimal(10,2) DEFAULT NULL,
  `nombre_employes_max` int(11) NOT NULL,
  `nombre_departements_max` int(11) DEFAULT NULL,
  `duree_essai` int(11) NOT NULL DEFAULT 0,
  `fonctionnalites` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fonctionnalites`)),
  `limitations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`limitations`)),
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `est_populaire` tinyint(1) NOT NULL DEFAULT 0,
  `est_personnalise` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `politiques`
--

CREATE TABLE `politiques` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `annuler_horaires_si_sortie_manquee` tinyint(1) NOT NULL DEFAULT 0,
  `nombre_pointages_par_jour` int(11) NOT NULL DEFAULT 2,
  `activer_pauses` tinyint(1) NOT NULL DEFAULT 1,
  `activer_heures_supplementaires` tinyint(1) NOT NULL DEFAULT 1,
  `notifier_utilisateurs` tinyint(1) NOT NULL DEFAULT 1,
  `tolerance_retard` int(11) NOT NULL DEFAULT 15,
  `tolerance_depart_anticipe` int(11) NOT NULL DEFAULT 0,
  `autoriser_permutations` tinyint(1) NOT NULL DEFAULT 1,
  `autoriser_recuperations` tinyint(1) NOT NULL DEFAULT 1,
  `autoriser_travail_weekend` tinyint(1) NOT NULL DEFAULT 0,
  `regles_presence` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`regles_presence`)),
  `regles_conges` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`regles_conges`)),
  `regles_supplementaires` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`regles_supplementaires`)),
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `presences`
--

CREATE TABLE `presences` (
  `id` char(36) NOT NULL,
  `employeur_id` char(36) NOT NULL,
  `jour_id` char(36) DEFAULT NULL,
  `raison_sortie_id` char(36) DEFAULT NULL,
  `date_heure_entree` datetime NOT NULL,
  `date_heure_sortie` datetime DEFAULT NULL,
  `latitude_entree` decimal(10,8) DEFAULT NULL,
  `longitude_entree` decimal(11,8) DEFAULT NULL,
  `latitude_sortie` decimal(10,8) DEFAULT NULL,
  `longitude_sortie` decimal(11,8) DEFAULT NULL,
  `adresse_ip_entree` varchar(255) DEFAULT NULL,
  `adresse_ip_sortie` varchar(255) DEFAULT NULL,
  `appareil_entree` varchar(255) DEFAULT NULL,
  `appareil_sortie` varchar(255) DEFAULT NULL,
  `duree_effective` int(11) DEFAULT NULL,
  `retard` int(11) DEFAULT NULL,
  `depart_anticipe` int(11) DEFAULT NULL,
  `statut` enum('present','absent','retard','sortie','conge') NOT NULL DEFAULT 'present',
  `statut_validation` enum('en_attente','approuve','rejete') NOT NULL DEFAULT 'en_attente',
  `validateur_id` char(36) DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `meta_donnees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_donnees`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `raison_sorties`
--

CREATE TABLE `raison_sorties` (
  `id` char(36) NOT NULL,
  `motif` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('pause','conge','mission','autre') NOT NULL DEFAULT 'autre',
  `duree_max` int(11) DEFAULT NULL,
  `necessite_validation` tinyint(1) NOT NULL DEFAULT 0,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `entreprise_id` char(36) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `meta_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_data`)),
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `location` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `supplementaires`
--

CREATE TABLE `supplementaires` (
  `id` char(36) NOT NULL,
  `employeur_id` char(36) NOT NULL,
  `date` date NOT NULL,
  `heure_debut` datetime NOT NULL,
  `heure_fin` datetime NOT NULL,
  `nombre_heures` decimal(5,2) NOT NULL,
  `taux_majoration` decimal(5,2) NOT NULL DEFAULT 50.00,
  `montant` decimal(10,2) NOT NULL,
  `motif` varchar(255) NOT NULL,
  `statut` enum('en_attente','approuve','rejete','annule') NOT NULL DEFAULT 'en_attente',
  `commentaire` text DEFAULT NULL,
  `validateur_id` char(36) DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `trackings`
--

CREATE TABLE `trackings` (
  `id` char(36) NOT NULL,
  `employeur_id` char(36) NOT NULL,
  `presence_id` char(36) DEFAULT NULL,
  `type` enum('entree','sortie','pause_debut','pause_fin') NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `date_heure` datetime NOT NULL,
  `methode_pointage` varchar(255) NOT NULL,
  `appareil` varchar(255) DEFAULT NULL,
  `adresse_ip` varchar(255) DEFAULT NULL,
  `meta_donnees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_donnees`)),
  `statut` enum('valide','invalide','suspect') NOT NULL DEFAULT 'valide',
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_conges`
--

CREATE TABLE `type_conges` (
  `id` char(36) NOT NULL,
  `entreprise_id` char(36) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duree_max_annuelle` int(11) DEFAULT NULL,
  `necessite_justificatif` tinyint(1) NOT NULL DEFAULT 0,
  `est_paye` tinyint(1) NOT NULL DEFAULT 1,
  `deductible_solde` tinyint(1) NOT NULL DEFAULT 1,
  `delai_demande_prealable` int(11) NOT NULL DEFAULT 0,
  `conditions_eligibilite` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditions_eligibilite`)),
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `phone_verified_at` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `pin` varchar(255) DEFAULT NULL,
  `pin_changed_at` timestamp NULL DEFAULT NULL,
  `require_pin_change` tinyint(1) NOT NULL DEFAULT 0,
  `pin_attempts` int(11) NOT NULL DEFAULT 0,
  `pin_locked_until` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `entreprise_id` char(36) DEFAULT NULL,
  `role` enum('super_admin','admin','entreprise','employeur') NOT NULL DEFAULT 'employeur',
  `statut` enum('actif','inactif','suspendu') NOT NULL DEFAULT 'actif',
  `telephone` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `langue` varchar(255) NOT NULL DEFAULT 'fr',
  `fuseau_horaire` varchar(255) NOT NULL DEFAULT 'UTC',
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `employeur_id` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `abonnements`
--
ALTER TABLE `abonnements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `abonnements_entreprise_id_foreign` (`entreprise_id`),
  ADD KEY `abonnements_plan_abonnement_id_foreign` (`plan_abonnement_id`);

--
-- Index pour la table `adresses`
--
ALTER TABLE `adresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adresses_adressable_type_adressable_id_index` (`adressable_type`,`adressable_id`);

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Index pour la table `conges`
--
ALTER TABLE `conges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conges_employeur_id_foreign` (`employeur_id`),
  ADD KEY `conges_type_conge_id_foreign` (`type_conge_id`),
  ADD KEY `conges_validateur_id_foreign` (`validateur_id`);

--
-- Index pour la table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departements_code_unique` (`code`),
  ADD KEY `departements_entreprise_id_foreign` (`entreprise_id`),
  ADD KEY `departements_parent_id_foreign` (`parent_id`),
  ADD KEY `departements_responsable_id_foreign` (`responsable_id`);

--
-- Index pour la table `employeurs`
--
ALTER TABLE `employeurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employeurs_matricule_unique` (`matricule`),
  ADD UNIQUE KEY `employeurs_email_unique` (`email`),
  ADD UNIQUE KEY `employeurs_code_employe_unique` (`code_employe`),
  ADD UNIQUE KEY `employeurs_qr_code_secret_unique` (`qr_code_secret`),
  ADD KEY `employeurs_entreprise_id_foreign` (`entreprise_id`),
  ADD KEY `employeurs_departement_id_foreign` (`departement_id`);

--
-- Index pour la table `entreprises`
--
ALTER TABLE `entreprises`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entreprises_email_unique` (`email`);

--
-- Index pour la table `facturations`
--
ALTER TABLE `facturations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `facturations_numero_facture_unique` (`numero_facture`),
  ADD KEY `facturations_entreprise_id_foreign` (`entreprise_id`),
  ADD KEY `facturations_abonnement_id_foreign` (`abonnement_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `frais_usages`
--
ALTER TABLE `frais_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frais_usages_entreprise_id_foreign` (`entreprise_id`),
  ADD KEY `frais_usages_abonnement_id_foreign` (`abonnement_id`),
  ADD KEY `frais_usages_facturation_id_foreign` (`facturation_id`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `jours`
--
ALTER TABLE `jours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jours_jour_travail_id_foreign` (`jour_travail_id`),
  ADD KEY `jours_employeur_id_foreign` (`employeur_id`),
  ADD KEY `jours_plage_horaire_id_foreign` (`plage_horaire_id`);

--
-- Index pour la table `jour_travails`
--
ALTER TABLE `jour_travails`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jour_travails_entreprise_id_jour_semaine_unique` (`entreprise_id`,`jour_semaine`);

--
-- Index pour la table `methode_pointages`
--
ALTER TABLE `methode_pointages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `methode_pointages_code_unique` (`code`),
  ADD KEY `methode_pointages_entreprise_id_foreign` (`entreprise_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Index pour la table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Index pour la table `parametres_notifications`
--
ALTER TABLE `parametres_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parametres_notifications_entreprise_id_unique` (`entreprise_id`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Index pour la table `permutations`
--
ALTER TABLE `permutations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permutations_employeur_1_id_foreign` (`employeur_1_id`),
  ADD KEY `permutations_employeur_2_id_foreign` (`employeur_2_id`),
  ADD KEY `permutations_plage_horaire_1_id_foreign` (`plage_horaire_1_id`),
  ADD KEY `permutations_plage_horaire_2_id_foreign` (`plage_horaire_2_id`),
  ADD KEY `permutations_validateur_id_foreign` (`validateur_id`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`);

--
-- Index pour la table `plage_horaires`
--
ALTER TABLE `plage_horaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plage_horaires_entreprise_id_foreign` (`entreprise_id`);

--
-- Index pour la table `plan_abonnements`
--
ALTER TABLE `plan_abonnements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_abonnements_code_unique` (`code`);

--
-- Index pour la table `politiques`
--
ALTER TABLE `politiques`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `politiques_entreprise_id_unique` (`entreprise_id`);

--
-- Index pour la table `presences`
--
ALTER TABLE `presences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `presences_employeur_id_foreign` (`employeur_id`),
  ADD KEY `presences_jour_id_foreign` (`jour_id`),
  ADD KEY `presences_raison_sortie_id_foreign` (`raison_sortie_id`),
  ADD KEY `presences_validateur_id_foreign` (`validateur_id`);

--
-- Index pour la table `raison_sorties`
--
ALTER TABLE `raison_sorties`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`),
  ADD KEY `roles_entreprise_id_foreign` (`entreprise_id`);

--
-- Index pour la table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `supplementaires`
--
ALTER TABLE `supplementaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplementaires_employeur_id_foreign` (`employeur_id`),
  ADD KEY `supplementaires_validateur_id_foreign` (`validateur_id`);

--
-- Index pour la table `trackings`
--
ALTER TABLE `trackings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trackings_employeur_id_foreign` (`employeur_id`),
  ADD KEY `trackings_presence_id_foreign` (`presence_id`);

--
-- Index pour la table `type_conges`
--
ALTER TABLE `type_conges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_conges_entreprise_id_foreign` (`entreprise_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD KEY `users_entreprise_id_foreign` (`entreprise_id`),
  ADD KEY `users_employeur_id_foreign` (`employeur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `abonnements`
--
ALTER TABLE `abonnements`
  ADD CONSTRAINT `abonnements_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `abonnements_plan_abonnement_id_foreign` FOREIGN KEY (`plan_abonnement_id`) REFERENCES `plan_abonnements` (`id`);

--
-- Contraintes pour la table `conges`
--
ALTER TABLE `conges`
  ADD CONSTRAINT `conges_employeur_id_foreign` FOREIGN KEY (`employeur_id`) REFERENCES `employeurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conges_type_conge_id_foreign` FOREIGN KEY (`type_conge_id`) REFERENCES `type_conges` (`id`),
  ADD CONSTRAINT `conges_validateur_id_foreign` FOREIGN KEY (`validateur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `departements`
--
ALTER TABLE `departements`
  ADD CONSTRAINT `departements_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `departements_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `departements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `departements_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `employeurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `employeurs`
--
ALTER TABLE `employeurs`
  ADD CONSTRAINT `employeurs_departement_id_foreign` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employeurs_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `facturations`
--
ALTER TABLE `facturations`
  ADD CONSTRAINT `facturations_abonnement_id_foreign` FOREIGN KEY (`abonnement_id`) REFERENCES `abonnements` (`id`),
  ADD CONSTRAINT `facturations_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `frais_usages`
--
ALTER TABLE `frais_usages`
  ADD CONSTRAINT `frais_usages_abonnement_id_foreign` FOREIGN KEY (`abonnement_id`) REFERENCES `abonnements` (`id`),
  ADD CONSTRAINT `frais_usages_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `frais_usages_facturation_id_foreign` FOREIGN KEY (`facturation_id`) REFERENCES `facturations` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `jours`
--
ALTER TABLE `jours`
  ADD CONSTRAINT `jours_employeur_id_foreign` FOREIGN KEY (`employeur_id`) REFERENCES `employeurs` (`id`),
  ADD CONSTRAINT `jours_jour_travail_id_foreign` FOREIGN KEY (`jour_travail_id`) REFERENCES `jour_travails` (`id`),
  ADD CONSTRAINT `jours_plage_horaire_id_foreign` FOREIGN KEY (`plage_horaire_id`) REFERENCES `plage_horaires` (`id`);

--
-- Contraintes pour la table `jour_travails`
--
ALTER TABLE `jour_travails`
  ADD CONSTRAINT `jour_travails_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `methode_pointages`
--
ALTER TABLE `methode_pointages`
  ADD CONSTRAINT `methode_pointages_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `parametres_notifications`
--
ALTER TABLE `parametres_notifications`
  ADD CONSTRAINT `parametres_notifications_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `permutations`
--
ALTER TABLE `permutations`
  ADD CONSTRAINT `permutations_employeur_1_id_foreign` FOREIGN KEY (`employeur_1_id`) REFERENCES `employeurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permutations_employeur_2_id_foreign` FOREIGN KEY (`employeur_2_id`) REFERENCES `employeurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permutations_plage_horaire_1_id_foreign` FOREIGN KEY (`plage_horaire_1_id`) REFERENCES `plage_horaires` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permutations_plage_horaire_2_id_foreign` FOREIGN KEY (`plage_horaire_2_id`) REFERENCES `plage_horaires` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permutations_validateur_id_foreign` FOREIGN KEY (`validateur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `plage_horaires`
--
ALTER TABLE `plage_horaires`
  ADD CONSTRAINT `plage_horaires_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `politiques`
--
ALTER TABLE `politiques`
  ADD CONSTRAINT `politiques_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `presences`
--
ALTER TABLE `presences`
  ADD CONSTRAINT `presences_employeur_id_foreign` FOREIGN KEY (`employeur_id`) REFERENCES `employeurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presences_jour_id_foreign` FOREIGN KEY (`jour_id`) REFERENCES `jours` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `presences_raison_sortie_id_foreign` FOREIGN KEY (`raison_sortie_id`) REFERENCES `raison_sorties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `presences_validateur_id_foreign` FOREIGN KEY (`validateur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`);

--
-- Contraintes pour la table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `supplementaires`
--
ALTER TABLE `supplementaires`
  ADD CONSTRAINT `supplementaires_employeur_id_foreign` FOREIGN KEY (`employeur_id`) REFERENCES `employeurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplementaires_validateur_id_foreign` FOREIGN KEY (`validateur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `trackings`
--
ALTER TABLE `trackings`
  ADD CONSTRAINT `trackings_employeur_id_foreign` FOREIGN KEY (`employeur_id`) REFERENCES `employeurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trackings_presence_id_foreign` FOREIGN KEY (`presence_id`) REFERENCES `presences` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `type_conges`
--
ALTER TABLE `type_conges`
  ADD CONSTRAINT `type_conges_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_employeur_id_foreign` FOREIGN KEY (`employeur_id`) REFERENCES `employeurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_entreprise_id_foreign` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
