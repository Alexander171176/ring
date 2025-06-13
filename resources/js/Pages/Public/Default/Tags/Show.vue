<script setup>
import {computed, onMounted, onUnmounted, ref} from "vue";
import {Head, Link, usePage} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import SectionArticlesPagination from "@/Components/Public/Default/Article/SectionArticlesPagination.vue";

const {t} = useI18n();
const {tag, articles, articlesCount} = usePage().props;

// Реактивная переменная для поискового запроса
const searchQuery = ref('');

// Вычисляемое свойство для фильтрации статей по названию
const filteredArticles = computed(() => {
    if (!searchQuery.value.trim()) {
        return articles;
    }
    const query = searchQuery.value.toLowerCase();
    return articles.filter(article =>
        article.title.toLowerCase().includes(query)
    );
});
</script>

<template>
    <DefaultLayout :title="tag.name" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
        <Head>
            <title>{{ tag.name }}</title>
            <meta name="title" :content="tag.name || ''"/>
            <meta name="keywords" :content="tag.meta_keywords || ''"/>
            <meta name="description" :content="tag.meta_desc || ''"/>

            <meta property="og:title" :content="tag.name || ''"/>
            <meta property="og:description" :content="tag.meta_desc || ''"/>
            <meta property="og:type" content="website"/>
            <meta property="og:url" :content="`/tags/${tag.url}`"/>
            <meta property="og:locale" :content="tag.locale || 'ru_RU'"/>

            <meta name="twitter:card" content="summary_large_image"/>
            <meta name="twitter:title" :content="tag.name || ''"/>
            <meta name="twitter:description" :content="tag.meta_desc || ''"/>

            <meta name="DC.title" :content="tag.name || ''"/>
            <meta name="DC.description" :content="tag.meta_desc || ''"/>
            <meta name="DC.identifier" :content="`/tags/${tag.url}`"/>
            <meta name="DC.language" :content="tag.locale || 'ru'"/>
        </Head>

        <div class="flex-1 p-4 selection:bg-red-400 selection:text-white bg-slate-50 dark:bg-slate-950">

            <!-- Хлебные крошки -->
            <nav class="text-sm ml-0 md:ml-4 lg:ml-6 xl:ml-8"
                 aria-label="Breadcrumb">
                <ol class="list-reset flex items-center space-x-0">
                    <li>
                        <Link href="/" class="hover:underline text-slate-900 dark:text-slate-100">
                            {{ t('home') }}
                        </Link>
                    </li>
                    <li>
                        <span class="mx-1 text-slate-900 dark:text-slate-100">/</span>
                    </li>
                    <li class="text-slate-900 dark:text-slate-100">
                        {{ tag.name }}
                    </li>
                </ol>
            </nav>

            <!-- Заголовок тега -->
            <h1 class="flex items-center justify-center my-4
                       text-center font-bolder text-xl
                       text-slate-900 dark:text-slate-100">
                {{ tag.name }}
                <span class="ml-2 px-1 py-0 text-xs font-semibold text-white bg-emerald-500 rounded-sm">
                  {{ articlesCount }}
                </span>
            </h1>

            <!-- Краткое описание тега, если есть -->
            <p v-if="tag.short"
               class="flex items-center justify-center mb-4
                      tracking-wide text-center text-md text-gray-700 dark:text-gray-300">
                {{ tag.short }}
            </p>

            <!-- Строка поиска -->
            <div class="mb-2 max-w-3xl mx-auto">
                <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="t('searchByName')"
                    class="w-full px-3 py-0.5 bg-white dark:bg-gray-700
                           font-semibold text-sm text-slate-600 dark:text-slate-100
                           border border-slate-500 dark:border-slate-400 rounded-sm
                           focus:outline-none focus:ring-1 focus:border-blue-300"
                />
            </div>

            <!-- Список статей, связанных с тегом -->
            <div v-if="filteredArticles.length" class="space-y-8">

                <div class="overflow-hidden">
                    <div class="p-6">

                        <SectionArticlesPagination
                            :articles="filteredArticles"
                            :items-per-page="10"
                        />

                    </div>
                </div>

            </div>
            <div v-else class="text-gray-500 text-lg text-center">
                {{ t('noData') }}
            </div>
        </div>
    </DefaultLayout>
</template>
