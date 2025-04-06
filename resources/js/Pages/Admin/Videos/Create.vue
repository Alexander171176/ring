<script setup>
import {defineProps, onMounted} from 'vue';
import {transliterate} from '@/utils/transliteration';
import {useI18n} from 'vue-i18n';
import {useForm} from '@inertiajs/vue3';
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
import SelectLocale from "@/Components/Admin/Select/SelectLocale.vue";
import MultiImageUpload from "@/Components/Admin/Image/MultiImageUpload.vue";
import VueMultiselect from 'vue-multiselect';
import VideoSourceFields from "@/Components/Admin/Video/Upload/VideoSourceFields.vue";

const {t} = useI18n();

// пустой массив рубрик
defineProps({
    sections: Array,
    articles: Array,
    images: Array, // Добавляем этот пропс для передачи списка изображений
    related_videos: {type: Array, default: () => []} // задаём дефолтное значение
})

// пустая форма
const form = useForm({
    sort: 0,
    locale: '',
    title: '',
    url: '',
    short: '',
    description: '',
    author: '',
    published_at: '',     // дата публикации
    duration: '',         // длительность видео (в секундах)
    source_type: 'local', // тип источника (по умолчанию local)
    video_url: '',        // URL файла локального видео (если применяется)
    external_video_id: '',// идентификатор видео из внешнего сервиса
    views: '',
    likes: '',
    meta_title: '',
    meta_keywords: '',
    meta_desc: '',
    activity: false,
    left: false,
    main: false,
    right: false,
    sections: [],
    articles: [],
    related_videos: [],
    images: [], // Добавляем массив для загруженных изображений
    video_file: null
});

// Функция форматирования даты
const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return '';
    return date.toISOString().split('T')[0];
};

onMounted(() => {
    if (form.published_at) {
        form.published_at = formatDate(form.published_at);
    }
});

const handleVideoFileUpload = (event) => {
    // Сохраняем файл в форме (при необходимости добавьте поле video_file в useForm)
    form.video_file = event.target.files[0];
};

// автоматическое заполнение поля url
const handleUrlInputFocus = () => {
    if (form.title) {
        form.url = transliterate(form.title.toLowerCase());
    }
};

// количество символов в поле при генерации SEO
const truncateText = (text, maxLength, addEllipsis = false) => {
    if (text.length <= maxLength) return text;
    const truncated = text.substr(0, text.lastIndexOf(' ', maxLength));
    return addEllipsis ? `${truncated}...` : truncated;
};

// автоматическая генерация мета-тегов
const generateMetaFields = () => {
    if (form.title && !form.meta_title) {
        form.meta_title = truncateText(form.title, 160);
    }
    if (form.short && !form.meta_keywords) {
        // Убираем HTML-теги, разбиваем строку на слова и объединяем через запятую
        const stripped = form.short.replace(/(<([^>]+)>)/gi, "");
        form.meta_keywords = stripped.split(/\s+/).filter(word => word.length > 0).join(', ');
    }
    if (form.description && !form.meta_desc) {
        form.meta_desc = truncateText(form.description.replace(/(<([^>]+)>)/gi, ""), 255, true);
    }
};

// метод сохранения
const submitForm = () => {
    //console.log("📌 Отправляемые изображения перед трансформацией:", form.images);

    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
        left: data.left ? 1 : 0,
        main: data.main ? 1 : 0,
        right: data.right ? 1 : 0,

        images: form.images.map(image => {
            if (image.file) {
                return {file: image.file, order: image.order, alt: image.alt, caption: image.caption}; // ✅ Новое изображение
            }
            if (image.id) {
                return {id: Number(image.id), order: image.order, alt: image.alt, caption: image.caption}; // ✅ Существующее изображение
            }
        }).filter(Boolean) // ❌ Убираем undefined/null
    }));

    //console.log("✅ Отправляемые изображения после трансформации:", form.images);

    form.post(route('videos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            //console.log("✔️ Форма успешно отправлена.");
        },
        onError: (errors) => {
            console.error("❌ Ошибка при отправке формы:", errors);
        }
    });
};

</script>

<template>
    <AdminLayout :title="t('addVideo')">
        <template #header>
            <TitlePage>
                {{ t('addVideo') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700
                        border border-blue-400 dark:border-blue-200
                        shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка назад -->
                    <DefaultButton :href="route('videos.index')">
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
                <form @submit.prevent="submitForm" enctype="multipart/form-data" class="p-3 w-full">

                    <!-- Активность, Локализация, Сортировка -->
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

                    <!-- Показывать в левом сайдбаре, в главных новостях, в правом сайдбаре -->
                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <!-- Показывать в левом сайдбаре -->
                        <div class="flex flex-row items-center gap-2">
                            <ActivityCheckbox v-model="form.left"/>
                            <LabelCheckbox for="left" :text="t('left')" class="text-sm h-8 flex items-center"/>
                        </div>

                        <!-- Показывать в главных новостях -->
                        <div class="flex flex-row items-center gap-2">
                            <ActivityCheckbox v-model="form.main"/>
                            <LabelCheckbox for="main" :text="t('main')" class="text-sm h-8 flex items-center"/>
                        </div>

                        <!-- Показывать в правом сайдбаре -->
                        <div class="flex flex-row items-center gap-2">
                            <ActivityCheckbox v-model="form.right"/>
                            <LabelCheckbox for="right" :text="t('right')" class="text-sm h-8 flex items-center"/>
                        </div>

                    </div>

                    <!-- Выбрать секции для показа -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="sections" :value="t('sections')" class="mb-1"/>
                        <VueMultiselect v-model="form.sections"
                                        :options="sections"
                                        :multiple="true"
                                        :close-on-select="true"
                                        :placeholder="t('select')"
                                        label="title"
                                        track-by="title"
                        />
                    </div>

                    <!-- Выбрать статьи для показа -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="articles" :value="t('articles')" class="mb-1"/>
                        <VueMultiselect v-model="form.articles"
                                        :options="articles"
                                        :multiple="true"
                                        :close-on-select="true"
                                        :placeholder="t('select')"
                                        label="title"
                                        track-by="title"
                        />
                    </div>

                    <!-- Название -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="title">
                            <span class="text-red-500 dark:text-red-300 font-semibold">*</span> {{ t('name') }}
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

                    <!-- url -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="url">
                            <span class="text-red-500 dark:text-red-300 font-semibold">*</span> {{ t('url') }}
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

                    <!-- краткое описание -->
                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="short" :value="t('shortDescription')"/>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.short.length }} / 255 {{ t('characters') }}
                            </div>
                        </div>
                        <MetaDescTextarea v-model="form.short" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.short"/>
                    </div>

                    <!-- описание -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="description" :value="t('description')"/>
                        <CKEditor v-model="form.description" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.description"/>
                    </div>

                    <!-- Дата публикации, Автор -->
                    <div class="mb-3 flex flex-col lg:flex-row sm:justify-between sm:space-x-4">
                        <!-- Дата публикации -->
                        <div class="flex flex-col lg:flex-row items-center mb-2 lg:mb-0 flex-1">
                            <LabelInput for="published_at" :value="t('publishedAt')"
                                        class="mb-1 lg:mb-0 lg:mr-2"/>
                            <InputText
                                id="published_at"
                                type="date"
                                v-model="form.published_at"
                                autocomplete="published_at"
                                class="w-full max-w-56"
                            />
                            <InputError class="mt-1 sm:mt-0" :message="form.errors.published_at"/>
                        </div>
                        <!-- Автор -->
                        <div class="flex flex-col lg:flex-row items-center mb-2 lg:mb-0 flex-1">
                            <LabelInput for="author" :value="t('postAuthor')"
                                        class="w-40 mb-1 lg:mb-0 lg:mr-2"/>
                            <InputText
                                id="author"
                                type="text"
                                v-model="form.author"
                                autocomplete="author"
                                class="w-full"
                            />
                            <InputError class="mt-1 sm:mt-0" :message="form.errors.author"/>
                        </div>
                    </div>

                    <!-- Блок выбора типа источника видео, Блок продолжительности видео -->
                    <div class="mb-3 flex flex-col lg:flex-row lg:justify-between lg:space-x-4">
                        <!-- Блок выбора типа источника видео -->
                        <div class="flex flex-col lg:flex-row items-center mb-2 lg:mb-0 flex-1">
                            <LabelInput for="source_type" :value="t('sourceType')" class="mb-1 lg:mb-0 lg:mr-2"/>
                            <select id="source_type" v-model="form.source_type"
                                    class="form-select px-2 py-0.5 min-w-[12rem] font-semibold text-sm
                                    rounded-sm shadow-sm dark:bg-cyan-800 dark:text-slate-100 border-slate-500 focus:border-indigo-500 focus:ring-indigo-300">
                                <option value="local">{{ t('local') }}</option>
                                <option value="youtube">{{ t('youtube') }}</option>
                                <option value="vimeo">{{ t('vimeo') }}</option>
                                <option value="code">{{ t('code') }}</option>
                            </select>
                            <InputError class="mt-1 lg:mt-0" :message="form.errors.source_type"/>
                        </div>

                        <!-- Блок продолжительности видео -->
                        <div class="flex flex-col lg:flex-row items-center mb-2 lg:mb-0 flex-1 justify-between">
                            <LabelInput for="duration" :value="t('duration')" class="mb-1 lg:mb-0 lg:mr-2"/>
                            <InputNumber id="duration" type="number" v-model="form.duration" autocomplete="duration"
                                         class="w-full max-w-24"/>
                            <InputError class="mt-1 lg:mt-0" :message="form.errors.duration"/>
                        </div>
                    </div>

                    <!-- Блок просмотра видео -->
                    <div class="flex flex-col items-start">
                        <VideoSourceFields
                            v-model="form.source_type"
                            v-model:video-url="form.video_url"
                            v-model:external-video-id="form.external_video_id"
                            v-model:video-file="form.video_file" />
                        <InputError class="mt-2" :message="form.errors.external_video_id"/>
                    </div>

                    <!-- Мультиселект для связанных видео -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="related_videos" :value="t('relatedVideos')" class="mb-1"/>
                        <VueMultiselect v-model="form.related_videos"
                                        :options="related_videos"
                                        :multiple="true"
                                        :close-on-select="true"
                                        :placeholder="t('select')"
                                        label="title"
                                        track-by="title"/>
                        <InputError class="mt-2" :message="form.errors.related_videos"/>
                    </div>

                    <!-- количество просмотров и лайков -->
                    <div class="mb-3 flex flex-col sm:flex-row justify-between">
                        <div class="flex flex-row items-center mb-2">
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

                    <!-- meta title -->
                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="meta_title" :value="t('metaTitle')"/>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.meta_title.length }} / 160 {{ t('characters') }}
                            </div>
                        </div>
                        <InputText
                            id="meta_title"
                            type="text"
                            v-model="form.meta_title"
                            maxlength="160"
                            autocomplete="url"
                        />
                        <InputError class="mt-2" :message="form.errors.meta_title"/>
                    </div>

                    <!-- meta keywords -->
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

                    <!-- meta description -->
                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="meta_desc" :value="t('metaDescription')"/>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.meta_desc.length }} / 200 {{ t('characters') }}
                            </div>
                        </div>
                        <MetaDescTextarea v-model="form.meta_desc" maxlength="200" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.meta_desc"/>
                    </div>

                    <!-- кнопка генерации метатегов -->
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

                    <!-- загрузка изображений превью видео -->
                    <MultiImageUpload @update:images="form.images = $event"/>

                    <!-- кнопка сохранения -->
                    <div class="flex items-center justify-center mt-4">
                        <DefaultButton :href="route('videos.index')" class="mb-3">
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

<style src="../../../../css/vue-multiselect.min.css"></style>
