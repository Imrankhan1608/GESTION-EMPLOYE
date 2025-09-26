self.addEventListener('install', (event) => {
  console.log('Service Worker installé');
});

self.addEventListener('fetch', (event) => {
  // Tu peux gérer la mise en cache ici si tu veux
});
self.addEventListener('install', (e) => {
  console.log('Service Worker installé');
});
