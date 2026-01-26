<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Video, ArrowLeft, Upload, CheckCircle, AlertCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';

interface Course {
    id: number;
    title: string;
}

interface Props {
    course: Course;
}

const props = defineProps<Props>();

const form = ref({
    title: '',
    duration: '',
    order: 0,
});

const videoFile = ref<File | null>(null);
const videoName = ref<string | null>(null);
const uploadProgress = ref(0);
const uploadStatus = ref<'idle' | 'uploading' | 'success' | 'error'>('idle');
const errorMessage = ref('');
const uploadedPath = ref<string | null>(null);

const handleVideoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        videoFile.value = file;
        videoName.value = file.name;
        uploadStatus.value = 'idle';
        errorMessage.value = '';
    }
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submit = async () => {
    if (!videoFile.value) {
        errorMessage.value = 'Please select a video file';
        return;
    }

    if (!form.value.title) {
        errorMessage.value = 'Please enter a video title';
        return;
    }

    uploadStatus.value = 'uploading';
    uploadProgress.value = 0;
    errorMessage.value = '';

    try {
        // Step 1: Get presigned URL from server
        const { data } = await axios.post(`/admin/courses/${props.course.id}/videos/upload-url`, {
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

        // Step 3: Save video record to database
        router.post(`/admin/courses/${props.course.id}/videos`, {
            title: form.value.title,
            video_path: path,
            duration: form.value.duration,
            order: form.value.order,
        });

    } catch (error: any) {
        uploadStatus.value = 'error';
        errorMessage.value = error.response?.data?.message || 'Upload failed. Please try again.';
        console.error('Upload error:', error);
    }
};
</script>

<template>
    <Head title="Upload Video - TDA Academy" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="`/admin/courses/${course.id}/edit`" class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Upload Video</h1>
                    <p class="text-muted-foreground">Course: {{ course.title }}</p>
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
                                placeholder="Enter video title"
                                required
                            />
                        </div>

                        <!-- Video Upload -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Video File</label>
                            <div
                                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-8 cursor-pointer hover:border-red-400 transition-colors"
                                :class="{
                                    'border-green-500 bg-green-50 dark:bg-green-900/20': uploadStatus === 'success',
                                    'border-red-500 bg-red-50 dark:bg-red-900/20': uploadStatus === 'error',
                                }"
                                @click="($refs.videoInput as HTMLInputElement).click()"
                            >
                                <CheckCircle v-if="uploadStatus === 'success'" class="h-12 w-12 text-green-500 mb-4" />
                                <AlertCircle v-else-if="uploadStatus === 'error'" class="h-12 w-12 text-red-500 mb-4" />
                                <Upload v-else-if="uploadStatus === 'uploading'" class="h-12 w-12 text-red-500 mb-4 animate-pulse" />
                                <Video v-else class="h-12 w-12 text-muted-foreground mb-4" />

                                <p v-if="videoName" class="text-sm font-medium">{{ videoName }}</p>
                                <p v-else class="text-sm text-muted-foreground">Click to select a video</p>

                                <p v-if="videoFile" class="text-xs text-muted-foreground mt-1">
                                    {{ formatFileSize(videoFile.size) }}
                                </p>
                                <p v-else class="text-xs text-muted-foreground mt-2">MP4, AVI, MOV (max 5GB)</p>
                            </div>
                            <input
                                ref="videoInput"
                                type="file"
                                accept="video/*"
                                class="hidden"
                                @change="handleVideoChange"
                                :disabled="uploadStatus === 'uploading'"
                            />

                            <!-- Upload Progress -->
                            <div v-if="uploadStatus === 'uploading'" class="mt-4">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span>Uploading directly to cloud...</span>
                                    <span>{{ uploadProgress }}%</span>
                                </div>
                                <div class="h-3 w-full rounded-full bg-gray-200 dark:bg-gray-600 overflow-hidden">
                                    <div
                                        class="h-3 rounded-full bg-red-600 transition-all duration-300"
                                        :style="{ width: `${uploadProgress}%` }"
                                    ></div>
                                </div>
                                <p class="text-xs text-muted-foreground mt-2">
                                    Please don't close this page while uploading
                                </p>
                            </div>

                            <!-- Success Message -->
                            <div v-if="uploadStatus === 'success'" class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <p class="text-sm text-green-600 dark:text-green-400 flex items-center gap-2">
                                    <CheckCircle class="h-4 w-4" />
                                    Video uploaded successfully! Saving...
                                </p>
                            </div>

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
                            <label for="duration" class="block text-sm font-medium mb-2">Duration (optional)</label>
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
                            <label for="order" class="block text-sm font-medium mb-2">Order (optional)</label>
                            <input
                                id="order"
                                v-model="form.order"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            />
                            <p class="mt-1 text-xs text-muted-foreground">Leave empty to add at the end</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="uploadStatus === 'uploading' || uploadStatus === 'success' || !videoFile"
                            class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ uploadStatus === 'uploading' ? 'Uploading...' : uploadStatus === 'success' ? 'Saving...' : 'Upload Video' }}
                        </button>
                        <Link
                            :href="`/admin/courses/${course.id}/edit`"
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
