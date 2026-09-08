<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

trait ScopesByOwner
{
    protected function currentUserSeesAllOwners(): bool
    {
        return Auth::user()?->hasRole('super-admin') === true;
    }

    protected function scopeForCurrentUser(Builder|Relation $query, string $ownerColumn = 'owner_id'): Builder|Relation
    {
        $user = Auth::user();

        if (!$user || $this->currentUserSeesAllOwners()) {
            return $query;
        }

        return $query->where($ownerColumn, $user->id);
    }

    protected function authorizeOwnedRecord(?int $ownerId, ?int $alsoUserId = null): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ($this->currentUserSeesAllOwners()) {
            return;
        }

        $allowed = array_filter([(int) $ownerId, (int) $alsoUserId]);

        if (!in_array((int) $user->id, $allowed, true)) {
            abort(403);
        }
    }
}
