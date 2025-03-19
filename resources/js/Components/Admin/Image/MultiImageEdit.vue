<script setup>
import { ref, defineEmits, defineProps, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import LabelInput from "@/Components/Admin/Input/LabelInput.vue";

const { t } = useI18n();
const props = defineProps({
    images: {
        type: Array,
        default: () => []
    }
});
const emit = defineEmits(['update:images', 'delete-image']);

// Создаем локальную копию изображений
const localImages = ref([]);

watch(
    () => props.images,
    (newImages) => {
        localImages.value = newImages.map(img => ({
            id: img.id,
            url: img.url, // ожидаем, что URL уже вычислен в родителе
            alt: img.alt || '',
            caption: img.caption || ''
        }));
    },
    { immediate: true }
);

const updateImages = () => {
    // Эмитируем обновленный локальный массив для родителя
    emit('update:images', localImages.value);
};

const removeImage = (index) => {
    const removedImage = localImages.value[index];
    emit('delete-image', removedImage.id);
    localImages.value.splice(index, 1);
    updateImages();
};
</script>

<template>
    <div class="multi-image-edit">
        <LabelInput :value="t('editImages')"/>
        <div v-if="localImages.length" class="grid grid-cols-4 gap-4">
            <div v-for="(image, index) in localImages" :key="image.id" class="relative border border-slate-500 rounded-sm py-0.5 px-2">
                <img :src="image.url" alt="Existing Image" class="h-40 w-full object-cover" />
                <input
                    v-model="image.alt"
                    @input="updateImages"
                    :placeholder="t('seoAltImage')"
                    class="w-full my-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded"
                />
                <input
                    v-model="image.caption"
                    @input="updateImages"
                    :placeholder="t('seoTitleImage')"
                    class="w-full mb-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded"
                />
                <button type="button" @click="removeImage(index)" class="absolute top-0 right-0 bg-rose-500 hover:bg-rose-700 text-white rounded-sm p-1">
                    <svg class="w-4 h-4 fill-current opacity-80" viewBox="0 0 16 16">
                        <path
                            d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm3.5 10.1l-1.4 1.4L8 9.4l-2.1 2.1-1.4-1.4L6.6 8 4.5 5.9l1.4-1.4L8 6.6l2.1-2.1 1.4 1.4L9.4 8l2.1 2.1z"
                        ></path>
                    </svg>
                </button>
            </div>
        </div>
        <div v-else>
            <p>{{ t('noData') }}</p>
        </div>
    </div>
</template>
