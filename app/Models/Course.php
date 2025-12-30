<?php

namespace App\Models;

use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory, Searchable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'available_from',
        'available_until',
        'is_draft',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'available_from' => 'date',
            'available_until' => 'date',
            'is_draft' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function isDraft(): bool
    {
        return (bool) $this->is_draft;
    }

    public function isPublished(): bool
    {
        return ! $this->isDraft();
    }

    /**
     * @return array<string>
     */
    public static function getSearchableColumns(): array
    {
        return [
            'name',
            'description',
        ];
    }
}
