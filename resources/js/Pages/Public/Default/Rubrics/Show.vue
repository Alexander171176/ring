<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const {rubric, sections, sectionsCount} = usePage().props;

defineProps({
    title: String,
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <PublicLayout
        :title="rubric.title"
        :can-login="canLogin"
        :can-register="canRegister"
    >
        <Head>
            <!-- Основные мета-теги -->
            <title>{{ rubric.title }}</title>
            <meta name="title" :content="rubric.title || ''"/>
            <meta name="keywords" :content="rubric.meta_keywords || ''"/>
            <meta name="description" :content="rubric.meta_desc || ''"/>

            <!-- Open Graph (Facebook, Twitter, LinkedIn) -->
            <meta property="og:title" :content="rubric.title || ''"/>
            <meta property="og:description" :content="rubric.meta_desc || ''"/>
            <meta property="og:type" content="website"/>
            <meta property="og:url" :content="`/rubrics/${rubric.url}`"/>
            <meta property="og:image" :content="rubric.icon || ''"/>
            <meta property="og:locale" :content="rubric.locale || 'ru_RU'"/>

            <!-- Twitter Card -->
            <meta name="twitter:card" content="summary_large_image"/>
            <meta name="twitter:title" :content="rubric.title || ''"/>
            <meta name="twitter:description" :content="rubric.meta_desc || ''"/>
            <meta name="twitter:image" :content="rubric.icon || ''"/>

            <!-- Dublin Core Metadata -->
            <meta name="DC.title" :content="rubric.title || ''"/>
            <meta name="DC.description" :content="rubric.meta_desc || ''"/>
            <meta name="DC.identifier" :content="`/rubrics/${rubric.url}`"/>
            <meta name="DC.language" :content="rubric.locale || 'ru'"/>
        </Head>

        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen
                    bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-slate-900
                    selection:bg-red-500 selection:text-white">
            <div class="max-w-8xl mx-auto">

                <!-- Название рубрики -->
                <h1 class="flex items-center justify-center my-4
                           text-center font-bolder text-3xl text-gray-900 dark:text-slate-100">
                    <span class="flex justify-center" v-html="rubric.icon" />
                    {{ rubric.title }}
                    <!-- Количество статей -->
                    <span class="ml-2 text-xs font-semibold text-white px-1 py-0 bg-emerald-500 rounded-sm">
                        {{ sectionsCount }}
                    </span>
                </h1>

                <p class="flex items-center justify-center
                          text-center text-xl text-gray-500 dark:text-gray-400 text-md mb-4">
                    {{ rubric.short }}
                </p>

                <!-- Список статей -->
                <div v-if="sections.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="section in sections"
                        :key="section.id"
                        class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden hover:shadow-xl transition duration-300"
                    >
                        <div class="p-6">
                            <!-- Заголовок статьи -->
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                <Link :href="`/sections/${section.id}`"
                                      class="hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ section.title }}
                                </Link>
                            </h2>

                            <!-- Описание статьи -->
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                {{ section.description }}
                            </p>

                            <!-- Кнопка "Читать далее" -->
                            <div class="flex justify-end mt-4">
                                <Link :href="`/sections/${section.id}`"
                                      class="inline-block px-3 py-1 rounded-sm
                                             bg-blue-600 hover:bg-blue-800 dark:bg-indigo-600 dark:hover:bg-indigo-800
                                             font-semibold text-slate-100 text-sm transition duration-300">
                                    {{ $t('readMore') }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Если статей нет -->
                <p v-else class="text-gray-500 text-lg text-center">
                    {{ $t('noData') }}
                </p>
            </div>
        </div>
    </PublicLayout>
</template>
