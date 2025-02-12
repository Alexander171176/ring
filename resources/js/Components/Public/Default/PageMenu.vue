<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const rubrics = ref([]);

// Функция для загрузки рубрик с API
const fetchRubrics = async () => {
    try {
        const response = await fetch('/api/menu-rubrics');

        if (!response.ok) {
            console.error(`Ошибка при загрузке рубрик: ${response.status}`);
            return;
        }

        const data = await response.json();

        if (data.rubrics && Array.isArray(data.rubrics)) {
            rubrics.value = data.rubrics;

            // Выводим локали всех загруженных рубрик
            // console.log('Загруженные рубрики и их локали:');
            // rubrics.value.forEach(rubric => {
            //     console.log(`ID: ${rubric.id}, Title: ${rubric.title}, Locale: ${rubric.locale}`);
            // });

        } else {
            console.error('Ожидался массив rubrics, но получено:', data);
        }
    } catch (error) {
        console.error('Ошибка при выполнении запроса:', error);
    }
};

onMounted(() => {
    fetchRubrics();
});
</script>

<template>
    <div class="flex flex-wrap justify-end space-x-4">
        <ul v-if="rubrics.length" class="flex flex-wrap gap-4">
            <li v-for="rubric in rubrics" :key="rubric.id">
                <a :href="`/rubrics/${rubric.url}`"
                   class="text-slate-100 hover:text-blue-400
                          text-md font-medium transition duration-300 ease-in-out">
                    {{ rubric.title }}
                </a>
            </li>
        </ul>
        <p v-else class="text-slate-100">{{ t('noData') }}</p>
    </div>
</template>
