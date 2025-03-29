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
import PermissionTable from '@/Components/Admin/Permission/Table/PermissionTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';

const { t } = useI18n();

const props = defineProps(['permissions', 'permissionsCount']);

const form = useForm({});

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const permissionToDeleteId = ref(null);
const confirmDelete = (id) => {
    permissionToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление разрешения
const deletePermission = () => {
    if (permissionToDeleteId.value !== null) {
        form.delete(route('permissions.destroy', permissionToDeleteId.value), {
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
const sortPermissions = (permissions) => {
    return permissions.slice().sort((a, b) => {
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
const filteredPermissions = computed(() => {
    let filtered = props.permissions;

    if (searchQuery.value) {
        filtered = filtered.filter(permission =>
            permission.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortPermissions(filtered);
});

// Пагинация
const paginatedPermissions = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredPermissions.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredPermissions.value.length / itemsPerPage.value));
</script>

<template>
    <AdminLayout :title="t('permissions')">
        <template #header>
            <TitlePage>
                {{ t('permissions') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('permissions.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addPermission') }}
                    </DefaultButton>
                </div>
                <SearchInput v-if="permissionsCount" v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable v-if="permissionsCount"> {{ permissionsCount }} </CountTable>
                <PermissionTable
                    :permissions="paginatedPermissions"
                    @delete="confirmDelete"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1"  v-if="permissionsCount">
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredPermissions.length"
                                @update:currentPage="currentPage = $event"
                                @update:itemsPerPage="itemsPerPage = $event"/>
                </div>
            </div>
        </div>

        <DangerModal
            :show="showConfirmDeleteModal"
            @close="closeModal"
            :onCancel="closeModal"
            :onConfirm="deletePermission"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
