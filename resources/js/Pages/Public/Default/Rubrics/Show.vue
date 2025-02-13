<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const {rubric, articles, articlesCount} = usePage().props;

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
            <title>{{ rubric.title }}</title>
            <meta name="description" :content="rubric.description || 'Default description'"/>
        </Head>

        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen
                    bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-slate-900
                    selection:bg-red-500 selection:text-white">
            <div class="max-w-8xl mx-auto">

                <!-- Название рубрики -->
                <h1 class="text-center text-3xl font-bolder text-gray-900 mb-4">
                    {{ rubric.title }}
                </h1>

                <!-- Количество статей -->
                <p class="text-gray-600 text-sm mb-6">
                    {{ $t('posts') }}: {{ articlesCount }}
                </p>

                <!-- Список статей -->
                <div v-if="articles.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="article in articles"
                        :key="article.id"
                        class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden hover:shadow-xl transition duration-300"
                    >
                        <div class="p-6">
                            <!-- Заголовок статьи -->
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                <Link :href="`/articles/${article.id}`"
                                      class="hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ article.title }}
                                </Link>
                            </h2>

                            <!-- Описание статьи -->
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                {{ article.description }}
                            </p>

                            <!-- Кнопка "Читать далее" -->
                            <div class="mt-4">
                                <Link
                                    :href="`/articles/${article.id}`"
                                    class="inline-block bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition duration-300"
                                >
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
