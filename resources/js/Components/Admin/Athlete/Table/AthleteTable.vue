<script setup>
import {defineEmits, defineProps, ref, watch} from 'vue';
import {useI18n} from 'vue-i18n';
import draggable from 'vuedraggable';
import ActivityToggle from '@/Components/Admin/Buttons/ActivityToggle.vue';
import IconEdit from "@/Components/Admin/Buttons/IconEdit.vue";
import DeleteIconButton from '@/Components/Admin/Buttons/DeleteIconButton.vue';

const {t} = useI18n();

const props = defineProps({
    athletes: Array,
    selectedAthletes: Array
});

const emits = defineEmits([
    'toggle-activity',
    'edit',
    'delete',
    'update-sort-order',
    'toggle-select',
    'toggle-all'
]);

// --- Локальная копия для vuedraggable ---
const localAthletes = ref([]);

// --- Следим за изменением props.athletes и обновляем локальную копию ---
watch(() => props.athletes, (newVal) => {
    // Создаем глубокую копию, чтобы избежать мутации props
    localAthletes.value = JSON.parse(JSON.stringify(newVal || []));
}, {immediate: true, deep: true}); // immediate: true для инициализации

// --- Функция, вызываемая vuedraggable после завершения перетаскивания ---
const handleDragEnd = () => {
    // Отправляем НОВЫЙ ПОРЯДОК ID из локального массива
    const newOrderIds = localAthletes.value.map(athlete => athlete.id);
    emits('update-sort-order', newOrderIds); // Отправляем массив ID
};

// --- Логика массовых действий ---
const toggleAll = (event) => {
    const checked = event.target.checked;
    const ids = localAthletes.value.map(r => r.id);
    emits('toggle-all', {ids, checked});
};

// Функция изображения
const getPrimaryImage = (athlete) => {
    // 1. Если есть изображения — использовать первое
    if (Array.isArray(athlete.images) && athlete.images.length > 0 && athlete.images[0].url) {
        return athlete.images[0].url;
    }

    // 2. Если есть аватар — построить путь
    if (athlete.avatar) {
        return `/storage/${athlete.avatar}`;
    }

    // 3. Если ничего нет — показать заглушку
    return '/storage/athlete_avatar/default-image.png';
};

</script>

<template>
    <div class="bg-white dark:bg-slate-700 shadow-lg rounded-sm border border-slate-200 dark:border-slate-600 relative">
        <div class="overflow-x-auto">
            <table v-if="athletes.length > 0"
                   class="table-auto w-full text-slate-700 dark:text-slate-100">
                <thead class="text-sm uppercase
                              bg-slate-200 dark:bg-cyan-900
                              border border-solid border-gray-300 dark:border-gray-700">
                <tr>
                    <th class="px-2 py-1 whitespace-nowrap w-1/12">
                        <div class="font-medium text-center">{{ t('id') }}</div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap w-1/12">
                        <div class="flex justify-center" :title="t('image')">
                            <svg class="w-6 h-6 fill-current shrink-0" viewBox="0 0 512 512">
                                <path d="M0 96C0 60.7 28.7 32 64 32l384 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96zM323.8 202.5c-4.5-6.6-11.9-10.5-19.8-10.5s-15.4 3.9-19.8 10.5l-87 127.6L170.7 297c-4.6-5.7-11.5-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6l96 0 32 0 208 0c8.9 0 17.1-4.9 21.2-12.8s3.6-17.4-1.4-24.7l-120-176zM112 192a48 48 0 1 0 0-96 48 48 0 1 0 0 96z"/>
                            </svg>
                        </div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap w-1/12">
                        <div class="flex justify-center" :title="t('localization')">
                            <svg class="w-8 h-8 fill-current shrink-0" viewBox="0 0 640 512">
                                <path d="M0 128C0 92.7 28.7 64 64 64l192 0 48 0 16 0 256 0c35.3 0 64 28.7 64 64l0 256c0 35.3-28.7 64-64 64l-256 0-16 0-48 0L64 448c-35.3 0-64-28.7-64-64L0 128zm320 0l0 256 256 0 0-256-256 0zM178.3 175.9c-3.2-7.2-10.4-11.9-18.3-11.9s-15.1 4.7-18.3 11.9l-64 144c-4.5 10.1 .1 21.9 10.2 26.4s21.9-.1 26.4-10.2l8.9-20.1 73.6 0 8.9 20.1c4.5 10.1 16.3 14.6 26.4 10.2s14.6-16.3 10.2-26.4l-64-144zM160 233.2L179 276l-38 0 19-42.8zM448 164c11 0 20 9 20 20l0 4 44 0 16 0c11 0 20 9 20 20s-9 20-20 20l-2 0-1.6 4.5c-8.9 24.4-22.4 46.6-39.6 65.4c.9 .6 1.8 1.1 2.7 1.6l18.9 11.3c9.5 5.7 12.5 18 6.9 27.4s-18 12.5-27.4 6.9l-18.9-11.3c-4.5-2.7-8.8-5.5-13.1-8.5c-10.6 7.5-21.9 14-34 19.4l-3.6 1.6c-10.1 4.5-21.9-.1-26.4-10.2s.1-21.9 10.2-26.4l3.6-1.6c6.4-2.9 12.6-6.1 18.5-9.8l-12.2-12.2c-7.8-7.8-7.8-20.5 0-28.3s20.5-7.8 28.3 0l14.6 14.6 .5 .5c12.4-13.1 22.5-28.3 29.8-45L448 228l-72 0c-11 0-20-9-20-20s9-20 20-20l52 0 0-4c0-11 9-20 20-20z"/>
                            </svg>
                        </div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap w-2/12">
                        <div class="font-medium text-left">{{ t('nickname') }}</div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap w-2/12">
                        <div class="font-medium text-left">{{ t('name') }}</div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap w-2/12">
                        <div class="font-medium text-left">{{ t('lastName') }}</div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap text-left w-2/12">
                        <div class="flex justify-start space-x-3.5">
                            <div class="text-orange-500 dark:text-orange-200 flex justify-left w-1/12"
                                 :title="t('wins')">
                                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                    <path d="M19.5,2a3.588,3.588,0,0,0-.5.05V4a6.958,6.958,0,0,1-1.524,4.347A3.5,3.5,0,1,0,19.5,2Z"></path>
                                    <path d="M4.5,2a3.588,3.588,0,0,1,.5.05V4A6.958,6.958,0,0,0,6.524,8.347,3.5,3.5,0,1,1,4.5,2Z"></path>
                                    <path d="M16,0a7.585,7.585,0,0,0-3.407.808A6.956,6.956,0,0,1,14,5a1,1,0,0,1-2,0A5.006,5.006,0,0,0,7,0V4A5,5,0,0,0,17,4V0Z"></path>
                                    <path d="M14.618,20H9.382L8.105,22.553A1,1,0,0,0,9,24h6a1,1,0,0,0,.895-1.447Z"></path>
                                    <path d="M21.157,10.793a2.771,2.771,0,0,0-3.828,0,.709.709,0,0,1-1,0,2.772,2.772,0,0,0-3.829,0,.722.722,0,0,1-1,0,2.771,2.771,0,0,0-3.828,0,.709.709,0,0,1-1,0,2.772,2.772,0,0,0-3.829,0,1,1,0,0,0,0,1.412L8.6,18H15.4l5.756-5.795A1,1,0,0,0,21.157,10.793Z"></path>
                                </svg>
                            </div>
                            <div class="text-blue-500 dark:text-blue-200 flex justify-left w-1/12"
                                 :title="t('draws')">
                                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                    <path d="M16.536,13.41a1,1,0,0,0-1.414,1.414l2.82,2.82-1.317,1.317-2.82-2.82a1,1,0,1,0-1.414,1.414l2.557,2.558a5.043,5.043,0,0,1-2.281.377,3.565,3.565,0,0,1-.8,1.923,7.091,7.091,0,0,0,1.135.1,6.983,6.983,0,0,0,4.95-2.047l2.108-2.136a1,1,0,0,0-.006-1.411Z"></path>
                                    <path d="M1.821,12.856a3.591,3.591,0,0,1,1.023-.7A6.052,6.052,0,0,1,9.082,3.09l.433-.433A9.188,9.188,0,0,1,10.9,1.517,8.068,8.068,0,0,0,1.321,13.5,3.628,3.628,0,0,1,1.821,12.856Z"></path>
                                    <rect x="0.786" y="15.158" width="11.894" height="5.222" rx="2.611" ry="2.611" transform="translate(14.537 0.443) rotate(45)"></rect>
                                    <path d="M21.636,3.364a8.071,8.071,0,0,0-11.414,0L5.585,8l.708.707a3.125,3.125,0,0,0,4.414,0L12.5,6.916l8.424,8.576.714-.714a8.071,8.071,0,0,0,0-11.414Z"></path>
                                </svg>
                            </div>
                            <div class="text-violet-500 dark:text-violet-200 flex justify-left w-1/12"
                                 :title="t('losses')">
                                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                    <path d="M19,7c0-3.86-3.141-7-7-7S5,3.14,5,7c0,3.519,2.614,6.432,6,6.92V20h2v-6.08C16.386,13.432,19,10.519,19,7z"></path>
                                    <path d="M15,16.118v1.998c4.146,0.331,6.656,1.293,6.986,1.883c-0.404,0.722-4.061,2-9.986,2 c-6.043,0-9.727-1.33-10.006-1.958c0.229-0.586,2.76-1.586,7.006-1.925v-1.999C4.796,16.438,0,17.482,0,20c0,3.158,7.543,4,12,4 s12-0.842,12-4C24,17.482,19.204,16.438,15,16.118z"></path>
                                </svg>
                            </div>
                            <div class="text-red-400 dark:text-red-400  flex justify-left w-1/12"
                                 :title="t('winsByKo')">
                                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                    <path d="M14.586,9.439S15.7,2.858,11.138,0A8.055,8.055,0,0,1,8.1,5.831C6.149,7.546,2.481,11.4,2.52,15.51A9.435,9.435,0,0,0,7.7,24a5.975,5.975,0,0,1,2.091-4.132,4.877,4.877,0,0,0,1.869-3.278,8.786,8.786,0,0,1,4.652,7.322v.02a8.827,8.827,0,0,0,5.137-7.659c.324-3.863-1.792-9.112-3.668-10.828A10.192,10.192,0,0,1,14.586,9.439Z"></path>
                                </svg>
                            </div>
                            <div class="text-rose-500 dark:text-rose-200 flex justify-left w-1/12"
                                 :title="t('winsBySubmission')">
                                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                    <path d="M3.1,5.3C2.8,4.8,2.2,4.6,1.8,4.9C1.3,5.2,1.1,5.8,1.4,6.3l9.5,16.5c0.2,0.3,0.5,0.5,0.9,0.5 c0.2,0,0.3,0,0.5-0.1c0.5-0.3,0.6-0.9,0.4-1.4L3.1,5.3z"></path>
                                    <path d="M22.6,9l-4.5-7.8C18,1,17.7,0.8,17.4,0.8c-0.3,0-0.6,0-0.8,0.3c-1.3,1.2-2.8,1.3-4.3,1.3l-1.1,0 c-2,0-4.2,0.2-5.9,2.7l5.6,9.6c0.2-0.1,0.3-0.2,0.4-0.4c1.2-2.1,2.6-2.2,4.5-2.2l1,0c1.8,0,3.8-0.1,5.7-1.8C22.8,10,22.8,9.5,22.6,9 z"></path>
                                </svg>
                            </div>
                            <div class="text-teal-500 dark:text-teal-200 flex justify-left w-1/12" :title="t('winsByDecision')">
                                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                    <path d="M15.334,1.278a21.767,21.767,0,0,0-14.668,0A1,1,0,0,0,0,2.221V12a8,8,0,0,0,16,0V2.221A1,1,0,0,0,15.334,1.278ZM3,8A1,1,0,0,1,4,7H5A1,1,0,0,1,5,9H4A1,1,0,0,1,3,8Zm5,9a3,3,0,0,1-3-3h6A3,3,0,0,1,8,17Zm4-8H11a1,1,0,0,1,0-2h1a1,1,0,0,1,0,2Z"></path>
                                    <path d="M23.334,5.273A22.073,22.073,0,0,0,18,4.1V12a1,1,0,0,1,1-1h1a1,1,0,0,1,0,2H19a1,1,0,0,1-1-1V12a9.938,9.938,0,0,1-2.016,6H16a3,3,0,0,1,3,3H15v0H12.276a9.862,9.862,0,0,1-1.877.7A7.993,7.993,0,0,0,24,16V6.215A1,1,0,0,0,23.334,5.273Z"></path>
                                </svg>
                            </div>
                            <div class="flex justify-left w-1/12" :title="t('noContests')">
                                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                    <circle cx="12" cy="3" r="3"></circle>
                                    <path d="M22,7H2c-.552,0-1,.448-1,1h0c0,.552,.448,1,1,1h7v14.263c0,.407,.33,.737,.737,.737h.525c.407,0,.737-.33,.737-.737v-7.263h2v7.263c0,.407,.33,.737,.737,.737h.525c.407,0,.737-.33,.737-.737V9h7c.552,0,1-.448,1-1h0c0-.552-.448-1-1-1Z"></path>
                                </svg>
                            </div>
                        </div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap w-2/12">
                        <div class="font-medium text-right">{{ t('actions') }}</div>
                    </th>
                    <th class="px-2 py-1 whitespace-nowrap text-center w-1/12">
                        <input type="checkbox" @change="toggleAll" />
                    </th>
                </tr>
                </thead>

                <draggable tag="tbody"
                           v-model="localAthletes"
                           item-key="id"
                           @end="handleDragEnd">
                    <template #item="{ element: athlete }">
                        <tr class="text-sm font-semibold border-b hover:bg-slate-100 dark:hover:bg-cyan-800">
                            <td class="px-2 py-1 whitespace-nowrap w-1/12">
                                <div class="text-center text-blue-600 dark:text-blue-200">
                                    {{ athlete.id }}
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap w-1/12">
                                <div class="flex justify-center">
                                    <template v-if="getPrimaryImage(athlete)">
                                        <img
                                            :src="getPrimaryImage(athlete)"
                                            class="h-8 w-8 object-cover rounded-xs"
                                         alt="avatar">
                                    </template>
                                    <template v-else>
                                        <img
                                            src="/storage/athlete_avatar/default-image.png"
                                            class="h-8 w-8 object-cover rounded-xs"
                                         alt="avatar">
                                    </template>
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap w-1/12">
                                <div class="text-center uppercase text-orange-500 dark:text-orange-200">
                                    {{ athlete.locale }}
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap w-2/12">
                                <div class="text-left text-rose-500 dark:text-rose-200"
                                     :title="athlete.nationality">
                                    {{ athlete.nickname }}
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap w-2/12">
                                <div class="text-left text-teal-600 dark:text-teal-200"
                                     :title="athlete.date_of_birth">
                                    {{ athlete.first_name }}
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap text-left w-2/12">
                                <div class="text-left text-violet-600 dark:text-violet-200">
                                    {{ athlete.last_name }}
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap font-bold text-left w-2/12">
                                <div class="flex justify-start space-x-4">
                                    <div class="text-orange-500 dark:text-orange-200 flex justify-left w-1/12">
                                        {{ athlete.wins }}
                                    </div>
                                    <div class="text-blue-500 dark:text-blue-200 flex justify-left w-1/12">
                                        {{ athlete.draws }}
                                    </div>
                                    <div class="text-violet-500 dark:text-violet-200 flex justify-left w-1/12">
                                        {{ athlete.losses }}
                                    </div>
                                    <div class="text-red-400 dark:text-red-400 flex justify-left w-1/12">
                                        {{ athlete.wins_by_ko }}
                                    </div>
                                    <div class="text-rose-500 dark:text-rose-200 flex justify-left w-1/12">
                                        {{ athlete.wins_by_submission }}
                                    </div>
                                    <div class="text-teal-500 dark:text-teal-200 flex justify-left w-1/12">
                                        {{ athlete.wins_by_decision }}
                                    </div>
                                    <div class="flex justify-left w-1/12">
                                        {{ athlete.no_contests }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap text-right w-2/12">
                                <div class="flex justify-end space-x-2">
                                    <ActivityToggle :isActive="athlete.activity"
                                                    @toggle-activity="$emit('toggle-activity', athlete)"
                                                    :title="athlete.activity ? t('enabled') : t('disabled')" />
                                    <IconEdit :href="route('admin.athletes.edit', athlete.id)" />
                                    <DeleteIconButton @click="$emit('delete', athlete.id)" />
                                </div>
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap text-center w-1/12">
                                <input type="checkbox"
                                       :checked="selectedAthletes.includes(athlete.id)"
                                       @change="$emit('toggle-select', athlete.id)" />
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
