<template>
    <svg @click="$emit('click')"
         class="w-6 h-6 shrink-0 fill-current text-amber-500" viewBox="0 0 16 16">
        <path
            d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z"></path>
    </svg>
</template>

<script setup>
defineEmits(['click']);
</script>
