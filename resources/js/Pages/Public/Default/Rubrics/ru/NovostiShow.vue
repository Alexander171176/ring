<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import {ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import BannerImageSlider from "@/Components/Public/Default/Banner/BannerImageSlider.vue";
import SectionArticles from "@/Components/Public/Default/Article/SectionArticles.vue";

const { t } = useI18n();

const { rubric, sections } = usePage().props;

// Реактивная переменная для поискового запроса
const searchQuery = ref('');

// Вычисляемое свойство, которое фильтрует секции по статьям, название которых содержит запрос
const filteredSections = computed(() => {
    if (!searchQuery.value.trim()) {
        return sections;
    }
    const query = searchQuery.value.toLowerCase();
    // Для каждой секции оставляем только те статьи, в названии которых есть запрос
    return sections
        .map(section => {
            return {
                ...section,
                articles: section.articles.filter(article =>
                    article.title.toLowerCase().includes(query)
                )
            };
        })
        .filter(section => section.articles.length > 0); // Отбрасываем секции без результатов
});

</script>

<template>
    <DefaultLayout :title="rubric.title"
                   :can-login="$page.props.canLogin"
                   :can-register="$page.props.canRegister">

        <!-- SEO -->
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
                        {{ rubric.title }}
                    </li>
                </ol>
            </nav>

            <!-- Заголовок рубрики -->
            <h1 class="flex items-center justify-center my-4
                       text-center font-bolder text-xl
                       text-slate-900 dark:text-slate-100">
                <span v-if="rubric.icon" class="flex justify-center" v-html="rubric.icon"/>
                {{ rubric.title }}
            </h1>

            <!-- Строка поиска -->
            <div class="max-w-xl mx-auto">
                <input v-model="searchQuery" type="text" :placeholder="t('searchByName')"
                       class="w-full px-3 py-0.5 bg-white dark:bg-gray-700
                              font-semibold text-sm text-slate-600 dark:text-slate-100
                              border border-slate-500 dark:border-slate-400 rounded-sm
                              focus:outline-none focus:ring-1 focus:border-blue-300"
                />
            </div>

            <!-- Блок секций -->
            <div v-if="filteredSections.length" class="space-y-8">

                <div v-for="section in filteredSections" :key="section.id"
                     class="overflow-hidden">

                    <div class="p-6">

                        <!-- Список статей с Компонент пагинацией -->
                        <SectionArticles
                            :articles="section.articles"
                            :items-per-page="10"
                        />

                        <!-- Если у секции есть баннеры, отображаем их -->
                        <div v-if="section.banners && section.banners.length" class="mt-4">
                            <div class="flex justify-center items-center flex-wrap">
                                <div v-for="banner in section.banners" :key="banner.id"
                                     class="w-full flex flex-col justify-center items-center">

                                    <!-- Название баннера -->
                                    <template v-if="banner.link">
                                        <Link :href="banner.link">
                                            <h3 class="mb-3 tracking-wide text-2xl
                                                       font-semibold text-slate-500 dark:text-slate-200">
                                                {{ banner.title }}
                                            </h3>
                                        </Link>
                                    </template>
                                    <template v-else>
                                        <h3 class="mb-3 tracking-wide text-xl
                                                   font-semibold text-slate-500 dark:text-slate-200">
                                            {{ banner.title }}
                                        </h3>
                                    </template>

                                    <!-- Изображение баннера -->
                                    <!-- Если banner.link не пустой, оборачиваем слайдер в ссылку, иначе просто выводим слайдер -->
                                    <div v-if="banner.images && banner.images.length > 0">
                                        <template v-if="banner.link">
                                            <Link :href="banner.link">
                                                <BannerImageSlider :images="banner.images" />
                                            </Link>
                                        </template>
                                        <template v-else>
                                            <BannerImageSlider :images="banner.images" />
                                        </template>
                                    </div>

                                    <p v-if="banner.short"
                                       class="max-w-xl w-full mt-3 text-center p-1
                                              tracking-wider text-lg font-semibold text-slate-600 dark:text-slate-300">
                                        {{ banner.short }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div v-else class="text-gray-500 text-lg text-center">
                {{ t('noData') }}
            </div>

        </div>

    </DefaultLayout>
</template>
