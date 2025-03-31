<script setup>
import {defineProps, ref, computed, watch} from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import Pagination from '@/Components/Admin/Pagination/Pagination.vue';
import ItemsPerPageSelect from '@/Components/Admin/Select/ItemsPerPageSelect.vue';
import SearchInput from '@/Components/Admin/Search/SearchInput.vue';
import SortSelect from '@/Components/Admin/Rubric/Sort/SortSelect.vue';
import RubricTable from '@/Components/Admin/Rubric/Table/RubricTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from '@/Components/Admin/Select/BulkActionSelect.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import axios from 'axios';

const { t } = useI18n();
const props = defineProps(['rubrics', 'rubricsCount', 'adminCountRubrics'])
const form = useForm({});

// Используем значение из props для начального количества элементов на странице
const itemsPerPage = ref(props.adminCountRubrics)

// чтобы при изменении itemsPerPage автоматически обновлялся параметр в базе,
watch(itemsPerPage, (newVal) => {
    axios.put(route('settings.updateAdminCountRubrics'), { value: newVal.toString() })
        .then(response => {
            // console.log('Количество элементов на странице обновлено:', response.data.value)
        })
        .catch(error => {
            console.error('Ошибка обновления настройки:', error.response.data)
        })
})

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const rubricToDeleteId = ref(null);
const confirmDelete = (id) => {
    rubricToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление рубрики
const deleteRubric = () => {
    if (rubricToDeleteId.value !== null) {
        form.delete(route('rubrics.destroy', rubricToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Кнопка активности
const toggleActivity = (rubric) => {
    const newActivity = !rubric.activity;
    axios.put(route('rubrics.updateActivity', rubric.id), { activity: newActivity })
        .then(response => {
            rubric.activity = newActivity;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Клонирование рубрики
const cloneRubric = (rubric) => {
    axios.post(route('rubrics.clone', rubric.id))
        .then(response => {
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error("Ошибка клонирования рубрики:", error);
        });
};

// Пагинация
const currentPage = ref(1);

// Строка поиска
const searchQuery = ref('');

// Параметр сортировки
const sortParam = ref('sort');

// Функция сортировки
const sortRubrics = (rubrics) => {
    if (sortParam.value === 'activity') {
        return rubrics.filter(rubric => rubric.activity)
    }
    if (sortParam.value === 'inactive') {
        return rubrics.filter(rubric => !rubric.activity)
    }
    return rubrics.slice().sort((a, b) => {
        if (a[sortParam.value] < b[sortParam.value]) return -1
        if (a[sortParam.value] > b[sortParam.value]) return 1
        return 0
    })
}

// Фильтр поиска
const filteredRubrics = computed(() => {
    let filtered = props.rubrics;

    if (searchQuery.value) {
        filtered = filtered.filter(rubric =>
            rubric.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortRubrics(filtered);
});

// Пагинация
const paginatedRubrics = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredRubrics.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredRubrics.value.length / itemsPerPage.value));

// Drag and drop
const recalculateSort = (event) => {
    paginatedRubrics.value.forEach((rubric, index) => {
        rubric.sort = index + 1;
        axios.put(route('rubrics.updateSort', rubric.id), { sort: rubric.sort })
            .then(
                // response => console.log(`Обновлена сортировка по идентификатору ${rubric.id} на ${rubric.sort}`)
            )
            .catch(
                error => console.error(error.response.data)
            );
    });
};

// Выбранные рубрики для массовых действий
const selectedRubrics = ref([]);

// Функции для выбора и отмены выбора всех элементов select
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    selectedRubrics.value = isChecked ? paginatedRubrics.value.map(rubric => rubric.id) : [];
};

const toggleSelectRubric = (rubricId) => {
    const index = selectedRubrics.value.indexOf(rubricId);
    if (index > -1) {
        selectedRubrics.value.splice(index, 1);
    } else {
        selectedRubrics.value.push(rubricId);
    }
};

// Функция для массового включения/выключения активности рубрик
const bulkToggleActivity = (newActivity) => {
    selectedRubrics.value.forEach((rubricId) => {
        axios.put(route('rubrics.updateActivity', rubricId), { activity: newActivity })
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

// Функция для массового удаления рубрик
const bulkDelete = () => {
    axios.delete(route('rubrics.bulkDestroy'), { data: { ids: selectedRubrics.value } })
        .then(response => {
            selectedRubrics.value = [];
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
        paginatedRubrics.value.forEach(rubric => {
            if (!selectedRubrics.value.includes(rubric.id)) {
                selectedRubrics.value.push(rubric.id);
            }
        });
    } else if (action === 'deselectAll') {
        selectedRubrics.value = [];
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
    <AdminLayout :title="t('rubrics')">
        <template #header>
            <TitlePage>
                {{ t('rubrics') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">

            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('rubrics.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addRubric') }}
                    </DefaultButton>
                    <BulkActionSelect v-if="rubricsCount" @change="handleBulkAction" />
                </div>
                <SearchInput v-if="rubricsCount" v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable v-if="rubricsCount"> {{ rubricsCount }} </CountTable>
                <RubricTable
                    :rubrics="paginatedRubrics"
                    :selected-rubrics="selectedRubrics"
                    @toggle-activity="toggleActivity"
                    @delete="confirmDelete"
                    @clone="cloneRubric"
                    @recalculate-sort="recalculateSort"
                    @toggle-select="toggleSelectRubric"
                    @toggle-all="toggleAll"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1" v-if="rubricsCount">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredRubrics.length"
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
            :onConfirm="deleteRubric"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
