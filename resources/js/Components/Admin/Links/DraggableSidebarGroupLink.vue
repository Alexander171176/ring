<script setup>
import { ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import Draggable from 'vuedraggable';
import DraggableSidebarLink from '@/Components/Admin/Links/DraggableSidebarLink.vue';

const props = defineProps({
    expanded: Boolean
});

const emit = defineEmits(['update:mainLinks', 'update:hiddenLinks']);

const { t } = useI18n();

const mainLinks = ref(JSON.parse(localStorage.getItem('mainLinks')) || [
    'admin',
    'rubrics',
    'sections',
    'articles',
    'comments',
    'reports',
    'charts',
    'users',
    'roles',
    'permissions',
    'settings',
    'parameters',
    'components',
    'diagrams',
    'plugins',
]);

const hiddenLinks = ref(JSON.parse(localStorage.getItem('hiddenLinks')) || [
    'apiTokens',
    'teamSettings',
]);

const showHiddenLinks = ref(false);

const toggleHiddenLinks = () => {
    showHiddenLinks.value = !showHiddenLinks.value;
};

const handleDragEnd = () => {
    localStorage.setItem('mainLinks', JSON.stringify(mainLinks.value));
    localStorage.setItem('hiddenLinks', JSON.stringify(hiddenLinks.value));
    emit('update:mainLinks', mainLinks.value);
    emit('update:hiddenLinks', hiddenLinks.value);
};

watch(mainLinks, (newVal) => {
    localStorage.setItem('mainLinks', JSON.stringify(newVal));
    emit('update:mainLinks', newVal);
});

watch(hiddenLinks, (newVal) => {
    localStorage.setItem('hiddenLinks', JSON.stringify(newVal));
    emit('update:hiddenLinks', newVal);
});

onMounted(() => {
    mainLinks.value = JSON.parse(localStorage.getItem('mainLinks')) || mainLinks.value;
    hiddenLinks.value = JSON.parse(localStorage.getItem('hiddenLinks')) || hiddenLinks.value;
});
</script>

<template>
    <Draggable v-model="mainLinks" @end="handleDragEnd" itemKey="id" group="links" tag="ul">
        <template #item="{ element }">
            <DraggableSidebarLink :id="element" :expanded="expanded" />
        </template>
    </Draggable>

    <button @click="toggleHiddenLinks" class="flex justify-center items-center w-full pt-3 mb-0">
        <span class="text-xs uppercase text-yellow-200 font-semibold">{{ t('more') }}</span>
    </button>

    <Draggable v-if="showHiddenLinks"
               v-model="hiddenLinks" @end="handleDragEnd" itemKey="id" group="links" tag="ul" class="my-3">
        <template #item="{ element }">
            <DraggableSidebarLink :id="element" :expanded="expanded" />
        </template>
    </Draggable>

    <br>
</template>
