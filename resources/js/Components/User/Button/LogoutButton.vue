<script setup>

defineProps({
    href: String,
    as: String
})
</script>

<template>
    <div>
        <button type="submit"
                class="btn px-3 py-1 whitespace-nowrap
                       text-center font-semibold
                       border border-solid border-slate-300 rounded-sm
                       text-slate-100 hover:text-slate-300
                       focus:text-slate-300 active:text-orange-500
                       focus:outline-none
                       transition duration-150 ease-in-out">
            <slot/>
        </button>
    </div>
</template>
