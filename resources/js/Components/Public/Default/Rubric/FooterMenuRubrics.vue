<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const rubrics = ref([]);
const rubricColumns = ref([[], []]);

const currentLocale = computed(() => usePage().props.locale ?? 'ru');

const fetchRubrics = async () => {
    const localePath = currentLocale.value;
    const url = `/${localePath}/api/menu-rubrics`;

    try {
        const response = await fetch(url);
        if (!response.ok) {
            console.error(`[TopMenuRubrics] Ошибка загрузки: ${response.status}`);
            return;
        }

        const data = await response.json();
        const allRubrics = Array.isArray(data.rubrics) ? data.rubrics : [];

        const midpoint = Math.ceil(allRubrics.length / 2);
        rubricColumns.value = [allRubrics.slice(0, midpoint), allRubrics.slice(midpoint)];
    } catch (error) {
        console.error('[TopMenuRubrics] Ошибка сети:', error);
    }
};

onMounted(fetchRubrics);
watch(currentLocale, fetchRubrics);
</script>

<template>
    <template v-if="rubricColumns[0].length || rubricColumns[1].length">

        <div>
            <h3 class="text-slate-700 dark:text-slate-100 text-sm font-bold mb-4">
                {{ t('partitions') }}
            </h3>
            <ul class="space-y-1">
                <li v-for="rubric in rubricColumns[0]" :key="rubric.id">
                    <Link :href="`/rubrics/${rubric.url}`"
                          class="flex items-center h-7 text-sm font-semibold
                                 text-slate-700 hover:text-red-500
                                 dark:text-slate-300 dark:hover:text-yellow-300">
                        {{ rubric.title }}
                    </Link>
                </li>
            </ul>
        </div>

        <div>
            <div class="mb-4 h-5"></div>
            <ul class="space-y-1">
                <li v-for="rubric in rubricColumns[1]" :key="rubric.id">
                    <Link :href="`/rubrics/${rubric.url}`"
                          class="flex items-center h-7 text-sm font-semibold
                                 text-slate-700 hover:text-red-500
                                 dark:text-slate-300 dark:hover:text-yellow-300">
                        {{ rubric.title }}
                    </Link>
                </li>
                <!-- 🔗 Внешняя ссылка -->
                <li>
                    <a href="https://www.nextgensports.live/" target="_blank"
                       rel="noopener noreferrer"
                       class="flex items-center h-7 text-sm font-semibold
                              text-slate-700 hover:text-red-500
                              dark:text-slate-300 dark:hover:text-yellow-300">
                        NextGenSports
                    </a>
                </li>
            </ul>
        </div>
    </template>
</template>
