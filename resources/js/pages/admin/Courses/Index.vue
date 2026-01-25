<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Video, Users } from 'lucide-vue-next';

interface Course {
    id: number;
    title: string;
    description: string | null;
    thumbnail: string | null;
    is_active: boolean;
    videos_count: number;
    users_count: number;
    created_at: string;
}

interface Props {
    courses: {
        data: Course[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const deleteCourse = (course: Course) => {
    if (confirm(`Are you sure you want to delete "${course.title}"? All videos will also be deleted.`)) {
        router.delete(`/admin/courses/${course.id}`);
    }
};
</script>

<template>
    <Head title="Courses - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Courses</h1>
                    <p class="text-muted-foreground">Manage your courses and their content</p>
                </div>
                <Link
                    href="/admin/courses/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    <Plus class="h-4 w-4" />
                    New Course
                </Link>
            </div>

            <!-- Courses Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="course in courses.data"
                    :key="course.id"
                    class="rounded-xl border bg-white shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700"
                >
                    <!-- Thumbnail -->
                    <div class="aspect-video bg-gray-100 dark:bg-gray-700">
                        <img
                            v-if="course.thumbnail"
                            :src="`/storage/${course.thumbnail}`"
                            :alt="course.title"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full items-center justify-center text-muted-foreground">
                            <Video class="h-12 w-12" />
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold">{{ course.title }}</h3>
                            <span
                                :class="[
                                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                    course.is_active
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                        : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                ]"
                            >
                                {{ course.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p v-if="course.description" class="text-sm text-muted-foreground line-clamp-2 mb-3">
                            {{ course.description }}
                        </p>

                        <!-- Stats -->
                        <div class="flex items-center gap-4 text-sm text-muted-foreground mb-4">
                            <span class="flex items-center gap-1">
                                <Video class="h-4 w-4" />
                                {{ course.videos_count }} videos
                            </span>
                            <span class="flex items-center gap-1">
                                <Users class="h-4 w-4" />
                                {{ course.users_count }} users
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <Link
                                :href="`/admin/courses/${course.id}/edit`"
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <Pencil class="h-4 w-4" />
                                Edit
                            </Link>
                            <button
                                @click="deleteCourse(course)"
                                class="rounded-lg border border-red-200 dark:border-red-800 p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="courses.data.length === 0" class="col-span-full rounded-xl border bg-white dark:bg-gray-800 dark:border-gray-700 p-12 text-center">
                    <Video class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                    <p class="text-muted-foreground mb-4">No courses yet</p>
                    <Link
                        href="/admin/courses/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        <Plus class="h-4 w-4" />
                        Create your first course
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="courses.last_page > 1" class="flex items-center justify-center gap-1">
                <Link
                    v-for="link in courses.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'rounded-lg px-3 py-2 text-sm',
                        link.active ? 'bg-red-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                        !link.url && 'pointer-events-none opacity-50'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </AdminLayout>
</template>
