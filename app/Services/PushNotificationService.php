<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Envoie une notification push à un utilisateur.
     *
     * @param User   $user  Destinataire
     * @param string $title Titre de la notification
     * @param string $body  Corps de la notification
     * @param string $url   URL à ouvrir au clic (défaut: /dashboard)
     */
    public function send(User $user, string $title, string $body, string $url = '/dashboard'): void
    {
        if (! class_exists(\Minishlink\WebPush\WebPush::class)) {
            Log::warning('[Push] minishlink/web-push non installé — notification ignorée.');
            return;
        }

        $publicKey  = config('miensafleet.vapid_public_key');
        $privateKey = config('miensafleet.vapid_private_key');
        $subject    = config('miensafleet.vapid_subject');

        if (! $publicKey || ! $privateKey) {
            Log::warning('[Push] Clés VAPID manquantes — notification ignorée.');
            return;
        }

        $subscriptions = PushSubscription::where('user_id', $user->id)->get();
        if ($subscriptions->isEmpty()) return;

        $auth    = ['VAPID' => compact('subject', 'publicKey', 'privateKey')];
        $webPush = new \Minishlink\WebPush\WebPush($auth);

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'url'   => $url,
            'icon'  => '/icons/icon-192x192.png',
            'badge' => '/icons/icon-72x72.png',
        ]);

        $toDelete = [];

        foreach ($subscriptions as $sub) {
            $subscription = \Minishlink\WebPush\Subscription::create([
                'endpoint'       => $sub->endpoint,
                'publicKey'      => $sub->public_key ?? '',
                'authToken'      => $sub->auth_token ?? '',
                'contentEncoding'=> 'aesgcm',
            ]);

            $webPush->queueNotification($subscription, $payload);
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                $toDelete[] = $report->getRequest()->getUri()->__toString();
            }
        }

        // Supprimer les abonnements expirés (code 410)
        if (! empty($toDelete)) {
            PushSubscription::whereIn('endpoint', $toDelete)->delete();
        }
    }

    /**
     * Envoie une notification push à plusieurs utilisateurs.
     */
    public function sendToMany(iterable $users, string $title, string $body, string $url = '/dashboard'): void
    {
        foreach ($users as $user) {
            $this->send($user, $title, $body, $url);
        }
    }
}
