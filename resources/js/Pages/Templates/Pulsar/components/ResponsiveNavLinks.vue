<script setup>
import ResponsiveNavLink from '@/Pages/Templates/Default/components/ResponsiveNavLink.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
    <ResponsiveNavLink v-if="$page.props.auth.user" :href="route('dashboard')" :active="route().current('dashboard')">
        {{ t('dashboard') }}
    </ResponsiveNavLink>
    <ResponsiveNavLink v-if="$page.props.auth.user" :href="route('admin')" :active="route().current('dashboard')">
        {{ t('adminPanel') }}
    </ResponsiveNavLink>
</template>
