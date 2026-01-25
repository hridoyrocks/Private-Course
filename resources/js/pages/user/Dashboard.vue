<script setup lang="ts">
import UserDashboardLayout from '@/layouts/user/UserDashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, PlayCircle, Clock, ArrowRight } from 'lucide-vue-next';

interface Course {
    id: number;
    title: string;
    description: string | null;
    thumbnail: string | null;
    videos_count: number;
    pivot: {
        expires_at: string | null;
    };
    is_expired: boolean;
    days_remaining: number | null;
}

interface Props {
    courses: Course[];
    stats: {
        totalCourses: number;
        expiredCourses: number;
        totalVideos: number;
    };
}

const props = defineProps<Props>();

const activeCourses = props.courses.filter(c => !c.is_expired);

const formatDaysRemaining = (days: number | null) => {
    if (days === null) return 'Lifetime';
    if (days < 0) return 'Expired';
    if (days === 0) return 'Expires today';
    return `${days} days left`;
};
</script>

<template>
    <Head title="Dashboard - TDA Academy" />

    <UserDashboardLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back!</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Continue learning from where you left off.</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20">
                            <BookOpen class="h-6 w-6 text-red-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalCourses }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Active Courses</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <PlayCircle class="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalVideos }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Videos</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                            <Clock class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.expiredCourses }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Expired</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Courses -->
            <div>
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">My Courses</h2>
                    <Link
                        href="/my/courses"
                        class="text-sm font-medium text-red-600 hover:text-red-700 flex items-center gap-1"
                    >
                        View All
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div v-if="activeCourses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <Link
                        v-for="course in activeCourses.slice(0, 6)"
                        :key="course.id"
                        :href="`/my/courses/${course.id}`"
                        class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <!-- Thumbnail -->
                        <div class="aspect-video bg-gray-100 dark:bg-gray-700 relative">
                            <img
                                v-if="course.thumbnail"
                                :src="`/storage/${course.thumbnail}`"
                                :alt="course.title"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full items-center justify-center">
                                <BookOpen class="h-10 w-10 text-gray-400" />
                            </div>

                            <!-- Video count -->
                            <div class="absolute bottom-3 right-3 flex items-center gap-1.5 bg-black/70 text-white text-xs font-medium px-2 py-1 rounded">
                                <PlayCircle class="h-3 w-3" />
                                {{ course.videos_count }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 dark:text-white group-hover:text-red-600 transition-colors line-clamp-1">
                                {{ course.title }}
                            </h3>
                            <p v-if="course.description" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">
                                {{ course.description }}
                            </p>
                            <div class="flex items-center gap-1.5 mt-3 text-xs text-gray-500 dark:text-gray-400">
                                <Clock class="h-3.5 w-3.5" />
                                <span :class="{ 'text-orange-600': course.days_remaining !== null && course.days_remaining <= 7 }">
                                    {{ formatDaysRemaining(course.days_remaining) }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <BookOpen class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <h3 class="font-medium text-gray-900 dark:text-white mb-1">No courses yet</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Contact administrator to get access to courses.</p>
                </div>
            </div>
        </div>
    </UserDashboardLayout>
</template>
