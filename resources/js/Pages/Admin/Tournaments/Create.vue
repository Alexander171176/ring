<script setup>
/**
 * @version PulsarCMS 1.0
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 */
import {useToast} from 'vue-toastification';
import {useI18n} from 'vue-i18n';
import {onMounted, ref} from "vue";
import {useForm} from '@inertiajs/vue3';
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
import TinyEditor from "@/Components/Admin/TinyEditor/TinyEditor.vue";
import MultiImageUpload from "@/Components/Admin/Image/MultiImageUpload.vue";
import SelectLocale from "@/Components/Admin/Select/SelectLocale.vue";
import TypeSelect from "@/Components/Admin/Tournament/Select/TypeSelect.vue";
import StatusSelect from "@/Components/Admin/Tournament/Select/StatusSelect.vue";
import SelectAthlete from "@/Components/Admin/Tournament/Select/SelectAthlete.vue";

// --- Инициализация ---
const toast = useToast();
const {t} = useI18n();

/**
 * Входные свойства компонента.
 */
const props = defineProps({
    images: Array,
    athletes: {
        type: Array,
        required: true
    }
});

/**
 * Форма для создания.
 */
const form = useForm({
    sort: '0',
    activity: false, // Активность
    locale: '',
    name: '', // Название турнира
    short: '', // Краткое Описание
    description: '', // Описание
    tournament_date_time: '', // Дата проведения
    status: null, // Статус
    venue: '', // Место проведения
    city: '', // Город проведения
    country: '', // Страна проведения
    weight_class_name: '', // Название весовой категории (например, "Тяжелый вес")
    rounds_scheduled: '0', // Количество запланированных раундов
    is_title_fight: false, // Является ли поединок титульным
    fighter_red_id: '', // Спортсмен в красном углу
    fighter_blue_id: '', // Спортсмен в синем углу
    winner_id: '', // указания победителя поединка
    method_of_victory: '', // Метод победы (например, "KO", "Submission")
    round_of_finish: '', // Раунд, в котором завершился поединок
    time_of_finish: '', // Время в раунде завершения поединка (например, "02:35")
    images: [] // Добавляем массив для загруженных изображений
});

/**
 * Функция форматирования даты.
 */
const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return '';
    return date.toISOString().slice(0, 16); // формат для datetime-local
};

/**
 * Монтируем формат даты.
 */
onMounted(() => {
    if (form.tournament_date_time) {
        form.tournament_date_time = formatDate(form.tournament_date_time);
    }
});

/**
 * Отправляет данные формы для создания.
 */
const submit = () => {
    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
        is_title_fight: data.is_title_fight ? 1 : 0,
    }));

    // console.log('📝 Отправка формы со следующими данными:', form.data());

    form.post(route('admin.tournaments.store'), {
        forceFormData: true,
        errorBag: 'createTournament',
        preserveScroll: true,
        onSuccess: (response) => {
            // console.log('✅ Сервер вернул успешный ответ:', response);
            toast.success('Турнир успешно создан!');
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
    <AdminLayout :title="t('addTournament')">
        <template #header>
            <TitlePage>
                {{ t('addTournament') }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700
                        border border-blue-400 dark:border-blue-200
                        shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка назад -->
                    <DefaultButton :href="route('admin.tournaments.index')">
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

                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <!-- Является ли поединок титульным -->
                        <div class="flex flex-row items-center gap-2 w-full">
                            <ActivityCheckbox v-model="form.is_title_fight"/>
                            <LabelCheckbox for="is_title_fight"
                                           :text="t('isTitleFight')"
                                           class="text-sm h-8 flex items-center"/>
                        </div>

                        <!-- Дата и время проведения -->
                        <div class="flex flex-row items-center justify-end w-full gap-2">
                            <div class="flex justify-start w-full">
                                <LabelInput for="tournament_date_time" :value="t('date')"
                                            class="mb-1 lg:mb-0 lg:mr-2"/>
                                <InputText
                                    id="tournament_date_time"
                                    type="datetime-local"
                                    v-model="form.tournament_date_time"
                                    autocomplete="tournament_date_time"
                                    class="w-full max-w-56"
                                />
                                <InputError class="mt-1 sm:mt-0" :message="form.errors.tournament_date_time"/>
                            </div>
                        </div>

                        <!-- статус Турнира -->
                        <StatusSelect v-model="form.status" :error="form.errors.status"/>

                    </div>

                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <!-- Поле Название весовой категории -->
                        <div class="flex flex-row items-center justify-end w-full">
                            <LabelInput for="weight_class_name" class="mt-4 mr-2">
                                {{ t('weightClassName') }}
                            </LabelInput>
                            <div class="mb-3 flex flex-col items-end w-96">
                                <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                    {{ form.weight_class_name.length }} / 100 {{ t('characters') }}
                                </div>
                                <InputText
                                    id="weight_class_name"
                                    type="text"
                                    v-model="form.weight_class_name"
                                    maxlength="100"
                                    autocomplete="weight_class_name"
                                    :placeholder="t('placeholderWeightClassName')"
                                />
                                <InputError class="mt-2" :message="form.errors.weight_class_name"/>
                            </div>
                        </div>

                        <!-- Количество запланированных раундов -->
                        <div class="flex flex-row items-center justify-end w-full">
                            <div class="h-8 flex items-center">
                                <LabelInput for="rounds_scheduled" :value="t('roundsScheduled')"
                                            class="mr-2 w-auto"/>
                            </div>
                            <InputNumber
                                id="rounds_scheduled"
                                type="number"
                                v-model="form.rounds_scheduled"
                                autocomplete="rounds_scheduled"
                                class="max-w-20"
                            />
                            <InputError class="mt-2 lg:mt-0" :message="form.errors.rounds_scheduled"/>
                        </div>

                    </div>

                    <div class="mb-3 flex justify-between flex-col lg:flex-row items-center gap-4">

                        <!-- Поле Страна -->
                        <div class="flex flex-row items-center justify-end w-full">
                            <LabelInput for="country" class="mt-4 mr-2">
                                {{ t('country') }}
                            </LabelInput>
                            <div class="mb-3 flex flex-col items-start w-96">
                                <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                    {{ form.country.length }} / 100 {{ t('characters') }}
                                </div>
                                <InputText
                                    class="max-w-80"
                                    id="country"
                                    type="text"
                                    v-model="form.country"
                                    maxlength="100"
                                    required
                                    autocomplete="country"
                                />
                                <InputError class="mt-2" :message="form.errors.country"/>
                            </div>
                        </div>

                        <!-- Поле Город -->
                        <div class="flex flex-row items-center justify-end w-full">
                            <LabelInput for="country" class="mt-4 mr-2">
                                {{ t('city') }}
                            </LabelInput>
                            <div class="mb-3 flex flex-col items-start w-96">
                                <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                    {{ form.city.length }} / 100 {{ t('characters') }}
                                </div>
                                <InputText
                                    class="max-w-80"
                                    id="city"
                                    type="text"
                                    v-model="form.city"
                                    maxlength="100"
                                    required
                                    autocomplete="city"
                                />
                                <InputError class="mt-2" :message="form.errors.city"/>
                            </div>
                        </div>

                    </div>

                    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <SelectAthlete
                            v-model="form.fighter_red_id"
                            :options="props.athletes"
                            :label="t('fighterRed')"
                            :error="form.errors.fighter_red_id"
                        />
                        <SelectAthlete
                            v-model="form.fighter_blue_id"
                            :options="props.athletes"
                            :label="t('fighterBlue')"
                            :error="form.errors.fighter_blue_id"
                        />
                        <SelectAthlete
                            v-model="form.winner_id"
                            :options="props.athletes"
                            :label="t('winner')"
                            :error="form.errors.winner_id"
                        />
                    </div>

                    <!-- Поле Имя -->
                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="name">
                                <span class="text-red-500 dark:text-red-300 font-semibold">*</span> {{ t('title') }}
                            </LabelInput>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.name.length }} / 255 {{ t('characters') }}
                            </div>
                        </div>
                        <InputText
                            id="name"
                            type="text"
                            v-model="form.name"
                            maxlength="255"
                            required
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name"/>
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

                    <!-- Изображения турнира -->
                    <MultiImageUpload @update:images="form.images = $event"/>

                    <div class="flex items-center justify-center mt-4">
                        <DefaultButton :href="route('admin.tournaments.index')" class="mb-3">
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
