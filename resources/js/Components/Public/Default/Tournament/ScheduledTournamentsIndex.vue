<script setup>
import {computed} from 'vue';
import {Link} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';

const { t, locale } = useI18n();
const props = defineProps({
    tournaments: {
        type: Array,
        required: true
    }
});

const scheduledTournaments = computed(() => {
    return [...props.tournaments]
        .filter(t => t.status === 'scheduled')
        .sort((a, b) => new Date(b.tournament_date_time) - new Date(a.tournament_date_time));
});

function highlightVs(name) {
    const formatted = name.replaceAll('-', ' ');
    return formatted.replace(/\bvs\b/i, '<span class="text-red-400">vs</span>');
}

const linkHref = locale.value === 'kk'
    ? '/rubrics/zhadnama'
    : '/rubrics/raspisanie';

</script>

<template>
    <div v-if="scheduledTournaments.length" class="mt-8">

        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">
            {{ t('statusScheduled') }}
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <div v-for="tournament in scheduledTournaments" :key="tournament.id"
                 class="overflow-hidden bg-slate-100 dark:bg-blue-950 rounded-xl
                        border-1 border-blue-800 dark:border-slate-400
                        shadow-md shadow-gray-400 dark:shadow-gray-900">

                <div>
                    <Link :href="`/tournaments/${tournament.url}`">
                        <template v-if="tournament.images?.length">
                            <div class="relative w-full h-full overflow-hidden">
                                <img :src="tournament.images[0].url"
                                     :alt="tournament.images[0].alt"
                                     class="w-full h-full object-cover"/>

                            </div>
                            <!-- Инфо -->
                            <div class="bg-slate-100 dark:bg-blue-950
                                        text-gray-700 dark:text-gray-100 text-center">
                                <h3 class="uppercase font-bold text-sm py-1"
                                    v-html="highlightVs(tournament.name)"></h3>

                                <div class="flex items-center justify-center
                                            bg-blue-900 dark:bg-blue-700
                                            text-xs text-slate-100 mt-1 py-1">
                                    <svg class="w-3 h-3 mr-1 fill-current" viewBox="0 0 16 16">
                                        <path
                                            d="M15 2h-2V0h-2v2H9V0H7v2H5V0H3v2H1a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V3a1 1 0 00-1-1zm-1 12H2V6h12v8z"></path>
                                    </svg>
                                    {{ tournament.tournament_date_time }}
                                </div>

                                <div v-if="tournament.venue" class="text-xs py-1">
                                    {{ tournament.venue }}
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
                    </Link>
                </div>

            </div>

        </div>

        <div class="w-full flex justify-center items-center py-4">
            <Link
                :href="linkHref"
                class="inline-block px-2 py-0.5 rounded-sm text-sm font-semibold
                       border border-slate-400 dark:border-slate-500
                       bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                       hover:text-red-400 dark:hover:text-red-300
                       transition duration-200 ease-in-out shadow-sm hover:shadow-md">
                {{ t('watchAll') }} »
            </Link>
        </div>

    </div>
</template>
