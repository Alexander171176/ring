<script setup>
import {defineProps, ref, computed} from 'vue';
import {useForm} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import Pagination from '@/Components/Admin/Pagination/Pagination.vue';
import ItemsPerPageSelect from '@/Components/Admin/Select/ItemsPerPageSelect.vue';
import SearchInput from '@/Components/Admin/Search/SearchInput.vue';
import SortSelect from '@/Components/Admin/Parameters/Sort/SortSelect.vue';
import ParameterTable from '@/Components/Admin/Parameters/Table/ParameterTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import axios from 'axios';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";

const {t} = useI18n();

const props = defineProps(['settings', 'settingsCount']);

const form = useForm({});

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const settingToDeleteId = ref(null);
const confirmDelete = (id) => {
    settingToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление параметра
const deleteSetting = () => {
    if (settingToDeleteId.value !== null) {
        form.delete(route('parameters.destroy', settingToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Кнопка активности
const toggleActivity = (setting) => {
    const newActivity = !setting.activity;
    axios.put(route('settings.updateActivity', setting.id), {activity: newActivity})
        .then(response => {
            if (response.data.reload) {
                window.location.reload();
            } else {
                setting.activity = newActivity;
            }
        })
        .catch(error => {
            console.error("Ошибка при обновлении активности параметра:", error.response?.data || error.message);
        });
};

// Автоматическое заполнение constant при фокусе
const handleConstantFocus = () => {
    if (form.option) {
        form.constant = form.option.replace(/([a-z0-9])([A-Z])/g, '$1_$2').toUpperCase();
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
const sortSettings = (settings) => {
    if (sortParam.value === 'activity') {
        return settings.filter(setting => setting.activity);
    }
    if (sortParam.value === 'inactive') {
        return settings.filter(setting => !setting.activity);
    }
    return settings.slice().sort((a, b) => {
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
const filteredSettings = computed(() => {
    let filtered = props.settings;

    if (searchQuery.value) {
        filtered = filtered.filter(setting =>
            setting.option.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortSettings(filtered);
});

// Пагинация
const paginatedSettings = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredSettings.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredSettings.value.length / itemsPerPage.value));
</script>

<template>
    <AdminLayout :title="t('parametersHeader')">
        <template #header>
            <TitlePage>
                {{ t('parametersHeader') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">

            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('parameters.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addParameter') }}
                    </DefaultButton>
                </div>
                <SearchInput v-if="settingsCount" v-model="searchQuery" :placeholder="t('searchByParameter')"/>
                <span class="mb-4 py-1 px-3
                         w-full flex items-center justify-center
                         text-md italic font-semibold text-rose-400
                         bg-amber-50 opacity-80 border border-rose-200">
                    {{ t('componentParametersWarning') }}
                </span>
                <CountTable v-if="settingsCount"> {{ settingsCount }}</CountTable>
                <ParameterTable
                    :settings="paginatedSettings"
                    @toggle-activity="toggleActivity"
                    @delete="confirmDelete"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1" v-if="settingsCount">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event"/>
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredSettings.length"
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
            :onConfirm="deleteSetting"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
