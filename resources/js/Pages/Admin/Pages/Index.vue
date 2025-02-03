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
import SortSelect from '@/Components/Admin/Page/Sort/SortSelect.vue';
import PageTable from '@/Components/Admin/Page/Table/PageTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from '@/Components/Admin/Page/Select/BulkActionSelect.vue';
import axios from 'axios';

const { t } = useI18n();

const props = defineProps(['pages', 'pagesCount']);

const form = useForm({});

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const pageToDeleteId = ref(null);
const confirmDelete = (id) => {
    pageToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление
const deletePage = () => {
    if (pageToDeleteId.value !== null) {
        form.delete(route('pages.destroy', pageToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Кнопка активности
const toggleActivity = (page) => {
    const newActivity = !page.activity;
    axios.put(route('pages.updateActivity', page.id), { activity: newActivity })
        .then(response => {
            page.activity = newActivity;
            if (response.data.reload) {
                window.location.reload(); // Перезагрузка страницы для отображения новых данных
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Кнопка активности для переключения показа страницы в меню
const togglePrintInMenu = (page) => {
    const newPrintInMenu = !page.print_in_menu;
    axios.put(route('pages.printInMenu', page.id), { print_in_menu: newPrintInMenu })
        .then(response => {
            page.print_in_menu = newPrintInMenu;
            if (response.data.reload) {
                window.location.reload(); // Перезагрузка страницы для отображения новых данных
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Клонирование страницы
const clonePage = (page) => {
    axios.post(route('pages.clone', page.id))
        .then(response => {
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error("Ошибка клонирования страницы:", error);
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
const sortPages = (pages) => {
    if (sortParam.value === 'activity') {
        return pages.filter(page => page.activity);
    }
    if (sortParam.value === 'inactive') {
        return pages.filter(page => !page.activity);
    }
    if (sortParam.value === 'printInMenu') {
        return pages.filter(page => page.print_in_menu);
    }
    if (sortParam.value === 'notPrintInMenu') {
        return pages.filter(page => !page.print_in_menu);
    }
    return pages.slice().sort((a, b) => {
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
const filteredPages = computed(() => {
    let filtered = props.pages;

    if (searchQuery.value) {
        filtered = filtered.filter(page =>
            page.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortPages(filtered);
});

// Пагинация
const paginatedPages = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredPages.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredPages.value.length / itemsPerPage.value));

// Drag and drop
const recalculateSort = (event) => {
    paginatedPages.value.forEach((page, index) => {
        page.sort = index + 1;
        axios.put(route('pages.updateSort', page.id), { sort: page.sort })
            .then(
                // response => console.log(`Обновлена сортировка по идентификатору ${page.id} на ${page.sort}`)
            )
            .catch(
                // error => console.error(error.response.data)
            );
    });
};

// Выбранные страницы для массовых действий
const selectedPages = ref([]);

// Функции для выбора и отмены выбора всех элементов select
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    selectedPages.value = isChecked ? paginatedPages.value.map(page => page.id) : [];
};

const toggleSelectPage = (pageId) => {
    const index = selectedPages.value.indexOf(pageId);
    if (index > -1) {
        selectedPages.value.splice(index, 1);
    } else {
        selectedPages.value.push(pageId);
    }
};

// Функция массового включения/выключения показа в меню
const bulkTogglePrintInMenu = (showInMenu) => {
    selectedPages.value.forEach((pageId) => {
        axios.put(route('pages.printInMenu', pageId), { print_in_menu: showInMenu })
            .then(response => {
                if (response.data.reload) {
                    window.location.reload(); // Перезагрузка страницы для отображения новых данных
                }
            })
            .catch(error => {
                console.error(error.response.data);
            });
    });
};

// Функция обновления активности для массовых действий
const bulkToggleActivity = (newActivity) => {
    selectedPages.value.forEach((pageId) => {
        axios.put(route('pages.updateActivity', pageId), { activity: newActivity })
            .then(response => {
                if (response.data.reload) {
                    window.location.reload(); // Перезагрузка страницы для отображения новых данных
                }
            })
            .catch(error => {
                console.error(error.response.data);
            });
    });
};

// Функция удаления для массовых действий
const bulkDelete = () => {
    axios.delete(route('pages.bulkDestroy'), { data: { ids: selectedPages.value } })
        .then(response => {
            selectedPages.value = [];
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Функции для массовых действий
const handleBulkAction = (event) => {
    const action = event.target.value;
    if (action === 'selectAll') {
        paginatedPages.value.forEach(page => {
            if (!selectedPages.value.includes(page.id)) {
                selectedPages.value.push(page.id);
            }
        });
    } else if (action === 'deselectAll') {
        selectedPages.value = [];
    } else if (action === 'activate') {
        bulkToggleActivity(true);
    } else if (action === 'deactivate') {
        bulkToggleActivity(false);
    } else if (action === 'delete') {
        bulkDelete();
    } else if (action === 'showInMenu') {
        bulkTogglePrintInMenu(true);
    } else if (action === 'hideFromMenu') {
        bulkTogglePrintInMenu(false);
    }
    event.target.value = ''; // Сбросить выбранное значение после выполнения действия
};

</script>

<template>
    <AdminLayout :title="t('sitePages')">
        <template #header>
            <TitlePage>
                {{ t('sitePages') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('pages.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addPage') }}
                    </DefaultButton>
                    <BulkActionSelect @change="handleBulkAction" />
                </div>
                <SearchInput v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable> {{ pagesCount }}</CountTable>
                <PageTable
                    :pages="paginatedPages"
                    :selected-pages="selectedPages"
                    @toggle-activity="toggleActivity"
                    @toggle-printInMenu="togglePrintInMenu"
                    @delete="confirmDelete"
                    @clone="clonePage"
                    @recalculate-sort="recalculateSort"
                    @toggle-select="toggleSelectPage"
                    @toggle-all="toggleAll"
                    />
                <div class="flex justify-between items-center flex-col md:flex-row my-1">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredPages.length"
                                @update:currentPage="currentPage = $event"/>
                    <SortSelect :sortParam="sortParam" @update:sortParam="val => sortParam = val"/>
                </div>
            </div>
        </div>

        <DangerModal
            :show="showConfirmDeleteModal"
            @close="closeModal"
            :onCancel="closeModal"
            :onConfirm="deletePage"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
