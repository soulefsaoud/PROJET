const CACHE_NAME = 'recettes-ai-v2';
const urlsToCache = [
    '/',
    '/ai-recipes',
    '/css/app.css',
    '/js/app.js',
    '/images/icon-192x192.png',
    '/images/icon2-512x512.png'
];

// Installation du Service Worker ok 2
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Cache ouvert');
                return cache.addAll(urlsToCache);
            })
    );
});

// Activation du Service Worker
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Suppression ancien cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Interception des requêtes
self.addEventListener('fetch', (event) => {
    // Stratégie Cache First pour les ressources statiques
    if (event.request.destination === 'image' ||
        event.request.url.includes('.css') ||
        event.request.url.includes('.js')) {

        event.respondWith(
            caches.match(event.request)
                .then((response) => {
                    return response || fetch(event.request);
                })
        );
    }

    // Stratégie Network First pour les API
    // Stratégie Network First pour les API
    else if (event.request.url.includes('/api/')) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    // Clonez la réponse IMMEDIATEMENT pour éviter l'erreur
                    const responseClone = response.clone();
                    // Met en cache uniquement si la réponse est valide
                    if (response.ok) {
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    // Retournez la réponse originale
                    return response;
                })
                .catch(() => {
                    // Retour au cache si le réseau échoue
                    return caches.match(event.request);
                })
        );
    }


    // Stratégie Cache First pour les pages
    // Stratégie Cache First pour les pages
    else {
        event.respondWith(
            caches.match(event.request)
                .then((cachedResponse) => {
                    // Si la réponse est en cache, on la retourne immédiatement
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Sinon, on fait une requête réseau
                    return fetch(event.request)
                        .then((fetchResponse) => {
                            // Vérifiez que la réponse est valide
                            if (!fetchResponse || !fetchResponse.ok || fetchResponse.type !== 'basic') {
                                return fetchResponse;
                            }
                            // Clonez la réponse AVANT de la retourner ou de la mettre en cache
                            const responseClone = fetchResponse.clone();
                            // Mettez en cache le clone
                            caches.open(CACHE_NAME)
                                .then((cache) => {
                                    cache.put(event.request, responseClone);
                                });
                            // Retournez la réponse originale
                            return fetchResponse;
                        })
                        .catch(() => {
                            // En cas d'échec réseau, retourne la page offline
                            return caches.match('/offline.html');
                        });
                })
        );
    }


// Gestion des recettes en cache pour usage offline
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CACHE_RECIPES') {
        const recipes = event.data.recipes;
        const ingredients = event.data.ingredients;

        caches.open(CACHE_NAME).then((cache) => {
            // Stocke les recettes avec une clé basée sur les ingrédients
            const cacheKey = `/offline-recipes/${btoa(ingredients.join(','))}`;
            const response = new Response(JSON.stringify(recipes), {
                headers: {'Content-Type': 'application/json'}
            });
            cache.put(cacheKey, response);
        });
    }
})
});
