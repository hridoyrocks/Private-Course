<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Video as VideoIcon, RefreshCw, Play, FileVideo, Upload, CheckCircle, AlertCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';

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

const form = ref({
    title: props.video.title,
    duration: props.video.duration || '',
    order: props.video.order,
    is_active: props.video.is_active,
});

const videoFile = ref<File | null>(null);
const videoName = ref<string | null>(null);
const uploadProgress = ref(0);
const uploadStatus = ref<'idle' | 'uploading' | 'success' | 'error'>('idle');
const errorMessage = ref('');
const uploadedPath = ref<string | null>(null);
const showPreview = ref(false);
const isSubmitting = ref(false);

const handleVideoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        videoFile.value = file;
        videoName.value = file.name;
        uploadStatus.value = 'idle';
        errorMessage.value = '';
        uploadedPath.value = null;
    }
};

const clearVideo = () => {
    videoFile.value = null;
    videoName.value = null;
    uploadStatus.value = 'idle';
    uploadedPath.value = null;
};

const getFileName = (path: string) => {
    return path.split('/').pop() || path;
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submit = async () => {
    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        // If new video selected, upload it first
        if (videoFile.value && !uploadedPath.value) {
            uploadStatus.value = 'uploading';
            uploadProgress.value = 0;

            // Step 1: Get presigned URL from server
            const { data } = await axios.post(`/admin/videos/${props.video.id}/upload-url`, {
                filename: videoFile.value.name,
                content_type: videoFile.value.type,
            });

            const { upload_url, path } = data;

            // Step 2: Upload directly to R2
            await axios.put(upload_url, videoFile.value, {
                headers: {
                    'Content-Type': videoFile.value.type,
                },
                onUploadProgress: (progressEvent) => {
                    if (progressEvent.total) {
                        uploadProgress.value = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                    }
                },
            });

            uploadedPath.value = path;
            uploadStatus.value = 'success';
        }

        // Step 3: Update video record
        router.put(`/admin/videos/${props.video.id}`, {
            title: form.value.title,
            video_path: uploadedPath.value || '',
            duration: form.value.duration,
            order: form.value.order,
            is_active: form.value.is_active,
        });

    } catch (error: any) {
        uploadStatus.value = 'error';
        errorMessage.value = error.response?.data?.message || 'Update failed. Please try again.';
        console.error('Update error:', error);
        isSubmitting.value = false;
    }
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
                                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-6 cursor-pointer hover:border-red-400 transition-colors"
                                @click="($refs.videoInput as HTMLInputElement).click()"
                            >
                                <RefreshCw class="h-8 w-8 text-muted-foreground mb-2" />
                                <p class="text-sm text-muted-foreground">Click to replace video file</p>
                                <p class="text-xs text-muted-foreground mt-1">MP4, AVI, MOV (max 5GB)</p>
                            </div>
                            <div
                                v-else
                                class="rounded-lg border transition-colors"
                                :class="{
                                    'border-green-500 bg-green-50 dark:bg-green-900/20': uploadStatus === 'success',
                                    'border-red-500 bg-red-50 dark:bg-red-900/20': uploadStatus === 'error',
                                    'border-blue-300 bg-blue-50 dark:bg-blue-900/20': uploadStatus === 'uploading',
                                    'border-green-300 dark:border-green-600 bg-green-50 dark:bg-green-900/20': uploadStatus === 'idle',
                                }"
                            >
                                <div class="flex items-center justify-between p-4">
                                    <div class="flex items-center gap-3">
                                        <CheckCircle v-if="uploadStatus === 'success'" class="h-8 w-8 text-green-600" />
                                        <AlertCircle v-else-if="uploadStatus === 'error'" class="h-8 w-8 text-red-600" />
                                        <Upload v-else-if="uploadStatus === 'uploading'" class="h-8 w-8 text-blue-600 animate-pulse" />
                                        <VideoIcon v-else class="h-8 w-8 text-green-600" />
                                        <div>
                                            <p class="text-sm font-medium">{{ videoName }}</p>
                                            <p v-if="videoFile" class="text-xs text-muted-foreground">
                                                {{ formatFileSize(videoFile.size) }}
                                            </p>
                                            <p v-if="uploadStatus === 'idle'" class="text-xs text-green-600">New video selected - will replace current</p>
                                            <p v-if="uploadStatus === 'success'" class="text-xs text-green-600">Upload complete!</p>
                                        </div>
                                    </div>
                                    <button
                                        v-if="uploadStatus !== 'uploading'"
                                        type="button"
                                        @click="clearVideo"
                                        class="text-sm text-red-600 hover:underline"
                                    >
                                        Remove
                                    </button>
                                </div>

                                <!-- Upload Progress -->
                                <div v-if="uploadStatus === 'uploading'" class="px-4 pb-4">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span>Uploading directly to cloud...</span>
                                        <span>{{ uploadProgress }}%</span>
                                    </div>
                                    <div class="h-3 w-full rounded-full bg-gray-200 dark:bg-gray-600 overflow-hidden">
                                        <div
                                            class="h-3 rounded-full bg-blue-600 transition-all duration-300"
                                            :style="{ width: `${uploadProgress}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <input
                                ref="videoInput"
                                type="file"
                                accept="video/*"
                                class="hidden"
                                @change="handleVideoChange"
                                :disabled="uploadStatus === 'uploading'"
                            />

                            <!-- Error Message -->
                            <div v-if="errorMessage" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                    <AlertCircle class="h-4 w-4" />
                                    {{ errorMessage }}
                                </p>
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
                            :disabled="isSubmitting || uploadStatus === 'uploading'"
                            class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ uploadStatus === 'uploading' ? 'Uploading...' : isSubmitting ? 'Saving...' : 'Update Video' }}
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
