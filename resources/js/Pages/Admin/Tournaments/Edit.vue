<script setup>
/**
 * @version PulsarCMS 1.0
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 */
import { useToast } from 'vue-toastification';
import { useI18n } from 'vue-i18n';
import {computed, defineProps, onMounted, ref} from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue';
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue';
import LabelInput from '@/Components/Admin/Input/LabelInput.vue';
import InputText from '@/Components/Admin/Input/InputText.vue';
import InputError from '@/Components/Admin/Input/InputError.vue';
import PrimaryButton from '@/Components/Admin/Buttons/PrimaryButton.vue';
import MetaDescTextarea from "@/Components/Admin/Textarea/MetaDescTextarea.vue";
import LabelCheckbox from "@/Components/Admin/Checkbox/LabelCheckbox.vue";
import ActivityCheckbox from "@/Components/Admin/Checkbox/ActivityCheckbox.vue";
import InputNumber from "@/Components/Admin/Input/InputNumber.vue";
import CKEditor from "@/Components/Admin/CKEditor/CKEditor.vue";
import TinyEditor from "@/Components/Admin/TinyEditor/TinyEditor.vue";

// Импорт двух отдельных компонентов для работы с изображениями:
import MultiImageUpload from '@/Components/Admin/Image/MultiImageUpload.vue'; // для загрузки новых изображений
import MultiImageEdit from '@/Components/Admin/Image/MultiImageEdit.vue';
import AvatarEditUpload from "@/Components/Admin/Athlete/Avatar/AvatarEditUpload.vue";
import StanceSelect from "@/Components/Admin/Athlete/Select/StanceSelect.vue";
import SelectLocale from "@/Components/Admin/Select/SelectLocale.vue";     // для редактирования существующего аватара


// --- Инициализация ---
const toast = useToast();
const { t } = useI18n();

/**
 * Входные свойства компонента.
 */
const props = defineProps({
    athlete: { type: Object, required: true }
});

/**
 * Формируем форму редактирования.
 */
const form = useForm({
    _method: 'PUT',
    sort: props.athlete.sort ?? 0,
    locale: props.athlete.locale ?? '',
    nickname: props.athlete.nickname ?? '',
    first_name: props.athlete.first_name ?? '',
    last_name: props.athlete.last_name ?? '',
    date_of_birth: props.athlete.date_of_birth ?? '',
    nationality: props.athlete.nationality ?? '',
    height_cm: props.athlete.height_cm ?? 0,
    reach_cm: props.athlete.reach_cm ?? 0,
    stance: props.athlete.stance ?? null,
    bio: props.athlete.bio ?? '',
    short: props.athlete.short ?? '',
    description: props.athlete.description ?? '',
    wins: props.athlete.wins ?? 0,
    losses: props.athlete.losses ?? 0,
    draws: props.athlete.draws ?? 0,
    no_contests: props.athlete.no_contests ?? 0,
    wins_by_ko: props.athlete.wins_by_ko ?? 0,
    wins_by_submission: props.athlete.wins_by_submission ?? 0,
    wins_by_decision: props.athlete.wins_by_decision ?? 0,
    activity: Boolean(props.athlete.activity),
    avatar: null,
    deletedImages: [] // массив для хранения ID удалённых изображений
});

const avatarUrl = computed(() => {
    return props.athlete.avatar
        ? `/storage/${props.athlete.avatar}`
        : null;
});

// console.log(props.athlete.avatar);

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return '';
    return date.toISOString().split('T')[0];
};

onMounted(() => {
    if (form.date_of_birth) {
        form.date_of_birth = formatDate(form.date_of_birth);
    }
});

/**
 * Массив существующих изображений.
 */
const existingImages = ref(
    (props.athlete.images || [])
        .filter(img => img.url) // фильтруем изображения, у которых есть URL
        .map(img => ({
            id: img.id,
            // Если есть WebP-версия, используем её, иначе — оригинальный URL
            url: img.webp_url || img.url,
            order: img.order || 0,
            alt: img.alt || '',
            caption: img.caption || ''
        }))
);

/**
 * Массив для новых изображений (будут содержать свойство file).
 */
const newImages = ref([]);

/**
 * Обработчик обновления существующих изображений, приходящих из компонента MultiImageEdit.
 */
const handleExistingImagesUpdate = (images) => {
    existingImages.value = images;
};

/**
 * Обработчик удаления изображения из существующего списка.
 */
const handleDeleteExistingImage = (deletedId) => {
    if (!form.deletedImages.includes(deletedId)) {
        form.deletedImages.push(deletedId);
    }
    existingImages.value = existingImages.value.filter(img => img.id !== deletedId);
    // console.log("Deleted IDs:", form.deletedImages);
    // console.log("Remaining images:", existingImages.value);
};

/**
 * Обработчик обновления новых изображений из компонента MultiImageUpload.
 */
const handleNewImagesUpdate = (images) => {
    newImages.value = images;
};

/**
 * Отправляет данные формы для обновления.
 */
const submit = () => {
    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
        images: [
            ...newImages.value.map(img => ({
                file: img.file,
                order: img.order,
                alt: img.alt,
                caption: img.caption
            })),
            ...existingImages.value.map(img => ({
                id: img.id,
                order: img.order,
                alt: img.alt,
                caption: img.caption
            }))
        ],
        deletedImages: form.deletedImages
    }));

    form.post(route('admin.athletes.update', props.athlete.id), {
        forceFormData: true,
        errorBag: 'editAthlete',
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Спортсмен успешно обновлён!');
        },
        onError: (errors) => {
            console.error('❌ Ошибки валидации:', errors);
            const firstError = errors[Object.keys(errors)[0]];
            toast.error(firstError || 'Пожалуйста, проверьте правильность заполнения полей.');
        }
    });
};

</script>

<template>
    <AdminLayout :title="t('editAthlete')">
        <template #header>
            <TitlePage>
                {{ t('editAthlete') }} ID:{{ props.athlete.id }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700
                        border border-blue-400 dark:border-blue-200
                        shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка назад -->
                    <DefaultButton :href="route('admin.athletes.index')">
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
                <form @submit.prevent="submit" enctype="multipart/form-data" class="p-3 w-full">

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

                    <!-- Поле Псевдоним -->
                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="nickname">
                                {{ t('nickname') }}
                            </LabelInput>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.nickname.length }} / 100 {{ t('characters') }}
                            </div>
                        </div>
                        <InputText
                            id="nickname"
                            type="text"
                            v-model="form.nickname"
                            maxlength="100"
                            required
                            autocomplete="nickname"
                        />
                        <InputError class="mt-2" :message="form.errors.nickname"/>
                    </div>

                    <!-- Поле Имя -->
                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="first_name">
                                <span class="text-red-500 dark:text-red-300 font-semibold">*</span> {{ t('name') }}
                            </LabelInput>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.first_name.length }} / 100 {{ t('characters') }}
                            </div>
                        </div>
                        <InputText
                            id="first_name"
                            type="text"
                            v-model="form.first_name"
                            maxlength="100"
                            required
                            autocomplete="first_name"
                        />
                        <InputError class="mt-2" :message="form.errors.first_name"/>
                    </div>

                    <!-- Поле Фамилия -->
                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="last_name">
                                <span class="text-red-500 dark:text-red-300 font-semibold">*</span> {{ t('lastName') }}
                            </LabelInput>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.last_name.length }} / 100 {{ t('characters') }}
                            </div>
                        </div>
                        <InputText
                            id="last_name"
                            type="text"
                            v-model="form.last_name"
                            maxlength="100"
                            required
                            autocomplete="last_name"
                        />
                        <InputError class="mt-2" :message="form.errors.last_name"/>
                    </div>

                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <!-- Дата рождения -->
                        <div class="mt-3 flex flex-col items-start w-full">
                            <div class="flex justify-start w-full">
                                <LabelInput for="date_of_birth" :value="t('dateBirth')"
                                            class="mb-1 lg:mb-0 lg:mr-2"/>
                                <InputText
                                    id="date_of_birth"
                                    type="date"
                                    v-model="form.date_of_birth"
                                    autocomplete="date_of_birth"
                                    class="w-full max-w-56"
                                />
                                <InputError class="mt-1 sm:mt-0" :message="form.errors.date_of_birth"/>
                            </div>
                        </div>

                        <!-- Поле Страна -->
                        <div class="flex flex-row items-center justify-end w-full">
                            <LabelInput for="nationality" class="mt-4 mr-2">
                                {{ t('country') }}
                            </LabelInput>
                            <div class="mb-3 flex flex-col items-end w-96">
                                <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                    {{ form.nationality.length }} / 100 {{ t('characters') }}
                                </div>
                                <InputText
                                    id="nationality"
                                    type="text"
                                    v-model="form.nationality"
                                    maxlength="100"
                                    required
                                    autocomplete="nationality"
                                />
                                <InputError class="mt-2" :message="form.errors.nationality"/>
                            </div>
                        </div>

                    </div>

                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <StanceSelect v-model="form.stance" :error="form.errors.stance" class="mt-3" />

                        <!-- рост в сантиметрах -->
                        <div class="flex flex-row items-center gap-2">
                            <div class="h-8 flex items-center">
                                <LabelInput for="height_cm" :value="t('cmHeight')" class="text-sm"/>
                            </div>
                            <InputNumber
                                id="height_cm"
                                type="number"
                                v-model="form.height_cm"
                                autocomplete="height_cm"
                                class="w-full lg:w-28"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.height_cm"/>
                        </div>

                        <!-- размах рук в сантиметрах -->
                        <div class="flex flex-row items-center gap-2">
                            <div class="h-8 flex items-center">
                                <LabelInput for="reach_cm" :value="t('cmReach')" class="text-sm"/>
                            </div>
                            <InputNumber
                                id="reach_cm"
                                type="number"
                                v-model="form.reach_cm"
                                autocomplete="reach_cm"
                                class="w-full lg:w-28"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.reach_cm"/>
                        </div>

                    </div>

                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <!-- Количество поражений -->
                        <div class="flex flex-row items-center gap-2">
                            <div class="h-8 flex items-center">
                                <LabelInput for="losses" :value="t('losses')" class="text-sm"/>
                            </div>
                            <InputNumber
                                id="losses"
                                type="number"
                                v-model="form.losses"
                                autocomplete="losses"
                                class="w-full lg:w-28"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.losses"/>
                        </div>

                        <!-- Количество ничьих -->
                        <div class="flex flex-row items-center gap-2">
                            <div class="h-8 flex items-center">
                                <LabelInput for="draws" :value="t('draws')" class="text-sm"/>
                            </div>
                            <InputNumber
                                id="draws"
                                type="number"
                                v-model="form.draws"
                                autocomplete="draws"
                                class="w-full lg:w-28"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.draws"/>
                        </div>

                        <!-- Количество побед -->
                        <div class="flex flex-row items-center gap-2">
                            <div class="h-8 flex items-center">
                                <LabelInput for="wins" :value="t('wins')" class="text-sm"/>
                            </div>
                            <InputNumber
                                id="wins"
                                type="number"
                                v-model="form.wins"
                                autocomplete="wins"
                                class="w-full lg:w-28"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.wins"/>
                        </div>

                    </div>

                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <!-- Количество побед нокаутом -->
                        <div class="flex flex-row items-center gap-2">
                            <div class="h-8 flex items-center">
                                <LabelInput for="wins_by_ko" :value="t('winsByKo')" class="text-sm"/>
                            </div>
                            <InputNumber
                                id="wins_by_ko"
                                type="number"
                                v-model="form.wins_by_ko"
                                autocomplete="wins_by_ko"
                                class="w-full lg:w-28"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.wins_by_ko"/>
                        </div>

                        <!-- Количество побед сдачей (сабмишном) -->
                        <div class="flex flex-row items-center gap-2">
                            <div class="h-8 flex items-center">
                                <LabelInput for="wins_by_submission" :value="t('winsBySubmission')" class="text-sm"/>
                            </div>
                            <InputNumber
                                id="wins_by_submission"
                                type="number"
                                v-model="form.wins_by_submission"
                                autocomplete="wins_by_submission"
                                class="w-full lg:w-28"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.wins_by_submission"/>
                        </div>

                        <!-- Количество побед решением судей -->
                        <div class="flex flex-row items-center gap-2">
                            <div class="h-8 flex items-center">
                                <LabelInput for="wins_by_decision" :value="t('winsByDecision')" class="text-sm"/>
                            </div>
                            <InputNumber
                                id="wins_by_decision"
                                type="number"
                                v-model="form.wins_by_decision"
                                autocomplete="wins_by_decision"
                                class="w-full lg:w-28"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.wins_by_decision"/>
                        </div>

                    </div>

                    <!-- Количество боев признанных несостоявшимися -->
                    <div class="flex flex-row items-center gap-2">
                        <div class="h-8 flex items-center">
                            <LabelInput for="no_contests" :value="t('noContests')" class="text-sm"/>
                        </div>
                        <InputNumber
                            id="no_contests"
                            type="number"
                            v-model="form.no_contests"
                            autocomplete="no_contests"
                            class="w-full lg:w-28"
                        />
                        <InputError class="mt-2 lg:mt-0" :message="form.errors.no_contests"/>
                    </div>

                    <!-- Биография -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="bio" :value="t('bio')" class="w-full flex justify-center"/>
                        <TinyEditor v-model="form.bio" :height="500"/>
                        <InputError class="mt-2" :message="form.errors.bio"/>
                    </div>

                    <!-- Краткое описание -->
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

                    <!-- Описание -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="description" :value="t('description')"/>
                        <TinyEditor v-model="form.description" :height="500"/>
                        <!-- <CKEditor v-model="form.description" class="w-full"/> -->
                        <InputError class="mt-2" :message="form.errors.description"/>
                    </div>

                    <!-- Аватар -->
                    <AvatarEditUpload
                        v-model="form.avatar"
                        :current-avatar="props.athlete.avatar"
                        :error="form.errors.avatar"
                    />

                    <!-- Блок редактирования существующих изображений -->
                    <div class="mt-4">
                        <MultiImageEdit
                            :images="existingImages"
                            @update:images="handleExistingImagesUpdate"
                            @delete-image="handleDeleteExistingImage" />
                    </div>

                    <!-- Блок загрузки новых изображений -->
                    <div class="mt-4">
                        <MultiImageUpload @update:images="handleNewImagesUpdate" />
                    </div>

                    <div class="flex items-center justify-center mt-4">
                        <DefaultButton :href="route('admin.athletes.index')" class="mb-3">
                            <template #icon>
                                <!-- SVG -->
                                <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                                </svg>
                            </template>
                            {{ t('back') }}
                        </DefaultButton>
                        <PrimaryButton class="ms-4"
                                       :class="{ 'opacity-25': form.processing }"
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
