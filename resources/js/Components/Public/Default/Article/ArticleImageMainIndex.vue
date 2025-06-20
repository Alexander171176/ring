<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    images: {
        type: Array,
        required: true,
    },
    link: {
        type: String,
        default: '',
    }
});

const firstImage = props.images[0];
</script>

<template>
    <div class="relative w-full h-full">
        <!-- Обёртка в ссылку при наличии -->
        <Link v-if="link" :href="link" class="block w-full h-full">
            <img
                :src="firstImage.webp_url || firstImage.url"
                :alt="firstImage.alt"
                class="w-full h-full object-cover rounded-md"
            />
        </Link>
        <img
            v-else
            :src="firstImage.webp_url || firstImage.url"
            :alt="firstImage.alt"
            class="w-full h-full object-cover rounded-md"
        />
    </div>
</template>
