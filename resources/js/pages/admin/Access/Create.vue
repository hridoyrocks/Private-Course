<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Course {
    id: number;
    title: string;
}

interface Props {
    users: User[];
    courses: Course[];
}

const props = defineProps<Props>();

const form = useForm({
    user_id: '',
    course_id: '',
    expires_at: '',
});

const submit = () => {
    form.post('/admin/access');
};
</script>

<template>
    <Head title="Grant Access - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/admin/access" class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Grant Access</h1>
                    <p class="text-muted-foreground">Give a user access to a course</p>
                </div>
            </div>

            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700 space-y-5">
                        <!-- User -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium mb-2">User</label>
                            <select
                                id="user_id"
                                v-model="form.user_id"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                required
                            >
                                <option value="">Select a user</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.email }})
                                </option>
                            </select>
                            <p v-if="form.errors.user_id" class="mt-1.5 text-sm text-red-600">{{ form.errors.user_id }}</p>
                        </div>

                        <!-- Course -->
                        <div>
                            <label for="course_id" class="block text-sm font-medium mb-2">Course</label>
                            <select
                                id="course_id"
                                v-model="form.course_id"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                required
                            >
                                <option value="">Select a course</option>
                                <option v-for="course in courses" :key="course.id" :value="course.id">
                                    {{ course.title }}
                                </option>
                            </select>
                            <p v-if="form.errors.course_id" class="mt-1.5 text-sm text-red-600">{{ form.errors.course_id }}</p>
                        </div>

                        <!-- Expires At -->
                        <div>
                            <label for="expires_at" class="block text-sm font-medium mb-2">Expiry Date (optional)</label>
                            <input
                                id="expires_at"
                                v-model="form.expires_at"
                                type="date"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            />
                            <p class="mt-1 text-xs text-muted-foreground">Leave blank for lifetime access</p>
                            <p v-if="form.errors.expires_at" class="mt-1.5 text-sm text-red-600">{{ form.errors.expires_at }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Granting...' : 'Grant Access' }}
                        </button>
                        <Link
                            href="/admin/access"
                            class="rounded-lg border border-gray-300 dark:border-gray-600 px-6 py-2.5 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
