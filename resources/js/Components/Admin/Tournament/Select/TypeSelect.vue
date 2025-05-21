<script setup>
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

defineProps({
    modelValue: String,
    error: String,
});

const emit = defineEmits(['update:modelValue']);

const options = [
    {value: 'boxing_bout', labelKey: 'typeSelectBoxingBout'},
    {value: 'mma_tournament', labelKey: 'typeSelectMmaTournament'},
    {value: 'press_conference', labelKey: 'typeSelectPressConference'},
];

const update = (e) => emit('update:modelValue', e.target.value);
</script>

<template>
    <div class="flex flex-row items-center gap-2">
        <label for="stance"
               class="mr-2 font-medium text-sm text-indigo-600 dark:text-sky-500">
            {{ t('type') }}
        </label>
        <select
            id="stance"
            :value="modelValue"
            @change="update"
            class="py-0.5 form-select w-auto rounded-sm shadow-sm
                   border-slate-500 dark:bg-slate-800 dark:text-white"
        >
            <option value="" disabled>{{ t('notSelected') }}</option>
            <option
                v-for="opt in options"
                :key="opt.value"
                :value="opt.value"
                :selected="opt.value === modelValue"
                :class="{
                    'bg-blue-500 text-white': opt.value === modelValue,
                    'bg-white text-gray-800 dark:bg-slate-700 dark:text-gray-100': opt.value !== modelValue,
                }"
            >
                {{ t(opt.labelKey) }}
            </option>
        </select>
        <p v-if="error" class="mt-2 text-sm text-red-500">{{ error }}</p>
    </div>
</template>
