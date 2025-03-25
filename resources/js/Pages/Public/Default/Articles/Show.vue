<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

const {article, recommendedArticles} = usePage().props;

</script>

<template>
    <PublicLayout :title="article.title" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
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

        <!-- Обернём основное содержимое в блок с микроданными для BlogPosting -->
        <article itemscope itemtype="https://schema.org/BlogPosting"
                 class="flex-1 p-4 bg-slate-100 dark:bg-slate-800 selection:bg-red-400 selection:text-white">
            <!-- Микроданные для заголовка -->
            <header>
                <h1 itemprop="headline"
                    class="flex items-center justify-center my-4
                           text-center font-bolder text-3xl text-gray-900 dark:text-slate-100">
                    {{ article.title }}
                    <span :title="t('views')"
                          class="ml-2 px-1 py-0 text-xs font-semibold text-white bg-emerald-500 rounded-full">
            {{ article.views }}
          </span>
                </h1>
                <!-- Дата публикации, форматируем по необходимости -->
                <time itemprop="datePublished" datetime="{{ article.created_at.substring(0, 10) }}"
                      class="flex items-center justify-center text-sm text-orange-500 dark:text-orange-400 mb-2">
                    {{ article.created_at.substring(0, 10) }}
                </time>
            </header>

            <!-- Краткое описание -->
            <p itemprop="description"
               class="flex items-center justify-center my-4 text-center text-xl text-teal-700 dark:text-teal-200">
                {{ article.short }}
            </p>

            <!-- Изображение статьи -->
            <div v-if="article.images && article.images.length > 0"
                 class="flex flex-col justify-center items-center"
                 itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
                <img
                    :src="article.images[0].url"
                    :alt="article.images[0].alt"
                    class="w-full max-w-4xl h-auto object-cover
                           border-4 border-sky-600 shadow-lg shadow-gray-400 dark:shadow-gray-600"
                    itemprop="url"
                />
                <!-- Дополнительные метатеги для изображения -->
                <meta itemprop="width" content="800"/>
                <meta itemprop="height" content="600"/>
                <!-- Блок для caption -->
                <div v-if="article.images[0].caption"
                     class="mt-2 text-center text-sm text-gray-600 dark:text-gray-300 italic underline decoration-double"
                     itemprop="caption">
                    {{ article.images[0].caption }}
                </div>
            </div>

            <!-- Полное описание -->
            <div class="w-full max-w-4xl mx-auto my-4 text-center text-xl text-gray-700 dark:text-gray-200
                        border border-dashed border-slate-400 dark:border-slate-200"
                 v-html="article.description" itemprop="articleBody"></div>

            <!-- Теги -->
            <div class="flex justify-center items-center mb-3 font-semibold text-violet-600 dark:text-violet-300">
                <span v-for="(tag, index) in article.tags" :key="tag.id">
                  <Link :href="`/tags/${tag.slug}`" itemprop="keywords">{{ tag.name }}</Link>
                  <span v-if="index < article.tags.length - 1">, </span>
                </span>
            </div>

            <!-- Автор -->
            <div class="flex justify-center items-center font-semibold text-sky-600 dark:text-sky-300"
                 itemprop="author">
                {{ article.author }}
            </div>

            <!-- Блок рекомендованных статей -->
            <div class="mt-4">
                <h2 v-if="recommendedArticles && recommendedArticles.length > 0"
                    class="text-orange-400 dark:text-orange-300 text-center text-2xl font-semibold mb-4">
                    {{ t('relatedArticles') }}:
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

                    <!-- Изменённый фрагмент для каждого рекомендованного элемента -->
                    <div v-for="rec in recommendedArticles" :key="rec.id"
                         class="p-4 border border-gray-300 rounded shadow">
                        <div class="relative w-full">
                            <!-- Обёртка с соотношением сторон 4:3 -->
                            <div class="w-full aspect-[4/3] overflow-hidden">
                                <img
                                    v-if="rec.images && rec.images.length > 0"
                                    :src="rec.images[0].url"
                                    :alt="rec.images[0].alt"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-gray-400">
                                    <span class="text-gray-500 dark:text-gray-700">{{ t('noCurrentImage') }}</span>
                                </div>
                            </div>
                            <!-- Прозрачный блок с информацией, накладывается снизу на изображение -->
                            <div class="absolute bottom-0 left-0 w-full p-2 bg-slate-800 bg-opacity-75">
                                <div class="text-xs font-semibold text-yellow-200">
                                    {{ rec.created_at.substring(0, 10) }}
                                </div>
                                <Link :href="`/articles/${rec.url}`"
                                      class="text-sm font-semibold text-white hover:text-amber-400">
                                    {{ rec.title }}
                                </Link>
<!--                                <p class="text-xs text-gray-200 mt-1">{{ rec.short }}</p>-->
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </article>

    </PublicLayout>
</template>
