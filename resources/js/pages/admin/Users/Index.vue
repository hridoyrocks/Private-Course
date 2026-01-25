<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Monitor, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    max_devices: number;
    is_active: boolean;
    devices_count: number;
    courses_count: number;
    created_at: string;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const showCreateModal = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    max_devices: 1,
    is_active: true,
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const createUser = () => {
    form.post('/admin/users', {
        onSuccess: () => {
            closeCreateModal();
        },
    });
};

const deleteUser = (user: User) => {
    if (confirm(`Are you sure you want to delete "${user.name}"?`)) {
        router.delete(`/admin/users/${user.id}`);
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="User Management - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
                    <p class="text-muted-foreground">Manage platform users and their access</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    <Plus class="h-4 w-4" />
                    Add User
                </button>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b dark:border-gray-700 text-left text-sm text-muted-foreground">
                                <th class="px-6 py-4 font-medium">Name</th>
                                <th class="px-6 py-4 font-medium">Email</th>
                                <th class="px-6 py-4 font-medium">Devices</th>
                                <th class="px-6 py-4 font-medium">Courses</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium">Created</th>
                                <th class="px-6 py-4 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-b last:border-0 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                                <td class="px-6 py-4 text-muted-foreground">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-sm">
                                        <Monitor class="h-4 w-4 text-muted-foreground" />
                                        {{ user.devices_count }}/{{ user.max_devices }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ user.courses_count }}</td>
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
                                <td class="px-6 py-4 text-muted-foreground">{{ formatDate(user.created_at) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <Link
                                            :href="`/admin/users/${user.id}/edit`"
                                            class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="deleteUser(user)"
                                            class="rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-muted-foreground">
                                    No users found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="flex items-center justify-center gap-1 border-t dark:border-gray-700 p-4">
                    <Link
                        v-for="link in users.links"
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

        <!-- Create User Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white dark:bg-gray-800 shadow-xl">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold">Create New User</h3>
                    <button @click="closeCreateModal" class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Body -->
                <form @submit.prevent="createUser" class="p-6 space-y-5">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium mb-2">Full Name</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            placeholder="Enter full name"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium mb-2">Email Address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            placeholder="user@example.com"
                            required
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium mb-2">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            placeholder="Enter password"
                            required
                        />
                        <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <!-- Max Devices -->
                    <div>
                        <label for="max_devices" class="block text-sm font-medium mb-2">Max Devices</label>
                        <input
                            id="max_devices"
                            v-model="form.max_devices"
                            type="number"
                            min="1"
                            max="10"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            required
                        />
                        <p class="mt-1 text-xs text-muted-foreground">Maximum number of devices user can login from</p>
                        <p v-if="form.errors.max_devices" class="mt-1.5 text-sm text-red-600">{{ form.errors.max_devices }}</p>
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center gap-3">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                        />
                        <label for="is_active" class="text-sm font-medium">Active Account</label>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create User' }}
                        </button>
                        <button
                            type="button"
                            @click="closeCreateModal"
                            class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
