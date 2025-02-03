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
import SortSelect from '@/Components/Admin/Guide/Sort/SortSelect.vue';
import GuideTable from '@/Components/Admin/Guide/Table/GuideTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from '@/Components/Admin/Select/BulkActionSelect.vue';
import axios from 'axios';

const { t } = useI18n();

const props = defineProps(['guides', 'guidesCount']);

const form = useForm({});

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const guideToDeleteId = ref(null);
const confirmDelete = (id) => {
    guideToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление статьи
const deleteGuide = () => {
    if (guideToDeleteId.value !== null) {
        form.delete(route('guides.destroy', guideToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Кнопка активности
const toggleActivity = (guide) => {
    const newActivity = !guide.activity;
    axios.put(route('guides.updateActivity', guide.id), { activity: newActivity })
        .then(response => {
            guide.activity = newActivity;
            if (response.data.reload) {
                window.location.reload(); // Перезагрузка страницы при наличии флага reload
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Клонирование статьи
const cloneGuide = (guide) => {
    axios.post(route('guides.clone', guide.id))
        .then(response => {
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error("Ошибка клонирования гайда:", error);
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
const sortGuides = (guides) => {
    if (sortParam.value === 'activity') {
        return guides.filter(guide => guide.activity);
    }
    if (sortParam.value === 'inactive') {
        return guides.filter(guide => !guide.activity);
    }
    return guides.slice().sort((a, b) => {
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
const filteredGuides = computed(() => {
    let filtered = props.guides;

    if (searchQuery.value) {
        filtered = filtered.filter(guide =>
            guide.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortGuides(filtered);
});

// Пагинация
const paginatedGuides = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredGuides.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredGuides.value.length / itemsPerPage.value));

// Drag and drop
const recalculateSort = (event) => {
    paginatedGuides.value.forEach((guide, index) => {
        guide.sort = index + 1;
        axios.put(route('guides.updateSort', guide.id), { sort: guide.sort })
            .then(
                // response => console.log(`Обновлена сортировка по идентификатору ${guide.id} на ${guide.sort}`)
            )
            .catch(
                error => console.error(error.response.data)
            );
    });
};

// Выбранные статьи для массовых действий
const selectedGuides = ref([]);

// Функции для выбора и отмены выбора всех элементов select
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    selectedGuides.value = isChecked ? paginatedGuides.value.map(guide => guide.id) : [];
};

const toggleSelectGuide = (guideId) => {
    const index = selectedGuides.value.indexOf(guideId);
    if (index > -1) {
        selectedGuides.value.splice(index, 1);
    } else {
        selectedGuides.value.push(guideId);
    }
};

// Функции для массового обновления активности
const bulkToggleActivity = (newActivity) => {
    selectedGuides.value.forEach((guideId) => {
        axios.put(route('guides.updateActivity', guideId), { activity: newActivity })
            .then(response => {
                if (response.data.reload) {
                    window.location.reload(); // Перезагрузка страницы при наличии флага reload
                }
            })
            .catch(error => {
                console.error(error.response.data);
            });
    });
};

const bulkDelete = () => {
    axios.delete(route('guides.bulkDestroy'), { data: { ids: selectedGuides.value } })
        .then(response => {
            selectedGuides.value = [];
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
        paginatedGuides.value.forEach(guide => {
            if (!selectedGuides.value.includes(guide.id)) {
                selectedGuides.value.push(guide.id);
            }
        });
    } else if (action === 'deselectAll') {
        selectedGuides.value = [];
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
    <AdminLayout :title="t('guides')">
        <template #header>
            <TitlePage>
                {{ t('guides') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('guides.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addGuide') }}
                    </DefaultButton>
                    <BulkActionSelect @change="handleBulkAction" />
                </div>
                <SearchInput v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable> {{ guidesCount }}</CountTable>
                <GuideTable
                    :guides="paginatedGuides"
                    :selected-guides="selectedGuides"
                    @toggle-activity="toggleActivity"
                    @delete="confirmDelete"
                    @clone="cloneGuide"
                    @recalculate-sort="recalculateSort"
                    @toggle-select="toggleSelectGuide"
                    @toggle-all="toggleAll"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredGuides.length"
                                @update:currentPage="currentPage = $event"/>
                    <SortSelect :sortParam="sortParam" @update:sortParam="val => sortParam = val"/>
                </div>
            </div>
        </div>

        <DangerModal
            :show="showConfirmDeleteModal"
            @close="closeModal"
            :onCancel="closeModal"
            :onConfirm="deleteGuide"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
