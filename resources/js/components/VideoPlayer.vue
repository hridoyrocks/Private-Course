<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import {
    Play,
    Pause,
    Volume2,
    VolumeX,
    Maximize,
    Minimize,
    SkipBack,
    SkipForward,
    Settings,
    Loader2
} from 'lucide-vue-next';

interface Props {
    src: string;
    title?: string;
    autoplay?: boolean;
    onEnded?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    autoplay: true
});

const emit = defineEmits(['ended', 'error']);

const videoRef = ref<HTMLVideoElement | null>(null);
const containerRef = ref<HTMLDivElement | null>(null);
const progressRef = ref<HTMLDivElement | null>(null);

// State
const isPlaying = ref(false);
const isMuted = ref(false);
const isFullscreen = ref(false);
const isLoading = ref(true);
const hasError = ref(false);
const errorMessage = ref('');
const showControls = ref(true);
const currentTime = ref(0);
const duration = ref(0);
const volume = ref(1);
const buffered = ref(0);
const playbackRate = ref(1);
const showSettings = ref(false);

let controlsTimeout: NodeJS.Timeout | null = null;

// Computed
const progress = computed(() => {
    if (duration.value === 0) return 0;
    return (currentTime.value / duration.value) * 100;
});

const bufferedProgress = computed(() => {
    if (duration.value === 0) return 0;
    return (buffered.value / duration.value) * 100;
});

const formatTime = (seconds: number): string => {
    if (isNaN(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

// Methods
const togglePlay = () => {
    if (!videoRef.value) return;

    if (isPlaying.value) {
        videoRef.value.pause();
    } else {
        videoRef.value.play();
    }
};

const toggleMute = () => {
    if (!videoRef.value) return;
    videoRef.value.muted = !videoRef.value.muted;
    isMuted.value = videoRef.value.muted;
};

const setVolume = (e: Event) => {
    if (!videoRef.value) return;
    const target = e.target as HTMLInputElement;
    const value = parseFloat(target.value);
    videoRef.value.volume = value;
    volume.value = value;
    isMuted.value = value === 0;
};

const toggleFullscreen = async () => {
    if (!containerRef.value) return;

    if (!document.fullscreenElement) {
        await containerRef.value.requestFullscreen();
        isFullscreen.value = true;
    } else {
        await document.exitFullscreen();
        isFullscreen.value = false;
    }
};

const seek = (e: MouseEvent) => {
    if (!videoRef.value || !progressRef.value) return;

    const rect = progressRef.value.getBoundingClientRect();
    const percent = (e.clientX - rect.left) / rect.width;
    videoRef.value.currentTime = percent * duration.value;
};

const skip = (seconds: number) => {
    if (!videoRef.value) return;
    videoRef.value.currentTime = Math.max(0, Math.min(duration.value, videoRef.value.currentTime + seconds));
};

const setPlaybackRate = (rate: number) => {
    if (!videoRef.value) return;
    videoRef.value.playbackRate = rate;
    playbackRate.value = rate;
    showSettings.value = false;
};

const handleTimeUpdate = () => {
    if (!videoRef.value) return;
    currentTime.value = videoRef.value.currentTime;
};

const handleLoadedMetadata = () => {
    if (!videoRef.value) return;
    duration.value = videoRef.value.duration;
    isLoading.value = false;
};

const handleProgress = () => {
    if (!videoRef.value) return;
    if (videoRef.value.buffered.length > 0) {
        buffered.value = videoRef.value.buffered.end(videoRef.value.buffered.length - 1);
    }
};

const handlePlay = () => {
    isPlaying.value = true;
};

const handlePause = () => {
    isPlaying.value = false;
};

const handleEnded = () => {
    isPlaying.value = false;
    emit('ended');
};

const handleWaiting = () => {
    isLoading.value = true;
};

const handleCanPlay = () => {
    isLoading.value = false;
    hasError.value = false;
};

const handleError = (e: Event) => {
    isLoading.value = false;
    hasError.value = true;
    const video = e.target as HTMLVideoElement;
    const error = video.error;
    if (error) {
        switch (error.code) {
            case MediaError.MEDIA_ERR_ABORTED:
                errorMessage.value = 'Video loading was aborted';
                break;
            case MediaError.MEDIA_ERR_NETWORK:
                errorMessage.value = 'Network error occurred';
                break;
            case MediaError.MEDIA_ERR_DECODE:
                errorMessage.value = 'Video decoding error';
                break;
            case MediaError.MEDIA_ERR_SRC_NOT_SUPPORTED:
                errorMessage.value = 'Video format not supported';
                break;
            default:
                errorMessage.value = 'An error occurred';
        }
    }
    emit('error', errorMessage.value);
};

const retryVideo = () => {
    if (videoRef.value) {
        hasError.value = false;
        isLoading.value = true;
        videoRef.value.load();
        videoRef.value.play().catch(() => {});
    }
};

const showControlsTemporarily = () => {
    showControls.value = true;

    if (controlsTimeout) {
        clearTimeout(controlsTimeout);
    }

    controlsTimeout = setTimeout(() => {
        if (isPlaying.value) {
            showControls.value = false;
        }
    }, 3000);
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.target instanceof HTMLInputElement) return;

    switch (e.key) {
        case ' ':
        case 'k':
            e.preventDefault();
            togglePlay();
            break;
        case 'ArrowLeft':
            e.preventDefault();
            skip(-10);
            break;
        case 'ArrowRight':
            e.preventDefault();
            skip(10);
            break;
        case 'ArrowUp':
            e.preventDefault();
            if (videoRef.value) {
                videoRef.value.volume = Math.min(1, videoRef.value.volume + 0.1);
                volume.value = videoRef.value.volume;
            }
            break;
        case 'ArrowDown':
            e.preventDefault();
            if (videoRef.value) {
                videoRef.value.volume = Math.max(0, videoRef.value.volume - 0.1);
                volume.value = videoRef.value.volume;
            }
            break;
        case 'm':
            toggleMute();
            break;
        case 'f':
            toggleFullscreen();
            break;
    }

    showControlsTemporarily();
};

// Fullscreen change handler
const handleFullscreenChange = () => {
    isFullscreen.value = !!document.fullscreenElement;
};

onMounted(() => {
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('keydown', handleKeydown);

    if (videoRef.value && props.autoplay) {
        videoRef.value.play().catch(() => {
            // Autoplay blocked
        });
    }
});

onUnmounted(() => {
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
    document.removeEventListener('keydown', handleKeydown);

    if (controlsTimeout) {
        clearTimeout(controlsTimeout);
    }
});

// Watch for src changes
watch(() => props.src, () => {
    isLoading.value = true;
    hasError.value = false;
    currentTime.value = 0;
    duration.value = 0;
});
</script>

<template>
    <div
        ref="containerRef"
        class="relative bg-black w-full h-full group select-none"
        @mousemove="showControlsTemporarily"
        @mouseleave="isPlaying && (showControls = false)"
        @click.self="togglePlay"
    >
        <!-- Video Element -->
        <video
            ref="videoRef"
            :src="src"
            class="w-full h-full"
            preload="auto"
            playsinline
            @timeupdate="handleTimeUpdate"
            @loadedmetadata="handleLoadedMetadata"
            @progress="handleProgress"
            @play="handlePlay"
            @pause="handlePause"
            @ended="handleEnded"
            @waiting="handleWaiting"
            @canplay="handleCanPlay"
            @error="handleError"
            @contextmenu.prevent
        />

        <!-- Loading Overlay -->
        <div
            v-if="isLoading && !hasError"
            class="absolute inset-0 z-30 flex items-center justify-center bg-black/50"
        >
            <div class="text-center">
                <Loader2 class="h-12 w-12 text-red-500 animate-spin mx-auto" />
                <p class="text-white text-sm mt-3">Loading video...</p>
            </div>
        </div>

        <!-- Error Overlay -->
        <div
            v-if="hasError"
            class="absolute inset-0 z-30 flex items-center justify-center bg-black/80"
        >
            <div class="text-center px-4">
                <div class="w-16 h-16 rounded-full bg-red-600/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="text-red-400 mb-4">{{ errorMessage }}</p>
                <button
                    @click="retryVideo"
                    class="bg-red-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors"
                >
                    Try Again
                </button>
            </div>
        </div>

        <!-- Play/Pause Overlay (Center) -->
        <div
            v-if="!isLoading && !hasError"
            class="absolute inset-0 z-10 flex items-center justify-center cursor-pointer"
            @click="togglePlay"
        >
            <div
                v-if="!isPlaying"
                class="w-20 h-20 rounded-full bg-red-600/90 flex items-center justify-center transition-transform hover:scale-110"
            >
                <Play class="h-10 w-10 text-white ml-1" fill="white" />
            </div>
        </div>

        <!-- Controls -->
        <div
            :class="[
                'absolute bottom-0 left-0 right-0 z-20 bg-gradient-to-t from-black/90 via-black/50 to-transparent transition-opacity duration-300 pt-16',
                showControls || !isPlaying ? 'opacity-100' : 'opacity-0',
                showControls || !isPlaying ? 'pointer-events-auto' : 'pointer-events-none'
            ]"
        >
            <!-- Progress Bar -->
            <div
                ref="progressRef"
                class="relative h-1 mx-4 mb-2 cursor-pointer group/progress"
                @click="seek"
            >
                <div class="absolute inset-0 bg-white/30 rounded-full"></div>
                <div
                    class="absolute top-0 left-0 h-full bg-white/50 rounded-full"
                    :style="{ width: `${bufferedProgress}%` }"
                ></div>
                <div
                    class="absolute top-0 left-0 h-full bg-red-500 rounded-full"
                    :style="{ width: `${progress}%` }"
                ></div>
                <div
                    class="absolute top-1/2 -translate-y-1/2 w-3 h-3 bg-red-500 rounded-full opacity-0 group-hover/progress:opacity-100 transition-opacity"
                    :style="{ left: `calc(${progress}% - 6px)` }"
                ></div>
            </div>

            <!-- Control Buttons -->
            <div class="flex items-center justify-between px-4 pb-3">
                <div class="flex items-center gap-2">
                    <!-- Play/Pause -->
                    <button
                        @click="togglePlay"
                        class="p-2 rounded-lg text-white hover:bg-white/20 transition-colors"
                    >
                        <Pause v-if="isPlaying" class="h-5 w-5" />
                        <Play v-else class="h-5 w-5" />
                    </button>

                    <!-- Skip Backward -->
                    <button
                        @click="skip(-10)"
                        class="p-2 rounded-lg text-white hover:bg-white/20 transition-colors"
                        title="Rewind 10s"
                    >
                        <SkipBack class="h-5 w-5" />
                    </button>

                    <!-- Skip Forward -->
                    <button
                        @click="skip(10)"
                        class="p-2 rounded-lg text-white hover:bg-white/20 transition-colors"
                        title="Forward 10s"
                    >
                        <SkipForward class="h-5 w-5" />
                    </button>

                    <!-- Volume -->
                    <div class="flex items-center gap-1 group/volume">
                        <button
                            @click="toggleMute"
                            class="p-2 rounded-lg text-white hover:bg-white/20 transition-colors"
                        >
                            <VolumeX v-if="isMuted || volume === 0" class="h-5 w-5" />
                            <Volume2 v-else class="h-5 w-5" />
                        </button>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.1"
                            :value="volume"
                            @input="setVolume"
                            class="w-0 group-hover/volume:w-20 transition-all duration-200 accent-red-500"
                        />
                    </div>

                    <!-- Time -->
                    <span class="text-white text-sm ml-2">
                        {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Playback Speed -->
                    <div class="relative">
                        <button
                            @click="showSettings = !showSettings"
                            class="p-2 rounded-lg text-white hover:bg-white/20 transition-colors"
                        >
                            <Settings class="h-5 w-5" />
                        </button>

                        <!-- Settings Menu -->
                        <div
                            v-if="showSettings"
                            class="absolute bottom-full right-0 mb-2 bg-gray-900 rounded-lg py-2 min-w-[120px] shadow-xl"
                        >
                            <p class="px-3 py-1 text-xs text-gray-400 uppercase">Speed</p>
                            <button
                                v-for="rate in [0.5, 0.75, 1, 1.25, 1.5, 2]"
                                :key="rate"
                                @click="setPlaybackRate(rate)"
                                :class="[
                                    'w-full px-3 py-1.5 text-left text-sm hover:bg-white/10 transition-colors',
                                    playbackRate === rate ? 'text-red-500' : 'text-white'
                                ]"
                            >
                                {{ rate === 1 ? 'Normal' : `${rate}x` }}
                            </button>
                        </div>
                    </div>

                    <!-- Fullscreen -->
                    <button
                        @click="toggleFullscreen"
                        class="p-2 rounded-lg text-white hover:bg-white/20 transition-colors"
                    >
                        <Minimize v-if="isFullscreen" class="h-5 w-5" />
                        <Maximize v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Title (Top) -->
        <div
            v-if="title"
            :class="[
                'absolute top-0 left-0 right-0 p-4 bg-gradient-to-b from-black/70 to-transparent transition-opacity duration-300',
                showControls || !isPlaying ? 'opacity-100' : 'opacity-0'
            ]"
        >
            <p class="text-white font-medium truncate">{{ title }}</p>
        </div>
    </div>
</template>

<style scoped>
/* Hide default video controls */
video::-webkit-media-controls {
    display: none !important;
}

video::-webkit-media-controls-enclosure {
    display: none !important;
}

/* Custom range input styling */
input[type="range"] {
    -webkit-appearance: none;
    background: transparent;
    height: 4px;
}

input[type="range"]::-webkit-slider-runnable-track {
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    height: 12px;
    width: 12px;
    border-radius: 50%;
    background: #ef4444;
    margin-top: -4px;
    cursor: pointer;
}

input[type="range"]::-moz-range-track {
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
}

input[type="range"]::-moz-range-thumb {
    height: 12px;
    width: 12px;
    border-radius: 50%;
    background: #ef4444;
    border: none;
    cursor: pointer;
}
</style>
