<script setup>
import {Head, usePage} from "@inertiajs/vue3";
import {defineProps} from 'vue';
import {useRubrics} from '@/composables/rubrics.js';
import {useSection} from '@/composables/sections.js';
import {useArticle} from '@/composables/articles.js';
import {useBanners} from '@/composables/banners.js';
import {useVideos} from '@/composables/videos.js';
import {useSetting} from '@/composables/settings.js';
import {usePlugin} from '@/composables/plugins.js';
import Header from "@/Partials/Default/Header.vue";
import LeftSidebar from "@/Components/Public/Default/Partials/LeftSidebar.vue";
import RightSidebar from "@/Components/Public/Default/Partials/RightSidebar.vue";
import Footer from "@/Partials/Default/Footer.vue";

const { siteSettings } = usePage().props;
const props = defineProps({
    title: String,
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const {hasPlugin} = usePlugin();
const {hasSetting} = useSetting();
const {HasRubric} = useRubrics();
const {HasVideosSection} = useSection();
const {HasSection} = useArticle();
const {HasTags} = useArticle();
const {HasBanners} = useBanners();
const {HasVideos} = useVideos();

const {props: pageProps} = usePage();

</script>

<template>
    <Head :title="title"/>

    <!-- Импортируем Header и передаем пропсы -->
    <Header :can-login="canLogin" :can-register="canRegister"/>

    <main class="min-h-screen flex justify-center flex-col lg:flex-row tracking-wider">
        <!-- Левый сайдбар: не показываем, если параметр равен "false" -->
        <LeftSidebar v-if="!siteSettings.ViewLeftColumn || siteSettings.ViewLeftColumn === 'true'" />

        <slot/>

        <!-- Правый сайдбар: не показываем, если параметр равен "false" -->
        <RightSidebar v-if="!siteSettings.ViewRightColumn || siteSettings.ViewRightColumn === 'true'" />
    </main>

    <Footer :can-login="canLogin" :can-register="canRegister"/>

</template>
