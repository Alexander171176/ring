<script setup>
import { ref, defineEmits, defineProps } from 'vue';
import LabelInput from "@/Components/Admin/Input/LabelInput.vue";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const emit = defineEmits(['update:images', 'remove-existing-image']); //  Убрали  update:deletedImages
const previewImages = ref([]);
const fileInput = ref(null);

const props = defineProps({
    existingImages: {
        type: Array,
        default: () => []
    }
});

//  Инициализируем  previewImages  сразу при создании компонента
previewImages.value = props.existingImages.map(img => ({
    id: img.id,
    url: img.url,
    alt: img.alt || '',
    caption: img.caption || '',
    isExisting: img.isExisting?? true,
}));

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
                    caption: '',
                    isExisting: false //  Ключевой момент
                });
                updateImages();
            };
            reader.readAsDataURL(file);
        }
    });
    console.log("MultiImageManager handleFileUpload:", event.target.files);
};

const updateImages = () => {
    const allImages = previewImages.value.map(image => {
        if (image.isExisting) {
            return {
                id: Number(image.id),
                alt: image.alt,
                caption: image.caption,
                url: image.url,
            };
        } else {
            return {
                file: image.file,
                alt: image.alt,
                caption: image.caption,
                url: image.url,
            };
        }
    });

    console.log("MultiImageManager updateImages:", allImages);
    emit('update:images', allImages);
};

const removeImage = (index) => {
    const removedImage = previewImages.value[index];

    if (removedImage.isExisting) {
        // Эмитим событие удаления для родительского компонента
        emit('remove-existing-image', removedImage.id);
        // Удаляем изображение из локального массива, чтобы UI обновился
        previewImages.value.splice(index, 1);
        updateImages();
    } else {
        // Для новых изображений просто удаляем их и обновляем
        previewImages.value.splice(index, 1);
        updateImages();
    }

    // Если требуется, сбрасываем input
    if (fileInput.value) {
        fileInput.value.value = null;
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};

</script>

<template>
    <div class="multi-image-manager">
        <LabelInput :value="t('addImage')"/>

        <button type="button" @click="triggerFileInput"
                class="mt-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            {{ t('addImage') }}
        </button>
        <input type="file" ref="fileInput" style="display: none" multiple @change="handleFileUpload" accept="image/*">

        <div v-if="previewImages.length" class="mt-4 grid grid-cols-4 gap-4">
            <div v-for="(image, index) in previewImages" :key="index"
                 class="relative border border-slate-500 rounded-sm py-0.5 px-2">
                <img :src="image.url" class="h-40 w-full object-cover"  alt=""/>
                <input v-model="image.alt" @input="updateImages" :placeholder="t('seoAltImage')"
                       class="w-full my-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded" />
                <input v-model="image.caption" @input="updateImages" :placeholder="t('seoTitleImage')"
                       class="w-full mb-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded" />
                <button type="button" @click="removeImage(index)" :title="t('delete')"
                        class="absolute top-0 right-0 bg-rose-500 hover:bg-rose-700 text-white rounded-sm p-1">
                    <svg class="w-4 h-4 shrink-0 fill-current opacity-80" viewBox="0 0 16 16">
                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm3.5 10.1l-1.4 1.4L8 9.4l-2.1 2.1-1.4-1.4L6.6 8 4.5 5.9l1.4-1.4L8 6.6l2.1-2.1 1.4 1.4L9.4 8l2.1 2.1z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
