<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ArticleImageSlider from "@/Components/Public/Default/Article/ArticleImageSlider.vue";

const { t } = useI18n();
// Получаем данные из страницы, включая новый пропс rightArticles
const { rightArticles, } = usePage().props;

// Используем prop rightArticles вместо вычисления через секции
const articles = computed(() => rightArticles || []);

const isCollapsed = ref(false);
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
};

const sidebarClasses = computed(() => {
    return [
        'transition-all',
        'duration-300',
        'p-2',
        'bg-slate-100',
        'dark:bg-slate-800',
        'w-full', // на маленьких экранах всегда full width
        isCollapsed.value ? 'lg:w-8' : 'lg:w-80'
    ].join(' ');
});
</script>

<template>
    <aside v-if="articles.length > 0" :class="sidebarClasses">
        <div class="flex items-center justify-between">
            <h2 v-if="!isCollapsed"
                class="w-full text-center text-xl font-semibold text-gray-900 dark:text-slate-100">
                {{ t('latestNews') }}
            </h2>
            <button @click="toggleSidebar" class="focus:outline-none" :title="t('toggleSidebar')">
                <svg v-if="isCollapsed"
                     class="w-6 h-6 text-rose-500 dark:text-rose-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 5v14l-11-7z" />
                </svg>
                <svg v-else
                     class="w-6 h-6 text-rose-500 dark:text-rose-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z" />
                </svg>
            </button>
        </div>

        <!-- Содержимое сайдбара показывается, когда он развернут -->
        <div v-show="!isCollapsed" class="mt-4">
            <ul>
                <li v-for="article in articles" :key="article.id"
                    class="mb-2 pb-2 border-b border-slate-500 dark:border-slate-300">

                    <!-- Изображение статьи -->
                    <Link v-if="article.images && article.images.length > 0"
                          :href="`/articles/${article.url}`"
                          class="h-40 overflow-hidden">
                        <ArticleImageSlider
                            :images="article.images"
                            :link="`/articles/${article.url}`"
                            :alt="article.images[0].alt || t('noCurrentImage')"
                            :title="article.images[0].caption || t('postImage')"
                        />
                    </Link>
                    <Link v-else :href="`/articles/${article.url}`"
                          class="h-40 flex items-center justify-center bg-gray-200 dark:bg-gray-400">
                        <span class="text-gray-500 dark:text-gray-700">{{ t('noCurrentImage') }}</span>
                    </Link>

                    <!-- Ссылка и дата статьи -->
                    <div class="px-3 my-1">
                        <div class="text-xs font-semibold text-orange-500 dark:text-orange-400 mb-1">
                            {{ article.created_at.substring(0, 10) }}
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <Link :href="`/articles/${article.url}`"
                                  class="hover:text-blue-600 dark:hover:text-blue-400">
                                {{ article.title }}
                            </Link>
                        </h3>
                    </div>

                    <!-- Краткое описание статьи -->
                    <div class="flex flex-wrap items-center pl-1
                                border border-dashed border-slate-400 dark:border-slate-200">
                        <p class="italic text-sm font-semibold text-slate-600 dark:text-slate-300">
                            {{ article.short }}
                        </p>
                    </div>

                </li>
            </ul>
        </div>

    </aside>
</template>

<style scoped>
/* Дополнительные стили при необходимости */
</style>
