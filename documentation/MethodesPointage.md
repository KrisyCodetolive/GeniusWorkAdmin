# Méthodes de Pointage dans GENIUS WORK

GENIUS WORK propose deux méthodes principales de pointage pour répondre aux différents besoins des entreprises :

## 1. Pointage par QR Code Web

### Principe
Le pointage par QR Code Web permet aux employés d'effectuer leur pointage directement depuis leur smartphone en scannant un QR code affiché sur le site de travail.

### Fonctionnement
1. **Génération du QR Code**
   - Chaque site possède un QR code unique généré automatiquement
   - Le QR code contient un token qui identifie le site
   - Le QR code est valide pendant 24 heures

2. **Processus de Pointage**
   - L'employé scanne le QR code avec son smartphone
   - Il est redirigé vers une page web sécurisée
   - Il saisit son identifiant unique (qr_code_secret)
   - Le système vérifie l'identité et détermine le type de pointage (entrée, sortie, pause)
   - L'employé reçoit une confirmation de son pointage

### Avantages
- Ne nécessite aucun matériel spécifique autre qu'un smartphone
- Fonctionne même en déplacement ou sur des sites temporaires
- Permet la géolocalisation pour vérifier la présence réelle sur site
- Expérience utilisateur fluide et moderne

## 2. Pointage par QR Code Physique

### Principe
Le pointage par QR Code Physique utilise un scanner fixe qui lit les QR codes imprimés sur les badges des employés.

### Fonctionnement
1. **Configuration du Terminal de Pointage**
   - Installation d'un terminal dédié sur le site (ordinateur, tablette)
   - Le terminal exécute l'application Smart Clock de GENIUS WORK
   - Le terminal est équipé d'un scanner de QR code

2. **Processus de Pointage**
   - L'employé présente son badge avec QR code devant le scanner
   - Le système lit automatiquement le QR code contenant l'identifiant unique de l'employé
   - Le système détermine le type de pointage (entrée, sortie, pause)
   - Une confirmation visuelle et sonore est affichée sur le terminal
   - L'employé peut voir son statut et l'historique récent des pointages

### Avantages
- Processus très rapide (pas de saisie manuelle)
- Idéal pour les sites fixes avec un grand nombre d'employés
- Expérience utilisateur simplifiée
- Confirmation visuelle et sonore immédiate
- Centralisation des pointages sur un terminal dédié

## Comparaison des Méthodes

| Critère | QR Code Web | QR Code Physique |
|---------|-------------|------------------|
| Matériel requis | Smartphone personnel | Terminal + Scanner |
| Rapidité | Moyenne (plusieurs étapes) | Élevée (scan instantané) |
| Mobilité | Excellente | Limitée (fixe) |
| Géolocalisation | Oui | Non (fixe au site) |
| Convient pour | Petites équipes, équipes mobiles | Grandes équipes, sites fixes |
| Coût d'installation | Très faible | Modéré |

## Implémentation Technique

Les deux méthodes utilisent le même service sous-jacent (`WebPointageService`) pour traiter les pointages, garantissant une cohérence dans la logique métier et les données enregistrées. La différence principale réside dans l'interface utilisateur et le mode d'acquisition de l'identifiant de l'employé.

### Flux de données commun
1. Identification de l'employé via son identifiant unique
2. Vérification de l'appartenance au site/entreprise
3. Détermination du type de pointage
4. Enregistrement du pointage avec horodatage
5. Calcul des retards, heures supplémentaires et durées de pause
6. Confirmation du pointage

## Recommandations d'Utilisation

- **Petites entreprises avec un seul site** : QR Code Web
- **Entreprises avec équipes mobiles** : QR Code Web
- **Grandes entreprises avec sites fixes** : QR Code Physique
- **Solution hybride** : Utilisation des deux méthodes selon les besoins spécifiques des différents sites
