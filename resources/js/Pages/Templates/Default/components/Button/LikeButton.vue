<script setup>
const props = defineProps({
    likeCount: Number
})

const emit = defineEmits(['click'])

const onClick = () => {
    emit('click')
}
</script>

<template>
    <button @click="onClick" class="flex items-center">
        <svg class="w-4 h-4 shrink-0 fill-current mr-1.5" viewBox="0 0 16 16">
            <path d="M14.682 2.318A4.485 4.485 0 0011.5 1 4.377 4.377 0 008 2.707 4.383 4.383 0 004.5 1a4.5 4.5 0 00-3.182 7.682L8 15l6.682-6.318a4.5 4.5 0 000-6.364zm-1.4 4.933L8 12.247l-5.285-5A2.5 2.5 0 014.5 3c1.437 0 2.312.681 3.5 2.625C9.187 3.681 10.062 3 11.5 3a2.5 2.5 0 011.785 4.251h-.003z"></path>
        </svg>
        <span class="text-sm font-semibold">{{ likeCount }}</span>
    </button>
</template>
