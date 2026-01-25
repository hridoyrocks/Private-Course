<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Video as VideoIcon, RefreshCw, Play, FileVideo } from 'lucide-vue-next';
import { ref } from 'vue';

interface Course {
    id: number;
    title: string;
}

interface Video {
    id: number;
    title: string;
    video_path: string;
    duration: string | null;
    order: number;
    is_active: boolean;
    course: Course;
    preview_url: string | null;
}

interface Props {
    video: Video;
}

const props = defineProps<Props>();

const form = useForm({
    title: props.video.title,
    duration: props.video.duration || '',
    order: props.video.order,
    is_active: props.video.is_active,
    video: null as File | null,
});

const videoName = ref<string | null>(null);
const uploadProgress = ref(0);
const showPreview = ref(false);

const handleVideoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.video = file;
        videoName.value = file.name;
    }
};

const clearVideo = () => {
    form.video = null;
    videoName.value = null;
};

const getFileName = (path: string) => {
    return path.split('/').pop() || path;
};

const submit = () => {
    form.post(`/admin/videos/${props.video.id}`, {
        forceFormData: true,
        onProgress: (progress) => {
            if (progress.percentage) {
                uploadProgress.value = progress.percentage;
            }
        },
    });
};
</script>

<template>
    <Head title="Edit Video - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="`/admin/courses/${video.course.id}/edit`" class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Video</h1>
                    <p class="text-muted-foreground">Course: {{ video.course.title }}</p>
                </div>
            </div>

            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700 space-y-5">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium mb-2">Video Title</label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                required
                            />
                            <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <!-- Current Video -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Current Video</label>
                            <div class="rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 overflow-hidden">
                                <!-- Video Preview -->
                                <div v-if="video.preview_url && showPreview" class="aspect-video bg-black">
                                    <video
                                        :src="video.preview_url"
                                        controls
                                        class="w-full h-full"
                                    >
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                                <!-- Video Info -->
                                <div class="flex items-center justify-between p-4">
                                    <div class="flex items-center gap-3">
                                        <FileVideo class="h-8 w-8 text-red-600" />
                                        <div>
                                            <p class="text-sm font-medium">{{ getFileName(video.video_path) }}</p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ video.duration || 'Duration not set' }}
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        v-if="video.preview_url"
                                        type="button"
                                        @click="showPreview = !showPreview"
                                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                                    >
                                        <Play class="h-4 w-4" />
                                        {{ showPreview ? 'Hide' : 'Preview' }}
                                    </button>
                                    <span v-else class="text-xs text-muted-foreground">
                                        Preview not available
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Replace Video -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Replace Video (optional)</label>
                            <div
                                v-if="!videoName"
                                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-6 cursor-pointer hover:border-red-400"
                                @click="($refs.videoInput as HTMLInputElement).click()"
                            >
                                <RefreshCw class="h-8 w-8 text-muted-foreground mb-2" />
                                <p class="text-sm text-muted-foreground">Click to replace video file</p>
                                <p class="text-xs text-muted-foreground mt-1">MP4, AVI, MOV (max 5GB)</p>
                            </div>
                            <div
                                v-else
                                class="flex items-center justify-between rounded-lg border border-green-300 dark:border-green-600 bg-green-50 dark:bg-green-900/20 p-4"
                            >
                                <div class="flex items-center gap-3">
                                    <VideoIcon class="h-8 w-8 text-green-600" />
                                    <div>
                                        <p class="text-sm font-medium">{{ videoName }}</p>
                                        <p class="text-xs text-green-600">New video selected - will replace current</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="clearVideo"
                                    class="text-sm text-red-600 hover:underline"
                                >
                                    Remove
                                </button>
                            </div>
                            <input
                                ref="videoInput"
                                type="file"
                                accept="video/*"
                                class="hidden"
                                @change="handleVideoChange"
                            />
                            <p v-if="form.errors.video" class="mt-1.5 text-sm text-red-600">{{ form.errors.video }}</p>

                            <!-- Upload Progress -->
                            <div v-if="form.processing && uploadProgress > 0 && videoName" class="mt-4">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span>Uploading...</span>
                                    <span>{{ uploadProgress }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-600">
                                    <div
                                        class="h-2 rounded-full bg-red-600 transition-all"
                                        :style="{ width: `${uploadProgress}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div>
                            <label for="duration" class="block text-sm font-medium mb-2">Duration</label>
                            <input
                                id="duration"
                                v-model="form.duration"
                                type="text"
                                placeholder="e.g. 10:30"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            />
                        </div>

                        <!-- Order -->
                        <div>
                            <label for="order" class="block text-sm font-medium mb-2">Order</label>
                            <input
                                id="order"
                                v-model="form.order"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            />
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center gap-3">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                            />
                            <label for="is_active" class="text-sm font-medium">Active Video</label>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            {{ form.processing ? (videoName ? 'Uploading...' : 'Saving...') : 'Update Video' }}
                        </button>
                        <Link
                            :href="`/admin/courses/${video.course.id}/edit`"
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
