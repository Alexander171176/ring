<script setup>
import {ref, defineProps, onMounted, computed} from "vue";
import {Link} from '@inertiajs/vue3';
import {useHead} from '@vueuse/head';
import {useI18n} from 'vue-i18n';
import {format} from 'date-fns';
import {ru} from 'date-fns/locale';
import DefaultLayout from "./../../layouts/DefaultLayout.vue";
import TitlePage from "@/Components/Admin/Headlines/TitlePage.vue";
import LikeButton from './../../components/Button/LikeButton.vue';
import ViewButton from './../../components/Button/ViewButton.vue';
import CommentButton from './../../components/Button/CommentButton.vue';

const {t} = useI18n();

const props = defineProps({
    page: Object,
    articles: {
        type: Array,
        default: () => [],
    },
    rubrics: {
        type: Array,
        default: () => [],
    },
    user: Object,
});

// поиск
const searchQuery = ref('');
// пагинация
const currentPage = ref(1);
const pageSize = ref(6); // показ количества карточек на одной странице
const isLiked = ref({}); // Статус лайков для каждой статьи
const likeCount = ref({}); // Количество лайков для каждой статьи

// Метод проверки, поставил ли пользователь лайк для каждой статьи
const checkIfLiked = async (articleId) => {
    if (props.user && props.user.id) {
        try {
            const response = await axios.get(`/api/articles/${articleId}/is-liked`, {
                params: {
                    user_id: props.user.id, // передаем ID пользователя напрямую
                },
            });
            // console.log(`Статья ID: ${articleId}, Лайкнул: ${response.data.liked}`);
            isLiked.value[articleId] = response.data.liked; // обновляем состояние
        } catch (error) {
            console.error('Ошибка при проверке лайка:', error);
        }
    } else {
        isLiked.value[articleId] = false; // если пользователь не авторизован
    }
};

// Метод для отправки лайка
const likeArticle = async (articleId) => {
    if (!props.user || !props.user.id) {
        alert('Вы должны быть авторизованы для лайка статьи.');
        return;
    }

    try {
        const response = await axios.post(`/api/articles/${articleId}/like`, {
            user_id: props.user.id,  // Передаем user_id в тело запроса
        });
        likeCount.value[articleId] = response.data.likes;  // Обновляем количество лайков
        isLiked.value[articleId] = true;  // Обновляем состояние, что лайк был поставлен
    } catch (error) {
        if (error.response && error.response.status === 400) {
            isLiked.value[articleId] = true;  // Статья уже была лайкнута
            alert('Вы уже лайкнули эту статью.');
        } else {
            console.error('Ошибка при лайке статьи:', error);
        }
    }
};

// формтирование даты создания в человекопонятный формат
const formatDate = (date) => {
    return format(new Date(date), 'dd MMMM yyyy', {locale: ru});
};

// путь к изображению по умолчанию
const imagePath = (path) => {
    if (!path) {
        return '/storage/article_images/default-image.png';
    }
    if (path.startsWith('http') || path.startsWith('https')) {
        return path;
    }
    return `/storage/${path}`;
};

// фильтрация статей по поиску
const filteredArticles = computed(() => {
    return props.articles.filter(article => article.title.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

// пагинация
const paginatedArticles = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    const end = start + pageSize.value;
    return filteredArticles.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredArticles.value.length / pageSize.value);
});

const goToPage = (page) => {
    currentPage.value = page;
};

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
                    content: props.page.og_description || 'Последние новости и лучшие лайф-хаки из мира ИТ разработчиков и консультантов'
                }
            ]
        });
    }

    // Выводим в консоль пропс user
    // console.log('Пользователь:', props.user);

    // Инициализация количества лайков и статуса для каждой статьи
    props.articles.forEach(article => {
        likeCount.value[article.id] = article.likes || 0; // Инициализируем количество лайков
        checkIfLiked(article.id); // Проверяем статус лайка для каждой статьи
    });
});

</script>

<template>
    <DefaultLayout :title="t('blog')">
        <template #header>
            <TitlePage>
                {{ t('blog') }}
            </TitlePage>
        </template>

        <!-- Articles Section -->
        <div class="w-full px-2 sm:px-4 md:px-6 lg:px-8">

            <div class="flex flex-wrap">

                <!-- Sidebar for Rubrics -->
                <div class="w-full md:w-1/6 p-4">
                    <ul v-if="props.rubrics.length > 0">
                        <li v-for="rubric in props.rubrics" :key="rubric.id"
                            class="mb-2 px-2 flex items-center
                                   bg-slate-50 hover:bg-slate-100
                                   dark:bg-slate-900 dark:hover:bg-slate-800
                                   text-lg
                                   text-slate-900 hover:text-orange-700
                                   dark:text-slate-100 dark:hover:text-orange-100
                                   border border-slate-500 hover:border-orange-700
                                   dark:border-slate-500 dark:hover:border-orange-100">

                            <!-- Иконка рубрики -->
                            <div v-html="rubric.icon" />

                            <!-- Используем параметр URL rubric.url для перехода на страницу рубрики -->
                            <Link :href="`/blog/rubric/${rubric.url}`"
                                  class="block w-full font-semibold text-blue-500 dark:text-blue-300">
                                {{ rubric.title }}
                            </Link>

                        </li>
                    </ul>
                </div>

                <!-- Заголовок страницы и параграф описания -->
                <div class="w-full md:w-5/6 text-center">
                    <h1 class="text-center
                               text-2xl font-semibold
                               text-blue-500 dark:text-blue-300
                               sm:text-5xl sm:tracking-tight lg:text-6xl">
                        {{ t('blogTitle') }}
                    </h1>
                    <p class="my-8 max-w-2xl mx-auto text-2xl text-slate-600 dark:text-slate-300 sm:mt-4">
                        {{ t('blogTitleContent') }}
                    </p>
                </div>

            </div>

            <!-- поиск -->
            <input v-model="searchQuery"
                   class="my-4 p-2 w-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-100"
                   :placeholder="t('searchByName')"/>

            <!-- блок карточек -->
            <div class="grid xl:grid-cols-3 gap-6 mb-8" v-if="paginatedArticles.length > 0">

                <article v-for="article in paginatedArticles" :key="article.id"
                         class="flex flex-col sm:flex-row
                             bg-slate-100 dark:bg-slate-700 shadow-lg
                             rounded border border-slate-200 overflow-hidden">

                    <!-- ссылка с изображением -->
                    <a class="block w-full sm:w-56"
                       :href="'/blog/' + article.url">
                        <img class="w-full h-auto sm:h-48 object-cover px-4 py-4"
                             :src="imagePath(article.image_url)"
                             :alt="article.seo_alt"
                             :title="article.seo_title"/>
                    </a>

                    <div class="flex-1 p-5 flex flex-col">
                        <div class="flex-1">
                            <div class="flex flex-wrap justify-between">

                                <!-- форматированная дата создания -->
                                <div class="text-sm font-semibold text-indigo-600 dark:text-indigo-200 uppercase">
                                    {{ formatDate(article.created_at) }}
                                </div>

                                <!-- блок просмотров -->
                                <div class="flex w-fit">

                                    <!-- лайки -->
                                    <LikeButton
                                        :title="t('likes')"
                                        :likeCount="likeCount[article.id]"
                                        @click="likeArticle(article.id)"
                                        :class="isLiked[article.id] ? 'text-rose-500' : 'text-slate-400'"
                                        :disabled="isLiked[article.id]"
                                    />

                                    <!-- просмотры -->
                                    <ViewButton :title="t('views')" :views="article.views" />

                                    <!-- количество комментариев -->
                                    <CommentButton :title="t('comments')" :commentsCount="article.comments_count || 0" />

                                </div>
                            </div>

                            <!-- ссылка с названием -->
                            <a class="inline-flex my-2" :href="'/blog/' + article.url">
                                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-100">
                                    {{ article.title }}
                                </h3>
                            </a>

                            <!-- краткое описание -->
                            <div class="text-sm text-gray-600 dark:text-gray-200 mb-1 max-h-16 overflow-hidden"
                                 v-html="article.short" />

                            <!-- автор-->
                            <div class="w-fit text-sm font-semibold text-orange-600 dark:text-orange-200 float-right">
                                {{ article.author }}
                            </div>

                        </div>
                    </div>

                </article>

            </div>

            <!-- Пагинация -->
            <div class="flex justify-center items-center space-x-2 flex-wrap">
                <button v-for="n in totalPages" :key="n" @click="goToPage(n)"
                        :class="{'font-semibold border border-slate-500 rounded': n === currentPage}"
                        class="text-slate-500 hover:text-red-700 dark:hover:text-red-300 px-2">
                    {{ n }}
                </button>
            </div>

            <div class="flex justify-center items-center mt-4 text-slate-900 dark:text-slate-300"
                 v-html="props.page.content">
            </div>

        </div>

    </DefaultLayout>
</template>
