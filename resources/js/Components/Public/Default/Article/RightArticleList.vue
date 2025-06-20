<script setup>
import { Link } from '@inertiajs/vue3';
import {defineProps} from 'vue';
import ArticleImageLeft from '@/Components/Public/Default/Article/ArticleImageLeft.vue';

const props = defineProps({
    articles: Array,
    appUrl: String,
});

const getImgSrc = (imgPath) => {
    if (!imgPath) return '';
    const base = props.appUrl.endsWith('/') ? props.appUrl.slice(0, -1) : props.appUrl;
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath;
    return `${base}/storage/${path}`;
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // месяцы от 0
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
};

</script>

<template>
    <div class="space-y-4">
        <div
            v-for="article in articles"
            :key="article.id"
            class="bg-white dark:bg-slate-800 shadow-md overflow-hidden">

            <!-- Изображение статьи с плавной сменой (если их больше одного) -->
            <div class="relative">

                <!-- Условие если есть article.images -->
                <div v-if="article.images && article.images.length > 0"
                     class="relative flex flex-col justify-center items-center
                            border border-gray-400"
                     itemprop="image" itemscope itemtype="https://schema.org/ImageObject">

                    <ArticleImageLeft :images="article.images"
                                      :link="`/articles/${article.url}`"
                                      class="w-full"/>

                    <meta itemprop="width" content="800"/>
                    <meta itemprop="height" content="600"/>

                    <!-- Заголовок поверх изображения -->
                    <div class="absolute bottom-2 bg-black/60 px-3 py-1 w-full">
                        <h2 class="text-xs sm:text-sm font-semibold text-white">
                            {{ article.title }}
                        </h2>
                        <div class="p-0 text-[9px] text-white/85 text-end">
                            {{ formatDate(article.published_at) }}
                        </div>
                    </div>
                </div>

                <!-- Иначе:: если есть article.img -->
                <div v-else-if="article.img"
                     class="relative flex justify-center items-center"
                     itemprop="image" itemscope itemtype="https://schema.org/ImageObject">

                    <meta itemprop="width" content="800"/>
                    <meta itemprop="height" content="600"/>

                    <Link :href="`/articles/${article.url}`" class="block w-full h-54">
                        <img :src="getImgSrc(article.img)"
                             :alt="article.title"
                             class="rounded-lg border border-black dark:border-gray-200 w-full">
                    </Link>

                    <!-- Заголовок поверх изображения -->
                    <div class="absolute bottom-2 bg-black/60 px-3 py-1 w-full">
                        <h2 class="text-xs sm:text-sm font-semibold text-white">
                            {{ article.title }}
                        </h2>
                        <div class="p-0 text-[9px] text-white/85 text-end">
                            {{ formatDate(article.published_at) }}
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</template>
