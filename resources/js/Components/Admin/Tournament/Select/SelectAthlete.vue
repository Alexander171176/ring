<script setup>
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

defineProps({
    modelValue: [Number, String],
    error: String,
    label: String,
    options: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (event) => {
    const value = parseInt(event.target.value);
    emit('update:modelValue', isNaN(value) ? '' : value);
};
</script>

<template>
    <div class="w-full">
        <label class="mr-2 font-medium text-sm text-indigo-600 dark:text-sky-500">
            {{ label }}
        </label>
        <div class="relative">
            <select
                :value="modelValue"
                @change="updateValue"
                class="py-1 form-select w-full rounded shadow-sm border-slate-500 dark:bg-cyan-800 dark:text-slate-100"
            >
                <option value="">{{ t('select') }}</option>
                <option
                    v-for="athlete in options"
                    :key="athlete.id"
                    :value="athlete.id"
                >
                    {{ athlete.nickname }}
                </option>
            </select>
        </div>

        <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
    </div>
</template>
