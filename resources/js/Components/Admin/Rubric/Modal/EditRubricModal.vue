<script setup>
import { ref, defineProps, onMounted, watch, defineEmits } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import CloseIconButton from '@/Components/Admin/Buttons/CloseIconButton.vue';
import CancelButton from '@/Components/Admin/Buttons/CancelButton.vue';
import PrimaryButton from '@/Components/Admin/Buttons/PrimaryButton.vue';
import InputNumber from '@/Components/Admin/Input/InputNumber.vue';
import ActivityCheckbox from '@/Components/Admin/Checkbox/ActivityCheckbox.vue';
import LabelCheckbox from '@/Components/Admin/Checkbox/LabelCheckbox.vue';
import LabelInput from '@/Components/Admin/Input/LabelInput.vue';
import InputText from '@/Components/Admin/Input/InputText.vue';
import InputError from '@/Components/Admin/Input/InputError.vue';
import SimpleImageUpload from '@/Components/Admin/Input/SimpleImageUpload.vue';
import CKEditor from '@/Components/Admin/CKEditor/CKEditor.vue';
import MetaDescTextarea from '@/Components/Admin/Textarea/MetaDescTextarea.vue';
import MetatagsButton from '@/Components/Admin/Buttons/MetatagsButton.vue';
import { transliterate } from '@/utils/transliteration';

const { t } = useI18n();

const props = defineProps({
    rubric: {
        type: Object,
        default: null
    },
    show: {
        type: Boolean,
        required: true
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    _method: 'PUT',
    sort: 0,
    title: '',
    url: '',
    description: '',
    image_url: null,
    seo_title: '',
    seo_alt: '',
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

// автоматическое заполнение поля seo_alt
const handleSeoAltFocus = () => {
    if (form.seo_title && !form.seo_alt) {
        form.seo_alt = form.seo_title;
    }
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

    if (form.description && !form.meta_desc) {
        form.meta_desc = truncateText(form.description.replace(/(<([^>]+)>)/gi, ""), 200, true);
    }
};

// Изображение
const imagePreview = ref(props.rubric?.image_url ? `/storage/${props.rubric.image_url}` : null);
const imageFile = ref(null);

const handleImageSelected = (file) => {
    imageFile.value = file;
};

// инициализация данных в форме для модального окна
const updateForm = () => {
    form.sort = props.rubric?.sort ?? 0;
    form.title = props.rubric?.title ?? '';
    form.url = props.rubric?.url ?? '';
    form.description = props.rubric?.description ?? '';
    form.seo_title = props.rubric?.seo_title ?? '';
    form.seo_alt = props.rubric?.seo_alt ?? '';
    form.meta_title = props.rubric?.meta_title ?? '';
    form.meta_keywords = props.rubric?.meta_keywords ?? '';
    form.meta_desc = props.rubric?.meta_desc ?? '';
    form.activity = Boolean(props.rubric?.activity ?? false);
    imagePreview.value = props.rubric?.image_url ? `/storage/${props.rubric.image_url}` : null;

    // console.log("Форма дополнена данными:", {
    //     rubric: props.rubric
    // });
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

    form.post(route('rubrics.update', props.rubric.id), {
        errorBag: 'editRubric',
        preserveScroll: true,
        onSuccess: () => {
            // console.log("Форма успешно обновлена.");
            emit('close'); // Закрываем модальное окно
        },
        onError: (errors) => {
            // console.error("Не удалось обновить форму:", errors);
        }
    });
};

// путь к изображению и вывод рубрик
onMounted(() => {
    updateForm();
});

// обновление формы при изменении пропсов show и rubric
watch(
    () => props.show,
    (newShow) => {
        if (newShow) {
            updateForm();
        }
    }
);

watch(
    () => props.rubric,
    (newRubric) => {
        updateForm();
    }
);
</script>

<template>
    <div v-if="show" class="fixed inset-0 flex items-center justify-center z-50 overflow-y-auto custom-scrollbar">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="absolute
                    w-full
                    max-w-screen-2xl
                    max-h-screen
                    overflow-y-auto
                    bg-white dark:bg-gray-800
                    p-6
                    rounded-lg
                    shadow-lg
                    z-10
                    custom-scrollbar">
            <CloseIconButton @close="emit('close')" />
            <h2 class="text-center text-2xl font-bold mb-4 text-gray-600 dark:text-slate-100 tracking-wide">
                {{ t('editRubric') }} ID: {{ props.rubric.id }}
            </h2>
            <form @submit.prevent="submitForm" enctype="multipart/form-data" class="p-3 w-full">
                <div class="pb-12">

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
                        <LabelInput for="title" :value="t('rubricTitle')" />
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
                        <LabelInput for="url" :value="t('rubricUrl')"/>
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
                        <LabelInput for="description" :value="t('description')"/>
                        <CKEditor v-model="form.description" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.description"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="image_url" :value="t('rubricImage')"/>
                        <SimpleImageUpload
                            @fileSelected="handleImageSelected"
                        />
                        <div v-if="imagePreview" class="mt-2 w-full">
                            <img :src="imagePreview" :alt="t('currentImage')" class="w-full h-fit object-cover">
                        </div>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="seo_title" :value="t('seoTitle')"/>
                        <InputText
                            id="seo_title"
                            type="text"
                            v-model="form.seo_title"
                            autocomplete="url"
                        />
                        <InputError class="mt-2" :message="form.errors.seo_title"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="seo_alt" :value="t('seoAlt')"/>
                        <InputText
                            id="seo_alt"
                            type="text"
                            v-model="form.seo_alt"
                            autocomplete="url"
                            @focus="handleSeoAltFocus"
                        />
                        <InputError class="mt-2" :message="form.errors.seo_alt"/>
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

                </div>
                <div class="fixed
                            bottom-0
                            left-1/2
                            transform -translate-x-1/2
                            bg-white dark:bg-gray-800
                            p-4
                            shadow-md
                            z-20
                            w-full max-w-screen-2xl">
                    <div class="flex justify-end space-x-2">
                        <CancelButton @close="emit('close')" />
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
                </div>
            </form>
        </div>
    </div>
</template>
