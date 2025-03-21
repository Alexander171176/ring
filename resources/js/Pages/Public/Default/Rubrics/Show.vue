<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const {rubric, sections, activeArticlesCount} = usePage().props;

// Если нужно использовать i18n:
import {useI18n} from 'vue-i18n';

const {t} = useI18n();
</script>

<template>
    <PublicLayout :title="rubric.title" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
        <Head>
            <title>{{ rubric.title }}</title>
            <meta name="title" :content="rubric.title || ''"/>
            <meta name="keywords" :content="rubric.meta_keywords || ''"/>
            <meta name="description" :content="rubric.meta_desc || ''"/>

            <meta property="og:title" :content="rubric.title || ''"/>
            <meta property="og:description" :content="rubric.meta_desc || ''"/>
            <meta property="og:type" content="website"/>
            <meta property="og:url" :content="`/rubrics/${rubric.url}`"/>
            <meta property="og:image" :content="rubric.icon || ''"/>
            <meta property="og:locale" :content="rubric.locale || 'ru_RU'"/>

            <meta name="twitter:card" content="summary_large_image"/>
            <meta name="twitter:title" :content="rubric.title || ''"/>
            <meta name="twitter:description" :content="rubric.meta_desc || ''"/>
            <meta name="twitter:image" :content="rubric.icon || ''"/>

            <meta name="DC.title" :content="rubric.title || ''"/>
            <meta name="DC.description" :content="rubric.meta_desc || ''"/>
            <meta name="DC.identifier" :content="`/rubrics/${rubric.url}`"/>
            <meta name="DC.language" :content="rubric.locale || 'ru'"/>
        </Head>

        <div class="relative min-h-screen
                    sm:flex sm:justify-center sm:items-center
                    bg-dots-darker bg-center bg-gray-100
                    dark:bg-dots-lighter dark:bg-slate-900
                    selection:bg-red-500 selection:text-white">

            <div class="max-w-8xl mx-auto tracking-wider">

                <!-- Заголовок рубрики -->
                <h1 class="flex items-center justify-center my-4
                           text-center font-bolder text-3xl
                           text-gray-900 dark:text-slate-100">

                    <span class="flex justify-center" v-html="rubric.icon"/>

                    {{ rubric.title }}

                    <span class="ml-2 px-1 py-0
                                 text-xs font-semibold text-white
                                 bg-emerald-500 rounded-sm">
                        {{ activeArticlesCount }}
                    </span>

                </h1>

                <p class="flex items-center justify-center mb-4
                          text-center text-xl text-gray-500 dark:text-gray-400">
                    {{ rubric.short }}
                </p>

                <!-- Блок секций -->
                <div v-if="sections.length" class="space-y-8">

                    <div v-for="section in sections" :key="section.id"
                         class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">

                        <div class="p-6">

                            <!-- Заголовок секции -->
                            <h2 class="mb-2 text-2xl font-semibold text-red-500 dark:text-red-200"
                                :title="section.short">
                                {{ section.title }}
                            </h2>

                            <!-- Список статей для секции -->
                            <div v-if="section.articles && section.articles.length"
                                 class="grid grid-cols-12 gap-6">

                                <div v-for="article in section.articles" :key="article.id"
                                     class="col-span-full sm:col-span-6 xl:col-span-3 bg-slate-50 dark:bg-slate-800
                                            shadow-lg rounded-sm border border-slate-300 dark:border-slate-400
                                            overflow-hidden">

                                    <div class="flex flex-col h-full">

                                        <!-- Вывод изображения, если оно есть -->
                                        <div v-if="article.images && article.images.length > 0"
                                             class="h-40 overflow-hidden">
                                            <img :src="article.images[0].url"
                                                 :alt="article.images[0].alt"
                                                 class="w-full h-full object-cover"/>
                                        </div>
                                        <div v-else
                                             class="h-40 flex items-center justify-center
                                                    bg-gray-200 dark:bg-gray-400">
                                            <span class="text-gray-500 dark:text-gray-700">
                                                {{ t('noCurrentImage') }}
                                            </span>
                                        </div>

                                        <div class="grow flex flex-col p-5">

                                            <div class="grow">

                                                <header class="mb-3">
                                                    <h3 class="text-lg text-gray-900 dark:text-white font-semibold">
                                                        <Link :href="`/articles/${article.url}`"
                                                              class="hover:text-blue-600 dark:hover:text-blue-400">
                                                            {{ article.title }}
                                                        </Link>
                                                    </h3>
                                                </header>

                                                <div class="flex flex-wrap items-center mb-3">
                                                    <p class="font-semibold text-slate-600 dark:text-slate-300 text-sm">
                                                        {{ article.short }}
                                                    </p>
                                                </div>

                                                <ul class="text-sm space-y-2 mb-3">
                                                    <li class="flex items-center justify-between">
                                                        <div class="font-semibold text-amber-500 dark:text-amber-300">
                                                            {{ article.author }}
                                                        </div>
                                                        <svg class="w-4 h-4 fill-current text-slate-400 shrink-0 ml-3"
                                                             viewBox="0 0 16 16">
                                                            <path
                                                                d="M15 15V5l-5-5H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h12c.6 0 1-.4 1-1zM3 2h6v4h4v8H3V2z"></path>
                                                        </svg>
                                                    </li>
                                                    <li class="flex items-center justify-between">
                                                        <div class="font-semibold text-gray-700 dark:text-gray-300">
                                                            Просмотры:
                                                        </div>
                                                        <div class="flex items-center justify-between">
                                                            <span
                                                                class="font-semibold text-gray-700 dark:text-gray-300">
                                                                {{ article.views }}
                                                            </span>
                                                            <svg
                                                                class="w-4 h-4 fill-current text-slate-400 shrink-0 ml-3"
                                                                viewBox="0 0 16 16">
                                                                <path
                                                                    d="M8 2C3.246 2 .251 7.29.127 7.515a.998.998 0 0 0 .002.975c.07.125 1.044 1.801 2.695 3.274C4.738 13.582 6.283 14 8 14c4.706 0 7.743-5.284 7.872-5.507a1 1 0 0 0 0-.98A13.292 13.292 0 0 0 8 2zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"></path>
                                                            </svg>
                                                        </div>
                                                    </li>
                                                    <li class="flex items-center justify-between">
                                                        <div class="font-semibold text-gray-700 dark:text-gray-300">
                                                            Лайки:
                                                        </div>
                                                        <div class="flex items-center justify-between">
                                                            <span
                                                                class="font-semibold text-gray-700 dark:text-gray-300">
                                                                {{ article.likes }}
                                                            </span>
                                                            <svg
                                                                class="w-4 h-4 fill-current text-slate-400 shrink-0 ml-3"
                                                                viewBox="0 0 512 512">
                                                                <path
                                                                    d="M225.8 468.2l-2.5-2.3L48.1 303.2C17.4 274.7 0 234.7 0 192.8l0-3.3c0-70.4 50-130.8 119.2-144C158.6 37.9 198.9 47 231 69.6c9 6.4 17.4 13.8 25 22.3c4.2-4.8 8.7-9.2 13.5-13.3c3.7-3.2 7.5-6.2 11.5-9c0 0 0 0 0 0C313.1 47 353.4 37.9 392.8 45.4C462 58.6 512 119.1 512 189.5l0 3.3c0 41.9-17.4 81.9-48.1 110.4L288.7 465.9l-2.5 2.3c-8.2 7.6-19 11.9-30.2 11.9s-22-4.2-30.2-11.9zM239.1 145c-.4-.3-.7-.7-1-1.1l-17.8-20-.1-.1s0 0 0 0c-23.1-25.9-58-37.7-92-31.2C81.6 101.5 48 142.1 48 189.5l0 3.3c0 28.5 11.9 55.8 32.8 75.2L256 430.7 431.2 268c20.9-19.4 32.8-46.7 32.8-75.2l0-3.3c0-47.3-33.6-88-80.1-96.9c-34-6.5-69 5.4-92 31.2c0 0 0 0-.1 .1s0 0-.1 .1l-17.8 20c-.3 .4-.7 .7-1 1.1c-4.5 4.5-10.6 7-16.9 7s-12.4-2.5-16.9-7z"/>
                                                            </svg>
                                                        </div>
                                                    </li>
                                                    <li class="flex items-center justify-between">
                                                        <div class="font-semibold text-violet-600 dark:text-violet-300">
                                                            <span v-for="(tag, index) in article.tags" :key="tag.id">
                                                              <Link :href="`/tags/${tag.slug}`">{{ tag.name }}</Link>
                                                              <span v-if="index < article.tags.length - 1">, </span>
                                                            </span>
                                                        </div>
                                                        <svg class="w-4 h-4 fill-current text-slate-400 shrink-0 ml-3"
                                                             viewBox="0 0 16 16">
                                                            <path
                                                                d="M15.686 5.695L10.291.3c-.4-.4-.999-.4-1.399 0s-.4.999 0 1.399l.6.599-6.794 3.697-1-1c-.4-.399-.999-.399-1.398 0-.4.4-.4 1 0 1.4l1.498 1.498 2.398 2.398L.6 13.988 2 15.387l3.696-3.697 3.997 3.996c.5.5 1.199.2 1.398 0 .4-.4.4-.999 0-1.398l-.999-1 3.697-6.694.6.6c.599.6 1.199.2 1.398 0 .3-.4.3-1.1-.1-1.499zM8.493 11.79L4.196 7.494l6.695-3.697 1.298 1.299-3.696 6.694z"></path>
                                                        </svg>
                                                    </li>
                                                </ul>

                                            </div>

                                            <div>
                                                <Link :href="`/articles/${article.url}`"
                                                      class="block w-full flex items-center justify-center
                                                             rounded-sm py-1 bg-blue-500 hover:bg-indigo-600 text-white">
                                                    {{ t('readMore') }}
                                                </Link>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div v-else class="text-gray-500 text-sm">
                                {{ t('noData') }}
                            </div>

                        </div>

                    </div>

                </div>

                <div v-else class="text-gray-500 text-lg text-center">
                    {{ t('noData') }}
                </div>

            </div>

        </div>

    </PublicLayout>
</template>
