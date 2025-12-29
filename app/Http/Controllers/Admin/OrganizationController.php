<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrganizationRequest;
use App\Http\Requests\Admin\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Organization::class);

        $organizations = Organization::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Organizations/Index', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Organization::class);

        return Inertia::render('Admin/Organizations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrganizationRequest $request): RedirectResponse
    {
        $organization = Organization::create($request->validated());

        return Redirect::route('admin.organizations.show', $organization)
            ->with('success', 'Organization created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Organization $organization): Response
    {
        $this->authorize('view', $organization);

        $organization->load('users');

        return Inertia::render('Admin/Organizations/Show', [
            'organization' => $organization,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Organization $organization): Response
    {
        $this->authorize('update', $organization);

        return Inertia::render('Admin/Organizations/Edit', [
            'organization' => $organization,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $organization->update($request->validated());

        return Redirect::route('admin.organizations.show', $organization)
            ->with('success', 'Organization updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return Redirect::route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }
}
