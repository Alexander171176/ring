<script setup>
defineProps({
    views: Number,  // Число просмотров передается через пропсы
});
</script>

<template>
    <button class="flex items-center ml-4
                   text-slate-400 hover:text-indigo-500
                   dark:text-slate-200 dark:hover:text-indigo-300">
        <svg class="w-4 h-4 shrink-0 fill-current mr-1.5" viewBox="0 0 16 16">
            <path
                d="M13 7h2v6a1 1 0 0 1-1 1H4v2l-4-3 4-3v2h9V7ZM3 9H1V3a1 1 0 0 1 1-1h10V0 l4 3-4 3V4 H3 v5 Z"></path>
        </svg>
        <span class="text-sm text-slate-500 dark:text-slate-300">
            {{ views }}
        </span>
    </button>
</template>
