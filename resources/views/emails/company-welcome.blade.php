@component('mail::message')
<div style="text-align: center; margin-bottom: 20px;">
    <img src="https://work.dia.ci/images/logo/logo2.png" alt="Genius Work Logo" style="max-width: 200px;">
</div>

# Bienvenue sur **Genius Work**, {{ $entreprise->nom }} !

<div style="background-color: #f8fafc; border-left: 4px solid #4f46e5; padding: 15px; margin: 20px 0;">
    Nous sommes ravis d'accueillir votre entreprise sur notre plateforme Genius Work. ✨ Votre abonnement a été activé avec succès et vous pouvez dès maintenant profiter de tous nos services premium.
</div>

## Détails de l'entreprise
- **Nom de l'entreprise** : {{ $entreprise->nom }}
- **Secteur d'activité** : {{ $entreprise->secteur_activite }}
- **Administrateur** : {{ $user->name }}

## Détails de l'abonnement
- **Plan** : {{ $planNom }}
- **Date de début** : {{ $dateDebut }}
- **Date de fin** : {{ $dateFin }}

<div style="background-color: #f0f9ff; border-radius: 8px; padding: 15px; margin: 20px 0;">
    <h3 style="margin-top: 0; color: #0369a1;">Avec Genius Work, votre entreprise peut :</h3>
    <ul style="list-style-type: none; padding-left: 0;">
        <li style="margin-bottom: 8px;">✅ Gérer efficacement vos ressources humaines</li>
        <li style="margin-bottom: 8px;">✅ Suivre les présences en temps réel</li>
        <li style="margin-bottom: 8px;">✅ Optimiser votre productivité</li>
        <li style="margin-bottom: 8px;">✅ Analyser vos performances</li>
        <li style="margin-bottom: 8px;">✅ Centraliser toutes vos données professionnelles</li>
    </ul>
</div>

@component('mail::button', ['url' => $dashboardUrl, 'color' => 'primary'])
Accéder au tableau de bord
@endcomponent

## Besoin d'assistance ?

Notre équipe d'experts est disponible pour vous aider à tirer le meilleur parti de **Genius Work** :

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
