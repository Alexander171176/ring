<script setup>

import {Link} from "@inertiajs/vue3";

defineProps({
    href: String,
    as: String
})
</script>

<template>
    <div>
        <button type="submit"
                class="px-3 pb-0.5 text-sm font-semibold
                       text-slate-100 hover:text-yellow-200
                       dark:text-slate-100 dark:hover:text-yellow-200
                       focus:outline focus:outline-2 focus:rounded-sm
                       focus:outline-yellow-200 dark:focus:outline-yellow-200">
            <slot/>
        </button>
    </div>
</template>
