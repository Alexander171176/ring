<script setup>
import {Head, Link, router, usePage} from '@inertiajs/vue3';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { useI18n } from 'vue-i18n';
import {computed, onMounted, onUnmounted, ref, watch} from "vue";
import ScheduledTournamentsShow from "@/Components/Public/Default/Tournament/ScheduledTournamentsShow.vue";
import CompletedTournamentsShow from "@/Components/Public/Default/Tournament/CompletedTournamentsShow.vue";

const { t } = useI18n();
const {
    tournament,
    scheduledTournaments,
    completedTournaments,
    scheduledPagination,
    completedPagination
} = usePage().props;

// console.log('scheduledTournaments:', scheduledTournaments);
// console.log('completedTournaments:', completedTournaments);
// console.log('scheduledPagination:', scheduledPagination);
// console.log('completedPagination:', completedPagination);

function highlightVs(name) {
    const formatted = name.replaceAll('-', ' ');
    return formatted.replace(/\bvs\b/i, '<span class="text-red-500 font-black">vs</span>');
}

// Таймер
const countdown = ref({
    days: '00',
    hours: '00',
    minutes: '00',
    seconds: '00',
});

const isStarted = ref(false);
let countdownInterval;

function pad(value) {
    return String(value).padStart(2, '0');
}

function updateCountdown() {
    const targetDate = new Date(tournament.tournament_date_time);
    const now = new Date();
    const diff = targetDate - now;

    if (diff <= 0) {
        isStarted.value = true;
        clearInterval(countdownInterval);
        return;
    }

    countdown.value = {
        days: pad(Math.floor(diff / (1000 * 60 * 60 * 24))),
        hours: pad(Math.floor((diff / (1000 * 60 * 60)) % 24)),
        minutes: pad(Math.floor((diff / (1000 * 60)) % 60)),
        seconds: pad(Math.floor((diff / 1000) % 60)),
    };
}

onMounted(() => {
    updateCountdown();
    countdownInterval = setInterval(updateCountdown, 1000);
});

onUnmounted(() => {
    clearInterval(countdownInterval);
});

function calculateAge(dateString) {
    const birthDate = new Date(dateString);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

const currentPageScheduled = ref(scheduledPagination.currentPage);
const currentPageCompleted = ref(completedPagination.currentPage);

watch(currentPageScheduled, (page) => {
    if (page >= 1 && page <= scheduledPagination.lastPage) {
        router.get(route('public.tournaments.show', tournament.url), {
            page_scheduled: page,
            page_completed: currentPageCompleted.value
        }, { preserveScroll: true })
    }
})

watch(currentPageCompleted, (page) => {
    if (page >= 1 && page <= completedPagination.lastPage) {
        router.get(route('public.tournaments.show', tournament.url), {
            page_scheduled: currentPageScheduled.value,
            page_completed: page
        }, { preserveScroll: true })
    }
})

const winnerFighter = computed(() => {
    let nickname = null;

    if (tournament.winner_id === tournament.fighter_red?.id) {
        nickname = tournament.fighter_red?.nickname;
    } else if (tournament.winner_id === tournament.fighter_blue?.id) {
        nickname = tournament.fighter_blue?.nickname;
    }

    if (nickname) {
        return nickname.replaceAll('-', ' ').toUpperCase();
    }

    return null;
});

</script>

<template>
    <DefaultLayout :title="tournament.title" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
        <Head>
            <title>{{ tournament.name }}</title>
            <meta name="title" :content="tournament.name || ''"/>
            <meta name="description" :content="tournament.meta_desc || ''"/>
            <meta name="keywords" :content="tournament.meta_keywords || ''"/>
            <meta name="author" :content="'ring.com.kz'"/>
            <meta name="viewport" content="width=device-width, initial-scale=1"/>
            <meta property="og:title" :content="tournament.name || ''"/>
            <meta property="og:description" :content="tournament.meta_desc || ''"/>
            <meta property="og:type" content="tournament"/>
            <meta property="og:url" :content="`/tournaments/${tournament.url}`"/>
            <meta property="og:image" :content="tournament.images?.[0]?.url || ''"/>
            <meta property="og:locale" :content="tournament.locale || 'ru_RU'"/>
            <meta name="twitter:card" content="summary_large_image"/>
            <meta name="twitter:title" :content="tournament.name || ''"/>
            <meta name="twitter:description" :content="tournament.meta_desc || ''"/>
            <meta name="twitter:image" :content="tournament.images?.[0]?.url || ''"/>
            <meta itemprop="name" :content="tournament.name || ''"/>
            <meta itemprop="description" :content="tournament.meta_desc || ''"/>
            <meta itemprop="image" :content="tournament.images?.[0]?.url || ''"/>
        </Head>

        <div class="flex flex-col w-full bg-slate-50 dark:bg-blue-950">

            <nav class="text-left text-sm ml-0 md:ml-4 lg:ml-6 xl:ml-8 mt-4 px-1"
                 aria-label="Breadcrumb">
                <ol class="list-reset flex flex-col md:flex-row items-center justify-start space-x-0">
                    <li>
                        <Link href="/" class="hover:underline text-slate-900 dark:text-slate-100">
                            {{ t('home') }}
                        </Link>
                        <span class="mx-1 text-slate-900 dark:text-slate-100">/</span>
                    </li>
                    <li class="text-slate-900 dark:text-slate-100 lowercase">
                        {{ tournament.name }}
                    </li>
                </ol>
            </nav>

            <div class="flex flex-col bg-slate-50 dark:bg-blue-950 w-full p-8">

                <div class="flex flex-col lg:flex-row lg:gap-6 items-start w-full">

                    <!-- Левая колонка — изображение -->
                    <div v-if="tournament.images?.length" class="w-full lg:w-2/3">
                        <template v-if="tournament.images?.length">
                            <img :src="tournament.images[0].url"
                                 :alt="tournament.images[0].alt"
                                 class="w-full h-auto object-cover rounded-xl
                                        border-2 border-gray-400 dark:border-gray-600
                                        shadow-lg shadow-gray-400 dark:shadow-gray-950"/>
                        </template>

                        <!-- Краткое описание -->
                        <div v-if="tournament.short" class="my-2 xl:my-3">
                            <p class="text-sm text-slate-900 dark:text-slate-100 px-1 py-2">
                                {{ tournament.short }}
                            </p>
                        </div>

                    </div>

                    <!-- Правая колонка — текст -->
                    <div v-if="tournament.images?.length"
                         class="w-full md:w-1/2 md:mx-auto lg:w-1/3 flex flex-col justify-start">

                        <span class="text-left text-[11px] text-gray-700 dark:text-gray-300
                                    mt-0 mb-2 xl:mt-8 xl:mb-4">
                            {{ t('battle') }}
                        </span>

                        <header>
                            <div class="my-1">
                                <h1 class="uppercase font-black dark:text-white"
                                    v-html="highlightVs(tournament.name)">
                                </h1>
                            </div>
                        </header>

                        <!-- Дата и время -->
                        <time :datetime="tournament.tournament_date_time"
                              class="text-red-500 dark:text-red-400 text-xs font-black my-4">
                            {{ tournament.tournament_date_time }} (UTC+5) Astana
                        </time>

                        <div class="my-1 xl:my-4">

                            <!-- Место проведения -->
                            <div v-if="tournament.venue"
                                  class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                                {{ tournament.venue }}
                            </div>

                            <!-- Количество раундов -->
                            <div v-if="tournament.rounds_scheduled"
                                 class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                                {{ t('roundsScheduled') }}: {{ tournament.rounds_scheduled }}
                            </div>

                            <!-- Название весовой категории -->
                            <div v-if="tournament.weight_class_name"
                                 class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                                {{ tournament.weight_class_name }}
                            </div>

                        </div>

                        <div class="my-1 xl:my-4">

                            <!-- Победитель -->
                            <div v-if="winnerFighter"
                                 class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                                {{ t('winner') }}: {{ winnerFighter }}
                            </div>

                            <!-- Метод победы (например, "KO", "Submission") -->
                            <div v-if="tournament.method_of_victory"
                                 class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                                {{ t('methodVictory') }}: {{ tournament.method_of_victory }}
                            </div>

                            <!-- Раунд, в котором завершился поединок -->
                            <div v-if="tournament.round_of_finish"
                                 class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                                {{ t('roundFinish') }}: {{ tournament.round_of_finish }}
                            </div>

                            <!-- Время в раунде завершения поединка (например, "02:35") -->
                            <div v-if="tournament.time_of_finish"
                                 class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                                {{ t('timeFinish') }}: {{ tournament.time_of_finish }}
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Таймер -->
                <div v-if="!isStarted" class="w-full text-center mt-4">
                    <div v-if="countdown" class="flex justify-center gap-3 flex-wrap">
                        <div class="flex flex-col items-center w-16 h-16 rounded-lg
                                    bg-gray-200 dark:bg-gray-700
                                    shadow-md shadow-gray-400 dark:shadow-gray-900
                                    border border-slate-400">
                            <span class="text-xl font-bold text-gray-800 dark:text-white mt-3">
                                {{ countdown.days }}
                            </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-300 uppercase mt-1">
                                {{ t('timerDays') || 'дней' }}
                            </span>
                        </div>
                        <div class="flex flex-col items-center w-16 h-16 rounded-lg
                                    bg-gray-200 dark:bg-gray-700
                                    shadow-md shadow-gray-400 dark:shadow-gray-900
                                    border border-slate-400">
                            <span class="text-xl font-bold text-gray-800 dark:text-white mt-3">
                                {{ countdown.hours }}
                            </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-300 uppercase mt-1">
                                {{ t('timerHours') || 'часов' }}
                            </span>
                        </div>
                        <div class="flex flex-col items-center w-16 h-16 rounded-lg
                                    bg-gray-200 dark:bg-gray-700
                                    shadow-md shadow-gray-400 dark:shadow-gray-900
                                    border border-slate-400">
                            <span class="text-xl font-bold text-gray-800 dark:text-white mt-3">
                                {{ countdown.minutes }}
                            </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-300 uppercase mt-1">
                                {{ t('timerMinutes') || 'мин' }}
                            </span>
                        </div>
                        <div class="flex flex-col items-center w-16 h-16 rounded-lg
                                    bg-gray-200 dark:bg-gray-700
                                    shadow-md shadow-gray-400 dark:shadow-gray-900
                                    border border-slate-400">
                            <span class="text-xl font-bold text-gray-800 dark:text-white mt-3">
                                {{ countdown.seconds }}
                            </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-300 uppercase mt-1">
                                {{ t('timerSeconds') || 'сек' }}
                            </span>
                        </div>
                    </div>

                    <p v-else class="text-sm font-semibold text-green-600 dark:text-green-400 mt-2">
                        {{ t('tournamentStarted') || 'Турнир уже начался' }}
                    </p>
                </div>

                <!-- Бойцы -->
                <div class="w-full py-8 mb-4 flex flex-col sm:flex-row items-center justify-between gap-4">

                    <div class="flex flex-col items-center w-full sm:w-1/2">
                        <div class="relative w-64 h-64">
                            <div class="absolute inset-0 bg-red-600 rounded-full z-0"></div>
                            <img v-if="tournament.fighter_red?.avatar"
                                 :src="`/storage/${tournament.fighter_red.avatar}`"
                                 class="absolute left-1/2 -top-4 transform -translate-x-1/2 z-10 w-64 h-64 object-contain"
                                 :alt="tournament.fighter_red.nickname"/>
                        </div>
                        <span class="mt-4 text-center text-sm font-black uppercase text-slate-900 dark:text-slate-100">
                            {{ tournament.fighter_red?.nickname.replaceAll('-', ' ') }}
                        </span>
                        <span class="text-center text-[10px] text-slate-900 dark:text-slate-100 font-semibold">
                            {{ t('statistics') }}
                            {{ tournament.fighter_red?.wins }} -
                            {{ tournament.fighter_red?.draws }} -
                            {{ tournament.fighter_red?.losses }}
                            ({{ tournament.fighter_red?.wins_by_ko }} KO)
                        </span>
                        <span v-if="tournament.fighter_red?.date_of_birth"
                              class="text-center text-xs text-slate-600 dark:text-slate-300">
                            {{ t('age') }} {{ calculateAge(tournament.fighter_red.date_of_birth) }} {{ t('years')}}
                        </span>
                    </div>

                        <span class="text-xl sm:text-2xl font-bold text-orange-400">vs</span>

                    <div class="flex flex-col items-center w-full sm:w-1/2">
                        <div class="relative w-64 h-64">
                            <div class="absolute inset-0 bg-blue-600 rounded-full z-0"></div>
                            <img v-if="tournament.fighter_blue?.avatar"
                                 :src="`/storage/${tournament.fighter_blue.avatar}`"
                                 class="absolute left-1/2 -top-4 transform -translate-x-1/2 z-10 w-64 h-64 object-contain"
                                 :alt="tournament.fighter_blue.nickname"/>
                        </div>
                        <span class="mt-4 text-center text-sm font-black uppercase text-slate-900 dark:text-slate-100">
                            {{ tournament.fighter_blue?.nickname.replaceAll('-', ' ') }}
                        </span>
                        <span class="text-center text-[10px] text-slate-900 dark:text-slate-100 font-semibold">
                            {{ t('statistics') }}
                            {{ tournament.fighter_blue?.wins }} -
                            {{ tournament.fighter_blue?.draws }} -
                            {{ tournament.fighter_blue?.losses }}
                            ({{ tournament.fighter_blue?.wins_by_ko }} KO)
                        </span>
                        <span v-if="tournament.fighter_blue?.date_of_birth"
                              class="text-center text-xs text-slate-600 dark:text-slate-300">
                            {{ t('age') }} {{ calculateAge(tournament.fighter_blue.date_of_birth) }} {{ t('years')}}
                        </span>
                    </div>

                </div>

                <div v-if="tournament.description"
                     class="my-2 xl:my-3 dark:text-slate-100"
                     v-html="tournament.description">
                </div>

            </div>

            <!-- Запланированные турниры -->
            <ScheduledTournamentsShow
                :tournaments="scheduledTournaments"
                :pagination="scheduledPagination"
                :tournament-url="tournament.url"
                v-model:currentPage="currentPageScheduled"
            />

            <!-- Завершённые турниры -->
            <CompletedTournamentsShow
                :tournaments="completedTournaments"
                :pagination="completedPagination"
                :tournament-url="tournament.url"
                v-model:currentPage="currentPageCompleted"
            />

        </div>
    </DefaultLayout>
</template>
