<script lang="ts">
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import AdminCoursesCreateCourseDialog from './components/admin-courses-create-course-dialog.svelte';
    import AdminCoursesEditCourseDialog from './components/admin-courses-edit-course-dialog.svelte';
    import AdminCoursesTable from './components/admin-courses-table.svelte';
    import { page } from '@inertiajs/svelte';
    import { onDestroy, onMount } from 'svelte';

    export type AdminCourse = {
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
            data: AdminCourse[];
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
    };

    const { courses, filters = {} }: Props = $props();

    let editCourseDialogOpen = $state(false);
    let selectedCourseForEdit = $state<AdminCourse | null>(null);
    let selectedCourseIdForEdit = $state<number | null>(null);

    function getCurrentUrlSearchParams(): URLSearchParams {
        const url = new URL($page.url, 'http://localhost');
        return new URLSearchParams(url.search);
    }

    function replaceBrowserUrlSearchParams(params: URLSearchParams) {
        if (typeof window === 'undefined') return;

        const query = params.toString();
        const nextUrl =
            window.location.pathname +
            (query ? `?${query}` : '') +
            window.location.hash;
        window.history.replaceState({}, '', nextUrl);
    }

    function setEditCourseQueryParam(courseId: number | null) {
        const params =
            typeof window === 'undefined'
                ? getCurrentUrlSearchParams()
                : new URLSearchParams(window.location.search);

        if (courseId === null) {
            params.delete('edit_course');
        } else {
            params.set('edit_course', String(courseId));
        }

        replaceBrowserUrlSearchParams(params);
    }

    function openEditCourseDialog(course: AdminCourse) {
        selectedCourseForEdit = course;
        selectedCourseIdForEdit = course.id;
        editCourseDialogOpen = true;
        setEditCourseQueryParam(course.id);
    }

    function closeEditCourseDialog() {
        editCourseDialogOpen = false;
        selectedCourseForEdit = null;
        selectedCourseIdForEdit = null;
        setEditCourseQueryParam(null);
    }

    function syncEditModalFromUrl() {
        const params = getCurrentUrlSearchParams();
        const courseIdParam = params.get('edit_course');

        if (!courseIdParam) {
            if (editCourseDialogOpen) closeEditCourseDialog();
            return;
        }

        const courseId = Number(courseIdParam);
        if (!Number.isFinite(courseId) || courseId <= 0) return;

        const course = courses.data.find((c) => c.id === courseId) ?? null;
        selectedCourseIdForEdit = courseId;
        selectedCourseForEdit = course;
        editCourseDialogOpen = true;
    }

    onMount(() => {
        syncEditModalFromUrl();
        window.addEventListener('popstate', syncEditModalFromUrl);
    });

    onDestroy(() => {
        window.removeEventListener('popstate', syncEditModalFromUrl);
    });

    let lastKnownUrl = $state('');
    $effect(() => {
        if ($page.url !== lastKnownUrl) {
            lastKnownUrl = $page.url;
            syncEditModalFromUrl();
        }
    });
</script>

<svelte:head>
    <title>Courses</title>
</svelte:head>

<AuthenticatedLayout>
    <div class="flex flex-col gap-4" data-testid="admin-courses-page">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Courses</h1>
                <p class="text-muted-foreground">
                    Manage courses and their availability
                </p>
            </div>
            <AdminCoursesCreateCourseDialog />
        </div>

        <AdminCoursesEditCourseDialog
            bind:open={editCourseDialogOpen}
            selectedCourse={selectedCourseForEdit}
            selectedCourseId={selectedCourseIdForEdit}
            onClose={closeEditCourseDialog}
        />

        <AdminCoursesTable
            {courses}
            {filters}
            onOpenEditCourseDialog={openEditCourseDialog}
        />
    </div>
</AuthenticatedLayout>
