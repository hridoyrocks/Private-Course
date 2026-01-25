<script setup lang="ts">
import UserDashboardLayout from '@/layouts/user/UserDashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, PlayCircle, Clock, AlertTriangle, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';

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
}

const props = defineProps<Props>();

const searchQuery = ref('');
const filterStatus = ref<'all' | 'active' | 'expired'>('all');

const filteredCourses = computed(() => {
    let result = props.courses;

    if (filterStatus.value === 'active') {
        result = result.filter(c => !c.is_expired);
    } else if (filterStatus.value === 'expired') {
        result = result.filter(c => c.is_expired);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(c =>
            c.title.toLowerCase().includes(query) ||
            c.description?.toLowerCase().includes(query)
        );
    }

    return result;
});

const activeCourses = computed(() => props.courses.filter(c => !c.is_expired));
const expiredCourses = computed(() => props.courses.filter(c => c.is_expired));

const formatDaysRemaining = (days: number | null) => {
    if (days === null) return 'Lifetime';
    if (days < 0) return 'Expired';
    if (days === 0) return 'Expires today';
    return `${days} days left`;
};
</script>

<template>
    <Head title="My Courses - TDA Academy" />

    <UserDashboardLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Courses</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ activeCourses.length }} active, {{ expiredCourses.length }} expired</p>
                </div>

                <!-- Search -->
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search courses..."
                        class="w-full md:w-64 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 pl-10 pr-4 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                    />
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center gap-2 mb-6">
                <button
                    @click="filterStatus = 'all'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        filterStatus === 'all'
                            ? 'bg-red-600 text-white'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                    ]"
                >
                    All ({{ courses.length }})
                </button>
                <button
                    @click="filterStatus = 'active'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        filterStatus === 'active'
                            ? 'bg-red-600 text-white'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                    ]"
                >
                    Active ({{ activeCourses.length }})
                </button>
                <button
                    @click="filterStatus = 'expired'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        filterStatus === 'expired'
                            ? 'bg-red-600 text-white'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                    ]"
                >
                    Expired ({{ expiredCourses.length }})
                </button>
            </div>

            <!-- Course Grid -->
            <div v-if="filteredCourses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <component
                    :is="course.is_expired ? 'div' : Link"
                    v-for="course in filteredCourses"
                    :key="course.id"
                    :href="course.is_expired ? undefined : `/my/courses/${course.id}`"
                    :class="[
                        'group bg-white dark:bg-gray-800 rounded-xl border overflow-hidden',
                        course.is_expired
                            ? 'border-gray-200 dark:border-gray-700 opacity-60'
                            : 'border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow cursor-pointer'
                    ]"
                >
                    <!-- Thumbnail -->
                    <div class="aspect-video bg-gray-100 dark:bg-gray-700 relative">
                        <img
                            v-if="course.thumbnail"
                            :src="`/storage/${course.thumbnail}`"
                            :alt="course.title"
                            :class="['h-full w-full object-cover', course.is_expired ? 'grayscale' : '']"
                        />
                        <div v-else class="flex h-full items-center justify-center">
                            <BookOpen class="h-10 w-10 text-gray-400" />
                        </div>

                        <!-- Expired Overlay -->
                        <div v-if="course.is_expired" class="absolute inset-0 flex items-center justify-center bg-black/50">
                            <div class="text-center text-white">
                                <AlertTriangle class="h-8 w-8 mx-auto mb-1" />
                                <span class="text-sm font-medium">Expired</span>
                            </div>
                        </div>

                        <!-- Video count -->
                        <div v-else class="absolute bottom-3 right-3 flex items-center gap-1.5 bg-black/70 text-white text-xs font-medium px-2 py-1 rounded">
                            <PlayCircle class="h-3 w-3" />
                            {{ course.videos_count }}
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <h3 :class="[
                            'font-medium line-clamp-1 transition-colors',
                            course.is_expired
                                ? 'text-gray-500 dark:text-gray-400'
                                : 'text-gray-900 dark:text-white group-hover:text-red-600'
                        ]">
                            {{ course.title }}
                        </h3>
                        <p v-if="course.description" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">
                            {{ course.description }}
                        </p>
                        <div class="flex items-center gap-1.5 mt-3 text-xs">
                            <Clock class="h-3.5 w-3.5 text-gray-400" />
                            <span :class="[
                                course.is_expired ? 'text-red-600' :
                                course.days_remaining !== null && course.days_remaining <= 7 ? 'text-orange-600' : 'text-gray-500 dark:text-gray-400'
                            ]">
                                {{ formatDaysRemaining(course.days_remaining) }}
                            </span>
                        </div>
                    </div>
                </component>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                <BookOpen class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <h3 class="font-medium text-gray-900 dark:text-white mb-1">
                    {{ searchQuery ? 'No courses found' : 'No courses yet' }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ searchQuery ? 'Try a different search term.' : 'Contact administrator to get access to courses.' }}
                </p>
            </div>
        </div>
    </UserDashboardLayout>
</template>
