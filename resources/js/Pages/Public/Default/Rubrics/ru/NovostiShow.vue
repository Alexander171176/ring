<script setup>
defineProps({
    rubric: Object,
    sections: Array,
});
</script>

<template>
    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-4">{{ rubric.title }}</h1>

        <div v-if="sections.length">
            <h2 class="text-xl font-semibold mb-2">Связанные секции:</h2>
            <ul class="list-disc pl-6">
                <li v-for="section in sections" :key="section.id">
                    {{ section.title }}
                </li>
            </ul>
        </div>
        <div v-else>
            <p>Нет связанных секций.</p>
        </div>
    </div>
</template>
