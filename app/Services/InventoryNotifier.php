<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\InventoryNotification;
use Illuminate\Support\Facades\Notification;

class InventoryNotifier
{
    public static function notifyAll(string $title, string $message, string $type = 'info', ?string $url = null, array $meta = []): void
    {
        $users = User::query()->whereNotNull('id')->get(); // semua user
        Notification::send($users, new InventoryNotification($title, $message, $type, $url, $meta));
    }
}
