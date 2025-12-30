<?php

namespace Database\Factories;

use App\Models\Course;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * @var class-string<\App\Models\Course>
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'available_from' => null,
            'available_until' => null,
            'is_draft' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn() => [
            'is_draft' => true,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn() => [
            'is_draft' => false,
        ]);
    }

    public function withDateRange(?CarbonImmutable $from = null, ?CarbonImmutable $until = null): static
    {
        $fromDate = $from ?? CarbonImmutable::today();
        $untilDate = $until ?? $fromDate->addDays(30);

        return $this->state(fn() => [
            'available_from' => $fromDate->toDateString(),
            'available_until' => $untilDate->toDateString(),
        ]);
    }
}
