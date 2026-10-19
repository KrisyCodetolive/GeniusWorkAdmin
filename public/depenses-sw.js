// Service worker du portail des dépenses (périmètre /depense uniquement).
// Il ne met JAMAIS en cache de page ni de requête applicative : un ancien worker servant des pages
// périmées avait provoqué des erreurs 419 (jeton CSRF expiré). Seule la page « hors ligne » et les
// icônes sont conservées, pour afficher un écran propre quand le réseau manque.
const VERSION = 'depenses-v1';
const HORS_LIGNE = '/depense/hors-ligne';
const FICHIERS = [HORS_LIGNE, '/images/depenses/icon-192.png', '/images/depenses/icon-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(VERSION).then((cache) => cache.addAll(FICHIERS)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cles) => Promise.all(cles.filter((cle) => cle !== VERSION).map((cle) => caches.delete(cle))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const requete = event.request;
    if (requete.method !== 'GET') return;

    if (requete.mode === 'navigate') {
        event.respondWith(fetch(requete).catch(() => caches.match(HORS_LIGNE)));
        return;
    }

    const url = new URL(requete.url);
    if (url.origin === self.location.origin && FICHIERS.includes(url.pathname)) {
        event.respondWith(caches.match(requete).then((reponse) => reponse || fetch(requete)));
    }
});
