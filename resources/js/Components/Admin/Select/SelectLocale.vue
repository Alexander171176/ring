<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import { useI18n } from 'vue-i18n';
import LabelInput from "@/Components/Admin/Input/LabelInput.vue";
import InputError from "@/Components/Admin/Input/InputError.vue";

const { t } = useI18n();

const locales = ref([
    { label: 'English', value: 'en' },
    { label: 'Русский', value: 'ru' },
    { label: 'Kazakh', value: 'kz' }
]);

const props = defineProps({
    modelValue: String, // Двустороннее связывание данных
    errorMessage: String // Сообщение об ошибке
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="flex flex-row items-center gap-2 w-auto">
        <div class="h-8 flex items-center">
            <LabelInput for="locale" :value="t('localization')" class="text-sm"/>
        </div>
        <select
            id="locale"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
            class="block w-full py-0.5 border-slate-500 font-semibold text-sm focus:border-indigo-500
                   focus:ring-indigo-300 rounded-sm shadow-sm dark:bg-cyan-800 dark:text-slate-100">
            <option value="" disabled>{{ t('selectLocale') }}</option>
            <option v-for="locale in locales" :key="locale.value" :value="locale.value">
                {{ locale.label }}
            </option>
        </select>
        <InputError class="mt-2 lg:mt-0" :message="errorMessage"/>
    </div>
</template>
