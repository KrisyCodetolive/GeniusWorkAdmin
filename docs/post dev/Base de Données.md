# Base de Données Genius Work SaaS

La conception de la base de données que vous proposez est solide et bien structurée pour répondre aux besoins de **Genius Work SaaS**. L'utilisation d'une **base de données unique** avec un **tenant_id** (UUID) pour filtrer les données en fonction de l'utilisateur connecté est une excellente approche pour garantir la sécurité et l'isolation des données entre les tenants. Voici une version optimisée et clarifiée de la conception de la base de données, en tenant compte de vos spécifications et en ajoutant quelques améliorations pour une meilleure gestion des données.

---

### **1\. Tables Principales**

#### **A. Tables liées aux Entreprises (Tenants)**

1.  **Entreprise** :
    - `IDEntreprise` (PK, UUID)
    - `Nom`
    - `Adresse`
    - `Contact`
    - `Email`
    - `DateCreation`
    - `Statut` (Actif/Inactif)
2.  **PlanAbonnement** :
    - `IDPlanAbonnement` (PK, UUID)
    - `Type` (Starter, Side Business, Entreprise)
    - `Description`
    - `PrixMensuel`
    - `NombreMaxUtilisateurs`
3.  **Abonnement** :
    - `IDAbonnement` (PK, UUID)
    - `IDEntreprise` (FK, UUID)
    - `IDPlanAbonnement` (FK, UUID)
    - `DateDebut`
    - `DateFin`
    - `Statut` (Actif/Inactif)
4.  **Facturation** :
    - `IDFacturation` (PK, UUID)
    - `IDEntreprise` (FK, UUID)
    - `Montant`
    - `DateFacturation`
    - `StatutPaiement` (Payé/En attente)
    - `Details` (Détails des frais : SMS, emails, etc.)
5.  **FraisUsage** :
    - `IDFraisUsage` (PK, UUID)
    - `IDEntreprise` (FK, UUID)
    - `Type` (SMS, Email, etc.)
    - `Quantite`
    - `CoutUnitaire`
    - `Date`

---

#### **B. Tables liées aux Employés et Présences**

1.  **Employeur** :
    - `IDEmployeur` (PK, UUID)
    - `IDEntreprise` (FK, UUID)
    - `IDDepartement` (FK, UUID)
    - `Nom`
    - `Prenom`
    - `Code`
    - `DateEmbauche`
    - `Image`
    - `IDAdresse` (FK, UUID)
    - `DateNaissance`
    - `Statut` (Actif/Inactif)
2.  **Departement** :
    - `IDDepartement` (PK, UUID)
    - `IDEntreprise` (FK, UUID)
    - `Nom`
    - `Description`
3.  **JourTravail** :
    - `IDJourTravail` (PK, UUID)
    - `HeureDebut`
    - `HeureFin`
4.  **PlageHoraire** :
    - `IDPlageHoraire` (PK, UUID)
    - `HeureDebut`
    - `HeureFin`
5.  **Jour** :
    - `IDJour` (PK, UUID)
    - `IDJourTravail` (FK, UUID)
    - `IDEmployeur` (FK, UUID)
    - `IDPlageHoraire` (FK, UUID)
6.  **Presence** :
    - `IDPresence` (PK, UUID)
    - `IDEmployeur` (FK, UUID)
    - `Type` (Entrée/Sortie)
    - `Statut` (Présent/Absent)
    - `Source` (QR Code, Biométrie, Mobile)
    - `IDRaisonSortie` (FK, UUID)
    - `DateHeure`
7.  **RaisonSortie** :
    - `IDRaisonSortie` (PK, UUID)
    - `Motif`
    - `Description`
    - `Statut` (Actif/Inactif)
8.  **Supplementaire** :
    - `IDSupplementaire` (PK, UUID)
    - `IDEmployeur` (FK, UUID)
    - `Date`
    - `NombreHeures`
    - `Statut` (Approuvé/En attente)
9.  **Permutation** :
    - `IDPermutation` (PK, UUID)
    - `IDEmployeur` (FK, UUID)
    - `PlageHoraire1`
    - `PlageHoraire2`
    - `Date`
    - `Statut` (Approuvé/En attente)

---

#### **C. Tables liées aux Congés**

1.  **Conges** :
    - `IDConges` (PK, UUID)
    - `IDEmployeur` (FK, UUID)
    - `IDTypeConges` (FK, UUID)
    - `DateDebut`
    - `DateFin`
    - `Duree`
    - `Statut` (Approuvé/En attente/Rejeté)
2.  **TypeConges** :
    - `IDTypeConges` (PK, UUID)
    - `Nom`
    - `Description`

---

#### **D. Tables liées aux Notifications**

1.  **Notification** :
    - `IDNotification` (PK, UUID)
    - `IDEmployeur` (FK, UUID)
    - `Message`
    - `DateEnvoi`
    - `Statut` (Envoyé/Non envoyé)
2.  **ParametresNotification** :
    - `IDParametresNotification` (PK, UUID)
    - `IDEntreprise` (FK, UUID)
    - `NotifierAbsences` (Oui/Non)
    - `NotifierRetards` (Oui/Non)
    - `NotifierConges` (Oui/Non)
    - `PersonnalisationSMS` (Texte personnalisé)

---

#### **E. Tables liées aux Politiques d'Entreprise**

1.  **Politiques** :
    - `IDPolitique` (PK, UUID)
    - `IDEntreprise` (FK, UUID)
    - `AnnulerHorairesSiSortieManquee` (Oui/Non)
    - `NombrePointagesParJour`
    - `ActiverPauses` (Oui/Non)
    - `ActiverHeuresSupplementaires` (Oui/Non)
    - `NotifierUtilisateurs` (Oui/Non)

---

#### **F. Tables liées au Tracking et Méthodes de Pointage**

1.  **Tracking** :
    - `IDTracking` (PK, UUID)
    - `IDEmployeur` (FK, UUID)
    - `Type` (Entrée/Sortie)
    - `Location` (Coordonnées GPS)
    - `DateHeure`
    - `Statut` (Validé/Non validé)
2.  **MethodePointage** :
    - `IDMethodePointage` (PK, UUID)
    - `Methode` (QR Code, Biométrie, Mobile)
    - `Valeur` (Code QR, Empreinte digitale, etc.)

---

### **2\. Tables SaaS (Multi-tenancy)**

1.  **Tenant** :
    - `IDTenant` (PK, UUID)
    - `IDEntreprise` (FK, UUID)
    - `Nom`
    - `Domaine`
    - `Statut` (Actif/Inactif)
2.  **UtilisateurTenant** :
    - `IDUtilisateurTenant` (PK, UUID)
    - `IDTenant` (FK, UUID)
    - `IDEmployeur` (FK, UUID)
    - `Role` (Admin, Employé)
    - `Statut` (Actif/Inactif)

---

### **3\. Autres Tables Utiles**

1.  **Adresse** :
    - `IDAdresse` (PK, UUID)
    - `Commune`
    - `Quartier`
    - `Description`
2.  **Responsable** :
    - `IDResponsable` (PK, UUID)
    - `Nom`
    - `Prenom`
    - `Contact`
    - `IDDepartement` (FK, UUID)
3.  **HistoriqueModifications** :
    - `IDHistorique` (PK, UUID)
    - `IDEmployeur` (FK, UUID)
    - `Action` (Modification, Suppression, etc.)
    - `DateHeure`
    - `Details`

---

### **4\. Relations entre les Tables**

- **Entreprise** a plusieurs **Departements**.
- **Departement** a plusieurs **Employeurs**.
- **Employeur** a plusieurs **Presences**, **Conges**, et **Supplementaires**.
- **Presence** est liée à une **RaisonSortie**.
- **Conges** est lié à un **TypeConges**.
- **Entreprise** a un **PlanAbonnement** et des **Facturations**.
- **Entreprise** définit des **Politiques** et des **ParametresNotification**.
- **Tracking** et **MethodePointage** sont liés à **Employeur**.

---

### **5\. Améliorations et Bonnes Pratiques**

1.  **UUID pour les Clés Primaires** :
    - L'utilisation d'UUID pour les clés primaires améliore la sécurité en rendant les IDs difficiles à deviner.
    - Cela facilite également la fusion de données provenant de différentes sources.
2.  **Indexation** :
    - Ajouter des index sur les colonnes fréquemment interrogées (comme `IDEntreprise`, `IDEmployeur`, etc.) pour améliorer les performances des requêtes.
3.  **Soft Deletes** :
    - Ajouter une colonne `deleted_at` dans les tables pour permettre la suppression douce (soft delete) et conserver l'historique des données.
4.  **Audit des Modifications** :
    - La table **HistoriqueModifications** permet de suivre toutes les modifications apportées aux données, ce qui est essentiel pour la traçabilité et la sécurité.
5.  **Sécurité des Données** :
    - Chiffrer les données sensibles (comme les emails, les numéros de téléphone, etc.) dans la base de données.
    - Utiliser des rôles et des permissions pour limiter l'accès aux données sensibles.

---

### **Conclusion**

Cette conception de base de données est optimisée pour **Genius Work SaaS**, avec une structure claire et sécurisée. L'utilisation d'UUID pour les clés primaires et le filtrage des données par `tenant_id` garantit une isolation efficace des données entre les tenants. Cette approche est scalable et adaptée aux besoins des entreprises de toutes tailles.