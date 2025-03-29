<script setup>
import {defineProps} from 'vue';
import {useRubric} from '@/composables/rubrics.js';
import {useArticle} from "@/composables/articles.js";
import {useBanner} from "@/composables/banners.js";
import {Head, usePage} from "@inertiajs/vue3";
import Header from "@/Partials/Public/Header.vue";
import LeftSidebar from "@/Components/Public/Default/Partials/LeftSidebar.vue";
import RightSidebar from "@/Components/Public/Default/Partials/RightSidebar.vue";
import Footer from "@/Partials/Public/Footer.vue";

const props = defineProps({
    title: String,
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const {HasRubric} = useRubric();
const {HasArticle} = useArticle();
const {HasBanner} = useBanner();

const {props: pageProps} = usePage();
</script>

<template>
    <Head :title="title"/>

    <!-- Импортируем Header и передаем пропсы -->
    <Header :can-login="canLogin" :can-register="canRegister"/>

    <main class="min-h-screen flex justify-center flex-col lg:flex-row tracking-wider">
        <!-- Левый сайдбар -->
        <LeftSidebar/>

        <slot/>

        <!-- Правый сайдбар -->
        <RightSidebar/>
    </main>

    <Footer :can-login="canLogin" :can-register="canRegister"/>

</template>
