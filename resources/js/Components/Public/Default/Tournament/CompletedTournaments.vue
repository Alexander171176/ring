<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import {Link, router} from '@inertiajs/vue3';

const { t } = useI18n();

const props = defineProps({
    tournaments: Object,
    pagination: Object,
    currentPage: Number,
    rubricUrl: String,
    locale: String,
    search: String,
});

const emit = defineEmits(['page-changed']);

// Поисковый запрос
const searchQuery = ref(props.search || '');

watch(() => props.search, (newVal) => {
    searchQuery.value = newVal || '';
});

const onSearch = () => {
    const query = searchQuery.value.trim();
    router.get(`/${props.locale}/rubrics/${props.rubricUrl}`, {
        search: query,
        page_scheduled: 1,
        page_articles: 1,
        page_completed: 1,
    }, {
        preserveScroll: true,
        preserveState: false,
    });
};

const clearSearch = () => {
    searchQuery.value = '';
    router.get(`/${props.locale}/rubrics/${props.rubricUrl}`, {
        page_scheduled: 1,
        page_articles: 1,
        page_completed: 1,
    }, {
        preserveScroll: true,
        preserveState: false,
    });
};

// Вид отображения
const viewKey = 'completed_view_mode';
const viewMode = ref(localStorage.getItem(viewKey) || 'grid');

const setViewMode = (mode) => {
    viewMode.value = mode;
    localStorage.setItem(viewKey, mode);
};

// Пагинация
const currentPage = ref(props.currentPage || 1);
watch(currentPage, (val) => emit('page-changed', val));

// Обратный отсчёт
const countdowns = ref({});
const updateCountdowns = () => {
    const now = new Date().getTime();
    props.tournaments.data.forEach(t => {
        const time = new Date(t.tournament_date_time).getTime();
        const diff = time - now;
        if (diff > 0) {
            countdowns.value[t.id] = {
                days: Math.floor(diff / (1000 * 60 * 60 * 24)),
                hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
                minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
                seconds: Math.floor((diff % (1000 * 60)) / 1000)
            };
        } else {
            countdowns.value[t.id] = null;
        }
    });
};

let timer = null;
onMounted(() => {
    updateCountdowns();
    timer = setInterval(updateCountdowns, 1000);
});
onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});

function highlightVs(name) {
    const formatted = name.replaceAll('-', ' ');
    return formatted.replace(/\bvs\b/i, '<span class="text-red-400">vs</span>');
}

// watch(
//     () => props.tournaments,
//     (newVal) => {
//         console.log('[DEBUG] Пришли турниры:', newVal.data.map(t => t.name));
//     },
//     { immediate: true }
// );

// watch(
//     () => searchQuery.value,
//     (newVal) => {
//         console.log('[DEBUG] Строка поиска:', newVal);
//     },
//     { immediate: true }
// );

</script>

<template>
    <section class="space-y-8" v-if="tournaments.data.length">

        <!-- Заголовок и переключатель вида -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-4 mt-6">

            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                {{ t('statusCompleted') }}
            </h2>

            <!-- Поисковая строка -->
            <form @submit.prevent="onSearch" class="w-full max-w-lg">
                <div class="relative w-full">
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="t('searchByName')"
                        class="w-full pl-3 pr-16 py-1 bg-white dark:bg-gray-700 font-semibold text-sm text-slate-600 dark:text-slate-100 border border-slate-500 dark:border-slate-400 rounded-sm focus:outline-none focus:ring-1 focus:border-blue-300"
                    />

                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="clearSearch"
                        class="absolute right-8 top-1/2 transform -translate-y-1/2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-800 border border-gray-900 dark:border-gray-100 py-0 px-1.5 text-slate-700 dark:text-white hover:text-red-500"
                        title="Очистить"
                    >✕
                    </button>

                    <button
                        type="submit"
                        class="absolute right-1 top-1/2 transform -translate-y-1/2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-800 border border-gray-900 dark:border-gray-100 p-1"
                        :title="t('searchByName')"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                            <path class="fill-current text-slate-500 dark:text-slate-300"
                                  d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z"/>
                            <path class="fill-current text-slate-400 dark:text-slate-200"
                                  d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z"/>
                        </svg>
                    </button>
                </div>
            </form>

            <div class="flex justify-end items-center space-x-2">
                <button @click="setViewMode('grid')"
                        :class="[
                          'p-1 border transition-colors duration-200 rounded',
                          viewMode === 'grid'
                          ? 'border-slate-400 dark:border-slate-200 text-red-400 dark:text-red-200'
                          : 'border-slate-300 dark:border-slate-400 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 hover:border-slate-500 dark:hover:border-slate-500'
                        ]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>

                <button @click="setViewMode('horizontal')"
                        :class="[
                          'p-1 border transition-colors duration-200 rounded',
                          viewMode === 'horizontal'
                          ? 'border-slate-400 dark:border-slate-200 text-red-400 dark:text-red-200'
                          : 'border-slate-300 dark:border-slate-400 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 hover:border-slate-500 dark:hover:border-slate-500'
                        ]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- GRID VIEW -->
        <div v-if="viewMode === 'grid'"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-4">

            <div v-for="tournament in tournaments.data" :key="tournament.id"
                 class="overflow-hidden bg-slate-100 dark:bg-blue-950 rounded-xl
                        border-1 border-blue-800 dark:border-slate-400
                        shadow-md shadow-gray-400 dark:shadow-gray-900">
                <Link :href="`/tournaments/${tournament.url}`">
                    <div>

                        <template v-if="tournament.images?.length">
                            <div class="relative w-full h-full overflow-hidden">
                                <img :src="tournament.images[0].url"
                                     :alt="tournament.images[0].alt"
                                     class="w-full h-full object-cover"/>

                            </div>
                            <!-- Инфо -->
                            <div class="bg-slate-100 dark:bg-blue-950
                                            text-gray-700 dark:text-gray-100 text-center">
                                <h3 class="uppercase font-bold text-sm py-1 px-1 h-12"
                                    v-html="highlightVs(tournament.name)">
                                </h3>

                                <div class="flex items-center justify-center
                                                bg-blue-900 dark:bg-blue-700
                                                text-xs text-slate-100 py-1">
                                    <svg class="w-3 h-3 mr-1 mb-1 fill-current" viewBox="0 0 16 16">
                                        <path
                                            d="M15 2h-2V0h-2v2H9V0H7v2H5V0H3v2H1a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V3a1 1 0 00-1-1zm-1 12H2V6h12v8z"></path>
                                    </svg>
                                    {{ tournament.tournament_date_time }}
                                </div>

                                <div v-if="tournament.venue" class="text-xs py-1 h-9">
                                    {{ tournament.venue }}
                                </div>
                            </div>

                        </template>

                        <!-- Блок бойцов + Инфо -->
                        <div v-else
                             class="relative overflow-hidden">

                            <!-- Контейнер бойцов -->
                            <div class="flex flex-col sm:flex-row justify-around items-center
                                            bg-gray-200 dark:bg-gray-800 gap-6 py-8 px-2">

                                <!-- Красный боец -->
                                <div class="flex flex-col items-center">
                                    <div class="relative w-24 h-24">
                                        <div class="absolute inset-0 bg-red-600 rounded-full"></div>
                                        <img v-if="tournament.fighter_red?.avatar"
                                             :src="`/storage/${tournament.fighter_red.avatar}`"
                                             class="absolute inset-0 m-auto z-10 w-full h-full object-contain"
                                             :alt="tournament.fighter_red?.nickname"/>
                                    </div>
                                </div>

                                <!-- Надпись VS -->
                                <span class="text-orange-500 text-lg sm:text-xl md:text-2xl font-bold">vs</span>

                                <!-- Синий боец -->
                                <div class="flex flex-col items-center">
                                    <div class="relative w-24 h-24">
                                        <div class="absolute inset-0 bg-blue-600 rounded-full"></div>
                                        <img v-if="tournament.fighter_blue?.avatar"
                                             :src="`/storage/${tournament.fighter_blue.avatar}`"
                                             class="absolute inset-0 m-auto z-10 w-full h-full object-contain"
                                             :alt="tournament.fighter_blue?.nickname"/>
                                    </div>
                                </div>

                            </div>

                            <!-- Название турнира -->
                            <h3 class="text-gray-700 dark:text-gray-100
                                           text-center uppercase font-bold text-sm py-1 px-1 h-12"
                                v-html="highlightVs(tournament.name)"></h3>

                            <!-- Дата -->
                            <div class="h-7 bg-blue-900 dark:bg-blue-700
                                            flex items-center justify-center
                                            text-xs text-slate-100 text-center py-1">
                                <svg class="w-3 h-3 mr-1 mb-1 fill-current" viewBox="0 0 16 16">
                                    <path
                                        d="M15 2h-2V0h-2v2H9V0H7v2H5V0H3v2H1a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V3a1 1 0 00-1-1zm-1 12H2V6h12v8z"/>
                                </svg>
                                <span>{{ tournament.tournament_date_time }}</span>
                            </div>

                            <!-- Место проведения -->
                            <div v-if="tournament.venue"
                                 class="text-xs text-center mt-1 text-gray-700 dark:text-gray-100 h-9">
                                {{ tournament.venue }}
                            </div>

                        </div>

                    </div>
                </Link>
            </div>

        </div>

        <!-- HORIZONTAL VIEW -->
        <div v-else class="w-full max-w-7xl mx-auto px-4 space-y-6">

            <div v-for="tournament in tournaments.data" :key="tournament.id"
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
                                 :alt="tournament.fighter_red.nickname"/>
                        </div>
                        <!--                        <span class="my-4 text-center text-sm font-semibold uppercase text-slate-100">-->
                        <!--                            {{ tournament.fighter_red?.nickname.replaceAll('-', ' ') }}-->
                        <!--                        </span>-->
                    </div>

                    <span class="text-xl sm:text-2xl font-bold text-orange-400">vs</span>

                    <div class="flex flex-col items-center w-full sm:w-1/2 max-w-xs">
                        <div class="relative w-52 h-52">
                            <div class="absolute inset-0 bg-blue-600 rounded-full z-0"></div>
                            <img v-if="tournament.fighter_blue?.avatar"
                                 :src="`/storage/${tournament.fighter_blue.avatar}`"
                                 class="absolute left-1/2 -top-4 transform -translate-x-1/2 z-10 w-52 h-52 object-contain"
                                 :alt="tournament.fighter_blue.nickname"/>
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
                            <path
                                d="M15 2h-2V0h-2v2H9V0H7v2H5V0H3v2H1a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V3a1 1 0 00-1-1zm-1 12H2V6h12v8z"></path>
                        </svg>
                        {{ tournament.tournament_date_time }}
                    </div>
                    <div v-if="tournament.venue" class="text-xs text-slate-600 dark:text-slate-200">
                        {{ tournament.venue }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Пагинация -->
        <div v-if="pagination.lastPage > 1"
             class="flex items-center justify-center mt-6 space-x-2 text-sm font-medium">
            <button @click="currentPage--" :disabled="currentPage <= 1"
                    class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-400 dark:border-gray-200 disabled:opacity-50">
                «
            </button>

            <input type="number" v-model.number="currentPage" :min="1" :max="pagination.lastPage"
                   class="w-16 text-center px-3 py-1.5 border border-gray-400 dark:border-gray-200 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"/>

            <span>/ {{ pagination.lastPage }}</span>

            <button @click="currentPage++" :disabled="currentPage >= pagination.lastPage"
                    class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-400 dark:border-gray-200 disabled:opacity-50">
                »
            </button>
        </div>
    </section>
</template>
