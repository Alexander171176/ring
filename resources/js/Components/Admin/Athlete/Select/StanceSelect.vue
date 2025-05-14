<script setup>
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

defineProps({
    modelValue: String,
    error: String,
});

const emit = defineEmits(['update:modelValue']);

const options = [
    {value: 'orthodox', labelKey: 'stanceOrthodox'},
    {value: 'southpaw', labelKey: 'stanceSouthpaw'},
    {value: 'switch', labelKey: 'stanceSwitch'},
];

const update = (e) => emit('update:modelValue', e.target.value);
</script>

<template>
    <div class="flex flex-row items-center gap-2">
        <label for="stance"
               class="mr-2 font-medium text-sm text-indigo-600 dark:text-sky-500">
            {{ t('stanceLabel') }}
        </label>
        <select
            id="stance"
            :value="modelValue"
            @change="update"
            class="py-0.5 form-select w-auto rounded-sm shadow-sm
                   border-slate-500 dark:bg-slate-800 dark:text-white"
        >
            <option value="" disabled>{{ t('stancePlaceholder') }}</option>
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
