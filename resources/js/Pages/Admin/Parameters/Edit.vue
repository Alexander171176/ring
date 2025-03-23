<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TitlePage from '@/Components/Admin/Headlines/TitlePage.vue'
import DefaultButton from '@/Components/Admin/Buttons/DefaultButton.vue'
import LabelInput from '@/Components/Admin/Input/LabelInput.vue'
import InputText from '@/Components/Admin/Input/InputText.vue'
import InputError from '@/Components/Admin/Input/InputError.vue'
import PrimaryButton from '@/Components/Admin/Buttons/PrimaryButton.vue'
import MetaDescTextarea from '@/Components/Admin/Textarea/MetaDescTextarea.vue';
import LabelCheckbox from '@/Components/Admin/Checkbox/LabelCheckbox.vue';
import ActivityCheckbox from '@/Components/Admin/Checkbox/ActivityCheckbox.vue';
import { useI18n } from 'vue-i18n';
import { ref, watch } from 'vue';

const { t } = useI18n();

const props = defineProps({
    setting: {
        type: Object,
        default: () => ({})
    },
})

const form = useForm({
    _method: 'PUT',
    type: ref(props.setting?.type ?? ''),
    option: ref(props.setting?.option ?? ''),
    value: ref(props.setting?.value ?? ''),
    constant: ref(props.setting?.constant ?? ''),
    category: ref(props.setting?.category ?? ''),
    description: ref(props.setting?.description ?? ''),
    activity: ref(Boolean(props.setting?.activity ?? false)),
})

// Функция для преобразования camelCase в UPPER_CASE с нижним подчёркиванием
const toUpperCaseWithUnderscore = (str) => {
    return str.replace(/([a-z0-9])([A-Z])/g, '$1_$2').toUpperCase();
};

// Обработчик фокуса на поле constant
const handleConstantFocus = () => {
    if (form.option) {
        form.constant = toUpperCaseWithUnderscore(form.option);
    }
};

// Watcher для отслеживания изменений в props.setting
watch(() => props.setting, (newSetting) => {
    form.type = newSetting?.type ?? '';
    form.option = newSetting?.option ?? '';
    form.value = newSetting?.value ?? '';
    form.constant = newSetting?.constant ?? '';
    form.category = newSetting?.category ?? '';
    form.description = newSetting?.description ?? '';
    form.activity = Boolean(newSetting?.activity ?? false);
}, { immediate: true });
</script>

<template>
    <AdminLayout :title="t('editParameter')">
        <template #header>
            <TitlePage>
                {{ t('editParameter') }} ID:{{ props.setting?.id }}
            </TitlePage>
        </template>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-12xl mx-auto">
            <div class="p-4 bg-slate-50 dark:bg-slate-700
                        border border-blue-400 dark:border-blue-200
                        shadow-lg shadow-gray-500 dark:shadow-slate-400
                        bg-opacity-95 dark:bg-opacity-95">
                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Кнопка назад -->
                    <DefaultButton :href="route('parameters.index')">
                        <template #icon>
                            <!-- SVG -->
                            <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                <path d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                            </svg>
                        </template>
                        {{ t('back') }}
                    </DefaultButton>

                    <!-- Right: Actions -->
                    <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                        <!-- Datepicker built with flatpickr -->
                    </div>
                </div>
                <form @submit.prevent="form.put(route('parameters.update', { id: props.setting.id }))"
                      class="p-3 w-full">

                    <div class="mb-3 flex items-center">
                        <div class="flex justify-between w-full">
                            <div class="flex flex-row items-center">
                                <ActivityCheckbox v-model="form.activity"/>
                                <LabelCheckbox for="activity" :text="t('activity')"/>
                            </div>
                        </div>
                        <div class="flex flex-row items-center">
                            <LabelInput for="type" :value="t('type')" class="mr-3"/>
                            <InputText
                                id="type"
                                type="text"
                                v-model="form.type"
                                autocomplete="type"
                            />
                            <InputError class="mt-2" :message="form.errors.type"/>
                        </div>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="option" :value="t('parameterName')"/>
                        <InputText
                            id="option"
                            type="text"
                            v-model="form.option"
                            required
                            autocomplete="option"
                        />
                        <InputError class="mt-2" :message="form.errors.option"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="constant" :value="t('parameterConstant')"/>
                        <InputText
                            id="constant"
                            type="text"
                            v-model="form.constant"
                            @focus="handleConstantFocus"
                            required
                            autocomplete="constant"
                        />
                        <InputError class="mt-2" :message="form.errors.constant"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="value" :value="t('parameterValue')"/>
                        <InputText
                            id="value"
                            type="text"
                            v-model="form.value"
                            autocomplete="value"
                        />
                        <InputError class="mt-2" :message="form.errors.value"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <LabelInput for="category" :value="t('parameterCategory')"/>
                        <InputText
                            id="category"
                            type="text"
                            v-model="form.category"
                            autocomplete="category"
                        />
                        <InputError class="mt-2" :message="form.errors.category"/>
                    </div>

                    <div class="mb-3 flex flex-col items-start">
                        <div class="flex justify-between w-full">
                            <LabelInput for="description" :value="t('parameterDescription')"/>
                            <div class="text-md text-gray-900 dark:text-gray-400 mt-1">
                                {{ form.description.length }} / 255 {{ t('characters') }}
                            </div>
                        </div>
                        <MetaDescTextarea v-model="form.description" class="w-full"/>
                        <InputError class="mt-2" :message="form.errors.description"/>
                    </div>

                    <div class="flex items-center justify-center mt-4">
                        <DefaultButton :href="route('parameters.index')" class="mb-3">
                            <template #icon>
                                <!-- SVG -->
                                <svg class="w-4 h-4 fill-current text-slate-100 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path d="M4.3 4.5c1.9-1.9 5.1-1.9 7 0 .7.7 1.2 1.7 1.4 2.7l2-.3c-.2-1.5-.9-2.8-1.9-3.8C10.1.4 5.7.4 2.9 3.1L.7.9 0 7.3l6.4-.7-2.1-2.1zM15.6 8.7l-6.4.7 2.1 2.1c-1.9 1.9-5.1 1.9-7 0-.7-.7-1.2-1.7-1.4-2.7l-2 .3c.2 1.5.9 2.8 1.9 3.8 1.4 1.4 3.1 2 4.9 2 1.8 0 3.6-.7 4.9-2l2.2 2.2.8-6.4z"></path>
                                </svg>
                            </template>
                            {{ t('back') }}
                        </DefaultButton>
                        <PrimaryButton class="ms-4 mb-0"
                                       :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
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
