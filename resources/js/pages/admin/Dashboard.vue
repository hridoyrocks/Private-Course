<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Users, BookOpen, Video, UserCheck, ArrowRight, TrendingUp } from 'lucide-vue-next';

interface Props {
    stats: {
        totalUsers: number;
        activeUsers: number;
        totalCourses: number;
        totalVideos: number;
    };
    recentUsers: Array<{
        id: number;
        name: string;
        email: string;
        created_at: string;
        is_active: boolean;
    }>;
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Admin Dashboard - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="text-muted-foreground">Welcome back! Here's an overview of your platform.</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Users</p>
                            <p class="text-3xl font-bold mt-1">{{ stats.totalUsers }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Active Users</p>
                            <p class="text-3xl font-bold mt-1">{{ stats.activeUsers }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <UserCheck class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Courses</p>
                            <p class="text-3xl font-bold mt-1">{{ stats.totalCourses }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <BookOpen class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Videos</p>
                            <p class="text-3xl font-bold mt-1">{{ stats.totalVideos }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <Video class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users Table -->
            <div class="rounded-xl border bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between p-6 border-b dark:border-gray-700">
                    <div>
                        <h2 class="text-lg font-semibold">Recent Users</h2>
                        <p class="text-sm text-muted-foreground">Latest registered users on your platform</p>
                    </div>
                    <Link
                        href="/admin/users"
                        class="inline-flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400"
                    >
                        View all
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b dark:border-gray-700 text-left text-sm text-muted-foreground">
                                <th class="px-6 py-3 font-medium">Name</th>
                                <th class="px-6 py-3 font-medium">Email</th>
                                <th class="px-6 py-3 font-medium">Date</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in recentUsers"
                                :key="user.id"
                                class="border-b last:border-0 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                                <td class="px-6 py-4 text-muted-foreground">{{ user.email }}</td>
                                <td class="px-6 py-4 text-muted-foreground">{{ formatDate(user.created_at) }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            user.is_active
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                        ]"
                                    >
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="recentUsers.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-muted-foreground">
                                    No users found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 sm:grid-cols-3">
                <Link
                    href="/admin/users/create"
                    class="flex items-center gap-4 rounded-xl border bg-white p-6 shadow-sm hover:border-red-200 hover:bg-red-50/50 transition-colors dark:bg-gray-800 dark:border-gray-700 dark:hover:border-red-800 dark:hover:bg-red-900/10"
                >
                    <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <Users class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="font-medium">Add New User</p>
                        <p class="text-sm text-muted-foreground">Create a new user account</p>
                    </div>
                </Link>

                <Link
                    href="/admin/courses/create"
                    class="flex items-center gap-4 rounded-xl border bg-white p-6 shadow-sm hover:border-red-200 hover:bg-red-50/50 transition-colors dark:bg-gray-800 dark:border-gray-700 dark:hover:border-red-800 dark:hover:bg-red-900/10"
                >
                    <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <BookOpen class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="font-medium">Create Course</p>
                        <p class="text-sm text-muted-foreground">Add a new course</p>
                    </div>
                </Link>

                <Link
                    href="/admin/access/create"
                    class="flex items-center gap-4 rounded-xl border bg-white p-6 shadow-sm hover:border-red-200 hover:bg-red-50/50 transition-colors dark:bg-gray-800 dark:border-gray-700 dark:hover:border-red-800 dark:hover:bg-red-900/10"
                >
                    <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <TrendingUp class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="font-medium">Grant Access</p>
                        <p class="text-sm text-muted-foreground">Give course access to user</p>
                    </div>
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
