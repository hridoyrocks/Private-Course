<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Video, ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

interface Course {
    id: number;
    title: string;
}

interface Props {
    course: Course;
}

const props = defineProps<Props>();

const form = useForm({
    title: '',
    video: null as File | null,
    duration: '',
    order: 0,
});

const videoName = ref<string | null>(null);
const uploadProgress = ref(0);

const handleVideoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.video = file;
        videoName.value = file.name;
    }
};

const submit = () => {
    form.post(`/admin/courses/${props.course.id}/videos`, {
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
                            <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <!-- Video Upload -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Video File</label>
                            <div
                                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-8 cursor-pointer hover:border-red-400"
                                @click="($refs.videoInput as HTMLInputElement).click()"
                            >
                                <Video class="h-12 w-12 text-muted-foreground mb-4" />
                                <p v-if="videoName" class="text-sm font-medium">{{ videoName }}</p>
                                <p v-else class="text-sm text-muted-foreground">Click to select a video</p>
                                <p class="text-xs text-muted-foreground mt-2">MP4, AVI, MOV (max 5GB)</p>
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
                            <div v-if="form.processing && uploadProgress > 0" class="mt-4">
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
                            :disabled="form.processing || !form.video"
                            class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Uploading...' : 'Upload Video' }}
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
