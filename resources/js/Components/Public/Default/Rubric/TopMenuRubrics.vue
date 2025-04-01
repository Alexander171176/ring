<script setup>
import {ref, onMounted, computed} from "vue";
import {usePage, Link} from "@inertiajs/vue3";
import {useI18n} from "vue-i18n";

const {t} = useI18n();
const rubrics = ref([]);

// Получаем текущий маршрут
const currentRoute = computed(() => usePage().url);

// Функция для загрузки рубрик с API
const fetchRubrics = async () => {
    try {
        const response = await fetch("/api/menu-rubrics");

        if (!response.ok) {
            console.error(`Ошибка при загрузке рубрик: ${response.status}`);
            return;
        }

        const data = await response.json();

        if (data.rubrics && Array.isArray(data.rubrics)) {
            rubrics.value = data.rubrics;
        } else {
            console.error("Ожидался массив rubrics, но получено:", data);
        }
    } catch (error) {
        console.error("Ошибка при выполнении запроса:", error);
    }
};

onMounted(() => {
    fetchRubrics();
});
</script>

<template>
    <nav class="flex flex-wrap justify-center gap-4 p-2 dark:bg-cyan-800 bg-sky-800">
        <ul v-if="rubrics.length" class="flex flex-wrap gap-2">
            <li v-for="rubric in rubrics" :key="rubric.id">
                <Link
                    :href="`/rubrics/${rubric.url}`"
                    class="px-3 py-1 rounded-sm text-sm font-medium transition duration-300
                           text-white hover:bg-cyan-800 dark:hover:bg-blue-800"
                    :class="{
                        'bg-orange-500 dark:bg-orange-400 text-white': currentRoute.includes(`/rubrics/${rubric.url}`)
                    }"
                >
                    {{ rubric.title }}
                </Link>
            </li>
        </ul>
        <p v-else class="text-slate-100">{{ t("dataUploaded") }}</p>
    </nav>
</template>
