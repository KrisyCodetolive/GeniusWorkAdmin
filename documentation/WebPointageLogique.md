# Logique de Pointage Web - GENIUS WORK

## Vue d'ensemble

Le système de pointage web de GENIUS WORK permet aux employés de pointer leur présence via un QR code unique associé à chaque site. La logique implémentée dans le `WebPointageService` gère l'ensemble du processus de pointage, depuis la génération du QR code jusqu'à l'enregistrement des entrées et sorties.

## Flux de traitement

### 1. Génération du QR Code
- Chaque site possède un QR code unique généré à la demande
- Le QR code contient un token unique qui identifie le site
- Le QR code a une durée de validité de 24 heures

### 2. Processus de Pointage

Lorsqu'un employé scanne le QR code avec son identifiant (qr_code_secret), le système suit la logique suivante :

#### Étape 1: Identification de l'employé
- Le système vérifie si l'identifiant correspond à un employé valide
- Il vérifie également si l'employé appartient à l'entreprise associée au site

#### Étape 2: Détermination du type de pointage (entrée, sortie, pause)
- Le système vérifie si l'employé a déjà pointé aujourd'hui
  - Si aucun pointage n'existe pour la journée → ENTRÉE
  - Si un pointage d'entrée existe sans pointage de sortie correspondant → SORTIE
  - Si le dernier pointage était une sortie → ENTRÉE (nouveau cycle)
  - Si une demande de pause est faite et que l'entreprise l'autorise :
    - Si le dernier pointage était une entrée ou une fin de pause → DÉBUT DE PAUSE
    - Si le dernier pointage était un début de pause → FIN DE PAUSE

#### Étape 3: Vérification des plages horaires (pour les entrées)
- Si l'employé a une plage horaire définie, le système l'utilise
- Sinon, une plage horaire par défaut est appliquée (08h00 - 18h00)
- Le système compare l'heure de pointage avec l'heure de début prévue
  - Si l'employé pointe après l'heure de début → RETARD
  - Le retard est calculé en minutes et enregistré

#### Étape 4: Enregistrement du pointage
- Le système crée un nouvel enregistrement de présence avec :
  - L'identifiant de l'employé
  - Le site de pointage
  - La date et l'heure exactes
  - Le type de pointage (entrée/sortie/pause)
  - Le statut de retard (si applicable)
  - Les coordonnées géographiques (si disponibles)
  - La méthode de pointage (QR Code)

#### Étape 5: Calcul des heures travaillées (pour les sorties)
- Si le pointage est une sortie, le système :
  - Récupère le pointage d'entrée correspondant
  - Calcule la durée entre l'entrée et la sortie
  - Enregistre cette durée dans le pointage de sortie
  - Enregistre le statut de retard (si applicable)
  - Calcul les heures supplémentaires (si applicable) en fonction si l'employé travaille au-delà de la plage horaire

### 3. Vérification de la position géographique
- Si le site a des coordonnées géographiques définies et que l'employé fournit sa position :
  - Le système calcule la distance entre l'employé et le site
  - Si la distance dépasse un seuil configurable (par défaut 100m), le pointage est refusé

### 4. Historique et rapports
- Tous les pointages sont enregistrés et accessibles via l'interface d'historique
- Les administrateurs peuvent consulter les rapports de présence par employé, par site ou par période
- Les statistiques incluent les heures travaillées, les retards et les absences

## Règles spécifiques

1. **Retards** : Un employé est considéré en retard si son pointage d'entrée est postérieur à l'heure de début de sa plage horaire
2. **Absences** : Un employé sans pointage d'entrée pour un jour ouvré est considéré absent
3. **Heures supplémentaires** : Les heures travaillées au-delà de la plage horaire sont comptabilisées comme heures supplémentaires
4. **Géolocalisation** : Le pointage peut être restreint à une zone géographique autour du site (geofencing)
5. **Sécurité** : Seul l'identifiant unique de l'employé (qr_code_secret) permet de valider un pointage
6. **Pauses** : Les pauses sont autorisées uniquement si l'entreprise a activé cette fonctionnalité dans ses politiques

Cette logique assure un suivi précis et fiable des présences tout en offrant la flexibilité nécessaire pour s'adapter aux différentes configurations d'entreprises et de sites.
