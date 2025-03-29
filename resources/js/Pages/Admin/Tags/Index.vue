<script setup>
import {defineProps, ref, computed} from 'vue';
import {useForm} from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import Pagination from '@/Components/Admin/Pagination/Pagination.vue';
import SearchInput from '@/Components/Admin/Search/SearchInput.vue';
import TagTable from '@/Components/Admin/Tag/Table/TagTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from "@/Components/Admin/Tag/Select/BulkActionSelect.vue";
import ItemsPerPageSelect from "@/Components/Admin/Select/ItemsPerPageSelect.vue";
import SortSelect from "@/Components/Admin/Tag/Sort/SortSelect.vue";

const { t } = useI18n();

const props = defineProps(['tags', 'tagsCount']);

const form = useForm({});

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const tagToDeleteId = ref(null);
const confirmDelete = (id) => {
    tagToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление тега
const deleteTag = () => {
    if (tagToDeleteId.value !== null) {
        form.delete(route('tags.destroy', tagToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Пагинация
const currentPage = ref(1);
const itemsPerPage = ref(10); // Количество элементов на странице

// Строка поиска
const searchQuery = ref('');

// Параметр сортировки
const sortParam = ref('id');

// Функция сортировки
const sortTags = (tags) => {
    return tags.slice().sort((a, b) => {
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
const filteredTags = computed(() => {
    let filtered = props.tags;

    if (searchQuery.value) {
        filtered = filtered.filter(tag =>
            tag.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortTags(filtered);
});

// Пагинация
const paginatedTags = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredTags.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredTags.value.length / itemsPerPage.value));

// Выбранные теги для массовых действий
const selectedTags = ref([]);

// Функции для выбора и отмены выбора всех элементов select
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    selectedTags.value = isChecked ? paginatedTags.value.map(tag => tag.id) : [];
};

const toggleSelectTag = (tagId) => {
    const index = selectedTags.value.indexOf(tagId);
    if (index > -1) {
        selectedTags.value.splice(index, 1);
    } else {
        selectedTags.value.push(tagId);
    }
};

// Функция для массового удаления тегов
const bulkDelete = () => {
    axios.delete(route('tags.bulkDestroy'), { data: { ids: selectedTags.value } })
        .then(response => {
            selectedTags.value = [];
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
        paginatedTags.value.forEach(tag => {
            if (!selectedTags.value.includes(tag.id)) {
                selectedTags.value.push(tag.id);
            }
        });
    } else if (action === 'deselectAll') {
        selectedTags.value = [];
    } else if (action === 'delete') {
        bulkDelete();
    }
    event.target.value = ''; // Сбросить выбранное значение после выполнения действия
};

</script>

<template>
    <AdminLayout :title="t('tags')">
        <template #header>
            <TitlePage>
                {{ t('tags') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('tags.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addTag') }}
                    </DefaultButton>
                    <BulkActionSelect v-if="tagsCount" @change="handleBulkAction" />
                </div>
                <SearchInput v-if="tagsCount" v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable v-if="tagsCount"> {{ tagsCount }} </CountTable>
                <TagTable
                    :tags="paginatedTags"
                    :selected-tags="selectedTags"
                    @delete="confirmDelete"
                    @toggle-select="toggleSelectTag"
                    @toggle-all="toggleAll"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1" v-if="tagsCount">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredTags.length"
                                @update:currentPage="currentPage = $event"
                                @update:itemsPerPage="itemsPerPage = $event"/>
                    <SortSelect :sortParam="sortParam" @update:sortParam="val => sortParam = val" />
                </div>
            </div>
        </div>

        <DangerModal
            :show="showConfirmDeleteModal"
            @close="closeModal"
            :onCancel="closeModal"
            :onConfirm="deleteTag"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
