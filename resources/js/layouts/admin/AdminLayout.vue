<script setup lang="ts">
import { ref } from 'vue';
import AdminSidebar from './AdminSidebar.vue';
import { usePage } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Link, router } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;

const sidebarOpen = ref(false);

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Mobile sidebar backdrop -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <AdminSidebar :open="sidebarOpen" @close="sidebarOpen = false" />

        <!-- Main content area -->
        <div class="lg:pl-72">
            <!-- Top navbar -->
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b bg-white px-4 sm:px-6 dark:bg-gray-800 dark:border-gray-700">
                <!-- Mobile menu button -->
                <Button
                    variant="ghost"
                    size="icon"
                    class="lg:hidden"
                    @click="sidebarOpen = true"
                >
                    <Menu class="h-5 w-5" />
                </Button>

                <!-- Spacer -->
                <div class="flex-1" />

                <!-- User dropdown -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="flex items-center gap-2 px-2">
                            <Avatar class="h-8 w-8">
                                <AvatarFallback class="bg-red-600 text-white text-sm">
                                    {{ getInitials(user.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <span class="hidden sm:block text-sm font-medium">{{ user.name }}</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <div class="px-2 py-1.5">
                            <p class="text-sm font-medium">{{ user.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                        </div>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem as-child>
                            <Link href="/settings/profile" class="cursor-pointer">
                                Profile Settings
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="logout" class="cursor-pointer text-red-600">
                            Logout
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </header>

            <!-- Page content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
