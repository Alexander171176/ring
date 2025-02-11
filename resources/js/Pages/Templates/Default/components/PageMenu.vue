<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const rubrics = ref([]);

// Функция для загрузки рубрик с API
const fetchRubrics = async () => {
    console.log('Запуск загрузки рубрик...');
    try {
        const response = await fetch('/api/rubrics');
        console.log('Статус ответа:', response.status);

        if (!response.ok) {
            console.error(`Ошибка при загрузке рубрик: ${response.status}`);
            return;  // Прекращаем выполнение функции
        }

        const data = await response.json();
        console.log('Полученные данные:', data);

        if (data.rubrics && Array.isArray(data.rubrics)) {
            rubrics.value = data.rubrics;
            console.log('Рубрики успешно загружены в компонент:', rubrics.value);
        } else {
            console.error('Ожидался массив rubrics, но получено:', data);
        }
    } catch (error) {
        console.error('Ошибка при выполнении запроса:', error);
    }
};

onMounted(() => {
    console.log('Компонент PageMenu смонтирован');
    fetchRubrics();
});
</script>

<template>
    <nav class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg">
        <ul v-if="rubrics.length" class="flex flex-col space-y-2">
            <li v-for="rubric in rubrics" :key="rubric.id">
                <a
                    :href="`/rubrics/${rubric.url}`"
                    class="text-blue-600 dark:text-blue-300 hover:underline text-lg font-medium"
                >
                    {{ rubric.title }}
                </a>
            </li>
        </ul>
        <p v-else class="text-gray-500 dark:text-gray-400">{{ t('noData') }}</p>
    </nav>
</template>

<style scoped>
nav {
    max-width: 300px;
}
</style>
