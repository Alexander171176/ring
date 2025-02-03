<template>
    <div v-if="page">
        <span>Страница из шаблона Pulsar</span>
        <h1>{{ page.title }}</h1>
        <div v-html="page.content"></div>
    </div>
    <div v-else>
        <p>Loading...</p>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    page: Object
});
</script>
