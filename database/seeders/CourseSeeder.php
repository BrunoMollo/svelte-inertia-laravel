<?php

namespace Database\Seeders;

use App\Models\Course;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Draft course (no dates)
        Course::factory()
            ->draft()
            ->firstOrCreate(
                ['name' => 'Draft - Example course'],
                [
                    'description' => 'Draft course example (not published).',
                    'available_from' => null,
                    'available_until' => null,
                ]
            );

        // Published course with date range
        Course::factory()
            ->published()
            ->withDateRange(
                CarbonImmutable::today(),
                CarbonImmutable::today()->addDays(30)
            )
            ->firstOrCreate(
                ['name' => 'Published - With dates'],
                [
                    'description' => 'Published course with an availability date range.',
                ]
            );

        // Published course without date range
        Course::factory()
            ->published()
            ->firstOrCreate(
                ['name' => 'Published - No dates'],
                [
                    'description' => 'Published course without availability limits.',
                    'available_from' => null,
                    'available_until' => null,
                ]
            );

        // Soft-deleted course
        $deleted = Course::factory()
            ->published()
            ->firstOrCreate(
                ['name' => 'Deleted - Example course'],
                [
                    'description' => 'Soft-deleted course example.',
                    'available_from' => null,
                    'available_until' => null,
                ]
            );

        if ($deleted->deleted_at === null) {
            $deleted->delete();
        }
    }
}
