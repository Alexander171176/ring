<script setup>
import { defineProps, ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import Pagination from '@/Components/Admin/Pagination/Pagination.vue';
import ItemsPerPageSelect from '@/Components/Admin/Select/ItemsPerPageSelect.vue';
import SearchInput from '@/Components/Admin/Search/SearchInput.vue';
import SortSelect from '@/Components/Admin/Article/Sort/SortSelect.vue';
import ArticleTable from '@/Components/Admin/Article/Table/ArticleTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from '@/Components/Admin/Select/BulkActionSelect.vue';
import axios from 'axios';

const { t } = useI18n();

const props = defineProps(['articles', 'articlesCount']);

const form = useForm({});

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const articleToDeleteId = ref(null);
const confirmDelete = (id) => {
    articleToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление статьи
const deleteArticle = () => {
    if (articleToDeleteId.value !== null) {
        form.delete(route('articles.destroy', articleToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Кнопка активности
const toggleActivity = (article) => {
    const newActivity = !article.activity;
    axios.put(route('articles.updateActivity', article.id), { activity: newActivity })
        .then(response => {
            article.activity = newActivity;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
             console.error(error.response.data);
        });
};

// Клонирование статьи
const cloneArticle = (article) => {
    axios.post(route('articles.clone', article.id))
        .then(response => {
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error("Ошибка клонирования статьи:", error);
        });
};

// Пагинация
const currentPage = ref(1);
const itemsPerPage = ref(10); // Количество элементов на странице

// Строка поиска
const searchQuery = ref('');

// Параметр сортировки
const sortParam = ref('sort');

// Функция сортировки
const sortArticles = (articles) => {
    if (sortParam.value === 'activity') {
        return articles.filter(article => article.activity);
    }
    if (sortParam.value === 'inactive') {
        return articles.filter(article => !article.activity);
    }
    return articles.slice().sort((a, b) => {
        if (sortParam.value === 'views' || sortParam.value === 'likes') {
            // ✅ Сортировка в порядке убывания для просмотров и лайков
            return b[sortParam.value] - a[sortParam.value];
        }
        if (a[sortParam.value] < b[sortParam.value]) {
            return -1;
        }
        if (a[sortParam.value] > b[sortParam.value]) {
            return 1;
        }
        return 0;
    });
};

// Фильтр поиска
const filteredArticles = computed(() => {
    let filtered = props.articles;

    if (searchQuery.value) {
        filtered = filtered.filter(article =>
            article.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortArticles(filtered);
});

// Пагинация
const paginatedArticles = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredArticles.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredArticles.value.length / itemsPerPage.value));

// Drag and drop
const recalculateSort = (event) => {
    paginatedArticles.value.forEach((article, index) => {
        article.sort = index + 1;
        axios.put(route('articles.updateSort', article.id), { sort: article.sort })
            .then(
                // response => console.log(`Обновлена сортировка по идентификатору ${article.id} на ${article.sort}`)
            )
            .catch(
                error => console.error(error.response.data)
            );
    });
};

// Выбранные статьи для массовых действий
const selectedArticles = ref([]);

// Функции для выбора и отмены выбора всех элементов select
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    selectedArticles.value = isChecked ? paginatedArticles.value.map(article => article.id) : [];
};

const toggleSelectArticle = (articleId) => {
    const index = selectedArticles.value.indexOf(articleId);
    if (index > -1) {
        selectedArticles.value.splice(index, 1);
    } else {
        selectedArticles.value.push(articleId);
    }
};

// Функции для массового включения/выключения активности
const bulkToggleActivity = (newActivity) => {
    const updatePromises = selectedArticles.value.map((articleId) =>
        axios.put(route('articles.updateActivity', articleId), { activity: newActivity })
    );

    Promise.all(updatePromises)
        .then((responses) => {
            const reloadRequired = responses.some(response => response.data.reload);
            if (reloadRequired) {
                window.location.reload();
            }
        })
        .catch(error => {
             console.error(error.response.data);
        });
};

const bulkDelete = () => {
    axios.delete(route('articles.bulkDestroy'), { data: { ids: selectedArticles.value } })
        .then(response => {
            selectedArticles.value = [];
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

const handleBulkAction = (event) => {
    const action = event.target.value;
    if (action === 'selectAll') {
        paginatedArticles.value.forEach(article => {
            if (!selectedArticles.value.includes(article.id)) {
                selectedArticles.value.push(article.id);
            }
        });
    } else if (action === 'deselectAll') {
        selectedArticles.value = [];
    } else if (action === 'activate') {
        bulkToggleActivity(true);
    } else if (action === 'deactivate') {
        bulkToggleActivity(false);
    } else if (action === 'delete') {
        bulkDelete();
    }
    event.target.value = ''; // Сбросить выбранное значение после выполнения действия
};

</script>

<template>
    <AdminLayout :title="t('posts')">
        <template #header>
            <TitlePage>
                {{ t('posts') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('articles.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addPost') }}
                    </DefaultButton>
                    <BulkActionSelect @change="handleBulkAction" />
                </div>
                <SearchInput v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable> {{ articlesCount }}</CountTable>
                <ArticleTable
                    :articles="paginatedArticles"
                    :selected-articles="selectedArticles"
                    @toggle-activity="toggleActivity"
                    @delete="confirmDelete"
                    @clone="cloneArticle"
                    @recalculate-sort="recalculateSort"
                    @toggle-select="toggleSelectArticle"
                    @toggle-all="toggleAll"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredArticles.length"
                                @update:currentPage="currentPage = $event"/>
                    <SortSelect :sortParam="sortParam" @update:sortParam="val => sortParam = val"/>
                </div>
            </div>
        </div>

        <DangerModal
            :show="showConfirmDeleteModal"
            @close="closeModal"
            :onCancel="closeModal"
            :onConfirm="deleteArticle"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
