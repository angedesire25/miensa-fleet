<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** Marque une notification comme lue */
    public function markRead(string $id): JsonResponse|RedirectResponse
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        $url = $notification->data['url'] ?? route('dashboard');

        return redirect($url);
    }

    /** Marque toutes les notifications non lues comme lues */
    public function markAllRead(): JsonResponse|RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('swal_success', 'Toutes les notifications ont été marquées comme lues.');
    }
}
