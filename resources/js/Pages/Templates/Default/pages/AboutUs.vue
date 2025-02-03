<script setup>
import {defineProps, onMounted} from "vue";
import {useHead} from '@vueuse/head';
import {useI18n} from 'vue-i18n';
import DefaultLayout from "@/Pages/Templates/Default/layouts/DefaultLayout.vue";
import TitlePage from "@/Components/Admin/Headlines/TitlePage.vue";

const {t} = useI18n();

const props = defineProps({
    page: Object,
    sections: {
        type: Array,
        default: () => [],
    }
});

// монтируем метатеги страницы
onMounted(() => {
    if (props.page) {
        useHead({
            title: props.page.title,
            meta: [
                { name: 'viewport', content: 'width=device-width, initial-scale=1' },
                { name: 'description', content: props.page.meta_desc },
                { name: 'keywords', content: props.page.meta_keywords },
                { property: 'og:title', content: props.page.title },
                {
                    property: 'og:description',
                    content: props.page.og_description || 'О нас'
                }
            ]
        });
    }
});
</script>

<template>
    <DefaultLayout :title="t('abouts')">

        <template #header>
            <TitlePage>
                {{ t('abouts') }}
            </TitlePage>
        </template>

        <div class="max-w-7xl mx-auto">

            <div class="px-1 py-3">
                <h1 class="text-center text-4xl text-gray-700 dark:text-slate-200 px-2 py-1">
                    {{ t('abouts') }}
                </h1>
                <p class="text-2xl font-semibold text-orange-400 my-4 px-2 py-1"
                   v-html="props.page.content">
                </p>
            </div>

            <!-- Динамическое отображение всех секций -->
            <section
                v-for="section in sections"
                :key="section.id"
                :id="'section-' + section.id"
                :name="section.type"
                :class="`mb-6 ${section.tailwind}`"
                :style="{ backgroundImage: section.image ? `url(/storage/${section.image})` : 'none' }"
            >
                <h2 class="text-2xl font-bold text-sky-600">{{ section.title }}</h2>
                <div v-html="section.content" class="italic text-lg text-slate-900 dark:text-slate-100 mt-2"></div>
            </section>

        </div>

    </DefaultLayout>
</template>

<style scoped></style>
