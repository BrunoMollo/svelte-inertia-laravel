<?php

namespace App\Mail\Concerns;

use App\Models\User;

/**
 * Trait to set the mailable locale based on the user's preferred locale.
 *
 * Usage: Call $this->setUserLocale() in your Mailable constructor.
 *
 * Example:
 * public function __construct(public User $user)
 * {
 *     $this->setUserLocale();
 * }
 */
trait UsesUserLocale
{
    /**
     * Set the locale for the mailable based on the user's preferred locale.
     */
    protected function setUserLocale(): void
    {
        $locale = $this->getUserLocale();
        $this->locale($locale);
    }

    /**
     * Get the locale for the mailable based on the user's preferred locale.
     */
    protected function getUserLocale(): string
    {
        if (property_exists($this, 'user') && $this->user instanceof User) {
            return $this->user->preferredLocale();
        }

        return app()->getLocale();
    }
}
