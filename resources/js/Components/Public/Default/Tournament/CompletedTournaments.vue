<script setup>
import {ref, computed, watch, onMounted, onBeforeUnmount} from 'vue';
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

const props = defineProps({
    tournaments: Array,
});

const viewMode = ref('grid'); // 'grid' | 'horizontal'
const setViewMode = (mode) => {
    viewMode.value = mode;
};

const currentPage = ref(1);
const sortOrder = ref('desc'); // 'asc' | 'desc'
const itemsPerPage = 8;

const searchQuery = ref('');

const completedTournaments = computed(() => {
    return [...props.tournaments]
        .filter(t => t.status === 'completed')
        .filter(t => t.name?.toLowerCase().includes(searchQuery.value.toLowerCase()))
        .sort((a, b) => {
            const aTime = new Date(a.tournament_date_time);
            const bTime = new Date(b.tournament_date_time);
            return sortOrder.value === 'asc' ? aTime - bTime : bTime - aTime;
        });
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(completedTournaments.value.length / itemsPerPage))
);

const paginatedCompleted = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return completedTournaments.value.slice(start, start + itemsPerPage);
});

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};
const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

watch(sortOrder, () => {
    currentPage.value = 1;
});

const getTournamentImgSrc = (imgPath) => {
    if (!imgPath) return '';
    const base = appUrl.endsWith('/') ? appUrl.slice(0, -1) : appUrl;
    const path = imgPath.startsWith('/') ? imgPath.slice(1) : imgPath;
    return `${base}/storage/${path}`;
};

const countdowns = ref({}); // храним оставшееся время для каждого турнира

const updateCountdowns = () => {
    const now = new Date().getTime();

    props.tournaments.forEach(tournament => {
        const targetTime = new Date(tournament.tournament_date_time).getTime();
        const distance = targetTime - now;

        if (distance > 0) {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdowns.value[tournament.id] = {days, hours, minutes, seconds};
        } else {
            countdowns.value[tournament.id] = null;
        }
    });
};

let timerInterval = null;
onMounted(() => {
    updateCountdowns();
    timerInterval = setInterval(updateCountdowns, 1000);
});

onBeforeUnmount(() => {
    if (timerInterval) clearInterval(timerInterval);
});

function highlightVs(name) {
    const formatted = name.replaceAll('-', ' ');
    return formatted.replace(/\bvs\b/i, '<span class="text-red-400">vs</span>');
}
</script>

<template>
    <section class="space-y-8" v-if="completedTournaments.length">

        <!-- Заголовок и фильтры -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-4 mt-6">

            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                {{ t('statusCompleted') }}
            </h2>

            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">

                <!-- Поиск -->
                <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="t('searchByName')"
                    class="px-3 py-1 rounded-sm w-full sm:w-44
               border border-gray-400 dark:border-gray-200
               dark:bg-gray-700 text-sm text-slate-900 dark:text-slate-100"
                />

                <!-- Сортировка -->
                <select v-model="sortOrder"
                        class="px-3 py-1 rounded-sm w-full sm:w-44
                     border border-gray-400 dark:border-gray-200
                     dark:bg-gray-700 text-sm text-slate-900 dark:text-slate-100">
                    <option value="asc">{{ t('idAsc') }}</option>
                    <option value="desc">{{ t('idDesc') }}</option>
                </select>

                <!-- Переключатель вида -->
                <div class="flex justify-end items-center space-x-2">
                    <button @click="setViewMode('grid')"
                            :class="[
                  'p-1 border transition-colors duration-200 rounded',
                  viewMode === 'grid'
                  ? 'border-slate-400 dark:border-slate-200 text-red-400 dark:text-red-200'
                  : 'border-slate-300 dark:border-slate-400 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 hover:border-slate-500 dark:hover:border-slate-500']">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>

                    <button @click="setViewMode('horizontal')"
                            :class="[
                  'p-1 border transition-colors duration-200 rounded',
                  viewMode === 'horizontal'
                  ? 'border-slate-400 dark:border-slate-200 text-red-400 dark:text-red-200'
                  : 'border-slate-300 dark:border-slate-400 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 hover:border-slate-500 dark:hover:border-slate-500']">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- horizontal view -->
        <div v-if="viewMode === 'horizontal'"
             class="w-full max-w-7xl mx-auto px-4 space-y-6">

            <div v-for="tournament in paginatedCompleted" :key="tournament.id"
                 class="border-b border-gray-200 dark:border-gray-700 pb-4">

                <!-- Изображение -->
                <template v-if="tournament.images?.length">
                    <img :src="tournament.images[0].url" :alt="tournament.images[0].alt"
                         class="w-full max-w-3xl h-auto mx-auto object-cover rounded-xl mb-4
                                border-2 border-gray-400 dark:border-gray-600
                                shadow-lg shadow-gray-400 dark:shadow-gray-950"/>
                </template>

                <!-- Бойцы -->
                <div v-else
                     class="max-w-3xl mx-auto bg-gray-300 dark:bg-gray-700 py-8 mb-4 rounded-xl
                            border-2 border-gray-400 dark:border-gray-600
                            shadow-lg shadow-gray-400 dark:shadow-gray-950
                            flex flex-col sm:flex-row items-center justify-center gap-4">

                    <div class="flex flex-col items-center w-full sm:w-1/2 max-w-xs">
                        <div class="relative w-52 h-52">
                            <div class="absolute inset-0 bg-red-600 rounded-full z-0"></div>
                            <img v-if="tournament.fighter_red?.avatar"
                                 :src="`/storage/${tournament.fighter_red.avatar}`"
                                 class="absolute left-1/2 -top-4 transform -translate-x-1/2 z-10 w-52 h-52 object-contain"
                                 :alt="tournament.fighter_red.nickname" />
                        </div>
<!--                        <span class="my-4 text-center text-sm font-semibold uppercase text-slate-100">-->
<!--                            {{ tournament.fighter_red?.nickname.replaceAll('-', ' ') }}-->
<!--                        </span>-->
                    </div>

                    <span class="text-xl sm:text-2xl font-bold text-orange-400">vs</span>

                    <div class="flex flex-col items-center w-full sm:w-1/2 max-w-xs">
                        <div class="relative w-52 h-52">
                            <div class="absolute inset-0 bg-red-600 rounded-full z-0"></div>
                            <img v-if="tournament.fighter_blue?.avatar"
                                 :src="`/storage/${tournament.fighter_blue.avatar}`"
                                 class="absolute left-1/2 -top-4 transform -translate-x-1/2 z-10 w-52 h-52 object-contain"
                                 :alt="tournament.fighter_blue.nickname" />
                        </div>
<!--                        <span class="my-4 text-center text-sm font-semibold uppercase text-slate-100">-->
<!--                            {{ tournament.fighter_blue?.nickname.replaceAll('-', ' ') }}-->
<!--                        </span>-->
                    </div>
                </div>

                <!-- Информация -->
                <div class="text-center space-y-1">
                    <h3 class="uppercase font-semibold dark:text-white" v-html="highlightVs(tournament.name)"></h3>
                    <div class="flex flex-row items-center justify-center text-xs text-slate-600 dark:text-slate-200">
                        <svg class="w-3 h-3 fill-current shrink-0 text-slate-600 dark:text-slate-200 mr-1"
                             viewBox="0 0 16 16">
                            <path d="M15 2h-2V0h-2v2H9V0H7v2H5V0H3v2H1a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V3a1 1 0 00-1-1zm-1 12H2V6h12v8z"></path>
                        </svg>
                        {{ tournament.tournament_date_time }}
                    </div>
                    <div v-if="tournament.venue" class="text-xs text-slate-600 dark:text-slate-200">
                        {{ tournament.venue }}
                    </div>
                    <div v-if="tournament.winner"
                         class="flex items-center justify-center px-2
                                    font-semibold text-xs text-slate-800 dark:text-slate-200">
                        {{ t('winner') }}:
                        <div class="ml-1 uppercase">
                            {{ tournament.winner.nickname.replaceAll('-', ' ') }}
                        </div>
                    </div>
<!--                    <span v-if="tournament.short?.trim() !== ''" class="block text-xs text-slate-900 dark:text-slate-100">-->
<!--                      {{ tournament.short }}-->
<!--                    </span>-->
                </div>
            </div>
        </div>

        <!-- grid view -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 px-4">

            <div v-for="tournament in paginatedCompleted" :key="tournament.id"
                 class="flex flex-col bg-white dark:bg-gray-800 rounded-lg
                        shadow-xl shadow-gray-400 dark:shadow-gray-950
                        border border-gray-500 dark:border-gray-700
                        p-2 space-y-2">

                <!-- Блок изображения -->
                <template v-if="tournament.images?.length">
                    <img :src="tournament.images[0].url" :alt="tournament.images[0].alt"
                         class="w-full h-auto object-cover rounded" />
                </template>

                <!-- Блок бойцов -->
                <div v-else class="flex justify-around items-end bg-gray-300 dark:bg-gray-700 rounded-md py-2">
                    <div class="flex flex-col items-center">
                        <div class="relative w-24 h-24">
                            <div class="absolute inset-0 bg-red-600 rounded-full"></div>
                            <img v-if="tournament.fighter_red?.avatar"
                                 :src="`/storage/${tournament.fighter_red.avatar}`"
                                 class="absolute inset-0 m-auto z-10 w-20 h-20 object-contain"
                                 :alt="tournament.fighter_red?.nickname"/>
                        </div>
<!--                        <span class="uppercase text-xs font-semibold text-slate-100 mt-1 pl-1">-->
<!--                            {{ tournament.fighter_red?.nickname.replaceAll('-', ' ') }}-->
<!--                        </span>-->
                    </div>
                    <span class="text-orange-400 text-lg font-bold">vs</span>
                    <div class="flex flex-col items-center">
                        <div class="relative w-24 h-24">
                            <div class="absolute inset-0 bg-blue-600 rounded-full"></div>
                            <img v-if="tournament.fighter_blue?.avatar"
                                 :src="`/storage/${tournament.fighter_blue.avatar}`"
                                 class="absolute inset-0 m-auto z-10 w-20 h-20 object-contain"
                                 :alt="tournament.fighter_blue?.nickname"/>
                        </div>
<!--                        <span class="uppercase text-center text-xs font-semibold text-slate-100 mt-1 pl-1">-->
<!--                            {{ tournament.fighter_blue?.nickname.replaceAll('-', ' ') }}-->
<!--                        </span>-->
                    </div>
                </div>

                <!-- Инфо -->
                <div class="text-center">
                    <h3 class="uppercase font-semibold dark:text-white text-sm"
                        v-html="highlightVs(tournament.name)"></h3>
                    <div class="flex flex-row items-center justify-center text-xs text-slate-600 dark:text-slate-200">
                        <svg class="w-3 h-3 fill-current shrink-0 text-slate-600 dark:text-slate-200 mr-1"
                             viewBox="0 0 16 16">
                            <path d="M15 2h-2V0h-2v2H9V0H7v2H5V0H3v2H1a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V3a1 1 0 00-1-1zm-1 12H2V6h12v8z"></path>
                        </svg>
                        {{ tournament.tournament_date_time }}
                    </div>
                    <div v-if="tournament.venue" class="text-xs text-slate-600 dark:text-slate-200">
                        {{ tournament.venue }}
                    </div>
                    <div v-if="tournament.winner"
                         class="flex items-center justify-center px-2
                                    font-semibold text-xs text-slate-800 dark:text-slate-200">
                        {{ t('winner') }}:
                        <div class="ml-1 uppercase">
                            {{ tournament.winner.nickname.replaceAll('-', ' ') }}
                        </div>
                    </div>
<!--                    <span v-if="tournament.short?.trim() !== ''" class="block text-xs text-slate-900 dark:text-slate-100">-->
<!--                      {{ tournament.short }}-->
<!--                    </span>-->
                </div>
            </div>
        </div>

        <!-- Пагинация -->
        <div v-if="totalPages > 1"
             class="flex flex-row justify-center items-center mt-6 space-y-0 space-x-2 text-xs font-semibold">

            <button @click="prevPage" :disabled="currentPage === 1"
                    class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                           border border-gray-400 dark:border-gray-200 disabled:opacity-50">
                «
            </button>

            <span class="text-gray-700 dark:text-gray-200">{{ t('page') }}</span>

            <input type="number" v-model.number="currentPage" :min="1" :max="totalPages"
                   class="w-12 text-center px-1 py-1 border border-gray-400 dark:border-gray-200 rounded
                          bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs" />

            <span class="text-gray-700 dark:text-gray-200">{{ t('of') }} {{ totalPages }}</span>

            <button @click="nextPage" :disabled="currentPage === totalPages"
                    class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                           border border-gray-400 dark:border-gray-200 disabled:opacity-50">
                »
            </button>
        </div>

    </section>
</template>
