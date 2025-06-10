<!-- MaintenanceModal.vue -->
<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-6 w-full max-w-md text-center">
            <h2 class="text-xl text-orange-500 dark:text-slate-100 mb-2">
                {{ t('technicalWorkTitle') }}
            </h2>
            <span class="text-teal-600 text-lg font-semibold">
                {{ t('technicalWorkText') }}
                <span class="text-gray-500 block mt-2">{{ t('technicalWorkThanks') }}</span>
            </span>
            <button
                @click="close"
                class="mt-4 px-4 py-1 bg-indigo-600 hover:bg-indigo-700 text-sm text-white rounded transition"
            >
                {{ t('close') }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const show = ref(true)

function close() {
    show.value = false
}
</script>
