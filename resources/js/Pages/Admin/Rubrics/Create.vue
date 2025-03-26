<script setup>
import {useI18n} from 'vue-i18n';
import {transliterate} from '@/utils/transliteration';
import {useForm} from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import PrimaryButton from '@/Components/Admin/Buttons/PrimaryButton.vue';
import MetatagsButton from '@/Components/Admin/Buttons/MetatagsButton.vue';
import LabelCheckbox from '@/Components/Admin/Checkbox/LabelCheckbox.vue';
import ActivityCheckbox from '@/Components/Admin/Checkbox/ActivityCheckbox.vue';
import MetaDescTextarea from '@/Components/Admin/Textarea/MetaDescTextarea.vue';
import InputNumber from '@/Components/Admin/Input/InputNumber.vue';
import LabelInput from '@/Components/Admin/Input/LabelInput.vue';
import InputText from '@/Components/Admin/Input/InputText.vue';
import InputError from '@/Components/Admin/Input/InputError.vue';
import CKEditor from "@/Components/Admin/CKEditor/CKEditor.vue";
import SelectLocale from "@/Components/Admin/Select/SelectLocale.vue";

const {t} = useI18n();

// пустая форма
const form = useForm({
    sort: '0',
    icon: '',
    locale: '',
    title: '',
    url: '',
    short: '',
    meta_title: '',
    meta_keywords: '',
    meta_desc: '',
    activity: false,
});

// автоматическое заполнение поля url
const handleUrlInputFocus = () => {
    if (form.title) {
        form.url = transliterate(form.title.toLowerCase());
    }
};

// автоматическая генерация мета-тегов
const truncateText = (text, maxLength, addEllipsis = false) => {
    if (!text) return ''; // Защита от пустых значений
    if (text.length <= maxLength) return text;
    const lastSpaceIndex = text.lastIndexOf(' ', maxLength);
    const truncated = lastSpaceIndex === -1 ? text.substr(0, maxLength) : text.substr(0, lastSpaceIndex);
    return addEllipsis ? `${truncated}...` : truncated;
};

const generateMetaFields = () => {
    if (form.title && !form.meta_title) {
        form.meta_title = truncateText(form.title, 160);
    }

    if (!form.meta_keywords && form.title) {
        form.meta_keywords = truncateText(form.title, 200); // Используем title вместо tags
    }

    if (form.short && !form.meta_desc) {
        form.meta_desc = truncateText(form.short.replace(/(<([^>]+)>)/gi, ""), 255, true);
    }
};

// метод сохранения
const submitForm = () => {

    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
    }));

    // console.log("Форма для отправки заполнена:", form.data());

    form.post(route('rubrics.store'), {
        errorBag: 'createRubric',
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
    <AdminLayout :title="t('createRubric')">
        <template #header>
            <TitlePage>
                {{ t('createRubric') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700
                        border border-blue-400 dark:border-blue-200
                        shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка назад -->
                    <DefaultButton :href="route('rubrics.index')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                <path d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                            </svg>
                        </template>
                        {{ t('back') }}
                    </DefaultButton>

                    <!-- Right: Actions -->
                    <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                        <!-- Datepicker built with flatpickr -->
                    </div>
                </div>
                <form @submit.prevent="submitForm" enctype="multipart/form-data" class="p-3 w-full">

                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <!-- Активность -->
                        <div class="flex flex-row items-center gap-2">
                            <ActivityCheckbox v-model="form.activity"/>
                            <LabelCheckbox for="activity" :text="t('activity')" class="text-sm h-8 flex items-center"/>
                        </div>

                        <!-- Локализация -->
                        <div class="flex flex-row items-center gap-2 w-auto">
                            <SelectLocale v-model="form.locale" :errorMessage="form.errors.locale"/>
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.locale"/>
                        </div>

                        <!-- Сортировка -->
                        <div class="flex flex-row items-center gap-2">
                            <div class="h-8 flex items-center">
                                <LabelInput for="sort" :value="t('sort')" class="text-sm"/>
                            </div>
                            <InputNumber
                                id="sort"
                                type="number"
                                v-model="form.sort"
                                autocomplete="sort"
                                class="w-full lg:w-28"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.sort"/>
                        </div>

                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="meta_desc" :value="t('svg')"/>
                        <MetaDescTextarea v-model="form.icon" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.icon"/>
                    </div>

                    <!-- Поле title -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="title">
                            <span class="text-red-500 dark:text-red-300 font-semibold">*</span> {{ t('rubricTitle') }}
                        </LabelInput>
                        <InputText
                            id="title"
                            type="text"
                            v-model="form.title"
                            required
                            autocomplete="title"
                        />
                        <InputError class="mt-2" :message="form.errors.title"/>
                    </div>

                    <!-- Поле url -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="url">
                            <span class="text-red-500 dark:text-red-300 font-semibold">*</span> {{ t('rubricUrl') }}
                        </LabelInput>
                        <InputText
                            id="url"
                            type="text"
                            v-model="form.url"
                            required
                            autocomplete="url"
                            @focus="handleUrlInputFocus"
                        />
                        <InputError class="mt-2" :message="form.errors.url"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="short" :value="t('description')"/>
                        <CKEditor v-model="form.short" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.short"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="meta_title" :value="t('metaTitle')"/>
                            <div class="text-xs text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.meta_title.length }} / 255 {{ t('characters') }}
                            </div>
                        </div>
                        <InputText
                            id="meta_title"
                            type="text"
                            v-model="form.meta_title"
                            maxlength="255"
                            autocomplete="url"
                        />
                        <InputError class="mt-2" :message="form.errors.meta_title"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="meta_keywords" :value="t('metaKeywords')"/>
                            <div class="text-xs text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.meta_keywords.length }} / 255 {{ t('characters') }}
                            </div>
                        </div>
                        <InputText
                            id="meta_keywords"
                            type="text"
                            v-model="form.meta_keywords"
                            maxlength="255"
                            autocomplete="url"
                        />
                        <InputError class="mt-2" :message="form.errors.meta_keywords"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="meta_desc" :value="t('metaDescription')"/>
                            <div class="text-xs text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.meta_desc.length }} / 255 {{ t('characters') }}
                            </div>
                        </div>
                        <MetaDescTextarea v-model="form.meta_desc" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.meta_desc"/>
                    </div>

                    <div class="flex justify-end mt-4">
                        <MetatagsButton @click.prevent="generateMetaFields">
                            <template #icon>
                                <svg class="w-4 h-4 fill-current text-slate-600 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path
                                        d="M13 7h2v6a1 1 0 01-1 1H4v2l-4-3 4-3v2h9V7zM3 9H1V3a1 1 0 011-1h10V0l4 3-4 3V4H3v5z"></path>
                                </svg>
                            </template>
                            {{ t('generateMetaTags') }}
                        </MetatagsButton>
                    </div>

                    <div class="flex items-center justify-center mt-4">
                        <DefaultButton :href="route('rubrics.index')" class="mb-3">
                            <template #icon>
                                <!-- SVG -->
                                <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path
                                        d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                                </svg>
                            </template>
                            {{ t('back') }}
                        </DefaultButton>
                        <PrimaryButton class="ms-4 mb-0" :class="{ 'opacity-25': form.processing }"
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
