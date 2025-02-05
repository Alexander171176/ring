<script setup>
import {computed} from 'vue';
import {Link, usePage} from '@inertiajs/vue3';
import {useI18n} from 'vue-i18n';
import {sidebarIcons} from '@/utils/sidebarIcons';

const props = defineProps({
    id: String,
    expanded: Boolean
});

const {t} = useI18n();
const {props: pageProps} = usePage();

const linkInfo = {
    admin: {label: t('adminPanel'), route: 'admin'},
    apiTokens: {label: t('apiTokens'), route: 'api-tokens.index'},
    teamSettings: {label: t('teamSettings'), route: 'teams.show', params: {team: pageProps.auth.user.current_team}},
    users: {label: t('users'), route: 'users.index'},
    roles: {label: t('roles'), route: 'roles.index'},
    permissions: {label: t('permissions'), route: 'permissions.index'},
    rubrics: {label: t('rubrics'), route: 'rubrics.index'},
    articles: {label: t('posts'), route: 'articles.index'},
    comments: {label: t('comments'), route: 'comments.index'},
    reports: {label: t('reports'), route: 'reports.index'},
    charts: {label: t('charts'), route: 'charts.index'},
    diagrams: {label: t('diagrams'), route: 'diagrams.index'},
    settings: {label: t('settings'), route: 'settings.index'},
    parameters: {label: t('parameters'), route: 'parameters.index'},
    components: {label: t('components'), route: 'components.index'},
    plugins: {label: t('plugins'), route: 'plugins.index'},
};

const link = computed(() => linkInfo[props.id]);

const svgContent = computed(() => sidebarIcons[props.id]);

const classes = computed(() => {
    return link.value.route === route().current()
        ? 'flex items-center px-1 pt-1 text-sm font-medium leading-3 text-yellow-100 focus:outline-none transition duration-150 ease-in-out'
        : 'flex items-center px-1 pt-1 text-sm font-medium leading-3 text-slate-300 hover:text-yellow-100 focus:outline-none focus:text-yellow-100 transition duration-150 ease-in-out';
});

const containerClasses = computed(() => {
    return props.expanded ? 'mb-1' : 'mb-3';
});

const textClasses = computed(() => {
    return props.expanded ? 'ml-3 opacity-100' : 'ml-3 opacity-0 whitespace-nowrap overflow-hidden';
});
</script>

<template>
    <li :class="containerClasses">
        <Link :href="route(link.route, link.params || {})" :class="classes">
            <svg class="shrink-0 h-4 w-4" viewBox="0 0 24 24" v-html="svgContent"></svg>
            <span class="text-sm font-medium transition-opacity duration-200 max-w-full" :class="textClasses">
                {{ link.label }}
            </span>
        </Link>
    </li>
</template>
