<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { appUrl, video, recommendedVideos } = usePage().props;

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
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
</script>

<template>
    <DefaultLayout :title="video.title" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
        <Head>
            <title>{{ video.title }}</title>
            <meta name="title" :content="video.title || ''" />
            <meta name="description" :content="video.meta_desc || ''" />
            <meta name="keywords" :content="video.meta_keywords || ''" />
            <meta name="author" :content="video.author || ''" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <meta property="og:title" :content="video.title || ''" />
            <meta property="og:description" :content="video.meta_desc || ''" />
            <meta property="og:type" content="video" />
            <meta property="og:url" :content="`/videos/${video.url}`" />
            <meta property="og:image" :content="video.images?.[0]?.url || ''" />
            <meta property="og:locale" :content="video.locale || 'ru_RU'" />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" :content="video.title || ''" />
            <meta name="twitter:description" :content="video.meta_desc || ''" />
            <meta name="twitter:image" :content="video.images?.[0]?.url || ''" />
            <meta itemprop="name" :content="video.title || ''" />
            <meta itemprop="description" :content="video.meta_desc || ''" />
            <meta itemprop="image" :content="video.images?.[0]?.url || ''" />
        </Head>

        <div class="flex flex-col w-full">

            <nav class="text-left text-sm ml-0 md:ml-4 lg:ml-6 xl:ml-8 mt-4 px-1"
                 aria-label="Breadcrumb">
                <ol class="list-reset flex flex-col md:flex-row items-center justify-start space-x-0">
                    <li>
                        <Link href="/" class="hover:underline text-slate-900 dark:text-slate-100">
                            {{ t('home') }}
                        </Link>
                        <span class="mx-1 text-slate-900 dark:text-slate-100">/</span>
                    </li>
                    <li class="text-slate-900 dark:text-slate-100 lowercase">
                        {{ video.title }}
                    </li>
                </ol>
            </nav>

            <div class="flex flex-col p-4 bg-slate-50 dark:bg-slate-950 mx-auto">
                <div class="flex-1 max-w-3xl">
                    <header>
                        <div class="flex flex-col items-center justify-center my-1">

                            <h1 class="text-center font-semibold text-xl text-gray-900 dark:text-slate-100 mb-4">
                                {{ video.title }}
                            </h1>

                            <span :title="t('views')"
                                  class="hidden ml-2 px-1 py-0.5 text-xs font-semibold text-white bg-emerald-500 rounded-full">
                                {{ video.views }}
                            </span>

                        </div>
                    </header>

                    <div class="relative w-full aspect-video bg-black rounded shadow overflow-hidden">

                        <template v-if="video.source_type === 'code'">
                            <div class="w-full h-full" v-html="getVideoUrl(video)"></div>
                        </template>

                        <iframe v-else-if="['youtube', 'vimeo'].includes(video.source_type)"
                                class="w-full h-full"
                                :src="getVideoUrl(video)"
                                frameborder="0"
                                allow="fullscreen; picture-in-picture"
                                allowfullscreen>
                        </iframe>

                        <video v-else-if="video.source_type === 'local'"
                               class="w-full h-full object-contain"
                               controls>
                            <source :src="getVideoUrl(video)" type="video/mp4" />
                            {{ t('videoNotSupported') }}
                        </video>

                    </div>

                    <time :datetime="formatDate(video.published_at)"
                          class="opacity-75 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">
                        {{ t('published') }}: {{ formatDate(video.published_at) }}
                    </time>

                    <div v-if="video.short" class="my-3">
                        <p class="text-center text-sm text-slate-700 dark:text-slate-300 font-semibold">
                            {{ video.short }}
                        </p>
                    </div>

                    <div v-if="video.description"
                         class="w-full max-w-4xl mx-auto my-4 p-2 dark:text-slate-100"
                         v-html="video.description">
                    </div>

                </div>
            </div>

            <!-- Рекомендованные видео -->
            <div v-if="recommendedVideos && recommendedVideos.length > 0"
                 class="px-8 pb-4">

                <h2 class="mb-4 text-center text-xl font-semibold text-slate-800 dark:text-slate-100">
                    {{ t('relatedVideos') }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

                    <div v-for="item in recommendedVideos" :key="item.id"
                         class="relative flex flex-col rounded
                                overflow-hidden bg-white dark:bg-slate-800
                                border-2 border-slate-400 shadow-md shadow-gray-400">

                        <!-- Плеер -->
                        <div class="relative w-full bg-black aspect-video
                                    flex items-center justify-center">

                            <template v-if="item.source_type === 'code'">
                                <div class="w-full h-full" v-html="getVideoUrl(item)"></div>
                            </template>

                            <iframe v-else-if="['youtube', 'vimeo'].includes(item.source_type)"
                                    class="w-full h-full"
                                    :src="getVideoUrl(item)"
                                    frameborder="0"
                                    allow="autoplay; fullscreen; picture-in-picture"
                                    allowfullscreen>
                            </iframe>

                            <video v-else-if="item.source_type === 'local'"
                                   class="w-full h-full object-contain"
                                   controls>
                                <source :src="getVideoUrl(item)" type="video/mp4" />
                                {{ t('videoNotSupported') }}
                            </video>

                        </div>

                        <!-- Название и дата -->
                        <div class="text-center text-sm font-semibold text-slate-800 dark:text-slate-100 pt-1">
                            <Link :href="`/videos/${item.url}`"
                                  class="block text-sm font-semibold text-slate-800 dark:text-slate-100
                                         hover:text-red-600 hover:dark:text-red-400 transition">
                                {{ item.title }}
                            </Link>
                            <div class="pb-2 text-center text-xs text-slate-600 dark:text-slate-400">
                                {{ formatDate(item.published_at) }}
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </DefaultLayout>
</template>
