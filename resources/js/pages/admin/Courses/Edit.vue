<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Upload, Plus, Pencil, Trash2, GripVertical, Video, ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

interface VideoItem {
    id: number;
    title: string;
    duration: string | null;
    order: number;
    is_active: boolean;
}

interface Course {
    id: number;
    title: string;
    description: string | null;
    thumbnail: string | null;
    is_active: boolean;
    videos: VideoItem[];
}

interface Props {
    course: Course;
}

const props = defineProps<Props>();

const form = useForm({
    title: props.course.title,
    description: props.course.description || '',
    thumbnail: null as File | null,
    is_active: props.course.is_active,
});

const thumbnailPreview = ref<string | null>(
    props.course.thumbnail ? `/storage/${props.course.thumbnail}` : null
);

const handleThumbnailChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.thumbnail = file;
        thumbnailPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(`/admin/courses/${props.course.id}`, {
        forceFormData: true,
        _method: 'PUT',
    });
};

const deleteVideo = (video: VideoItem) => {
    if (confirm(`Are you sure you want to delete "${video.title}"?`)) {
        router.delete(`/admin/videos/${video.id}`);
    }
};
</script>

<template>
    <Head title="Edit Course - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/admin/courses" class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Course</h1>
                    <p class="text-muted-foreground">{{ course.title }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Course Form -->
                <div class="space-y-6">
                    <div class="rounded-xl border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h2 class="text-lg font-semibold mb-4">Course Information</h2>
                        <form @submit.prevent="submit" class="space-y-5">
                            <div>
                                <label for="title" class="block text-sm font-medium mb-2">Course Title</label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                    required
                                />
                                <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-600">{{ form.errors.title }}</p>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium mb-2">Description</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Thumbnail</label>
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
                            </div>

                            <div class="flex items-center gap-3">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                                />
                                <label for="is_active" class="text-sm font-medium">Active Course</label>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving...' : 'Update Course' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Videos -->
                <div class="space-y-6">
                    <div class="rounded-xl border bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                            <h2 class="font-semibold">Videos ({{ course.videos.length }})</h2>
                            <Link
                                :href="`/admin/courses/${course.id}/videos/create`"
                                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                            >
                                <Plus class="h-4 w-4" />
                                Upload Video
                            </Link>
                        </div>
                        <div v-if="course.videos.length === 0" class="p-8 text-center text-muted-foreground">
                            <Video class="mx-auto h-12 w-12 mb-4" />
                            <p>No videos yet</p>
                        </div>
                        <div v-else class="divide-y dark:divide-gray-700">
                            <div
                                v-for="video in course.videos"
                                :key="video.id"
                                class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <GripVertical class="h-5 w-5 text-muted-foreground cursor-move" />
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ video.title }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ video.duration || 'No duration' }}
                                        <span
                                            :class="[
                                                'ml-2 inline-flex rounded-full px-2 py-0.5 text-xs',
                                                video.is_active
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                            ]"
                                        >
                                            {{ video.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Link
                                        :href="`/admin/videos/${video.id}/edit`"
                                        class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button
                                        @click="deleteVideo(video)"
                                        class="rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
