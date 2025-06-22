<script setup>
import {Link} from '@inertiajs/vue3';
import {defineProps} from 'vue';
import ArticleImageRight from "@/Components/Public/Default/Article/ArticleImageRight.vue";

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
    <div v-if="articles.length" class="lg:col-span-1">
        <div class="py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
                <div
                    v-for="article in articles"
                    :key="article.id"
                    class="overflow-hidden">

                    <!-- ✅ Вся карточка теперь является ссылкой -->
                    <Link :href="`/articles/${article.url}`"
                          class="block">

                        <!-- код изображений -->
                        <div class="relative overflow-hidden max-h-56 rounded-xl
                                    border-2 border-gray-400">

                            <!-- article.images -->
                            <div v-if="article.images && article.images.length > 0"
                                 class="relative flex flex-col justify-center items-center
                                        border border-gray-400 rounded-md"
                                 itemprop="image" itemscope itemtype="https://schema.org/ImageObject">

                                <ArticleImageRight :images="article.images" class="w-full"/>

                                <meta itemprop="width" content="800"/>
                                <meta itemprop="height" content="600"/>
                            </div>

                            <!-- article.img -->
                            <div v-else-if="article.img"
                                 class="relative flex justify-center items-center"
                                 itemprop="image" itemscope itemtype="https://schema.org/ImageObject">

                                <meta itemprop="width" content="800"/>
                                <meta itemprop="height" content="600"/>

                                <img :src="getImgSrc(article.img)"
                                     :alt="article.title"
                                     class="w-full h-full object-cover">
                            </div>

                            <!-- Общий блок заголовка и даты -->
                            <div class="absolute bottom-2 bg-black/60 px-3 py-1 w-full">
                                <h2 class="text-xs sm:text-sm font-semibold text-white">
                                    {{ article.title }}
                                </h2>
                                <div class="p-0 text-[9px] text-white/85 text-end">
                                    {{ formatDate(article.published_at) }}
                                </div>
                            </div>

                        </div>

                    </Link>

                </div>
            </div>
        </div>
    </div>
</template>
