# Use cases spécifiques pour Genius Work SaaS

Voici une liste détaillée de **use cases spécifiques** pour **Genius Work SaaS**, basée sur les fonctionnalités proposées. Ces use cases décrivent les interactions entre les différents acteurs (administrateurs, employés, responsables, etc.) et le système.

---

### **A. Use Cases pour les Administrateurs (Super Admin)**

#### **1\. Gestion des Plans d'Abonnement**

- **Use Case** : Créer un nouveau plan d'abonnement.
    - **Acteur** : Super Admin.
    - **Description** : Le Super Admin crée un nouveau plan d'abonnement (Starter, Side Business, Entreprise) avec des limites d'utilisateurs et un prix mensuel.
    - **Préconditions** : Aucune.
    - **Postconditions** : Le plan est disponible pour les entreprises.
- **Use Case** : Modifier un plan d'abonnement.
    - **Acteur** : Super Admin.
    - **Description** : Le Super Admin modifie les détails d'un plan existant (prix, limites d'utilisateurs, etc.).
    - **Préconditions** : Le plan existe.
    - **Postconditions** : Les modifications sont appliquées.

#### **2\. Gestion des Tenants (Entreprises)**

- **Use Case** : Créer un nouveau tenant (entreprise).
    - **Acteur** : Super Admin.
    - **Description** : Le Super Admin enregistre une nouvelle entreprise avec ses informations de base (nom, adresse, contact, etc.) et lui attribue un plan d'abonnement puis lui créer un utilisateur associé avec les droits appropriés.
    - **Préconditions** : Aucune.
    - **Postconditions** : L'entreprise est créée et peut configurer son compte.
- **Use Case** : Désactiver un tenant.
    - **Acteur** : Super Admin.
    - **Description** : Le Super Admin désactive un tenant (entreprise) en cas de non-paiement ou d'inactivité.
    - **Préconditions** : Le tenant existe.
    - **Postconditions** : Le tenant est désactivé et ne peut plus accéder au système.

---

### **B. Use Cases pour les Entreprises (Tenants)**

#### **1\. Gestion des Politiques d'Entreprise**

- **Use Case** : Configurer les politiques de pointage.
    - **Acteur** : Admin de l'entreprise.
    - **Description** : L'admin configure les politiques de pointage (nombre de pointages par jour, annulation des horaires si sortie manquée, etc.).
    - **Préconditions** : L'entreprise est active.
    - **Postconditions** : Les politiques sont appliquées.
- **Use Case** : Activer/désactiver les notifications.
    - **Acteur** : Admin de l'entreprise.
    - **Description** : L'admin active ou désactive les notifications (SMS/emails) pour les absences, retards, et congés.
    - **Préconditions** : L'entreprise est active.
    - **Postconditions** : Les notifications sont activées ou désactivées.

#### **2\. Gestion des Départements et Employés**

- **Use Case** : Créer un nouveau département.
    - **Acteur** : Admin de l'entreprise.
    - **Description** : L'admin crée un nouveau département et lui attribue un responsable.
    - **Préconditions** : L'entreprise est active.
    - **Postconditions** : Le département est créé.
- **Use Case** : Importer des employés en masse.
    - **Acteur** : Admin de l'entreprise.
    - **Description** : L'admin importe une liste d'employés via un fichier CSV/Excel.
    - **Préconditions** : L'entreprise est active.
    - **Postconditions** : Les employés sont ajoutés au système.

#### **3\. Gestion des Présences**

- **Use Case** : Visualiser les présences en temps réel.
    - **Acteur** : Admin de l'entreprise.
    - **Description** : L'admin consulte les présences en temps réel pour tous les employés.
    - **Préconditions** : L'entreprise est active.
    - **Postconditions** : Les présences sont affichées.
- **Use Case** : Enregistrer une absence manuelle.
    - **Acteur** : Admin de l'entreprise.
    - **Description** : L'admin enregistre manuellement une absence pour un employé.
    - **Préconditions** : L'employé existe.
    - **Postconditions** : L'absence est enregistrée.

#### **4\. Gestion des Congés**

- **Use Case** : Valider une demande de congé.
    - **Acteur** : Responsable de département.
    - **Description** : Le responsable valide ou rejette une demande de congé soumise par un employé.
    - **Préconditions** : La demande de congé existe.
    - **Postconditions** : La demande est approuvée ou rejetée.
- **Use Case** : Consulter les soldes de congés.
    - **Acteur** : Admin de l'entreprise.
    - **Description** : L'admin consulte les soldes de congés pour tous les employés.
    - **Préconditions** : L'entreprise est active.
    - **Postconditions** : Les soldes de congés sont affichés.

---

### **C. Use Cases pour les Employés**

#### **1\. Pointage**

- **Use Case** : Pointer via QR Code.
    - **Acteur** : Employé.
    - **Description** : L'employé pointe en scannant un QR Code à l'entrée ou à la sortie.
    - **Préconditions** : L'employé est actif.
    - **Postconditions** : Le pointage est enregistré.
- **Use Case** : Pointer via application mobile.
    - **Acteur** : Employé.
    - **Description** : L'employé pointe via l'application mobile avec validation de la géolocalisation.
    - **Préconditions** : L'employé est actif.
    - **Postconditions** : Le pointage est enregistré.

#### **2\. Gestion des Congés**

- **Use Case** : Soumettre une demande de congé.
    - **Acteur** : Employé.
    - **Description** : L'employé soumet une demande de congé avec une date de début et de fin.
    - **Préconditions** : L'employé est actif.
    - **Postconditions** : La demande est soumise pour validation.
- **Use Case** : Consulter l'état d'une demande de congé.
    - **Acteur** : Employé.
    - **Description** : L'employé consulte l'état (en attente, approuvé, rejeté) d'une demande de congé.
    - **Préconditions** : La demande de congé existe.
    - **Postconditions** : L'état de la demande est affiché.

#### **3\. Notifications**

- **Use Case** : Recevoir une notification de retard.
    - **Acteur** : Employé.
    - **Description** : L'employé reçoit une notification par SMS/email en cas de retard.
    - **Préconditions** : L'employé est actif.
    - **Postconditions** : La notification est reçue.

---

### **D. Use Cases pour les Responsables de Département**

#### **1\. Gestion des Employés**

- **Use Case** : Consulter les présences d'un employé.
    - **Acteur** : Responsable de département.
    - **Description** : Le responsable consulte les présences et les absences d'un employé.
    - **Préconditions** : L'employé existe.
    - **Postconditions** : Les présences sont affichées.

#### **2\. Gestion des Congés**

- **Use Case** : Approuver une demande de congé.
    - **Acteur** : Responsable de département.
    - **Description** : Le responsable approuve une demande de congé soumise par un employé.
    - **Préconditions** : La demande de congé existe.
    - **Postconditions** : La demande est approuvée.

---

### **E. Use Cases Techniques**

#### **1\. Gestion des Notifications**

- **Use Case** : Envoyer une notification par SMS.
    - **Acteur** : Système.
    - **Description** : Le système envoie une notification par SMS à un employé en cas d'absence ou de retard.
    - **Préconditions** : L'employé est actif.
    - **Postconditions** : La notification est envoyée.

#### **2\. Génération de Rapports**

- **Use Case** : Générer un rapport de présences.
    - **Acteur** : Système.
    - **Description** : Le système génère un rapport des présences pour une période donnée.
    - **Préconditions** : Les données de présence existent.
    - **Postconditions** : Le rapport est généré et disponible au téléchargement.

---

### **Conclusion**

Ces use cases couvrent les interactions principales entre les acteurs et le système **Genius Work SaaS**. Ils permettent de structurer le développement en se concentrant sur les besoins spécifiques des utilisateurs (administrateurs, employés, responsables, etc.) et en garantissant une expérience utilisateur fluide et intuitive.