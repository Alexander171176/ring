<script setup>
/**
 * @version PulsarCMS 1.0
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 */
import {defineProps, ref, watch, computed} from 'vue'; // computed может понадобиться для locale links
import {useI18n} from 'vue-i18n';
import {useToast} from 'vue-toastification';
import {router, Link} from '@inertiajs/vue3'; // Link для ссылок локалей
import draggable from 'vuedraggable'; // Импортируем draggable
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";
import DangerModal from '@/Components/Admin/Modal/DangerModal.vue';
import CountTable from '@/Components/Admin/Count/CountTable.vue';
import BulkActionSelect from "@/Components/Admin/Page/Select/BulkActionSelect.vue";
import PageTreeItem from "@/Components/Admin/Page/Tree/PageTreeItem.vue"; // <-- Новый компонент

// --- Инициализация экземпляр i18n, toast ---
const {t} = useI18n();
const toast = useToast();

/**
 * Входные свойства компонента.
 */
const props = defineProps({
    pages: Array,           // Теперь это иерархический массив
    pagesCount: Number,
    currentLocale: String,
    availableLocales: Array,
    errors: Object // Ошибки валидации от Laravel (если есть)
});

// --- Локальная реактивная копия дерева для vuedraggable ---
const localPages = ref([]);

watch(() => props.pages, (newVal) => {
    // Глубокая копия для мутабельности
    localPages.value = JSON.parse(JSON.stringify(newVal || []));
}, {immediate: true, deep: true});

/**
 * Флаг отображения модального окна подтверждения удаления.
 */
const showConfirmDeleteModal = ref(false);

/**
 * ID для удаления.
 */
const pageToDeleteId = ref(null);

/**
 * Название для отображения в модальном окне.
 */
const pageToDeleteTitle = ref('');

/**
 * Открывает модальное окно подтверждения удаления с входными переменными.
 */
const confirmDelete = (page) => { // Принимаем весь объект page
    pageToDeleteId.value = page.id;
    pageToDeleteTitle.value = page.title; // Берем title из объекта
    showConfirmDeleteModal.value = true;
};

/**
 * Закрывает модальное окно подтверждения и сбрасывает связанные переменные.
 */
const closeModal = () => {
    showConfirmDeleteModal.value = false;
    pageToDeleteId.value = null;
    pageToDeleteTitle.value = '';
};

/**
 * Отправляет запрос на удаление страницы на сервер.
 */
const deletePage = () => {
    if (pageToDeleteId.value === null) return;
    const idToDelete = pageToDeleteId.value;
    const titleToDelete = pageToDeleteTitle.value;

    router.delete(route('admin.pages.destroy', {page: idToDelete}), {
        preserveScroll: true,
        // preserveState: false, // Перезагружаем данные после удаления
        onSuccess: () => {
            toast.success(`Страница "${titleToDelete || 'ID: ' + idToDelete}" удалена.`);
            // localPages обновятся через watch(props.pages), так как preserveState: false
        },
        onError: (errors) => {
            const errorMsg = errors.general || errors[Object.keys(errors)[0]] || 'Произошла ошибка при удалении.';
            toast.error(`${errorMsg} (Страница: ${titleToDelete || 'ID: ' + idToDelete})`);
        },
        onFinish: () => {
            closeModal(); // Закрываем в любом случае
        }
    });
};

/**
 * Отправляет запрос для изменения статуса активности.
 */
const toggleActivity = (page) => {
    const newActivity = !page.activity;
    const actionText = newActivity ? t('activated') : t('deactivated'); // Используем t()

    router.put(route('admin.actions.pages.updateActivity', {page: page.id}),
        {activity: newActivity},
        {
            preserveScroll: true,
            preserveState: true, // Оптимистичное обновление или дождаться success
            onSuccess: () => {
                // Найдем и обновим локально (для preserveState: true)
                // Нужна рекурсивная функция поиска
                const findAndUpdateActivity = (nodes, id, activity) => {
                    for (const node of nodes) {
                        if (node.id === id) {
                            node.activity = activity;
                            return true;
                        }
                        if (node.children && node.children.length > 0) {
                            if (findAndUpdateActivity(node.children, id, activity)) {
                                return true;
                            }
                        }
                    }
                    return false;
                };
                findAndUpdateActivity(localPages.value, page.id, newActivity);

                toast.success(`Страница "${page.title}" ${actionText}.`);
            },
            onError: (errors) => {
                toast.error(errors.activity || errors.general || `Ошибка изменения активности для "${page.title}".`);
            },
        }
    );
};

// --- Drag and Drop ---
/**
 * Обрабатывает окончание перетаскивания на ЛЮБОМ уровне.
 * event содержит информацию о перемещении.
 */
const handleDragEnd = (event) => {
    // console.log('Событие завершения перетаскивания:', event); // Для отладки

    // --- Ключевая логика обновления структуры ---
    // 1. Получить ID перемещенного элемента
    // 2. Получить новый parent_id (из data-атрибута родительского draggable или null)
    // 3. Пересчитать sort для нового родителя (всех элементов в event.to)
    // 4. Пересчитать sort для старого родителя (если изменился) (всех элементов в event.from)
    // 5. Собрать массив измененных [{ id, parent_id, sort }]
    // 6. Отправить на бэкенд

    // --- Этап 1: Подготовка данных (упрощенный пример) ---
    // Нужна рекурсивная функция для обновления сортировки и сбора изменений
    let changes = [];
    const updateSortAndCollectChanges = (nodes, parentId) => {
        nodes.forEach((node, index) => {
            let changed = false;
            if (node.sort !== index) {
                node.sort = index;
                changed = true;
            }
            if (node.parent_id !== parentId) {
                // Важно: parent_id ДОЛЖЕН быть уже обновлен Vuedraggable/Vue
                // при перемещении между списками. Проверить это!
                // Если Vuedraggable не обновляет parent_id автоматически,
                // нужно найти перемещенный элемент и обновить его parent_id вручную здесь.
                // node.parent_id = parentId; // Примерно так
                changed = true;
            }
            if (changed) {
                changes.push({
                    id: node.id,
                    sort: node.sort,
                    parent_id: parentId // Используем новый parentId
                });
            }
            if (node.children && node.children.length) {
                updateSortAndCollectChanges(node.children, node.id); // Рекурсивный вызов
            }
        });
    };

    // Вызываем функцию для всего дерева, начиная с корневого уровня (parentId = null)
    updateSortAndCollectChanges(localPages.value, null);

    // Убираем дубликаты, если элемент попал в changes несколько раз (маловероятно при правильной логике)
    // const uniqueChanges = Array.from(new Map(changes.map(item => [item.id, item])).values());
    const uniqueChanges = changes.reduce((acc, current) => {
        const x = acc.find(item => item.id === current.id);
        if (!x) {
            return acc.concat([current]);
        } else {
            // Если дубликат, берем последнее состояние (хотя по идее оно должно быть одно)
            Object.assign(x, current);
            return acc;
        }
    }, []);

    // console.log('Sending changes:', uniqueChanges);

    // --- Этап 2: Отправка на бэкенд ---
    if (uniqueChanges.length > 0) {
        router.put(route('admin.actions.pages.updateSortBulk'), {
            pages: uniqueChanges,
            locale: props.currentLocale // Передаем текущую локаль
        }, {
            preserveScroll: true,
            preserveState: true, // Чтобы не перерисовывать все дерево при успехе
            onSuccess: () => {
                toast.success('Иерархия успешно обновлена');
                // Данные в localPages уже обновлены локально
            },
            onError: (errors) => {
                console.error("Ошибка обновления сортировки:", errors);
                toast.error(errors.message || 'Ошибка обновления иерархии');
                // Откатить изменения локально сложно, проще перезагрузить данные с сервера
                router.reload({only: ['pages'], preserveScroll: true});
            },
        });
    } else {
        console.log('Никаких изменений в порядке сортировки нет.');
    }
};

/**
 * Массив выбранных ID для массовых действий.
 */
const selectedPages = ref([]);

/**
 * Функция для рекурсивного поиска всех ID в дереве.
 */
const getAllIds = (nodes) => {
    let ids = [];
    nodes.forEach(node => {
        ids.push(node.id);
        if (node.children && node.children.length) {
            ids = ids.concat(getAllIds(node.children));
        }
    });
    return ids;
};

/**
 * Логика выбора всех для массовых действий.
 */
const toggleAll = (event) => {
    const checked = event.target?.checked; // Проверяем событие чекбокса
    const allNodeIds = getAllIds(localPages.value); // Получаем все ID в текущем дереве

    if (checked === true) { // Явная проверка на true
        selectedPages.value = allNodeIds;
    } else if (checked === false) { // Явная проверка на false
        selectedPages.value = [];
    }
};

/**
 * Обрабатывает событие выбора/снятия выбора одной строки.
 */
const toggleSelectPage = (pageId) => {
    const index = selectedPages.value.indexOf(pageId);
    if (index > -1) {
        selectedPages.value.splice(index, 1);
    } else {
        selectedPages.value.push(pageId);
    }
};

/**
 * Функция для поиска узлов по ID и обновления их активности.
 */
const updateActivityByIds = (nodes, ids, activity) => {
    nodes.forEach(node => {
        if (ids.includes(node.id)) {
            node.activity = activity;
        }
        if (node.children && node.children.length) {
            updateActivityByIds(node.children, ids, activity);
        }
    });
};

/**
 * Выполняет массовое включение/выключение активности выбранных.
 */
const bulkToggleActivity = (newActivity) => {
    if (!selectedPages.value.length) {
        toast.warning('Выберите страницы для активации/деактивации');
        return;
    }

    const idsToUpdate = [...selectedPages.value]; // Копируем массив ID

    router.put(route('admin.actions.pages.bulkUpdateActivity'), {
        ids: idsToUpdate,
        activity: newActivity,
    }, {
        preserveScroll: true,
        preserveState: true, // Остаемся на месте
        onSuccess: () => {
            // Оптимистично обновляем локальные данные
            updateActivityByIds(localPages.value, idsToUpdate, newActivity);
            selectedPages.value = []; // Очищаем выбор
            toast.success('Статус активации массово обновлены'); // Нужен перевод
        },
        onError: () => {
            toast.error('Произошла оштбка при массовом обновлении статуса активности'); // Нужен перевод
        }
    });
};

/**
 * Обрабатывает выбор действия в селекте массовых действий.
 */
const handleBulkAction = (event) => {
    const action = event.target.value;
    if (action === 'selectAll') {
        toggleAll({target: {checked: true}}); // Имитируем событие
    } else if (action === 'deselectAll') {
        toggleAll({target: {checked: false}}); // Имитируем событие
    } else if (action === 'activate') {
        bulkToggleActivity(true);
    } else if (action === 'deactivate') {
        bulkToggleActivity(false);
    }
    event.target.value = ''; // Сбросить
};

/**
 * Генерация ссылок для переключения локалей.
 */
const localeLink = (locale) => {
    return route('admin.pages.index', {locale: locale});
};

</script>

<template>
    <AdminLayout :title="t('pages')">
        <template #header>
            <TitlePage>
                {{ t('pages') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">

                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('admin.pages.create', { locale: currentLocale })">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"></path>
                            </svg>
                        </template>
                        {{ t('addPage') }}
                    </DefaultButton>
                    <BulkActionSelect v-if="pagesCount > 0" @change="handleBulkAction"/>
                </div>
                <div class="flex items-center justify-between mt-5">
                    <!-- Переключатель локалей -->
                    <div class="flex items-center justify-end space-x-2 px-3 py-1
                                border-x border-t border-gray-400 rounded-t-lg bg-gray-100 dark:bg-gray-900">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            {{ t('localization') }}:
                        </span>
                        <template v-for="locale in availableLocales" :key="locale">
                            <Link :href="localeLink(locale)"
                                  :class="['px-3 py-1 text-sm font-medium rounded-sm',
                                           currentLocale === locale ? 'bg-blue-500 text-white'
                                           : 'bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600']"
                                  preserve-scroll
                                  preserve-state>
                                {{ locale.toUpperCase() }}
                            </Link>
                        </template>
                    </div>
                    <div class="flex items-center">
                        <CountTable v-if="pagesCount">{{ pagesCount }}</CountTable>
                        <input type="checkbox" id="select-all-header" @change="toggleAll"
                               class="form-checkbox rounded-sm text-indigo-500 ml-2" :title="t('selectAll')">
                    </div>
                </div>

                <!-- Корневой Draggable -->
                <div class="bg-gray-300 dark:bg-gray-900 border border-gray-400 relative">

                    <draggable v-model="localPages"
                               tag="div"
                               item-key="id"
                               handle=".handle"
                               group="pages"
                               @end="handleDragEnd"
                               class="page-tree-root"
                               :data-parent-id="null">

                        <template #item="{ element: page }">
                            <PageTreeItem :page="page"
                                          :level="0"
                                          :selected-pages="selectedPages"
                                          @toggle-activity="toggleActivity"
                                          @delete="confirmDelete"
                                          @toggle-select="toggleSelectPage"
                                          @request-drag-end="handleDragEnd"/>
                        </template>

                        <template #header v-if="localPages.length === 0 && pagesCount > 0">
                            <div class="p-4 text-center text-slate-500 dark:text-slate-400">
                                {{ t('loading') }}...
                            </div>
                        </template>

                        <template #footer v-if="localPages.length === 0 && pagesCount === 0">
                            <div class="p-4 text-center text-slate-900 dark:text-slate-100">
                                {{ t('noData') }}
                            </div>
                        </template>

                    </draggable>
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

<style scoped>
/* Стили для визуализации дерева, можно вынести */
.page-tree-root {
    padding: 5px;
}
</style>
