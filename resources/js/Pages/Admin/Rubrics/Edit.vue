<script setup>
import {defineProps, ref, computed} from 'vue';
import {transliterate} from '@/utils/transliteration';
import {useI18n} from 'vue-i18n';
import {useForm} from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import MetatagsButton from '@/Components/Admin/Buttons/MetatagsButton.vue';
import InputError from '@/Components/Admin/Input/InputError.vue';
import CKEditor from '@/Components/Admin/CKEditor/CKEditor.vue';
import MetaDescTextarea from '@/Components/Admin/Textarea/MetaDescTextarea.vue';
import LabelCheckbox from '@/Components/Admin/Checkbox/LabelCheckbox.vue';
import PrimaryButton from '@/Components/Admin/Buttons/PrimaryButton.vue';
import ActivityCheckbox from '@/Components/Admin/Checkbox/ActivityCheckbox.vue';
import InputNumber from '@/Components/Admin/Input/InputNumber.vue';
import InputText from '@/Components/Admin/Input/InputText.vue';
import LabelInput from '@/Components/Admin/Input/LabelInput.vue';

const {t} = useI18n();

// Пропсы
const props = defineProps({
    rubric: {
        type: Object,
        required: true
    },
    translations: {
        type: Array,
        required: true
    }
});

// Форма
const form = useForm({
    _method: 'PUT',
    sort: props.rubric.sort ?? 0,
    icon: props.rubric.icon ?? '',
    activity: Boolean(props.rubric.activity ?? false),
    translations: props.translations.map((translation) => ({
        locale: translation.locale,
        title: translation.title,
        url: translation.url,
        short: translation.short,
        description: translation.description,
        meta_title: translation.meta_title,
        meta_keywords: translation.meta_keywords,
        meta_desc: translation.meta_desc
    }))
});

// Активный таб (устанавливается первый доступный язык)
const activeTab = ref(form.translations.length > 0 ? form.translations[0].locale : null);

// Автоматическое заполнение поля URL
const handleUrlInputFocus = (translation) => {
    if (translation.title) {
        translation.url = transliterate(translation.title.toLowerCase());
    }
};

// Генерация мета-тегов
const truncateText = (text, maxLength, addEllipsis = false) => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    const truncated = text.substr(0, text.lastIndexOf(' ', maxLength));
    return addEllipsis ? `${truncated}...` : truncated;
};

const generateMetaFields = (translation) => {
    if (translation.title && !translation.meta_title) {
        translation.meta_title = truncateText(translation.title, 160);
    }

    if (translation.title && !translation.meta_keywords) {
        const keywords = translation.title.split(' ').join(', ');
        translation.meta_keywords = truncateText(keywords, 255);
    }

    if (translation.short && !translation.meta_desc) {
        translation.meta_desc = truncateText(translation.short.replace(/(<([^>]+)>)/gi, ""), 200, true);
    }
};

// Определяем текущий активный перевод
const activeTabTranslation = computed(() => {
    return form.translations.find(t => t.locale === activeTab.value);
});

// Метод сохранения
const submitForm = async () => {
    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
    }));

    form.post(route('rubrics.update', props.rubric.id), {
        errorBag: 'editRubric',
        preserveScroll: true
    });
};

</script>

<template>
    <AdminLayout :title="t('editRubric')">
        <template #header>
            <TitlePage>
                {{ t('editRubric') }}: {{ props.rubric.translations[0]?.title }}
            </TitlePage>
        </template>

        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">

            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">

                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('rubrics.index')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current text-white mr-2" viewBox="0 0 16 16">
                                <path
                                    d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                            </svg>
                        </template>
                        {{ t('back') }}
                    </DefaultButton>
                </div>

                <form @submit.prevent="submitForm" enctype="multipart/form-data" class="p-3 w-full">

                    <div class="mb-3 flex items-center">
                        <div class="flex justify-between w-full">
                            <div class="flex flex-row items-center">
                                <ActivityCheckbox v-model="form.activity"/>
                                <LabelCheckbox for="activity" :text="t('activity')"/>
                            </div>
                        </div>
                        <div class="flex flex-row items-center">
                            <LabelInput for="sort" :value="t('sort')" class="mr-3"/>
                            <InputNumber
                                id="sort"
                                type="number"
                                v-model="form.sort"
                                autocomplete="sort"
                            />
                            <InputError class="mt-2" :message="form.errors.sort"/>
                        </div>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="meta_desc" :value="t('svg')"/>
                        <MetaDescTextarea v-model="form.icon" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.icon"/>
                    </div>

                    <!-- Табы -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex justify-center space-x-4" role="tablist">
                            <button
                                v-for="(translation, index) in form.translations"
                                :key="translation.locale"
                                @click.prevent="activeTab = translation.locale"
                                :class="['px-4 py-2 font-medium text-sm rounded-t-md transition',
                                         activeTab === translation.locale ? 'text-orange-400 border-b-2 border-orange-400 dark:border-orange-200 dark:text-orange-200' : 'text-teal-600 dark:text-teal-300']"
                                role="tab"
                                :aria-selected="activeTab === translation.locale">
                                {{ translation.locale.toUpperCase() }}
                            </button>
                        </nav>
                    </div>

                    <!-- Контент активного таба -->
                    <div v-for="(translation, index) in form.translations" v-show="activeTab === translation.locale"
                         :key="translation.locale" class="mt-4">

                        <div class="mb-3 flex flex-col items-start">
                            <LabelInput :value="t('rubricTitle')"/>
                            <InputText v-model="translation.title" required autofocus/>
                            <InputError class="mt-2" :message="form.errors[`translations.${index}.title`]"/>
                        </div>

                        <div class="mb-3 flex flex-col items-start">
                            <LabelInput :value="t('rubricUrl')"/>
                            <InputText v-model="translation.url" required @focus="handleUrlInputFocus(translation)"/>
                            <InputError class="mt-2" :message="form.errors[`translations.${index}.url`]"/>
                        </div>

                        <div class="mb-3 flex flex-col items-start">
                            <div class="flex justify-between w-full">
                                <LabelInput :value="t('shortDescription')"/>
                                <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                    {{ translation.short.length }} / 255 {{ t('characters') }}
                                </div>
                            </div>
                            <MetaDescTextarea v-model="translation.short" maxlength="255" class="w-full h-16" />
                            <InputError class="mt-2" :message="form.errors[`translations.${index}.short`]"/>
                        </div>

                        <div class="mb-3 flex flex-col items-start">
                            <LabelInput :value="t('content')"/>
                            <CKEditor v-model="translation.description" class="w-full"/>
                            <InputError class="mt-2" :message="form.errors[`translations.${index}.description`]"/>
                        </div>

                        <div class="mb-3 flex flex-col items-start">
                            <div class="flex justify-between w-full">
                                <LabelInput :value="t('metaTitle')"/>
                                <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                    {{ translation.meta_title.length }} / 160 {{ t('characters') }}
                                </div>
                            </div>
                            <InputText v-model="translation.meta_title" maxlength="160"/>
                            <InputError class="mt-2" :message="form.errors[`translations.${index}.meta_title`]"/>
                        </div>

                        <div class="mb-3 flex flex-col items-start">
                            <div class="flex justify-between w-full">
                                <LabelInput :value="t('metaKeywords')"/>
                                <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                    {{ translation.meta_keywords.length }} / 255 {{ t('characters') }}
                                </div>
                            </div>
                            <MetaDescTextarea v-model="translation.meta_keywords" maxlength="255" class="w-full h-16" />
                            <InputError class="mt-2" :message="form.errors[`translations.${index}.meta_keywords`]"/>
                        </div>

                        <div class="mb-3 flex flex-col items-start">
                            <div class="flex justify-between w-full">
                                <LabelInput :value="t('metaDescription')"/>
                                <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                    {{ translation.meta_desc.length }} / 200 {{ t('characters') }}
                                </div>
                            </div>
                            <MetaDescTextarea v-model="translation.meta_desc" maxlength="200" class="w-full h-16" />
                            <InputError class="mt-2" :message="form.errors[`translations.${index}.meta_desc`]"/>
                        </div>

                    </div>

                    <div class="flex justify-end mt-4">
                        <MetatagsButton @click.prevent="generateMetaFields(activeTabTranslation)">
                            <template #icon>
                                <svg class="w-4 h-4 fill-current text-white shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path d="M13 7h2v6a1 1 0 01-1 1H4v2l-4-3 4-3v2h9V7zM3 9H1V3a1 1 0 011-1h10V0l4 3-4 3V4H3v5z"></path>
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
                                    <path d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
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
