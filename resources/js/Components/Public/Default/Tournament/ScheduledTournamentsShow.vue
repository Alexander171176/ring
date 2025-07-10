<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
    tournaments: Object,
    pagination: Object,
    tournamentUrl: String,
});

const currentPage = defineModel('currentPage');

watch(currentPage, (page) => {
    if (page >= 1 && page <= props.pagination.lastPage) {
        router.get(route('public.tournaments.show', props.tournamentUrl), {
            page_scheduled: page,
            page_completed: undefined,
        }, { preserveScroll: true });
    }
});

function highlightVs(name) {
    const formatted = name.replaceAll('-', ' ');
    return formatted.replace(/\bvs\b/i, '<span class="text-red-400">vs</span>');
}
</script>

<template>
    <div class="mb-8">
        <div v-if="tournaments.data.length" class="px-8">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">
                {{ t('statusScheduled') }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="tournament in tournaments.data" :key="tournament.id"
                     class="overflow-hidden bg-slate-100 dark:bg-blue-950 rounded-xl
                            shadow-xl shadow-gray-400 dark:shadow-gray-950
                            border border-gray-500 dark:border-gray-700">
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
        </div>

        <div v-if="pagination.lastPage > 1"
             class="flex items-center justify-center mt-6 space-x-2 text-sm font-medium">
            <button @click="currentPage--" :disabled="currentPage <= 1"
                    class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-400 dark:border-gray-200 disabled:opacity-50">«</button>

            <input type="number" v-model.number="currentPage" :min="1" :max="pagination.lastPage"
                   class="w-16 text-center px-3 py-1.5 border border-gray-400 dark:border-gray-200 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"/>

            <span class="text-gray-700 dark:text-gray-300">/ {{ pagination.lastPage }}</span>

            <button @click="currentPage++" :disabled="currentPage >= pagination.lastPage"
                    class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-400 dark:border-gray-200 disabled:opacity-50">»</button>
        </div>
    </div>
</template>
