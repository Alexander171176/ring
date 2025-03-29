<script setup>
import { defineProps, defineEmits } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import LeftToggle from "@/Components/Admin/Buttons/LeftToggle.vue";
import RightToggle from "@/Components/Admin/Buttons/RightToggle.vue";
import ActivityToggle from '@/Components/Admin/Buttons/ActivityToggle.vue';
import DeleteIconButton from '@/Components/Admin/Buttons/DeleteIconButton.vue';
import IconEdit from "@/Components/Admin/Buttons/IconEdit.vue";

const { t } = useI18n();

const props = defineProps({
    banners: Array,
    selectedBanners: Array
});

const emits = defineEmits([
    'toggle-left',
    'toggle-right',
    'toggle-activity',
    'edit',
    'delete',
    'recalculate-sort',
    'toggle-select'
]);

const recalculateSort = (event) => {
    emits('recalculate-sort', event);
};

const toggleAll = (event) => {
    const isChecked = event.target.checked;
    props.banners.forEach(banner => {
        if (isChecked && !props.selectedBanners.includes(banner.id)) {
            emits('toggle-select', banner.id);
        } else if (!isChecked && props.selectedBanners.includes(banner.id)) {
            emits('toggle-select', banner.id);
        }
    });
};

// Функция для выбора изображения с наименьшим значением order
const getPrimaryImage = (banner) => {
    if (banner.images && banner.images.length) {
        // Создаем копию массива и сортируем по возрастанию order
        return [...banner.images].sort((a, b) => a.order - b.order)[0];
    }
    return null;
};
</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="banners.length > 0" class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead
                    class="text-sm uppercase bg-slate-200 dark:bg-cyan-900 border border-solid border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="font-medium text-center">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="flex justify-center" :title="t('image')">
                            <svg class="w-6 h-6 fill-current shrink-0" viewBox="0 0 512 512">
                                <path d="M0 96C0 60.7 28.7 32 64 32l384 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96zM323.8 202.5c-4.5-6.6-11.9-10.5-19.8-10.5s-15.4 3.9-19.8 10.5l-87 127.6L170.7 297c-4.6-5.7-11.5-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6l96 0 32 0 208 0c8.9 0 17.1-4.9 21.2-12.8s3.6-17.4-1.4-24.7l-120-176zM112 192a48 48 0 1 0 0-96 48 48 0 1 0 0 96z"/>
                            </svg>
                        </div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('title') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-left">{{ t('sections') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-medium text-center">{{ t('show') }}</div>
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
                <draggable tag="tbody" :list="banners" @end="recalculateSort" itemKey="id">
                    <template #item="{ element: banner }">
                        <tr class="text-sm font-semibold border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center text-blue-600 dark:text-blue-200">{{ banner.id }}</div>
                            </td>
                            <td class="first:pl-5 last:pr-5 py-1">
                                <div class="flex justify-center">
                                    <template v-if="banner.images && banner.images.length">
                                        <img
                                            :src="getPrimaryImage(banner).webp_url || getPrimaryImage(banner).url"
                                            :alt="getPrimaryImage(banner).alt || t('defaultImageAlt')"
                                            :title="getPrimaryImage(banner).caption || t('postImage')"
                                            class="h-8 w-8 object-cover rounded-full"
                                        >
                                    </template>
                                    <template v-else>
                                        <img
                                            src="/storage/banner_images/default-image.png"
                                            :alt="t('defaultImageTitle')"
                                            class="h-8 w-8 object-cover rounded-full"
                                        >
                                    </template>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left text-teal-600 dark:text-violet-200">{{ banner.title }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-left">
                                    <span v-for="section in banner.sections" :key="section.id">
                                        <span :title="section.title"
                                              class="py-0.5 px-1.5 mr-0.5 badge bg-blue-500 dark:bg-blue-200
                                                     rounded-sm text-xs text-slate-100 dark:text-slate-900">
                                            {{ section.id }}
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <LeftToggle :isActive="banner.left"
                                                @toggle-left="$emit('toggle-left', banner)"
                                                :title="banner.left ? t('enabled') : t('disabled')"/>
                                    <RightToggle :isActive="banner.right"
                                                 @toggle-right="$emit('toggle-right', banner)"
                                                 :title="banner.right ? t('enabled') : t('disabled')"/>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <ActivityToggle :isActive="banner.activity"
                                                    @toggle-activity="$emit('toggle-activity', banner)"
                                                    :title="banner.activity ? t('enabled') : t('disabled')"/>
                                    <IconEdit :href="route('banners.edit', banner.id)" />
                                    <DeleteIconButton @delete="$emit('delete', banner.id)"/>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                <div class="text-center">
                                    <input type="checkbox" :checked="selectedBanners.includes(banner.id)"
                                           @change="$emit('toggle-select', banner.id)"/>
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
