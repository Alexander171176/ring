<script setup>
import {computed, ref} from 'vue';
import { useI18n } from 'vue-i18n';
import {usePage} from "@inertiajs/vue3";

const { t } = useI18n();

const { appUrl } = usePage().props;

const props = defineProps({
    tournament: Object,
    winnerFighter: String,
    highlightVs: Function,
    videos: Array
});

// console.log('Received videos:', props.videos);

const getVideoUrl = (video) => {
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
            if (video.video_url) return video.video_url;
            return `${appUrl}/storage/${video.external_video_id}`;
        }
        if (source === 'code') {
            return video.video_code || video.embed_code || null;
        }
    } catch (e) {
        console.error('❌ Ошибка разбора видео:', e);
        return null;
    }
    return null;
};

const activeSlide = ref(0); // 0 — это изображение, 1+ — это видео


</script>

<template>
    <div class="flex flex-col lg:flex-row lg:gap-6 items-start w-full">

        <!-- Левая колонка — слайдер изображение/видео -->
        <div v-if="tournament.images?.length" class="w-full lg:w-2/3">

            <!-- Слайд: либо изображение, либо видео -->
            <div class="relative w-full aspect-video bg-black rounded-xl overflow-hidden shadow-lg border-2 border-gray-400 dark:border-gray-600">

                <!-- Изображение турнира -->
                <img
                    v-if="activeSlide === 0"
                    :src="tournament.images[0].url"
                    :alt="tournament.images[0].alt"
                    class="w-full h-full object-cover"
                />

                <!-- Видео: отображается если выбрано -->
                <template v-else-if="activeSlide > 0 && videos?.[activeSlide - 1]">
                    <template v-if="videos[activeSlide - 1].source_type === 'code'">
                        <div class="w-full h-full" v-html="getVideoUrl(videos[activeSlide - 1])"></div>
                    </template>

                    <iframe v-else-if="['youtube', 'vimeo'].includes(videos[activeSlide - 1].source_type)"
                            class="w-full h-full"
                            :src="getVideoUrl(videos[activeSlide - 1])"
                            frameborder="0"
                            allow="fullscreen; picture-in-picture"
                            allowfullscreen>
                    </iframe>

                    <video v-else-if="videos[activeSlide - 1].source_type === 'local'"
                           class="w-full h-full object-contain"
                           controls>
                        <source :src="getVideoUrl(videos[activeSlide - 1])" type="video/mp4" />
                        {{ t('videoNotSupported') }}
                    </video>
                </template>

                <!-- 🔘 Точки навигации: только если есть видео -->
                <div v-if="videos?.length"
                     class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex justify-center items-center gap-2 z-10">

                    <!-- Первая точка — изображение -->
                    <button
                        @click="activeSlide = 0"
                        class="w-3 h-3 rounded-full transition"
                        :class="activeSlide === 0 ? 'bg-red-500' : 'bg-gray-400 dark:bg-gray-600'">
                    </button>

                    <!-- Остальные точки — видео -->
                    <button
                        v-for="(video, index) in videos"
                        :key="video.id"
                        @click="activeSlide = index + 1"
                        class="w-3 h-3 rounded-full transition"
                        :class="activeSlide === index + 1 ? 'bg-red-500' : 'bg-gray-400 dark:bg-gray-600'">
                    </button>
                </div>

            </div>

            <!-- Текст под слайдером -->
            <div v-if="tournament.short" class="my-2 xl:my-3">
                <p class="text-sm text-slate-900 dark:text-slate-100 px-1 py-2">
                    {{ tournament.short }}
                </p>
            </div>
        </div>

        <!-- Правая колонка — текст -->
        <div v-if="tournament.images?.length"
             class="w-full md:w-1/2 md:mx-auto lg:w-1/3 flex flex-col justify-start">

            <span class="text-left text-[11px] text-gray-700 dark:text-gray-300
                        mt-0 mb-2 xl:mt-8 xl:mb-4">
                {{ t('battle') }}
            </span>

            <header class="my-1">
                <h1 class="uppercase font-black dark:text-white"
                    v-html="highlightVs(tournament.name)">
                </h1>
            </header>

            <time :datetime="tournament.tournament_date_time"
                  class="text-red-500 dark:text-red-400 text-xs font-black my-4">
                {{ tournament.tournament_date_time }} (UTC+5) Astana
            </time>

            <div class="my-1 xl:my-4">
                <div v-if="tournament.venue" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ tournament.venue }}
                </div>

                <div v-if="tournament.rounds_scheduled" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('roundsScheduled') }}: {{ tournament.rounds_scheduled }}
                </div>

                <div v-if="tournament.weight_class_name" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ tournament.weight_class_name }}
                </div>
            </div>

            <div class="my-1 xl:my-4">
                <div v-if="winnerFighter" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('winner') }}: {{ winnerFighter }}
                </div>

                <div v-if="tournament.method_of_victory" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('methodVictory') }}: {{ tournament.method_of_victory }}
                </div>

                <div v-if="tournament.round_of_finish" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('roundFinish') }}: {{ tournament.round_of_finish }}
                </div>

                <div v-if="tournament.time_of_finish" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('timeFinish') }}: {{ tournament.time_of_finish }}
                </div>
            </div>

        </div>

    </div>
</template>
