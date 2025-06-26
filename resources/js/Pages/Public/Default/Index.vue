<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import MainArticleSlider from '@/Components/Public/Default/Article/MainArticleSlider.vue';
import RightArticleList from "@/Components/Public/Default/Article/RightArticleList.vue";
import LatestArticleCard from "@/Components/Public/Default/Article/LatestArticleCard.vue";
import MainBannerSlider from "@/Components/Public/Default/Banner/MainBannerSlider.vue";
import ScheduledTournamentsIndex from "@/Components/Public/Default/Tournament/ScheduledTournamentsIndex.vue";
import CompletedTournamentsIndex from "@/Components/Public/Default/Tournament/ComletedTournamentsIndex.vue";
import SectionList from "@/Components/Public/Default/Section/SectionList.vue";
import HomeVideoGrid from "@/Components/Public/Default/Video/HomeVideoGrid.vue";


const {t} = useI18n();
const {locale} = usePage().props;
const {
    rightArticles,
    latestArticles,
    mainBanners,
    scheduledTournaments,
    completedTournaments,
    sections,
    videos,
    appUrl
} = usePage().props;

defineProps({
    title: String,
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <DefaultLayout
        :title="title"
        :can-login="canLogin"
        :can-register="canRegister"
    >
        <Head>
            <title>{{ t('home') }}</title>
            <!-- Основные метатеги, Open Graph, Twitter, Dublin Core, Schema.org и т.д. -->
            <meta name="title" content="Новости профессионального бокса — Ring"/>
            <meta name="description" content="Свежие новости профессионального бокса: актуальные события, интервью, аналитика и результаты боёв. Следите за последними тенденциями в мире бокса"/>
            <meta name="keywords" content="бокс, профессиональный бокс, новости бокса, интервью с боксёрами, аналитика боёв, результаты боёв, казахстанский бокс, мировые боксёрские события"/>
            <meta name="author" content="RING"/>
            <meta name="viewport" content="width=device-width, initial-scale=1"/>

            <!-- Open Graph / Facebook -->
            <meta property="og:title" content="Новости профессионального бокса — Ring"/>
            <meta property="og:description" content="Свежие новости профессионального бокса: актуальные события, интервью, аналитика и результаты боёв. Следите за последними тенденциями в мире бокса"/>
            <meta property="og:type" content="Новости"/>
            <meta property="og:url" :content="`/home`"/>
            <meta property="og:locale" :content="locale || 'ru_RU'"/>

            <!-- Twitter -->
            <meta name="twitter:title" content="Новости профессионального бокса — Ring"/>
            <meta name="twitter:description" content="Свежие новости профессионального бокса: актуальные события, интервью, аналитика и результаты боёв. Следите за последними тенденциями в мире бокса"/>

            <!-- Schema.org / Google -->
            <meta itemprop="name" content="Новости профессионального бокса — Ring"/>
            <meta itemprop="description" content="Свежие новости профессионального бокса: актуальные события, интервью, аналитика и результаты боёв. Следите за последними тенденциями в мире бокса"/>
        </Head>

        <div class="flex-1 p-4 selection:bg-red-400 selection:text-white bg-slate-50 dark:bg-blue-950">

            <!-- Хлебные крошки -->
            <nav class="text-sm ml-0 md:ml-4 lg:ml-6 xl:ml-8"
                 aria-label="Breadcrumb">
                <ol class="list-reset flex items-center space-x-0">
                    <li class="text-slate-900 dark:text-slate-100">
                        {{ t('home') }}
                    </li>
                    <li>
                        <span class="mx-1 text-slate-900 dark:text-slate-100">/</span>
                    </li>
                </ol>
            </nav>

            <div class="space-y-8">
                <div class="overflow-hidden">
                    <div class="p-0 xl:p-6">

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

                            <MainArticleSlider/>

                            <!-- Правая колонка -->
                            <RightArticleList :articles="rightArticles" :app-url="appUrl"/>

                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <LatestArticleCard
                                v-for="article in latestArticles"
                                :key="article.id"
                                :article="article"
                                :app-url="appUrl"
                            />
                        </div>

                        <MainBannerSlider :banners="mainBanners" />

                        <ScheduledTournamentsIndex :tournaments="scheduledTournaments" />

                        <CompletedTournamentsIndex :tournaments="completedTournaments" />

                        <SectionList :sections="sections" :app-url="appUrl" />

                        <HomeVideoGrid :videos="videos" />

                    </div>
                </div>

            </div>
        </div>
    </DefaultLayout>
</template>

<style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}

@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style>
