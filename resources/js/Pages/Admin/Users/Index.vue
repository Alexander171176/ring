<script setup>
import {defineProps, ref, computed, watch} from 'vue';
import {useForm} from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import Pagination from '@/Components/Admin/Pagination/Pagination.vue';
import SearchInput from '@/Components/Admin/Search/SearchInput.vue';
import UserTable from '@/Components/Admin/User/Table/UserTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";
import ItemsPerPageSelect from "@/Components/Admin/Select/ItemsPerPageSelect.vue";

const { t } = useI18n();
const props = defineProps(['users', 'usersCount', 'adminCountUsers', 'roles', 'permissions']);
const form = useForm({});

// Используем значение из props для начального количества элементов на странице
const itemsPerPage = ref(props.adminCountUsers)

// чтобы при изменении itemsPerPage автоматически обновлялся параметр в базе,
watch(itemsPerPage, (newVal) => {
    axios.put(route('settings.updateAdminCountUsers'), { value: newVal.toString() })
        .then(response => {
            // console.log('Количество элементов на странице обновлено:', response.data.value)
        })
        .catch(error => {
            console.error('Ошибка обновления настройки:', error.response.data)
        })
})

const showConfirmDeleteModal = ref(false);
const userToDeleteId = ref(null);

const confirmDelete = (id) => {
    userToDeleteId.value = id;
    showConfirmDeleteModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteModal.value = false;
};

const deleteUser = () => {
    if (userToDeleteId.value !== null) {
        form.delete(route('users.destroy', userToDeleteId.value), {
            onSuccess: () => closeModal()
        });
    }
};

const currentPage = ref(1);
const searchQuery = ref('');
const sortParam = ref('id');

const sortUsers = (users) => {
    return users.slice().sort((a, b) => {
        if (a[sortParam.value] < b[sortParam.value]) {
            return -1;
        }
        if (a[sortParam.value] > b[sortParam.value]) {
            return 1;
        }
        return 0;
    });
};

const filteredUsers = computed(() => {
    let filtered = props.users;

    if (searchQuery.value) {
        filtered = filtered.filter(user =>
            user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortUsers(filtered);
});

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredUsers.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value));
</script>

<template>
    <AdminLayout :title="t('users')">
        <template #header>
            <TitlePage>
                {{ t('users') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка добавить -->
                    <DefaultButton :href="route('users.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addUser') }}
                    </DefaultButton>
                </div>
                <SearchInput v-if="usersCount" v-model="searchQuery" :placeholder="t('searchByNameOrEmail')"/>
                <CountTable v-if="usersCount"> {{ usersCount }} </CountTable>
                <UserTable
                    :users="paginatedUsers"
                    @delete="confirmDelete"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1" v-if="usersCount">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination
                        :current-page="currentPage"
                        :items-per-page="itemsPerPage"
                        :total-items="filteredUsers.length"
                        @update:currentPage="currentPage = $event"
                        @update:itemsPerPage="itemsPerPage = $event"
                    />
                    <div class="flex justify-center items-center h-fit sm:mr-4 mt-2 mb-2">
                        <label for="sortParam"
                               class="hidden lg:block sm:mr-2 tracking-wider
                                      text-sm font-semibold text-slate-600 dark:text-slate-100">
                                            {{ t('sort') }}
                        </label>
                        <select id="sortParam" v-model="sortParam"
                                class="w-20 px-3 py-0.5 form-select bg-white dark:bg-gray-200
                                       text-gray-600 dark:text-gray-900
                                       border border-slate-400 dark:border-slate-600
                                       rounded-sm shadow-sm">
                            <option value="id">{{ t('id') }}</option>
                            <option value="name">{{ t('name') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <DangerModal
            :show="showConfirmDeleteModal"
            @close="closeModal"
            :onCancel="closeModal"
            :onConfirm="deleteUser"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
