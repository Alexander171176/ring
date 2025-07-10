<script setup>
import {ref, computed, onMounted, onBeforeUnmount} from 'vue';
import {usePage} from '@inertiajs/vue3';
import ArticleImageMainIndex from '@/Components/Public/Default/Article/ArticleImageMainIndex.vue';

const {mainArticles, appUrl} = usePage().props;

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
    <div v-if="mainArticles.length" class="lg:col-span-2">
        <div
            class="relative w-full max-w-5xl mx-auto"
            @mouseenter="isPaused = true"
            @mouseleave="isPaused = false">

            <!-- Точки -->
            <div class="absolute bottom-5 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
            <span
                v-for="(article, index) in filteredArticles"
                :key="article.id"
                @click="goToSlide(index)"
                class="cursor-pointer w-3 h-3 rounded-full border border-gray-500 transition"
                :class="currentIndex === index ? 'bg-red-500' : 'bg-gray-300'"
            ></span>
            </div>

            <transition name="fade" mode="out-in">
                <div
                    :key="filteredArticles[currentIndex].id"
                    class="mt-2 md:mt-0 p-0 md:p-4 overflow-hidden"
                >
                    <div class="relative">
                        <img
                            v-if="filteredArticles[currentIndex].img"
                            :src="getImgSrc(filteredArticles[currentIndex].img)"
                            :alt="filteredArticles[currentIndex].title"
                            class="w-full h-auto object-cover
                               shadow-md shadow-gray-600 dark:shadow-gray-950
                               border-2 border-gray-400 transition md:rounded-2xl"
                        />
                        <div v-else-if="filteredArticles[currentIndex].images?.length > 0">
                            <ArticleImageMainIndex
                                :images="filteredArticles[currentIndex].images"
                                :link="`/articles/${filteredArticles[currentIndex].url}`"
                                class="w-full h-auto object-cover border-2 border-gray-400 transition rounded-2xl"
                            />
                        </div>

                        <!-- Заголовок поверх изображения -->
                        <div class="w-full absolute bottom-4 bg-black/75 px-2 py-1">
                            <h2 class="italic text-center text-sm font-bold text-white drop-shadow-lg">
                                {{ filteredArticles[currentIndex].title }}
                            </h2>
                        </div>
                    </div>
                </div>
            </transition>

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
