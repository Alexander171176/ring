<script setup>
import {Link} from '@inertiajs/vue3';

const props = defineProps({
    article: Object,
    appUrl: String,
});

const getImgSrc = (imgPath) => {
    if (!imgPath) return '';
    const base = props.appUrl.endsWith('/') ? props.appUrl.slice(0, -1) : props.appUrl;
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath;
    return `${base}/storage/${path}`;
};
</script>

<template>
    <Link :href="`/articles/${article.url}`"
          class="flex h-fit rounded-md">

        <!-- Изображение слева -->
        <div class="overflow-hidden w-24 md:w-32 lg:w-48
                    max-h-12 md:max-h-20 lg:max-h-28 rounded-md">
            <img
                v-if="article.images && article.images.length > 0"
                :src="article.images[0].webp_url || article.images[0].url"
                :alt="article.title"
                class="w-full h-fit object-cover"
            />
            <img
                v-else-if="article.img"
                :src="getImgSrc(article.img)"
                :alt="article.title"
                class="w-full h-auto object-cover"
            />
            <img
                v-else
                src="/article_images/default-image.png"
                alt="No image"
                class="w-full h-auto object-cover rounded-md border-2 border-gray-400"
            />
        </div>

        <!-- Текст справа -->
        <div class="flex-1 flex items-center px-3">
            <h2 class="text-xs md:text-sm lg:text-lg font-semibold text-gray-800 dark:text-white
                       hover:text-red-500 dark:hover:text-orange-300">
                {{ article.title }}
            </h2>
        </div>
    </Link>
</template>
