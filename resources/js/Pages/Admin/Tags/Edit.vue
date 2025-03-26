<script setup>
import { useI18n } from 'vue-i18n';
import {transliterate} from '@/utils/transliteration';
import { defineProps } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import LabelInput from '@/Components/Admin/Input/LabelInput.vue';
import InputText from '@/Components/Admin/Input/InputText.vue';
import InputError from '@/Components/Admin/Input/InputError.vue';
import PrimaryButton from '@/Components/Admin/Buttons/PrimaryButton.vue';
import SelectLocale from "@/Components/Admin/Select/SelectLocale.vue";

const { t } = useI18n();

const props = defineProps({
    tag: {
        type: Object,
        required: true
    },
});

const form = useForm({
    _method: 'PUT',
    name: props.tag?.name,
    locale: props.tag.locale ?? '',
    slug: props.tag.slug ?? '',
});

// автоматическое заполнение поля slug
const handleUrlInputFocus = () => {
    if (form.name) {
        form.slug = transliterate(form.name.toLowerCase());
    }
};

const submit = () => {
    form.put(route('tags.update', props.tag.id), {
        errorBag: 'editTag',
        preserveScroll: true,
        onSuccess: () => {
            // console.log("Форма успешно отправлена.");
        },
        onError: (errors) => {
            console.error("Не удалось отправить форму:", errors);
        }
    });
};
</script>

<template>
    <AdminLayout :title="t('editTag')">
        <template #header>
            <TitlePage>
                {{ t('editTag') }} ID:{{ props.tag.id }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-sm mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700
                        border border-blue-400 dark:border-blue-200
                        shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка назад -->
                    <DefaultButton :href="route('tags.index')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                <path
                                    d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                            </svg>
                        </template>
                        {{ t('back') }}
                    </DefaultButton>

                    <!-- Right: Actions -->
                    <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                        <!-- Datepicker built with flatpickr -->
                    </div>
                </div>
                <form @submit.prevent="submit" class="p-3 w-full">

                    <div class="mb-3 flex justify-start items-center">

                        <!-- Локализация -->
                        <div class="flex flex-row items-center w-auto">
                            <SelectLocale v-model="form.locale" :errorMessage="form.errors.locale"/>
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.locale"/>
                        </div>

                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="name">
                            <span class="text-red-500 dark:text-red-300 font-semibold">*</span> {{ t('name') }}
                        </LabelInput>
                        <InputText
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Поле slug -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="slug">
                            <span class="text-red-500 dark:text-red-300 font-semibold">*</span> {{ t('url') }}
                        </LabelInput>
                        <InputText
                            id="slug"
                            type="text"
                            v-model="form.slug"
                            required
                            autocomplete="slug"
                            @focus="handleUrlInputFocus"
                        />
                        <InputError class="mt-2" :message="form.errors.slug"/>
                    </div>

                    <div class="flex items-center justify-center mt-4">
                        <DefaultButton :href="route('tags.index')" class="mb-3">
                            <template #icon>
                                <!-- SVG -->
                                <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                                </svg>
                            </template>
                            {{ t('back') }}
                        </DefaultButton>
                        <PrimaryButton class="ms-4"
                                       :class="{ 'opacity-25': form.processing }"
                                       :disabled="form.processing">
                            <template #icon>
                                <svg class="w-4 h-4 fill-current text-slate-100" viewBox="0 0 16 16">
                                    <path
                                        d="M14.3 2.3L5 11.6 1.7 8.3c-.4-.4-1-.4-1.4 0-.4.4-.4 1 0 1.4l4 4c.2.2.4.3.7.3.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4-.4-.4-1-.4-1.4 0z"></path>
                                </svg>
                            </template>
                            {{ t('save') }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
