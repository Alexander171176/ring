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

const defaultImage = '/storage/athlete_images/default-image.png';
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

            <!-- Показываем текущий аватар справа от select -->
            <div v-if="modelValue" class="absolute top-0 right-1">
                <img
                    :src="(options.find(a => a.id === modelValue)?.avatar) || defaultImage"
                    alt="avatar"
                    class="w-8 h-8 rounded-full object-cover border"
                />
            </div>
        </div>

        <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
    </div>
</template>
