<script setup>
/**
 * @version PulsarCMS 1.0
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 */
import {defineProps, ref, computed, watch} from 'vue';
import { useI18n } from 'vue-i18n';
import {useToast} from 'vue-toastification';
import {router} from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import Pagination from '@/Components/Admin/Pagination/Pagination.vue';
import SearchInput from '@/Components/Admin/Search/SearchInput.vue';
import AthleteTable from '@/Components/Admin/Athlete/Table/AthleteTable.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from "@/Components/Admin/Athlete/Select/BulkActionSelect.vue";
import ItemsPerPageSelect from "@/Components/Admin/Select/ItemsPerPageSelect.vue";
import SortSelect from "@/Components/Admin/Athlete/Sort/SortSelect.vue";

// --- Инициализация экземпляр i18n, toast ---
const { t } = useI18n();
const toast = useToast();

/**
 * Входные свойства компонента.
 */
const props = defineProps(['athletes', 'athletesCount', 'adminCountAthletes', 'adminSortAthletes']);

/**
 * Реактивная переменная для хранения текущего количества элементов на странице.
 */
const itemsPerPage = ref(props.adminCountAthletes); // Используем значение из props

/**
 * Наблюдатель за изменением количества элементов на странице.
 */
watch(itemsPerPage, (newVal) => {
    router.put(route('admin.settings.updateAdminCountAthletes'), {value: newVal}, {
        preserveScroll: true,
        preserveState: true, // Не перезагружаем все props
        onSuccess: () => toast.info(`Показ ${newVal} элементов на странице.`),
        onError: (errors) => toast.error(errors.value || 'Ошибка обновления кол-ва элементов.'),
    });
});

/**
 * Реактивная переменная для хранения текущего параметра сортировки.
 */
const sortParam = ref(props.adminSortAthletes); // Используем значение из props

/**
 * Наблюдатель за изменением параметра сортировки.
 */
watch(sortParam, (newVal) => {
    router.put(route('admin.settings.updateAdminSortAthletes'), {value: newVal}, {
        preserveScroll: true,
        preserveState: true,
        // onSuccess: () => toast.info(`Сортировка изменена на ${newVal}.`), // TODO: добавить перевод для newVal
        onSuccess: () => toast.info('Сортировка успешно изменена'),
        onError: (errors) => toast.error(errors.value || 'Ошибка обновления сортировки.'),
    });
})

/**
 * Флаг отображения модального окна подтверждения удаления.
 */
const showConfirmDeleteModal = ref(false);

/**
 * ID для удаления.
 */
const athleteToDeleteId = ref(null);

/**
 * Название для отображения в модальном окне.
 */
const athleteToDeleteNickname = ref(''); // Сохраняем название для сообщения

/**
 * Открывает модальное окно подтверждения удаления с входными переменными.
 */
const confirmDelete = (id, nickname) => {
    athleteToDeleteId.value = id;
    athleteToDeleteNickname.value = nickname;
    showConfirmDeleteModal.value = true;
};

/**
 * Закрывает модальное окно подтверждения и сбрасывает связанные переменные.
 */
const closeModal = () => {
    showConfirmDeleteModal.value = false;
    athleteToDeleteId.value = null;
    athleteToDeleteNickname.value = '';
};

// --- Логика удаления ---
/**
 * Отправляет запрос на удаление тега на сервер.
 */
const deleteAthlete = () => {
    if (athleteToDeleteId.value === null) return;

    const idToDelete = athleteToDeleteId.value; // Сохраняем ID во временную переменную
    const nicknameToDelete = athleteToDeleteNickname.value; // Сохраняем nickname во временную переменную

    router.delete(route('admin.athletes.destroy', {athlete: idToDelete}), { // Используем временную переменную
        preserveScroll: true,
        preserveState: false,
        onSuccess: (page) => {
            closeModal(); // Закрываем модалку
            toast.success(`Тег "${nicknameToDelete || 'ID: ' + idToDelete}" удален.`);
            // console.log('Удаление успешно.');
        },
        onError: (errors) => {
            closeModal();
            const errorMsg = errors.general || errors[Object.keys(errors)[0]] || 'Произошла ошибка при удалении.';
            toast.error(`${errorMsg} (Тег: ${nicknameToDelete || 'ID: ' + idToDelete})`);
            console.error('Ошибка удаления:', errors);
        },
        onFinish: () => {
            // console.log('Запрос на удаление завершен.');
            athleteToDeleteId.value = null;
            athleteToDeleteNickname.value = '';
        }
    });
};

/**
 * Отправляет запрос для изменения статуса активности.
 */
const toggleActivity = (athlete) => {
    const newActivity = !athlete.activity;
    const actionText = newActivity ? 'активирован' : 'деактивирован';

    // Используем Inertia.put для простого обновления
    router.put(route('admin.actions.athletes.updateActivity', {athlete: athlete.id}),
        {activity: newActivity},
        {
            preserveScroll: true, // Сохраняем скролл
            preserveState: true,  // Обновляем только измененные props (если бэк отдает reload: false)
            // Или false, если бэк всегда отдает reload: true и нужно перезагрузить данные
            onSuccess: () => {
                // Обновляем состояние локально СРАЗУ ЖЕ (оптимистичное обновление)
                // Или дожидаемся обновления props, если preserveState: false
                // athlete.activity = newActivity; // Уже не нужно, если preserveState: false
                toast.success(`Тег "${athlete.nickname}" ${actionText}.`);
            },
            onError: (errors) => {
                toast.error(errors.activity || errors.general || `Ошибка изменения активности для "${athlete.nickname}".`);
                // Можно откатить изменение на фронте, если нужно
                // athlete.activity = !newActivity;
            },
        }
    );
};

/**
 * Текущая страница пагинации.
 */
const currentPage = ref(1);

/**
 * Строка поискового запроса.
 */
const searchQuery = ref('');

/**
 * Сортирует массив на основе текущего параметра сортировки.
 */
const sortAthletes = (athletes) => {
    if (sortParam.value === 'idAsc') {
        return athletes.slice().sort((a, b) => a.id - b.id);
    }
    if (sortParam.value === 'idDesc') {
        return athletes.slice().sort((a, b) => b.id - a.id);
    }
    if (sortParam.value === 'activity') {
        return athletes.filter(athlete => athlete.activity);
    }
    if (sortParam.value === 'inactive') {
        return athletes.filter(athlete => !athlete.activity);
    }
    // Для просмотров и лайков сортировка по убыванию:
    if (sortParam.value === 'wins' ||
        sortParam.value === 'draws' ||
        sortParam.value === 'losses' ||
        sortParam.value === 'wins_by_ko' ||
        sortParam.value === 'wins_by_submission' ||
        sortParam.value === 'wins_by_decision' ||
        sortParam.value === 'no_contests') {
        return athletes.slice().sort((a, b) => b[sortParam.value] - a[sortParam.value]);
    }
    // Для остальных полей — стандартное сравнение:
    return athletes.slice().sort((a, b) => {
        if (a[sortParam.value] < b[sortParam.value]) return -1
        if (a[sortParam.value] > b[sortParam.value]) return 1
        return 0
    });
};

/**
 * Вычисляемое свойство, отсортированный список поиска.
 */
const filteredAthletes = computed(() => {
    let filtered = props.athletes;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();

        filtered = filtered.filter(athlete =>
            (athlete.nickname && athlete.nickname.toLowerCase().includes(query)) ||
            (athlete.first_name && athlete.first_name.toLowerCase().includes(query)) ||
            (athlete.last_name && athlete.last_name.toLowerCase().includes(query))
        );
    }

    return sortAthletes(filtered);
});

/**
 * Вычисляемое свойство пагинации, возвращающее для текущей страницы.
 */
const paginatedAthletes = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredAthletes.value.slice(start, start + itemsPerPage.value);
});

/**
 * Вычисляемое свойство, возвращающее общее количество страниц пагинации.
 */
const totalPages = computed(() => Math.ceil(filteredAthletes.value.length / itemsPerPage.value));

/**
 * Обрабатывает событие обновления порядка сортировки от компонента таблицы (Drag and drop).
 */
const handleSortOrderUpdate = (orderedIds) => {
    // console.log('Обработка обновления порядка сортировки:', orderedIds);

    // Вычисляем начальное значение sort для этой страницы
    const startSort = (currentPage.value - 1) * itemsPerPage.value;

    // Подготавливаем данные для отправки: массив объектов { id: X, sort: Y }
    const sortData = orderedIds.map((id, index) => ({
        id: id,
        sort: startSort + index + 1 // Глобальный порядок на основе позиции на странице
    }));

    // console.log('Отправка данных для сортировки:', sortData);

    // Отправляем ОДИН запрос на сервер для обновления всего порядка
    router.put(route('admin.actions.athletes.updateSortBulk'),
        {athletes: sortData}, // Отправляем массив объектов
        {
            preserveScroll: true,
            preserveState: true, // Сохраняем состояние, т.к. на сервере нет редиректа
            onSuccess: () => {
                toast.success("Порядок тегов успешно обновлен.");
                // Обновляем локальные данные (если нужно, но Inertia должна прислать обновленные props)
                // Возможно, лучше сделать preserveState: false и дождаться обновления props
            },
            onError: (errors) => {
                console.error("Ошибка обновления сортировки:", errors);
                toast.error(errors.general || errors.athletes || "Не удалось обновить порядок тегов.");
                // TODO: Откатить порядок на фронтенде? Сложно без сохранения исходного состояния.
                // Проще сделать preserveState: false или router.reload при ошибке.
                router.reload({only: ['athletes'], preserveScroll: true}); // Перезагружаем данные при ошибке
            },
        }
    );
};

/**
 * Массив выбранных ID для массовых действий.
 */
const selectedAthletes = ref([]);

/**
 * Логика выбора всех для массовых действий.
 */
const toggleAll = ({ids, checked}) => {
    if (checked) {
        // добавляем текущее множество ids
        selectedAthletes.value = [...new Set([...selectedAthletes.value, ...ids])];
    } else {
        // удаляем эти ids из выбранных
        selectedAthletes.value = selectedAthletes.value.filter(id => !ids.includes(id));
    }
};

/**
 * Обрабатывает событие выбора/снятия выбора одной строки.
 */
const toggleSelectAthlete = (athleteId) => {
    const index = selectedAthletes.value.indexOf(athleteId);
    if (index > -1) {
        selectedAthletes.value.splice(index, 1);
    } else {
        selectedAthletes.value.push(athleteId);
    }
};

/**
 * Выполняет массовое включение/выключение активности выбранных.
 */
const bulkToggleActivity = (newActivity) => {
    if (!selectedAthletes.value.length) {
        toast.warning('Выберите теги для активации/деактивации тегов');
        return;
    }
    axios
        .put(route('admin.actions.athletes.bulkUpdateActivity'), {
            ids: selectedAthletes.value,
            activity: newActivity,
        })
        .then(() => {
            toast.success('Активность массово обновлена')
            // сразу очистим выбор
            const updatedIds = [...selectedAthletes.value]
            selectedAthletes.value = []
            // и оптимистично поправим флаг в таблице
            paginatedAthletes.value.forEach((a) => {
                if (updatedIds.includes(a.id)) {
                    a.activity = newActivity
                }
            })
        })
        .catch(() => {
            toast.error('Не удалось обновить активность')
        })
};

/**
 * Выполняет массовое удаление выбранных.
 */
const bulkDelete = () => {
    if (selectedAthletes.value.length === 0) {
        toast.warning('Выберите хотя бы один тег для удаления.'); // <--- Используем toast
        return;
    }
    if (!confirm(`Вы уверены, что хотите их удалить ?`)) {
        return;
    }
    router.delete(route('admin.actions.athletes.bulkDestroy'), {
        data: {ids: selectedAthletes.value},
        preserveScroll: true,
        preserveState: false, // Перезагружаем данные страницы
        onSuccess: (page) => {
            selectedAthletes.value = []; // Очищаем выбор
            toast.success('Массовое удаление тегов успешно завершено.');
            // console.log('Массовое удаление статей успешно завершено.');
        },
        onError: (errors) => {
            console.error("Ошибка массового удаления:", errors);
            // Отображаем первую ошибку
            const errorKey = Object.keys(errors)[0];
            const errorMessage = errors[errorKey] || 'Произошла ошибка при удалении тегов.';
            toast.error(errorMessage);
        },
    });
};

/**
 * Обрабатывает выбор действия в селекте массовых действий.
 */
const handleBulkAction = (event) => {
    const action = event.target.value;
    if (action === 'selectAll') {
        // Вызываем toggleAll с имитацией события checked: true
        selectedAthletes.value = paginatedAthletes.value.map(r => r.id);
    } else if (action === 'deselectAll') {
        selectedAthletes.value = [];
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
    <AdminLayout :title="t('athletes')">
        <template #header>
            <TitlePage>
                {{ t('athletes') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('admin.athletes.create')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addAthlete') }}
                    </DefaultButton>
                    <BulkActionSelect v-if="athletesCount" @change="handleBulkAction" />
                </div>
                <SearchInput v-if="athletesCount" v-model="searchQuery" :placeholder="t('searchByName')"/>
                <CountTable v-if="athletesCount"> {{ athletesCount }} </CountTable>
                <AthleteTable
                    :athletes="paginatedAthletes"
                    :selected-athletes="selectedAthletes"
                    @toggle-activity="toggleActivity"
                    @update-sort-order="handleSortOrderUpdate"
                    @delete="confirmDelete"
                    @toggle-select="toggleSelectAthlete"
                    @toggle-all="toggleAll"
                />
                <div class="flex justify-between items-center flex-col md:flex-row my-1" v-if="athletesCount">
                    <ItemsPerPageSelect :items-per-page="itemsPerPage" @update:itemsPerPage="itemsPerPage = $event" />
                    <Pagination :current-page="currentPage"
                                :items-per-page="itemsPerPage"
                                :total-items="filteredAthletes.length"
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
            :onConfirm="deleteAthlete"
            :cancelText="t('cancel')"
            :confirmText="t('yesDelete')"
        />
    </AdminLayout>
</template>
