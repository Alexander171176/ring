<script setup>
import { defineProps, defineEmits } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import ActivityToggle from '@/Components/Admin/Buttons/ActivityToggle.vue';
import CloneIconButton from '@/Components/Admin/Buttons/CloneIconButton.vue';
import DeleteIconButton from '@/Components/Admin/Buttons/DeleteIconButton.vue';
import PrintInMenuToggle from '@/Components/Admin/Page/Checkbox/PrintInMenuToggle.vue';
import IconEdit from "@/Components/Admin/Buttons/IconEdit.vue"; // Добавим компонент для переключения показа в меню

const { t } = useI18n();

const props = defineProps({
    pages: Array,
    selectedPages: Array
});

// обработчики событий
const emits = defineEmits(['toggle-printInMenu', 'toggle-activity', 'edit', 'delete', 'recalculate-sort', 'clone', 'toggle-select']);

// пересчитывает сортировку
const recalculateSort = (event) => {
    emits('recalculate-sort', event);
};

// выбрать все
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    props.pages.forEach(page => {
        if (isChecked && !props.selectedPages.includes(page.id)) {
            emits('toggle-select', page.id);
        } else if (!isChecked && props.selectedPages.includes(page.id)) {
            emits('toggle-select', page.id);
        }
    });
};
</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="pages.length > 0" class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead
                    class="text-sm uppercase
                           bg-slate-200 dark:bg-cyan-900
                           border border-solid border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="font-medium text-center">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('title') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('url') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-center">{{ t('parentPage') }}</div>
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
                <draggable tag="tbody" :list="pages" @end="recalculateSort" itemKey="id">
                    <template #item="{ element: page }">
                        <tr class="text-sm font-semibold border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">{{ page.id }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left text-teal-600 dark:text-violet-200">
                                    {{ page.title }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left text-blue-600 dark:text-blue-200">{{ page.url }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">
                                    <span class="text-blue-600 dark:text-blue-200">
                                        {{ page.parent ? page.parent.title : page.parent_id }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <PrintInMenuToggle :isActive="page.print_in_menu"
                                                       @toggle-printInMenu="$emit('toggle-printInMenu', page)"
                                                       :title="page.print_in_menu ? t('enabled') : t('disabled')"/>
                                    <ActivityToggle :isActive="page.activity"
                                                    @toggle-activity="$emit('toggle-activity', page)"
                                                    :title="page.activity ? t('enabled') : t('disabled')"/>
                                    <CloneIconButton @clone="$emit('clone', page)"/>
                                    <IconEdit :href="route('pages.edit', page.id)" />
                                    <DeleteIconButton @delete="$emit('delete', page.id)"/>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">
                                    <input type="checkbox" :checked="selectedPages.includes(page.id)"
                                           @change="$emit('toggle-select', page.id)"/>
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
