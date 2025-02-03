<script setup>

defineProps({
    href: String,
    as: String
})
</script>

<template>
    <div>
        <button type="submit"
                class="block
                       w-fit
                       px-4 py-2
                       text-center
                       text-lg
                       font-semibold
                       tracking-wide
                       leading-5
                       border border-solid border-slate-300
                       rounded-md
                       text-slate-900
                       hover:bg-slate-100
                       focus:outline-none
                       focus:bg-slate-100
                       dark:text-slate-300
                       dark:hover:text-slate-700
                       dark:focus:text-slate-700
                       dark:active:text-slate-700
                       dark:focus:bg-slate-100
                       dark:active:bg-slate-100
                       transition duration-150 ease-in-out">
            <slot/>
        </button>
    </div>
</template>
