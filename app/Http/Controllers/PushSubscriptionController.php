<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Enregistre ou met à jour un abonnement push.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'   => 'required|string',
            'public_key' => 'nullable|string',
            'auth_token' => 'nullable|string',
        ]);

        $userId       = auth()->id();
        $endpoint     = $request->input('endpoint');
        $endpointHash = hash('sha256', $endpoint);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'user_id'       => $userId,
                'endpoint'      => $endpoint,
                'endpoint_hash' => $endpointHash,
                'public_key'    => $request->input('public_key'),
                'auth_token'    => $request->input('auth_token'),
                'device_name'   => $this->guessDeviceName($request->userAgent() ?? ''),
            ]
        );

        return response()->json(['status' => 'subscribed']);
    }

    /**
     * Supprime un abonnement push.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('endpoint', $request->input('endpoint'))
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json(['status' => 'unsubscribed']);
    }

    private function guessDeviceName(string $ua): string
    {
        if (str_contains($ua, 'iPhone'))  return 'iPhone';
        if (str_contains($ua, 'iPad'))    return 'iPad';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac'))     return 'Mac';
        return 'Appareil inconnu';
    }
}
