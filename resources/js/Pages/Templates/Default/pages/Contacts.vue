<script setup>
import {defineProps, onMounted} from "vue";
import {useHead} from '@vueuse/head';
import {useI18n} from 'vue-i18n';
import DefaultLayout from "@/Pages/Templates/Default/layouts/DefaultLayout.vue";
import TitlePage from "@/Components/Admin/Headlines/TitlePage.vue";

const {t} = useI18n();

const props = defineProps({
    page: Object,
    contacts: {
        type: Array,
        default: () => [],
    }
});

// Проверка данных props.page и props.contacts
onMounted(() => {
    // console.log("Props page:", props.page);
    // console.log("Props contacts:", props.contacts);

    if (props.page) {
        useHead({
            title: props.page.title,
            meta: [
                {name: 'viewport', content: 'width=device-width, initial-scale=1'},
                {name: 'description', content: props.page.meta_desc},
                {name: 'keywords', content: props.page.meta_keywords},
                {property: 'og:title', content: props.page.title},
                {
                    property: 'og:description',
                    content: props.page.og_description || 'Контакты'
                }
            ]
        });
    }
});
</script>

<template>
    <DefaultLayout :title="t('contacts')">

        <template #header>
            <TitlePage>
                {{ t('contacts') }}
            </TitlePage>
        </template>

        <div class="bg-gray-100 dark:bg-slate-900 shadow-xl sm:rounded-lg">
            <div class="flex justify-center bg-white dark:bg-slate-700 shadow-lg rounded-sm relative">
                <div class="w-full flex justify-center
                            bg-white dark:bg-slate-700
                            shadow-lg rounded-sm relative">

                    <!-- Динамическое отображение всех секций -->
                    <section
                        v-for="contact in contacts"
                        :key="contact.id"
                        :id="'section-' + contact.id"
                        :class="`mb-1 ${contact.tailwind}`"
                        :style="{
                                backgroundImage: contact.image ? `url(/storage/${contact.image})` : 'none',
                                backgroundSize: 'contain',
                                backgroundRepeat: 'no-repeat',
                                backgroundPosition: 'center',
                                backgroundClip: 'content-box',
                                width: '800px',
                                height: '420px',
                                padding: '20px'
                                }">
                        <h2 class="my-4 text-center text-2xl font-bold text-sky-600 dark:text-sky-500">
                            {{ contact.title }}
                        </h2>
                        <strong v-html="contact.content" />
                    </section>

                </div>
            </div>
        </div>

    </DefaultLayout>
</template>

<style scoped></style>
