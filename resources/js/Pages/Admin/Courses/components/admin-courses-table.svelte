<script lang="ts">
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import * as AlertDialog from '$lib/components/ui/alert-dialog';
    import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
    import * as Table from '$lib/components/ui/table';
    import {
        createSvelteTable,
        FlexRender,
    } from '$lib/components/ui/data-table';
    import { debounce } from '$lib/utils';
    import { router, page, useForm } from '@inertiajs/svelte';
    import { createColumnHelper, getCoreRowModel } from '@tanstack/table-core';
    import {
        ChevronDown,
        Pencil,
        Search,
        Trash2,
        X,
        BookOpen,
    } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';

    export type AdminCoursesTableCourse = {
        id: number;
        name: string;
        description: string | null;
        available_from: string | null;
        available_until: string | null;
        is_draft: boolean;
        deleted_at: string | null;
        created_at: string;
    };

    type Props = {
        courses: {
            data: AdminCoursesTableCourse[];
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
        filters?: {
            search?: string;
            status?: string;
            include_deleted?: string | boolean;
            per_page?: string;
        };
        onOpenEditCourseDialog: (course: AdminCoursesTableCourse) => void;
    };

    const { courses, filters = {}, onOpenEditCourseDialog }: Props = $props();

    function getFilterValue(key: keyof NonNullable<Props['filters']>): string {
        const value = filters?.[key];
        if (value === true) return '1';
        if (value === false) return '';
        return value || '';
    }

    let filterSearch = $state(getFilterValue('search'));
    let filterStatus = $state(getFilterValue('status'));
    let includeDeleted = $state(getFilterValue('include_deleted') || '');
    let perPage = $state(getFilterValue('per_page') || '25');

    let deleteConfirmOpen = $state(false);
    let selectedCourseToDelete = $state<AdminCoursesTableCourse | null>(null);

    const deleteForm = useForm({});

    function getCurrentUrlSearchParams(): URLSearchParams {
        const url = new URL($page.url, 'http://localhost');
        return new URLSearchParams(url.search);
    }

    function buildQueryParams(pageNumber?: number): string {
        const params = getCurrentUrlSearchParams();

        if (filterSearch) params.set('search', filterSearch);
        else params.delete('search');

        if (filterStatus) params.set('status', filterStatus);
        else params.delete('status');

        if (includeDeleted) params.set('include_deleted', includeDeleted);
        else params.delete('include_deleted');

        if (perPage && perPage !== '25') params.set('per_page', perPage);
        else params.delete('per_page');

        if (pageNumber && pageNumber > 1)
            params.set('page', pageNumber.toString());
        else params.delete('page');

        return params.toString() ? `?${params.toString()}` : '';
    }

    function applyFilters() {
        router.get(
            '/admin/courses' + buildQueryParams(),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    const debouncedApplyFilters = debounce(applyFilters, 120);

    function handleSearchChange(value: string) {
        filterSearch = value;
        debouncedApplyFilters();
    }

    function handleStatusChange(value: string) {
        filterStatus = value;
        applyFilters();
    }

    function handleIncludeDeletedChange(value: string) {
        includeDeleted = value;
        applyFilters();
    }

    function handlePerPageChange(value: string) {
        perPage = value;
        router.get(
            '/admin/courses' + buildQueryParams(1),
            {},
            {
                preserveState: true,
                preserveScroll: false,
                replace: true,
            },
        );
    }

    function clearFilters() {
        filterSearch = '';
        filterStatus = '';
        includeDeleted = '';
        perPage = '25';
        router.get(
            '/admin/courses',
            {},
            { preserveState: false, preserveScroll: false, replace: true },
        );
    }

    const hasActiveFilters = $derived(
        !!filterSearch || !!filterStatus || !!includeDeleted,
    );

    function formatDate(dateString: string | null): string {
        if (!dateString) return '—';
        return new Date(dateString).toLocaleDateString();
    }

    function getStatus(course: AdminCoursesTableCourse): 'Draft' | 'Published' {
        return course.is_draft ? 'Draft' : 'Published';
    }

    function getAvailability(course: AdminCoursesTableCourse): string {
        const from = course.available_from
            ? formatDate(course.available_from)
            : '—';
        const until = course.available_until
            ? formatDate(course.available_until)
            : '—';
        if (from === '—' && until === '—') return 'No dates';
        return `${from} → ${until}`;
    }

    function requestDelete(course: AdminCoursesTableCourse) {
        selectedCourseToDelete = course;
        deleteConfirmOpen = true;
    }

    function confirmDelete() {
        if (!selectedCourseToDelete) return;
        if (selectedCourseToDelete.deleted_at) {
            toast.error('Course is already deleted');
            deleteConfirmOpen = false;
            return;
        }

        $deleteForm.delete(`/admin/courses/${selectedCourseToDelete.id}`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Course deleted successfully');
                deleteConfirmOpen = false;
                selectedCourseToDelete = null;
            },
            onError: () => {
                toast.error('Failed to delete course');
            },
        });
    }

    const columnHelper = createColumnHelper<AdminCoursesTableCourse>();

    const columns = [
        columnHelper.accessor('name', { header: 'Name' }),
        columnHelper.display({ id: 'status', header: 'Status' }),
        columnHelper.display({ id: 'availability', header: 'Availability' }),
        columnHelper.display({ id: 'created_at', header: 'Created' }),
        columnHelper.display({ id: 'actions', header: 'Actions' }),
    ];

    const table = $derived.by(() => {
        return createSvelteTable({
            data: courses.data,
            columns,
            getCoreRowModel: getCoreRowModel(),
        });
    });
</script>

<div class="rounded-lg border bg-card p-4" data-testid="admin-courses-filters">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            {#if hasActiveFilters}
                <Button
                    variant="ghost"
                    size="sm"
                    onclick={clearFilters}
                    data-testid="admin-courses-clear-filters"
                >
                    <X class="mr-2 h-4 w-4" />
                    Clear Filters
                </Button>
            {/if}
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="flex flex-col gap-2">
                <Label for="filter-search">Search</Label>
                <div class="relative w-full">
                    <Search
                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="filter-search"
                        type="text"
                        value={filterSearch}
                        oninput={(e) =>
                            handleSearchChange(
                                (e.target as HTMLInputElement).value,
                            )}
                        placeholder="Search by course name..."
                        class="pl-9"
                        data-testid="admin-courses-filter-search"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <Label for="filter-status">Status</Label>
                <DropdownMenu.Root>
                    <DropdownMenu.Trigger>
                        <Button
                            variant="outline"
                            class="w-full justify-between"
                            data-testid="admin-courses-filter-status-trigger"
                        >
                            {filterStatus
                                ? filterStatus.charAt(0).toUpperCase() +
                                  filterStatus.slice(1)
                                : 'All Status'}
                            <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                        </Button>
                    </DropdownMenu.Trigger>
                    <DropdownMenu.Content align="start" class="w-[220px]">
                        <DropdownMenu.Item
                            onclick={() => handleStatusChange('')}
                            data-testid="admin-courses-filter-status-all"
                        >
                            All Status
                        </DropdownMenu.Item>
                        <DropdownMenu.Separator />
                        <DropdownMenu.Item
                            onclick={() => handleStatusChange('draft')}
                            data-testid="admin-courses-filter-status-draft"
                        >
                            Draft
                        </DropdownMenu.Item>
                        <DropdownMenu.Item
                            onclick={() => handleStatusChange('published')}
                            data-testid="admin-courses-filter-status-published"
                        >
                            Published
                        </DropdownMenu.Item>
                    </DropdownMenu.Content>
                </DropdownMenu.Root>
            </div>

            <div class="flex flex-col gap-2">
                <Label for="filter-deleted">Deleted</Label>
                <DropdownMenu.Root>
                    <DropdownMenu.Trigger>
                        <Button
                            variant="outline"
                            class="w-full justify-between"
                            data-testid="admin-courses-filter-deleted-trigger"
                        >
                            {includeDeleted ? 'Included' : 'Excluded'}
                            <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                        </Button>
                    </DropdownMenu.Trigger>
                    <DropdownMenu.Content align="start" class="w-[220px]">
                        <DropdownMenu.Item
                            onclick={() => handleIncludeDeletedChange('')}
                            data-testid="admin-courses-filter-deleted-excluded"
                        >
                            Excluded
                        </DropdownMenu.Item>
                        <DropdownMenu.Item
                            onclick={() => handleIncludeDeletedChange('1')}
                            data-testid="admin-courses-filter-deleted-included"
                        >
                            Included
                        </DropdownMenu.Item>
                    </DropdownMenu.Content>
                </DropdownMenu.Root>
            </div>
        </div>
    </div>
</div>

<div class="rounded-lg border bg-card" data-testid="admin-courses-table">
    <div class="overflow-x-auto">
        <Table.Root>
            <Table.Header>
                {#each table.getHeaderGroups() as headerGroup (headerGroup.id)}
                    <Table.Row>
                        {#each headerGroup.headers as header (header.id)}
                            <Table.Head
                                class={header.id === 'actions'
                                    ? 'text-right'
                                    : ''}
                                colspan={header.colSpan}
                            >
                                {#if !header.isPlaceholder}
                                    <FlexRender
                                        content={header.column.columnDef.header}
                                        context={header.getContext()}
                                    />
                                {/if}
                            </Table.Head>
                        {/each}
                    </Table.Row>
                {/each}
            </Table.Header>

            <Table.Body>
                {#if table.getRowModel().rows.length === 0}
                    <Table.Row>
                        <Table.Cell
                            colspan={columns.length}
                            class="h-24 text-center"
                        >
                            No courses found
                        </Table.Cell>
                    </Table.Row>
                {:else}
                    {#each table.getRowModel().rows as row (row.id)}
                        <Table.Row
                            class={row.original.deleted_at ? 'opacity-60' : ''}
                        >
                            {#each row.getVisibleCells() as cell (cell.id)}
                                <Table.Cell
                                    class={[
                                        cell.column.id === 'actions'
                                            ? 'text-right'
                                            : '',
                                        cell.column.id === 'created_at'
                                            ? 'text-muted-foreground text-sm'
                                            : '',
                                    ]
                                        .filter(Boolean)
                                        .join(' ')}
                                >
                                    {#if cell.column.id === 'name'}
                                        <div class="flex items-center gap-2">
                                            <BookOpen
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                            <span class="font-medium"
                                                >{row.original.name}</span
                                            >
                                        </div>
                                    {:else if cell.column.id === 'status'}
                                        {#if row.original.deleted_at}
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-200"
                                            >
                                                Deleted
                                            </span>
                                        {:else if getStatus(row.original) === 'Draft'}
                                            <span
                                                class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
                                            >
                                                Draft
                                            </span>
                                        {:else}
                                            <span
                                                class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                                            >
                                                Published
                                            </span>
                                        {/if}
                                    {:else if cell.column.id === 'availability'}
                                        <span class="text-sm"
                                            >{getAvailability(
                                                row.original,
                                            )}</span
                                        >
                                    {:else if cell.column.id === 'created_at'}
                                        <span
                                            class="text-sm text-muted-foreground"
                                        >
                                            {formatDate(
                                                row.original.created_at,
                                            )}
                                        </span>
                                    {:else if cell.column.id === 'actions'}
                                        <div
                                            class="flex items-center justify-end gap-2"
                                        >
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() =>
                                                    onOpenEditCourseDialog(
                                                        row.original,
                                                    )}
                                                disabled={!!row.original
                                                    .deleted_at}
                                                data-testid={`admin-course-edit-${row.original.id}`}
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Button>

                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() =>
                                                    requestDelete(row.original)}
                                                disabled={!!row.original
                                                    .deleted_at}
                                                data-testid={`admin-course-delete-${row.original.id}`}
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    {:else}
                                        <FlexRender
                                            content={cell.column.columnDef.cell}
                                            context={cell.getContext()}
                                        />
                                    {/if}
                                </Table.Cell>
                            {/each}
                        </Table.Row>
                    {/each}
                {/if}
            </Table.Body>
        </Table.Root>
    </div>

    <div
        class="flex flex-col items-center justify-between gap-4 border-t px-6 py-4 sm:flex-row"
        data-testid="admin-courses-pagination"
    >
        <div class="text-sm text-muted-foreground">
            Showing {courses.data.length} of {courses.total} courses
            {#if courses.total > 0}
                (Page {courses.current_page} of {courses.last_page})
            {/if}
        </div>

        <div class="flex items-center gap-4">
            <DropdownMenu.Root>
                <DropdownMenu.Trigger>
                    <Button
                        variant="outline"
                        size="sm"
                        class="justify-between"
                        data-testid="admin-courses-per-page"
                    >
                        {perPage} per page
                        <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                </DropdownMenu.Trigger>
                <DropdownMenu.Content align="end" class="w-[160px]">
                    {#each [10, 25, 50, 100] as perPageOption}
                        <DropdownMenu.Item
                            onclick={() =>
                                handlePerPageChange(perPageOption.toString())}
                            data-testid={`admin-courses-per-page-${perPageOption}`}
                        >
                            {perPageOption} per page
                        </DropdownMenu.Item>
                    {/each}
                </DropdownMenu.Content>
            </DropdownMenu.Root>

            {#if courses.last_page > 1}
                <div class="flex gap-2">
                    {#if courses.current_page > 1}
                        <Button
                            variant="outline"
                            size="sm"
                            onclick={() =>
                                router.get(
                                    '/admin/courses' +
                                        buildQueryParams(
                                            courses.current_page - 1,
                                        ),
                                    {},
                                    {
                                        preserveState: true,
                                        preserveScroll: true,
                                    },
                                )}
                            data-testid="admin-courses-prev-page"
                        >
                            Previous
                        </Button>
                    {/if}
                    {#if courses.current_page < courses.last_page}
                        <Button
                            variant="outline"
                            size="sm"
                            onclick={() =>
                                router.get(
                                    '/admin/courses' +
                                        buildQueryParams(
                                            courses.current_page + 1,
                                        ),
                                    {},
                                    {
                                        preserveState: true,
                                        preserveScroll: true,
                                    },
                                )}
                            data-testid="admin-courses-next-page"
                        >
                            Next
                        </Button>
                    {/if}
                </div>
            {/if}
        </div>
    </div>
</div>

<AlertDialog.Root bind:open={deleteConfirmOpen}>
    <AlertDialog.Content data-testid="admin-courses-delete-confirm-dialog">
        <AlertDialog.Header>
            <AlertDialog.Title>Delete course</AlertDialog.Title>
            <AlertDialog.Description>
                {#if selectedCourseToDelete}
                    Are you sure you want to delete "{selectedCourseToDelete.name}"?
                {/if}
            </AlertDialog.Description>
        </AlertDialog.Header>
        <AlertDialog.Footer>
            <AlertDialog.Cancel
                data-testid="admin-courses-delete-confirm-cancel"
            >
                Cancel
            </AlertDialog.Cancel>
            <AlertDialog.Action
                onclick={confirmDelete}
                disabled={$deleteForm.processing}
                data-testid="admin-courses-delete-confirm-action"
            >
                {$deleteForm.processing ? 'Deleting...' : 'Confirm'}
            </AlertDialog.Action>
        </AlertDialog.Footer>
    </AlertDialog.Content>
</AlertDialog.Root>
