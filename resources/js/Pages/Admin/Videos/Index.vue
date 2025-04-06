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
import BulkActionSelect from '@/Components/Admin/Video/Select/BulkActionSelect.vue';
import axios from 'axios';

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

// Удаление видео
const deleteVideo = () => {
    if (videoToDeleteId.value !== null) {
        form.delete(route('videos.destroy', videoToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Кнопка включения видео в левой колонке
const toggleLeft = (video) => {
    const newLeft = !video.left;
    axios.put(route('videos.updateLeft', video.id), { left: newLeft })
        .then(response => {
            video.left = newLeft;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Кнопка включения видео как главной
const toggleMain = (video) => {
    const newMain = !video.main;
    axios.put(route('videos.updateMain', video.id), { main: newMain })
        .then(response => {
            video.main = newMain;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Кнопка включения видео в правой колонке
const toggleRight = (video) => {
    const newRight = !video.right;
    axios.put(route('videos.updateRight', video.id), { right: newRight })
        .then(response => {
            video.right = newRight;
            if (response.data.reload) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

// Кнопка активности
const toggleActivity = (video) => {
    const newActivity = !video.activity;
    axios.put(route('videos.updateActivity', video.id), { activity: newActivity })
        .then(response => {
            video.activity = newActivity;
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
    if (sortParam.value === 'activity') {
        return videos.filter(video => video.activity);
    }
    if (sortParam.value === 'inactive') {
        return videos.filter(video => !video.activity);
    }
    if (sortParam.value === 'left') {
        return videos.filter(video => video.left);
    }
    if (sortParam.value === 'noLeft') {
        return videos.filter(video => !video.left);
    }
    if (sortParam.value === 'main') {
        return videos.filter(video => video.main);
    }
    if (sortParam.value === 'noMain') {
        return videos.filter(video => !video.main);
    }
    if (sortParam.value === 'right') {
        return videos.filter(video => video.right);
    }
    if (sortParam.value === 'noRight') {
        return videos.filter(video => !video.right);
    }
    if (sortParam.value === 'locale') {
        // Сортировка по locale в обратном порядке
        return videos.slice().sort((a, b) => {
            if (a.locale < b.locale) return 1;
            if (a.locale > b.locale) return -1;
            return 0;
        });
    }
    // Для просмотров и лайков сортировка по убыванию:
    if (sortParam.value === 'views' || sortParam.value === 'likes') {
        return videos.slice().sort((a, b) => b[sortParam.value] - a[sortParam.value]);
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
            video.title.toLowerCase().includes(searchQuery.value.toLowerCase())
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

// Drag and drop
const recalculateSort = (event) => {
    paginatedVideos.value.forEach((video, index) => {
        video.sort = index + 1;
        axios.put(route('videos.updateSort', video.id), { sort: video.sort })
            .then(
                // response => console.log(`Обновлена сортировка по идентификатору ${video.id} на ${video.sort}`)
            )
            .catch(
                error => console.error(error.response.data)
            );
    });
};

// Выбранные статьи для массовых действий
const selectedVideos = ref([]);

// Функции для выбора и отмены выбора всех элементов select
const toggleAll = (event) => {
    const isChecked = event.target.checked;
    selectedVideos.value = isChecked ? paginatedVideos.value.map(video => video.id) : [];
};

const toggleSelectVideo = (videoId) => {
    const index = selectedVideos.value.indexOf(videoId);
    if (index > -1) {
        selectedVideos.value.splice(index, 1);
    } else {
        selectedVideos.value.push(videoId);
    }
};

// Функции для массового включения/выключения активности
const bulkToggleActivity = (newActivity) => {
    const updatePromises = selectedVideos.value.map((videoId) =>
        axios.put(route('videos.updateActivity', videoId), { activity: newActivity })
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
    const updatePromises = selectedVideos.value.map((videoId) =>
        axios.put(route('videos.updateLeft', videoId), { left: newLeft })
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

// Функции для массового включения/выключения в слайдер
const bulkToggleMain = (newMain) => {
    const updatePromises = selectedVideos.value.map((videoId) =>
        axios.put(route('videos.updateMain', videoId), { main: newMain })
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
    const updatePromises = selectedVideos.value.map((videoId) =>
        axios.put(route('videos.updateRight', videoId), { right: newRight })
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
    axios.delete(route('videos.bulkDestroy'), { data: { ids: selectedVideos.value } })
        .then(response => {
            selectedVideos.value = [];
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
        paginatedVideos.value.forEach(video => {
            if (!selectedVideos.value.includes(video.id)) {
                selectedVideos.value.push(video.id);
            }
        });
    } else if (action === 'deselectAll') {
        selectedVideos.value = [];
    } else if (action === 'activate') {
        bulkToggleActivity(true);
    } else if (action === 'deactivate') {
        bulkToggleActivity(false);
    } else if (action === 'left') {
        bulkToggleLeft(true);
    } else if (action === 'noLeft') {
        bulkToggleLeft(false);
    } else if (action === 'main') {
        bulkToggleMain(true);
    } else if (action === 'noMain') {
        bulkToggleMain(false);
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
                    <BulkActionSelect v-if="videosCount" @change="handleBulkAction" />
                </div>
                <SearchInput v-if="videosCount" v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable v-if="videosCount"> {{ videosCount }} </CountTable>
                <VideoTable
                    :videos="paginatedVideos"
                    :selected-videos="selectedVideos"
                    @toggle-left="toggleLeft"
                    @toggle-main="toggleMain"
                    @toggle-right="toggleRight"
                    @toggle-activity="toggleActivity"
                    @delete="confirmDelete"
                    @recalculate-sort="recalculateSort"
                    @toggle-select="toggleSelectVideo"
                    @toggle-all="toggleAll"
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
