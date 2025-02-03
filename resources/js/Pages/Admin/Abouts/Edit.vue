<script setup>
import { defineProps, ref, onMounted } from 'vue';
import {useI18n} from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import InputError from '@/Components/Admin/Input/InputError.vue';
import CKEditor from '@/Components/Admin/CKEditor/CKEditor.vue';
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
    section: {
        type: Object,
        required: true
    }
});

// данные полей формы
const form = useForm({
    _method: 'PUT',
    sort: props.section.sort ?? 0,
    type: props.section.type ?? '',
    tailwind: props.section.tailwind ?? '',
    title: props.section.title ?? '',
    content: props.section.content ?? '',
    image: null,
    activity: Boolean(props.section.activity ?? false),
});

// Изображение
const imagePreview = ref(props.section.image_url ? props.section.image_url : null); // Путь уже полный, без /storage/
const imageFile = ref(null);

const handleImageSelected = (file) => {
    imageFile.value = file;
};

// метод сохранения
const submitForm = async () => {
    if (imageFile.value) {
        form.image = imageFile.value;
    }

    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
    }));

    form.post(route('abouts.update', props.section.id), {
        errorBag: 'editSection',
        preserveScroll: true,
        onSuccess: () => {},
        onError: (errors) => {
            console.error("Не удалось обновить форму:", errors);
        }
    });
};

// путь к изображению
onMounted(() => {
    imagePreview.value = props.section.image_url ? props.section.image_url : null;
});

</script>

<template>
    <AdminLayout :title="t('editSection')">
        <template #header>
            <TitlePage>
                {{ t('editSection') }}: {{ props.section.title }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка назад -->
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

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="type" :value="t('type')" />
                        <InputText
                            id="type"
                            type="text"
                            v-model="form.type"
                            required
                            autocomplete="type"
                        />
                        <InputError class="mt-2" :message="form.errors.type"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="tailwind" :value="t('class')" />
                        <InputText
                            id="tailwind"
                            type="text"
                            v-model="form.tailwind"
                            autocomplete="tailwind"
                        />
                        <InputError class="mt-2" :message="form.errors.tailwind"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="title" :value="t('heading')" />
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
                        <SimpleImageUpload
                            @fileSelected="handleImageSelected"
                        />
                        <div v-if="imagePreview" class="mt-2 w-full">
                            <img :src="imagePreview" :alt="t('currentImage')" class="w-full h-fit object-cover">
                        </div>
                    </div>

                    <div class="flex items-center justify-center mt-4">
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
