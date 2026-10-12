// Auto-désinstallation : ce projet n'utilise pas de service worker.
// Un ancien worker enregistré sur ce domaine (autre projet) pouvait servir des pages
// en cache avec un jeton CSRF périmé, d'où des erreurs 419 « Page Expired » au login.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        for (const cle of await caches.keys()) await caches.delete(cle);
        await self.registration.unregister();
        for (const client of await self.clients.matchAll({ type: 'window' })) client.navigate(client.url);
    })());
});
