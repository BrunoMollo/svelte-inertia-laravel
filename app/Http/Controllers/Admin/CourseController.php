<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Course::query();

        if ($request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        // Filter by search (searches across searchable columns)
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Filter by status (draft/published)
        if ($request->filled('status')) {
            if ($request->input('status') === 'draft') {
                $query->where('is_draft', true);
            } elseif ($request->input('status') === 'published') {
                $query->where('is_draft', false);
            }
        }

        // Get per_page value, validate it's one of the allowed values
        $perPage = $request->input('per_page', 25);
        $allowedPerPage = [10, 25, 50, 100];
        if (! in_array((int) $perPage, $allowedPerPage, true)) {
            $perPage = 25;
        }

        $courses = $query->latest()->paginate((int) $perPage)->withQueryString();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['search', 'status', 'include_deleted', 'per_page']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = Course::create([
            'name' => $request->string('name'),
            'description' => $request->string('description')->toString() ?: null,
            'available_from' => $request->date('available_from'),
            'available_until' => $request->date('available_until'),
            'is_draft' => $request->boolean('is_draft'),
        ]);

        return redirect()->route('admin.courses.index')
            ->with('success', "Course \"{$course->name}\" created successfully.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $course->fill([
            'name' => $request->string('name'),
            'description' => $request->string('description')->toString() ?: null,
            'available_from' => $request->date('available_from'),
            'available_until' => $request->date('available_until'),
            'is_draft' => $request->boolean('is_draft'),
        ]);

        $course->save();

        return redirect($request->header('Referer') ?? route('admin.courses.index'))
            ->with('success', "Course \"{$course->name}\" updated successfully.");
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $course->delete();

        return redirect($request->header('Referer') ?? route('admin.courses.index'))
            ->with('success', "Course \"{$course->name}\" deleted successfully.");
    }
}
