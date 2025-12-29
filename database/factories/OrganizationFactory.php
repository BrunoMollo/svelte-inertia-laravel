<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact_name' => $this->faker->name(),
            'contact_email' => $this->faker->unique()->safeEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'activated_at' => now(),
        ];
    }

    /**
     * Indicate that the organization is blocked (not activated).
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'activated_at' => null,
        ]);
    }

    /**
     * Indicate that the organization is activated.
     */
    public function activated(): static
    {
        return $this->state(fn (array $attributes) => [
            'activated_at' => now(),
        ]);
    }
}
