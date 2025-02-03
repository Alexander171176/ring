<script setup>
import {defineProps, defineEmits} from 'vue';
import ActivityToggle from '@/Components/Admin/Buttons/ActivityToggle.vue';
import EditIconButton from '@/Components/Admin/Buttons/EditIconButton.vue';
import DeleteIconButton from '@/Components/Admin/Buttons/DeleteIconButton.vue';
import {useI18n} from 'vue-i18n';
import IconEdit from "@/Components/Admin/Buttons/IconEdit.vue";

const {t} = useI18n();

const props = defineProps({
    settings: Array
});

const emits = defineEmits(['toggle-activity', 'edit', 'delete']);
</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="settings.length > 0" class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead
                    class="text-sm uppercase
                           bg-slate-200 dark:bg-cyan-900
                           border border-solid border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="font-medium text-center">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('parameter') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('value') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-center">{{ t('category') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('description') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-center">{{ t('actions') }}</div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="setting in settings" :key="setting.id"
                    class="text-sm font-semibold border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="text-center">{{ setting.id }}</div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="text-left text-sm font-semibold text-orange-400 dark:text-orange-200">
                            {{ setting.option }}
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="text-left text-sm font-semibold text-blue-600 dark:text-violet-200">
                            {{ setting.value }}
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="text-center py-0.5 px-2 badge bg-blue-500 rounded-sm text-xs text-slate-100">
                            {{ setting.category }}
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="text-left">{{ setting.description }}</div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="flex justify-center space-x-2">
                            <ActivityToggle :isActive="setting.activity"
                                            @toggle-activity="$emit('toggle-activity', setting)"
                                            :title="setting.activity ? t('enabled') : t('disabled')"/>
                            <IconEdit :href="route('parameters.edit', setting.id)" />
                            <DeleteIconButton @delete="$emit('delete', setting.id)"/>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <div v-else class="p-5 text-center text-slate-700 dark:text-slate-100">
                {{ t('noData') }}
            </div>
        </div>
    </div>
</template>
