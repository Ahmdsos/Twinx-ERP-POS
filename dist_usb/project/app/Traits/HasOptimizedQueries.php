<?php

namespace App\Traits;

/**
 * HasOptimizedQueries Trait
 * Common optimized scopes for ERP models
 */
trait HasOptimizedQueries
{
    /**
     * Scope for active records only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive records only
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope for records created today
     */
    public function scopeToday($query, string $column = 'created_at')
    {
        return $query->whereDate($column, today());
    }

    /**
     * Scope for records this month
     */
    public function scopeThisMonth($query, string $column = 'created_at')
    {
        return $query->whereYear($column, now()->year)
            ->whereMonth($column, now()->month);
    }

    /**
     * Scope for records this year
     */
    public function scopeThisYear($query, string $column = 'created_at')
    {
        return $query->whereYear($column, now()->year);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $from, $to, string $column = 'created_at')
    {
        if ($from) {
            $query->whereDate($column, '>=', $from);
        }
        if ($to) {
            $query->whereDate($column, '<=', $to);
        }
        return $query;
    }

    /**
     * Scope for search in name field
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%");

            if (in_array('code', $this->fillable ?? [])) {
                $q->orWhere('code', 'like', "%{$term}%");
            }
        });
    }

    /**
     * Select only essential columns for listings
     */
    public function scopeSelectList($query)
    {
        $columns = ['id', 'name'];

        if (in_array('code', $this->fillable ?? [])) {
            $columns[] = 'code';
        }
        if (in_array('is_active', $this->fillable ?? [])) {
            $columns[] = 'is_active';
        }

        return $query->select($columns);
    }

    /**
     * Scope for ordering by name
     */
    public function scopeOrderByName($query, string $direction = 'asc')
    {
        return $query->orderBy('name', $direction);
    }
}
