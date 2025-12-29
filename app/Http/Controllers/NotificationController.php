<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $items = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('items'));
    }

    // DIPANGGIL AJAX dari app.blade
    public function unread()
    {
        $items = auth()->user()
            ->unreadNotifications()
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($n) {
                return [
                    'id'      => $n->id,
                    'title'   => $n->data['title'] ?? 'Notifikasi',
                    'message' => $n->data['message'] ?? ($n->data['body'] ?? ''),
                    'time'    => optional($n->created_at)->diffForHumans(),
                    'url'     => $n->data['url'] ?? '#',
                ];
            });

        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }

    public function markAsRead($id)
    {
        $notif = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notif->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
