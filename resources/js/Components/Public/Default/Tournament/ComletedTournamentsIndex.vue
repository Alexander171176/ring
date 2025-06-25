<!-- Компонент: CompletedTournamentsIndex.vue -->
<script setup>
import {ref, computed, watch} from 'vue';
import {Link} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

const props = defineProps({
    tournaments: {
        type: Array,
        required: true
    }
});

const currentPage = ref(1);
const sortOrder = ref('desc'); // 'asc' | 'desc'
const itemsPerPage = 2;

const completedTournaments = computed(() => {
    return [...props.tournaments]
        .filter(t => t.status === 'completed')
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

function highlightVs(name) {
    const formatted = name.replaceAll('-', ' ');
    return formatted.replace(/\bvs\b/i, '<span class="text-red-400">vs</span>');
}
</script>

<template>
    <div v-if="completedTournaments.length" class="mt-4">

        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">
            {{ t('statusCompleted') }}
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <div v-for="tournament in paginatedCompleted" :key="tournament.id"
                 class="overflow-hidden bg-gray-300
                        border-2 border-blue-800 dark:border-slate-400
                        shadow-md shadow-gray-400 dark:shadow-gray-900">

                <div>

                    <template v-if="tournament.images?.length">
                        <div class="relative w-full h-full overflow-hidden">
                            <img :src="tournament.images[0].url"
                                 :alt="tournament.images[0].alt"
                                 class="w-full h-full object-cover"/>

                            <!-- Инфо наложение -->
                            <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-center p-1">
                                <h3 class="uppercase font-semibold text-sm"
                                    v-html="highlightVs(tournament.name)"></h3>

                                <div class="flex items-center justify-center text-xs text-amber-400 mt-1">
                                    <svg class="w-3 h-3 mr-1 fill-current" viewBox="0 0 16 16">
                                        <path
                                            d="M15 2h-2V0h-2v2H9V0H7v2H5V0H3v2H1a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V3a1 1 0 00-1-1zm-1 12H2V6h12v8z"></path>
                                    </svg>
                                    {{ tournament.tournament_date_time }}
                                </div>

                                <div v-if="tournament.venue" class="text-xs">
                                    {{ tournament.venue }}
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Блок бойцов + Инфо в наложении -->
                    <div v-else
                         class="relative overflow-hidden p-4">

                        <!-- Контейнер бойцов -->
                        <div class="flex flex-col sm:flex-row justify-around items-center gap-6">

                            <!-- Красный боец -->
                            <div class="flex flex-col items-center">
                                <div class="relative w-32 h-32
                                            sm:w-40 sm:h-40 md:w-48 md:h-48 lg:w-50 lg:h-50 xl:w-56 xl:h-56">
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
                                <div class="relative w-32 h-32
                                            sm:w-40 sm:h-40 md:w-48 md:h-48 lg:w-50 lg:h-50 xl:w-56 xl:h-56">
                                    <div class="absolute inset-0 bg-blue-600 rounded-full"></div>
                                    <img v-if="tournament.fighter_blue?.avatar"
                                         :src="`/storage/${tournament.fighter_blue.avatar}`"
                                         class="absolute inset-0 m-auto z-10 w-full h-full object-contain"
                                         :alt="tournament.fighter_blue?.nickname"/>
                                </div>
                            </div>

                        </div>

                        <!-- Название турнира -->
                        <h3 class="mt-2 px-2 uppercase font-semibold text-sm text-center"
                            v-html="highlightVs(tournament.name)"></h3>

                        <!-- Место проведения -->
                        <div v-if="tournament.venue"
                             class="text-xs text-center mt-1 pb-10 text-gray-700">
                            {{ tournament.venue }}
                        </div>

                        <!-- Дата наложение -->
                        <div class="h-10 absolute bottom-2 left-0 right-0 bg-blue-950/70">
                            <div class="flex items-center justify-center
                                        font-semibold text-xs sm:text-sm md:text-base text-white text-center mt-1">
                                <svg class="w-3 h-3 mr-2 fill-current" viewBox="0 0 16 16">
                                    <path
                                        d="M15 2h-2V0h-2v2H9V0H7v2H5V0H3v2H1a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V3a1 1 0 00-1-1zm-1 12H2V6h12v8z"/>
                                </svg>
                                <span>{{ tournament.tournament_date_time }}</span>
                            </div>
                        </div>

                    </div>

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
                          bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs"/>

            <span class="text-gray-700 dark:text-gray-200">{{ t('of') }} {{ totalPages }}</span>

            <button @click="nextPage" :disabled="currentPage === totalPages"
                    class="px-3 py-1 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                           border border-gray-400 dark:border-gray-200 disabled:opacity-50">
                »
            </button>
        </div>

    </div>
</template>
