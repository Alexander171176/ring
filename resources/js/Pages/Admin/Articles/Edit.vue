<script setup>
import { ref, defineProps, onMounted, watch } from 'vue';
import { transliterate } from '@/utils/transliteration';
import {useI18n} from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import PrimaryButton from '@/Components/Admin/Buttons/PrimaryButton.vue';
import MetatagsButton from '@/Components/Admin/Buttons/MetatagsButton.vue';
import LabelCheckbox from '@/Components/Admin/Checkbox/LabelCheckbox.vue';
import ActivityCheckbox from '@/Components/Admin/Checkbox/ActivityCheckbox.vue';
import CKEditor from '@/Components/Admin/CKEditor/CKEditor.vue';
import MetaDescTextarea from '@/Components/Admin/Textarea/MetaDescTextarea.vue';
import InputNumber from '@/Components/Admin/Input/InputNumber.vue';
import LabelInput from '@/Components/Admin/Input/LabelInput.vue';
import InputText from '@/Components/Admin/Input/InputText.vue';
import InputError from '@/Components/Admin/Input/InputError.vue';
import SimpleImageUpload from '@/Components/Admin/Input/SimpleImageUpload.vue';
import VueMultiselect from 'vue-multiselect';

const {t} = useI18n();

// пропсы статей и рубрик
const props = defineProps({
    article: {
        type: Object,
        required: true
    },
    rubrics: Array,
});

// данные полей формы
const form = useForm({
    _method: 'PUT',
    sort: props.article.sort ?? 0,
    title: props.article.title ?? '',
    url: props.article.url ?? '',
    short: props.article.short ?? '',
    description: props.article.description ?? '',
    author: props.article.author ?? '',
    tags: props.article.tags ?? '',
    views: props.article.views ?? '',
    likes: props.article.likes ?? '',
    image_url: null,
    seo_title: props.article.seo_title ?? '',
    seo_alt: props.article.seo_alt ?? '',
    meta_title: props.article.meta_title ?? '',
    meta_keywords: props.article.meta_keywords ?? '',
    meta_desc: props.article.meta_desc ?? '',
    activity: Boolean(props.article.activity ?? false),
    rubrics: []
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

// автоматическое заполнение поля tags
const handleTagsInputFocus = () => {
    const rubricsTitles = form.rubrics.map(rubric => rubric.title).filter(Boolean);
    const tags = [...rubricsTitles];

    if (form.author) {
        tags.push(form.author);
    }

    form.tags = tags.join(', ');
};

// автоматическая генерация мета-тегов
const truncateText = (text, maxLength, addEllipsis = false) => {
    if (text.length <= maxLength) return text;
    const truncated = text.substr(0, text.lastIndexOf(' ', maxLength));
    return addEllipsis ? `${truncated}...` : truncated;
};

const generateMetaFields = () => {
    if (form.title && !form.meta_title) {
        form.meta_title = truncateText(form.title, 160);
    }

    if (form.tags && !form.meta_keywords) {
        form.meta_keywords = truncateText(form.tags, 200);
    }

    if (form.short && !form.meta_desc) {
        form.meta_desc = truncateText(form.short.replace(/(<([^>]+)>)/gi, ""), 255, true);
    }
};

// Путь к изображению
const imagePreview = ref(props.article.image_url ? props.article.image_url : null); // Путь уже полный, без /storage/
const imageFile = ref(null);

const handleImageSelected = (file) => {
    imageFile.value = file;
    // console.log('Выбранное изображение:', file); // Проверяем, что файл выбран
};

// метод сохранения
const submitForm = async () => {
    if (imageFile.value) {
        form.image_url = imageFile.value;
    }

    //console.log('Форма перед отправкой:', form.data()); // Проверка данных формы

    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
    }));

    form.post(route('articles.update', props.article.id), {
        errorBag: 'editArticle',
        preserveScroll: true,
        onSuccess: () => {
            //console.log('Форма успешно обновлена.');
        },
        onError: (errors) => {
            console.error('Ошибка при обновлении:', errors); // Выводим ошибки при обновлении
        }
    });
};

// путь к изображению и вывод рубрик
onMounted(() => {
    imagePreview.value = props.article.image_url ? props.article.image_url : null;
    form.rubrics = props.article?.rubrics;
});

// вывод рубрик
watch(
    () => props.article,
    () => (form.rubrics = props.article?.rubrics)
);
</script>

<template>
    <AdminLayout :title="t('editArticle')">
        <template #header>
            <TitlePage>
                {{ t('editArticle') }}: {{ props.article.title }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">

            <div class="p-4 bg-slate-50 dark:bg-slate-700
                        border border-blue-400 dark:border-blue-200
                        shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('articles.index')">
                        <template #icon>
                            <!-- SVG -->
                            <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                <path d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
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
                        <LabelInput for="rubrics" :value="t('rubrics')" class="mb-1"/>
                        <VueMultiselect v-model="form.rubrics"
                                        :options="rubrics"
                                        :multiple="true"
                                        :close-on-select="true"
                                        :placeholder="t('select')"
                                        label="title"
                                        track-by="title"
                        />
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="title" :value="t('postTitle')"/>
                        <InputText
                            id="title"
                            type="text"
                            v-model="form.title"
                            required
                            autocomplete="title"
                        />
                        <InputError class="mt-2" :message="form.errors.title"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="url" :value="t('postUrl')"/>
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

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="author" :value="t('postAuthor')"/>
                        <InputText
                            id="author"
                            type="text"
                            v-model="form.author"
                            autocomplete="author"
                        />
                        <InputError class="mt-2" :message="form.errors.author"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="tags" :value="t('tags')"/>
                        <InputText
                            id="tags"
                            type="text"
                            v-model="form.tags"
                            autocomplete="tags"
                            @focus="handleTagsInputFocus"
                        />
                        <InputError class="mt-2" :message="form.errors.tags"/>
                    </div>

                    <div class="mb-3 flex justify-between">
                        <div class="flex flex-row items-center">
                            <LabelInput for="views" :value="t('views')" class="mr-3"/>
                            <InputNumber
                                id="views"
                                type="number"
                                v-model="form.views"
                                autocomplete="views"
                            />
                            <InputError class="mt-2" :message="form.errors.views"/>
                        </div>

                        <div class="flex flex-row items-center">
                            <LabelInput for="likes" :value="t('likes')" class="mr-3"/>
                            <InputNumber
                                id="likes"
                                type="number"
                                v-model="form.likes"
                                autocomplete="likes"
                            />
                            <InputError class="mt-2" :message="form.errors.likes"/>
                        </div>
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
                        <MetaDescTextarea v-model="form.meta_desc" class="w-full"/>
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
                        <DefaultButton :href="route('articles.index')" class="mb-3">
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

<style src="../../../../css/vue-multiselect.min.css"></style>
