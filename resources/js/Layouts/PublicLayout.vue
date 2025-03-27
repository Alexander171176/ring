<script setup>
import {defineProps} from 'vue';
import {Head, usePage} from "@inertiajs/vue3";
import Header from "@/Partials/Public/Header.vue";
import Footer from "@/Partials/Public/Footer.vue";
import {useRubric} from '@/composables/rubrics.js';
import {useArticle} from "@/composables/articles.js";
import RightSidebar from "@/Components/Public/Default/Partials/RightSidebar.vue";

const props = defineProps({
    title: String,
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const {HasRubric} = useRubric();
const {HasArticle} = useArticle();

const {props: pageProps} = usePage();
</script>

<template>
    <Head :title="title"/>

    <!-- Импортируем Header и передаем пропсы -->
    <Header :can-login="canLogin" :can-register="canRegister"/>

    <main class="min-h-screen flex justify-center flex-col lg:flex-row tracking-wider">
        <slot/>

        <!-- Правый сайдбар -->
        <RightSidebar/>
    </main>

    <Footer :can-login="canLogin" :can-register="canRegister"/>

</template>
