<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import CreateTeamForm from '@/Pages/Teams/Partials/CreateTeamForm.vue'
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
    <AppLayout :title="t('createTeam')">
        <template #header>
            <TitlePage>
                {{ t('createTeam') }}
            </TitlePage>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <CreateTeamForm />
            </div>
        </div>
    </AppLayout>
</template>
