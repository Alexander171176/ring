<script setup>
import { defineProps, ref, watch } from 'vue';
import { transliterate } from '@/utils/transliteration';
import { useI18n } from 'vue-i18n';
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
import SelectLocale from "@/Components/Admin/Select/SelectLocale.vue";
import MultiImageManager from "@/Components/Image/MultiImageManager.vue"; //  Импортируем новый компонент
import VueMultiselect from 'vue-multiselect';

const { t } = useI18n();

const props = defineProps({
    article: {
        type: Object,
        required: true
    },
    rubrics: Array,
    tags: Array
});

// Используем ref для existingImages, newImages и deletedImages
const existingImages = ref((props.article.images || []).map(img => ({
    id: img.id ? Number(img.id) : undefined,
    url: `/storage/${img.path}`,
    alt: img.alt || '',
    caption: img.caption || '',
    isExisting: true,
})));
const newImages = ref([]);
const deletedImages = ref([]);


const form = useForm({  //  useForm  оставляем *только* для тех полей,
    _method: 'PUT',        //  которые НЕ меняются динамически
    sort: props.article.sort ?? 0,
    locale: props.article.locale ?? '',
    title: props.article.title ?? '',
    url: props.article.url ?? '',
    short: props.article.short ?? '',
    description: props.article.description ?? '',
    author: props.article.author ?? '',
    views: props.article.views ?? '',
    likes: props.article.likes ?? '',
    meta_title: props.article.meta_title ?? '',
    meta_keywords: props.article.meta_keywords ?? '',
    meta_desc: props.article.meta_desc ?? '',
    activity: Boolean(props.article.activity ?? false),
    rubrics: props.article.rubrics ?? [],
    tags: props.article.tags ?? [],
    deletedImages: [], // Добавляем пустой массив для удалённых изображений
});


const handleUrlInputFocus = () => {
    if (form.title) {
        form.url = transliterate(form.title.toLowerCase());
    }
};

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
        const tagNames = form.tags.map(tag => tag.name).join(', ');
        form.meta_keywords = truncateText(tagNames, 200);
    }

    if (form.short && !form.meta_desc) {
        form.meta_desc = truncateText(form.short.replace(/(<([^>]+)>)/gi, ""), 255, true);
    }
};

//  Обработчик удаления *существующего* изображения
const handleExistingImageDeleted = (deletedId) => {
    if (!deletedImages.value.includes(deletedId)) {
        deletedImages.value.push(deletedId);
    }
    existingImages.value = existingImages.value.filter(image => image.id !== deletedId);
    console.log("Edit.vue handleExistingImageDeleted - deletedImages:", deletedImages.value);
    console.log("Edit.vue handleExistingImageDeleted - existingImages:", existingImages.value);
};

//  Обработчик события  update:images  от  MultiImageManager
const handleImagesUpdated = (updatedImages) => {
    console.log("Edit.vue handleImagesUpdated:", updatedImages);

    // Фильтруем изображения, исключая те, что помечены как удалённые
    const filteredImages = updatedImages.filter(img => !(img.id && deletedImages.value.includes(img.id)));

    newImages.value = filteredImages.filter(img => !img.id);
    existingImages.value = filteredImages.filter(img => img.id);
    console.log("Edit existingImages:", existingImages.value);
    console.log("Edit newImages:", newImages.value);
};

const submitForm = () => {
    const images = [
        ...newImages.value.map(img => ({ file: img.file, alt: img.alt, caption: img.caption })),
        ...existingImages.value.map(img => ({ id: img.id, alt: img.alt, caption: img.caption })),
    ];

    // Обновляем поле deletedImages в форме
    form.deletedImages = deletedImages.value;

    const data = {
        ...form.data(), // теперь здесь будет и deletedImages
        activity: form.activity ? 1 : 0,
        images,
    };

    console.log("Edit.vue submitForm - data:", data);

    // Ручное создание FormData для корректной передачи вложенных файлов
    const formData = new FormData();

    for (const key in data) {
        if (key === 'images') {
            data.images.forEach((image, index) => {
                if (image.file) {
                    formData.append(`images[${index}][file]`, image.file);
                }
                // Добавляем alt и caption, даже если они пустые
                formData.append(`images[${index}][alt]`, image.alt || '');
                formData.append(`images[${index}][caption]`, image.caption || '');
                if (image.id) {
                    formData.append(`images[${index}][id]`, image.id);
                }
            });
        } else if (Array.isArray(data[key])) {
            data[key].forEach((value, index) => {
                // Если значение является объектом, можно сериализовать его,
                // либо, если ожидается массив примитивов, передаем напрямую.
                if (typeof value === 'object') {
                    formData.append(`${key}[${index}]`, JSON.stringify(value));
                } else {
                    formData.append(`${key}[${index}]`, value);
                }
            });
        } else {
            formData.append(key, data[key]);
        }
    }

    form.post(route('articles.update', props.article.id), {
        preserveScroll: true,
        data: formData,
        // forceFormData можно оставить, хотя теперь FormData формируется вручную
        forceFormData: true,
        onSuccess: (page) => {
            console.log("Edit.vue onSuccess:", page);
            window.location.href = route('articles.index');
        },
        onError: (errors) => {
            console.error("❌ Ошибка при обновлении статьи:", errors);
        }
    });
};

watch(() => props.existingImages, (newVal) => {
    previewImages.value = newVal.map(img => ({
        id: img.id,
        url: img.url,
        alt: img.alt || '',
        caption: img.caption || '',
        isExisting: img.isExisting ?? true,
    }));
});

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
                            <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                <path
                                    d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2 .8-6.4z"></path>
                            </svg>
                        </template>
                        {{ t('back') }}
                    </DefaultButton>
                </div>
                <form @submit.prevent="submitForm" enctype="multipart/form-data" class="p-3 w-full">

                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <div class="flex flex-row items-center gap-2">
                            {/*  Используем  v-model  с  ref  переменными  */}
                            <ActivityCheckbox v-model="form.activity"/>
                            <LabelCheckbox for="activity" :text="t('activity')" class="text-sm h-8 flex items-center"/>
                        </div>

                        <div class="flex flex-row items-center gap-2 w-auto">
                            <SelectLocale v-model="form.locale" :errorMessage="form.errors.locale"/>
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.locale"/>
                        </div>

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
                        <LabelInput for="tags" :value="t('tags')" class="mb-1"/>
                        <VueMultiselect v-model="form.tags"
                                        :options="tags"
                                        :multiple="true"
                                        :close-on-select="true"
                                        :placeholder="t('select')"
                                        label="name"
                                        track-by="name"
                        />
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
                                <svg class="w-4 h-4 fill-current text-slate-600 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path
                                        d="M13 7h2v6a1 1 0 01-1 1H4v2l-4-3 4-3v2h9V7zM3 9H1V3a1 1 0 011-1h10V0l4 3-4 3V4H3v5z"></path>
                                </svg>
                            </template>
                            {{ t('generateMetaTags') }}
                        </MetatagsButton>
                    </div>

                    <MultiImageManager
                        :existingImages="existingImages"
                        @update:images="handleImagesUpdated"
                        @remove-existing-image="handleExistingImageDeleted"
                    />

                    <div class="flex items-center justify-center mt-4">
                        <DefaultButton :href="route('articles.index')" class="mb-3">
                            <template #icon>
                                <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path
                                        d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2 .8-6.4z"></path>
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
