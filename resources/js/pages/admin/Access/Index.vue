<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';

interface Course {
    id: number;
    title: string;
    pivot: {
        expires_at: string | null;
        created_at: string;
    };
}

interface User {
    id: number;
    name: string;
    email: string;
    courses: Course[];
}

interface Props {
    accesses: {
        data: User[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const formatDate = (date: string | null) => {
    if (!date) return 'Lifetime';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const isExpired = (date: string | null) => {
    if (!date) return false;
    return new Date(date) < new Date();
};

const revokeAccess = (userId: number, courseId: number, courseName: string) => {
    if (confirm(`Revoke access to "${courseName}"?`)) {
        router.delete(`/admin/users/${userId}/access/${courseId}`);
    }
};
</script>

<template>
    <Head title="Access Control - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Access Control</h1>
                    <p class="text-muted-foreground">Manage user access to courses</p>
                </div>
                <Link
                    href="/admin/access/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    <Plus class="h-4 w-4" />
                    Grant Access
                </Link>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b dark:border-gray-700 text-left text-sm text-muted-foreground">
                                <th class="px-6 py-4 font-medium">User</th>
                                <th class="px-6 py-4 font-medium">Course</th>
                                <th class="px-6 py-4 font-medium">Expires</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="user in accesses.data" :key="user.id">
                                <tr
                                    v-for="(course, index) in user.courses"
                                    :key="`${user.id}-${course.id}`"
                                    class="border-b last:border-0 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-6 py-4">
                                        <template v-if="index === 0">
                                            <p class="font-medium">{{ user.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4">{{ course.title }}</td>
                                    <td class="px-6 py-4 text-muted-foreground">{{ formatDate(course.pivot.expires_at) }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="[
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                isExpired(course.pivot.expires_at)
                                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                                    : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            ]"
                                        >
                                            {{ isExpired(course.pivot.expires_at) ? 'Expired' : 'Active' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button
                                            @click="revokeAccess(user.id, course.id, course.title)"
                                            class="rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="accesses.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                                    No access records found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="accesses.last_page > 1" class="flex items-center justify-center gap-1 border-t dark:border-gray-700 p-4">
                    <Link
                        v-for="link in accesses.links"
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
        </div>
    </AdminLayout>
</template>
