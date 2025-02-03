<script setup>
import {ref, onMounted} from 'vue';
import {format} from 'date-fns';
import {ru} from 'date-fns/locale';
import axios from 'axios';
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

// Пропсы для получения данных
const props = defineProps({
    articleId: Number,
    auth: Object,
});

const comments = ref([]);
const editedComment = ref(null);
const newComment = ref('');
const parentCommentId = ref(null);
const isLoading = ref(false);

// Загрузка комментариев
const loadComments = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/api/comments/${props.articleId}`);
        comments.value = response.data || [];
    } catch (error) {
        console.error('Ошибка при загрузке комментариев:', error);
    } finally {
        isLoading.value = false;
    }
};

// Отправка нового комментария
const submitComment = async () => {
    if (!newComment.value.trim()) return;

    if (!props.auth || !props.auth.id) {
        console.error('Пользователь не авторизован или данные о пользователе отсутствуют.');
        return;
    }

    try {
        const response = await axios.post('/api/comments', {
            article_id: props.articleId, // Передаем корректный ID статьи
            user_id: props.auth.id, // ID пользователя напрямую из props.auth
            content: newComment.value,
            parent_id: parentCommentId.value,
        });

        if (parentCommentId.value) {
            const parentComment = comments.value.find(c => c.id === parentCommentId.value);
            if (parentComment) {
                if (!parentComment.replies) {
                    parentComment.replies = [];
                }
                parentComment.replies.push(response.data);
            }
        } else {
            comments.value.push(response.data); // Добавляем новый комментарий в список
        }

        newComment.value = '';  // Очищаем поле ввода
        parentCommentId.value = null;
    } catch (error) {
        if (error.response && error.response.status === 422) {
            // Обработка ошибок валидации
            console.error('Ошибка валидации:', error.response.data.errors);
        } else {
            console.error('Ошибка при отправке комментария:', error);
        }
    }
};

// Начать редактирование комментария
const editComment = (comment) => {
    editedComment.value = {...comment}; // Копируем комментарий для редактирования
};

// отмена редактирования
const cancelEdit = () => {
    editedComment.value = null;
};

// Сохранить отредактированный комментарий
const saveComment = async () => {
    if (!editedComment.value) return;

    try {
        const response = await axios.put(`/api/comments/${editedComment.value.id}`, {
            content: editedComment.value.content,
        });

        if (editedComment.value.parent_id) {
            const parentComment = comments.value.find(c => c.id === editedComment.value.parent_id);
            if (parentComment) {
                const index = parentComment.replies.findIndex(r => r.id === editedComment.value.id);
                parentComment.replies[index] = response.data;
            }
        } else {
            const index = comments.value.findIndex(c => c.id === editedComment.value.id);
            comments.value[index] = response.data;
        }

        editedComment.value = null; // Завершаем редактирование
    } catch (error) {
        console.error('Ошибка при редактировании комментария:', error);
    }
};

// Удалить комментарий
const deleteComment = async (commentId) => {
    try {
        await axios.delete(`/api/comments/${commentId}`);

        const parentComment = comments.value.find(c => c.replies && c.replies.some(r => r.id === commentId));
        if (parentComment) {
            parentComment.replies = parentComment.replies.filter(r => r.id !== commentId);
        } else {
            comments.value = comments.value.filter(c => c.id !== commentId);
        }
    } catch (error) {
        console.error('Ошибка при удалении комментария:', error);
    }
};

// показываем реплики
const replyToComment = (commentId) => {
    parentCommentId.value = commentId;
    newComment.value = '';
};

const cancelReply = () => {
    parentCommentId.value = null;
    newComment.value = '';
};

const formatDate = (date) => {
    return date ? format(new Date(date), 'dd MMMM yyyy, HH:mm', {locale: ru}) : '';
};

// Функция для форматирования и отображения даты комментария
const displayDate = (date) => {
    return date ? formatDate(date) : '';
};

onMounted(() => {
    loadComments();
});
</script>

<template>
    <!-- Комментарии -->
    <div class="comments-section">

        <!-- Заголовок -->
        <h2 class="text-center text-orange-500 dark:text-orange-300 text-lg font-semibold mb-1">
            {{ t('comments') }}
        </h2>

        <!-- Загрузка комментариев -->
        <div v-if="isLoading" class="mt-4">
            <p class="text-slate-900 dark:text-slate-100 text-lg font-semibold mb-4">
                {{ t('uploadingComments') }}
            </p>
        </div>

        <div v-else-if="comments.length > 0" class="mt-2">
            <div v-for="comment in comments" :key="comment.id"
                 class="">

                <!-- Редактируемый комментарий -->
                <div v-if="editedComment && editedComment.id === comment.id">

                    <textarea v-model="editedComment.content"
                              class="w-full p-2 mt-2
                                     bg-white dark:bg-gray-700
                                     text-slate-800 dark:text-white
                                     text-lg rounded border">
                    </textarea>

                    <!-- Кнопки отмены и сохранения -->
                    <div class="flex justify-end mt-2">

                        <button @click="cancelEdit"
                                :title="t('cancel')"
                                class="flex items-center py-1 px-3 mr-1 rounded
                                       border border-slate-400 hover:border-indigo-500
                                       dark:border-indigo-300 dark:hover:border-indigo-100">
                            <svg class="w-4 h-4 fill-current text-indigo-500" viewBox="0 0 16 16">
                                <path
                                    d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                            </svg>
                            <span class="ml-1 text-indigo-500">{{ t('cancel') }}</span>
                        </button>

                        <button @click="saveComment"
                                :title="t('save')"
                                class="flex items-center py-1 px-3 rounded
                                       bg-indigo-500
                                       border border-slate-300 hover:border-indigo-500
                                       dark:border-indigo-300 dark:hover:border-indigo-100">
                            <svg class="w-4 h-4 fill-current text-slate-100" viewBox="0 0 16 16">
                                <path
                                    d="M14.3 2.3L5 11.6 1.7 8.3c-.4-.4-1-.4-1.4 0-.4.4-.4 1 0 1.4l4 4c.2.2.4.3.7.3.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4-.4-.4-1-.4-1.4 0z"></path>
                            </svg>
                            <span class="ml-1 text-slate-100">{{ t('save') }}</span>
                        </button>

                    </div>

                </div>

                <div v-else class="border-dotted border border-slate-400 mt-2 px-3 py-1">
                    <!-- Отображение пользователя и его комментария -->
                    <div>
                        <div class="flex items-center justify-between mt-2">
                            <div class="font-semibold text-lg text-indigo-500 dark:text-blue-100">
                                {{ comment.user ? comment.user.name : 'Аноним' }}
                            </div>
                            <span class="text-sm text-slate-500 dark:text-slate-400 mr-2">
                            <div class="text-sm text-slate-500 dark:text-slate-300">
                                {{ formatDate(comment.created_at) }}
                            </div>
                        </span>
                        </div>
                        <div class="text-lg bg-indigo-500
                                    text-slate-100 p-3 mb-3
                                    rounded-lg rounded-tl-none
                                    border border-transparent
                                    shadow-md">
                            {{ comment.content }}
                        </div>
                    </div>

                    <!-- Кнопки редактирования и удаления -->
                    <div class="flex flex-row justify-end mt-2">

                        <button @click="replyToComment(comment.id)"
                                :title="t('reply')"
                                class="flex items-center py-1 px-3 mr-2 rounded
                                       border border-slate-300 hover:border-teal-500
                                       dark:hover:border-teal-200">
                            <svg class="w-4 h-4 fill-current mr-2
                                        text-teal-500 dark:text-teal-300
                                        shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M8 0C3.6 0 0 3.1 0 7s3.6 7 8 7h.6l5.4 2v-4.4c1.2-1.2 2-2.8 2-4.6 0-3.9-3.6-7-8-7zm4 10.8v2.3L8.9 12H8c-3.3 0-6-2.2-6-5s2.7-5 6-5 6 2.2 6 5c0 2.2-2 3.8-2 3.8z"></path>
                            </svg>
                            <span class="font-semibold text-teal-500">{{ t('reply') }}</span>
                        </button>

                        <button v-if="comment.user && comment.user.id === props.auth.id"
                                @click="editComment(comment)"
                                :title="t('edit')"
                                class="flex items-center py-2 px-2 mr-2 rounded
                                       border border-slate-300 hover:border-orange-500
                                       dark:border-orange-300 dark:hover:border-orange-200">
                            <svg class="w-4 h-4 fill-current
                                        text-orange-400 dark:text-orange-300
                                        shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M11.7.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM4.6 14H2v-2.6l6-6L10.6 8l-6 6zM12 6.6L9.4 4 11 2.4 13.6 5 12 6.6z"/>
                            </svg>
                        </button>
                        <button v-if="comment.user && comment.user.id === props.auth.id"
                                :title="t('delete')"
                                @click="deleteComment(comment.id)"
                                class="flex items-center py-2 px-2 rounded
                                       border border-slate-300 hover:border-rose-500
                                       dark:border-rose-300 dark:hover:border-rose-200">
                            <svg class="w-4 h-4 fill-current
                                        text-rose-500 dark:text-rose-400
                                        shrink-0"
                                 viewBox="0 0 16 16">
                                <path
                                    d="M5 7h2v6H5V7zm4 0h2v6H9V7zm3-6v2h4v2h-1v10c0 .6-.4 1-1 1H2c-.6 0-1-.4-1-1V5H0V3h4V1c0-.6.4-1 1-1h6c.6 0 1 .4 1 1zM6 2v1h4V2H6zm7 3H3v9h10V5z"/>
                            </svg>
                        </button>

                    </div>
                </div>

                <!-- Форма ответа на комментарий -->
                <div v-if="parentCommentId === comment.id" class="reply-form mt-2">
                    <textarea v-model="newComment" rows="2"
                              class="w-full p-2 mt-2
                                     bg-white dark:bg-gray-700
                                     text-slate-800 dark:text-white
                                     text-lg rounded border"
                              :placeholder="t('replyToComment')">
                    </textarea>
                    <div class="flex justify-end mt-2">
                        <button @click="cancelReply"
                                :title="t('cancel')"
                                class="flex items-center py-1 px-3 mr-2 rounded
                                       border border-slate-400 hover:border-indigo-500
                                       dark:border-indigo-300 dark:hover:border-indigo-100">
                            <svg class="w-4 h-4 fill-current text-indigo-500" viewBox="0 0 16 16">
                                <path
                                    d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                            </svg>
                            <span class="ml-1 text-indigo-500">{{ t('cancel') }}</span>
                        </button>
                        <button @click="submitComment"
                                :title="t('send')"
                                class="flex items-center py-1 px-3 rounded
                                       bg-indigo-500
                                       border border-slate-300 hover:border-indigo-500
                                       dark:border-indigo-300 dark:hover:border-indigo-100">
                            <span class="ml-1 text-slate-100">{{ t('send') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Отображение ответов на комментарии -->
                <div v-if="comment.replies && comment.replies.length > 0">
                    <div v-for="reply in comment.replies" :key="reply.id">

                        <div v-if="editedComment && editedComment.id === reply.id">

                            <!-- Редактируемая реплика -->
                            <textarea v-model="editedComment.content"
                                      class="w-full p-2 mt-2 rounded
                                             bg-white dark:bg-gray-700
                                             text-slate-800 dark:text-white text-lg
                                             border border-slate-400 dark:border-slate-200">
                            </textarea>

                            <div class="flex flex-row justify-end mt-2">
                                <button @click="cancelEdit"
                                        :title="t('cancel')"
                                        class="flex items-center py-1 px-3 mr-2 rounded
                                               border border-slate-400 hover:border-indigo-500
                                               dark:border-indigo-300 dark:hover:border-indigo-100">
                                    <svg class="w-4 h-4 fill-current text-indigo-500" viewBox="0 0 16 16">
                                        <path
                                            d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                                    </svg>
                                    <span class="ml-1 text-indigo-500">{{ t('cancel') }}</span>
                                </button>
                                <button @click="saveComment"
                                        class="flex items-center py-1 px-3 rounded
                                               bg-indigo-500
                                               border border-slate-300 hover:border-indigo-500
                                               dark:border-indigo-300 dark:hover:border-indigo-100">
                                    <span class="ml-1 text-slate-100">{{ t('save') }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-else class="border-dotted border border-slate-400 mt-2 px-3 py-1">

                            <div class="flex items-center justify-between">

                                <!-- Автор реплики -->
                                <div class="font-semibold text-lg text-blue-700 dark:text-blue-300">
                                    {{ reply.user ? reply.user.name : 'Аноним' }}
                                </div>

                                <!-- Отображение даты реплики -->
                                <div class="text-sm text-slate-500 dark:text-slate-300">
                                    {{ formatDate(reply.created_at) }} <!-- Здесь отображаем дату реплики -->
                                </div>
                            </div>

                            <!-- Отображение реплики -->
                            <div class="p-3 mb-1
                                        bg-white dark:bg-slate-700
                                        text-lg font-semibold
                                        text-blue-700 dark:text-white
                                        rounded-lg rounded-tl-none
                                        border border-slate-200
                                        shadow-md">
                                {{ reply.content }}
                            </div>

                            <!-- Кнопки для редактирования и удаления -->
                            <div v-if="reply.user && reply.user.id === props.auth.id"
                                 class="flex flex-row justify-end mt-2">
                                <button @click="editComment(reply)"
                                        :title="t('edit')"
                                        class="flex items-center py-2 px-2 mr-2 rounded
                                               border border-slate-300 hover:border-orange-500
                                               dark:border-orange-300 dark:hover:border-orange-200">
                                    <svg class="w-4 h-4 fill-current
                                                text-orange-400 dark:text-orange-300
                                                shrink-0" viewBox="0 0 16 16">
                                        <path
                                            d="M11.7.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM4.6 14H2v-2.6l6-6L10.6 8l-6 6zM12 6.6L9.4 4 11 2.4 13.6 5 12 6.6z"/>
                                    </svg>
                                </button>
                                <button @click="deleteComment(reply.id)"
                                        :title="t('delete')"
                                        class="flex items-center py-2 px-2 rounded
                                               border border-slate-300 hover:border-rose-500
                                               dark:border-rose-300 dark:hover:border-rose-200">
                                    <svg class="w-4 h-4 fill-current
                                                text-rose-500 dark:text-rose-400
                                                shrink-0"
                                         viewBox="0 0 16 16">
                                        <path
                                            d="M5 7h2v6H5V7zm4 0h2v6H9V7zm3-6v2h4v2h-1v10c0 .6-.4 1-1 1H2c-.6 0-1-.4-1-1V5H0V3h4V1c0-.6.4-1 1-1h6c.6 0 1 .4 1 1zM6 2v1h4V2H6zm7 3H3v9h10V5z"/>
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Форма добавления нового комментария -->
        <form v-if="!parentCommentId" @submit.prevent="submitComment" class="mt-2">
                            <textarea v-model="newComment" rows="3"
                                      class="w-full p-2 rounded
                                             bg-white dark:bg-gray-700
                                             text-slate-800 dark:text-white text-lg
                                             border border-slate-400 dark:border-slate-200"
                                      :placeholder="t('leaveComment')">
                            </textarea>
            <button type="submit"
                    class="float-right flex items-center py-1 px-3 mr-2 rounded
                           border border-slate-300 hover:border-blue-700
                           dark:hover:border-blue-300">
                <svg class="w-4 h-4 fill-current text-blue-700 dark:text-blue-300 shrink-0 mr-3"
                     viewBox="0 0 16 16">
                    <path
                        d="M7.3 8.7c-.4-.4-.4-1 0-1.4l7-7c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4l-7 7c-.4.4-1 .4-1.4 0zm0 6c-.4-.4-.4-1 0-1.4l7-7c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4l-7 7c-.4.4-1 .4-1.4 0zm-7-5c-.4-.4-.4-1 0-1.4l7-7c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4l-7 7c-.4.4-1 .4-1.4 0z"></path>
                </svg>
                <span class="ml-1 text-blue-700 dark:text-blue-300">{{ t('send') }}</span>
            </button>
        </form>
    </div>
</template>
