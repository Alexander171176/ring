<script setup>
import { ref, defineEmits, defineProps, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const emit = defineEmits(['update:images', 'delete-image']);

const previewImages = ref([]);

const props = defineProps({
    images: {
        type: Array,
        default: () => []
    }
});

// При изменении входящего массива используем уже готовые URL
watch(
    () => props.images,
    (newImages) => {
        previewImages.value = newImages.map((img) => ({
            id: img.id,
            url: img.url, // используем полученный URL, он должен быть вычислен в Edit.vue
            alt: img.alt || '',
            caption: img.caption || ''
        }));
    },
    {immediate: true}
);

const updateImages = () => {
    const images = previewImages.value.map((img) => ({
        id: img.id,
        alt: img.alt,
        caption: img.caption
    }));
    emit('update:images', images);
};

const removeImage = (index) => {
    const removedImage = previewImages.value[index];
    emit('delete-image', removedImage.id);
    previewImages.value.splice(index, 1);
    updateImages();
};
</script>

<template>
    <div class="multi-image-edit">
        <div v-if="previewImages.length" class="grid grid-cols-4 gap-4">
            <div
                v-for="(image, index) in previewImages"
                :key="image.id"
                class="relative border border-slate-500 rounded-sm py-0.5 px-2"
            >
                <img :src="image.url" alt="Existing Image" class="h-40 w-full object-cover"/>
                <input
                    v-model="image.alt"
                    @input="updateImages"
                    placeholder="Alt"
                    class="w-full my-2 py-0.5 px-2 text-sm border border-slate-500 rounded"
                />
                <input
                    v-model="image.caption"
                    @input="updateImages"
                    placeholder="Caption"
                    class="w-full mb-2 py-0.5 px-2 text-sm border border-slate-500 rounded"
                />
                <button
                    type="button"
                    @click="removeImage(index)"
                    class="absolute top-0 right-0 bg-rose-500 hover:bg-rose-700 text-white rounded-sm p-1"
                >
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
