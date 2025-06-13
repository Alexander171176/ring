<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import {useI18n} from 'vue-i18n';
import LikeButton from "@/Components/Public/Default/Article/LikeButton.vue";
import ArticleImageMain from "@/Components/Public/Default/Article/ArticleImageMain.vue";

const {t} = useI18n();
const {appUrl} = usePage().props;

// Извлекаем настройки из props, переданных через Inertia
const {article, recommendedArticles} = usePage().props;

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // месяцы от 0
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
};

const getImgSrc = (imgPath) => {
    if (!imgPath) return '';
    const base = appUrl.endsWith('/') ? appUrl.slice(0, -1) : appUrl;
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath;
    return `${base}/storage/${path}`;
};

</script>

<template>
    <DefaultLayout :title="article.title" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
        <Head>
            <title>{{ article.title }}</title>
            <!-- Основные метатеги, Open Graph, Twitter, Dublin Core, Schema.org и т.д. -->
            <meta name="title" :content="article.title || ''"/>
            <meta name="description" :content="article.meta_desc || ''"/>
            <meta name="keywords" :content="article.meta_keywords || ''"/>
            <meta name="author" :content="article.author || ''"/>
            <meta name="viewport" content="width=device-width, initial-scale=1"/>

            <!-- Open Graph / Facebook -->
            <meta property="og:title" :content="article.title || ''"/>
            <meta property="og:description" :content="article.meta_desc || ''"/>
            <meta property="og:type" content="article"/>
            <meta property="og:url" :content="`/articles/${article.url}`"/>
            <meta property="og:image"
                  :content="article.images && article.images.length > 0 ? article.images[0].url : ''"/>
            <meta property="og:locale" :content="article.locale || 'ru_RU'"/>

            <!-- Twitter -->
            <meta name="twitter:card" content="summary_large_image"/>
            <meta name="twitter:title" :content="article.title || ''"/>
            <meta name="twitter:description" :content="article.meta_desc || ''"/>
            <meta name="twitter:image"
                  :content="article.images && article.images.length > 0 ? article.images[0].url : ''"/>

            <!-- Schema.org / Google -->
            <meta itemprop="name" :content="article.title || ''"/>
            <meta itemprop="description" :content="article.meta_desc || ''"/>
            <meta itemprop="image" :content="article.images && article.images.length > 0 ? article.images[0].url : ''"/>
        </Head>

        <div class="flex flex-col w-full">

            <!-- Хлебные крошки -->
            <nav class="text-left text-sm ml-0 md:ml-4 lg:ml-6 xl:ml-8 mt-4 px-1"
                 aria-label="Breadcrumb">
                <ol class="list-reset flex flex-col md:flex-row items-center justify-start space-x-0">
                    <li>
                        <Link href="/" class="hover:underline text-slate-900 dark:text-slate-100">
                            {{ t('home') }}
                        </Link>
                        <span class="mx-1 text-slate-900 dark:text-slate-100">/</span>
                    </li>
                    <li class="text-slate-900 dark:text-slate-100 lowercase">
                        {{ article.title }}
                    </li>
                </ol>
            </nav>

            <!-- Применяем вычисляемый класс к элементу статьи -->
            <article itemscope itemtype="https://schema.org/BlogPosting"
                     class="flex flex-col p-4 selection:bg-red-400 selection:text-white
                        bg-slate-50 dark:bg-slate-950 mx-auto">

                <div class="flex-1 max-w-3xl">
                    <!-- Микроданные для заголовка -->
                    <header>
                        <div class="flex flex-col items-center justify-center my-1">
                            <!-- Дата публикации, форматируем по необходимости -->
                            <time itemprop="datePublished" datetime="{{ formatDate(article.published_at) }}"
                                  class="opacity-75 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">
                                {{ formatDate(article.published_at) }}
                            </time>
                            <h1 itemprop="headline"
                                class="text-center font-semibold text-xl text-gray-900 dark:text-slate-100 mb-4">
                                {{ article.title }}
                            </h1>
                            <div itemprop="interactionStatistic" itemscope
                                 itemtype="http://schema.org/InteractionCounter">
                                <meta itemprop="interactionType" content="http://schema.org/ViewAction">
                                <meta itemprop="userInteractionCount" :content="article.views">
                                <span :title="t('views')"
                                      class="hidden ml-2 px-1 py-0.5
                                         text-xs font-semibold text-white bg-emerald-500 rounded-full">
                            {{ article.views }}
                        </span>
                            </div>
                        </div>
                    </header>

                    <div v-if="article.short"
                         class="hidden items-center justify-center my-3">
                        <!-- Краткое описание -->
                        <p itemprop="description"
                           class="text-center text-md text-teal-700 dark:text-teal-200 mr-2">
                            {{ article.short }}
                        </p>
                    </div>

                    <!-- Изображение статьи с плавной сменой (если их больше одного) -->
                    <div class="p-4 border border-gray-300 rounded shadow-lg">
                        <!-- Условие: если есть article.img -->
                        <div v-if="article.img" class="flex justify-center items-center"
                             itemprop="image" itemscope
                             itemtype="https://schema.org/ImageObject">
                            <img :src="getImgSrc(article.img)"
                                 :alt="article.title"
                                 class="rounded-md border border-black dark:border-gray-200 w-full">
                            <meta itemprop="width" content="800"/>
                            <meta itemprop="height" content="600"/>
                        </div>

                        <!-- Иначе: если есть article.images -->
                        <div v-else-if="article.images && article.images.length > 0"
                             class="flex flex-col justify-center items-center"
                             itemprop="image" itemscope itemtype="https://schema.org/ImageObject">

                            <ArticleImageMain :images="article.images"
                                              :link="`/articles/${article.url}`"
                                              class="max-w-4xl"/>
                            <meta itemprop="width" content="800"/>
                            <meta itemprop="height" content="600"/>

                            <div v-if="article.images[0].caption"
                                 class="mt-2 text-center text-sm
                                    text-gray-600 dark:text-gray-300 italic underline decoration-double"
                                 itemprop="caption">
                                {{ article.images[0].caption }}
                            </div>
                        </div>
                    </div>

                    <!-- Полное описание -->
                    <div v-if="article.description"
                         class="w-full max-w-4xl mx-auto my-4 p-2 text-md text-slate-700 dark:text-slate-300"
                         v-html="article.description" itemprop="articleBody"></div>

                    <!-- Теги -->
                    <div v-if="article.tags"
                         class="flex flex-col md:flex-row justify-center items-center mb-3
                            italic font-semibold text-sm text-slate-700 dark:text-slate-300">
                                <span v-for="(tag, index) in article.tags" :key="tag.id">
                                  <Link :href="`/tags/${tag.slug}`" itemprop="keywords"
                                        class="hover:text-red-400 hover:dark:text-red-300">
                                      {{ tag.name }}
                                  </Link>
                                    <span v-if="index < article.tags.length - 1">,&nbsp;</span>
                                </span>
                    </div>

                    <div class="flex justify-center items-center">
                        <!-- Автор -->
                        <div v-if="article.author"
                             class="font-semibold text-sky-600 dark:text-sky-300"
                             itemprop="author">
                            <span class="mr-2">{{ article.author }}</span>
                        </div>
                        <!-- Лайк -->
                        <LikeButton/>
                    </div>

                </div>
            </article>

            <!-- Блок рекомендованных статей -->
            <div v-if="recommendedArticles && recommendedArticles.length > 0" class="mt-4 p-4">
                <h2 class="mb-4 tracking-wide text-center font-semibold text-xl
                           text-slate-700 dark:text-slate-300">
                    {{ t('relatedArticles') }}:
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-4 gap-4">

                    <!-- Изменённый фрагмент для каждого рекомендованного элемента -->
                    <div v-for="rec in recommendedArticles" :key="rec.id"
                         class="p-4 border border-gray-300 rounded shadow">

                        <div class="relative w-full">

                            <!-- Обёртка с соотношением сторон 4:3 -->
                            <div class="w-full aspect-[4/3] overflow-hidden">
                                <!-- Если есть rec.img -->
                                <img
                                    v-if="rec.img"
                                    :src="getImgSrc(rec.img)"
                                    :alt="rec.title"
                                    class="w-full h-full object-cover"
                                />

                                <!-- Если нет rec.img, но есть rec.images -->
                                <img
                                    v-else-if="rec.images && rec.images.length > 0"
                                    :src="rec.images[0].webp_url || rec.images[0].url"
                                    :alt="rec.images[0].alt"
                                    class="w-full h-full object-cover"
                                />

                                <!-- Если нет ни того, ни другого -->
                                <div v-else
                                     class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-gray-400">
                                        <span class="text-gray-500 dark:text-gray-700">
                                            {{ t('noCurrentImage') }}
                                        </span>
                                </div>
                            </div>

                            <!-- Прозрачный блок с информацией, накладывается снизу на изображение -->
                            <div class="absolute bottom-0 left-0 w-full p-2 bg-slate-800 bg-opacity-75">
                                <div class="text-xs font-semibold text-yellow-200">
                                    {{ formatDate(rec.published_at) }}
                                </div>
                                <Link :href="`/articles/${rec.url}`"
                                      class="text-xs font-semibold text-white hover:text-red-400">
                                    {{ rec.title }}
                                </Link>
                                <!-- <p class="text-xs text-gray-200 mt-1">{{ rec.short }}</p> -->
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </DefaultLayout>
</template>
