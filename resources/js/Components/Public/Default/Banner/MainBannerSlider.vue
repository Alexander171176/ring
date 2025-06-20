<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    banners: {
        type: Array,
        required: true,
    }
});

const currentIndex = ref(0);
const isHovered = ref(false);
let intervalId = null;

const startSlider = () => {
    stopSlider();
    if (props.banners.length > 1) {
        intervalId = setInterval(() => {
            if (!isHovered.value) {
                currentIndex.value = (currentIndex.value + 1) % props.banners.length;
            }
        }, 5000); // Плавная смена каждые 5 секунд
    }
};

const stopSlider = () => {
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }
};

onMounted(startSlider);
onBeforeUnmount(stopSlider);
</script>

<template>
    <div class="p-4 sm:p-6 md:p-8 bg-gradient-to-br from-slate-200 to-slate-100
                dark:from-slate-700 dark:to-slate-800
                rounded-md shadow-xl ring-4 ring-gray-400/40">
        <div
            class="relative w-full h-[200px] sm:h-[300px] md:h-[400px] lg:h-[500px] xl:h-[600px]
                   overflow-hidden rounded-3xl border-8 border-yellow-400
                   bg-slate-100 dark:bg-slate-900 shadow-inner"
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false"
        >
            <div
                v-for="(banner, index) in banners"
                :key="banner.id"
                class="absolute inset-0 w-full h-full transition-opacity duration-1000"
                :class="{
                    'opacity-100 z-10': currentIndex === index,
                    'opacity-0 z-0': currentIndex !== index
                }"
            >
                <Link :href="banner.link || '#'" class="block w-full h-full">
                    <img
                        :src="banner.images?.[0]?.webp_url || banner.images?.[0]?.url"
                        :alt="banner.title || ''"
                        :title="banner.title || ''"
                        class="w-full h-full object-cover rounded-lg transition-transform duration-500 hover:scale-105"
                    />
                </Link>
            </div>
        </div>
    </div>
</template>
