<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ArticleImageMainIndex from '@/Components/Public/Default/Article/ArticleImageMainIndex.vue';

const { mainArticles, appUrl } = usePage().props;

const getImgSrc = (imgPath) => {
    if (!imgPath) return '';
    const base = appUrl.endsWith('/') ? appUrl.slice(0, -1) : appUrl;
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath;
    return `${base}/storage/${path}`;
};

const filteredArticles = computed(() =>
    mainArticles.filter(article =>
        article.img || (article.images && article.images.length > 0)
    )
);

const slider = ref(null);

const scrollLeft = () => {
    slider.value?.scrollBy({ left: -300, behavior: 'smooth' });
};

const scrollRight = () => {
    slider.value?.scrollBy({ left: 300, behavior: 'smooth' });
};
</script>

<template>
    <div class="relative bg-gray-100 dark:bg-slate-900 py-8">
        <div class="max-w-8xl mx-auto px-4">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-4">
                {{ $t('main_articles') }}
            </h2>

            <div class="relative">
                <div
                    ref="slider"
                    class="flex space-x-4 overflow-x-auto scrollbar-hide scroll-smooth"
                >
                    <div
                        v-for="article in filteredArticles"
                        :key="article.id"
                        class="min-w-[300px] max-w-xs flex-shrink-0 bg-white dark:bg-slate-800 rounded-md shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300"
                    >
                        <div class="p-4 border-b border-gray-300">
                            <img
                                v-if="article.img"
                                :src="getImgSrc(article.img)"
                                :alt="article.title"
                                class="rounded-md border border-black dark:border-gray-200 w-full h-48 object-cover"
                            />
                            <div v-else-if="article.images?.length > 0">
                                <ArticleImageMainIndex
                                    :images="article.images"
                                    :link="`/articles/${article.url}`"
                                    class="w-full h-48 object-cover"
                                />
                            </div>
                        </div>
                        <div class="p-4">
                            <h2 class="text-md font-semibold text-gray-900 dark:text-white mb-1">
                                {{ article.title }}
                            </h2>
                            <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">
                                {{ article.short }}
                            </p>
                        </div>
                    </div>
                </div>

                <button
                    @click="scrollLeft"
                    class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white px-2 py-1 rounded-r z-10"
                >
                    &#10094;
                </button>
                <button
                    @click="scrollRight"
                    class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white px-2 py-1 rounded-l z-10"
                >
                    &#10095;
                </button>
            </div>
        </div>
    </div>
</template>
