<script setup lang="ts">
import UserDashboardLayout from '@/layouts/user/UserDashboardLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    PlayCircle,
    Clock,
    BookOpen,
    X,
    ChevronLeft,
    ChevronRight,
    Maximize,
    Minimize,
    Video,
    Lock
} from 'lucide-vue-next';

interface VideoItem {
    id: number;
    title: string;
    duration: string | null;
    order: number;
}

interface Course {
    id: number;
    title: string;
    description: string | null;
    thumbnail: string | null;
    active_videos: VideoItem[];
}

interface Props {
    course: Course;
    expiresAt: string | null;
    daysRemaining: number | null;
}

const props = defineProps<Props>();

// Video Player Modal State
const showVideoModal = ref(false);
const currentVideo = ref<VideoItem | null>(null);
const videoUrl = ref<string | null>(null);
const videoLoading = ref(false);
const videoError = ref<string | null>(null);
const isFullscreen = ref(false);
const videoRef = ref<HTMLVideoElement | null>(null);
const modalRef = ref<HTMLDivElement | null>(null);

const currentIndex = computed(() => {
    if (!currentVideo.value) return -1;
    return props.course.active_videos.findIndex(v => v.id === currentVideo.value?.id);
});

const prevVideo = computed(() => {
    if (currentIndex.value > 0) {
        return props.course.active_videos[currentIndex.value - 1];
    }
    return null;
});

const nextVideo = computed(() => {
    if (currentIndex.value < props.course.active_videos.length - 1) {
        return props.course.active_videos[currentIndex.value + 1];
    }
    return null;
});

const formatDaysRemaining = (days: number | null) => {
    if (days === null) return 'Lifetime Access';
    if (days < 0) return 'Expired';
    if (days === 0) return 'Expires Today';
    return `${days} Days Left`;
};

const openVideo = async (video: VideoItem) => {
    currentVideo.value = video;
    showVideoModal.value = true;
    videoLoading.value = true;
    videoError.value = null;
    videoUrl.value = null;

    try {
        const response = await fetch(`/my/videos/${video.id}/stream`);
        const data = await response.json();

        if (response.ok) {
            videoUrl.value = data.url;
        } else {
            videoError.value = data.error || 'Failed to load video';
        }
    } catch (e) {
        videoError.value = 'Failed to load video';
    } finally {
        videoLoading.value = false;
    }
};

const closeVideo = () => {
    showVideoModal.value = false;
    currentVideo.value = null;
    videoUrl.value = null;
    videoError.value = null;
    isFullscreen.value = false;
    if (document.fullscreenElement) {
        document.exitFullscreen();
    }
};

const playNext = () => {
    if (nextVideo.value) {
        openVideo(nextVideo.value);
    }
};

const playPrev = () => {
    if (prevVideo.value) {
        openVideo(prevVideo.value);
    }
};

const toggleFullscreen = async () => {
    if (!modalRef.value) return;

    if (!document.fullscreenElement) {
        await modalRef.value.requestFullscreen();
        isFullscreen.value = true;
    } else {
        await document.exitFullscreen();
        isFullscreen.value = false;
    }
};

// Handle fullscreen change
document.addEventListener('fullscreenchange', () => {
    isFullscreen.value = !!document.fullscreenElement;
});

// Prevent right-click on video
document.addEventListener('contextmenu', (e) => {
    if ((e.target as HTMLElement)?.closest('video')) {
        e.preventDefault();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (!showVideoModal.value) return;

    if (e.key === 'Escape') {
        if (isFullscreen.value) {
            document.exitFullscreen();
        } else {
            closeVideo();
        }
    } else if (e.key === 'ArrowRight' && nextVideo.value) {
        playNext();
    } else if (e.key === 'ArrowLeft' && prevVideo.value) {
        playPrev();
    } else if (e.key === 'f' || e.key === 'F') {
        toggleFullscreen();
    }
});
</script>

<template>
    <Head :title="`${course.title} - TDA Academy`" />

    <UserDashboardLayout>
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
            <!-- Course Header -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <!-- Hero Section -->
                <div class="relative h-48 md:h-64 bg-gradient-to-r from-gray-900 to-gray-800">
                    <img
                        v-if="course.thumbnail"
                        :src="`/storage/${course.thumbnail}`"
                        :alt="course.title"
                        class="absolute inset-0 w-full h-full object-cover opacity-40"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

                    <!-- Course Info Overlay -->
                    <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-600 text-white text-xs font-medium">
                                <Video class="h-3 w-3" />
                                {{ course.active_videos.length }} Videos
                            </span>
                            <span :class="[
                                'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium',
                                daysRemaining === null ? 'bg-green-600 text-white' :
                                daysRemaining <= 7 ? 'bg-orange-500 text-white' : 'bg-white/20 text-white'
                            ]">
                                <Clock class="h-3 w-3" />
                                {{ formatDaysRemaining(daysRemaining) }}
                            </span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-bold text-white">{{ course.title }}</h1>
                    </div>
                </div>

                <!-- Description -->
                <div v-if="course.description" class="px-6 md:px-8 py-5 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-gray-600 dark:text-gray-400">{{ course.description }}</p>
                </div>

                <!-- Start Learning CTA -->
                <div v-if="course.active_videos.length > 0" class="px-6 md:px-8 py-5 bg-gray-50 dark:bg-gray-800/50">
                    <button
                        @click="openVideo(course.active_videos[0])"
                        class="inline-flex items-center gap-3 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-medium transition-colors"
                    >
                        <PlayCircle class="h-5 w-5" />
                        Start Learning
                    </button>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Begin with "{{ course.active_videos[0].title }}"
                    </p>
                </div>
            </div>

            <!-- Video List -->
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Course Content</h2>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <button
                        v-for="(video, index) in course.active_videos"
                        :key="video.id"
                        @click="openVideo(video)"
                        class="w-full flex items-center gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-left"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 font-semibold text-sm flex-shrink-0">
                            {{ String(index + 1).padStart(2, '0') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ video.title }}</p>
                            <p v-if="video.duration" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ video.duration }}</p>
                        </div>
                        <PlayCircle class="h-5 w-5 text-gray-400 flex-shrink-0" />
                    </button>

                    <div v-if="course.active_videos.length === 0" class="px-6 py-12 text-center">
                        <BookOpen class="h-10 w-10 text-gray-400 mx-auto mb-3" />
                        <p class="text-gray-500 dark:text-gray-400">No videos available yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Player Modal -->
        <Teleport to="body">
            <div
                v-if="showVideoModal"
                class="fixed inset-0 z-50 flex items-center justify-center"
            >
                <!-- Backdrop -->
                <div
                    @click="closeVideo"
                    class="absolute inset-0 bg-black/90"
                ></div>

                <!-- Modal Content -->
                <div
                    ref="modalRef"
                    :class="[
                        'relative w-full bg-black flex flex-col',
                        isFullscreen ? 'h-full' : 'max-w-5xl mx-4 rounded-2xl overflow-hidden'
                    ]"
                >
                    <!-- Header -->
                    <div :class="[
                        'flex items-center justify-between px-4 py-3 bg-gray-900',
                        isFullscreen ? 'absolute top-0 left-0 right-0 z-10 bg-gradient-to-b from-black/80 to-transparent' : ''
                    ]">
                        <div class="flex-1 min-w-0 pr-4">
                            <p class="text-white font-medium truncate">{{ currentVideo?.title }}</p>
                            <p class="text-gray-400 text-sm">
                                {{ course.title }} • Video {{ currentIndex + 1 }} of {{ course.active_videos.length }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                @click="toggleFullscreen"
                                class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors"
                                :title="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'"
                            >
                                <Minimize v-if="isFullscreen" class="h-5 w-5" />
                                <Maximize v-else class="h-5 w-5" />
                            </button>
                            <button
                                @click="closeVideo"
                                class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Video Player -->
                    <div :class="['relative bg-black flex-1', isFullscreen ? '' : 'aspect-video']">
                        <!-- Loading -->
                        <div v-if="videoLoading" class="absolute inset-0 flex items-center justify-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-600 border-t-red-500"></div>
                        </div>

                        <!-- Error -->
                        <div v-else-if="videoError" class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center px-4">
                                <Lock class="h-12 w-12 text-red-500 mx-auto mb-3" />
                                <p class="text-red-400 mb-4">{{ videoError }}</p>
                                <button
                                    @click="currentVideo && openVideo(currentVideo)"
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700"
                                >
                                    Try Again
                                </button>
                            </div>
                        </div>

                        <!-- Video -->
                        <video
                            v-else-if="videoUrl"
                            ref="videoRef"
                            :src="videoUrl"
                            class="w-full h-full"
                            controls
                            autoplay
                            controlsList="nodownload"
                            disablePictureInPicture
                            @contextmenu.prevent
                            @ended="nextVideo && playNext()"
                        >
                            Your browser does not support the video tag.
                        </video>
                    </div>

                    <!-- Navigation Footer -->
                    <div :class="[
                        'flex items-center justify-between px-4 py-3 bg-gray-900',
                        isFullscreen ? 'absolute bottom-0 left-0 right-0 z-10 bg-gradient-to-t from-black/80 to-transparent' : ''
                    ]">
                        <button
                            v-if="prevVideo"
                            @click="playPrev"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700 text-sm transition-colors"
                        >
                            <ChevronLeft class="h-4 w-4" />
                            <span class="hidden sm:inline">Previous</span>
                        </button>
                        <div v-else></div>

                        <div class="flex items-center gap-1 text-gray-500 text-sm">
                            <span
                                v-for="(_, i) in course.active_videos"
                                :key="i"
                                :class="[
                                    'w-2 h-2 rounded-full',
                                    i === currentIndex ? 'bg-red-500' : 'bg-gray-600'
                                ]"
                            ></span>
                        </div>

                        <button
                            v-if="nextVideo"
                            @click="playNext"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm transition-colors"
                        >
                            <span class="hidden sm:inline">Next</span>
                            <ChevronRight class="h-4 w-4" />
                        </button>
                        <button
                            v-else
                            @click="closeVideo"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 text-sm transition-colors"
                        >
                            Complete
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </UserDashboardLayout>
</template>

<style scoped>
video::-webkit-media-controls-download-button,
video::-webkit-media-controls-overflow-button {
    display: none !important;
}
</style>
