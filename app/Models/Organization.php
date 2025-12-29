<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    /** @use HasFactory<\Database\Factories\OrganizationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'activated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
        ];
    }

    /**
     * Get the users that belong to the organization.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the organization is activated.
     */
    public function isActivated(): bool
    {
        return $this->activated_at !== null;
    }
}
