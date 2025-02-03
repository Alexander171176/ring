<script setup>
import {ref} from 'vue';
import {useI18n} from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import PrimaryButton from '@/Components/Admin/Buttons/PrimaryButton.vue';
import LabelCheckbox from '@/Components/Admin/Checkbox/LabelCheckbox.vue';
import ActivityCheckbox from '@/Components/Admin/Checkbox/ActivityCheckbox.vue';
import CKEditor from '@/Components/Admin/CKEditor/CKEditor.vue';
import InputNumber from '@/Components/Admin/Input/InputNumber.vue';
import LabelInput from '@/Components/Admin/Input/LabelInput.vue';
import InputText from '@/Components/Admin/Input/InputText.vue';
import InputError from '@/Components/Admin/Input/InputError.vue';
import ImageUpload from '@/Components/Admin/Input/ImageUpload.vue';

const {t} = useI18n();

// пустая форма
const form = useForm({
    sort: '0',
    type: '',
    tailwind: '',
    image: null,
    title: '',
    content: '',
    activity: false,
});

// Изображение
const imagePreview = ref(null);
const imageInput = ref(null);

const updateImagePreview = ({file, preview}) => {
    imagePreview.value = preview;
    imageInput.value = file;
};

const clearImageFileInput = () => {
    if (imageInput.value) {
        imageInput.value = null;
        imagePreview.value = null;
    }
};

// метод сохранения
const submitForm = () => {
    if (imageInput.value) {
        form.image = imageInput.value;
    }

    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
    }));

    // console.log("Форма для отправки заполнена:", form.data());

    form.post(route('abouts.store'), {
        errorBag: 'createAbout',
        preserveScroll: true,
        onSuccess: () => {
            clearImageFileInput();
            // console.log("Форма успешно отправлена.");
        },
        onError: (errors) => {
            // console.error("Не удалось отправить форму:", errors);
        }
    });
};
</script>

<template>
    <AdminLayout :title="t('createSection')">
        <template #header>
            <TitlePage>
                {{ t('createSection') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">

            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('abouts.index')">
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

                    <!-- Поле type -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="type" :value="t('type')"/>
                        <InputText
                            id="type"
                            type="text"
                            v-model="form.type"
                            required
                            autocomplete="type"
                        />
                        <InputError class="mt-2" :message="form.errors.type"/>
                    </div>

                    <!-- Поле tailwind -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="tailwind" :value="t('class')"/>
                        <InputText
                            id="tailwind"
                            type="text"
                            v-model="form.tailwind"
                            autocomplete="tailwind"
                        />
                        <InputError class="mt-2" :message="form.errors.tailwind"/>
                    </div>

                    <!-- Поле title -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="title" :value="t('heading')"/>
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
                        <LabelInput for="content" :value="t('content')"/>
                        <CKEditor v-model="form.content" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.content"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="image" :value="t('sectionImage')"/>
                        <ImageUpload
                            id="image"
                            name="image"
                            :imagePreview="imagePreview"
                            :altText="t('currentImage')"
                            :titleText="t('currentImage')"
                            @change="updateImagePreview"
                        />
                    </div>

                    <div class="flex justify-center mt-4">
                        <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }"
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
