@component('mail::message')
<div style="text-align: center; margin-bottom: 20px;">
    <img src="https://work.dia.ci/images/logo/logo2.png" alt="Genius Work Logo" style="max-width: 200px;">
</div>

# Bienvenue sur **Genius Work**, {{ $user->name }} !

<div style="background-color: #f8fafc; border-left: 4px solid #4f46e5; padding: 15px; margin: 20px 0;">
    Félicitations ! ✨ Votre compte a été créé avec succès et votre abonnement est maintenant actif. Nous sommes ravis de vous compter parmi nos utilisateurs.
</div>

## Détails de votre compte
- **Nom d'utilisateur** : {{ $user->name }}
- **Email** : {{ $user->email }}
- **Entreprise** : {{ $entreprise->nom }}

## Détails de l'abonnement
- **Plan** : {{ $abonnement->planAbonnement->nom }}
- **Date de début** : {{ $abonnement->date_debut->format('d/m/Y') }}
- **Date de fin** : {{ $abonnement->date_fin->format('d/m/Y') }}

Vous pouvez dès maintenant vous connecter à votre tableau de bord et commencer à utiliser **Genius Work** pour optimiser la gestion de votre entreprise.

@component('mail::button', ['url' => $dashboardUrl, 'color' => 'primary'])
Accéder à mon tableau de bord
@endcomponent

## Besoin d'aide ?

Notre équipe est disponible pour vous accompagner dans la prise en main de **Genius Work** :

- 💬 **WhatsApp** : [Discuter avec Jérémie N'da]({{ config('paiement.support.whatsapp') }})
- 📞 **Téléphone** : [{{ config('paiement.support.telephone') }}](tel:+2250704750465)
- 📧 **Email** : [{{ config('paiement.support.email') }}](mailto:{{ config('paiement.support.email') }})
- 🕐 **Horaires** : {{ config('paiement.support.horaires') }}

<div style="margin: 30px 0; text-align: center;">
    <a href="https://www.linkedin.com/showcase/geniusworkapp" style="margin: 0 10px; text-decoration: none;">
        <img src="https://cdn-icons-png.flaticon.com/512/174/174857.png" alt="LinkedIn" width="24" height="24">
    </a>
    <a href="https://work.genius.ci" style="margin: 0 10px; text-decoration: none;">
        <img src="https://cdn-icons-png.flaticon.com/512/1150/1150626.png" alt="Website" width="24" height="24">
    </a>
    <a href="tel:+2250704750465" style="margin: 0 10px; text-decoration: none;">
        <img src="https://cdn-icons-png.flaticon.com/512/552/552489.png" alt="Phone" width="24" height="24">
    </a>
</div>

Merci de votre confiance,<br>
L'équipe {{ config('app.name') }}

@component('mail::subcopy')
Si vous rencontrez des problèmes avec le bouton ci-dessus, copiez et collez l'URL suivante dans votre navigateur : {{ $dashboardUrl }}
@endcomponent
@endcomponent
