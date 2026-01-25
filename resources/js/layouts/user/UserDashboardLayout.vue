<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { LogOut, Menu, X, ChevronDown } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth?.user as { name: string; email: string } | undefined);

const showMobileMenu = ref(false);
const showUserMenu = ref(false);

const logout = () => {
    router.post('/logout');
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Top Navigation -->
        <nav class="sticky top-0 z-50 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <Link href="/my/courses" class="flex items-center">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">TDA Academy</span>
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center gap-6">
                        <Link
                            href="/my/courses"
                            class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
                        >
                            My Courses
                        </Link>
                    </div>

                    <!-- User Menu -->
                    <div class="hidden md:block relative">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-xs font-semibold text-white">
                                {{ user ? getInitials(user.name) : 'U' }}
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ user?.name }}</span>
                            <ChevronDown class="h-4 w-4 text-gray-500" />
                        </button>

                        <!-- Dropdown -->
                        <div
                            v-if="showUserMenu"
                            class="absolute right-0 mt-2 w-56 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg py-1 z-50"
                        >
                            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
                            </div>
                            <button
                                @click="logout"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-gray-50 dark:hover:bg-gray-700 w-full"
                            >
                                <LogOut class="h-4 w-4" />
                                Logout
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button
                        @click="showMobileMenu = !showMobileMenu"
                        class="md:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <Menu v-if="!showMobileMenu" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="showMobileMenu" class="md:hidden border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                <div class="px-4 py-3 space-y-1">
                    <Link
                        href="/my/courses"
                        class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        My Courses
                    </Link>
                    <button
                        @click="logout"
                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </nav>

        <!-- Click outside to close dropdown -->
        <div v-if="showUserMenu" @click="showUserMenu = false" class="fixed inset-0 z-40"></div>

        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
