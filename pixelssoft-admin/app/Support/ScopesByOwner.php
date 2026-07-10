<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

trait ScopesByOwner
{
    protected function scopeForCurrentUser(Builder|Relation $query, string $ownerColumn = 'owner_id'): Builder|Relation
    {
        $user = Auth::user();

        if (!$user) {
            return $query;
        }

        if ($user->hasRole('super-admin') || $user->can('crm.reports.view')) {
            return $query;
        }

        if ($user->hasAnyRole(['sales-manager', 'project-manager', 'finance', 'hr-admin'])) {
            return $query;
        }

        if ($user->hasRole('sales-rep')) {
            return $query->where($ownerColumn, $user->id);
        }

        return $query;
    }
}
