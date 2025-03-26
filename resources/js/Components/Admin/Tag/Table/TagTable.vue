<script setup>
import {defineProps, defineEmits} from 'vue';
import IconEdit from "@/Components/Admin/Buttons/IconEdit.vue";
import DeleteIconButton from '@/Components/Admin/Buttons/DeleteIconButton.vue';
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

const props = defineProps({
    tags: Array,
    selectedTags: Array
});

const emits = defineEmits(['edit', 'delete', 'toggle-select']);

const toggleAll = (event) => {
    const isChecked = event.target.checked;
    props.tags.forEach(tag => {
        if (isChecked && !props.selectedTags.includes(tag.id)) {
            emits('toggle-select', tag.id);
        } else if (!isChecked && props.selectedTags.includes(tag.id)) {
            emits('toggle-select', tag.id);
        }
    });
};
</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="tags.length > 0" class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead class="text-sm font-semibold uppercase
                                bg-slate-200 dark:bg-cyan-900
                                border border-solid
                                border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="font-semibold text-center">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="flex justify-center" :title="t('localization')">
                            <svg class="w-8 h-8 fill-current shrink-0" viewBox="0 0 640 512">
                                <path d="M0 128C0 92.7 28.7 64 64 64l192 0 48 0 16 0 256 0c35.3 0 64 28.7 64 64l0 256c0 35.3-28.7 64-64 64l-256 0-16 0-48 0L64 448c-35.3 0-64-28.7-64-64L0 128zm320 0l0 256 256 0 0-256-256 0zM178.3 175.9c-3.2-7.2-10.4-11.9-18.3-11.9s-15.1 4.7-18.3 11.9l-64 144c-4.5 10.1 .1 21.9 10.2 26.4s21.9-.1 26.4-10.2l8.9-20.1 73.6 0 8.9 20.1c4.5 10.1 16.3 14.6 26.4 10.2s14.6-16.3 10.2-26.4l-64-144zM160 233.2L179 276l-38 0 19-42.8zM448 164c11 0 20 9 20 20l0 4 44 0 16 0c11 0 20 9 20 20s-9 20-20 20l-2 0-1.6 4.5c-8.9 24.4-22.4 46.6-39.6 65.4c.9 .6 1.8 1.1 2.7 1.6l18.9 11.3c9.5 5.7 12.5 18 6.9 27.4s-18 12.5-27.4 6.9l-18.9-11.3c-4.5-2.7-8.8-5.5-13.1-8.5c-10.6 7.5-21.9 14-34 19.4l-3.6 1.6c-10.1 4.5-21.9-.1-26.4-10.2s.1-21.9 10.2-26.4l3.6-1.6c6.4-2.9 12.6-6.1 18.5-9.8l-12.2-12.2c-7.8-7.8-7.8-20.5 0-28.3s20.5-7.8 28.3 0l14.6 14.6 .5 .5c12.4-13.1 22.5-28.3 29.8-45L448 228l-72 0c-11 0-20-9-20-20s9-20 20-20l52 0 0-4c0-11 9-20 20-20z"/>
                            </svg>
                        </div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ t('name') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ t('url') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-end">{{ t('actions') }}</div>
                    </th>
                    <th class="px-2 first:pl-7 last:pr-7 py-3 whitespace-nowrap">
                        <div class="text-center font-medium">
                            <input type="checkbox" @change="toggleAll" />
                        </div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="tag in tags" :key="tag.id"
                    class="text-sm font-semibold
                            border-b-2
                            hover:bg-slate-100 dark:hover:bg-cyan-800">
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="text-center">{{ tag.id }}</div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="text-center uppercase text-orange-500 dark:text-orange-200">
                            {{ tag.locale }}
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="text-left text-teal-600 dark:text-violet-200">{{ tag.name }}</div>
                    </td>
                    <td class="px-2 first:pl-7 last:pr-7 py-1 whitespace-nowrap">
                        <div class="text-left text-violet-600 dark:text-violet-200">{{ tag.slug }}</div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <IconEdit :href="route('tags.edit', tag.id)" />
                            <DeleteIconButton @click="$emit('delete', tag.id)"/>
                        </div>
                    </td>
                    <td class="px-2 first:pl-7 last:pr-7 py-1 whitespace-nowrap">
                        <div class="text-center">
                            <input type="checkbox"
                                   :checked="selectedTags.includes(tag.id)"
                                   @change="$emit('toggle-select', tag.id)" />
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
