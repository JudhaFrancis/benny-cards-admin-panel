<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\User;

/**
 * Branch access for the logged-in user.
 *
 *  - allowed  = users.branch_ids (set per user; the role is never consulted, so a role change
 *               does not change what a user sees)
 *  - selected = X-Branch-Ids request header from the top-header branch picker
 *               ("all", or a comma list). Ids outside "allowed" are dropped; empty = all allowed.
 *
 * Users who hold every branch also see records that have no branch, when nothing narrower is selected.
 */
class BranchScope
{
    public static function allowedIds(?User $user = null): array
    {
        $user ??= auth()->user();
        if (!$user) {
            return [];
        }

        return collect($user->branch_ids ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
    }

    /** True when the user is assigned every current branch. */
    public static function hasAllBranches(?User $user = null): bool
    {
        $allowed = self::allowedIds($user);
        if (!$allowed) {
            return false;
        }

        return Branch::query()->whereNotIn('id', $allowed)->doesntExist();
    }

    /** Branch ids picked in the header, limited to what the user is allowed. */
    public static function activeIds(?User $user = null): array
    {
        $allowed = self::allowedIds($user);
        $raw = trim((string) request()?->header('X-Branch-Ids', ''));

        if ($raw === '' || strtolower($raw) === 'all') {
            return $allowed;
        }

        $picked = collect(explode(',', $raw))->map(fn ($id) => (int) trim($id))->filter()->all();
        $active = array_values(array_intersect($allowed, $picked));

        // Picked only branches the user no longer has: fall back to all of theirs
        return $active ?: $allowed;
    }

    /** True when the current view covers every branch (so branch-less records are shown too). */
    public static function coversAll(?User $user = null): bool
    {
        return self::hasAllBranches($user) && count(self::activeIds($user)) === count(self::allowedIds($user));
    }

    /**
     * Limit a query to the header-selected branches.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  bool  $selectedOnly  false = every branch the user may access (for single-record lookups)
     */
    public static function apply($query, string $column = 'orders.branch_id', bool $selectedOnly = true, ?User $user = null)
    {
        $user ??= auth()->user();
        if (!$user) {
            return $query;
        }

        $ids = $selectedOnly ? self::activeIds($user) : self::allowedIds($user);
        $withNull = self::hasAllBranches($user) && (!$selectedOnly || self::coversAll($user));

        if (!$ids) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($column, $ids, $withNull) {
            $q->whereIn($column, $ids);
            if ($withNull) {
                $q->orWhereNull($column);
            }
        });
    }

    /** Whether the user may use this branch id (e.g. when creating an order / enquiry). */
    public static function canUse($branchId, ?User $user = null): bool
    {
        return $branchId !== null && in_array((int) $branchId, self::allowedIds($user), true);
    }
}
