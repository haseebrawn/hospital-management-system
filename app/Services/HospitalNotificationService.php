<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\HospitalSystemNotification;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Throwable;

class HospitalNotificationService
{
    public function notifyRoles(array $roles, array $payload, ?User $except = null): void
    {
        $existingRoles = Role::query()
            ->where('guard_name', 'api')
            ->whereIn('name', $roles)
            ->pluck('name')
            ->all();

        if ($existingRoles === []) {
            return;
        }

        $users = User::query()
            ->role($existingRoles, 'api')
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->get();

        $this->notifyUsers($users, $payload);
    }

    public function notifyUsers(iterable $users, array $payload): void
    {
        collect($users)
            ->filter()
            ->unique('id')
            ->each(function (User $user) use ($payload) {
                try {
                    $user->notify(new HospitalSystemNotification($this->payload($payload)));
                } catch (Throwable $exception) {
                    Log::warning('Hospital notification delivery failed.', [
                        'user_id' => $user->id,
                        'module' => $payload['module'] ?? 'system',
                        'message' => $exception->getMessage(),
                    ]);
                }
            });
    }

    private function payload(array $payload): array
    {
        return [
            'title' => (string) ($payload['title'] ?? 'Hospital notification'),
            'message' => (string) ($payload['message'] ?? ''),
            'module' => (string) ($payload['module'] ?? 'system'),
            'type' => (string) ($payload['type'] ?? 'info'),
            'url' => $payload['url'] ?? null,
            'icon' => (string) ($payload['icon'] ?? 'fa-solid fa-bell'),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
