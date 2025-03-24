<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
// Получаем данные из страницы, включая новый пропс sidebarArticles
const { rubric, sidebarArticles, } = usePage().props;

// Используем prop sidebarArticles вместо вычисления через секции
const articles = computed(() => sidebarArticles || []);

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
        isCollapsed.value ? 'lg:w-8' : 'lg:w-64'
    ].join(' ');
});
</script>

<template>
    <aside :class="sidebarClasses">
        <div class="flex items-center justify-between">
            <h2 v-if="!isCollapsed" class="text-xl font-semibold text-gray-900 dark:text-slate-100">
                {{ rubric.title }}
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
                    class="mb-2 pb-2 border-b border-dashed border-slate-500 dark:border-slate-300">
                    <img
                        v-if="article.images && article.images.length > 0"
                        :src="article.images[0].url"
                        :alt="article.images[0].alt"
                        class="w-full h-full object-cover"
                    />
                    <div class="font-semibold text-xs text-orange-500 dark:text-orange-400 ml-2">
                        {{ article.created_at }}
                    </div>
                    <Link :href="`/articles/${article.url}`"
                          class="font-semibold text-gray-900 dark:text-white
                                 hover:text-blue-700 dark:hover:text-blue-600">
                        {{ article.title }}
                    </Link>
                </li>
            </ul>
        </div>

    </aside>
</template>

<style scoped>
/* Дополнительные стили при необходимости */
</style>
