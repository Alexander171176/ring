<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    images: {
        type: Array,
        required: true,
    },
    link: {
        type: String,
        default: '',
    }
});

const currentIndex = ref(0);
const isPaused = ref(false);

const totalImages = computed(() => props.images.length);
const currentImage = computed(() => props.images[currentIndex.value]);

let intervalId = null;

const nextImage = () => {
    if (!isPaused.value) {
        currentIndex.value = (currentIndex.value + 1) % totalImages.value;
    }
};

onMounted(() => {
    if (totalImages.value > 1) {
        intervalId = setInterval(nextImage, 5000);
    }
});

onBeforeUnmount(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>

<template>
    <div
        class="relative w-full h-full"
        @mouseenter="isPaused = true"
        @mouseleave="isPaused = false"
    >
        <component
            :is="link ? Link : 'div'"
            :href="link || null"
            class="block w-full h-54"
        >
            <transition name="fade" mode="out-in">
                <img
                    :key="currentImage.id"
                    :src="currentImage.webp_url || currentImage.url"
                    :alt="currentImage.alt"
                    class="w-full h-full object-cover"
                />
            </transition>
        </component>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 1s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
