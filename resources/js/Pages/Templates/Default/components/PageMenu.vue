<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n(); // Импортируем и используем функцию t для переводов

const props = defineProps({
    isOpen: Boolean
});

const pages = ref([]);

async function fetchPages() {
    try {
        // console.log('Выборка страниц...');
        const response = await fetch('/api/pages');
        // if (!response.ok) {
        //     console.error(`[Ошибка выборки] Ошибка HTTP! статус: ${response.status}`);
        //     return; // Прерываем выполнение, если ответ не успешен
        // }
        const data = await response.json();
        // console.log('Выбранные страницы:', data);
        pages.value = data; // Если ваши данные в корне ответа
    } catch (error) {
        // console.error(`[Ошибка выборки] Ошибка при выборке страниц: ${error.message}`);
    }
}

onMounted(() => {
    fetchPages();
});
</script>

<template>
    <nav class="page-menu p-2 w-full">
        <!-- Меню для больших экранов -->
        <ul class="hidden md:flex md:space-x-4 list-none p-0">
            <li v-for="page in pages" :key="page.id" class="text-gray-700 dark:text-gray-300">
                <Link :href="`/${page.slug}`"
                      class="font-semibold
                               text-gray-600
                               2xl:text-lg text-sm
                               px-3 py-1
                               hover:text-red-600
                               dark:text-gray-200
                               dark:hover:text-red-300
                               focus:outline
                               focus:outline-2
                               focus:rounded-sm
                               focus:outline-red-500
                               transition-colors duration-200">
                    {{ t(page.title) }}
                </Link>
            </li>
        </ul>
        <!-- Меню для маленьких экранов -->
        <ul :class="{'hidden': !isOpen, 'flex': isOpen}" class="flex-col md:hidden space-y-2 list-none p-0 pb-24 overflow-y-auto max-h-screen">
            <li v-for="page in pages" :key="page.id" class="text-gray-700 dark:text-gray-300">
                <Link :href="`/${page.slug}`"
                      class="font-semibold
                               text-gray-600
                               text-md
                               px-2 py-0
                               hover:text-red-600
                               dark:text-gray-200
                               dark:hover:text-red-300
                               focus:outline
                               focus:outline-2
                               focus:rounded-sm
                               focus:outline-red-500
                               transition-colors duration-200">
                    {{ t(page.title) }}
                </Link>
            </li>
        </ul>
    </nav>
</template>

<style scoped></style>
