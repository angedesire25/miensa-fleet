<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PWA — Progressive Web App
    |--------------------------------------------------------------------------
    */

    'pwa_enabled' => env('PWA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | VAPID — Web Push Notifications
    |
    | Générer les clés :
    |   php artisan tinker
    |   \Minishlink\WebPush\VAPID::createVapidKeys()
    |
    | Puis ajouter dans .env :
    |   VAPID_PUBLIC_KEY=...
    |   VAPID_PRIVATE_KEY=...
    |--------------------------------------------------------------------------
    */

    'vapid_public_key'  => env('VAPID_PUBLIC_KEY'),
    'vapid_private_key' => env('VAPID_PRIVATE_KEY'),
    'vapid_subject'     => env('VAPID_SUBJECT', 'mailto:admin@miensafleet.ci'),

];
