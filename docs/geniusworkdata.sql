-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 27 fév. 2025 à 05:24
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

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`id`, `entreprise_id`, `parent_id`, `nom`, `code`, `description`, `objectifs`, `configuration`, `niveau`, `ordre`, `statut`, `created_at`, `updated_at`, `deleted_at`, `responsable_id`) VALUES
('019545a4-2226-7334-8a88-29f40b47c939', '019545a4-10b9-7276-8459-83b53405a736', NULL, 'Direction Générale', 'DG-NQCK', 'Direction et administration', NULL, NULL, 0, 0, 'actif', '2025-02-27 04:22:19', '2025-02-27 04:22:19', NULL, NULL),
('019545a4-222a-7071-95eb-23a781465b18', '019545a4-10b9-7276-8459-83b53405a736', NULL, 'Ressources Humaines', 'RH-FHUW', 'Gestion des ressources humaines', NULL, NULL, 0, 0, 'actif', '2025-02-27 04:22:19', '2025-02-27 04:22:19', NULL, NULL),
('019545a4-222c-7208-bd51-a0a61b59178c', '019545a4-10b9-7276-8459-83b53405a736', NULL, 'Production', 'PROD-NC10', 'Service de production', NULL, NULL, 0, 0, 'actif', '2025-02-27 04:22:19', '2025-02-27 04:22:19', NULL, NULL),
('019545a5-c464-7130-b7b6-84873a8064dd', '019545a4-10b9-7276-8459-83b53405a736', NULL, 'Direction Générale', 'DG-IECN', 'Direction et administration', NULL, NULL, 0, 0, 'actif', '2025-02-27 04:24:06', '2025-02-27 04:24:06', NULL, NULL);

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

--
-- Déchargement des données de la table `entreprises`
--

INSERT INTO `entreprises` (`id`, `nom`, `email`, `telephone`, `site_web`, `logo`, `devise`, `configuration`, `parametres_presence`, `parametres_notification`, `statut`, `raison_sociale`, `rccm`, `nif`, `secteur_activite`, `nombre_employes`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
('019545a4-10b9-7276-8459-83b53405a736', 'TechCorp Solutions', 'contact@techcorp.com', '+22501020304', NULL, NULL, 'FCFA', '\"{\\\"fuseau_horaire\\\":\\\"Africa\\\\\\/Abidjan\\\",\\\"devise\\\":\\\"XOF\\\",\\\"langue\\\":\\\"fr\\\",\\\"jours_travail\\\":[\\\"Lundi\\\",\\\"Mardi\\\",\\\"Mercredi\\\",\\\"Jeudi\\\",\\\"Vendredi\\\"],\\\"politique_presence\\\":{\\\"tolerance_retard\\\":15,\\\"tolerance_depart\\\":5}}\"', NULL, NULL, 'actif', 'TechCorp Solutions SARL', NULL, NULL, 'Technologies', 50, 'Entreprise leader dans les solutions technologiques', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL),
('019545a4-10bc-72db-af81-f688fd8a8ac5', 'Global Trading SA', 'info@globaltrading.com', '+22507080910', NULL, NULL, 'FCFA', '\"{\\\"fuseau_horaire\\\":\\\"Africa\\\\\\/Abidjan\\\",\\\"devise\\\":\\\"XOF\\\",\\\"langue\\\":\\\"fr\\\",\\\"jours_travail\\\":[\\\"Lundi\\\",\\\"Mardi\\\",\\\"Mercredi\\\",\\\"Jeudi\\\",\\\"Vendredi\\\",\\\"Samedi\\\"],\\\"politique_presence\\\":{\\\"tolerance_retard\\\":10,\\\"tolerance_depart\\\":5}}\"', NULL, NULL, 'actif', 'Global Trading SA', NULL, NULL, 'Commerce', 100, 'Leader dans le commerce international', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL);

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

--
-- Déchargement des données de la table `jour_travails`
--

INSERT INTO `jour_travails` (`id`, `entreprise_id`, `jour_semaine`, `est_travaille`, `est_ferie`, `heure_debut_standard`, `heure_fin_standard`, `duree_pause_standard`, `plages_horaires`, `configuration`, `created_at`, `updated_at`, `deleted_at`) VALUES
('019545a4-1130-7274-a58e-8f47fdee3770', '019545a4-10b9-7276-8459-83b53405a736', 'Lundi', 1, 0, '08:00:00', '17:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"17:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-1134-7178-a436-5204fd16fdf2', '019545a4-10b9-7276-8459-83b53405a736', 'Mardi', 1, 0, '08:00:00', '17:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"17:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-1136-7179-94ce-6615ec749155', '019545a4-10b9-7276-8459-83b53405a736', 'Mercredi', 1, 0, '08:00:00', '17:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"17:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-1190-724a-92e7-f0361da2ec13', '019545a4-10b9-7276-8459-83b53405a736', 'Jeudi', 1, 0, '08:00:00', '17:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"17:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11d4-711f-8697-338d93e33b12', '019545a4-10b9-7276-8459-83b53405a736', 'Vendredi', 1, 0, '08:00:00', '16:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"16:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11d9-7016-9140-4998cb8eea08', '019545a4-10b9-7276-8459-83b53405a736', 'Samedi', 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11dc-706f-b66d-84f08f265549', '019545a4-10b9-7276-8459-83b53405a736', 'Dimanche', 0, 1, NULL, NULL, NULL, NULL, NULL, '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11df-7075-8fe5-c20547c43548', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Lundi', 1, 0, '08:00:00', '17:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"17:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11e2-70f2-bc60-f15080bd04aa', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Mardi', 1, 0, '08:00:00', '17:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"17:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11e4-712e-b12f-a82729059ead', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Mercredi', 1, 0, '08:00:00', '17:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"17:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11e6-70c6-946c-b972c124e90e', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Jeudi', 1, 0, '08:00:00', '17:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"17:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11e8-7044-9b52-57f681c59f5a', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Vendredi', 1, 0, '08:00:00', '16:00:00', 60, '[{\"debut\":\"08:00:00\",\"fin\":\"12:00:00\"},{\"debut\":\"13:00:00\",\"fin\":\"16:00:00\"}]', '\"{\\\"flexible_debut\\\":true,\\\"flexible_fin\\\":true,\\\"marge_debut\\\":30,\\\"marge_fin\\\":30}\"', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11ea-7075-a55c-c78a105d0ef0', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Samedi', 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-11ec-7019-b7c6-37395169932a', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Dimanche', 0, 1, NULL, NULL, NULL, NULL, NULL, '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL);

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

--
-- Déchargement des données de la table `methode_pointages`
--

INSERT INTO `methode_pointages` (`id`, `entreprise_id`, `nom`, `code`, `description`, `necessite_photo`, `necessite_geolocalisation`, `necessite_signature`, `necessite_validation`, `autoriser_hors_site`, `rayon_geofencing`, `configuration`, `validation_regles`, `statut`, `created_at`, `updated_at`, `deleted_at`) VALUES
('019545a4-10da-73e6-9b99-768a076d6992', '019545a4-10b9-7276-8459-83b53405a736', 'QR Code', 'QR-019545a4-10b9-7276-8459-83b53405a736', 'Pointage via scan de QR Code', 0, 1, 0, 0, 0, 100, '\"{\\\"duree_validite_qr\\\":30,\\\"rotation_automatique\\\":true,\\\"interval_rotation\\\":60}\"', '\"{\\\"verifier_appareil\\\":true,\\\"verifier_ip\\\":true}\"', 'actif', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-10dc-7210-b698-86b29f911bed', '019545a4-10b9-7276-8459-83b53405a736', 'Géolocalisation', 'GEO-019545a4-10b9-7276-8459-83b53405a736', 'Pointage par géolocalisation', 1, 1, 0, 1, 0, 50, '\"{\\\"precision_requise\\\":20,\\\"delai_validation\\\":5,\\\"photo_selfie\\\":true}\"', '\"{\\\"verifier_appareil\\\":true,\\\"verifier_ip\\\":true,\\\"verifier_precision\\\":true}\"', 'actif', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-10de-71e4-ba77-0afef7ba7322', '019545a4-10b9-7276-8459-83b53405a736', 'Badge NFC', 'NFC-019545a4-10b9-7276-8459-83b53405a736', 'Pointage via badge NFC', 0, 1, 0, 1, 0, 20, '\"{\\\"verification_uid\\\":true,\\\"rotation_cle\\\":true,\\\"interval_rotation\\\":24}\"', '\"{\\\"verifier_appareil\\\":true,\\\"verifier_badge\\\":true}\"', 'actif', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-10e0-7013-a4f3-e192af2db6a0', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'QR Code', 'QR-019545a4-10bc-72db-af81-f688fd8a8ac5', 'Pointage via scan de QR Code', 0, 1, 0, 0, 0, 100, '\"{\\\"duree_validite_qr\\\":30,\\\"rotation_automatique\\\":true,\\\"interval_rotation\\\":60}\"', '\"{\\\"verifier_appareil\\\":true,\\\"verifier_ip\\\":true}\"', 'actif', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-10e2-7381-9d38-c6da59695fdf', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Géolocalisation', 'GEO-019545a4-10bc-72db-af81-f688fd8a8ac5', 'Pointage par géolocalisation', 1, 1, 0, 1, 0, 50, '\"{\\\"precision_requise\\\":20,\\\"delai_validation\\\":5,\\\"photo_selfie\\\":true}\"', '\"{\\\"verifier_appareil\\\":true,\\\"verifier_ip\\\":true,\\\"verifier_precision\\\":true}\"', 'actif', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL),
('019545a4-10e4-712c-9f31-bda2b3912605', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Badge NFC', 'NFC-019545a4-10bc-72db-af81-f688fd8a8ac5', 'Pointage via badge NFC', 0, 1, 0, 1, 0, 20, '\"{\\\"verification_uid\\\":true,\\\"rotation_cle\\\":true,\\\"interval_rotation\\\":24}\"', '\"{\\\"verifier_appareil\\\":true,\\\"verifier_badge\\\":true}\"', 'actif', '2025-02-27 04:22:15', '2025-02-27 04:22:15', NULL);

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
(3, '2025_02_27_021105_create_permission_tables', 1),
(4, '2025_02_27_021106_create_entreprises_table', 1),
(5, '2025_02_27_021107_create_users_table', 1),
(6, '2025_02_27_021124_create_departements_table', 1),
(7, '2025_02_27_021125_create_employeurs_table', 1),
(8, '2025_02_27_021126_add_employeur_to_users_table', 1),
(9, '2025_02_27_021146_add_foreign_keys_to_departements', 1),
(10, '2025_02_27_021205_create_plan_abonnements_table', 1),
(11, '2025_02_27_021225_create_abonnements_table', 1),
(12, '2025_02_27_021226_create_facturations_table', 1),
(13, '2025_02_27_021235_create_frais_usages_table', 1),
(14, '2025_02_27_021430_create_adresses_table', 1),
(15, '2025_02_27_021502_create_jour_travails_table', 1),
(16, '2025_02_27_021510_create_plage_horaires_table', 1),
(17, '2025_02_27_021516_create_jours_table', 1),
(18, '2025_02_27_021519_create_raison_sorties_table', 1),
(19, '2025_02_27_021524_create_presences_table', 1),
(20, '2025_02_27_021656_create_supplementaires_table', 1),
(21, '2025_02_27_021712_create_permutations_table', 1),
(22, '2025_02_27_025210_create_type_conges_table', 1),
(23, '2025_02_27_025211_create_conges_table', 1),
(24, '2025_02_27_025212_create_notifications_table', 1),
(25, '2025_02_27_025213_create_parametres_notifications_table', 1),
(26, '2025_02_27_025214_create_politiques_table', 1),
(27, '2025_02_27_025215_create_trackings_table', 1),
(28, '2025_02_27_025216_create_methode_pointages_table', 1),
(29, '2025_02_27_025217_add_unique_codes_to_employeurs_table', 1),
(30, '2025_02_27_025218_add_phone_and_pin_to_users_table', 1),
(31, '2025_02_27_035219_add_custom_fields_to_roles_and_permissions_tables', 1),
(32, '2025_02_27_040040_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Structure de la table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', '019545a4-1469-7323-91f2-a5a5eb3d88e0'),
(2, 'App\\Models\\User', '019545a4-1868-7270-aede-83473a6c9994'),
(3, 'App\\Models\\User', '019545a4-1c50-724a-bd62-25d0757c8a07'),
(5, 'App\\Models\\User', '019545a4-2085-703c-b644-5a8707f8c543');

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

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `description`, `groupe`, `meta_data`, `is_system`, `statut`, `deleted_at`) VALUES
(1, 'view_users', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir les utilisateurs', 'users', '{\"group_description\":\"Gestion des utilisateurs\"}', 1, 'actif', NULL),
(2, 'create_users', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Créer des utilisateurs', 'users', '{\"group_description\":\"Gestion des utilisateurs\"}', 1, 'actif', NULL),
(3, 'edit_users', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Modifier les utilisateurs', 'users', '{\"group_description\":\"Gestion des utilisateurs\"}', 1, 'actif', NULL),
(4, 'delete_users', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Supprimer les utilisateurs', 'users', '{\"group_description\":\"Gestion des utilisateurs\"}', 1, 'actif', NULL),
(5, 'manage_roles', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Gérer les rôles', 'users', '{\"group_description\":\"Gestion des utilisateurs\"}', 1, 'actif', NULL),
(6, 'view_employees', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir les employés', 'employees', '{\"group_description\":\"Gestion des employ\\u00e9s\"}', 1, 'actif', NULL),
(7, 'create_employees', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Créer des employés', 'employees', '{\"group_description\":\"Gestion des employ\\u00e9s\"}', 1, 'actif', NULL),
(8, 'edit_employees', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Modifier les employés', 'employees', '{\"group_description\":\"Gestion des employ\\u00e9s\"}', 1, 'actif', NULL),
(9, 'delete_employees', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Supprimer des employés', 'employees', '{\"group_description\":\"Gestion des employ\\u00e9s\"}', 1, 'actif', NULL),
(10, 'view_employee_history', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir l\'historique des employés', 'employees', '{\"group_description\":\"Gestion des employ\\u00e9s\"}', 1, 'actif', NULL),
(11, 'view_presence', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir les présences', 'presence', '{\"group_description\":\"Gestion des pr\\u00e9sences\"}', 1, 'actif', NULL),
(12, 'manage_presence', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Gérer les présences', 'presence', '{\"group_description\":\"Gestion des pr\\u00e9sences\"}', 1, 'actif', NULL),
(13, 'validate_presence', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Valider les présences', 'presence', '{\"group_description\":\"Gestion des pr\\u00e9sences\"}', 1, 'actif', NULL),
(14, 'edit_presence', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Modifier les présences', 'presence', '{\"group_description\":\"Gestion des pr\\u00e9sences\"}', 1, 'actif', NULL),
(15, 'view_presence_history', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir l\'historique des présences', 'presence', '{\"group_description\":\"Gestion des pr\\u00e9sences\"}', 1, 'actif', NULL),
(16, 'view_leaves', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir les congés', 'leaves', '{\"group_description\":\"Gestion des cong\\u00e9s\"}', 1, 'actif', NULL),
(17, 'create_leaves', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Créer des congés', 'leaves', '{\"group_description\":\"Gestion des cong\\u00e9s\"}', 1, 'actif', NULL),
(18, 'approve_leaves', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Approuver les congés', 'leaves', '{\"group_description\":\"Gestion des cong\\u00e9s\"}', 1, 'actif', NULL),
(19, 'reject_leaves', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Rejeter les congés', 'leaves', '{\"group_description\":\"Gestion des cong\\u00e9s\"}', 1, 'actif', NULL),
(20, 'manage_leave_types', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Gérer les types de congés', 'leaves', '{\"group_description\":\"Gestion des cong\\u00e9s\"}', 1, 'actif', NULL),
(21, 'view_reports', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir les rapports', 'reports', '{\"group_description\":\"Gestion des rapports\"}', 1, 'actif', NULL),
(22, 'create_reports', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Créer des rapports', 'reports', '{\"group_description\":\"Gestion des rapports\"}', 1, 'actif', NULL),
(23, 'export_reports', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Exporter les rapports', 'reports', '{\"group_description\":\"Gestion des rapports\"}', 1, 'actif', NULL),
(24, 'manage_report_settings', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Gérer les paramètres des rapports', 'reports', '{\"group_description\":\"Gestion des rapports\"}', 1, 'actif', NULL),
(25, 'view_settings', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir les paramètres', 'settings', '{\"group_description\":\"Param\\u00e8tres syst\\u00e8me\"}', 1, 'actif', NULL),
(26, 'edit_settings', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Modifier les paramètres', 'settings', '{\"group_description\":\"Param\\u00e8tres syst\\u00e8me\"}', 1, 'actif', NULL),
(27, 'manage_company_settings', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Gérer les paramètres de l\'entreprise', 'settings', '{\"group_description\":\"Param\\u00e8tres syst\\u00e8me\"}', 1, 'actif', NULL),
(28, 'manage_system_settings', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Gérer les paramètres système', 'settings', '{\"group_description\":\"Param\\u00e8tres syst\\u00e8me\"}', 1, 'actif', NULL),
(29, 'view_billing', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir la facturation', 'billing', '{\"group_description\":\"Gestion de la facturation\"}', 1, 'actif', NULL),
(30, 'manage_subscriptions', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Gérer les abonnements', 'billing', '{\"group_description\":\"Gestion de la facturation\"}', 1, 'actif', NULL),
(31, 'view_invoices', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir les factures', 'billing', '{\"group_description\":\"Gestion de la facturation\"}', 1, 'actif', NULL),
(32, 'manage_payment_methods', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Gérer les moyens de paiement', 'billing', '{\"group_description\":\"Gestion de la facturation\"}', 1, 'actif', NULL),
(33, 'view_departments', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Voir les départements', 'departments', '{\"group_description\":\"Gestion des d\\u00e9partements\"}', 1, 'actif', NULL),
(34, 'create_departments', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Créer des départements', 'departments', '{\"group_description\":\"Gestion des d\\u00e9partements\"}', 1, 'actif', NULL),
(35, 'edit_departments', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Modifier les départements', 'departments', '{\"group_description\":\"Gestion des d\\u00e9partements\"}', 1, 'actif', NULL),
(36, 'delete_departments', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Supprimer des départements', 'departments', '{\"group_description\":\"Gestion des d\\u00e9partements\"}', 1, 'actif', NULL),
(37, 'manage_department_heads', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', 'Gérer les chefs de département', 'departments', '{\"group_description\":\"Gestion des d\\u00e9partements\"}', 1, 'actif', NULL);

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
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
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
  `description` text DEFAULT NULL,
  `prix_mensuel` decimal(10,2) NOT NULL,
  `prix_annuel` decimal(10,2) DEFAULT NULL,
  `duree_essai` int(11) NOT NULL DEFAULT 0,
  `nombre_employes_min` int(11) DEFAULT NULL,
  `nombre_employes_max` int(11) DEFAULT NULL,
  `cout_par_employe` decimal(10,2) DEFAULT NULL,
  `devise` varchar(3) NOT NULL DEFAULT 'XOF',
  `priorite` int(11) NOT NULL DEFAULT 0,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `fonctionnalites` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fonctionnalites`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `plan_abonnements`
--

INSERT INTO `plan_abonnements` (`id`, `nom`, `description`, `prix_mensuel`, `prix_annuel`, `duree_essai`, `nombre_employes_min`, `nombre_employes_max`, `cout_par_employe`, `devise`, `priorite`, `statut`, `fonctionnalites`, `created_at`, `updated_at`, `deleted_at`) VALUES
('019545a4-10ae-70b9-8168-7e2e17512cb2', 'Starter', 'Idéal pour les petites entreprises de 1 à 50 employés', 10000.00, 108000.00, 14, 1, 50, 100.00, 'XOF', 1, 'actif', '\"{\\\"gestion_employes\\\":{\\\"active\\\":true,\\\"limite\\\":50,\\\"description\\\":\\\"Gestion des employ\\\\u00e9s avec profils d\\\\u00e9taill\\\\u00e9s\\\"},\\\"pointage_mobile\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Pointage via application mobile\\\"},\\\"rapports_base\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Rapports de pr\\\\u00e9sence basiques\\\"},\\\"support_email\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Support par email\\\"}}\"', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL),
('019545a4-10b3-7202-962b-07490c15850b', 'Business', 'Pour les entreprises moyennes de 51 à 200 employés', 25000.00, 270000.00, 14, 51, 200, 80.00, 'XOF', 2, 'actif', '\"{\\\"gestion_employes\\\":{\\\"active\\\":true,\\\"limite\\\":200,\\\"description\\\":\\\"Gestion des employ\\\\u00e9s avec profils d\\\\u00e9taill\\\\u00e9s\\\"},\\\"pointage_mobile\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Pointage via application mobile\\\"},\\\"rapports_avances\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Rapports et analyses avanc\\\\u00e9s\\\"},\\\"support_prioritaire\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Support prioritaire par email et t\\\\u00e9l\\\\u00e9phone\\\"},\\\"gestion_conges\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Gestion compl\\\\u00e8te des cong\\\\u00e9s\\\"},\\\"planning_equipe\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Planning d\'\\\\u00e9quipe\\\"}}\"', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL),
('019545a4-10b5-71fe-ab9e-f93dc172b428', 'Entreprise', 'Solution complète pour les grandes entreprises', 50000.00, 540000.00, 14, 201, NULL, 50.00, 'XOF', 3, 'actif', '\"{\\\"gestion_employes\\\":{\\\"active\\\":true,\\\"limite\\\":null,\\\"description\\\":\\\"Gestion des employ\\\\u00e9s avec profils d\\\\u00e9taill\\\\u00e9s\\\"},\\\"pointage_multiple\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Toutes les m\\\\u00e9thodes de pointage (Mobile, QR, Biom\\\\u00e9trique)\\\"},\\\"rapports_personnalises\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Rapports personnalisables et exports automatiques\\\"},\\\"support_dedie\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Support d\\\\u00e9di\\\\u00e9 24\\\\\\/7\\\"},\\\"gestion_conges\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Gestion avanc\\\\u00e9e des cong\\\\u00e9s et absences\\\"},\\\"planning_avance\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Planning multi-\\\\u00e9quipes\\\"},\\\"api_integration\\\":{\\\"active\\\":true,\\\"description\\\":\\\"API pour int\\\\u00e9grations personnalis\\\\u00e9es\\\"},\\\"multi_sites\\\":{\\\"active\\\":true,\\\"description\\\":\\\"Gestion multi-sites\\\"}}\"', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL);

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

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `entreprise_id`, `description`, `meta_data`, `is_system`, `statut`, `deleted_at`) VALUES
(1, 'super_admin', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL, 'Super Administrateur du système', NULL, 1, 'actif', NULL),
(2, 'admin', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL, 'Administrateur', NULL, 1, 'actif', NULL),
(3, 'manager', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL, 'Manager de département', NULL, 1, 'actif', NULL),
(4, 'rh', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL, 'Responsable des Ressources Humaines', NULL, 1, 'actif', NULL),
(5, 'employee', 'web', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL, 'Employé', NULL, 1, 'actif', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(4, 1),
(5, 1),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(7, 1),
(7, 2),
(7, 4),
(8, 1),
(8, 2),
(8, 4),
(9, 1),
(10, 1),
(11, 1),
(11, 2),
(11, 3),
(11, 4),
(11, 5),
(12, 1),
(12, 2),
(12, 4),
(13, 1),
(13, 2),
(13, 3),
(13, 4),
(14, 1),
(15, 1),
(16, 1),
(16, 2),
(16, 3),
(16, 4),
(16, 5),
(17, 1),
(17, 5),
(18, 1),
(18, 2),
(18, 3),
(18, 4),
(19, 1),
(19, 2),
(19, 3),
(19, 4),
(20, 1),
(20, 4),
(21, 1),
(21, 2),
(21, 3),
(21, 4),
(22, 1),
(22, 2),
(22, 3),
(22, 4),
(23, 1),
(23, 2),
(23, 4),
(24, 1),
(25, 1),
(25, 2),
(26, 1),
(26, 2),
(27, 1),
(27, 2),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(33, 2),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(37, 2);

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

--
-- Déchargement des données de la table `type_conges`
--

INSERT INTO `type_conges` (`id`, `entreprise_id`, `nom`, `description`, `duree_max_annuelle`, `necessite_justificatif`, `est_paye`, `deductible_solde`, `delai_demande_prealable`, `conditions_eligibilite`, `configuration`, `statut`, `created_at`, `updated_at`, `deleted_at`) VALUES
('019545a4-10c1-7250-b47b-5445f7554326', '019545a4-10b9-7276-8459-83b53405a736', 'Congé annuel', 'Congé annuel payé', 30, 0, 1, 1, 7, '\"{\\\"anciennete_minimum\\\":12,\\\"statut_requis\\\":[\\\"CDI\\\",\\\"CDD\\\"]}\"', '\"{\\\"report_autorise\\\":true,\\\"max_jours_report\\\":10,\\\"fractionnement_autorise\\\":true,\\\"min_jours_fraction\\\":1}\"', 'actif', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL),
('019545a4-10c6-70db-b00c-7911e1ada7d1', '019545a4-10b9-7276-8459-83b53405a736', 'Congé maladie', 'Congé pour raison médicale', NULL, 1, 1, 0, 1, '\"{\\\"anciennete_minimum\\\":0,\\\"statut_requis\\\":[\\\"CDI\\\",\\\"CDD\\\",\\\"Stage\\\",\\\"Prestation\\\"]}\"', '\"{\\\"report_autorise\\\":false,\\\"justificatif_medical_requis\\\":true,\\\"delai_justificatif\\\":48}\"', 'actif', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL),
('019545a4-10c8-715b-a926-2b9ff46f5d4f', '019545a4-10b9-7276-8459-83b53405a736', 'Congé maternité', 'Congé de maternité', NULL, 1, 1, 0, 30, '\"{\\\"anciennete_minimum\\\":0,\\\"statut_requis\\\":[\\\"CDI\\\",\\\"CDD\\\"],\\\"sexe\\\":\\\"F\\\"}\"', '\"{\\\"duree_standard\\\":98,\\\"extension_multiple\\\":14,\\\"justificatif_medical_requis\\\":true}\"', 'actif', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL),
('019545a4-10cd-72b6-ac6e-0830dd7b95e3', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Congé annuel', 'Congé annuel payé', 30, 0, 1, 1, 7, '\"{\\\"anciennete_minimum\\\":12,\\\"statut_requis\\\":[\\\"CDI\\\",\\\"CDD\\\"]}\"', '\"{\\\"report_autorise\\\":true,\\\"max_jours_report\\\":10,\\\"fractionnement_autorise\\\":true,\\\"min_jours_fraction\\\":1}\"', 'actif', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL),
('019545a4-10cf-70e2-b75a-f413f587043a', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Congé maladie', 'Congé pour raison médicale', NULL, 1, 1, 0, 1, '\"{\\\"anciennete_minimum\\\":0,\\\"statut_requis\\\":[\\\"CDI\\\",\\\"CDD\\\",\\\"Stage\\\",\\\"Prestation\\\"]}\"', '\"{\\\"report_autorise\\\":false,\\\"justificatif_medical_requis\\\":true,\\\"delai_justificatif\\\":48}\"', 'actif', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL),
('019545a4-10d2-7375-a725-95259ecd410c', '019545a4-10bc-72db-af81-f688fd8a8ac5', 'Congé maternité', 'Congé de maternité', NULL, 1, 1, 0, 30, '\"{\\\"anciennete_minimum\\\":0,\\\"statut_requis\\\":[\\\"CDI\\\",\\\"CDD\\\"],\\\"sexe\\\":\\\"F\\\"}\"', '\"{\\\"duree_standard\\\":98,\\\"extension_multiple\\\":14,\\\"justificatif_medical_requis\\\":true}\"', 'actif', '2025-02-27 04:22:14', '2025-02-27 04:22:14', NULL);

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
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `phone_verified_at`, `password`, `pin`, `pin_changed_at`, `require_pin_change`, `pin_attempts`, `pin_locked_until`, `email_verified_at`, `entreprise_id`, `role`, `statut`, `telephone`, `photo`, `langue`, `fuseau_horaire`, `settings`, `preferences`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `employeur_id`) VALUES
('019545a4-1469-7323-91f2-a5a5eb3d88e0', 'Super Admin', 'admin@geniuswork.com', '+22501234567', '2025-02-27 04:22:15', '$2y$12$FyuHgUPTimsDYqRyMZjTcuaGAn/Jxm3mOgwnRTgS5E7MiGloyZEw6', '$2y$12$/o18SoPz8M9mvhSnixdF4eigzP9umJBrkm5z3Odg70tv5IKbjEmia', '2025-02-27 04:22:16', 0, 0, NULL, '2025-02-27 04:22:15', NULL, 'employeur', 'actif', NULL, NULL, 'fr', 'UTC', NULL, '{\"langue\":\"fr\",\"fuseau_horaire\":\"Africa\\/Abidjan\",\"notifications\":{\"email\":true,\"sms\":true,\"push\":true}}', NULL, NULL, NULL, '2025-02-27 04:22:16', '2025-02-27 04:22:16', NULL, NULL),
('019545a4-1868-7270-aede-83473a6c9994', 'Admin TechCorp', 'admin@techcorp.com', '+22502345678', '2025-02-27 04:22:16', '$2y$12$/5nci5.gjCcRZuYVucHoO.AXA9/1m.aH8ULrhOGy570kulJTJGDRu', '$2y$12$1p0oiPM99v/D30xKlGS5t.ArYNibcAnuVsNXr5yehMZ47wftsflze', '2025-02-27 04:22:17', 0, 0, NULL, '2025-02-27 04:22:16', NULL, 'employeur', 'actif', NULL, NULL, 'fr', 'UTC', NULL, '{\"langue\":\"fr\",\"fuseau_horaire\":\"Africa\\/Abidjan\",\"notifications\":{\"email\":true,\"sms\":true,\"push\":true}}', NULL, NULL, NULL, '2025-02-27 04:22:17', '2025-02-27 04:22:17', NULL, NULL),
('019545a4-1c50-724a-bd62-25d0757c8a07', 'Manager TechCorp', 'manager@techcorp.com', '+22503456789', '2025-02-27 04:22:17', '$2y$12$EtcWkOacabkGdEpSkOMgru.g3NxESNYPRN2WCJH6D9EKG52Dn4Itm', '$2y$12$ksdEJzqvstZOhVHxXOtW3./WvsWI00Aiq7.f963lR/DYQie3ZMzW2', '2025-02-27 04:22:18', 0, 0, NULL, '2025-02-27 04:22:17', NULL, 'employeur', 'actif', NULL, NULL, 'fr', 'UTC', NULL, '{\"langue\":\"fr\",\"fuseau_horaire\":\"Africa\\/Abidjan\",\"notifications\":{\"email\":true,\"sms\":true,\"push\":true}}', NULL, NULL, NULL, '2025-02-27 04:22:18', '2025-02-27 04:22:18', NULL, NULL),
('019545a4-2085-703c-b644-5a8707f8c543', 'Employé TechCorp', 'employee@techcorp.com', '+22504567890', '2025-02-27 04:22:18', '$2y$12$EZs12Cct4Fi9SVR0LS8vCOrsM2KvSJnR/wZvLdd9fs8SvpwZeqL/O', '$2y$12$ck7SCN5DkBamDAe7bsxDx.AaINpI2dJ703Qeqa73MT8ExjSigBP.2', '2025-02-27 04:22:19', 0, 0, NULL, '2025-02-27 04:22:18', NULL, 'employeur', 'actif', NULL, NULL, 'fr', 'UTC', NULL, '{\"langue\":\"fr\",\"fuseau_horaire\":\"Africa\\/Abidjan\",\"notifications\":{\"email\":true,\"sms\":true,\"push\":true}}', NULL, NULL, NULL, '2025-02-27 04:22:19', '2025-02-27 04:22:19', NULL, NULL);

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
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

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
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
