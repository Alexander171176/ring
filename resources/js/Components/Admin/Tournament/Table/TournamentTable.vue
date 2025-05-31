<script setup>
import {defineProps, defineEmits, watch, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import draggable from 'vuedraggable';
import ActivityToggle from '@/Components/Admin/Buttons/ActivityToggle.vue';
import IconEdit from "@/Components/Admin/Buttons/IconEdit.vue";
import DeleteIconButton from '@/Components/Admin/Buttons/DeleteIconButton.vue';

const {t} = useI18n();

const props = defineProps({
    tournaments: Array,
    selectedTournaments: Array
});

const emits = defineEmits([
    'toggle-activity',
    'edit',
    'delete',
    'update-sort-order',
    'toggle-select',
    'toggle-all'
]);

const getLocalizedType = (type) => {
    if (!type) return t('unknown');
    return t(`tournamentType${type.charAt(0).toUpperCase() + type.slice(1).replace(/_([a-z])/g, (_, g) => g.toUpperCase())}`);
};

const getLocalizedStatus = (status) => {
    if (!status) return t('unknown');
    return t(`tournamentStatus${status.charAt(0).toUpperCase() + status.slice(1)}`);
};

const formatDate = (datetime) => {
    if (!datetime) return t('unknown');
    const date = new Date(datetime);
    return date.toLocaleString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// --- Локальная копия для vuedraggable ---
const localTournaments = ref([]);

// --- Следим за изменением props.tournaments и обновляем локальную копию ---
watch(() => props.tournaments, (newVal) => {
    // Создаем глубокую копию, чтобы избежать мутации props
    localTournaments.value = JSON.parse(JSON.stringify(newVal || []));
}, {immediate: true, deep: true}); // immediate: true для инициализации

// --- Функция, вызываемая vuedraggable после завершения перетаскивания ---
const handleDragEnd = () => {
    // Отправляем НОВЫЙ ПОРЯДОК ID из локального массива
    const newOrderIds = localTournaments.value.map(tournament => tournament.id);
    emits('update-sort-order', newOrderIds); // Отправляем массив ID
};

// --- Логика массовых действий ---
const toggleAll = (event) => {
    const checked = event.target.checked;
    const ids = localTournaments.value.map(r => r.id);
    emits('toggle-all', {ids, checked});
};

// Функция изображения
const getPrimaryImage = (tournament) => {
    // 1. Если есть изображения — использовать первое
    if (Array.isArray(tournament.images) && tournament.images.length > 0 && tournament.images[0].url) {
        return tournament.images[0].url;
    }

    // 2. Если ничего нет — показать заглушку
    return '/storage/tournament_images/default-image.png';
};

</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="tournaments.length > 0" class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead class="text-sm font-semibold uppercase
                                bg-slate-200 dark:bg-cyan-900
                                border border-solid
                                border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 py-1 whitespace-nowrap w-1/12">
                        <div class="font-medium text-center">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap w-2/12">
                        <div class="font-medium text-left">{{ t('name') }}</div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap w-2/12">
                        <div class="font-medium text-left">{{ t('venue') }}</div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap w-2/12">
                        <div class="font-medium text-left">{{ t('status') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-end">{{ t('actions') }}</div>
                    </th>
                    <th class="px-2 first:pl-7 last:pr-7 py-3 whitespace-nowrap">
                        <div class="text-center font-medium">
                            <input type="checkbox" @change="toggleAll"/>
                        </div>
                    </th>
                </tr>
                </thead>
                <draggable tag="tbody" v-model="localTournaments" @end="handleDragEnd" itemKey="id">
                    <template #item="{ element: tournament }">
                        <tr class="text-sm font-semibold border-b-2 hover:bg-slate-100 dark:hover:bg-cyan-800">
                            <td class="px-2 py-1 whitespace-nowrap w-1/12">
                                <div class="text-center text-blue-600 dark:text-blue-200"
                                     :title="tournament.locale">
                                    {{ tournament.id }}
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap w-2/12">
                                <div class="text-left text-amber-500 dark:text-amber-200"
                                     :title="formatDate(tournament.tournament_date_time)">
                                    {{ tournament.name }}
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap w-2/12">
                                <div class="text-left text-teal-500 dark:text-teal-200"
                                     :title="getLocalizedStatus(tournament.status)">
                                    {{ tournament.venue }}
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap text-right w-2/12">
                                <div class="flex justify-end space-x-2">
                                    <ActivityToggle :isActive="tournament.activity"
                                                    @toggle-activity="$emit('toggle-activity', tournament)"
                                                    :title="tournament.activity ? t('enabled') : t('disabled')"/>
                                    <IconEdit :href="route('admin.tournaments.edit', tournament.id)"/>
                                    <DeleteIconButton @click="$emit('delete', tournament.id)"/>
                                </div>
                            </td>
                            <td class="px-2 first:pl-7 last:pr-7 py-1 whitespace-nowrap">
                                <div class="text-center">
                                    <input type="checkbox" :checked="selectedTournaments.includes(tournament.id)"
                                           @change="$emit('toggle-select', tournament.id)"/>
                                </div>
                            </td>
                        </tr>
                    </template>
                </draggable>
            </table>
            <div v-else class="p-5 text-center text-slate-700 dark:text-slate-100">
                {{ t('noData') }}
            </div>
        </div>
    </div>
</template>
