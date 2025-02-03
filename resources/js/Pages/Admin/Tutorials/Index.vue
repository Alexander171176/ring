<script setup>
import { defineProps, ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import Pagination from '@/Components/Admin/Pagination/Pagination.vue';
import ItemsPerPageSelect from '@/Components/Admin/Select/ItemsPerPageSelect.vue';
import SearchInput from '@/Components/Admin/Search/SearchInput.vue';
import SortSelect from '@/Components/Admin/Tutorial/Sort/SortSelect.vue';
import TutorialTable from '@/Components/Admin/Tutorial/Table/TutorialTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from '@/Components/Admin/Select/BulkActionSelect.vue';
import axios from 'axios';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";

const { t } = useI18n();

const props = defineProps(['tutorials', 'tutorialsCount']);

const form = useForm({});

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const tutorialToDeleteId = ref(null);
const confirmDelete = (id) => {
    tutorialToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление рубрики
const deleteTutorial = () => {
    if (tutorialToDeleteId.value !== null) {
        form.delete(route('tutorials.destroy', tutorialToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Кнопка активности
const toggleActivity = (tutorial) => {
    const newActivity = !tutorial.activity;
    axios.put(route('tutorials.updateActivity', tutorial.id), { activity: newActivity })
        .then(response => {
            tutorial.activity = newActivity;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Клонирование рубрики
const cloneTutorial = (tutorial) => {
    axios.post(route('tutorials.clone', tutorial.id))
        .then(response => {
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error("Ошибка клонирования руководства:", error);
        });
};

// Пагинация
const currentPage = ref(1);
const itemsPerPage = ref(10); // Количество элементов на странице

// Строка поиска
const searchQuery = ref('');

// Параметр сортировки
const sortParam = ref('id');

// Функция сортировки
const sortTutorials = (tutorials) => {
    if (sortParam.value === 'activity') {
        return tutorials.filter(tutorial => tutorial.activity);
    }
    if (sortParam.value === 'inactive') {
        return tutorials.filter(tutorial => !tutorial.activity);
    }
    return tutorials.slice().sort((a, b) => {
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
const filteredTutorials = computed(() => {
    let filtered = props.tutorials;

    if (searchQuery.value) {
        filtered = filtered.filter(tutorial =>
            tutorial.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortTutorials(filtered);
});

// Пагинация
const paginatedTutorials = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredTutorials.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredTutorials.value.length / itemsPerPage.value));

// Drag and drop
const recalculateSort = (event) => {
    paginatedTutorials.value.forEach((tutorial, index) => {
        tutorial.sort = index + 1;
        axios.put(route('tutorials.updateSort', tutorial.id), { sort: tutorial.sort })
            .then(
                // response => console.log(`Обновлена сортировка по идентификатору ${tutorial.id} на ${tutorial.sort}`)
            )
            .catch(
                // error => console.error(error.response.data)
            );
    });
};

// Выбранные рубрики для массовых действий
const selectedTutorials = ref([]);

// Функции для выбора и отмены выбора всех элементов select
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    selectedTutorials.value = isChecked ? paginatedTutorials.value.map(tutorial => tutorial.id) : [];
};

const toggleSelectTutorial = (tutorialId) => {
    const index = selectedTutorials.value.indexOf(tutorialId);
    if (index > -1) {
        selectedTutorials.value.splice(index, 1);
    } else {
        selectedTutorials.value.push(tutorialId);
    }
};

// Функции для выбора и отмены выбора всех элементов checkbox
const bulkToggleActivity = (newActivity) => {
    selectedTutorials.value.forEach((tutorialId) => {
        axios.put(route('tutorials.updateActivity', tutorialId), { activity: newActivity })
            .then(response => {
                if (response.data.reload) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error(error.response.data);
            });
    });
};

const bulkDelete = () => {
    axios.delete(route('tutorials.bulkDestroy'), { data: { ids: selectedTutorials.value } })
        .then(response => {
            selectedTutorials.value = [];
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
        paginatedTutorials.value.forEach(tutorial => {
            if (!selectedTutorials.value.includes(tutorial.id)) {
                selectedTutorials.value.push(tutorial.id);
            }
        });
    } else if (action === 'deselectAll') {
        selectedTutorials.value = [];
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
    <AdminLayout :title="t('tutorials')">
        <template #header>
            <TitlePage>
                {{ t('tutorials') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('tutorials.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addTutorial') }}
                    </DefaultButton>
                    <BulkActionSelect @change="handleBulkAction" />
                </div>
                <SearchInput v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable> {{ tutorialsCount }} </CountTable>
                <TutorialTable
                    :tutorials="paginatedTutorials"
                    :selected-tutorials="selectedTutorials"
                    @toggle-activity="toggleActivity"
                    @delete="confirmDelete"
                    @clone="cloneTutorial"
                    @recalculate-sort="recalculateSort"
                    @toggle-select="toggleSelectTutorial"
                    @toggle-all="toggleAll"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredTutorials.length"
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
            :onConfirm="deleteTutorial"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
