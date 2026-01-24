<script lang="ts">
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { _ } from '$lib/i18n';
    import { BookOpen, Calendar, ClipboardList, GraduationCap } from 'lucide-svelte';
</script>

<svelte:head>
    <title>{$_('Dashboard de Estudiante')}</title>
</svelte:head>

<AuthenticatedLayout>
    <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
        <div class="mb-4">
            <h1 class="text-3xl font-bold">{$_('Bienvenido, Estudiante')}</h1>
            <p class="text-muted-foreground">{$_('Aquí está tu panel de control')}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">{$_('Cursos Activos')}</p>
                        <p class="text-2xl font-bold">0</p>
                    </div>
                    <BookOpen class="h-8 w-8 text-muted-foreground" />
                </div>
            </div>

            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">{$_('Tareas Pendientes')}</p>
                        <p class="text-2xl font-bold">0</p>
                    </div>
                    <ClipboardList class="h-8 w-8 text-muted-foreground" />
                </div>
            </div>

            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">{$_('Próximas Clases')}</p>
                        <p class="text-2xl font-bold">0</p>
                    </div>
                    <Calendar class="h-8 w-8 text-muted-foreground" />
                </div>
            </div>

            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">{$_('Progreso General')}</p>
                        <p class="text-2xl font-bold">0%</p>
                    </div>
                    <GraduationCap class="h-8 w-8 text-muted-foreground" />
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold">{$_('Mis Cursos')}</h2>
                <div class="flex items-center justify-center py-8 text-muted-foreground">
                    <p>{$_('No tienes cursos inscritos todavía')}</p>
                </div>
            </div>

            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold">{$_('Actividad Reciente')}</h2>
                <div class="flex items-center justify-center py-8 text-muted-foreground">
                    <p>{$_('No hay actividad reciente')}</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">{$_('Calendario de Clases')}</h2>
            <div class="flex items-center justify-center py-12 text-muted-foreground">
                <p>{$_('No hay clases programadas')}</p>
            </div>
        </div>
    </div>
</AuthenticatedLayout>
