<script setup>
import {computed} from 'vue';
import {Link, usePage} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';
import ArticleImageSlider from "@/Components/Public/Default/Article/ArticleImageSlider.vue";

const props = defineProps({
    section: Object,
    appUrl: String
});

const { t, locale } = useI18n();
const {appUrl} = usePage().props;

const getImgSrc = (imgPath) => {
    if (!imgPath) return '';
    const base = appUrl.endsWith('/') ? appUrl.slice(0, -1) : appUrl;
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath;
    return `${base}/storage/${path}`;
};

const sortedArticles = computed(() => {
    return [...props.section.articles].sort((a, b) => new Date(b.sort) - new Date(a.sort));
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
};

const linkHref = locale.value === 'kk'
    ? '/rubrics/zhangalyqtar'
    : '/rubrics/novosti';

</script>

<template>
    <div v-if="section.articles.length">

        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 ml-3 mb-2">
            {{ section.title }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Левая колонка — первая статья -->
            <div v-if="sortedArticles.length > 0"
                 class="shadow overflow-hidden
                        border-2 border-gray-400 rounded-xl">

                <div class="relative">

                    <Link v-if="sortedArticles[0].img" :href="`/articles/${sortedArticles[0].url}`">
                        <img :src="getImgSrc(sortedArticles[0].img)" alt="Article image"
                             class="w-full h-full object-cover"/>
                        <div class="absolute bottom-0 left-0 w-full
                                    bg-black bg-opacity-50 text-white text-sm font-bold p-3">
                            {{ sortedArticles[0].title }}
                        </div>
                    </Link>

                    <Link v-else-if="sortedArticles[0].images?.length"
                          :href="`/articles/${sortedArticles[0].url}`">
                        <div class="relative">
                            <ArticleImageSlider
                                :images="sortedArticles[0].images"
                                :link="`/articles/${sortedArticles[0].url}`"/>
                            <div class="absolute bottom-0 left-0 w-full
                                        bg-black bg-opacity-50 text-white text-base font-bold p-3">
                                {{ sortedArticles[0].title }}
                            </div>
                        </div>
                    </Link>

                    <Link v-else :href="`/articles/${sortedArticles[0].url}`"
                          class="flex items-center justify-center
                                 bg-gray-200 dark:bg-gray-400 h-64 relative">
                        <span class="text-slate-900 dark:text-slate-100">{{ t('noCurrentImage') }}</span>

                        <div class="absolute bottom-0 left-0 w-full
                                    bg-black bg-opacity-50 text-white text-sm font-bold p-0.5">
                            {{ sortedArticles[0].title }}
                        </div>
                    </Link>

                </div>
            </div>

            <!-- Правая колонка — статьи 2–N -->
            <div class="grid grid-cols-1 gap-4">
                <div
                    v-for="(article, index) in sortedArticles.slice(1)"
                    :key="article.id"
                    class="overflow-hidden">

                    <div class="flex flex-col sm:flex-row">

                        <!-- Левая часть — изображение (1/4) -->
                        <div class="w-full sm:w-1/4 relative overflow-hidden h-auto max-h-28
                                    border-2 border-gray-400 rounded-md">

                            <Link v-if="article.img" :href="`/articles/${article.url}`">
                                <img :src="getImgSrc(article.img)" alt="Article image"
                                     class="w-full h-40 sm:h-full object-cover"/>
                            </Link>

                            <Link v-else-if="article.images?.length" :href="`/articles/${article.url}`">
                                <ArticleImageSlider
                                    :images="article.images"
                                    :link="`/articles/${article.url}`"/>
                            </Link>

                            <Link v-else :href="`/articles/${article.url}`"
                                  class="flex items-center justify-center
                                         bg-gray-200 dark:bg-gray-400 h-40 sm:h-full">
                                <span class="text-slate-900 dark:text-slate-100">{{ t('noCurrentImage') }}</span>
                            </Link>

                        </div>

                        <!-- Правая часть — заголовок и описание (3/4) -->
                        <div class="w-full sm:w-3/4 py-1 px-3 flex flex-col justify-center">
                            <Link :href="`/articles/${article.url}`"
                                  class="text-xs md:text-sm lg:text-lg font-semibold text-gray-800 dark:text-white
                                         hover:text-red-500 dark:hover:text-orange-300">
                                {{ article.title }}
                            </Link>
                            <div class="mt-2 text-[11px] text-slate-700/85 dark:text-slate-300/85">
                                {{ formatDate(article.published_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="w-full flex justify-center items-center py-4">
            <Link
                :href="linkHref"
                class="inline-block px-2 py-0.5 rounded-sm text-sm font-semibold
                       border border-slate-400 dark:border-slate-500
                       bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                       hover:text-red-400 dark:hover:text-red-300
                       transition duration-200 ease-in-out shadow-sm hover:shadow-md">
                {{ t('watchAll') }} »
            </Link>
        </div>

    </div>
</template>
