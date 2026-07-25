<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@inertia</title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="CuciKuy">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512.png">
    
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
    
    <!-- Service Worker Registration -->
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
              console.log('[PWA] Service Worker registered with scope:', registration.scope);
              
              // Check for updates
              registration.addEventListener('updatefound', function() {
                var newWorker = registration.installing;
                newWorker.addEventListener('statechange', function() {
                  if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    // New version available
                    if (confirm('Versi baru tersedia. Muat ulang untuk memperbarui?')) {
                      window.location.reload();
                    }
                  }
                });
              });
            })
            .catch(function(error) {
              console.error('[PWA] Service Worker registration failed:', error);
            });
          
          // Listen for controller change (new SW activated)
          navigator.serviceWorker.addEventListener('controllerchange', function() {
            window.location.reload();
          });
        });
        
        // Listen for online/offline events
        window.addEventListener('online', function() {
          document.body.classList.remove('offline');
          console.log('[PWA] Online');
        });
        
        window.addEventListener('offline', function() {
          document.body.classList.add('offline');
          console.log('[PWA] Offline');
        });
      }
    </script>
</body>
</html>
