<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import MainTournamentSlider from '@/Components/Public/Default/Tournament/MainTournamentSlider.vue';
import ScheduledTournaments from '@/Components/Public/Default/Tournament/ScheduledTournaments.vue';
import CompletedTournaments from '@/Components/Public/Default/Tournament/CompletedTournaments.vue';
import RubricSectionArticles from '@/Components/Public/Default/Article/RubricSectionArticles.vue';
import BannerImageSlider from '@/Components/Public/Default/Banner/BannerImageSlider.vue';
import MainBannerSlider from "@/Components/Public/Default/Banner/MainBannerSlider.vue";

const {t} = useI18n();

const {
    rubric,
    sections,
    sectionBanners,
    mainTournaments,
    scheduledTournaments,
    completedTournaments,
} = usePage().props;

</script>

<template>
    <DefaultLayout :title="rubric.title" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
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

        <div class="flex-1 p-4 selection:bg-red-400 selection:text-white bg-slate-50 dark:bg-blue-950">

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

            <!-- Слайдер турниров -->
            <MainTournamentSlider :tournaments="mainTournaments" :t="t"/>

            <!-- Турниры по статусу -->
            <ScheduledTournaments
                v-if="scheduledTournaments.length"
                :tournaments="scheduledTournaments"
            />

            <CompletedTournaments
                v-if="completedTournaments.length"
                :tournaments="completedTournaments"
            />

            <!-- Блок секций -->
            <div v-if="sections.length" class="space-y-8">

                <div v-for="section in sections" :key="section.id"
                     class="overflow-hidden">

                    <div class="p-1">

                        <!-- Список статей с Компонент пагинацией -->
                        <RubricSectionArticles
                            :section-title="section.title"
                            :articles="section.articles"
                            :items-per-page="16"
                        />

                    </div>

                </div>

            </div>

            <div v-else class="text-gray-500 text-lg text-center">
                {{ t('noData') }}
            </div>

            <MainBannerSlider
                v-if="sectionBanners && sectionBanners.length"
                :banners="sectionBanners"
            />

        </div>
    </DefaultLayout>
</template>
