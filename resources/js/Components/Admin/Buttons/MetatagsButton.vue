<script setup>
import {defineProps, ref} from 'vue'

const props = defineProps({
    href: {
        type: String,
        default: 'submit'
    }
})

const isPressed = ref(false)
</script>

<template>
    <button
        class="flex items-center
               btn px-2 py-0.5
               bg-yellow-400 rounded-sm shadow-md
               text-white text-sm font-semibold
               transition-colors duration-300 ease-in-out
               hover:bg-yellow-500 focus:bg-yellow-500 focus:outline-none"
        @mousedown="isPressed = true"
        @mouseup="isPressed = false"
        :class="{ 'ring-2 ring-yellow-400 ring-offset-2 ring-offset-white': isPressed }">
    <span>
      <slot name="icon">
        <!-- По умолчанию ваш SVG будет тут -->
        <svg class="w-4 h-4 fill-current text-white shrink-0 mr-2" viewBox="0 0 16 16">
            <path d="M13 7h2v6a1 1 0 01-1 1H4v2l-4-3 4-3v2h9V7zM3 9H1V3a1 1 0 011-1h10V0l4 3-4 3V4H3v5z"></path>
        </svg>
      </slot>
    </span>
        <span class="hidden xs:block ml-2">
      <slot/>
    </span>
    </button>
</template>
