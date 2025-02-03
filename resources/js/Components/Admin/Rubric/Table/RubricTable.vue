<script setup>
import { defineProps, defineEmits } from 'vue';
import draggable from 'vuedraggable';
import ActivityToggle from '@/Components/Admin/Buttons/ActivityToggle.vue';
import IconEdit from "@/Components/Admin/Buttons/IconEdit.vue";
import CloneIconButton from '@/Components/Admin/Buttons/CloneIconButton.vue';
import DeleteIconButton from '@/Components/Admin/Buttons/DeleteIconButton.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    rubrics: Array,
    selectedRubrics: Array
});

const emits = defineEmits(['toggle-activity', 'edit', 'delete', 'recalculate-sort', 'clone', 'toggle-select']);

const imagePath = (path) => {
    if (!path) {
        return '/storage/rubric_images/default-image.png';
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
    props.rubrics.forEach(rubric => {
        if (isChecked && !props.selectedRubrics.includes(rubric.id)) {
            emits('toggle-select', rubric.id);
        } else if (!isChecked && props.selectedRubrics.includes(rubric.id)) {
            emits('toggle-select', rubric.id);
        }
    });
};
</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="rubrics.length > 0" class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead class="text-sm uppercase
                              bg-slate-200 dark:bg-cyan-900
                              border border-solid border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="text-center font-medium">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-left font-medium">{{ t('icon') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-left font-medium">{{ t('image') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-left font-medium">{{ t('title') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-left font-medium">{{ t('url') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-center font-medium">{{ t('views') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-center font-medium">{{ t('actions') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-center font-medium">
                            <input type="checkbox" @change="toggleAll" />
                        </div>
                    </th>
                </tr>
                </thead>
                <draggable tag="tbody" :list="rubrics" @end="recalculateSort" itemKey="id">
                    <template #item="{ element: rubric }">
                        <tr class="text-sm font-semibold border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">{{ rubric.id }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center" v-html="rubric.icon" />
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left">
                                    <img :src="imagePath(rubric.image_url)"
                                         :alt="rubric.seo_alt || t('defaultImageAlt')"
                                         :title="rubric.seo_title || t('defaultImageTitle')"
                                         class="h-8 w-8 object-cover rounded-full">
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left text-teal-600 dark:text-violet-200">{{ rubric.title }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left text-blue-600 dark:text-blue-200">{{ rubric.url }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">{{ rubric.views }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <ActivityToggle :isActive="rubric.activity"
                                                    @toggle-activity="$emit('toggle-activity', rubric)"
                                                    :title="rubric.activity ? t('enabled') : t('disabled')" />
                                    <CloneIconButton @clone="$emit('clone', rubric)" />
                                    <IconEdit :href="route('rubrics.edit', rubric.id)" />
                                    <DeleteIconButton @delete="$emit('delete', rubric.id)" />
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">
                                    <input type="checkbox" :checked="selectedRubrics.includes(rubric.id)" @change="$emit('toggle-select', rubric.id)" />
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
