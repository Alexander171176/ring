<script setup>
import { computed } from 'vue';
import NewsSection from "@/Components/Public/Default/Section/NewsSection.vue";
import InterviewSection from "@/Components/Public/Default/Section/InterviewSection.vue";
import DefaultSection from "@/Components/Public/Default/Section/DefaultSection.vue";
import ScheduleSection from "@/Components/Public/Default/Section/ScheduleSection.vue";

const props = defineProps({
    sections: Array,
    appUrl: String
});

const isNewsSection = (title) => {
    const lower = title?.toLowerCase();
    return [
        'новости', 'жаңалықтар', 'news',
        'обзоры', 'шолу', 'reviews',
    ].includes(lower);
};

const isInterviewSection = (title) => {
    const lower = title?.toLowerCase();
    return [
        'расписание', 'жаднама', 'schedule',
        'интервью', 'сұхбат', 'interview',
    ].includes(lower);
};

const isScheduleSection = (title) => {
    const lower = title?.toLowerCase();
    return ['результаты', 'нәтижелер', 'results',].includes(lower);
};

const isHiddenSection = (title) => {
    const lower = title?.toLowerCase();
    return [
        'расписание', 'жаднама', 'schedule',
        'open ring', 'wbss', 'рейтинг р4р',
        'видео', 'бейне', 'video'
    ].includes(lower);
};

const filteredSections = computed(() => {
    return props.sections.filter(s => s.title && !isHiddenSection(s.title));
});

// onMounted(() => {
//     console.log('Section titles:', props.sections.map(r => r.title));
// });
</script>

<template>
    <div v-for="section in filteredSections" :key="section.id" class="mt-6">

        <NewsSection
            v-if="isNewsSection(section.title)"
            :section="section"
            :app-url="appUrl"
        />

        <InterviewSection
            v-else-if="isInterviewSection(section.title)"
            :section="section"
            :app-url="appUrl"
        />

        <ScheduleSection
            v-else-if="isScheduleSection(section.title)"
            :section="section"
            :app-url="appUrl"
        />

        <DefaultSection
            v-else
            :section="section"
            :app-url="appUrl"
        />
    </div>
</template>
