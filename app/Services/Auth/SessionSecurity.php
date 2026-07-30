<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SessionSecurity
{
    /**
     * Revoke every authenticated session and remember-me cookie
     * belonging to the user.
     */
    public function revokeAll(User $user): void
    {
        $this->rotateRememberToken($user);

        if (! $this->usesDatabaseSessions()) {
            return;
        }

        DB::table($this->sessionTable())
            ->where('user_id', $user->getKey())
            ->delete();
    }

    /**
     * Delete all database sessions except the currently active one.
     */
    public function revokeOtherDatabaseSessions(
        User $user,
        string $currentSessionId,
    ): void {
        if (
            ! $this->usesDatabaseSessions()
            || $currentSessionId === ''
        ) {
            return;
        }

        DB::table($this->sessionTable())
            ->where('user_id', $user->getKey())
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }

    private function rotateRememberToken(User $user): void
    {
        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();
    }

    private function usesDatabaseSessions(): bool
    {
        return config('session.driver') === 'database';
    }

    private function sessionTable(): string
    {
        return (string) config(
            'session.table',
            'sessions',
        );
    }
}
