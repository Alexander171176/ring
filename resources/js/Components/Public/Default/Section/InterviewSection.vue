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
    ? '/rubrics/sukhbat'
    : '/rubrics/intervyu';

</script>

<template>
    <div v-if="section.articles.length">

        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 ml-3 mb-2">
            {{ section.title }}
        </h2>

        <!-- Карточки статей: 4 в ряд -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            <div v-for="article in sortedArticles"
                :key="article.id"
                class="p-2 rounded-sm shadow-sm
                       overflow-hidden hover:bg-slate-50 dark:hover:bg-slate-800
                       hover:shadow-lg hover:shadow-gray-400 dark:hover:shadow-gray-700">

                <!-- Контейнер Изображения -->
                <div class="overflow-hidden h-auto mb-2 rounded-md max-h-36
                                border-2 border-gray-400
                                shadow-lg shadow-gray-400 dark:shadow-gray-900">

                    <!-- Изображение статьи -->
                    <Link v-if="article.img"
                          :href="`/articles/${article.url}`"
                          class="h-auto overflow-hidden">
                        <img :src="getImgSrc(article.img)"
                             alt="Article image"
                             class="w-full h-auto object-cover"/>
                    </Link>
                    <Link v-else-if="article.images && article.images.length > 0"
                          :href="`/articles/${article.url}`"
                          class="h-auto overflow-hidden">
                        <ArticleImageSlider
                            :images="article.images"
                            :link="`/articles/${article.url}`"
                            class="w-full h-full object-cover"/>
                    </Link>
                    <Link v-else
                          :href="`/articles/${article.url}`"
                          class="h-auto flex items-center justify-center bg-gray-200 dark:bg-gray-400">
                        <span class="text-gray-500 dark:text-gray-700">{{ t('noCurrentImage') }}</span>
                    </Link>
                </div>

                <!-- Контейнер Контента -->
                <div class="flex flex-col flex-grow">

                    <!-- Заголовок -->
                    <div class="mb-3 my-1 text-left">
                        <h3 class="text-md font-semibold text-black dark:text-white">
                            <Link :href="`/articles/${article.url}`"
                                  class="hover:text-blue-600 dark:hover:text-blue-400 line-clamp-2">
                                {{ article.title }}
                            </Link>
                        </h3>
                    </div>

                    <!-- Дата публикации -->
                    <div class="opacity-75 text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                        {{ formatDate(article.published_at) }}
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
