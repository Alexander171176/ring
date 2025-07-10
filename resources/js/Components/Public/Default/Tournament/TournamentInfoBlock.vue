<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    tournament: Object,
    winnerFighter: String,
    highlightVs: Function,
});

const { t } = useI18n();
</script>

<template>
    <div class="flex flex-col lg:flex-row lg:gap-6 items-start w-full">

        <!-- Левая колонка — изображение -->
        <div v-if="tournament.images?.length" class="w-full lg:w-2/3">
            <img :src="tournament.images[0].url"
                 :alt="tournament.images[0].alt"
                 class="w-full h-auto object-cover rounded-xl
                        border-2 border-gray-400 dark:border-gray-600
                        shadow-lg shadow-gray-400 dark:shadow-gray-950" />

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

            <header class="my-1">
                <h1 class="uppercase font-black dark:text-white"
                    v-html="highlightVs(tournament.name)">
                </h1>
            </header>

            <time :datetime="tournament.tournament_date_time"
                  class="text-red-500 dark:text-red-400 text-xs font-black my-4">
                {{ tournament.tournament_date_time }} (UTC+5) Astana
            </time>

            <div class="my-1 xl:my-4">
                <div v-if="tournament.venue" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ tournament.venue }}
                </div>

                <div v-if="tournament.rounds_scheduled" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('roundsScheduled') }}: {{ tournament.rounds_scheduled }}
                </div>

                <div v-if="tournament.weight_class_name" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ tournament.weight_class_name }}
                </div>
            </div>

            <div class="my-1 xl:my-4">
                <div v-if="winnerFighter" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('winner') }}: {{ winnerFighter }}
                </div>

                <div v-if="tournament.method_of_victory" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('methodVictory') }}: {{ tournament.method_of_victory }}
                </div>

                <div v-if="tournament.round_of_finish" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('roundFinish') }}: {{ tournament.round_of_finish }}
                </div>

                <div v-if="tournament.time_of_finish" class="text-[10px] sm:text-xs text-slate-700 dark:text-slate-300 font-semibold my-2 xl:my-3">
                    {{ t('timeFinish') }}: {{ tournament.time_of_finish }}
                </div>
            </div>

        </div>
    </div>
</template>
