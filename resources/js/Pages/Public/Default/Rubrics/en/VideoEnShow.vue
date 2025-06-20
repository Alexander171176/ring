<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import {ref, computed} from 'vue';
import {useI18n} from 'vue-i18n';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';

const {t} = useI18n();
const {rubric, videos, appUrl} = usePage().props;

const searchQuery = ref('');

const filteredVideos = computed(() => {
    return videos.filter(video => {
        if (!searchQuery.value.trim()) return true;
        return video.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

const currentPage = ref(1);
const itemsPerPage = 8;

const paginatedVideos = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredVideos.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredVideos.value.length / itemsPerPage);
});

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

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
            if (video.video_url) {
                return video.video_url;
            }
            console.warn("⚠️ У локального видео нет video_url");
            return null;
        }

        if (source === 'code') {
            return video.video_code || video.embed_code || null;
        }
    } catch (e) {
        console.error("❌ Ошибка разбора видео:", e);
        return null;
    }

    return null;
};

const activeVideoId = ref(null);
const playVideo = (id) => {
    activeVideoId.value = id;
};

</script>

<template>
    <DefaultLayout :title="rubric.title" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
        <Head>
            <title>{{ rubric.title }}</title>
            <meta name="title" :content="rubric.title || ''"/>
            <meta name="keywords" :content="rubric.meta_keywords || ''"/>
            <meta name="description" :content="rubric.meta_desc || ''"/>

            <meta property="og:title" :content="rubric.title || ''"/>
            <meta property="og:description" :content="rubric.meta_desc || ''"/>
            <meta property="og:type" content="website"/>
            <meta property="og:url" :content="`/rubrics/${rubric.url}`"/>
            <meta property="og:image" :content="rubric.icon || ''"/>
            <meta property="og:locale" :content="rubric.locale || 'ru_RU'"/>

            <meta name="twitter:card" content="summary_large_image"/>
            <meta name="twitter:title" :content="rubric.title || ''"/>
            <meta name="twitter:description" :content="rubric.meta_desc || ''"/>
            <meta name="twitter:image" :content="rubric.icon || ''"/>

            <meta name="DC.title" :content="rubric.title || ''"/>
            <meta name="DC.description" :content="rubric.meta_desc || ''"/>
            <meta name="DC.identifier" :content="`/rubrics/${rubric.url}`"/>
            <meta name="DC.language" :content="rubric.locale || 'ru'"/>
        </Head>

        <div class="flex-1 p-4 selection:bg-red-400 selection:text-white bg-slate-50 dark:bg-blue-950">

            <!-- Хлебные крошки -->
            <nav class="text-sm ml-0 md:ml-4 lg:ml-6 xl:ml-8" aria-label="Breadcrumb">
                <ol class="list-reset flex items-center space-x-0">
                    <li>
                        <Link href="/" class="hover:underline text-slate-900 dark:text-slate-100">
                            {{ t('home') }}
                        </Link>
                    </li>
                    <li>
                        <span class="mx-1 text-slate-900 dark:text-slate-100">/</span>
                    </li>
                    <li class="text-slate-900 dark:text-slate-100">
                        {{ rubric.title }}
                    </li>
                </ol>
            </nav>

            <!-- Заголовок рубрики -->
            <h1 class="flex items-center justify-center my-4
                       text-center font-bolder text-xl
                       text-slate-900 dark:text-slate-100">
                <span v-if="rubric.icon" class="flex justify-center" v-html="rubric.icon"/>
                {{ rubric.title }}
            </h1>

            <p v-if="rubric.short"
               class="flex items-center justify-center mb-4
                      tracking-wide text-center text-md text-gray-700 dark:text-gray-300">
                {{ rubric.short }}
            </p>

            <!-- Строка поиска -->
            <div class="max-w-xl mx-auto">
                <input v-model="searchQuery" type="text" :placeholder="t('searchByName')"
                       class="w-full px-3 py-0.5 bg-white dark:bg-gray-700
                           font-semibold text-sm text-slate-600 dark:text-slate-100
                           border border-slate-500 dark:border-slate-400 rounded-sm
                           focus:outline-none focus:ring-1 focus:border-blue-300"
                />
            </div>

            <div class="space-y-8 mt-8 mx-8">

                <!-- Список видео -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

                    <div v-for="video in paginatedVideos" :key="video.id"
                         class="relative flex flex-col rounded
                                overflow-hidden bg-white dark:bg-slate-800
                                border-2 border-slate-400 shadow-md shadow-gray-400">

                        <!-- Плеер -->
                        <div class="relative w-full bg-black aspect-video
                                    flex items-center justify-center">

                            <!-- Показываем изображение и кнопку Play -->
                            <template v-if="video.images?.length && activeVideoId !== video.id">

                                <img
                                    :src="video.images[0].url"
                                    :alt="video.images[0].alt || video.title"
                                    class="w-full h-full object-cover"
                                />

                                <!-- Обёртка для подложки и кнопки -->
                                <div class="absolute inset-0 flex items-center justify-center">

                                    <!-- Полупрозрачная подложка -->
                                    <!--                                    <div class="absolute inset-0 bg-black/15 backdrop-blur-sm"></div>-->

                                    <!-- Кнопка Play -->
                                    <button
                                        @click="playVideo(video.id)"
                                        class="absolute top-1/2
                                           left-1/2 transform -translate-x-1/2 -translate-y-1/2
                                           bg-white/30 hover:bg-white/40 backdrop-blur-md rounded-full
                                           p-2 border-8 border-white/30">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-8 h-8 text-red-600"
                                             viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </button>

                                </div>

                            </template>

                            <!-- Показываем плеер, если активен -->
                            <template v-else>

                                <template v-if="video.source_type === 'code'">
                                    <div class="w-full h-full" v-html="getVideoUrl(video)"></div>
                                </template>

                                <iframe
                                    v-else-if="['youtube', 'vimeo'].includes(video.source_type)"
                                    class="w-full h-full"
                                    :src="getVideoUrl(video)"
                                    frameborder="0"
                                    allow="autoplay; fullscreen; picture-in-picture"
                                    allowfullscreen
                                ></iframe>

                                <video
                                    v-else-if="video.source_type === 'local'"
                                    class="w-full h-full object-contain"
                                    controls
                                >
                                    <source :src="getVideoUrl(video)" type="video/mp4"/>
                                    {{ t('videoNotSupported') }}
                                </video>

                            </template>

                        </div>

                        <!-- Название и дата -->
                        <div class="text-center text-sm font-semibold text-slate-800 dark:text-slate-100 pt-1">
                            <Link
                                :href="`/videos/${video.url}`"
                                class="block text-sm font-semibold text-slate-800 dark:text-slate-100
                                       hover:text-red-600 hover:dark:text-red-400 transition">
                                {{ video.title }}
                            </Link>

                            <div class="pb-2 text-center text-xs text-slate-600 dark:text-slate-400">
                                {{ video.published_at }}
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Пагинация -->
                <div v-if="totalPages > 1"
                     class="flex justify-center items-center mt-4 space-x-2 text-xs font-semibold">

                    <!-- Кнопка назад -->
                    <button @click="prevPage" :disabled="currentPage === 1"
                            class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                   border border-gray-400 dark:border-gray-200 disabled:opacity-50">
                        «
                    </button>

                    <!-- Инпут страницы -->
                    <span class="text-gray-700 dark:text-gray-200">{{ t('page') }}</span>
                    <input
                        type="number"
                        v-model.number="currentPage"
                        :min="1"
                        :max="totalPages"
                        class="w-12 text-center px-1 py-1 border border-gray-400 dark:border-gray-200 rounded
               bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"
                    />
                    <span class="text-gray-700 dark:text-gray-200">{{ t('of') }} {{ totalPages }}</span>

                    <!-- Кнопка вперёд -->
                    <button @click="nextPage" :disabled="currentPage === totalPages"
                            class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                   border border-gray-400 dark:border-gray-200 disabled:opacity-50">
                        »
                    </button>
                </div>

            </div>

        </div>
    </DefaultLayout>
</template>
