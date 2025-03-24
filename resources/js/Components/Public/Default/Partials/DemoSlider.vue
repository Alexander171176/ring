<template>
    <div class="slider relative overflow-hidden w-full h-80">
        <!-- Контейнер слайдов -->
        <div
            class="slides flex transition-transform duration-500 ease-in-out h-full"
            :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
        >
            <div
                v-for="article in articles"
                :key="article.id"
                class="slide flex-shrink-0 w-full h-full"
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
                    <div class="text-xs font-semibold text-orange-500 dark:text-orange-400 mb-1">
                        {{ article.created_at.substring(0, 10) }}
                    </div>
                    <Link
                        :href="`/articles/${article.url}`"
                        class="font-semibold text-gray-900 dark:text-white hover:text-blue-700 dark:hover:text-blue-600"
                    >
                        {{ article.title }}
                    </Link>
                </div>
            </div>
        </div>
        <!-- Кнопки навигации -->
        <button
            @click="prev"
            class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-700 bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-full focus:outline-none"
            title="Previous"
        >
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.293 16.293a1 1 0 010-1.414L15.586 12H4a1 1 0 110-2h11.586l-3.293-3.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
        </button>
        <button
            @click="next"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-700 bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-full focus:outline-none"
            title="Next"
        >
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.707 3.707a1 1 0 010 1.414L4.414 8H16a1 1 0 110 2H4.414l3.293 3.293a1 1 0 01-1.414 1.414l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Получаем mainArticles из пропсов страницы
const { mainArticles } = usePage().props;
const articles = computed(() => mainArticles || []);

// Индекс текущего слайда
const currentSlide = ref(0);

// Функция перехода к следующему слайду
const next = () => {
    if (articles.value.length > 0) {
        currentSlide.value = (currentSlide.value + 1) % articles.value.length;
    }
};

// Функция перехода к предыдущему слайду
const prev = () => {
    if (articles.value.length > 0) {
        currentSlide.value = (currentSlide.value - 1 + articles.value.length) % articles.value.length;
    }
};

// Автопрокрутка слайдера каждые 3 секунды
let slideInterval = null;
onMounted(() => {
    slideInterval = setInterval(next, 3000);
});
onUnmounted(() => {
    clearInterval(slideInterval);
});
</script>

<style scoped>
.slider {
    /* Можно настроить дополнительные стили, если нужно */
}
</style>
