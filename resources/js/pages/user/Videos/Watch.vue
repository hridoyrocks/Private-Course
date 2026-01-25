<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { PlayCircle, ChevronLeft, ChevronRight, ArrowLeft, Clock, List, X, GraduationCap } from 'lucide-vue-next';

interface VideoItem {
    id: number;
    title: string;
    duration: string | null;
    order: number;
}

interface Course {
    id: number;
    title: string;
}

interface Props {
    video: {
        id: number;
        title: string;
        duration: string | null;
    };
    course: Course;
    allVideos: VideoItem[];
}

const props = defineProps<Props>();

const videoUrl = ref<string | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);
const videoRef = ref<HTMLVideoElement | null>(null);
const showPlaylist = ref(false);

const currentIndex = computed(() => {
    return props.allVideos.findIndex(v => v.id === props.video.id);
});

const prevVideo = computed(() => {
    if (currentIndex.value > 0) {
        return props.allVideos[currentIndex.value - 1];
    }
    return null;
});

const nextVideo = computed(() => {
    if (currentIndex.value < props.allVideos.length - 1) {
        return props.allVideos[currentIndex.value + 1];
    }
    return null;
});

const fetchVideoUrl = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await fetch(`/my/videos/${props.video.id}/stream`);
        const data = await response.json();

        if (response.ok) {
            videoUrl.value = data.url;
        } else {
            error.value = data.error || 'Failed to load video';
        }
    } catch (e) {
        error.value = 'Failed to load video';
    } finally {
        loading.value = false;
    }
};

let refreshInterval: number;

onMounted(() => {
    fetchVideoUrl();

    document.addEventListener('contextmenu', (e) => {
        if ((e.target as HTMLElement)?.closest('video')) {
            e.preventDefault();
        }
    });

    refreshInterval = setInterval(() => {
        if (videoUrl.value) {
            fetchVideoUrl();
        }
    }, 25 * 60 * 1000);
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<template>
    <Head :title="`${video.title} - TDA Academy`" />

    <div class="min-h-screen bg-gray-900">
        <!-- Header -->
        <header class="bg-gray-900 border-b border-gray-800">
            <div class="flex items-center justify-between px-4 py-3 max-w-7xl mx-auto">
                <div class="flex items-center gap-4">
                    <Link
                        :href="`/my/courses/${course.id}`"
                        class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors"
                    >
                        <ArrowLeft class="h-5 w-5" />
                        <span class="hidden sm:inline text-sm">Back</span>
                    </Link>
                    <div class="hidden md:flex items-center gap-2 text-gray-500">
                        <span class="w-px h-5 bg-gray-700"></span>
                        <Link href="/dashboard" class="flex items-center gap-2 text-gray-400 hover:text-white">
                            <GraduationCap class="h-5 w-5 text-red-500" />
                            <span class="text-sm font-medium">TDA Academy</span>
                        </Link>
                    </div>
                </div>

                <button
                    @click="showPlaylist = !showPlaylist"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors"
                >
                    <List class="h-5 w-5" />
                    <span class="hidden sm:inline text-sm">Playlist</span>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex">
            <!-- Video Section -->
            <div class="flex-1">
                <!-- Video Player -->
                <div class="bg-black">
                    <div class="max-w-5xl mx-auto">
                        <div class="aspect-video relative">
                            <!-- Loading -->
                            <div v-if="loading" class="absolute inset-0 flex items-center justify-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-600 border-t-red-500"></div>
                            </div>

                            <!-- Error -->
                            <div v-else-if="error" class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center px-4">
                                    <p class="text-red-400 mb-4">{{ error }}</p>
                                    <button
                                        @click="fetchVideoUrl"
                                        class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700"
                                    >
                                        Try Again
                                    </button>
                                </div>
                            </div>

                            <!-- Video -->
                            <video
                                v-else
                                ref="videoRef"
                                :src="videoUrl || undefined"
                                class="w-full h-full"
                                controls
                                controlsList="nodownload"
                                disablePictureInPicture
                                autoplay
                                @contextmenu.prevent
                            >
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>

                <!-- Video Info -->
                <div class="max-w-5xl mx-auto px-4 py-6">
                    <div class="text-sm text-gray-500 mb-2">
                        {{ course.title }} • Video {{ currentIndex + 1 }} of {{ allVideos.length }}
                    </div>
                    <h1 class="text-xl font-bold text-white mb-2">{{ video.title }}</h1>
                    <div v-if="video.duration" class="flex items-center gap-1.5 text-sm text-gray-400">
                        <Clock class="h-4 w-4" />
                        {{ video.duration }}
                    </div>

                    <!-- Navigation -->
                    <div class="flex items-center gap-3 mt-6">
                        <Link
                            v-if="prevVideo"
                            :href="`/my/videos/${prevVideo.id}/watch`"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700 text-sm"
                        >
                            <ChevronLeft class="h-4 w-4" />
                            Previous
                        </Link>
                        <Link
                            v-if="nextVideo"
                            :href="`/my/videos/${nextVideo.id}/watch`"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm"
                        >
                            Next
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                        <Link
                            v-if="!nextVideo"
                            :href="`/my/courses/${course.id}`"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 text-sm"
                        >
                            Complete
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Playlist Sidebar -->
            <div
                :class="[
                    'fixed md:relative top-0 right-0 h-full w-80 bg-gray-800 border-l border-gray-700 transform transition-transform z-50',
                    showPlaylist ? 'translate-x-0' : 'translate-x-full md:hidden'
                ]"
            >
                <div class="flex items-center justify-between p-4 border-b border-gray-700">
                    <div>
                        <h3 class="font-medium text-white">Playlist</h3>
                        <p class="text-xs text-gray-400">{{ allVideos.length }} videos</p>
                    </div>
                    <button @click="showPlaylist = false" class="md:hidden p-1 text-gray-400 hover:text-white">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="overflow-y-auto h-[calc(100%-60px)]">
                    <Link
                        v-for="(v, index) in allVideos"
                        :key="v.id"
                        :href="`/my/videos/${v.id}/watch`"
                        :class="[
                            'flex items-center gap-3 px-4 py-3 border-b border-gray-700/50 transition-colors',
                            v.id === video.id ? 'bg-red-600/20' : 'hover:bg-gray-700/50'
                        ]"
                    >
                        <div :class="[
                            'flex h-8 w-8 items-center justify-center rounded text-xs font-medium flex-shrink-0',
                            v.id === video.id ? 'bg-red-600 text-white' : 'bg-gray-700 text-gray-400'
                        ]">
                            {{ String(index + 1).padStart(2, '0') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p :class="['text-sm truncate', v.id === video.id ? 'text-white font-medium' : 'text-gray-300']">
                                {{ v.title }}
                            </p>
                            <p v-if="v.duration" class="text-xs text-gray-500">{{ v.duration }}</p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Mobile overlay -->
            <div
                v-if="showPlaylist"
                @click="showPlaylist = false"
                class="fixed inset-0 bg-black/50 z-40 md:hidden"
            ></div>
        </div>
    </div>
</template>

<style scoped>
video::-webkit-media-controls-download-button {
    display: none !important;
}
video::-webkit-media-controls-overflow-button {
    display: none !important;
}
</style>
