<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import MainSlider from "@/Components/Public/Default/Article/MainSlider.vue";
import LeftColumn from "@/Components/Public/Default/Partials/LeftColumn.vue";
import SectionArticlesPagination from "@/Components/Public/Default/Article/SectionArticlesPagination.vue";
import BannerImageSlider from "@/Components/Public/Default/Banner/BannerImageSlider.vue";

const {t} = useI18n();

const { rubric, sections, activeArticlesCount } = usePage().props;
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

        <div class="flex-1 p-4
                         bg-white dark:bg-slate-800
                         selection:bg-red-400 selection:text-white">

            <div class="flex justify-center flex-col md:flex-row md:space-x-4">
                <MainSlider/>
                <LeftColumn/>
            </div>

            <!-- Заголовок рубрики -->
            <h1 class="flex items-center justify-center my-4
                           text-center font-bolder text-3xl
                           text-gray-900 dark:text-slate-100">

                <span v-if="rubric.icon" class="flex justify-center" v-html="rubric.icon"/>

                {{ rubric.title }}

                <span class="ml-2 px-1 py-0
                                 text-xs font-semibold text-white
                                 bg-emerald-500 rounded-sm">
                    {{ activeArticlesCount }}
                </span>

            </h1>

            <p class="flex items-center justify-center mb-4
                      tracking-wide text-center text-xl text-gray-700 dark:text-gray-300">
                {{ rubric.short }}
            </p>

            <!-- Блок секций -->
            <div v-if="sections.length" class="space-y-8">

                <div v-for="section in sections" :key="section.id"
                     class="bg-white dark:bg-gray-800 shadow-lg rounded-sm overflow-hidden">

                    <div class="p-6">

                        <!-- Заголовок секции -->
                        <h2 class="mb-2 text-2xl font-semibold text-amber-500 dark:text-red-200"
                            :title="section.short">
                            {{ section.title }}
                        </h2>

                        <!-- Список статей с Компонент пагинацией -->
                        <SectionArticlesPagination :articles="section.articles" :items-per-page="3" />

                        <!-- Если у секции есть баннеры, отображаем их -->
                        <div v-if="section.banners && section.banners.length" class="mt-4">
                            <div class="flex justify-center items-center flex-wrap">
                                <div v-for="banner in section.banners" :key="banner.id"
                                     class="w-full flex flex-col justify-center items-center">

                                    <h3 class="mb-3 tracking-wide text-2xl
                                               font-semibold text-slate-500 dark:text-slate-200">
                                        {{ banner.title }}
                                    </h3>

                                    <!-- Изображение баннера -->
                                    <div v-if="banner.images && banner.images.length > 0"
                                         class="overflow-hidden">
                                        <BannerImageSlider :images="banner.images" />
                                    </div>

                                    <p class="max-w-xl w-full mt-3 text-center p-1
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

    </PublicLayout>
</template>
