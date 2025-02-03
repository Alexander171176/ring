<script setup>
import {defineProps} from 'vue';
import {useI18n} from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from "@/Components/Admin/Buttons/DefaultButton.vue";

const {t} = useI18n();

const props = defineProps({
    contacts: Array
});

</script>

<template>
    <AdminLayout :title="t('sectionContacts')">
        <template #header>
            <TitlePage>
                {{ t('sectionContacts') }}
            </TitlePage>
        </template>
        <div v-if="contacts.length > 0" class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div v-for="contact in contacts" :key="contact.id">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('contacts.edit', { contact: contact.id })">
                        <template #icon>
                            <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                <path
                                    d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z"></path>
                            </svg>
                        </template>
                        {{ t('editSection') }}
                    </DefaultButton>
                </div>
                <div class="bg-gray-100 dark:bg-slate-900 shadow-xl sm:rounded-lg">
                    <div class="flex justify-center">
                        <div class="w-full flex justify-center
                                    py-6 bg-white dark:bg-slate-700
                                    shadow-lg rounded-sm relative">
                            <section :class="`border border-slate-400 ${contact.tailwind}`"
                                     :style="{
                                     backgroundImage: contact.image ? `url(/storage/${contact.image})` : 'none',
                                     backgroundSize: 'contain',
                                     backgroundRepeat: 'no-repeat',
                                     backgroundPosition: 'center',
                                     backgroundClip: 'content-box',
                                     width: '800px',
                                     height: '420px',
                                     padding: '20px'
                                     }">
                                <h2 class="my-4 text-center text-2xl font-bold text-sky-600 dark:text-sky-500">
                                    {{ contact.title }}
                                </h2>
                                <strong v-html="contact.content" />
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-5 text-center text-slate-700 dark:text-slate-100">
                {{ t('noData') }}
            </div>
        </div>
    </AdminLayout>
</template>
