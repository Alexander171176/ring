<script setup>
import { ref, defineEmits, defineProps, watch } from 'vue';
import LabelInput from "@/Components/Admin/Input/LabelInput.vue";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const emit = defineEmits(['update:images']);
const previewImages = ref([]);

const props = defineProps({
    existingImages: {
        type: Array,
        default: () => [] // ✅ Поддержка существующих изображений
    }
});

// ✅ Заполняем массив previewImages при загрузке страницы
watch(() => props.existingImages, (newImages) => {
    previewImages.value = newImages.map(img => ({
        id: img.id,
        url: `/storage/article_images/${img.path}`,
        alt: img.alt || '',
        caption: img.caption || ''
    }));
}, { immediate: true });

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        if (!previewImages.value.some(img => img.file && img.file.name === file.name)) {
            const reader = new FileReader();
            reader.onload = () => {
                previewImages.value.push({
                    file,
                    url: reader.result,
                    alt: '',
                    caption: ''
                });
                updateImages();
            };
            reader.readAsDataURL(file);
        }
    });
};

const updateImages = () => {
    const images = previewImages.value.map(img => {
        if (img.file) {
            return { file: img.file, alt: img.alt, caption: img.caption }; // ✅ Новое изображение
        }
        if (img.id) {
            return { id: img.id, alt: img.alt, caption: img.caption }; // ✅ Существующее изображение
        }
    });

    console.log("✅ Отправляемые изображения:", images);
    emit('update:images', images);
};

const removeImage = (index) => {
    previewImages.value.splice(index, 1);
    updateImages();
};
</script>

<template>
    <div class="multi-image-upload">
        <LabelInput :value="t('uploadNewImages')"/>
        <input type="file" multiple @change="handleFileUpload" accept="image/*"
               class="block w-full text-md text-gray-700 dark:text-gray-100
                      file:mr-4 file:py-0.5 file:px-2 file:border-0 file:text-sm file:font-semibold
                      file:bg-blue-600 file:text-white hover:file:bg-blue-700"/>

        <div v-if="previewImages.length" class="mt-4 grid grid-cols-4 gap-4">
            <div v-for="(image, index) in previewImages" :key="index"
                 class="relative border border-slate-500 rounded-sm py-0.5 px-2">
                <img :src="image.url" :alt="t('view')" class="h-40 w-full object-cover"/>
                <input v-model="image.alt" @input="updateImages()" :placeholder="t('seoAltImage')"
                       class="w-full my-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded" />
                <input v-model="image.caption" @input="updateImages()" :placeholder="t('seoTitleImage')"
                       class="w-full mb-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded" />
                <button @click="removeImage(index)" :title="t('delete')"
                        class="absolute top-0 right-0 bg-rose-500 hover:bg-rose-700 text-white rounded-sm py-0.5 px-2">
                    &times;
                </button>
            </div>
        </div>
    </div>
</template>
