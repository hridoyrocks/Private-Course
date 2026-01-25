<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutDashboard, Users, BookOpen, Key, X } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    open: boolean;
}

defineProps<Props>();
const emit = defineEmits(['close']);

const page = usePage();
const currentUrl = computed(() => page.url);

const navItems = [
    {
        title: 'Dashboard',
        href: '/admin',
        icon: LayoutDashboard,
    },
    {
        title: 'Users',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Courses',
        href: '/admin/courses',
        icon: BookOpen,
    },
    {
        title: 'Access Control',
        href: '/admin/access',
        icon: Key,
    },
];

const isActive = (href: string) => {
    if (href === '/admin') {
        return currentUrl.value === '/admin';
    }
    return currentUrl.value.startsWith(href);
};
</script>

<template>
    <!-- Mobile sidebar -->
    <div
        :class="[
            'fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-gray-800 transform transition-transform duration-300 ease-in-out lg:hidden',
            open ? 'translate-x-0' : '-translate-x-full'
        ]"
    >
        <div class="flex h-full flex-col">
            <!-- Header -->
            <div class="flex h-16 items-center justify-between px-6 border-b dark:border-gray-700">
                <Link href="/admin" class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-red-600 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">T</span>
                    </div>
                    <span class="font-bold text-lg">TDA Academy</span>
                </Link>
                <button @click="emit('close')" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                        isActive(item.href)
                            ? 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                    ]"
                    @click="emit('close')"
                >
                    <component :is="item.icon" class="h-5 w-5" />
                    {{ item.title }}
                </Link>
            </nav>
        </div>
    </div>

    <!-- Desktop sidebar -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:flex lg:w-72 lg:flex-col">
        <div class="flex h-full flex-col bg-white dark:bg-gray-800 border-r dark:border-gray-700">
            <!-- Header -->
            <div class="flex h-16 items-center px-6 border-b dark:border-gray-700">
                <Link href="/admin" class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg bg-red-600 flex items-center justify-center">
                        <span class="text-white font-bold">T</span>
                    </div>
                    <div>
                        <span class="font-bold text-lg block leading-tight">TDA Academy</span>
                        <span class="text-xs text-muted-foreground">Admin Panel</span>
                    </div>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                        isActive(item.href)
                            ? 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                    ]"
                >
                    <component :is="item.icon" class="h-5 w-5" />
                    {{ item.title }}
                </Link>
            </nav>

            <!-- Footer -->
            <div class="border-t dark:border-gray-700 p-4">
                <p class="text-xs text-center text-muted-foreground">
                    © 2025 TDA Academy
                </p>
            </div>
        </div>
    </div>
</template>
