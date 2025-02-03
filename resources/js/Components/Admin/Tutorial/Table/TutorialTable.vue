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
    tutorials: Array,
    selectedTutorials: Array
});

const emits = defineEmits(['toggle-activity', 'edit', 'delete', 'recalculate-sort', 'clone', 'toggle-select']);

const imagePath = (path) => {
    if (!path) {
        return '/storage/tutorial_images/default-image.png';
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
    props.tutorials.forEach(tutorial => {
        if (isChecked && !props.selectedTutorials.includes(tutorial.id)) {
            emits('toggle-select', tutorial.id);
        } else if (!isChecked && props.selectedTutorials.includes(tutorial.id)) {
            emits('toggle-select', tutorial.id);
        }
    });
};
</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="tutorials.length > 0" class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead class="text-sm uppercase
                              bg-slate-200 dark:bg-cyan-900
                              border border-solid border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="font-medium text-center">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('icon') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('image') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('title') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-center">{{ t('views') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-center">{{ t('actions') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-center">
                            <input type="checkbox" @change="toggleAll" />
                        </div>
                    </th>
                </tr>
                </thead>
                <draggable tag="tbody" :list="tutorials" @end="recalculateSort" itemKey="id">
                    <template #item="{ element: tutorial }">
                        <tr class="text-sm font-semibold border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">{{ tutorial.id }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center" v-html="tutorial.icon" />
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left">
                                    <img :src="imagePath(tutorial.image_url)"
                                         :alt="tutorial.seo_alt || t('defaultImageAlt')"
                                         :title="tutorial.seo_title || t('defaultImageTitle')"
                                         class="h-8 w-8 object-cover rounded-full">
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left text-teal-600 dark:text-violet-200">{{ tutorial.title }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">{{ tutorial.views }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <ActivityToggle :isActive="tutorial.activity"
                                                    @toggle-activity="$emit('toggle-activity', tutorial)"
                                                    :title="tutorial.activity ? t('enabled') : t('disabled')" />
                                    <CloneIconButton @clone="$emit('clone', tutorial)" />
                                    <IconEdit :href="route('tutorials.edit', tutorial.id)" />
                                    <DeleteIconButton @delete="$emit('delete', tutorial.id)" />
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">
                                    <input type="checkbox" :checked="selectedTutorials.includes(tutorial.id)" @change="$emit('toggle-select', tutorial.id)" />
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
