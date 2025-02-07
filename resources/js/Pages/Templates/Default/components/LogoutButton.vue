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
                       px-2 py-1
                       text-center
                       2xl:text-lg text-sm
                       font-semibold
                       tracking-wide
                       leading-5
                       border border-solid border-slate-300
                       rounded-md
                       bg-slate-100
                       hover:bg-slate-50
                       focus:outline-none
                       focus:bg-slate-50
                       text-rose-700
                       hover:text-rose-500
                       focus:text-rose-500
                       active:text-rose-500
                       transition duration-150 ease-in-out">
            <slot/>
        </button>
    </div>
</template>
