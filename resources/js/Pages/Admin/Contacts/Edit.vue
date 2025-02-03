<script setup>
import { defineProps, ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import InputError from '@/Components/Admin/Input/InputError.vue';
import CKEditor from '@/Components/Admin/CKEditor/CKEditor.vue';
import PrimaryButton from '@/Components/Admin/Buttons/PrimaryButton.vue';
import InputText from '@/Components/Admin/Input/InputText.vue';
import LabelInput from '@/Components/Admin/Input/LabelInput.vue';
import SimpleImageUpload from '@/Components/Admin/Input/SimpleImageUpload.vue';

const { t } = useI18n();

// Пропсы контакта
const props = defineProps({
    contact: {
        type: Object,
        required: true
    }
});

// Данные полей формы
const form = useForm({
    _method: 'PUT',
    tailwind: props.contact.tailwind ?? '',
    title: props.contact.title ?? '',
    content: props.contact.content ?? '',
    image: null,
    remove_image: false, // Флаг для удаления изображения
});

// Изображение
const imagePreview = ref(props.contact.image ? `/storage/${props.contact.image}` : null); // Путь к изображению
const imageFile = ref(null);

// Метод для обработки выбора изображения
const handleImageSelected = (file) => {
    imageFile.value = file;
    form.remove_image = false; // Сбрасываем флаг удаления изображения при выборе нового
};

// Метод для удаления изображения
const removeImage = () => {
    imagePreview.value = null; // Убираем предпросмотр
    form.image = ''; // Очищаем изображение
    form.remove_image = true; // Устанавливаем флаг для удаления изображения
};

// Метод сохранения формы
const submitForm = async () => {
    if (imageFile.value) {
        form.image = imageFile.value; // Если выбрано новое изображение, присваиваем его
    }

    form.transform((data) => ({
        ...data
    }));

    form.post(route('contacts.update', props.contact.id), {
        errorBag: 'editContact',
        preserveScroll: true,
        onSuccess: () => {
            // Можно добавить логику для успешного обновления
        },
        onError: (errors) => {
            console.error("Не удалось обновить форму:", errors);
        }
    });
};

// Путь к изображению при монтировании
onMounted(() => {
    imagePreview.value = props.contact.image ? `/storage/${props.contact.image}` : null;
});
</script>

<template>
    <AdminLayout :title="t('editSection')">
        <template #header>
            <TitlePage>
                {{ t('edit') }} {{ t('sectionContacts') }} ID:{{ props.contact.id }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700 border border-blue-400 dark:border-blue-200
                        overflow-hidden shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <DefaultButton :href="route('contacts.index')">
                        <template #icon>
                            <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                <path
                                    d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                            </svg>
                        </template>
                        {{ t('back') }}
                    </DefaultButton>
                </div>
                <form @submit.prevent="submitForm" enctype="multipart/form-data" class="p-3 w-full">

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="tailwind" :value="t('class')"/>
                        <InputText id="tailwind" type="text" v-model="form.tailwind" autocomplete="tailwind"/>
                        <InputError class="mt-2" :message="form.errors.tailwind"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="title" :value="t('heading')"/>
                        <InputText id="title" type="text" v-model="form.title" required autocomplete="title"/>
                        <InputError class="mt-2" :message="form.errors.title"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="content" :value="t('content')"/>
                        <CKEditor v-model="form.content" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.content"/>
                    </div>

                    <!-- Поле для загрузки изображения -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="image" :value="t('sectionImage')"/>
                        <SimpleImageUpload @fileSelected="handleImageSelected"/>
                        <div v-if="imagePreview" class="mt-2 w-full">
                            <img :src="imagePreview" alt="Preview" class="w-full h-fit object-cover">

                            <!-- Кнопка для удаления изображения -->
                            <button type="button" @click="removeImage"
                                    class="btn mb-3 px-3 py-1
                                               flex items-center float-right
                                               bg-red-500 text-white
                                               text-lg font-semibold
                                               rounded-md shadow-md
                                               transition-colors duration-300 ease-in-out
                                               hover:bg-red-600 focus:bg-red-600 focus:outline-none">
                                <svg class="w-4 h-4 fill-current text-white shrink-0"
                                     viewBox="0 0 16 16">
                                    <path d="M5 7h2v6H5V7zm4 0h2v6H9V7zm3-6v2h4v2h-1v10c0 .6-.4 1-1 1H2c-.6 0-1-.4-1-1V5H0V3h4V1c0-.6.4-1 1-1h6c.6 0 1 .4 1 1zM6 2v1h4V2H6zm7 3H3v9h10V5z"></path>
                                </svg>
                                <span class="ml-2">{{ t('deleteImage') }}</span>
                            </button>

                        </div>
                    </div>

                    <!-- Скрытое поле для удаления изображения -->
                    <input type="hidden" v-model="form.remove_image"/>

                    <div class="flex items-center justify-center mt-4">
                        <DefaultButton :href="route('contacts.index')" class="mb-3 mr-2">
                            <template #icon>
                                <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path
                                        d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                                </svg>
                            </template>
                            {{ t('back') }}
                        </DefaultButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <template #icon>
                                <svg class="w-4 h-4 fill-current text-slate-100" viewBox="0 0 16 16">
                                    <path d="M14.3 2.3L5 11.6 1.7 8.3c-.4-.4-1-.4-1.4 0-.4.4-.4 1 0 1.4l4 4c.2.2.4.3.7.3.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4-.4-.4-1-.4-1.4 0z"></path>
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
