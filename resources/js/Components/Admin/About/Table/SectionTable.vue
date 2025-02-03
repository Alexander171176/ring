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
    sections: Array,
    selectedSections: Array
});

const emits = defineEmits(['toggle-activity', 'edit', 'delete', 'recalculate-sort', 'clone', 'toggle-select']);

const recalculateSort = (event) => {
    emits('recalculate-sort', event);
};

const toggleAll = (event) => {
    const isChecked = event.target.checked;
    props.sections.forEach(section => {
        if (isChecked && !props.selectedSections.includes(section.id)) {
            emits('toggle-select', section.id);
        } else if (!isChecked && props.selectedSections.includes(section.id)) {
            emits('toggle-select', section.id);
        }
    });
};
</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="sections.length > 0" class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead
                    class="text-md font-semibold uppercase bg-slate-200 dark:bg-cyan-900 border border-solid border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="font-semibold text-center">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ t('title') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-center">{{ t('actions') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-center">
                            <input type="checkbox" @change="toggleAll"/>
                        </div>
                    </th>
                </tr>
                </thead>
                <draggable tag="tbody" :list="sections" @end="recalculateSort" itemKey="id">
                    <template #item="{ element: section }">
                        <tr class="border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center text-md font-semibold">
                                    {{ section.id }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left text-md font-semibold text-blue-600 dark:text-violet-200">
                                    {{ section.title }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <ActivityToggle :isActive="section.activity"
                                                    @toggle-activity="$emit('toggle-activity', section)"
                                                    :title="section.activity ? t('enabled') : t('disabled')"/>
                                    <CloneIconButton @clone="$emit('clone', section)"/>
                                    <IconEdit :href="route('abouts.edit', section.id)"/>
                                    <DeleteIconButton @delete="$emit('delete', section.id)"/>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">
                                    <input type="checkbox" :checked="selectedSections.includes(section.id)"
                                           @change="$emit('toggle-select', section.id)"/>
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
