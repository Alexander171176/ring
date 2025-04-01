<script setup>
import {defineProps, ref, computed, watch} from 'vue';
import {useForm} from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import Pagination from '@/Components/Admin/Pagination/Pagination.vue';
import SearchInput from '@/Components/Admin/Search/SearchInput.vue';
import RoleTable from '@/Components/Admin/Role/Table/RoleTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";
import ItemsPerPageSelect from "@/Components/Admin/Select/ItemsPerPageSelect.vue";

const { t } = useI18n();
const props = defineProps(['roles', 'rolesCount', 'adminCountRoles']);
const form = useForm({});

// Используем значение из props для начального количества элементов на странице
const itemsPerPage = ref(props.adminCountRoles)

// чтобы при изменении itemsPerPage автоматически обновлялся параметр в базе,
watch(itemsPerPage, (newVal) => {
    axios.put(route('settings.updateAdminCountRoles'), { value: newVal.toString() })
        .then(response => {
            // console.log('Количество элементов на странице обновлено:', response.data.value)
        })
        .catch(error => {
            console.error('Ошибка обновления настройки:', error.response.data)
        })
})

// Модальное окно удаления
const showConfirmDeleteModal = ref(false);
const roleToDeleteId = ref(null);
const confirmDelete = (id) => {
    roleToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};
const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

// Удаление роли
const deleteRole = () => {
    if (roleToDeleteId.value !== null) {
        form.delete(route('roles.destroy', roleToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

// Пагинация
const currentPage = ref(1);

// Строка поиска
const searchQuery = ref('');

// Параметр сортировки
const sortParam = ref('id');

// Функция сортировки
const sortRoles = (roles) => {
    return roles.slice().sort((a, b) => {
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
const filteredRoles = computed(() => {
    let filtered = props.roles;

    if (searchQuery.value) {
        filtered = filtered.filter(role =>
            role.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortRoles(filtered);
});

// Пагинация
const paginatedRoles = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredRoles.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredRoles.value.length / itemsPerPage.value));
</script>

<template>
    <AdminLayout :title="t('roles')">
        <template #header>
            <TitlePage>
                {{ t('roles') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка добавить -->
                    <DefaultButton :href="route('roles.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addRole') }}
                    </DefaultButton>
                </div>
                <SearchInput v-if="rolesCount" v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable v-if="rolesCount"> {{ rolesCount }} </CountTable>
                <RoleTable
                    :roles="paginatedRoles"
                    @delete="confirmDelete"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1" v-if="rolesCount">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredRoles.length"
                                @update:currentPage="currentPage = $event"
                                @update:itemsPerPage="itemsPerPage = $event"/>
                </div>
            </div>
        </div>

        <DangerModal
            :show="showConfirmDeleteModal"
            @close="closeModal"
            :onCancel="closeModal"
            :onConfirm="deleteRole"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
