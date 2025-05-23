# Documentation - Changement d'abonnement

## Aperçu

Cette fonctionnalité permet aux utilisateurs de changer leur plan d'abonnement en fonction du nombre d'employés de leur entreprise. Le processus comprend les étapes suivantes :

1. L'utilisateur clique sur "Changer de plan" dans l'interface Filament
2. Il est redirigé vers un formulaire où il peut saisir le nombre d'employés souhaité
3. Le système calcule automatiquement le forfait adapté et le coût
4. L'utilisateur confirme et procède au paiement via Paystack
5. Une fois le paiement validé, l'abonnement est mis à jour

## Architecture

La fonctionnalité est construite selon une architecture MVC avec services dédiés :

- **Services** : `ChangeAbonnementService` pour la logique métier
- **Contrôleur** : `ChangeAbonnementController` pour gérer les routes et les vues
- **Vues** : Formulaires et pages de confirmation
- **Routes** : Définies dans `routes/abonnement.php`
- **Intégration Filament** : Action personnalisée dans `AbonnementResource`

## Calcul du coût

Le coût de l'abonnement est calculé en fonction du nombre d'employés :

- **Starter** (1-50 employés) : 10 000 FCFA + 100 FCFA par employé
- **Side Business** (51-100 employés) : 15 000 FCFA + 100 FCFA par employé
- **Enterprise** (101+ employés) : 30 000 FCFA + 100 FCFA par employé

## Processus de paiement

Le paiement est géré via Paystack :

1. Une facturation est créée pour le changement d'abonnement
2. L'utilisateur est redirigé vers la passerelle de paiement Paystack
3. Après paiement, Paystack renvoie l'utilisateur vers notre callback
4. Le système vérifie le statut du paiement et finalise le changement d'abonnement

## Utilisation

### Pour les administrateurs

1. Accédez à la liste des abonnements dans Filament
2. Cliquez sur l'action "Changer de plan" pour un abonnement
3. Suivez le processus guidé

### Pour les développeurs

Pour étendre ou modifier cette fonctionnalité :

```php
// Ajouter une nouvelle méthode de calcul de coût
public function calculerForfaitPersonnalise(int $nombreEmployes): array
{
    // Logique personnalisée
    return [
        'forfait' => 'Personnalisé',
        'cout_total' => $coutCalcule
    ];
}
```

## Dépannage

### Problèmes courants

1. **Erreur lors du paiement** : Vérifiez les logs pour les détails de l'erreur Paystack
2. **Abonnement non mis à jour** : Vérifiez que la méthode `finaliserChangementAbonnement` a été appelée
3. **Mauvais calcul de coût** : Vérifiez les paramètres dans `calculerForfaitEtCout`

### Journalisation

Les événements importants sont journalisés dans `storage/logs/laravel.log` avec le préfixe "Changement d'abonnement".
