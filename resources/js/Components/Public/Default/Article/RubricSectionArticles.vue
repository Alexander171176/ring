<script setup>
import {Link, router, usePage} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';
import {ref, onMounted, computed, watch} from 'vue';
import ArticleImageSlider from "@/Components/Public/Default/Article/ArticleImageSlider.vue";

const {t} = useI18n();
const {appUrl} = usePage().props;

const getImgSrc = (imgPath) => {
    if (!imgPath) return '';
    const base = appUrl.endsWith('/') ? appUrl.slice(0, -1) : appUrl;
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath;
    return `${base}/storage/${path}`;
};

const props = defineProps({
    articles: Array,
    pagination: Object,
    baseUrl: String,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // месяцы от 0
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
};

const viewMode = ref('horizontal');

onMounted(() => {
    const saved = localStorage.getItem('articleViewMode');
    if (saved === 'grid') viewMode.value = 'grid';
});

const setViewMode = (mode) => {
    viewMode.value = mode;
    localStorage.setItem('articleViewMode', mode);
};

const goToPage = (page) => {
    if (page >= 1 && page <= props.pagination.lastPage) {
        router.get(props.baseUrl, {page}, {
            preserveScroll: true,
            preserveState: false, // ⬅ ОБЯЗАТЕЛЬНО!
            only: ['articles', 'pagination'], // ⬅ Чтобы ничего лишнего не дергать
        });
    }
};
</script>

<template>
    <div>
        <div class="space-y-3">

            <!-- Заголовок и фильтры -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-4 mt-6">

                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                    {{ t('otherNews') }}
                </h2>

                <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">

                    <!-- Переключатель вида -->
                    <div class="flex justify-end items-center space-x-2">
                        <button @click="setViewMode('grid')"
                                :class="[
                  'p-1 border transition-colors duration-200 rounded',
                  viewMode === 'grid'
                  ? 'border-slate-400 dark:border-slate-200 text-red-400 dark:text-red-200'
                  : 'border-slate-300 dark:border-slate-400 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 hover:border-slate-500 dark:hover:border-slate-500']">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>

                        <button @click="setViewMode('horizontal')"
                                :class="[
                  'p-1 border transition-colors duration-200 rounded',
                  viewMode === 'horizontal'
                  ? 'border-slate-400 dark:border-slate-200 text-red-400 dark:text-red-200'
                  : 'border-slate-300 dark:border-slate-400 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 hover:border-slate-500 dark:hover:border-slate-500']">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                </div>

            </div>

            <!-- Внутренний контейнер grid -->
            <div v-if="viewMode === 'grid'"
                 class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

                <div v-for="article in articles" :key="article.id"
                     class="p-2 rounded-sm shadow-sm
                       overflow-hidden hover:bg-slate-50 dark:hover:bg-slate-800
                       hover:shadow-lg hover:shadow-gray-400 dark:hover:shadow-gray-700">

                    <!-- Контейнер Изображения -->
                    <div class="overflow-hidden h-auto mb-2
                                border border-gray-800 dark:border-gray-400
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

                        <!-- Краткое описание -->
                        <div class="mb-3">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 line-clamp-3">
                                {{ article.short }}
                            </p>
                        </div>

                        <!-- Дата публикации -->
                        <div class="opacity-75 text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            {{ formatDate(article.published_at) }}
                        </div>

                    </div>
                </div>
            </div>

            <!-- Внутренний контейнер horizontal -->
            <div v-else class="space-y-4">

                <div v-for="article in articles" :key="article.id"
                    class="col-span-full flex flex-row items-start space-x-3 p-2 shadow-sm rounded-sm
                       overflow-hidden hover:bg-slate-50 dark:hover:bg-slate-800
                       hover:shadow-lg hover:shadow-gray-400 dark:hover:shadow-gray-700">

                    <!-- Изображение -->
                    <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-56 h-auto
                                shrink-0 overflow-hidden">

                        <Link v-if="article.img" :href="`/articles/${article.url}`"
                              class="h-auto overflow-hidden">
                            <img :src="getImgSrc(article.img)" :alt="article.title"
                                 class="w-full h-auto object-cover rounded-lg
                                        border border-black dark:border-gray-200"/>
                        </Link>
                        <Link v-else-if="article.images?.length" :href="`/articles/${article.url}`">
                            <ArticleImageSlider :images="article.images" :link="`/articles/${article.url}`"/>
                        </Link>
                        <Link v-else :href="`/articles/${article.url}`"
                              class="h-auto flex items-center justify-center bg-gray-200 dark:bg-gray-400">
                            <span class="text-slate-900 dark:text-slate-100">{{ t('noCurrentImage') }}</span>
                        </Link>
                    </div>

                    <div class="flex flex-col flex-grow pl-3">

                        <!-- Заголовок -->
                        <div class="mb-3 my-1 text-left">
                            <h3 class="text-md font-semibold text-black dark:text-white">
                                <Link :href="`/articles/${article.url}`"
                                      class="hover:text-blue-600 dark:hover:text-blue-400 line-clamp-2">
                                    {{ article.title }}
                                </Link>
                            </h3>
                        </div>

                        <!-- Краткое описание -->
                        <div class="mb-3">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 line-clamp-3">
                                {{ article.short }}
                            </p>
                        </div>

                        <!-- Дата публикации -->
                        <div class="opacity-75 text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            {{ formatDate(article.published_at) }}
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- Пагинация -->
        <div class="flex items-center justify-center mt-6 space-x-2 text-sm font-medium">

            <!-- Кнопка назад -->
            <button @click="goToPage(pagination.currentPage - 1)"
                    :disabled="pagination.currentPage === 1"
                    class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                           border border-gray-400 dark:border-gray-200 disabled:opacity-50">
                «
            </button>

            <input type="number"
                   :value="pagination.currentPage"
                   @change="e => goToPage(Number(e.target.value))"
                   :min="1"
                   :max="pagination.lastPage"
                   class="w-16 text-center px-3 py-1.5 border border-gray-400 dark:border-gray-200 rounded
                       bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"/>

            <span>/ {{ pagination.lastPage }}</span>

            <!-- Кнопка вперёд -->
            <button @click="goToPage(pagination.currentPage + 1)"
                    :disabled="pagination.currentPage === pagination.lastPage"
                    class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                           border border-gray-400 dark:border-gray-200 disabled:opacity-50">
                »
            </button>

        </div>

    </div>
</template>
