<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import RightSidebar from "@/Components/Public/Default/Partials/RightSidebar.vue";
import {useI18n} from 'vue-i18n';
import MainSlider from "@/Components/Public/Default/Article/MainSlider.vue";
import RightColumn from "@/Components/Public/Default/Partials/RightColumn.vue";

const {t} = useI18n();

const {article} = usePage().props;
</script>

<template>
    <PublicLayout :title="article.title" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
        <Head>
            <title>{{ article.title }}</title>
            <meta name="title" :content="article.title || ''"/>
            <meta name="keywords" :content="article.meta_keywords || ''"/>
            <meta name="description" :content="article.meta_desc || ''"/>

            <meta property="og:title" :content="article.title || ''"/>
            <meta property="og:description" :content="article.meta_desc || ''"/>
            <meta property="og:type" content="website"/>
            <meta property="og:url" :content="`/articles/${article.url}`"/>
            <meta property="og:image" :content="article.icon || ''"/>
            <meta property="og:locale" :content="article.locale || 'ru_RU'"/>

            <meta name="twitter:card" content="summary_large_image"/>
            <meta name="twitter:title" :content="article.title || ''"/>
            <meta name="twitter:description" :content="article.meta_desc || ''"/>
            <meta name="twitter:image" :content="article.icon || ''"/>

            <meta name="DC.title" :content="article.title || ''"/>
            <meta name="DC.description" :content="article.meta_desc || ''"/>
            <meta name="DC.identifier" :content="`/articles/${article.url}`"/>
            <meta name="DC.language" :content="article.locale || 'ru'"/>
        </Head>

        <main class="min-h-screen flex flex-col lg:flex-row tracking-wider">

            <div class="flex-1 p-4
                         bg-slate-100 dark:bg-slate-800
                         selection:bg-red-400 selection:text-white">

                <!-- Заголовок статьи -->
                <h1 class="flex items-center justify-center my-4
                           text-center font-bolder text-3xl
                           text-gray-900 dark:text-slate-100">
                    {{ article.title }}
                </h1>

                <p class="flex items-center justify-center mb-4
                          text-center text-xl text-gray-500 dark:text-gray-400">
                    {{ article.short }}
                </p>

            </div>

        </main>

    </PublicLayout>
</template>
