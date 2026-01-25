<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { Monitor, Trash2, BookOpen, X, ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

interface Device {
    id: number;
    device_name: string;
    ip_address: string;
    last_active_at: string;
}

interface Course {
    id: number;
    title: string;
    pivot: {
        expires_at: string | null;
    };
}

interface User {
    id: number;
    name: string;
    email: string;
    max_devices: number;
    is_active: boolean;
    devices: Device[];
    courses: Course[];
}

interface Props {
    user: User;
    allCourses: Array<{ id: number; title: string }>;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    max_devices: props.user.max_devices,
    is_active: props.user.is_active,
});

const accessForm = useForm({
    course_id: '',
    expires_at: '',
});

const showAccessModal = ref(false);

const submit = () => {
    form.put(`/admin/users/${props.user.id}`);
};

const removeDevice = (deviceId: number) => {
    if (confirm('Remove this device?')) {
        router.delete(`/admin/users/${props.user.id}/devices/${deviceId}`);
    }
};

const removeAllDevices = () => {
    if (confirm('Remove all devices?')) {
        router.delete(`/admin/users/${props.user.id}/devices`);
    }
};

const grantAccess = () => {
    accessForm.post(`/admin/users/${props.user.id}/access`, {
        onSuccess: () => {
            showAccessModal.value = false;
            accessForm.reset();
        }
    });
};

const revokeAccess = (courseId: number) => {
    if (confirm('Revoke access to this course?')) {
        router.delete(`/admin/users/${props.user.id}/access/${courseId}`);
    }
};

const formatDate = (date: string | null) => {
    if (!date) return 'Lifetime';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Edit User - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/admin/users" class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User</h1>
                    <p class="text-muted-foreground">{{ user.email }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- User Form -->
                <div class="space-y-6">
                    <div class="rounded-xl border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h2 class="text-lg font-semibold mb-4">User Information</h2>
                        <form @submit.prevent="submit" class="space-y-5">
                            <div>
                                <label for="name" class="block text-sm font-medium mb-2">Full Name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                    required
                                />
                                <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium mb-2">Email Address</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                    required
                                />
                                <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium mb-2">New Password (optional)</label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                    placeholder="Leave blank to keep current password"
                                />
                                <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
                            </div>

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
                            </div>

                            <div class="flex items-center gap-3">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                                />
                                <label for="is_active" class="text-sm font-medium">Active Account</label>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving...' : 'Update User' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Devices -->
                <div class="space-y-6">
                    <div class="rounded-xl border bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                            <h2 class="font-semibold">Devices ({{ user.devices.length }}/{{ user.max_devices }})</h2>
                            <button
                                v-if="user.devices.length > 0"
                                @click="removeAllDevices"
                                class="text-sm text-red-600 hover:underline"
                            >
                                Remove All
                            </button>
                        </div>
                        <div v-if="user.devices.length === 0" class="p-6 text-center text-muted-foreground">
                            No devices registered
                        </div>
                        <div v-else class="divide-y dark:divide-gray-700">
                            <div v-for="device in user.devices" :key="device.id" class="flex items-center justify-between p-4">
                                <div class="flex items-center gap-3">
                                    <Monitor class="h-5 w-5 text-muted-foreground" />
                                    <div>
                                        <p class="font-medium text-sm">{{ device.device_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ device.ip_address }} • {{ formatDateTime(device.last_active_at) }}</p>
                                    </div>
                                </div>
                                <button
                                    @click="removeDevice(device.id)"
                                    class="rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Access -->
            <div class="rounded-xl border bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                    <div>
                        <h2 class="font-semibold">Course Access</h2>
                        <p class="text-sm text-muted-foreground">Manage which courses this user can access</p>
                    </div>
                    <button
                        @click="showAccessModal = true"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        <BookOpen class="h-4 w-4" />
                        Grant Access
                    </button>
                </div>
                <div v-if="user.courses.length === 0" class="p-6 text-center text-muted-foreground">
                    No course access granted
                </div>
                <div v-else class="divide-y dark:divide-gray-700">
                    <div v-for="course in user.courses" :key="course.id" class="flex items-center justify-between p-4">
                        <div>
                            <p class="font-medium">{{ course.title }}</p>
                            <p class="text-sm text-muted-foreground">Expires: {{ formatDate(course.pivot.expires_at) }}</p>
                        </div>
                        <button
                            @click="revokeAccess(course.id)"
                            class="rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Modal -->
        <div v-if="showAccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Grant Course Access</h3>
                    <button @click="showAccessModal = false" class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <form @submit.prevent="grantAccess" class="space-y-4">
                    <div>
                        <label for="course_id" class="block text-sm font-medium mb-2">Course</label>
                        <select
                            id="course_id"
                            v-model="accessForm.course_id"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            required
                        >
                            <option value="">Select a course</option>
                            <option v-for="course in allCourses" :key="course.id" :value="course.id">
                                {{ course.title }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="expires_at" class="block text-sm font-medium mb-2">Expiry Date (optional)</label>
                        <input
                            id="expires_at"
                            v-model="accessForm.expires_at"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">Leave blank for lifetime access</p>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button
                            type="submit"
                            :disabled="accessForm.processing"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            {{ accessForm.processing ? 'Granting...' : 'Grant Access' }}
                        </button>
                        <button
                            type="button"
                            @click="showAccessModal = false"
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
