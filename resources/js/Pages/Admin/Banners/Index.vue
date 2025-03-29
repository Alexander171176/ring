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
import SortSelect from '@/Components/Admin/Banner/Sort/SortSelect.vue';
import BannerTable from '@/Components/Admin/Banner/Table/BannerTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from '@/Components/Admin/Banner/Select/BulkActionSelect.vue';
import axios from 'axios';

const { t } = useI18n();

const props = defineProps(['banners', 'bannersCount']);

const form = useForm({});

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const bannerToDeleteId = ref(null);
const confirmDelete = (id) => {
    bannerToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление статьи
const deleteBanner = () => {
    if (bannerToDeleteId.value !== null) {
        form.delete(route('banners.destroy', bannerToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Кнопка включения статьи в левой колонке
const toggleLeft = (banner) => {
    const newLeft = !banner.left;
    axios.put(route('banners.updateLeft', banner.id), { left: newLeft })
        .then(response => {
            banner.left = newLeft;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Кнопка включения статьи в правой колонке
const toggleRight = (banner) => {
    const newRight = !banner.right;
    axios.put(route('banners.updateRight', banner.id), { right: newRight })
        .then(response => {
            banner.right = newRight;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Кнопка активности
const toggleActivity = (banner) => {
    const newActivity = !banner.activity;
    axios.put(route('banners.updateActivity', banner.id), { activity: newActivity })
        .then(response => {
            banner.activity = newActivity;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Пагинация
const currentPage = ref(1);
const itemsPerPage = ref(10); // Количество элементов на странице

// Строка поиска
const searchQuery = ref('');

// Параметр сортировки
const sortParam = ref('idDesc');

// Функция сортировки
const sortBanners = (banners) => {
    // Фильтры для отдельных состояний остаются прежними:
    if (sortParam.value === 'activity') {
        return banners.filter(banner => banner.activity);
    }
    if (sortParam.value === 'inactive') {
        return banners.filter(banner => !banner.activity);
    }
    if (sortParam.value === 'left') {
        return banners.filter(banner => banner.left);
    }
    if (sortParam.value === 'noLeft') {
        return banners.filter(banner => !banner.left);
    }
    if (sortParam.value === 'right') {
        return banners.filter(banner => banner.right);
    }
    if (sortParam.value === 'noRight') {
        return banners.filter(banner => !banner.right);
    }

    // Добавляем сортировку по id в двух направлениях:
    if (sortParam.value === 'idAsc') {
        return banners.slice().sort((a, b) => a.id - b.id);
    }
    if (sortParam.value === 'idDesc') {
        return banners.slice().sort((a, b) => b.id - a.id);
    }

    // Для просмотров и лайков сортировка по убыванию:
    if (sortParam.value === 'views' || sortParam.value === 'likes') {
        return banners.slice().sort((a, b) => b[sortParam.value] - a[sortParam.value]);
    }

    // Для остальных полей — стандартное сравнение:
    return banners.slice().sort((a, b) => {
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
const filteredBanners = computed(() => {
    let filtered = props.banners;

    if (searchQuery.value) {
        filtered = filtered.filter(banner =>
            banner.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortBanners(filtered);
});

// Пагинация
const paginatedBanners = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredBanners.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredBanners.value.length / itemsPerPage.value));

// Drag and drop
const recalculateSort = (event) => {
    paginatedBanners.value.forEach((banner, index) => {
        banner.sort = index + 1;
        axios.put(route('banners.updateSort', banner.id), { sort: banner.sort })
            .then(
                // response => console.log(`Обновлена сортировка по идентификатору ${banner.id} на ${banner.sort}`)
            )
            .catch(
                error => console.error(error.response.data)
            );
    });
};

// Выбранные статьи для массовых действий
const selectedBanners = ref([]);

// Функции для выбора и отмены выбора всех элементов select
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    selectedBanners.value = isChecked ? paginatedBanners.value.map(banner => banner.id) : [];
};

const toggleSelectBanner = (bannerId) => {
    const index = selectedBanners.value.indexOf(bannerId);
    if (index > -1) {
        selectedBanners.value.splice(index, 1);
    } else {
        selectedBanners.value.push(bannerId);
    }
};

// Функции для массового включения/выключения активности
const bulkToggleActivity = (newActivity) => {
    const updatePromises = selectedBanners.value.map((bannerId) =>
        axios.put(route('banners.updateActivity', bannerId), { activity: newActivity })
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

// Функции для массового включения/выключения в левую колонку
const bulkToggleLeft = (newLeft) => {
    const updatePromises = selectedBanners.value.map((bannerId) =>
        axios.put(route('banners.updateLeft', bannerId), { left: newLeft })
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

// Функции для массового включения/выключения в правую колонку
const bulkToggleRight = (newRight) => {
    const updatePromises = selectedBanners.value.map((bannerId) =>
        axios.put(route('banners.updateRight', bannerId), { right: newRight })
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
    axios.delete(route('banners.bulkDestroy'), { data: { ids: selectedBanners.value } })
        .then(response => {
            selectedBanners.value = [];
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
        paginatedBanners.value.forEach(banner => {
            if (!selectedBanners.value.includes(banner.id)) {
                selectedBanners.value.push(banner.id);
            }
        });
    } else if (action === 'deselectAll') {
        selectedBanners.value = [];
    } else if (action === 'activate') {
        bulkToggleActivity(true);
    } else if (action === 'deactivate') {
        bulkToggleActivity(false);
    } else if (action === 'left') {
        bulkToggleLeft(true);
    } else if (action === 'noLeft') {
        bulkToggleLeft(false);
    } else if (action === 'right') {
        bulkToggleRight(true);
    } else if (action === 'noRight') {
        bulkToggleRight(false);
    } else if (action === 'delete') {
        bulkDelete();
    }
    event.target.value = ''; // Сбросить выбранное значение после выполнения действия
};

</script>

<template>
    <AdminLayout :title="t('banners')">
        <template #header>
            <TitlePage>
                {{ t('banners') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('banners.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addBanner') }}
                    </DefaultButton>
                    <BulkActionSelect v-if="bannersCount" @change="handleBulkAction" />
                </div>
                <SearchInput v-if="bannersCount" v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable v-if="bannersCount"> {{ bannersCount }}</CountTable>
                <BannerTable
                    :banners="paginatedBanners"
                    :selected-banners="selectedBanners"
                    @toggle-left="toggleLeft"
                    @toggle-right="toggleRight"
                    @toggle-activity="toggleActivity"
                    @delete="confirmDelete"
                    @recalculate-sort="recalculateSort"
                    @toggle-select="toggleSelectBanner"
                    @toggle-all="toggleAll"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1" v-if="bannersCount">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredBanners.length"
                                @update:currentPage="currentPage = $event"/>
                    <SortSelect :sortParam="sortParam" @update:sortParam="val => sortParam = val"/>
                </div>
            </div>
        </div>

        <DangerModal
            :show="showConfirmDeleteModal"
            @close="closeModal"
            :onCancel="closeModal"
            :onConfirm="deleteBanner"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
