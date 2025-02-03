<script setup>
import { defineProps, computed, defineEmits } from 'vue';

const props = defineProps({
    isActive: Boolean,
    title: String
});

const emits = defineEmits(['toggle-printInMenu']);

const buttonClass = computed(() => [
    'flex items-center py-1 px-1 rounded',
    'border border-slate-300',
    'hover:border-red-500',
    'dark:border-red-300 dark:hover:border-red-100'
]);

const emitToggle = () => {
    // console.log('Toggle button clicked'); // Добавляем лог для отладки
    emits('toggle-printInMenu');
};
</script>

<template>
    <button :class="buttonClass" :title="title" @click="emitToggle">
        <svg v-if="isActive"
             class="w-3 h-3 shrink-0 fill-current text-orange-500 mx-1.5" viewBox="0 0 12 12">
            <path d="M6 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2A6 6 0 1 1 6 0a6 6 0 0 1 0 12Z"></path>
        </svg>
        <svg v-else
             class="w-3 h-3 shrink-0 fill-current text-gray-500 mx-1.5" viewBox="0 0 12 12">
            <path d="M6 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2A6 6 0 1 1 6 0a6 6 0 0 1 0 12Z"></path>
        </svg>
    </button>
</template>
