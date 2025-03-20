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
                <thead class="text-sm uppercase
                              bg-slate-200 dark:bg-cyan-900
                              border border-solid border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 first:pl-7 last:pr-7 py-3 whitespace-nowrap w-px">
                        <div class="text-center font-medium">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 first:pl-7 last:pr-7 py-3 whitespace-nowrap">
                        <div class="text-center font-medium">{{ t('icon') }}</div>
                    </th>
                    <th class="px-2 first:pl-7 last:pr-7 py-3 whitespace-nowrap">
                        <div class="text-center font-medium">{{ t('localization') }}</div>
                    </th>
                    <th class="px-2 first:pl-7 last:pr-7 py-3 whitespace-nowrap">
                        <div class="text-left font-medium">{{ t('title') }}</div>
                    </th>
                    <th class="px-2 first:pl-7 last:pr-7 py-3 whitespace-nowrap">
                        <div class="text-center font-medium">{{ t('actions') }}</div>
                    </th>
                    <th class="px-2 first:pl-7 last:pr-7 py-3 whitespace-nowrap">
                        <div class="text-center font-medium">
                            <input type="checkbox" @change="toggleAll" />
                        </div>
                    </th>
                </tr>
                </thead>
                <draggable tag="tbody" :list="sections" @end="recalculateSort" itemKey="id">
                    <template #item="{ element: section }">
                        <tr class="text-sm font-semibold border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                            <td class="px-2 first:pl-7 last:pr-7 py-1 whitespace-nowrap">
                                <div class="text-center text-blue-600 dark:text-blue-200">{{ section.id }}</div>
                            </td>
                            <td class="px-2 first:pl-7 last:pr-7 py-1 whitespace-nowrap">
                                <div class="flex justify-center" v-html="section.icon" />
                            </td>
                            <td class="px-2 first:pl-7 last:pr-7 py-1 whitespace-nowrap">
                                <div class="text-center uppercase text-orange-500 dark:text-orange-200">
                                    {{ section.locale }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-7 last:pr-7 py-1 whitespace-nowrap">
                                <div class="text-left text-yellow-500 dark:text-yellow-200">{{ section.title }}</div>
                            </td>
                            <td class="px-2 first:pl-7 last:pr-7 py-1 whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <ActivityToggle :isActive="section.activity"
                                                    @toggle-activity="$emit('toggle-activity', section)"
                                                    :title="section.activity ? t('enabled') : t('disabled')" />
                                    <CloneIconButton @clone="$emit('clone', section)" />
                                    <IconEdit :href="route('sections.edit', section.id)" />
                                    <DeleteIconButton @delete="$emit('delete', section.id)" />
                                </div>
                            </td>
                            <td class="px-2 first:pl-7 last:pr-7 py-1 whitespace-nowrap">
                                <div class="text-center">
                                    <input type="checkbox" :checked="selectedSections.includes(section.id)" @change="$emit('toggle-select', section.id)" />
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
