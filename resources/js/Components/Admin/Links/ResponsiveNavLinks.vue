<script setup>
import { useI18n } from 'vue-i18n';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const { t } = useI18n();
</script>

<template>
    <div class="pt-2 pb-24 space-y-1">
        <ResponsiveNavLink :href="route('admin')" :active="route().current('admin')">{{ t('adminPanel') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('users.index')" :active="route().current('users.index')">{{ t('users') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('roles.index')" :active="route().current('roles.index')">{{ t('roles') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('permissions.index')" :active="route().current('permissions.index')">{{ t('permissions') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('rubrics.index')" :active="route().current('rubrics.index')">{{ t('rubrics') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('sections.index')" :active="route().current('sections.index')">{{ t('sections') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('articles.index')" :active="route().current('articles.index')">{{ t('posts') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('tags.index')" :active="route().current('tags.index')">{{ t('tags') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('comments.index')" :active="route().current('comments.index')">{{ t('comments') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('banners.index')" :active="route().current('banners.index')">{{ t('banners') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('reports.index')" :active="route().current('reports.index')">{{ t('reports') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('charts.index')" :active="route().current('charts.index')">{{ t('charts') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('settings.index')" :active="route().current('settings.index')">{{ t('settings') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('parameters.index')" :active="route().current('parameters.index')">{{ t('parameters') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('components.index')" :active="route().current('components.index')">{{ t('components') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('diagrams.index')" :active="route().current('diagrams.index')">{{ t('diagrams') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('plugins.index')" :active="route().current('plugins.index')">{{ t('plugins') }}</ResponsiveNavLink>
        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">{{ t('dashboard') }}</ResponsiveNavLink>
    </div>
</template>
