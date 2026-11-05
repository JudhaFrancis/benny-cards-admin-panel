<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Generates monthly sequential numbers in the format PREFIX + MONYY + 3-digit sequence,
 * e.g. OD-NOV26001 (orders) or EQ-NOV26001 (enquiries).
 *
 * The sequence restarts every month. Soft-deleted records are included, so a number is never reused.
 */
class SequenceNumberGenerator
{
    /**
     * @param class-string<Model> $modelClass Model whose table holds the numbers
     * @param string $column Column storing the number (e.g. order_number)
     * @param string $prefix Prefix including separator (e.g. "OD-")
     */
    public static function next(string $modelClass, string $column, string $prefix): string
    {
        $monthPrefix = $prefix . strtoupper(date('My')); // e.g. OD-NOV26

        $query = in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)
            ? $modelClass::withTrashed()
            : $modelClass::query();

        $latest = $query
            ->where($column, 'LIKE', $monthPrefix . '%')
            ->orderBy('id', 'desc')
            ->value($column);

        $sequence = $latest ? ((int) substr($latest, strlen($monthPrefix))) + 1 : 1;

        return $monthPrefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
