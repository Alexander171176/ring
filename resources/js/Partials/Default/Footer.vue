<script setup>
import {useI18n} from 'vue-i18n';
import {Link} from "@inertiajs/vue3";
import FooterMenuRubrics from "@/Components/Public/Default/Rubric/FooterMenuRubrics.vue";

const {t} = useI18n();

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>

<template>
    <footer class="bg-slate-50 dark:bg-blue-950 pt-2">

        <div class="mx-8 pt-6 border border-gray-700 dark:border-gray-300 rounded-t-2xl">
            <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4
                    gap-8 pl-20 sm:pl-32 md:pl-16 lg:pl-24">

                <FooterMenuRubrics/>

                <!-- Соцсети -->
                <div>
                    <h3 class="text-slate-700 dark:text-slate-100 text-sm font-bold mb-4">
                        {{ t('social') }}
                    </h3>
                    <ul class="space-y-1 text-sm">
                        <li>
                            <a href="https://www.instagram.com/ringkz_com/?utm_medium=copy_link"
                               title="Instagram"
                               class="flex items-center h-7 text-sm font-semibold text-slate-700 hover:text-red-500
                                      dark:text-slate-300 dark:hover:text-yellow-300" target="_blank">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="32"
                                    height="32"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" />
                                    <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                    <path d="M16.5 7.5l0 .01" />
                                </svg>

                            </a>
                        </li>
                        <li>
                            <a href="https://www.facebook.com/diamondringboxing"
                               title="Facebook"
                               class="flex items-center h-7 text-sm font-semibold text-slate-700 hover:text-red-500
                                      dark:text-slate-300 dark:hover:text-yellow-300" target="_blank">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="28"
                                    height="28"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3" />
                                </svg>

                            </a>
                        </li>
                        <li>
                            <a href="https://twitter.com/ringkz_com?s=11"
                               title="X - Twitter"
                               class="flex items-center h-7 text-sm font-semibold text-slate-700 hover:text-red-500
                                      dark:text-slate-300 dark:hover:text-yellow-300" target="_blank">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="28"
                                    height="28"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
                                    <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
                                </svg>

                            </a>
                        </li>
                        <li>
                            <a href="https://vk.com/diamondring"
                               title="VK"
                               class="flex items-center h-7 text-sm font-semibold text-slate-700 hover:text-red-500
                                      dark:text-slate-300 dark:hover:text-yellow-300" target="_blank">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="32"
                                    height="32"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M14 19h-4a8 8 0 0 1 -8 -8v-5h4v5a4 4 0 0 0 4 4h0v-9h4v4.5l.03 0a4.531 4.531 0 0 0 3.97 -4.496h4l-.342 1.711a6.858 6.858 0 0 1 -3.658 4.789h0a5.34 5.34 0 0 1 3.566 4.111l.434 2.389h0h-4a4.531 4.531 0 0 0 -3.97 -4.496v4.5z" />
                                </svg>

                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Страницы -->
                <div>
                    <h3 class="text-slate-700 dark:text-slate-100 text-sm font-bold mb-4">
                        {{ t('pages') }}
                    </h3>
                    <ul class="space-y-1 text-sm">
                        <li>
                            <Link :href="route('home')" :title="t('home')"
                                  class="flex items-center h-7
                                     text-sm font-semibold
                                     text-slate-700 hover:text-red-500
                                     dark:text-slate-300 dark:hover:text-yellow-300">
                                {{ t('home') }}
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('login')" :title="t('login')"
                                  class="flex items-center h-7
                                     text-sm font-semibold
                                     text-slate-700 hover:text-red-500
                                     dark:text-slate-300 dark:hover:text-yellow-300">
                                {{ t('login') }}
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('register')" :title="t('register')"
                                  class="flex items-center h-7
                                     text-sm font-semibold
                                     text-slate-700 hover:text-red-500
                                     dark:text-slate-300 dark:hover:text-yellow-300">
                                {{ t('register') }}
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('profile.show')" :title="t('profile')"
                                  class="flex items-center h-7
                                     text-sm font-semibold
                                     text-slate-700 hover:text-red-500
                                     dark:text-slate-300 dark:hover:text-yellow-300">
                                {{ t('profile') }}
                            </Link>
                        </li>
                    </ul>
                </div>

            </div>
            <!-- Изображение -->
            <div class="w-full mt-4 text-center">
                <button
                    @click="scrollToTop"
                    class="w-full"
                    title="Наверх"
                >
                    <img
                        src="/storage/image-footer.jpg"
                        alt="Footer Banner"
                        class="w-full h-fit object-contain cursor-pointer transition hover:opacity-80"
                    />
                </button>
            </div>
        </div>
    </footer>
</template>
