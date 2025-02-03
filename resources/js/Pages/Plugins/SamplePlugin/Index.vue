<script setup>
import {ref, onMounted, computed} from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from './Part/TitlePage.vue';
import SearchInput from './Part/SearchInput.vue';
import CountTable from './Part/CountTable.vue';
import EditBlockModal from './Part/EditBlockModal.vue';
import ActionButton from './Part/ActionButton.vue';
import ActivityToggle from './Part/ActivityToggle.vue';
import PrimaryButton from './Part/PrimaryButton.vue';
import Pagination from './Part/Pagination.vue';
import ItemsPerPageSelect from './Part/ItemsPerPageSelect.vue';
import SortSelect from './Part/SortSelect.vue';
import SamplePlugin from './Public/SamplePlugin.vue';
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

// приходящие pluginName и id
const props = defineProps({
    pluginName: {
        type: String,
        required: true
    },
    id: {
        type: Number,
        default: 0,
    }
});

// проверяем текущий url админка или нет
const isAdmin = ref(window.location.pathname.includes('/admin'));

// актиность плагина по умолчанию
const isActive = ref(false);

// модальное окно инициализация
const showEditModal = ref(false);
// редактирование блока инициализация иначе создание
const isEdit = ref(false);
// выбор блока
const selectedBlock = ref(null);
// массив блоков инициализация
const blocks = ref([]);

// запись всех блоков с БД в массив в формате json
const fetchBlocks = async () => {
    try {
        const response = await axios.get(`/api/plugins/${props.pluginName}/blocks`);
        blocks.value = response.data;
        // console.log('Загруженные блоки:', blocks.value);
    } catch (error) {
        // console.error('Ошибка при получении блоков:', error.response ? error.response.data : error.message);
    }
};

// модальное окно создания блока и значения по умолчанию
const openCreateModal = () => {
    selectedBlock.value = {links: '', svg_blocks: '', title: '', paragraph: ''};
    isEdit.value = false;
    showEditModal.value = true;
};

// модальное окно редактирования блока текущих значений в БД
const editBlock = (block) => {
    selectedBlock.value = {...block};
    isEdit.value = true;
    showEditModal.value = true;
};

// удаление блока
const deleteBlock = async (id) => {
    try {
        await axios.delete(`/api/plugins/${props.pluginName}/blocks/${id}`);
        await fetchBlocks();
    } catch (error) {
        console.error('Ошибка при удалении блока:', error.response ? error.response.data : error.message);
    }
};

// проверка и создание таблицы плагина
const createPluginTable = async () => {
    try {
        const response = await axios.post('/api/plugins/create-table', {
            pluginName: props.pluginName,
            fields: [
                {name: 'links', type: 'text'},
                {name: 'svg_blocks', type: 'text'},
                {name: 'title', type: 'text'},
                {name: 'paragraph', type: 'text'},
                {name: 'sort', type: 'integer'},
                {name: 'activity', type: 'boolean'}
            ],
            initialData: [
                {
                    links: 'https://laravel.com/docs/10.x',
                    svg_blocks: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-7 h-7 stroke-red-500"> <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/> </svg>',
                    title: 'Документация Laravel',
                    paragraph: 'В Laravel есть замечательная документация, охватывающая каждый аспект фреймворка. Независимо от того, являетесь ли вы новичком или имеете предыдущий опыт работы с Laravel, мы рекомендуем прочитать нашу документацию от начала до конца.',
                    sort: '1',
                    activity: 'true'
                },
                {
                    links: 'https://laracasts.com',
                    svg_blocks: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-7 h-7 stroke-red-500"> <path stroke-linecap="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 004.5 7.5v9a2.25 2.25 0 002.25 2.25z"/> </svg>',
                    title: 'Laracasts',
                    paragraph: 'Laracasts предлагает тысячи видеоуроков по разработке на Laravel, PHP и JavaScript. Ознакомьтесь с ними, убедитесь сами и значительно повысьте свои навыки разработки в процессе.',
                    sort: '2',
                    activity: 'true'
                },
                {
                    links: 'https://laravel-news.com',
                    svg_blocks: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-7 h-7 stroke-red-500"> <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"/> </svg>',
                    title: 'Laravel News',
                    paragraph: 'Laravel News - это управляемый сообществом портал и информационная рассылка, объединяющая все последние и наиболее важные новости в экосистеме Laravel, включая новые выпуски пакетов и учебные пособия.',
                    sort: '3',
                    activity: 'false'
                }
            ]  // пустой массив, если начальные данные не нужны
        });
        console.log(response.data.message);
    } catch (error) {
        console.error('Ошибка при создании таблицы плагинов:', error.response ? error.response.data : error.message);
    }
};

// проверка активности плагина
const checkPluginActivity = async () => {
    try {
        await createPluginTable();
        await fetchBlocks();
        const response = await axios.get(`/api/plugins/active`);
        const activePlugins = response.data;
        isActive.value = activePlugins.some(plugin => plugin.name === props.pluginName);
    } catch (error) {
        console.error('Ошибка при проверке активности плагина:', error.response ? error.response.data : error.message);
    }
};

// строка поиска
const searchQuery = ref('');

// Параметр сортировки
const sortParam = ref('id');

// Функция сортировки
const sortBlocks = (blocks) => {
    if (sortParam.value === 'activity') {
        return blocks.filter(block => block.activity);
    }
    if (sortParam.value === 'inactive') {
        return blocks.filter(block => !block.activity);
    }
    return blocks.slice().sort((a, b) => {
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
const filteredBlocks = computed(() => {
    let filtered = blocks.value;

    if (searchQuery.value) {
        filtered = filtered.filter(block =>
            block.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return sortBlocks(filtered);
});

// Пагинация
const currentPage = ref(1);
const itemsPerPage = ref(10); // Количество элементов на странице

const paginatedBlocks = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredBlocks.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredBlocks.value.length / itemsPerPage.value));

// Количество всех блоков
const totalBlocks = computed(() => blocks.value.length);

// Функция сортировки по полю sort
const recalculateSort = (event) => {
    paginatedBlocks.value.forEach((block, index) => {
        block.sort = index + 1;
        axios.put(`/api/plugins/${props.pluginName}/blocks/${block.id}/sort`, {sort: block.sort})
            .then(response => console.log(`Обновлена сортировка по идентификатору ${block.id} на ${block.sort}`))
            .catch(error => console.error(error.response.data));
    });
};

// Функция клика активно/неактивно для блока
const toggleActivity = async (block) => {
    const newActivity = !block.activity;
    try {
        await axios.put(`/api/plugins/${props.pluginName}/blocks/${block.id}`, {activity: newActivity});
        block.activity = newActivity;
    } catch (error) {
        console.error('Ошибка при обновлении активности блока:', error.response ? error.response.data : error.message);
    }
};

onMounted(() => {
    checkPluginActivity();
});
</script>

<template>
    <AdminLayout v-if="isAdmin" :title="props.pluginName">
        <template #header>
            <TitlePage>
                {{ props.pluginName }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <PrimaryButton @click="openCreateModal">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('add') }}
                    </PrimaryButton>
                </div>
                <EditBlockModal
                    :block="selectedBlock"
                    :show="showEditModal"
                    :isEdit="isEdit"
                    :pluginName="props.pluginName"
                    @close="showEditModal = false"
                    @update="fetchBlocks"
                />
                <SearchInput v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable> {{ totalBlocks }}</CountTable>
                <template v-if="paginatedBlocks.length > 0">
                    <table class="table-auto w-full text-slate-700 dark:text-slate-100">
                        <thead
                            class="text-md font-semibold uppercase bg-slate-200 dark:bg-cyan-900 border border-solid border-gray-300 dark:border-gray-700">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="font-semibold text-center">ID</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Ссылка блока</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">SVG</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Текст заголовка</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center">Действия</div>
                            </th>
                        </tr>
                        </thead>
                        <draggable tag="tbody" :list="paginatedBlocks" @end="recalculateSort" itemKey="id">
                            <template #item="{ element: block }">
                                <tr class="border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                        <div class="text-center">{{ block.id }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                        <div class="text-left text-blue-600 dark:text-violet-200 font-semibold">
                                            {{ block.links }}
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                        <div v-html="block.svg_blocks"></div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                        <div class="text-left font-semibold">{{ block.title }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap">
                                        <div class="flex justify-center space-x-2">
                                            <ActivityToggle :isActive="block.activity"
                                                            @toggle-activity="toggleActivity(block)"
                                                            :title="block.activity ? t('enabled') : t('disabled')"/>
                                            <ActionButton
                                                action="edit"
                                                title="Редактировать"
                                                @click="editBlock(block)"
                                            >
                                                <template #icon>
                                                    <svg class="w-4 h-4 fill-current
                                                            text-sky-500
                                                            hover:text-sky-700
                                                            dark:text-sky-300
                                                            dark:hover:text-sky-100
                                                            shrink-0"
                                                         viewBox="0 0 16 16">
                                                        <path
                                                            d="M11.7.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM4.6 14H2v-2.6l6-6L10.6 8l-6 6zM12 6.6L9.4 4 11 2.4 13.6 5 12 6.6z"/>
                                                    </svg>
                                                </template>
                                            </ActionButton>
                                            <ActionButton
                                                action="delete"
                                                title="Удалить"
                                                @click="deleteBlock(block.id)"
                                            >
                                                <template #icon>
                                                    <svg class="w-4 h-4 fill-current
                                                            text-rose-400
                                                            hover:text-rose-500
                                                            dark:text-red-300
                                                            dark:hover:text-red-100
                                                            shrink-0"
                                                         viewBox="0 0 16 16">
                                                        <path
                                                            d="M5 7h2v6H5V7zm4 0h2v6H9V7zm3-6v2h4v2h-1v10c0 .6-.4 1-1 1H2c-.6 0-1-.4-1-1V5H0V3h4V1c0-.6.4-1 1-1h6c.6 0 1 .4 1 1zM6 2v1h4V2H6zm7 3H3v9h10V5z"/>
                                                    </svg>
                                                </template>
                                            </ActionButton>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </draggable>
                    </table>
                </template>
                <template v-else>
                    <div class="p-5 text-center text-slate-700 dark:text-slate-100">
                        {{ t('noData') }}
                    </div>
                </template>
                <div class="flex justify-between items-center flex-col md:flex-row">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event"/>
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredBlocks.length"
                                @update:currentPage="currentPage = $event"
                                @update:itemsPerPage="itemsPerPage = $event"/>
                    <SortSelect :sortParam="sortParam" @update:sortParam="val => sortParam = val"/>
                </div>
            </div>
        </div>
    </AdminLayout>
    <SamplePlugin
        v-if="!isAdmin && isActive"
        :pluginName="props.pluginName"
        :isActive="isActive"
        :id="id"
        :blocks="blocks"
    />
</template>
