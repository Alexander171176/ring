<script setup>
import {defineProps, ref, computed, watch} from 'vue';
import {useForm} from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import Pagination from '@/Components/Admin/Pagination/Pagination.vue';
import SearchInput from '@/Components/Admin/Search/SearchInput.vue';
import VideoTable from '@/Components/Admin/Video/Table/VideoTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import ItemsPerPageSelect from "@/Components/Admin/Select/ItemsPerPageSelect.vue";
import SortSelect from "@/Components/Admin/Video/Sort/SortSelect.vue";

const { t } = useI18n();
const props = defineProps(['videos', 'videosCount', 'adminCountVideos', 'adminSortVideos']);
const form = useForm({});

// Используем значение из props для начального количества элементов на странице
const itemsPerPage = ref(props.adminCountVideos)

// чтобы при изменении itemsPerPage автоматически обновлялся параметр в базе,
watch(itemsPerPage, (newVal) => {
    axios.put(route('settings.updateAdminCountVideos'), { value: newVal.toString() })
        .then(response => {
            // console.log('Количество элементов на странице обновлено:', response.data.value)
        })
        .catch(error => {
            console.error('Ошибка обновления настройки:', error.response.data)
        })
})

// параметр сортировки по умолчанию, устанавливаем из props
const sortParam = ref(props.adminSortVideos)
watch(sortParam, (newVal) => {
    axios.put(route('settings.updateAdminSortVideos'), { value: newVal })
        .then(response => {
            // console.log('Сортировка обновлена:', response.data.value)
        })
        .catch(error => {
            console.error('Ошибка обновления сортировки:', error.response.data)
        })
})

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const videoToDeleteId = ref(null);
const confirmDelete = (id) => {
    videoToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление разрешения
const deleteVideo = () => {
    if (videoToDeleteId.value !== null) {
        form.delete(route('videos.destroy', videoToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Пагинация
const currentPage = ref(1);

// Строка поиска
const searchQuery = ref('');

// Функция сортировки
const sortVideos = (videos) => {
    // Добавляем сортировку по id в двух направлениях:
    if (sortParam.value === 'idAsc') {
        return videos.slice().sort((a, b) => a.id - b.id);
    }
    if (sortParam.value === 'idDesc') {
        return videos.slice().sort((a, b) => b.id - a.id);
    }
    // Для остальных полей — стандартное сравнение:
    return videos.slice().sort((a, b) => {
        if (a[sortParam.value] < b[sortParam.value]) return -1
        if (a[sortParam.value] > b[sortParam.value]) return 1
        return 0
    });
};

// Фильтр поиска
const filteredVideos = computed(() => {
    let filtered = props.videos;

    if (searchQuery.value) {
        filtered = filtered.filter(video =>
            video.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortVideos(filtered);
});

// Пагинация
const paginatedVideos = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredVideos.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredVideos.value.length / itemsPerPage.value));
</script>

<template>
    <AdminLayout :title="t('videos')">
        <template #header>
            <TitlePage>
                {{ t('videos') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('videos.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addVideo') }}
                    </DefaultButton>
                </div>
                <SearchInput v-if="videosCount" v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable v-if="videosCount"> {{ videosCount }} </CountTable>
                <VideoTable
                    :videos="paginatedVideos"
                    @delete="confirmDelete"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1"  v-if="videosCount">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredVideos.length"
                                @update:currentPage="currentPage = $event"
                                @update:itemsPerPage="itemsPerPage = $event"/>
                    <SortSelect :sortParam="sortParam" @update:sortParam="val => sortParam = val"/>
                </div>
            </div>
        </div>

        <DangerModal
            :show="showConfirmDeleteModal"
            @close="closeModal"
            :onCancel="closeModal"
            :onConfirm="deleteVideo"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
