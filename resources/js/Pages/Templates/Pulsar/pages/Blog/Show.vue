<script setup>
import {defineProps, ref, onMounted} from 'vue';
import axios from 'axios';
import {useHead} from '@vueuse/head';
import {useI18n} from 'vue-i18n';
import {format} from 'date-fns';
import {ru} from 'date-fns/locale';
import DefaultLayout from "./../../layouts/DefaultLayout.vue";
import CommentSection from "./../../components/Blog/CommentSection.vue";
import TitlePage from "@/Components/Admin/Headlines/TitlePage.vue";

const {t} = useI18n();

// Получаем статью, данные пользователя и авторизации через пропсы
const props = defineProps({
    article: Object,
    auth: Object,
    user: Object, // Информация о текущем пользователе
});

const comments = ref([]); // Массив для комментариев
const isLoading = ref(false);  // Статус загрузки комментариев
const isLiked = ref(false); // Статус лайка (относится к текущей статье)
const likeCount = ref(props.article.likes); // Количество лайков (инициализируется значением статьи)

// Метод для проверки, лайкнул ли пользователь текущую статью
const checkIfLiked = async () => {
    if (props.user && props.user.id) {
        try {
            const response = await axios.get(`/api/articles/${props.article.id}/is-liked`, {
                params: {user_id: props.user.id}, // Передаём ID пользователя
            });
            isLiked.value = response.data.liked; // Обновляем состояние
            //console.log(`Статья ID: ${props.article.id}, Лайкнул: ${response.data.liked}`);
        } catch (error) {
            console.error('Ошибка при проверке лайка:', error);
        }
    } else {
        isLiked.value = false; // Если пользователь не авторизован
    }
};

// Метод для отправки лайка
const likeArticle = async () => {
    if (!props.user || !props.user.id) {
        alert('Вы должны быть авторизованы для лайка статьи.');
        return;
    }

    try {
        // Передаем user_id и article_id в запросе
        const response = await axios.post(`/api/articles/${props.article.id}/like`, {
            user_id: props.user.id,
            article_id: props.article.id
        });
        likeCount.value = response.data.likes; // Обновляем количество лайков
        isLiked.value = true; // Обновляем состояние, что лайк был поставлен
    } catch (error) {
        if (error.response && error.response.status === 400) {
            isLiked.value = true; // Статья уже была лайкнута
            alert('Вы уже лайкнули эту статью.');
        } else {
            console.error('Ошибка при лайке статьи:', error);
        }
    }
};

// Форматирование даты
const formatDate = (date) => {
    return date ? format(new Date(date), 'dd MMMM yyyy', {locale: ru}) : '';
};

// Изображение статьи
const imagePath = (path) => {
    if (!path) {
        return '/storage/article_images/default-image.png';
    }
    if (path.startsWith('http') || path.startsWith('https')) {
        return path;
    }
    return `/storage/${path}`;
};

// Устанавливаем метатеги с помощью vueuse/head
useHead({
    title: props.article?.meta_title || 'Заголовок статьи',
    meta: [
        {name: 'description', content: props.article?.meta_desc || 'Описание'},
        {name: 'keywords', content: props.article?.meta_keywords || 'Ключевые слова'},
        {property: 'og:title', content: props.article?.meta_title || 'Заголовок статьи'},
        {property: 'og:description', content: props.article?.meta_desc || 'Описание'},
        {property: 'og:image', content: props.article?.image_url || '/default.jpg'},
        {property: 'og:type', content: 'article'},
    ],
});

// Загрузка комментариев при монтировании компонента
const loadComments = async () => {
    isLoading.value = true;
    try {
        if (props.article?.id) {
            const response = await axios.get(`/api/comments/${props.article.id}`);
            comments.value = response.data || []; // Получаем комментарии с сервера
        }
    } catch (error) {
        console.error('Ошибка при загрузке комментариев:', error);
    } finally {
        isLoading.value = false;
    }
};

// Проверка лайков и загрузка комментариев при монтировании компонента
onMounted(() => {
    if (props.article?.id) {
        loadComments();
        checkIfLiked(); // Проверяем статус лайка при монтировании
    }
});
</script>

<template>
    <DefaultLayout :title="props.article?.title || 'Статья'">
        <template #header>
            <TitlePage>
                {{ props.article?.title || 'Заголовок статьи' }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full">
            <div class="max-w-5xl mx-auto flex flex-col">

                <!-- Статья -->
                <div>
                    <div class="mb-6">
                        <a class="w-fit flex items-center
                                  btn-sm px-3 bg-slate-100 dark:bg-slate-700
                                  rounded border border-slate-200 hover:border-slate-300
                                  text-slate-600 dark:text-slate-100"
                           :href="route('blog')">
                            <svg class="fill-current text-slate-400 mr-2" width="7" height="12" viewBox="0 0 7 12">
                                <path d="M5.4.6 6.8 2l-4 4 4 4-1.4 1.4L0 6z"/>
                            </svg>
                            <span>{{ t('back') }}</span>
                        </a>
                        <div class="text-sm font-semibold text-indigo-500 uppercase mb-2 float-right">
                            {{ formatDate(props.article.created_at) }}
                        </div>
                    </div>
                    <h1 class="text-center text-2xl font-semibold md:text-3xl
                               text-teal-700 dark:text-teal-300 mb-2">
                        {{ props.article.title }}
                    </h1>
                    <!-- Image -->
                    <figure class="mb-6">
                        <img class="w-full h-auto rounded-sm"
                             :alt="props.article.seo_alt"
                             :title="props.article.seo_title"
                             :src="imagePath(props.article.image_url)"/>
                        <div class="flex w-fit float-right my-2">

                            <!-- лайки -->
                            <button @click="likeArticle" :disabled="isLiked" :title="t('likes')"
                                    class="flex items-center
                                           text-slate-400 hover:text-indigo-500
                                           dark:text-slate-200 dark:hover:text-indigo-300">
                                <svg class="w-4 h-4 shrink-0 fill-current mr-1.5"
                                     :class="isLiked ? 'text-rose-500' : 'text-slate-400'"
                                     viewBox="0 0 16 16">
                                    <path d="M14.682 2.318A4.485 4.485 0 0011.5 1 4.377 4.377 0 008 2.707 4.383 4.383 0 004.5 1a4.5 4.5 0 00-3.182 7.682L8 15l6.682-6.318a4.5 4.5 0 000-6.364zm-1.4 4.933L8 12.247l-5.285-5A2.5 2.5 0 014.5 3c1.437 0 2.312.681 3.5 2.625C9.187 3.681 10.062 3 11.5 3a2.5 2.5 0 011.785 4.251h-.003z"></path>
                                </svg>
                                <span class="text-sm font-semibold"
                                      :class="isLiked ? 'text-teal-500' : 'text-slate-500'">
                                    {{ likeCount }}
                                </span>
                            </button>

                            <!-- просмотры -->
                            <button :title="t('views')"
                                    class="flex items-center ml-4
                                           text-slate-400 hover:text-indigo-500
                                           dark:text-slate-200 dark:hover:text-indigo-300">
                                <svg class="w-4 h-4 shrink-0 fill-current mr-1.5" viewBox="0 0 16 16">
                                    <path d="M13 7h2v6a1 1 0 0 1-1 1H4v2l-4-3 4-3v2h9V7ZM3 9H1V3a1 1 0 0 1 1-1h10V0 l4 3-4 3V4 H3 v5 Z"></path>
                                </svg>
                                <span class="text-sm text-slate-500 dark:text-slate-300">
                                    {{ props.article.views }}
                                </span>
                            </button>

                            <!-- количество комментариев -->
                            <button :title="t('comments')"
                                    class="flex items-center ml-4
                                           text-slate-400 hover:text-indigo-500
                                           dark:text-slate-200 dark:hover:text-indigo-300">
                                <svg class="w-4 h-4 shrink-0 fill-current mr-1.5" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.1 0 7s3.6 7 8 7h.6l5.4 2v-4.4c1.2-1.2 2-2.8 2-4.6 0-3.9-3.6-7-8-7zm4 10.8v2.3L8.9 12H8 c-3.3 0-6-2.2-6-5s2.7-5 6-5 6 2.2 6 5 c0 2.2-2 3.8-2 3.8 z"></path>
                                </svg>
                                <span class="text-sm text-slate-500 dark:text-slate-300">{{ comments.length }}</span>
                            </button>

                        </div>
                    </figure>
                    <div class="text-xl leading-snug text-gray-700 dark:text-gray-100 my-8"
                         v-html="props.article.description"></div>
                </div>

                <!-- Комментарии -->
                <CommentSection :articleId="props.article.id" :auth="props.auth"/>

            </div>
        </div>
    </DefaultLayout>
</template>
