<script setup>
import { ref, watch } from 'vue'
import LabelInput from '@/Components/Admin/Input/LabelInput.vue'
import InputError from '@/Components/Admin/Input/InputError.vue'

const props = defineProps({
    modelValue: File,
    currentAvatar: {
        type: String,
        default: null
    },
    error: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue'])

const previewUrl = ref(null)

watch(
    () => props.modelValue,
    (file) => {
        if (file instanceof File) {
            previewUrl.value = URL.createObjectURL(file)
        } else {
            previewUrl.value = null
        }
    },
    { immediate: true }
)

const handleChange = (e) => {
    const file = e.target.files[0]
    emit('update:modelValue', file)
}
</script>

<template>
    <div class="mb-3 flex flex-col items-start">
        <LabelInput for="avatar-upload" value="Avatar" />

        <!-- Предпросмотр нового аватара -->
        <div v-if="previewUrl" class="mb-2">
            <img :src="previewUrl" alt="Preview"
                 class="w-32 h-32 object-cover rounded border border-gray-300 dark:border-gray-600" />
        </div>

        <!-- Текущий аватар -->
        <div v-else-if="currentAvatar" class="mb-2">
            <img :src="`/storage/${currentAvatar}`" alt="Current Avatar"
                 class="w-32 h-32 object-cover rounded border border-gray-300 dark:border-gray-600" />
        </div>

        <input
            id="avatar-upload"
            type="file"
            accept="image/png"
            @change="handleChange"
            class="mt-1 text-sm text-gray-700 dark:text-gray-300"
        />

        <InputError class="mt-2" :message="error" />
    </div>
</template>
