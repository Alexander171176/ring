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
import SortSelect from '@/Components/Admin/Section/Sort/SortSelect.vue';
import SectionTable from '@/Components/Admin/Section/Table/SectionTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from '@/Components/Admin/Select/BulkActionSelect.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import axios from 'axios';

const { t } = useI18n();
const props = defineProps(['sections', 'sectionsCount', 'adminCountSections', 'adminSortSections']);
const form = useForm({});

// Используем значение из props для начального количества элементов на странице
const itemsPerPage = ref(props.adminCountSections)

// чтобы при изменении itemsPerPage автоматически обновлялся параметр в базе,
watch(itemsPerPage, (newVal) => {
    axios.put(route('settings.updateAdminCountSections'), { value: newVal.toString() })
        .then(response => {
            // console.log('Количество элементов на странице обновлено:', response.data.value)
        })
        .catch(error => {
            console.error('Ошибка обновления настройки:', error.response.data)
        })
})

// параметр сортировки по умолчанию, устанавливаем из props
const sortParam = ref(props.adminSortSections)
watch(sortParam, (newVal) => {
    axios.put(route('settings.updateAdminSortSections'), { value: newVal })
        .then(response => {
            // console.log('Сортировка обновлена:', response.data.value)
        })
        .catch(error => {
            console.error('Ошибка обновления сортировки:', error.response.data)
        })
})

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const sectionToDeleteId = ref(null);
const confirmDelete = (id) => {
    sectionToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление рубрики
const deleteSection = () => {
    if (sectionToDeleteId.value !== null) {
        form.delete(route('sections.destroy', sectionToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Кнопка активности
const toggleActivity = (section) => {
    const newActivity = !section.activity;
    axios.put(route('sections.updateActivity', section.id), { activity: newActivity })
        .then(response => {
            section.activity = newActivity;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Клонирование рубрики
const cloneSection = (section) => {
    axios.post(route('sections.clone', section.id))
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

// Функция сортировки
const sortSections = (sections) => {
    // Добавляем сортировку по id в двух направлениях:
    if (sortParam.value === 'idAsc') {
        return sections.slice().sort((a, b) => a.id - b.id);
    }
    if (sortParam.value === 'idDesc') {
        return sections.slice().sort((a, b) => b.id - a.id);
    }
    if (sortParam.value === 'activity') {
        return sections.filter(section => section.activity);
    }
    if (sortParam.value === 'inactive') {
        return sections.filter(section => !section.activity);
    }
    if (sortParam.value === 'locale') {
        // Сортировка по locale в обратном порядке
        return sections.slice().sort((a, b) => {
            if (a.locale < b.locale) return 1;
            if (a.locale > b.locale) return -1;
            return 0;
        });
    }
    return sections.slice().sort((a, b) => {
        if (a[sortParam.value] < b[sortParam.value]) return -1
        if (a[sortParam.value] > b[sortParam.value]) return 1
        return 0
    });
};

// Фильтр поиска
const filteredSections = computed(() => {
    let filtered = props.sections;

    if (searchQuery.value) {
        filtered = filtered.filter(section =>
            section.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortSections(filtered);
});

// Пагинация
const paginatedSections = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredSections.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredSections.value.length / itemsPerPage.value));

// Drag and drop
const recalculateSort = (event) => {
    paginatedSections.value.forEach((section, index) => {
        section.sort = index + 1;
        axios.put(route('sections.updateSort', section.id), { sort: section.sort })
            .then(
                // response => console.log(`Обновлена сортировка по идентификатору ${section.id} на ${section.sort}`)
            )
            .catch(
                error => console.error(error.response.data)
            );
    });
};

// Выбранные рубрики для массовых действий
const selectedSections = ref([]);

// Функции для выбора и отмены выбора всех элементов select
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    selectedSections.value = isChecked ? paginatedSections.value.map(section => section.id) : [];
};

const toggleSelectSection = (sectionId) => {
    const index = selectedSections.value.indexOf(sectionId);
    if (index > -1) {
        selectedSections.value.splice(index, 1);
    } else {
        selectedSections.value.push(sectionId);
    }
};

// Функция для массового включения/выключения активности рубрик
const bulkToggleActivity = (newActivity) => {
    selectedSections.value.forEach((sectionId) => {
        axios.put(route('sections.updateActivity', sectionId), { activity: newActivity })
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
    axios.delete(route('sections.bulkDestroy'), { data: { ids: selectedSections.value } })
        .then(response => {
            selectedSections.value = [];
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
        paginatedSections.value.forEach(section => {
            if (!selectedSections.value.includes(section.id)) {
                selectedSections.value.push(section.id);
            }
        });
    } else if (action === 'deselectAll') {
        selectedSections.value = [];
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
    <AdminLayout :title="t('sections')">
        <template #header>
            <TitlePage>
                {{ t('sections') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">

            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('sections.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addSection') }}
                    </DefaultButton>
                    <BulkActionSelect v-if="sectionsCount" @change="handleBulkAction" />
                </div>
                <SearchInput v-if="sectionsCount" v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable v-if="sectionsCount"> {{ sectionsCount }} </CountTable>
                <SectionTable
                    :sections="paginatedSections"
                    :selected-sections="selectedSections"
                    @toggle-activity="toggleActivity"
                    @delete="confirmDelete"
                    @clone="cloneSection"
                    @recalculate-sort="recalculateSort"
                    @toggle-select="toggleSelectSection"
                    @toggle-all="toggleAll"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1" v-if="sectionsCount">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredSections.length"
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
            :onConfirm="deleteSection"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
