<script setup>
import { defineProps, ref, onMounted } from 'vue';
import { transliterate } from '@/utils/transliteration';
import {useI18n} from 'vue-i18n';
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
import SimpleImageUpload from '@/Components/Admin/Input/SimpleImageUpload.vue';

const {t} = useI18n();

// пропсы рубрик
const props = defineProps({
    tutorial: {
        type: Object,
        required: true
    }
});

// данные полей формы
const form = useForm({
    _method: 'PUT',
    sort: props.tutorial.sort ?? 0,
    icon: props.tutorial.icon ?? '',
    title: props.tutorial.title ?? '',
    url: props.tutorial.url ?? '',
    short: props.tutorial.short ?? '',
    description: props.tutorial.description ?? '',
    image_url: null,
    seo_title: props.tutorial.seo_title ?? '',
    seo_alt: props.tutorial.seo_alt ?? '',
    meta_title: props.tutorial.meta_title ?? '',
    meta_keywords: props.tutorial.meta_keywords ?? '',
    meta_desc: props.tutorial.meta_desc ?? '',
    activity: Boolean(props.tutorial.activity ?? false),
});

// автоматическое заполнение поля url
const handleUrlInputFocus = () => {
    if (form.title) {
        form.url = transliterate(form.title.toLowerCase());
    }
};

// автоматическое заполнение поля seo_alt
const handleSeoAltFocus = () => {
    if (form.seo_title && !form.seo_alt) {
        form.seo_alt = form.seo_title;
    }
};

// автоматическая генерация мета-тегов
const truncateText = (text, maxLength, addEllipsis = false) => {
    if (!text) return ''; // Защита от пустых значений
    if (text.length <= maxLength) return text;
    const truncated = text.substr(0, text.lastIndexOf(' ', maxLength));
    return addEllipsis ? `${truncated}...` : truncated;
};

const generateMetaFields = () => {
    if (form.title && !form.meta_title) {
        form.meta_title = truncateText(form.title, 160);
    }

    if (form.title && !form.meta_keywords) {
        const keywords = form.title.split(' ').join(', ');
        form.meta_keywords = truncateText(keywords, 160);
    }

    if (form.short && !form.meta_desc) {
        form.meta_desc = truncateText(form.short.replace(/(<([^>]+)>)/gi, ""), 255, true);
    }
};

// Изображение
const imagePreview = ref(props.tutorial.image_url ? props.tutorial.image_url : null); // Путь уже полный, без /storage/
const imageFile = ref(null);

const handleImageSelected = (file) => {
    imageFile.value = file;
};

// метод сохранения
const submitForm = async () => {
    if (imageFile.value) {
        form.image_url = imageFile.value;
    }

    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
    }));

    // console.log("Форма для отправки заполнена:", form.data());

    form.post(route('tutorials.update', props.tutorial.id), {
        errorBag: 'editTutorial',
        preserveScroll: true,
        onSuccess: () => {
            // console.log("Форма успешно обновлена.");
        },
        onError: (errors) => {
            // console.error("Не удалось обновить форму:", errors);
        }
    });
};

// путь к изображению
onMounted(() => {
    imagePreview.value = props.tutorial.image_url ? props.tutorial.image_url : null;
});

</script>

<template>
    <AdminLayout :title="t('editTutorial')">
        <template #header>
            <TitlePage>
                {{ t('editTutorial') }}: {{ props.tutorial.title }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка назад -->
                    <DefaultButton :href="route('tutorials.index')">
                        <template #icon>
                            <!-- SVG -->
                            <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
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

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="title" :value="t('tutorialTitle')" />
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
                        <LabelInput for="url" :value="t('url')"/>
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
                        <div class="flex justify-between w-full">
                            <LabelInput for="meta_desc" :value="t('shortDescription')"/>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.short.length }} / 255 {{ t('characters') }}
                            </div>
                        </div>
                        <MetaDescTextarea v-model="form.short" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.short"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="description" :value="t('description')"/>
                        <CKEditor v-model="form.description" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.description"/>
                    </div>

                    <!-- Загрузка главного изображения -->
                    <div class="p-5 mb-3 flex flex-col md:flex-row
                                justify-center bg-white dark:bg-slate-800 shadow-md
                                border border-slate-300 space-y-6 md:space-y-0 md:gap-6">

                        <!-- Загрузка и Превью изображения -->
                        <div class="md:w-1/2 w-full flex justify-start">
                            <div class="w-full max-w-md">
                                <LabelInput for="image_url" :value="t('currentImage')"
                                            class="text-slate-800 dark:text-slate-100"/>
                                <SimpleImageUpload @fileSelected="handleImageSelected" />
                                <InputError :message="form.errors.image_url" />
                                <div v-if="imagePreview" class="mt-2 w-full">
                                    <img :src="imagePreview" :alt="t('currentImage')" class="w-full h-fit object-cover">
                                </div>
                            </div>
                        </div>

                        <!-- SEO поля -->
                        <div class="md:w-1/2 w-full space-y-1">
                            <div class="flex flex-col items-start space-y-2">

                                <LabelInput :value="t('metaTagsImage')"
                                            class="text-slate-800 dark:text-slate-100"/>

                                <div class="w-full flex flex-col items-start">
                                    <LabelInput for="seo_title" :value="t('seoTitle')" />
                                    <InputText
                                        id="seo_title"
                                        type="text"
                                        v-model="form.seo_title"
                                        autocomplete="url"
                                    />
                                    <InputError class="mt-2" :message="form.errors.seo_title" />
                                </div>

                                <div class="w-full flex flex-col items-start">
                                    <LabelInput for="seo_alt" :value="t('seoAlt')" />
                                    <InputText
                                        id="seo_alt"
                                        type="text"
                                        v-model="form.seo_alt"
                                        autocomplete="url"
                                        @focus="handleSeoAltFocus"
                                    />
                                    <InputError class="mt-2" :message="form.errors.seo_alt" />
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="meta_title" :value="t('metaTitle')"/>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
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
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
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
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.meta_desc.length }} / 255 {{ t('characters') }}
                            </div>
                        </div>
                        <MetaDescTextarea v-model="form.meta_desc" class="w-full" />
                        <InputError class="mt-2" :message="form.errors.meta_desc"/>
                    </div>

                    <div class="flex justify-end mt-4">
                        <MetatagsButton @click.prevent="generateMetaFields">
                            <template #icon>
                                <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path
                                        d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                                </svg>
                            </template>
                            {{ t('generateMetaTags') }}
                        </MetatagsButton>
                    </div>

                    <div class="flex items-center justify-center mt-4">
                        <DefaultButton :href="route('tutorials.index')" class="mb-3">
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
