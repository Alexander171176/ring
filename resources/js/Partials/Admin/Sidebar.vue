<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { defineProps, defineEmits } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import DraggableSidebarGroupLink from '@/Components/Admin/Links/DraggableSidebarGroupLink.vue';
import SidebarLinkPlugin from '@/Components/Admin/Links/SidebarLinkPlugin.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

library.add(fas);

const { t } = useI18n();
const props = defineProps({
    sidebarOpen: Boolean,
    sidebarTitle: String,
});
const emit = defineEmits(['close-sidebar']);
const trigger = ref(null);
const sidebar = ref(null);
const sidebarExpanded = ref(localStorage.getItem('sidebar-expanded') === 'true'); // Теперь состояние раскрытия загружается из localStorage
const sidebarHexColor = ref('155e75'); // Цвет сайдбара по умолчанию
const sidebarOpacity = ref(0.99);
const sidebarRgbColor = ref('');
const currentPath = ref(window.location.pathname); // Используем window.location.pathname для текущего пути

const updateSidebarColor = (hex, opacity) => {
    sidebarHexColor.value = hex;
    sidebarOpacity.value = opacity;
    sidebarRgbColor.value = hexToRgb(hex);
};

const sidebarStyle = computed(() => {
    const hexColor = `#${sidebarHexColor.value}`;
    const opacity = sidebarOpacity.value;
    return {
        backgroundColor: hexColor,
        opacity: opacity,
    };
});

const hexToRgb = (hex) => {
    if (hex.length !== 6) return '';
    const bigint = parseInt(hex, 16);
    const r = (bigint >> 16) & 255;
    const g = (bigint >> 8) & 255;
    const b = bigint & 255;
    return `${r},${g},${b}`;
};

// Добавляем функцию rgbToHex для преобразования RGB в HEX
const rgbToHex = (rgb) => {
    const rgbArray = rgb.split(',').map(Number);
    if (rgbArray.length !== 3 || rgbArray.some(isNaN)) return '';
    return rgbArray.map((num) => num.toString(16).padStart(2, '0')).join('');
};

const clickHandler = ({ target }) => {
    if (!sidebar.value || !trigger.value) return;
    if (!props.sidebarOpen || sidebar.value.contains(target) || trigger.value.contains(target)) return;
    emit('close-sidebar');
};

const keyHandler = ({ keyCode }) => {
    if (!props.sidebarOpen || keyCode !== 27) return;
    emit('close-sidebar');
};

onMounted(async () => {
    document.addEventListener('click', clickHandler);
    document.addEventListener('keydown', keyHandler);
    await loadWidgetPanelSettings(); // Используем общую функцию для загрузки настроек
});

onUnmounted(() => {
    document.removeEventListener('click', clickHandler);
    document.removeEventListener('keydown', keyHandler);
});

const loadWidgetPanelSettings = async () => {
    try {
        const response = await axios.get('/api/settings/widget-panel');
        const { color, opacity } = response.data;
        if (color && opacity !== undefined) {
            sidebarHexColor.value = color.startsWith('#') ? color.substring(1) : rgbToHex(color);
            sidebarOpacity.value = parseFloat(opacity);
        }
    } catch (error) {
        console.error('Ошибка при загрузке настроек сайдбара:', error);
    }
};

// Следим за изменением состояния раскрытия сайдбара и сохраняем его в localStorage
watch(sidebarExpanded, (newVal) => {
    localStorage.setItem('sidebar-expanded', newVal.toString());
});

const isActive = (path) => {
    return currentPath.value === path;
};
</script>

<template>
    <div>
        <div
            class="fixed inset-0 z-20 bg-cyan-800 dark:bg-gray-700 dark:border-r dark:border-gray-600 bg-opacity-30 md:hidden md:z-auto transition-opacity duration-200"
            :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true"></div>

        <div id="sidebar" ref="sidebar"
             :style="sidebarStyle"
             class="h-screen absolute z-40 w-64 left-0 top-0 pb-16 p-2 flex flex-col bg-cyan-800 dark:bg-gray-700 dark:border-r dark:border-gray-600 md:static md:left-auto md:top-auto md:translate-x-0 md:overflow-y-auto overflow-y-scroll no-scrollbar transition-all duration-200 ease-in-out"
             :class="{ 'translate-x-0': sidebarOpen, '-translate-x-64': !sidebarOpen, 'hidden md:flex': true, 'md:w-16': !sidebarExpanded, 'md:!w-64 2xl:!w-64': sidebarExpanded }">

            <div class="flex justify-around items-center mb-5 pr-3 md:px-0">
                <button @click.prevent="sidebarExpanded = !sidebarExpanded" title="t('toggleSidebar')">
                    <svg :class="{ 'rotate-180': sidebarExpanded }"
                         class="mx-1 w-6 h-6 py-1 fill-current transition-transform duration-200 border border-gray-400 hover:border-red-400"
                         viewBox="0 0 24 24">
                        <path class="text-slate-400 hover:text-red-400"
                              d="M19.586 11l-5-5L16 4.586 23.414 12 16 19.414 14.586 18l5-5H7v-2z"/>
                        <path class="text-slate-600" d="M3 23H1V1h2z"/>
                    </svg>
                </button>
                <Link :href="route('dashboard')" v-if="sidebarExpanded">
                    <ApplicationMark class="h-6 w-auto 2xl:block"/>
                </Link>
                <span class="text-indigo-300 font-semibold text-md hidden 2xl:block" v-if="sidebarExpanded">
                    Pulsar CMS {{ sidebarTitle }}
                </span>
                <FontAwesomeIcon :icon="['fas', 'sliders']" class="text-white" v-if="sidebarExpanded"/>
            </div>

            <div class="space-y-2">
                <!-- Скрываем span, если sidebarExpanded равно false -->
                <span class="flex justify-center text-xs uppercase text-yellow-200 font-semibold pl-3" v-if="sidebarExpanded">
                    {{ t('pages') }}
                </span>
                <DraggableSidebarGroupLink :expanded="sidebarExpanded"/>

                <!-- Скрываем span, если sidebarExpanded равно false -->
                <span class="flex justify-center text-xs uppercase text-yellow-200 font-semibold pl-3 mt-0"
                      v-if="sidebarExpanded">
                    {{ t('plugins') }}
                </span>
                <ul>
                    <SidebarLinkPlugin class="mt-0"
                        v-for="plugin in $page.props.plugins"
                        :key="plugin.id"
                        :href="`/admin/plugins/${plugin.id}`"
                        :active="isActive(`/admin/plugins/${plugin.id}`)"
                        :expanded="props.expanded"
                    >
                        <template #default>
                            <!-- Отображаем SVG иконку, используя v-html -->
                            <span v-html="plugin.icon" class="icon-class"></span>
                        </template>
                        <template #text>
                            {{ plugin.name }}
                        </template>
                    </SidebarLinkPlugin>
                </ul>
            </div>
        </div>
    </div>
</template>

<style>
.sidebar-expanded {
    width: 18rem !important;
}
</style>
