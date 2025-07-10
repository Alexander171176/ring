<script setup>
/**
 * @version PulsarCMS 1.0
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 */
import {useToast} from 'vue-toastification';
import {useI18n} from 'vue-i18n';
import { transliterate } from '@/utils/transliteration';
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
import VueMultiselect from "vue-multiselect";
import MetatagsButton from "@/Components/Admin/Buttons/MetatagsButton.vue";

// --- Инициализация ---
const toast = useToast();
const {t} = useI18n();

/**
 * Входные свойства компонента.
 */
const props = defineProps({
    images: Array,
    videos: Array,
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
    left: false,
    main: false,
    right: false,
    locale: '',
    name: '', // Название турнира
    url: '',
    short: '', // Краткое Описание
    description: '', // Описание
    meta_title: '',
    meta_keywords: '',
    meta_desc: '',
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
    videos: [],
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
 * Автоматически генерирует URL из поля name, если URL пуст.
 */
const handleUrlInputFocus = () => {
    if (form.name && form.tournament_date_time) {
        const namePart = transliterate(form.name.toLowerCase().replace(/\s+/g, '-'));
        const date = new Date(form.tournament_date_time);

        const datePart = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}-${String(date.getHours()).padStart(2, '0')}-${String(date.getMinutes()).padStart(2, '0')}`;

        form.url = `${namePart}-${datePart}`;
    }
};

/**
 * Обрезает текст до заданной длины, стараясь не разрывать слова при генерации мета-тегов.
 */
const truncateText = (text, maxLength, addEllipsis = false) => {
    if (text.length <= maxLength) return text;
    const truncated = text.substr(0, text.lastIndexOf(' ', maxLength));
    return addEllipsis ? `${truncated}...` : truncated;
};

/**
 * Генерирует значения для мета-полей (title, keywords, description),
 * если они не были заполнены вручную.
 */
const generateMetaFields = () => {
    // Генерация meta_title
    if (form.name && !form.meta_title) {
        form.meta_title = truncateText(form.name, 160); // Используем вашу функцию truncateText
    }

    // Генерация meta_keywords из form.short
    if (!form.meta_keywords && form.short) {
        // 1. Удаляем HTML-теги (на случай, если они есть в form.short)
        let text = form.short.replace(/(<([^>]+)>)/gi, "");

        // 2. Удаляем знаки препинания, кроме дефисов внутри слов (опционально)
        //    Оставляем буквы (включая кириллицу/другие языки), цифры, дефисы и пробелы
        text = text.replace(/[.,!?;:()\[\]{}"'«»]/g, ''); // Удаляем основную пунктуацию
        // text = text.replace(/[^\p{L}\p{N}\s-]/gu, ''); // Более строгий вариант: оставить только буквы, цифры, пробелы, дефис

        // 3. Разбиваем текст на слова по пробелам
        const words = text.split(/\s+/)
            // 4. Фильтруем пустые строки и короткие слова (например, менее 3 символов), если нужно
            .filter(word => word && word.length >= 3)
            // 5. Приводим к нижнему регистру (стандартно для ключевых слов)
            .map(word => word.toLowerCase())
            // 6. Удаляем дубликаты слов
            .filter((value, index, self) => self.indexOf(value) === index);

        // 7. Объединяем слова через запятую и пробел
        const keywords = words.join(', ');

        // 8. Обрезаем результат до максимальной длины (если нужно)
        form.meta_keywords = truncateText(keywords, 255); // Используем вашу функцию truncateText
    }

    // Генерация meta_desc из form.short
    if (form.short && !form.meta_desc) {
        // Убираем HTML-теги для описания
        const descText = form.short.replace(/(<([^>]+)>)/gi, "");
        form.meta_desc = truncateText(descText, 200, true); // Используем другую длину и добавление ...
    }
};

/**
 * Отправляет данные формы для создания.
 */
const submit = () => {
    form.transform((data) => ({
        ...data,
        activity: data.activity ? 1 : 0,
        left: data.left ? 1 : 0,
        main: data.main ? 1 : 0,
        right: data.right ? 1 : 0,
        is_title_fight: data.is_title_fight ? 1 : 0,
        images: form.images.map(image => {
            if (image.file) {
                return {file: image.file, order: image.order, alt: image.alt, caption: image.caption}; // Новое изображение
            }
            if (image.id) {
                return {id: Number(image.id), order: image.order, alt: image.alt, caption: image.caption}; // Существующее изображение
            }
        }).filter(Boolean), // Убираем undefined/null
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
                                <span class="text-sm text-red-500 dark:text-red-300 font-semibold mr-1">*</span>
                                <LabelInput for="tournament_date_time" :value="t('date')"
                                            class="mb-1 lg:mb-0 lg:mr-2"/>
                                <InputText
                                    id="tournament_date_time"
                                    type="datetime-local"
                                    v-model="form.tournament_date_time"
                                    autocomplete="tournament_date_time"
                                    required
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
                                <span class="text-sm text-red-500 dark:text-red-300 font-semibold mr-1">*</span>
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
                            <span class="text-sm text-red-500 dark:text-red-300 font-semibold mr-1">*</span>
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
                            <span class="text-sm text-red-500 dark:text-red-300 font-semibold mr-1">*</span>
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

                    <!-- Поле URL -->
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

                    <!-- Выбрать видео для показа -->
                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="videos" :value="t('videos')" class="mb-1"/>
                        <VueMultiselect v-model="form.videos"
                                        :options="videos"
                                        :multiple="true"
                                        :close-on-select="true"
                                        :placeholder="t('select')"
                                        label="title"
                                        track-by="title"
                        />
                    </div>

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
                                {{ form.meta_desc.length }} / 200 {{ t('characters') }}
                            </div>
                        </div>
                        <MetaDescTextarea v-model="form.meta_desc" maxlength="200" class="w-full"/>
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

<style src="../../../../css/vue-multiselect.min.css"></style>
