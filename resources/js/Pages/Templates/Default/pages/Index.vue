<script setup>
import {defineProps, shallowRef, onMounted, watch} from 'vue';
import axios from 'axios';
import DefaultLayout from '@/Pages/Templates/Default/layouts/DefaultLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import Maintenance from '@/Pages/Maintenance.vue';
import {useI18n} from 'vue-i18n';
import {useHead} from '@vueuse/head';

const {t} = useI18n();

const props = defineProps({
    page: Object,
    canLogin: Boolean,
    canRegister: Boolean,
});

const downtimeSite = shallowRef(false);
const currentComponent = shallowRef(null);

const addHtmlComments = () => {
    const head = document.head;
    const startComment = document.createComment(' Pulsar CMS ');
    const endComment = document.createComment(' Метатеги ');

    if (head.firstChild) {
        head.insertBefore(startComment, head.firstChild);
    } else {
        head.appendChild(startComment);
    }
    head.appendChild(endComment);
};

const fetchSettings = async () => {
    try {
        const response = await axios.get('/api/settings/downtimeSite');
        // console.log('Ответ API о состоянии заглушки:', response.data);
        downtimeSite.value = response.data.value === 'true';
        // console.log('Настройка downtimeSite после обработки:', downtimeSite.value);
    } catch (error) {
        // console.error('Ошибка при получении настроек:', error);
    } finally {
        updateComponent();
    }
};

const updateComponent = () => {
    if (downtimeSite.value) {
        // console.log('Сайт на техническом обслуживании. Переключение на Maintenance.vue');
        currentComponent.value = Maintenance;
    } else {
        currentComponent.value = DefaultLayout;
    }
};

const updateMetaTags = (page) => {
    useHead({
        title: page?.meta_title || 'DigitalPro', // Заголовок страницы
        meta: [
            {name: 'viewport', content: 'width=device-width, initial-scale=1'}, // Настройки viewport для мобильных устройств
            {name: 'description', content: page?.meta_desc || 'Описание страницы'}, // Описание страницы
            {name: 'keywords', content: page?.meta_keywords || 'Ключевые слова страницы'}, // Ключевые слова страницы
            {property: 'og:title', content: page?.meta_title || 'Open Graph заголовок'}, // Open Graph заголовок
            {property: 'og:description', content: page?.meta_desc || 'Open Graph описание'}, // Open Graph описание
            {property: 'og:image', content: page?.og_image || 'default-image.png'}, // Open Graph изображение
            {property: 'og:type', content: 'website'}, // Open Graph тип контента
            {property: 'og:url', content: window.location.href}, // Open Graph URL страницы
            {property: 'og:site_name', content: 'Pulsar CMS'}, // Open Graph название сайта
            {property: 'og:locale', content: 'ru_RU'}, // Open Graph локаль
            {name: 'DC.title', content: page?.meta_title || 'Dublin Core заголовок'}, // Dublin Core заголовок
            {name: 'DC.description', content: page?.meta_desc || 'Dublin Core описание'}, // Dublin Core описание
            {name: 'DC.subject', content: page?.meta_keywords || 'Dublin Core ключевые слова'}, // Dublin Core ключевые слова
            {name: 'DC.type', content: 'Text'}, // Dublin Core тип контента
            {name: 'DC.creator', content: 'Pulsar CMS'}, // Dublin Core создатель
            {name: 'DC.publisher', content: 'DigitalPro'}, // Dublin Core издатель
            {name: 'DC.date', content: new Date().toISOString()}, // Dublin Core дата публикации
            {name: 'twitter:card', content: 'summary_large_image'}, // Twitter Card тип
            {name: 'twitter:site', content: '@yoursite'}, // Twitter аккаунт сайта
            {name: 'twitter:title', content: page?.meta_title || 'Default Title'}, // Twitter заголовок
            {name: 'twitter:description', content: page?.meta_desc || 'Default description'}, // Twitter описание
            {name: 'twitter:image', content: page?.og_image || 'default-image.png'}, // Twitter изображение
            {name: 'author', content: 'Александр Косолапов'}, // Автор страницы
            {rel: 'canonical', href: window.location.href}, // Канонический URL страницы
        ]
    });
};

onMounted(() => {
    fetchSettings();
    addHtmlComments();
});

watch(() => props.page, (newPage) => {
    updateMetaTags(newPage);
}, {immediate: true});

watch(downtimeSite, (newValue) => {
    updateComponent();
});

</script>

<template>
    <component :is="currentComponent" :title="t('welcome')" :can-login="props.canLogin"
               :can-register="props.canRegister">
        <template #header>
            <TitlePage>
                {{ t('welcome') }}
            </TitlePage>
        </template>
        <div v-if="props.page">
            <h1 class="text-red-600 dark:text-red-100">{{ props.page.title }}</h1>
            <div class="text-slate-600 dark:text-slate-100" v-html="props.page.content"></div>
        </div>
        <div v-else>
            <p class="text-slate-600 dark:text-slate-100">Loading...</p>
        </div>
    </component>
</template>
