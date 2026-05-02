<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function send(User $user, string $type, string $message, string $url): void
    {
        DB::table('notifications')->insert([
            'user_id' => $user->id,
            'type' => $type,
            'data' => json_encode([
                'message' => $message,
                'url' => $url,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
