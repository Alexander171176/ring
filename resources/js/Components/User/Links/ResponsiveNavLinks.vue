<script setup>
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
    <div class="pt-2 pb-3 space-y-1">
        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
            {{ t('dashboard') }}
        </ResponsiveNavLink>
        <ResponsiveNavLink :href="route('rubrics.index')" :active="route().current('rubrics.index')">
            {{ t('rubrics') }}
        </ResponsiveNavLink>
        <ResponsiveNavLink :href="route('articles.index')" :active="route().current('articles.index')">
            {{ t('posts') }}
        </ResponsiveNavLink>
    </div>
</template>
