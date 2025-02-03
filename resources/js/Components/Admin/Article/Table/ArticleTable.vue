<script setup>
import { defineProps, defineEmits } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import ActivityToggle from '@/Components/Admin/Buttons/ActivityToggle.vue';
import CloneIconButton from '@/Components/Admin/Buttons/CloneIconButton.vue';
import DeleteIconButton from '@/Components/Admin/Buttons/DeleteIconButton.vue';
import IconEdit from "@/Components/Admin/Buttons/IconEdit.vue";

const { t } = useI18n();

const props = defineProps({
    articles: Array,
    selectedArticles: Array
});

const emits = defineEmits(['toggle-activity', 'edit', 'delete', 'recalculate-sort', 'clone', 'toggle-select']);

const imagePath = (path) => {
    if (!path) {
        return '/storage/article_images/default-image.png';
    }
    if (path.startsWith('http') || path.startsWith('https')) {
        return path;
    }
    return `/storage/${path}`;
};

const recalculateSort = (event) => {
    emits('recalculate-sort', event);
};

const toggleAll = (event) => {
    const isChecked = event.target.checked;
    props.articles.forEach(article => {
        if (isChecked && !props.selectedArticles.includes(article.id)) {
            emits('toggle-select', article.id);
        } else if (!isChecked && props.selectedArticles.includes(article.id)) {
            emits('toggle-select', article.id);
        }
    });
};
</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="articles.length > 0" class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead
                    class="text-sm uppercase bg-slate-200 dark:bg-cyan-900 border border-solid border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="font-medium text-center">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('image') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('title') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('rubrics') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-center">{{ t('views') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-center">{{ t('likes') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-center">{{ t('actions') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-center">
                            <input type="checkbox" @change="toggleAll"/>
                        </div>
                    </th>
                </tr>
                </thead>
                <draggable tag="tbody" :list="articles" @end="recalculateSort" itemKey="id">
                    <template #item="{ element: article }">
                        <tr class="text-sm font-semibold border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">{{ article.id }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left">
                                    <img :src="imagePath(article.image_url)"
                                         :alt="article.seo_alt || t('defaultImageAlt')"
                                         :title="article.seo_title || t('defaultImageTitle')"
                                         class="h-8 w-8 object-cover rounded-full">
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left text-teal-600 dark:text-violet-200">{{ article.title }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left">
                                    <span v-for="rubric in article.rubrics" :key="rubric.id"
                                          class="py-0.5 px-2 badge bg-blue-500 rounded-sm text-xs text-slate-100">
                                        {{ rubric.title }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">{{ article.views }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">{{ article.likes }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <ActivityToggle :isActive="article.activity"
                                                    @toggle-activity="$emit('toggle-activity', article)"
                                                    :title="article.activity ? t('enabled') : t('disabled')"/>
                                    <CloneIconButton @clone="$emit('clone', article)"/>
                                    <IconEdit :href="route('articles.edit', article.id)" />
                                    <DeleteIconButton @delete="$emit('delete', article.id)"/>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">
                                    <input type="checkbox" :checked="selectedArticles.includes(article.id)"
                                           @change="$emit('toggle-select', article.id)"/>
                                </div>
                            </td>
                        </tr>
                    </template>
                </draggable>
            </table>
            <div v-else class="p-5 text-center text-slate-700 dark:text-slate-100">
                {{ t('noData') }}
            </div>
        </div>
    </div>
</template>
