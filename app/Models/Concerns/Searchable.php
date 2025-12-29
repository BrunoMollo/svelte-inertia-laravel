<?php

namespace App\Models\Concerns;

trait Searchable
{
    /**
     * Get the searchable columns for this model.
     * Must be implemented by the using class.
     *
     * @return array<string>
     */
    abstract public static function getSearchableColumns(): array;

    /**
     * Apply search filter to a query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeSearch($query, string $searchTerm): \Illuminate\Database\Eloquent\Builder
    {
        $searchableColumns = static::getSearchableColumns();

        return $query->where(function ($q) use ($searchTerm, $searchableColumns) {
            foreach ($searchableColumns as $index => $column) {
                if ($index === 0) {
                    $q->where($column, 'like', '%' . $searchTerm . '%');
                } else {
                    $q->orWhere($column, 'like', '%' . $searchTerm . '%');
                }
            }
        });
    }
}
