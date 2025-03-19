<script setup>
import { ref, defineEmits, defineProps, watch } from 'vue';
import LabelInput from "@/Components/Admin/Input/LabelInput.vue";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

//  defineEmits  определяет, какие события компонент может *отправлять* родительскому компоненту.
const emit = defineEmits(['update:images', 'update:deletedImages']);

// previewImages  -  локальный реактивный массив,
// который используется для *отображения* изображений в компоненте
// и для отслеживания изменений (редактирование alt и caption).
const previewImages = ref([]);

//  defineProps  определяет, какие *входные* данные (свойства, props) компонент ожидает от родителя.
const props = defineProps({
    existingImages: {
        type: Array,
        default: () => []  //  Пустой массив по умолчанию
    }
});

//  watch  -  это "наблюдатель" (watcher).  Он следит за изменениями
//  в  props.existingImages  (т.е., за изменениями массива существующих изображений).
//  Когда  props.existingImages  изменяется (родителем), выполняется функция,
//  переданная в  watch.  { immediate: true }  означает, что функция
//  выполнится *сразу* при создании компонента (а не только при *изменении*  props.existingImages).
watch(() => props.existingImages, (newImages) => {
    //  Копируем  newImages  в  previewImages.  Используем  map,
    //  чтобы создать *новый* массив (а не просто ссылку на старый).
    //  Это важно для реактивности Vue.
    previewImages.value = newImages.map(img => ({
        id: img.id,           //  ID  существующего изображения (обязательно!).
        url: img.url,         //  URL  изображения (для тега  <img>).
        alt: img.alt || '',     //  Alt  (если нет, то пустая строка).
        caption: img.caption || '' //  Caption  (если нет, то пустая строка).
    }));
    console.log("MultiImageEdit.vue watch props.existingImages:", previewImages.value); // Лог
}, {immediate: true, deep: true}); // deep: true -  чтобы отслеживать изменения внутри объектов массива.

//  updateImages  -  эта функция вызывается, когда пользователь
//  изменяет  alt  или  caption  у изображения.
const updateImages = () => {
    //  Формируем новый массив  allImages  из  previewImages.
    //  Этот массив будет отправлен родителю.
    const allImages = previewImages.value.map(image => ({
        id: image.id ? Number(image.id) : undefined, //  Преобразуем  id  в число.
        url: image.url,  // url нужен
        alt: image.alt,
        caption: image.caption
    }));

    console.log("MultiImageEdit updateImages:", allImages); // Лог
    //  Отправляем событие  update:images  родителю (Edit.vue).
    //  Передаем  allImages  как аргумент.
    emit('update:images', allImages);
};

//  removeImage  -  эта функция вызывается, когда пользователь
//  нажимает кнопку "удалить" у изображения.
const removeImage = (index) => {
    //  Получаем удаляемое изображение из  previewImages  по индексу.
    const removedImage = previewImages.value[index];

    //  Если у изображения есть  id  (т.е. оно *существующее*),
    //  то отправляем событие  update:deletedImages  родителю,
    //  передавая  id  удаленного изображения.
    if (removedImage.id) {
        emit('update:deletedImages', removedImage.id);
    }

    //  Удаляем изображение из  previewImages  по индексу.
    previewImages.value.splice(index, 1);

    //  Вызываем  updateImages, чтобы отправить родителю
    //  обновленный список изображений (без удаленного).
    updateImages();
};

</script>

<template>
    <div class="multi-image-edit">
        <LabelInput :value="t('editImages')"/>

        <div v-if="previewImages.length" class="mt-4 grid grid-cols-4 gap-4">
            <div v-for="(image, index) in previewImages" :key="index"
                 class="relative border border-slate-500 rounded-sm py-0.5 px-2">
                <!-- Отображаем изображение, если есть image.url -->
                <img v-if="image.url" :src="image.url" class="h-40 w-full object-cover" alt=""/>

                <!-- Поле ввода для alt -->
                <input v-model="image.alt" @input="updateImages" :placeholder="t('seoAltImage')"
                       class="w-full my-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded"/>

                <!-- Поле ввода для caption -->
                <input v-model="image.caption" @input="updateImages" :placeholder="t('seoTitleImage')"
                       class="w-full mb-2 py-0.5 px-2 text-sm font-semibold border border-slate-500 rounded"/>

                <!-- Кнопка удаления -->
                <button @click="removeImage(index)" :title="t('delete')"
                        class="absolute top-0 right-0 bg-rose-500 hover:bg-rose-700 text-white rounded-sm p-1">
                    <svg class="w-4 h-4 shrink-0 fill-current opacity-80" viewBox="0 0 16 16">
                        <path
                            d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm3.5 10.1l-1.4 1.4L8 9.4l-2.1 2.1-1.4-1.4L6.6 8 4.5 5.9l1.4-1.4L8 6.6l2.1-2.1 1.4 1.4L9.4 8l2.1 2.1z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
