<script setup>
import {Head, usePage} from "@inertiajs/vue3";
import {defineProps} from 'vue';
import {useSetting} from '@/composables/settings.js';
import {usePlugin} from '@/composables/plugins.js';
import Header from "@/Partials/Default/Header.vue";
import Footer from "@/Partials/Default/Footer.vue";
import YandexMetrika from "@/Components/System/YandexMetrika.vue";
import LiveInternetCounter from "@/Components/System/LiveInternetCounter.vue";

const {siteSettings} = usePage().props;

const props = defineProps({
    title: String,
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const {hasPlugin} = usePlugin();
const {hasSetting} = useSetting();

const {props: pageProps} = usePage();

</script>

<template>
    <Head :title="title"/>

    <!-- Импортируем Header и передаем пропсы -->
    <Header :can-login="canLogin" :can-register="canRegister"/>

    <main class="min-h-screen flex justify-center flex-col lg:flex-row tracking-wider bg-slate-50 dark:bg-blue-950">
        <slot/>
    </main>

    <div class="flex items-center justify-center gap-1 bg-slate-50 dark:bg-blue-950">
        <LiveInternetCounter/>
        <YandexMetrika/>
    </div>

    <Footer :can-login="canLogin" :can-register="canRegister"/>

</template>
