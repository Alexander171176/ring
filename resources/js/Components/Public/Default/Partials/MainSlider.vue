<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { mainArticles } = usePage().props;
const articles = computed(() => mainArticles || []);
</script>

<template>
    <div class="mt-4">
        <!-- Контейнер слайдера: горизонтальная прокрутка с отступами между элементами -->
        <div class="flex overflow-x-auto space-x-4 p-2">
            <div
                v-for="article in articles"
                :key="article.id"
                class="min-w-[250px] flex-shrink-0 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-slate-300 dark:border-slate-400"
            >
                <!-- Изображение статьи -->
                <div v-if="article.images && article.images.length > 0" class="h-40 overflow-hidden">
                    <img
                        :src="article.images[0].url"
                        :alt="article.images[0].alt"
                        class="w-full h-full object-cover"
                    />
                </div>
                <div v-else class="h-40 flex items-center justify-center bg-gray-200 dark:bg-gray-400">
          <span class="text-gray-500 dark:text-gray-700">
            {{ t('noCurrentImage') }}
          </span>
                </div>
                <!-- Информация о статье -->
                <div class="p-3">
                    <!-- Дата создания (выведите в нужном формате, если формат уже задан в ресурсе) -->
                    <div class="text-xs font-semibold text-orange-500 dark:text-orange-400 mb-1">
                        {{ article.created_at }}
                    </div>
                    <!-- Заголовок статьи с ссылкой -->
                    <Link
                        :href="`/articles/${article.url}`"
                        class="font-semibold text-gray-900 dark:text-white hover:text-blue-700 dark:hover:text-blue-600"
                    >
                        {{ article.title }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* При необходимости можно добавить дополнительные стили для скроллбара */
</style>
