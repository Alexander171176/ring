<script setup>
import {computed, ref} from 'vue'
import {Link, usePage} from '@inertiajs/vue3'
import {useI18n} from 'vue-i18n'

const {t} = useI18n()
const {appUrl} = usePage().props

// ✅ Теперь мы получаем массив видео напрямую
const props = defineProps({
    videos: Array,
})

const videos = computed(() => props.videos)

const sortedVideos = computed(() => {
    return [...videos.value].sort((a, b) => new Date(b.published_at) - new Date(a.published_at))
})

const getImgSrc = (imgPath) => {
    if (!imgPath) return ''
    const base = appUrl.endsWith('/') ? appUrl.slice(0, -1) : appUrl
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath
    return `${base}/storage/${path}`
}

const activeVideoId = ref(null)
const playVideo = (id) => {
    activeVideoId.value = id
}

const getVideoUrl = (video) => {
    const source = video.source_type

    try {
        if (source === 'youtube') {
            const ext = video.external_video_id
            if (!ext && video.display_source) return video.display_source

            if (ext?.startsWith('http')) {
                const url = new URL(ext)
                const videoId = url.searchParams.get('v')
                return videoId ? `https://www.youtube.com/embed/${videoId}` : null
            }

            return ext ? `https://www.youtube.com/embed/${ext}` : null
        }

        if (source === 'vimeo') {
            const ext = video.external_video_id
            if (!ext && video.display_source) return video.display_source

            if (ext?.startsWith('http')) {
                const url = new URL(ext)
                const videoId = url.pathname.split('/').pop()
                return `https://player.vimeo.com/video/${videoId}`
            }

            return ext ? `https://player.vimeo.com/video/${ext}` : null
        }

        if (source === 'local') {
            return video.video_url || video.display_source || null
        }

        if (source === 'code') {
            return video.video_code || video.embed_code || null
        }
    } catch (e) {
        console.error('❌ Ошибка разбора видео:', e)
        return null
    }

    return null
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    return `${day}.${month}.${year}`
}

const currentPage = ref(1)
const itemsPerPage = 4

const totalPages = computed(() => {
    return Math.ceil(videos.value.length / itemsPerPage)
})

const paginatedVideos = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return sortedVideos.value.slice(start, start + itemsPerPage)
})

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++
}
</script>

<template>
    <div v-if="videos.length" class="mt-6">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 ml-3 mb-2">
            {{ t('videos') }}
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                v-for="vid in paginatedVideos"
                :key="vid.id"
                class="overflow-hidden"
            >
                <div class="relative w-full bg-black aspect-video flex items-center justify-center">
                    <!-- Картинка + кнопка Play -->
                    <template v-if="vid.images?.length && activeVideoId !== vid.id">
                        <img
                            :src="vid.images[0].url"
                            :alt="vid.images[0].alt || vid.title"
                            class="w-full h-full object-cover"
                        />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <button
                                @click="playVideo(vid.id)"
                                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white/30 hover:bg-white/40 backdrop-blur-md rounded-full p-2 border-8 border-white/30"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600" viewBox="0 0 24 24"
                                     fill="currentColor">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <!-- Плеер -->
                    <template v-else>
                        <template v-if="vid.source_type === 'code'">
                            <div class="w-full h-full" v-html="getVideoUrl(vid)"></div>
                        </template>

                        <iframe
                            v-else-if="['youtube', 'vimeo'].includes(vid.source_type)"
                            class="w-full h-full"
                            :src="getVideoUrl(vid)"
                            frameborder="0"
                            allow="autoplay; fullscreen; picture-in-picture"
                            allowfullscreen
                        ></iframe>

                        <video
                            v-else-if="vid.source_type === 'local' && getVideoUrl(vid)"
                            class="w-full h-full object-contain"
                            controls
                            preload="metadata"
                        >
                            <source :src="getVideoUrl(vid)" type="video/mp4"/>
                            {{ t('videoNotSupported') }}
                        </video>

                        <div v-else class="text-center text-white py-4">
                            {{ t('videoNotSupported') }}
                        </div>
                    </template>
                </div>

                <!-- Заголовок и дата -->
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-black dark:text-white line-clamp-2 mb-2">
                        <Link :href="`/videos/${vid.url}`"
                              class="hover:text-red-600 hover:dark:text-red-400 transition">
                            {{ vid.title }}
                        </Link>
                    </h3>

                    <time
                        v-if="vid.published_at"
                        :datetime="formatDate(vid.published_at)"
                        class="text-xs text-slate-500 dark:text-slate-400"
                    >
                        {{ t('published') }}: {{ formatDate(vid.published_at) }}
                    </time>
                </div>
            </div>
        </div>

        <!-- Пагинация -->
        <div
            v-if="totalPages > 1"
            class="flex justify-center items-center mt-4 space-x-2 text-xs font-semibold"
        >
            <button
                @click="prevPage"
                :disabled="currentPage === 1"
                class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-400 dark:border-gray-200 disabled:opacity-50"
            >
                «
            </button>

            <span class="text-gray-700 dark:text-gray-200">{{ t('page') }}</span>

            <input
                type="number"
                v-model.number="currentPage"
                :min="1"
                :max="totalPages"
                class="w-12 text-center px-1 py-1 border border-gray-400 dark:border-gray-200 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"
            />

            <span class="text-gray-700 dark:text-gray-200">{{ t('of') }} {{ totalPages }}</span>

            <button
                @click="nextPage"
                :disabled="currentPage === totalPages"
                class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-400 dark:border-gray-200 disabled:opacity-50"
            >
                »
            </button>
        </div>
    </div>
</template>
