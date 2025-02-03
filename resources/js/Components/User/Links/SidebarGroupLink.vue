<script setup>
import SidebarLink from '@/Components/User/Links/SidebarLink.vue'
import { defineProps } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
    expanded: {
        type: Boolean,
        default: false
    }
})

const { t } = useI18n()
</script>

<template>
    <SidebarLink :href="route('dashboard')"
                 :active="route().current('dashboard')"
                 :expanded="props.expanded">
        <template #default>
            <svg class="shrink-0 h-6 w-6" viewBox="0 0 24 24">
                <path class="fill-current text-blue-500"
                      d="M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0z"/>
                <path class="fill-current text-blue-600"
                      d="M12 3c-4.963 0-9 4.037-9 9s4.037 9 9 9 9-4.037 9-9-4.037-9-9-9z"/>
                <path class="fill-current text-blue-200"
                      d="M12 15c-1.654 0-3-1.346-3-3 0-.462.113-.894.3-1.285L6 6l4.714 3.301A2.973 2.973 0 0112 9c1.654 0 3 1.346 3 3s-1.346 3-3 3z"/>
            </svg>
        </template>
        <template #text>
            {{ t('dashboard') }}
        </template>
    </SidebarLink>
    <SidebarLink :href="route('rubrics.index')"
                 :active="route().current('rubrics.index')"
                 :expanded="props.expanded">
        <template #default>
            <svg class="shrink-0 h-6 w-6" viewBox="0 0 24 24">
                <circle class="fill-current text-blue-600" cx="16" cy="8" r="8"></circle>
                <circle class="fill-current text-blue-400" cx="8" cy="16" r="8"></circle>
            </svg>
        </template>
        <template #text>
            {{ t('rubrics') }}
        </template>
    </SidebarLink>
    <SidebarLink :href="route('articles.index')"
                 :active="route().current('articles.index')"
                 :expanded="props.expanded">
        <template #default>
            <svg class="shrink-0 h-6 w-6" viewBox="0 0 24 24">
                <path class="fill-current text-blue-600" d="M8 1v2H3v19h18V3h-5V1h7v23H1V1z"></path>
                <path class="fill-current text-blue-600" d="M1 1h22v23H1z"></path>
                <path class="fill-current text-blue-400" d="M15 10.586L16.414 12 11 17.414 7.586 14 9 12.586l2 2zM5 0h14v4H5z"></path>
            </svg>
        </template>
        <template #text>
            {{ t('posts') }}
        </template>
    </SidebarLink>
</template>
