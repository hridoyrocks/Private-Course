<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Upload, ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    title: '',
    description: '',
    thumbnail: null as File | null,
    is_active: true,
});

const thumbnailPreview = ref<string | null>(null);

const handleThumbnailChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.thumbnail = file;
        thumbnailPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post('/admin/courses', {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Create Course - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/admin/courses" class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Course</h1>
                    <p class="text-muted-foreground">Add a new course to your platform</p>
                </div>
            </div>

            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700 space-y-5">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium mb-2">Course Title</label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                placeholder="Enter course title"
                                required
                            />
                            <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium mb-2">Description (optional)</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                placeholder="Describe your course..."
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1.5 text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <!-- Thumbnail -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Thumbnail (optional)</label>
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-32 w-48 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 cursor-pointer overflow-hidden hover:border-red-400"
                                    @click="($refs.thumbnailInput as HTMLInputElement).click()"
                                >
                                    <img
                                        v-if="thumbnailPreview"
                                        :src="thumbnailPreview"
                                        alt="Preview"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="text-center text-muted-foreground">
                                        <Upload class="mx-auto h-8 w-8 mb-2" />
                                        <p class="text-xs">Click to upload</p>
                                    </div>
                                </div>
                                <input
                                    ref="thumbnailInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleThumbnailChange"
                                />
                            </div>
                            <p v-if="form.errors.thumbnail" class="mt-1.5 text-sm text-red-600">{{ form.errors.thumbnail }}</p>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center gap-3">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                            />
                            <label for="is_active" class="text-sm font-medium">Active Course</label>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Course' }}
                        </button>
                        <Link
                            href="/admin/courses"
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
