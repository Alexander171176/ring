<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { computed, ref } from 'vue';

const { t } = useI18n();

const {
    rubric,
    scheduledTournaments,
    completedTournaments,
} = usePage().props;

const itemsPerPage = 10;

// 🔁 Принудительная сортировка по tournament_date_time от новых к старым
const sortedScheduledTournaments = computed(() => {
    return [...scheduledTournaments].sort((a, b) =>
        new Date(b.tournament_date_time) - new Date(a.tournament_date_time)
    );
});
const sortedCompletedTournaments = computed(() => {
    return [...completedTournaments].sort((a, b) =>
        new Date(b.tournament_date_time) - new Date(a.tournament_date_time)
    );
});

// 🔢 Пагинация для запланированных
const scheduledPage = ref(1);
const totalScheduledPages = computed(() =>
    Math.max(1, Math.ceil(sortedScheduledTournaments.value.length / itemsPerPage))
);
const paginatedScheduled = computed(() => {
    const start = (scheduledPage.value - 1) * itemsPerPage;
    return sortedScheduledTournaments.value.slice(start, start + itemsPerPage);
});
const goToScheduledPage = (page) => {
    scheduledPage.value = Math.min(Math.max(1, page), totalScheduledPages.value);
};

// 🔢 Пагинация для завершённых
const completedPage = ref(1);
const totalCompletedPages = computed(() =>
    Math.max(1, Math.ceil(sortedCompletedTournaments.value.length / itemsPerPage))
);
const paginatedCompleted = computed(() => {
    const start = (completedPage.value - 1) * itemsPerPage;
    return sortedCompletedTournaments.value.slice(start, start + itemsPerPage);
});
const goToCompletedPage = (page) => {
    completedPage.value = Math.min(Math.max(1, page), totalCompletedPages.value);
};
</script>

<template>
    <DefaultLayout :title="rubric.title"
                   :can-login="$page.props.canLogin"
                   :can-register="$page.props.canRegister">

        <!-- SEO -->
        <Head>
            <title>{{ rubric.title }}</title>
            <meta name="title" :content="rubric.title || ''"/>
            <meta name="keywords" :content="rubric.meta_keywords || ''"/>
            <meta name="description" :content="rubric.meta_desc || ''"/>

            <meta property="og:title" :content="rubric.title || ''"/>
            <meta property="og:description" :content="rubric.meta_desc || ''"/>
            <meta property="og:type" content="website"/>
            <meta property="og:url" :content="`/rubrics/${rubric.url}`"/>
            <meta property="og:image" :content="rubric.icon || ''"/>
            <meta property="og:locale" :content="rubric.locale || 'ru_RU'"/>

            <meta name="twitter:card" content="summary_large_image"/>
            <meta name="twitter:title" :content="rubric.title || ''"/>
            <meta name="twitter:description" :content="rubric.meta_desc || ''"/>
            <meta name="twitter:image" :content="rubric.icon || ''"/>

            <meta name="DC.title" :content="rubric.title || ''"/>
            <meta name="DC.description" :content="rubric.meta_desc || ''"/>
            <meta name="DC.identifier" :content="`/rubrics/${rubric.url}`"/>
            <meta name="DC.language" :content="rubric.locale || 'ru'"/>
        </Head>

        <div class="flex-1 p-4 selection:bg-red-400 selection:text-white bg-slate-50 dark:bg-slate-950">

            <!-- Хлебные крошки -->
            <nav class="text-sm ml-0 md:ml-4 lg:ml-6 xl:ml-8"
                 aria-label="Breadcrumb">
                <ol class="list-reset flex items-center space-x-0">
                    <li>
                        <Link href="/" class="hover:underline text-slate-900 dark:text-slate-100">
                            {{ t('home') }}
                        </Link>
                    </li>
                    <li>
                        <span class="mx-1 text-slate-900 dark:text-slate-100">/</span>
                    </li>
                    <li class="text-slate-900 dark:text-slate-100">
                        {{ rubric.title }}
                    </li>
                </ol>
            </nav>

            <!-- Заголовок рубрики -->
            <h1 class="flex items-center justify-center my-4
                       text-center font-bolder text-xl
                       text-slate-900 dark:text-slate-100">
                <span v-if="rubric.icon" class="flex justify-center" v-html="rubric.icon"/>
                {{ rubric.title }}
            </h1>

            <!-- Турниры по статусу -->
            <section class="space-y-6 max-w-4xl mx-auto my-8">

                <!-- Запланированные -->
                <div v-if="scheduledTournaments.length">

                    <!-- Заголовок группы -->
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">
                        {{ t('typeSelectScheduled') }}
                    </h2>

                    <!-- Данные турнира -->
                    <ul class="space-y-4">
                        <li v-for="tournament in paginatedScheduled" :key="tournament.id"
                            class="text-sm text-slate-700 dark:text-slate-200 border-b pb-2 border-gray-200 dark:border-gray-700">

                                  <span class="font-medium block mb-1">
                                    {{ tournament.name }} — {{ tournament.tournament_date_time }}
                                  </span>

                            <div class="flex items-center space-x-4 pl-2">
                                <!-- 🔴 Красный угол -->
                                <div class="flex items-center space-x-2">
                                    <img v-if="tournament.fighter_red?.avatar"
                                         :src="`/storage/${tournament.fighter_red.avatar}`"
                                         alt="Red Fighter"
                                         class="w-8 h-8 object-cover rounded-full border border-gray-300 dark:border-gray-600"/>
                                    <span v-if="tournament.fighter_red">{{ tournament.fighter_red.nickname }}</span>
                                </div>

                                <span>vs</span>

                                <!-- 🔵 Синий угол -->
                                <div class="flex items-center space-x-2">
                                    <img v-if="tournament.fighter_blue?.avatar"
                                         :src="`/storage/${tournament.fighter_blue.avatar}`"
                                         alt="Blue Fighter"
                                         class="w-8 h-8 object-cover rounded-full border border-gray-300 dark:border-gray-600"/>
                                    <span v-if="tournament.fighter_blue">{{ tournament.fighter_blue.nickname }}</span>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <!-- Пагинация -->
                    <div class="flex items-center space-x-2 mt-4" v-if="totalScheduledPages > 1">
                        <button @click="goToScheduledPage(scheduledPage - 1)" :disabled="scheduledPage === 1"
                                class="px-2 py-1 text-sm border rounded disabled:opacity-50">←</button>

                        <input type="number" v-model.number="scheduledPage"
                               @change="goToScheduledPage(scheduledPage)"
                               :min="1"
                               :max="totalScheduledPages"
                               class="w-16 px-2 py-1 text-center border rounded"/>

                        <span>/ {{ totalScheduledPages }}</span>

                        <button @click="goToScheduledPage(scheduledPage + 1)" :disabled="scheduledPage === totalScheduledPages"
                                class="px-2 py-1 text-sm border rounded disabled:opacity-50">→</button>
                    </div>

                </div>

                <!-- Завершённые -->
                <div v-if="completedTournaments.length">

                    <!-- Заголовок группы -->
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">
                        {{ t('statusSelectCompleted') }}
                    </h2>

                    <!-- Данные турнира -->
                    <ul class="space-y-4">
                        <li v-for="tournament in paginatedCompleted" :key="tournament.id"
                            class="text-sm text-slate-700 dark:text-slate-200 border-b pb-2 border-gray-200 dark:border-gray-700">

                                  <span class="font-medium block mb-1">
                                    {{ tournament.name }} — {{ tournament.tournament_date_time }}
                                  </span>

                            <div class="flex items-center space-x-4 pl-2">
                                <!-- 🔴 Красный -->
                                <div class="flex items-center space-x-2">
                                    <img v-if="tournament.fighter_red?.avatar"
                                         :src="`/storage/${tournament.fighter_red.avatar}`"
                                         alt="Red Fighter"
                                         class="w-8 h-8 object-cover rounded-full border border-gray-300 dark:border-gray-600"/>
                                    <span v-if="tournament.fighter_red">{{ tournament.fighter_red.nickname }}</span>
                                </div>

                                <span>vs</span>

                                <!-- 🔵 Синий -->
                                <div class="flex items-center space-x-2">
                                    <img v-if="tournament.fighter_blue?.avatar"
                                         :src="`/storage/${tournament.fighter_blue.avatar}`"
                                         alt="Blue Fighter"
                                         class="w-8 h-8 object-cover rounded-full border border-gray-300 dark:border-gray-600"/>
                                    <span v-if="tournament.fighter_blue">{{ tournament.fighter_blue.nickname }}</span>
                                </div>

                                <!-- 🏆 Победитель -->
                                <template v-if="tournament.winner">
                                      <span class="ml-4 text-xs text-green-700 dark:text-green-400 font-medium">
                                        🏆 {{ tournament.winner.nickname }}
                                      </span>
                                </template>
                            </div>
                        </li>
                    </ul>

                    <!-- Пагинация -->
                    <div class="flex items-center space-x-2 mt-4" v-if="totalCompletedPages > 1">
                        <button @click="goToCompletedPage(completedPage - 1)" :disabled="completedPage === 1"
                                class="px-2 py-1 text-sm border rounded disabled:opacity-50">←</button>

                        <input type="number" v-model.number="completedPage"
                               @change="goToCompletedPage(completedPage)"
                               :min="1"
                               :max="totalCompletedPages"
                               class="w-16 px-2 py-1 text-center border rounded"/>

                        <span>/ {{ totalCompletedPages }}</span>

                        <button @click="goToCompletedPage(completedPage + 1)" :disabled="completedPage === totalCompletedPages"
                                class="px-2 py-1 text-sm border rounded disabled:opacity-50">→</button>
                    </div>

                </div>

            </section>

        </div>

    </DefaultLayout>
</template>
