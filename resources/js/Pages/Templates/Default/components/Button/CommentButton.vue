<script setup>
defineProps({
    commentsCount: {
        type: Number,
        required: true,
    }
});
</script>

<template>
    <button class="flex items-center ml-4
                   text-slate-400 hover:text-indigo-500
                   dark:text-slate-200 dark:hover:text-indigo-300">
        <svg class="w-4 h-4 shrink-0 fill-current mr-1.5" viewBox="0 0 16 16">
            <path d="M8 0C3.6 0 0 3.1 0 7s3.6 7 8 7h.6l5.4 2v-4.4c1.2-1.2 2-2.8 2-4.6 0-3.9-3.6-7-8-7zm4 10.8v2.3L8.9 12H8 c-3.3 0-6-2.2-6-5s2.7-5 6-5 6 2.2 6 5 c0 2.2-2 3.8-2 3.8 z"></path>
        </svg>
        <span class="text-sm text-slate-500 dark:text-slate-300">
            {{ commentsCount }}
        </span>
    </button>
</template>
