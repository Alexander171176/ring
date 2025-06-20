<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ArticleImageMainIndex from '@/Components/Public/Default/Article/ArticleImageMainIndex.vue';

const { mainArticles, appUrl } = usePage().props;

const getImgSrc = (imgPath) => {
    if (!imgPath) return '';
    const base = appUrl.endsWith('/') ? appUrl.slice(0, -1) : appUrl;
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath;
    return `${base}/storage/${path}`;
};

const filteredArticles = computed(() =>
    mainArticles.filter(article =>
        article.img || (article.images && article.images.length > 0)
    )
);

const currentIndex = ref(0);
let intervalId = null;
const isPaused = ref(false);

const nextSlide = () => {
    currentIndex.value = (currentIndex.value + 1) % filteredArticles.value.length;
};

const goToSlide = (index) => {
    currentIndex.value = index;
};

const startAutoSlide = () => {
    intervalId = setInterval(() => {
        if (!isPaused.value) nextSlide();
    }, 5000);
};

const stopAutoSlide = () => {
    clearInterval(intervalId);
};

onMounted(startAutoSlide);
onBeforeUnmount(stopAutoSlide);
</script>

<template>
    <div
        class="relative w-full max-w-5xl mx-auto"
        @mouseenter="isPaused = true"
        @mouseleave="isPaused = false"
    >
        <transition name="fade" mode="out-in">
            <div
                :key="filteredArticles[currentIndex].id"
                class="rounded-md shadow-md bg-white dark:bg-slate-800 overflow-hidden"
            >
                <div class="p-4 border-b border-gray-300">
                    <img
                        v-if="filteredArticles[currentIndex].img"
                        :src="getImgSrc(filteredArticles[currentIndex].img)"
                        :alt="filteredArticles[currentIndex].title"
                        class="rounded-md border border-black dark:border-gray-200 w-full h-auto object-cover"
                    />
                    <div v-else-if="filteredArticles[currentIndex].images?.length > 0">
                        <ArticleImageMainIndex
                            :images="filteredArticles[currentIndex].images"
                            :link="`/articles/${filteredArticles[currentIndex].url}`"
                            class="w-full h-64 object-cover"
                        />
                    </div>
                </div>
                <div class="p-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        {{ filteredArticles[currentIndex].title }}
                    </h2>
                    <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">
                        {{ filteredArticles[currentIndex].short }}
                    </p>
                </div>
            </div>
        </transition>

        <!-- Точки -->
        <div class="absolute bottom-3 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
            <span
                v-for="(article, index) in filteredArticles"
                :key="article.id"
                @click="goToSlide(index)"
                class="cursor-pointer w-3 h-3 rounded-full border border-gray-500 transition"
                :class="currentIndex === index ? 'bg-red-500' : 'bg-gray-300'"
            ></span>
        </div>
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
