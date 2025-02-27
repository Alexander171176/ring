<script setup>
import { ref, defineEmits, defineProps, watch } from 'vue';
import LabelInput from "@/Components/Admin/Input/LabelInput.vue";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const emit = defineEmits(['update:images', 'update:deletedImages']);
const previewImages = ref([]);

// ✅ Принимаем существующие изображения
const props = defineProps({
    existingImages: {
        type: Array,
        default: () => []
    }
});

// ✅ Заполняем массив `previewImages` при загрузке
watch(() => props.existingImages, (newImages) => {
    previewImages.value = newImages.map(img => ({
        id: img.id,
        url: img.path ? `/storage/${img.path}` : null,
        alt: img.alt || '',
        caption: img.caption || ''
    }));
}, { immediate: true });

// ✅ Обновление изображений
const updateImages = () => {
    emit('update:images', previewImages.value);
};

// ✅ Удаление изображения
const removeImage = (index) => {
    const removedImage = previewImages.value[index];

    if (removedImage.id) {
        emit('update:deletedImages', removedImage.id); // 🔥 Передаём ID удалённого изображения
    }

    previewImages.value.splice(index, 1); // Удаляем только нужное изображение
    updateImages();
};
</script>

<template>
    <div class="multi-image-edit">
        <LabelInput :value="t('editImages')"/>
        <div v-if="previewImages.length" class="mt-4 grid grid-cols-4 gap-4">
            <div v-for="(image, index) in previewImages" :key="image.id"
                 class="relative border border-slate-500 rounded-sm py-0.5 px-2">
                <img v-if="image.url" :src="image.url" class="h-40 w-full object-cover" alt=""/>
                <input v-model="image.alt" @input="updateImages()" :placeholder="t('seoAltImage')"
                       class="w-full my-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded" />
                <input v-model="image.caption" @input="updateImages()" :placeholder="t('seoTitleImage')"
                       class="w-full mb-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded" />
                <button @click="removeImage(index)" :title="t('delete')"
                        class="absolute top-0 right-0 bg-rose-500 hover:bg-rose-700 text-white rounded-sm p-1">
                    <svg class="w-4 h-4 shrink-0 fill-current opacity-80" viewBox="0 0 16 16">
                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm3.5 10.1l-1.4 1.4L8 9.4l-2.1 2.1-1.4-1.4L6.6 8 4.5 5.9l1.4-1.4L8 6.6l2.1-2.1 1.4 1.4L9.4 8l2.1 2.1z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
