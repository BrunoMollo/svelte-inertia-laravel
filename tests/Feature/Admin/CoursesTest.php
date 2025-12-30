<?php

use App\Models\Course;
use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'superadmin']);
    Role::firstOrCreate(['name' => 'teacher']);
    Role::firstOrCreate(['name' => 'student']);
});

test('superadmin can view courses index page', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    Course::factory()->count(2)->create();

    $response = $this
        ->actingAs($superadmin)
        ->get('/admin/courses');

    $response->assertOk();
    $response->assertInertia(fn(AssertableInertia $page) => $page->component('Admin/Courses/Index'));
});

test('non-superadmin cannot access courses index page', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $this
        ->actingAs($teacher)
        ->get('/admin/courses')
        ->assertForbidden();
});

test('courses index can be filtered by status', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $draft = Course::factory()->draft()->create(['name' => 'Draft Course']);
    $published = Course::factory()->published()->create(['name' => 'Published Course']);

    $response = $this
        ->actingAs($superadmin)
        ->get('/admin/courses?status=draft');

    $response->assertOk();
    $response->assertSee($draft->name, false);
    $response->assertDontSee($published->name, false);
});

test('courses index excludes deleted by default, but can include deleted', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $active = Course::factory()->create(['name' => 'Active Course']);
    $deleted = Course::factory()->create(['name' => 'Deleted Course']);
    $deleted->delete();

    $response = $this
        ->actingAs($superadmin)
        ->get('/admin/courses');

    $response->assertOk();
    $response->assertSee($active->name, false);
    $response->assertDontSee($deleted->name, false);

    $response = $this
        ->actingAs($superadmin)
        ->get('/admin/courses?include_deleted=1');

    $response->assertOk();
    $response->assertSee($active->name, false);
    $response->assertSee($deleted->name, false);
});

test('superadmin can create a course', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $response = $this
        ->actingAs($superadmin)
        ->post('/admin/courses', [
            'name' => 'New Course',
            'description' => 'Some description',
            'available_from' => null,
            'available_until' => null,
            'is_draft' => true,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin/courses');

    $this->assertDatabaseHas('courses', [
        'name' => 'New Course',
        'description' => 'Some description',
        'is_draft' => 1,
    ]);
});

test('superadmin can update a course', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $course = Course::factory()->draft()->create([
        'name' => 'Old Course Name',
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->from('/admin/courses')
        ->patch("/admin/courses/{$course->id}", [
            'name' => 'Updated Course Name',
            'description' => '',
            'available_from' => '2025-01-01',
            'available_until' => '2025-01-31',
            'is_draft' => false,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin/courses');

    $course->refresh();
    expect($course->name)->toBe('Updated Course Name');
    expect($course->is_draft)->toBeFalse();
    expect($course->available_from?->toDateString())->toBe('2025-01-01');
    expect($course->available_until?->toDateString())->toBe('2025-01-31');
});

test('superadmin can soft delete a course', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $course = Course::factory()->create();

    $response = $this
        ->actingAs($superadmin)
        ->from('/admin/courses')
        ->delete("/admin/courses/{$course->id}");

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin/courses');

    $this->assertSoftDeleted('courses', [
        'id' => $course->id,
    ]);
});
