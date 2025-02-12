<script setup>
import { Head } from "@inertiajs/vue3";
import Header from "@/Partials/Public/Header.vue";
import Footer from "@/Partials/Public/Footer.vue";

const props = defineProps({
    title: String,
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head :title="title" />

    <!-- Импортируем Header и передаем пропсы -->
    <Header :can-login="canLogin" :can-register="canRegister" />

    <main>
        <slot />
    </main>

    <Footer />

</template>
