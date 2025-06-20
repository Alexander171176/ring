<script setup>
import {ref, computed, watchEffect, onMounted, onBeforeUnmount} from 'vue';
import {usePage} from "@inertiajs/vue3";

const {appUrl} = usePage().props;

const props = defineProps({
    tournaments: Array,
    t: Function,
});

const emit = defineEmits(['video-shown']);

// Сортировка
const sorted = computed(() => {
    return [...props.tournaments].sort((a, b) => b.sort - a.sort);
});

// Пагинация
const page = ref(1);
const itemsPerPage = 1;
const totalPages = computed(() => Math.max(1, Math.ceil(sorted.value.length / itemsPerPage)));
const paginated = computed(() => {
    const start = (page.value - 1) * itemsPerPage;
    return sorted.value.slice(start, start + itemsPerPage);
});
const goToPage = (p) => {
    page.value = Math.min(Math.max(1, p), totalPages.value);
};

// Видео
const activeVideoTournamentId = ref(null);
const showVideo = (id) => { activeVideoTournamentId.value = id; emit('video-shown', id); };
const closeVideo = () => { activeVideoTournamentId.value = null; };

// Конвертация
const getVideoUrl = (video) => {
    if (!video.external_video_id) return null;

    const source = video.source_type;

    try {
        if (source === 'youtube') {
            const url = new URL(video.external_video_id);
            const videoId = url.searchParams.get('v');
            return `https://www.youtube.com/embed/${videoId}`;
        }

        if (source === 'vimeo') {
            const url = new URL(video.external_video_id);
            const videoId = url.pathname.split('/').pop();
            return `https://player.vimeo.com/video/${videoId}`;
        }

        if (source === 'local') {
            const baseUrl = usePage().props.appUrl.replace(/\/$/, '');
            const path = video.external_video_id.replace(/^\/+/, ''); // удалим /
            return `${baseUrl}/storage/${path}`;
        }

        if (source === 'code') {
            return video.external_video_id; // возвращаем HTML-код напрямую
        }

    } catch {
        return null;
    }

    return null;
};

const getTournamentImgSrc = (imgPath) => {
    if (!imgPath) return '';
    const base = appUrl.endsWith('/') ? appUrl.slice(0, -1) : appUrl;
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath;
    return `${base}/storage/${path}`;
};

const countdowns = ref({}); // храним оставшееся время для каждого турнира

const updateCountdowns = () => {
    const now = new Date().getTime();

    props.tournaments.forEach(tournament => {
        const targetTime = new Date(tournament.tournament_date_time).getTime();
        const distance = targetTime - now;

        if (distance > 0) {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdowns.value[tournament.id] = { days, hours, minutes, seconds };
        } else {
            countdowns.value[tournament.id] = null;
        }
    });
};

let timerInterval = null;
onMounted(() => {
    updateCountdowns();
    timerInterval = setInterval(updateCountdowns, 1000);
});

onBeforeUnmount(() => {
    if (timerInterval) clearInterval(timerInterval);
});

function highlightVs(name) {
    const formatted = name.replaceAll('-', ' ');
    return formatted.replace(/\bvs\b/i, '<span class="text-red-400">vs</span>');
}

</script>

<template>
    <div v-if="paginated.length" class="max-w-3xl mx-auto relative overflow-hidden shadow-lg shadow-gray-600">
        <div class="relative w-full">
            <div v-for="tournament in paginated" :key="tournament.id"
                 class="min-w-full px-4 pt-4 bg-blue-900 text-slate-100 text-lg font-semibold uppercase relative"
            >
                <div class="text-center">
                    <h3
                        class="uppercase font-semibold text-center dark:text-white mb-2"
                        v-html="highlightVs(tournament.name)">
                    </h3>
                    <span class="text-sm block mb-1 text-amber-400">{{ tournament.venue }}</span>
                    <span class="text-sm block mb-1 text-slate-400">{{ tournament.tournament_date_time }}</span>
                </div>

                <!-- Видео -->
                <div v-if="activeVideoTournamentId === tournament.id"
                     class="relative w-full h-80 bg-black flex items-center justify-center">
                    <button @click="closeVideo" class="absolute top-2 right-2 text-white text-xl z-10">✕</button>

                    <template v-if="tournament.videos?.length">
                        <template v-if="tournament.videos[0].source_type === 'code'">
                            <div class="w-full h-full" v-html="getVideoUrl(tournament.videos[0])"></div>
                        </template>

                        <iframe
                            v-else-if="['youtube', 'vimeo'].includes(tournament.videos[0].source_type)"
                            class="w-full h-full"
                            :src="getVideoUrl(tournament.videos[0])"
                            frameborder="0"
                            allow="autoplay; fullscreen; picture-in-picture"
                            allowfullscreen
                        ></iframe>

                        <video v-else controls autoplay class="w-full h-full object-contain">
                            <source :src="getVideoUrl(tournament.videos[0])" type="video/mp4" />
                            {{ t('videoNotSupported') }}
                        </video>
                    </template>
                </div>

                <!-- Блок с изображением или бойцами -->
                <div v-else>
                    <!-- Если есть изображение -->
                    <template v-if="tournament.images && tournament.images.length > 0">
                        <img
                            :src="tournament.images[0].url"
                            :alt="tournament.images[0].alt"
                            class="w-full h-auto object-cover"
                        />
                    </template>

                    <!-- Иначе старый блок с бойцами -->
                    <div v-else class="flex items-end space-x-4 pt-4">
                        <div class="flex flex-col items-center w-1/2">
                            <img v-if="tournament.fighter_red?.avatar"
                                 :src="`/storage/${tournament.fighter_red.avatar}`"
                                 class="w-auto h-80"
                                 :alt="tournament.fighter_red.nickname" />
                            <span class="mt-4">{{ tournament.fighter_red?.nickname.replaceAll('-', ' ') }}</span>
                        </div>

                        <span class="text-orange-400">vs</span>

                        <div class="flex flex-col items-center w-1/2">
                            <img v-if="tournament.fighter_blue?.avatar"
                                 :src="`/storage/${tournament.fighter_blue.avatar}`"
                                 class="w-auto h-80"
                                 :alt="tournament.fighter_blue.nickname" />
                            <span class="mt-4">{{ tournament.fighter_blue?.nickname.replaceAll('-', ' ') }}</span>
                        </div>
                    </div>

                    <!-- ▶ Кнопка Play -->
                    <button
                        v-if="tournament.videos?.length"
                        @click="showVideo(tournament.id)"
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2
                               bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-full p-1
                               border-8 border-white/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-red-500"
                             fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>

                    <!-- Обратный отсчёт -->
                    <div class="w-fit mx-auto flex justify-center items-center gap-0 mt-2
                                shadow rounded-sm bg-white text-blue-900"
                         v-if="countdowns[tournament.id]">
                        <div class="text-center px-3 py-1">
                            <div class="text-xs">{{ t('timerDays') }}</div>
                            <div class="text-xl font-bold text-rose-600">
                                {{ countdowns[tournament.id].days }}
                            </div>
                        </div>
                        <div class="text-center px-3 py-1">
                            <div class="text-xs">{{ t('timerHours') }}</div>
                            <div class="text-xl font-bold text-rose-600">
                                {{ countdowns[tournament.id].hours }}
                            </div>
                        </div>
                        <div class="text-center px-3 py-1">
                            <div class="text-xs">{{ t('timerMinutes') }}</div>
                            <div class="text-xl font-bold text-rose-600">
                                {{ countdowns[tournament.id].minutes }}
                            </div>
                        </div>
                        <div class="text-center px-3 py-1">
                            <div class="text-xs">{{ t('timerSeconds') }}</div>
                            <div class="text-xl font-bold text-rose-600">
                                {{ countdowns[tournament.id].seconds }}
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-center mt-4 text-white text-lg">{{ t('timerTournamentStarted') }}</p>

                </div>

            </div>
        </div>

        <!-- Пагинация -->
        <div class="flex justify-center items-center space-x-2 p-2 bg-blue-900" v-if="totalPages > 1">
            <span
                v-for="p in totalPages"
                :key="p"
                @click="goToPage(p)"
                class="w-3 h-3 rounded-full cursor-pointer transition-all duration-200"
                :class="page === p ? 'bg-red-500' : 'bg-gray-100 hover:bg-gray-300'"
            />
        </div>
    </div>
</template>
