<template>
    <div class="flex items-center gap-3 px-3 pb-0.5">
        <!-- Instagram -->
        <a href="https://www.instagram.com/ringkz_com/?utm_medium=copy_link"
           title="Instagram" class="social-icon group">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                 class="transition duration-300 group-hover:scale-110 text-white hover:text-blue-600">
                <path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z"/>
                <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                <path d="M16.5 7.5l0 .01"/>
            </svg>
        </a>

        <!-- X (Twitter) -->
        <a href="https://twitter.com/ringkz_com?s=11"
           title="X (Twitter)" class="social-icon group">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                 class="transition duration-300 group-hover:scale-110 text-white hover:text-blue-600">
                <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
                <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
            </svg>
        </a>

        <!-- Facebook -->
        <a href="https://www.facebook.com/diamondringboxing"
           title="Facebook" class="social-icon group">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                 class="transition duration-300 group-hover:scale-110 text-white hover:text-blue-600">
                <path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3" />
            </svg>
        </a>

        <!-- VK -->
        <a href="https://vk.com/diamondring"
           title="VK" class="social-icon group">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                 class="transition duration-300 group-hover:scale-110 text-white hover:text-blue-600">
                <path d="M14 19h-4a8 8 0 0 1 -8 -8v-5h4v5a4 4 0 0 0 4 4h0v-9h4v4.5l.03 0a4.531 4.531 0 0 0 3.97 -4.496h4l-.342 1.711a6.858 6.858 0 0 1 -3.658 4.789h0a5.34 5.34 0 0 1 3.566 4.111l.434 2.389h0h-4a4.531 4.531 0 0 0 -3.97 -4.496v4.5z" />
            </svg>
        </a>
    </div>
</template>

<script setup lang="ts">
// Ничего не нужно, иконки статичны
</script>

<style scoped>
.social-icon {
    color: white;
    transition: color 0.3s ease, transform 0.3s ease;
}
.social-icon:hover,
.social-icon:focus {
    color: #facc15; /* жёлтый-500 */
    outline: none;
}
</style>
