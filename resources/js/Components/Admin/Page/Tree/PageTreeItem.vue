<script setup>
import {defineProps, defineEmits, ref, watch} from 'vue';
import draggable from 'vuedraggable';
import ActivityToggle from '@/Components/Admin/Buttons/ActivityToggle.vue';
import IconEdit from "@/Components/Admin/Buttons/IconEdit.vue";
import DeleteIconButton from '@/Components/Admin/Buttons/DeleteIconButton.vue';
import {Link} from "@inertiajs/vue3";
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

const props = defineProps({
    page: Object,
    level: Number,
    selectedPages: Array,
});

const emits = defineEmits([
    'toggle-activity',
    'delete',
    'toggle-select',
    'request-drag-end'
]);

const isExpanded = ref(true);

// Логируем входящие props
// console.log('[PageTreeItem] page:', props.page);
// console.log('[PageTreeItem] level:', props.level);
// console.log('[PageTreeItem] selectedPages:', props.selectedPages);

// Слежение за изменениями props.page
watch(() => props.page, (newVal, oldVal) => {
    // console.log('[PageTreeItem] props.page изменился:', newVal);
}, {deep: true});

// Слежение за selectedPages
watch(() => props.selectedPages, (newVal, oldVal) => {
    // console.log('[PageTreeItem] selectedPages изменился:', newVal);
}, {deep: true});

const handleInnerDragEnd = (event) => {
    // console.log('[PageTreeItem] handleInnerDragEnd event:', event);
    emits('request-drag-end', event);
};

const toggleExpand = () => {
    isExpanded.value = !isExpanded.value;
    // console.log('[PageTreeItem] isExpanded:', isExpanded.value);
};
</script>

<template>
    <div>
        <!-- Элемент страницы -->
        <div class="page-item mb-1" :style="{ marginLeft: level * 20 + 'px' }">

            <div class="flex items-center justify-between py-1 px-3
                        border border-gray-400 rounded-sm
                        bg-white dark:bg-slate-600
                        hover:bg-slate-50 dark:hover:bg-slate-700
                        transition duration-150 ease-in-out">

                <div class="flex items-center space-x-2 flex-grow min-w-0">

                    <span class="handle cursor-move mr-1 flex-shrink-0"
                          :title="t('dragDrop')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                             class="w-4 h-4">
                            <path class="fill-current text-sky-500 dark:text-sky-200"
                                  d="M278.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-64 64c-9.2 9.2-11.9 22.9-6.9 34.9s16.6 19.8 29.6 19.8l32 0 0 96-96 0 0-32c0-12.9-7.8-24.6-19.8-29.6s-25.7-2.2-34.9 6.9l-64 64c-12.5 12.5-12.5 32.8 0 45.3l64 64c9.2 9.2 22.9 11.9 34.9 6.9s19.8-16.6 19.8-29.6l0-32 96 0 0 96-32 0c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l64 64c12.5 12.5 32.8 12.5 45.3 0l64-64c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8l-32 0 0-96 96 0 0 32c0 12.9 7.8 24.6 19.8 29.6s25.7 2.2 34.9-6.9l64-64c12.5-12.5 12.5-32.8 0-45.3l-64-64c-9.2-9.2-22.9-11.9-34.9-6.9s-19.8 16.6-19.8 29.6l0 32-96 0 0-96 32 0c12.9 0 24.6-7.8 29.6-19.8s2.2-25.7-6.9-34.9l-64-64z"/>
                        </svg>
                    </span>

                    <button v-if="page.children && page.children.length"
                            :title="isExpanded ? t('collapse') : t('expand')"
                            @click="toggleExpand"
                            class="flex-shrink-0 text-slate-900 hover:text-red-500
                                   dark:text-slate-100 dark:hover:text-red-200">

                        <svg class="w-5 h-5 transform transition-transform duration-150"
                             :class="{ 'rotate-90': isExpanded }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5l7 7-7 7"></path>
                        </svg>

                    </button>

                    <span v-else class="w-4 h-4 inline-block flex-shrink-0"></span>

                    <input type="checkbox" :checked="selectedPages.includes(page.id)"
                           @change="$emit('toggle-select', page.id)"
                           class="form-checkbox rounded-sm text-indigo-500 flex-shrink-0"/>

                    <span class="font-semibold text-sm text-amber-600 dark:text-amber-200 mr-1 flex-shrink-0">
                        {{ page.id }}
                    </span>

                    <Link :href="route('admin.pages.edit', page.id)"
                          class="font-medium text-teal-600 dark:text-teal-100
                                 hover:text-indigo-600 dark:hover:text-indigo-300 truncate"
                          :title="page.url">
                        {{ page.title }}
                    </Link>

                    <span class="text-xs ml-1 px-1.5 py-0.5 rounded-sm border border-slate-400 flex-shrink-0"
                          :class="page.activity
                          ? 'bg-amber-100 dark:bg-amber-700/50 text-amber-700 dark:text-amber-300'
                          : 'bg-blue-200 dark:bg-blue-900/50 text-gray-900 dark:text-gray-100'">
                        {{ page.locale.toUpperCase() }}
                    </span>
                </div>

                <!-- Правая часть -->
                <div class="flex items-center space-x-1 flex-shrink-0 ml-4">
                    <ActivityToggle :isActive="page.activity"
                        @toggle-activity="$emit('toggle-activity', page)"
                        :title="page.activity ? t('enabled') : t('disabled')"/>
                    <IconEdit :href="route('admin.pages.edit', page.id)"/>
                    <DeleteIconButton @click.stop="$emit('delete', page)"/>
                </div>
            </div>
        </div>

        <!-- Дочерние элементы -->
        <div v-show="isExpanded && page.children && page.children.length"
             class="children-container mt-1">

            <draggable v-model="page.children"
                       tag="div"
                       item-key="id"
                       handle=".handle"
                       group="pages"
                       @end="handleInnerDragEnd"
                       class="page-tree-children"
                       :data-parent-id="page.id">

                <template #item="{ element: childPage }">

                    <PageTreeItem :page="childPage"
                                  :level="level + 1"
                                  :selected-pages="selectedPages"
                                  @toggle-activity="(p) => $emit('toggle-activity', p)"
                                  @delete="(p) => $emit('delete', p)"
                                  @toggle-select="(id) => $emit('toggle-select', id)"
                                  @request-drag-end="handleInnerDragEnd"/>

                </template>

            </draggable>
        </div>
    </div>
</template>
