<script setup>
import { defineProps, ref } from 'vue';
import { transliterate } from '@/utils/transliteration';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';
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

const { t } = useI18n();

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
        translation.meta_keywords = truncateText(keywords, 160);
    }

    if (translation.short && !translation.meta_desc) {
        translation.meta_desc = truncateText(translation.short.replace(/(<([^>]+)>)/gi, ""), 255, true);
    }
};

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

        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-6xl mx-auto">
            <div class="p-5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 shadow-lg rounded-lg">

                <div class="flex justify-between mb-4">
                    <DefaultButton :href="route('rubrics.index')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current text-white mr-2" viewBox="0 0 16 16">
                                <path d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                            </svg>
                        </template>
                        {{ t('back') }}
                    </DefaultButton>
                </div>

                <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3">
                            <ActivityCheckbox v-model="form.activity"/>
                            <LabelCheckbox for="activity" :text="t('activity')"/>
                        </div>
                        <div class="flex items-center space-x-3">
                            <LabelInput for="sort" :value="t('sort')"/>
                            <InputNumber id="sort" type="number" v-model="form.sort" autocomplete="off"/>
                            <InputError :message="form.errors.sort"/>
                        </div>
                    </div>

                    <div>
                        <LabelInput for="meta_desc" :value="t('svg')"/>
                        <MetaDescTextarea v-model="form.icon" class="w-full"/>
                        <InputError :message="form.errors.icon"/>
                    </div>

                    <!-- Табы -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex space-x-4" role="tablist">
                            <button
                                v-for="(translation, index) in form.translations"
                                :key="translation.locale"
                                @click.prevent="activeTab = translation.locale"
                                :class="['px-4 py-2 font-medium text-sm rounded-t-md transition',
                                         activeTab === translation.locale ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400']"
                                role="tab"
                                :aria-selected="activeTab === translation.locale">
                                {{ translation.locale.toUpperCase() }}
                            </button>
                        </nav>
                    </div>

                    <!-- Контент активного таба -->
                    <div v-for="(translation, index) in form.translations" v-show="activeTab === translation.locale" :key="translation.locale" class="mt-4">
                        <LabelInput :value="t('rubricTitle')"/>
                        <InputText v-model="translation.title" required autofocus/>
                        <InputError :message="form.errors[`translations.${index}.title`]"/>

                        <LabelInput :value="t('rubricUrl')"/>
                        <InputText v-model="translation.url" required @focus="handleUrlInputFocus(translation)"/>
                        <InputError :message="form.errors[`translations.${index}.url`]"/>

                        <LabelInput :value="t('shortDescription')"/>
                        <MetaDescTextarea v-model="translation.short"/>
                        <InputError :message="form.errors[`translations.${index}.short`]"/>

                        <LabelInput :value="t('description')"/>
                        <CKEditor v-model="translation.description"/>
                        <InputError :message="form.errors[`translations.${index}.description`]"/>
                    </div>

                    <div class="flex justify-end mt-4">
                        <MetatagsButton @click.prevent="generateMetaFields">
                            <template #icon>
                                <svg class="w-4 h-4 fill-current text-white mr-2" viewBox="0 0 16 16">
                                    <path d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                                </svg>
                            </template>
                            {{ t('generateMetaTags') }}
                        </MetatagsButton>
                    </div>

                    <div class="flex justify-end mt-4">
                        <PrimaryButton @click.prevent="submitForm">
                            {{ t('save') }}
                        </PrimaryButton>
                    </div>

                </form>
            </div>
        </div>
    </AdminLayout>
</template>
