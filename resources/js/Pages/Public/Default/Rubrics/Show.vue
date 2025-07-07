<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import SectionArticles from "@/Components/Public/Default/Article/SectionArticles.vue";
import MainBannerSlider from "@/Components/Public/Default/Banner/MainBannerSlider.vue";

const {t} = useI18n();
const {rubric, sectionBanners, articles, pagination, locale} = usePage().props;
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

        <div class="flex-1 p-4 bg-slate-50 dark:bg-blue-950">

            <!-- Хлебные крошки -->
            <nav class="text-sm ml-0 md:ml-4 lg:ml-6 xl:ml-8 mb-4"
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

            <p v-if="rubric.short"
               class="flex items-center justify-center mb-4
                      tracking-wide text-center text-md text-gray-700 dark:text-gray-300">
                {{ rubric.short }}
            </p>

            <!-- Вывод статей -->
            <SectionArticles
                :articles="articles.data"
                :pagination="pagination"
                :base-url="`/${locale}/rubrics/${rubric.url}`"
                :search="$page.props.filters?.search ?? ''"
            />

            <!-- Баннеры -->
            <MainBannerSlider
                v-if="sectionBanners && sectionBanners.length"
                :banners="sectionBanners"
            />
        </div>
    </DefaultLayout>
</template>
