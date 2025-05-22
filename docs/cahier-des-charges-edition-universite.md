# Cahier des Charges - Genius Work Édition Université

## 1. Introduction

### 1.1 Contexte

Les universités publiques en Côte d'Ivoire font face à des défis majeurs en matière de gestion administrative, particulièrement concernant le suivi des présences et la traçabilité des étudiants. La gestion manuelle par registres papier entraîne des problèmes significatifs : erreurs fréquentes, perte de données, difficultés à générer des rapports fiables, et manque de communication efficace entre l'administration, les étudiants et leurs parents.

Ces défis sont encore plus prononcés dans les cités universitaires, où la gestion des résidents nécessite un suivi rigoureux pour des raisons de sécurité et d'optimisation des ressources.

### 1.2 Objectifs du Projet

Développer une version spécialisée de Genius Work (précédemment GENIUS WORK) adaptée aux besoins spécifiques des universités publiques ivoiriennes, avec un accent particulier sur :

1. La numérisation complète des processus de gestion des présences
2. La traçabilité des étudiants dans les campus et cités universitaires
3. L'amélioration de la communication entre toutes les parties prenantes
4. La génération de rapports fiables pour l'administration et les autorités de tutelle
5. La sécurisation des campus et résidences universitaires

### 1.3 Portée du Projet

Le projet couvre le développement, le déploiement et la maintenance d'une solution complète de gestion des présences et de traçabilité pour les universités, comprenant :

- Adaptation de l'application Genius Work existante
- Développement de modules spécifiques au contexte universitaire
- Intégration avec les systèmes d'information universitaires existants
- Formation du personnel administratif et support technique
- Déploiement progressif sur les campus et cités universitaires

## 2. État des Lieux de l'Existant

### 2.1 Architecture Actuelle de Genius Work

Genius Work est actuellement une solution SIRH complète avec les composants suivants :

- **Backend** : Application Laravel avec architecture MVC
- **Frontend** : Interface Filament pour l'administration, application mobile pour les utilisateurs
- **Base de données** : Structure multi-tenant permettant la séparation des données par entreprise
- **API** : Endpoints RESTful pour l'interaction avec l'application mobile et systèmes tiers

### 2.2 Fonctionnalités Existantes Pertinentes

Les fonctionnalités suivantes de Genius Work peuvent être adaptées pour l'édition universitaire :

1. **Gestion des présences**
   - Système de pointage multi-méthodes (biométrique, QR code, géolocalisation)
   - Historique des pointages
   - Rapports de présence

2. **Gestion des utilisateurs**
   - Profils utilisateurs avec rôles et permissions
   - Hiérarchie organisationnelle
   - Authentification sécurisée

3. **Gestion des sites**
   - Définition de zones géographiques
   - Paramètres de geofencing
   - Association utilisateurs-sites

4. **Système de notification**
   - Alertes automatisées
   - Notifications push via l'application mobile
   - Historique des communications

5. **Génération de rapports**
   - Tableaux de bord analytiques
   - Exportation de données
   - Rapports personnalisables

### 2.3 Limitations Actuelles pour le Contexte Universitaire

1. **Structure organisationnelle** : Modèle actuel orienté entreprise, non adapté à la hiérarchie universitaire
2. **Échelle** : Optimisé pour des entreprises de taille moyenne, pas pour des milliers d'utilisateurs
3. **Intégrations** : Absence de connecteurs pour systèmes de scolarité universitaire
4. **Fonctionnalités spécifiques** : Absence de modules pour gestion des résidences, cours, et événements académiques
5. **Reporting** : Rapports non adaptés aux exigences ministérielles de l'enseignement supérieur

## 3. Spécifications Fonctionnelles

### 3.1 Architecture Générale

#### 3.1.1 Structure Multi-Campus

- Modèle hiérarchique : Université > Campus > Faculté/École > Département > Filière
- Gestion des bâtiments : Administratifs, Pédagogiques, Résidentiels
- Cartographie interactive des campus

#### 3.1.2 Gestion des Utilisateurs

- **Types d'utilisateurs** :
  - Administration (différents niveaux d'accès)
  - Corps enseignant
  - Personnel technique et de service
  - Étudiants (résidents et non-résidents)
  - Parents/Tuteurs (accès limité)

- **Système de rôles et permissions** :
  - Super Admin (niveau ministériel)
  - Admin Université
  - Admin Campus
  - Admin Résidence
  - Gestionnaire de département
  - Utilisateur standard

#### 3.1.3 Base de Données

- Extension du modèle multi-tenant pour supporter la structure universitaire
- Nouvelles tables pour les entités spécifiques (cours, résidences, événements académiques)
- Optimisation pour la gestion de volumes importants de données

### 3.2 Modules Spécifiques à Développer

#### 3.2.1 Module de Gestion des Résidences Universitaires

- **Fonctionnalités** :
  - Registre numérique des résidents
  - Attribution et gestion des chambres/logements
  - Suivi des entrées/sorties
  - Gestion des visiteurs
  - Rapports d'occupation
  - Alertes automatiques (absences prolongées, incidents)

- **Interface dédiée** :
  - Dashboard pour administrateurs de résidence
  - Vue d'ensemble de l'occupation
  - Recherche et filtrage des résidents
  - Gestion des demandes de logement

#### 3.2.2 Module de Suivi Académique

- **Fonctionnalités** :
  - Intégration avec emploi du temps
  - Suivi des présences aux cours
  - Alertes d'absentéisme
  - Statistiques de fréquentation par cours/filière
  - Rapports pour administration et enseignants

- **Interface dédiée** :
  - Vue calendrier des cours
  - Tableau de bord d'assiduité
  - Génération de rapports personnalisés

#### 3.2.3 Module de Communication Multi-canal

- **Canaux de communication** :
  - SMS (prioritaire pour la Côte d'Ivoire)
  - Email
  - Notifications push (application mobile)
  - Portail web
  - Tableaux d'affichage numériques

- **Types de communications** :
  - Alertes urgentes (sécurité, fermetures exceptionnelles)
  - Informations académiques
  - Notifications administratives
  - Communications générales
  - Alertes d'absentéisme (pour parents/tuteurs)

#### 3.2.4 Module de Reporting Institutionnel

- **Rapports standards** :
  - Statistiques de présence globales
  - Taux d'occupation des résidences
  - Assiduité par filière/département
  - Tendances d'absentéisme

- **Rapports spécifiques** :
  - Formats conformes aux exigences ministérielles
  - Rapports pour conseils d'université
  - Statistiques pour partenaires financiers
  - Indicateurs de performance clés

### 3.3 Adaptations des Fonctionnalités Existantes

#### 3.3.1 Système de Pointage

- **Méthodes à adapter** :
  - Pointage biométrique (optimisation pour grand volume)
  - QR code (intégration avec cartes d'étudiant)
  - Géolocalisation (définition de zones campus)
  - NFC/RFID (compatibilité avec systèmes d'accès existants)

- **Nouvelles fonctionnalités** :
  - Pointage par proxy autorisé (personnel administratif)
  - Validation par enseignant (présence en cours)
  - Pointage groupé (pour événements, examens)
  - Mode hors-ligne renforcé (zones à connectivité limitée)

#### 3.3.2 Application Mobile

- **Adaptations UI/UX** :
  - Interface simplifiée pour étudiants
  - Accès aux emplois du temps
  - Visualisation des présences/absences
  - Demandes administratives (congés, autorisations)

- **Nouvelles fonctionnalités** :
  - Mode campus (informations localisées)
  - Notifications académiques
  - Accès aux ressources universitaires
  - Communication avec administration

#### 3.3.3 Interface d'Administration

- **Adaptations** :
  - Terminologie adaptée au contexte universitaire
  - Tableaux de bord spécifiques par rôle
  - Gestion des périodes académiques
  - Intégration du calendrier universitaire

- **Nouvelles fonctionnalités** :
  - Gestion des événements académiques
  - Suivi des examens et évaluations
  - Administration des résidences
  - Rapports pour autorités de tutelle

## 4. Spécifications Techniques

### 4.1 Architecture Technique

#### 4.1.1 Infrastructure

- **Serveurs** :
  - Application : Serveurs dédiés haute performance
  - Base de données : Cluster optimisé pour lectures/écritures intensives
  - Cache : Redis pour optimisation des performances
  - Stockage : Système redondant pour documents et médias

- **Déploiement** :
  - Option cloud : AWS/Azure avec région Afrique
  - Option on-premise : Serveurs physiques dans l'infrastructure universitaire
  - Option hybride : Données sensibles on-premise, services annexes cloud

#### 4.1.2 Sécurité

- **Authentification** :
  - Multi-facteur pour administrateurs
  - SSO (Single Sign-On) avec systèmes universitaires existants
  - Gestion avancée des sessions

- **Protection des données** :
  - Chiffrement des données sensibles
  - Anonymisation pour statistiques
  - Conformité RGPD et réglementations locales

- **Audit et traçabilité** :
  - Journalisation complète des actions
  - Détection d'activités suspectes
  - Rapports de sécurité périodiques

#### 4.1.3 Performance et Scalabilité

- **Optimisations** :
  - Mise en cache avancée
  - Indexation optimisée pour requêtes fréquentes
  - Traitement asynchrone pour tâches lourdes

- **Scalabilité** :
  - Architecture horizontalement scalable
  - Partitionnement des données par campus/année
  - Load balancing automatique

### 4.2 Intégrations

#### 4.2.1 Systèmes Universitaires

- **Scolarité** :
  - Import/export des données étudiants
  - Synchronisation des inscriptions
  - Partage des résultats académiques

- **Emploi du temps** :
  - Import des plannings de cours
  - Synchronisation des salles et ressources
  - Notifications de changements

- **Bibliothèque** :
  - Vérification des accès
  - Suivi des emprunts
  - Statistiques d'utilisation

#### 4.2.2 Systèmes de Communication

- **Passerelles SMS** :
  - Intégration avec opérateurs locaux
  - Gestion des crédits et rapports de livraison
  - Templates personnalisables

- **Email** :
  - Serveur SMTP dédié
  - Templates responsives
  - Tracking d'ouverture et clics

- **Systèmes d'affichage** :
  - API pour écrans d'information
  - Gestion de contenu centralisée
  - Diffusion ciblée par localisation

#### 4.2.3 Systèmes de Contrôle d'Accès

- **Tourniquets et portes** :
  - Protocoles standards (Wiegand, OSDP)
  - Intégration avec systèmes existants
  - Mode dégradé en cas de panne réseau

- **Lecteurs biométriques** :
  - Compatibilité multi-vendeurs
  - Stockage sécurisé des templates
  - Vérification en temps réel

### 4.3 Interfaces Utilisateurs

#### 4.3.1 Interface Web Administrative

- **Technologies** :
  - Laravel + Filament (existant)
  - Adaptations UI pour contexte universitaire
  - Composants spécifiques pour nouveaux modules

- **Responsive design** :
  - Optimisation pour tous appareils
  - Accessibilité WCAG 2.1 AA
  - Support multilingue (français, anglais)

#### 4.3.2 Application Mobile

- **Technologies** :
  - React Native (cross-platform)
  - Optimisation pour appareils bas/milieu de gamme
  - Mode hors-ligne robuste

- **Fonctionnalités** :
  - Pointage multi-méthodes
  - Notifications push
  - Accès aux informations personnelles
  - Demandes administratives

#### 4.3.3 Kiosques et Bornes

- **Matériel** :
  - Écrans tactiles industriels
  - Lecteurs biométriques intégrés
  - Imprimantes de tickets/reçus

- **Logiciel** :
  - Interface simplifiée pour auto-service
  - Mode kiosque sécurisé
  - Maintenance et mise à jour à distance

## 5. Méthodologie de Développement

### 5.1 Approche Agile

- **Sprints** : Cycles de développement de 2 semaines
- **Revues** : Démonstrations régulières avec parties prenantes
- **Backlog** : Priorisation continue des fonctionnalités
- **Tests utilisateurs** : Implication d'administrateurs et étudiants

### 5.2 Phases de Développement

#### 5.2.1 Phase 1 : Fondations (3 mois)

- Adaptation de l'architecture existante
- Développement du modèle de données universitaire
- Mise en place des intégrations de base
- Prototype des interfaces principales

#### 5.2.2 Phase 2 : Modules Essentiels (4 mois)

- Module de gestion des résidences
- Système de pointage adapté
- Interface administrative universitaire
- Application mobile version beta

#### 5.2.3 Phase 3 : Fonctionnalités Avancées (3 mois)

- Module de communication multi-canal
- Reporting institutionnel
- Intégrations avancées
- Optimisations de performance

#### 5.2.4 Phase 4 : Finalisation et Tests (2 mois)

- Tests de charge et performance
- Correction de bugs
- Documentation complète
- Préparation au déploiement

### 5.3 Assurance Qualité

- **Tests unitaires** : Couverture > 80% du code
- **Tests d'intégration** : Validation des flux complets
- **Tests de performance** : Simulation de charge universitaire réelle
- **Tests de sécurité** : Audit et pentests
- **Tests utilisateurs** : Sessions avec personnel administratif et étudiants

## 6. Déploiement et Formation

### 6.1 Stratégie de Déploiement

#### 6.1.1 Déploiement Pilote

- Sélection d'un campus/résidence représentatif
- Installation de l'infrastructure nécessaire
- Migration des données existantes
- Phase de test en conditions réelles (1-2 mois)
- Collecte de feedback et ajustements

#### 6.1.2 Déploiement Progressif

- Planification par campus/faculté
- Installation du matériel nécessaire
- Formation du personnel local
- Migration des données par phases
- Support renforcé pendant la transition

### 6.2 Formation

#### 6.2.1 Formation Administrateurs

- **Programme** :
  - Administration système
  - Gestion des utilisateurs et permissions
  - Configuration des modules
  - Génération et analyse des rapports
  - Procédures de maintenance

- **Format** :
  - Sessions en présentiel (3-5 jours)
  - Documentation détaillée
  - Vidéos tutorielles
  - Environnement de formation dédié

#### 6.2.2 Formation Utilisateurs

- **Programme pour personnel** :
  - Utilisation quotidienne du système
  - Gestion des présences
  - Communication avec étudiants
  - Résolution des problèmes courants

- **Programme pour étudiants** :
  - Sessions d'information
  - Guides d'utilisation simplifiés
  - Tutoriels dans l'application mobile
  - FAQ et support en ligne

### 6.3 Support et Maintenance

#### 6.3.1 Support Technique

- **Niveaux de support** :
  - Niveau 1 : Support utilisateur quotidien
  - Niveau 2 : Problèmes techniques complexes
  - Niveau 3 : Développement et corrections

- **Canaux de support** :
  - Portail dédié
  - Email
  - Téléphone (heures ouvrables)
  - Support d'urgence 24/7 pour incidents critiques

#### 6.3.2 Maintenance

- **Maintenance préventive** :
  - Mises à jour de sécurité
  - Optimisations de performance
  - Sauvegardes et vérifications

- **Maintenance évolutive** :
  - Nouvelles fonctionnalités
  - Améliorations basées sur feedback
  - Adaptations aux changements réglementaires

## 7. Gouvernance du Projet

### 7.1 Équipe Projet

- **Côté Genius Work** :
  - Chef de projet
  - Architecte solution
  - Développeurs (backend, frontend, mobile)
  - Spécialiste intégration
  - Expert UX/UI
  - Ingénieur QA
  - Support technique

- **Côté Université** :
  - Sponsor projet (niveau direction)
  - Responsable informatique
  - Représentant administration
  - Représentant résidences
  - Représentant corps enseignant
  - Représentant étudiants

### 7.2 Communication et Reporting

- **Réunions régulières** :
  - Comité de pilotage mensuel
  - Réunions d'avancement hebdomadaires
  - Revues de sprint bi-mensuelles

- **Outils de suivi** :
  - Plateforme de gestion de projet partagée
  - Tableau de bord d'avancement
  - Système de tickets pour problèmes/demandes

### 7.3 Gestion des Risques

- **Risques identifiés** :
  - Résistance au changement
  - Problèmes d'infrastructure technique
  - Complexité des intégrations
  - Volumétrie de données sous-estimée

- **Stratégies d'atténuation** :
  - Plan de conduite du changement
  - Audits techniques préalables
  - Prototypes d'intégration précoces
  - Tests de charge avec données réalistes

## 8. Livrables

### 8.1 Livrables Logiciels

- **Applications** :
  - Backend Genius Work Édition Université
  - Interface d'administration adaptée
  - Application mobile étudiants/personnel
  - Modules d'intégration

- **Documentation technique** :
  - Architecture système
  - Modèle de données
  - API et interfaces
  - Guide de déploiement

### 8.2 Livrables Matériels (Optionnels)

- **Spécifications** pour :
  - Bornes de pointage
  - Lecteurs biométriques
  - Kiosques d'information
  - Infrastructure réseau requise

### 8.3 Documentation Utilisateur

- **Manuels** :
  - Guide administrateur
  - Guide utilisateur (personnel)
  - Guide utilisateur (étudiants)
  - Procédures opérationnelles

- **Supports de formation** :
  - Présentations
  - Exercices pratiques
  - Vidéos tutorielles
  - Base de connaissances

## 9. Contraintes et Prérequis

### 9.1 Contraintes Techniques

- Compatibilité avec infrastructure IT existante
- Performance avec connexion internet limitée
- Support des appareils mobiles d'entrée/milieu de gamme
- Sécurité des données conforme aux standards éducatifs

### 9.2 Contraintes Organisationnelles

- Calendrier académique (déploiement pendant vacances)
- Processus de validation administrative
- Implication des représentants étudiants
- Conformité avec directives ministérielles

### 9.3 Prérequis

- Accès aux systèmes d'information existants
- Désignation des interlocuteurs clés
- Infrastructure réseau minimale
- Engagement de la direction universitaire

## 10. Calendrier Prévisionnel

### 10.1 Planning Global

- **Phase d'analyse et conception** : 3 mois
- **Phase de développement** : 10 mois
- **Phase de test et validation** : 2 mois
- **Déploiement pilote** : 2 mois
- **Déploiement général** : 6-12 mois (selon taille de l'université)

### 10.2 Jalons Clés

- **J1** : Validation du cahier des charges
- **J2** : Finalisation de l'architecture technique
- **J3** : Livraison du prototype fonctionnel
- **J4** : Validation des modules essentiels
- **J5** : Recette application complète
- **J6** : Démarrage pilote
- **J7** : Bilan pilote et ajustements
- **J8** : Déploiement général terminé

## 11. Budget et Investissement

### 11.1 Structure de Coûts

- **Développement logiciel** :
  - Adaptation architecture existante
  - Développement nouveaux modules
  - Intégrations spécifiques
  - Tests et assurance qualité

- **Infrastructure** :
  - Serveurs et stockage
  - Licences logicielles tierces
  - Équipements de pointage
  - Réseau et sécurité

- **Services** :
  - Gestion de projet
  - Formation
  - Support initial renforcé
  - Transfert de compétences

### 11.2 Options de Financement

- Budget propre de l'université
- Financement ministériel
- Partenariats avec organismes internationaux
- Modèle de paiement échelonné

## 12. Annexes

### 12.1 Glossaire

Définition des termes techniques et spécifiques au domaine universitaire.

### 12.2 Références

- Standards techniques applicables
- Textes réglementaires pertinents
- Études de cas similaires

### 12.3 Maquettes et Prototypes

- Wireframes des interfaces principales
- Flux utilisateurs
- Exemples de rapports

---

Document préparé par : Équipe Genius Work  
Version : 1.0  
Date : 29 avril 2025  
Classification : Confidentiel
