<script setup>
import {useI18n} from 'vue-i18n';
import {Link} from "@inertiajs/vue3";
import FooterMenuRubrics from "@/Components/Public/Default/Rubric/FooterMenuRubrics.vue";
const {t} = useI18n();
</script>

<template>
    <footer class="w-full px-4 py-6 bg-blue-950 dark:bg-gray-900 border-t border-dashed
                   border-gray-300 text-gray-300">

        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4
                    gap-8 pl-20 sm:pl-32 md:pl-16 lg:pl-24">

            <!-- Страницы -->
            <div>
                <h3 class="text-white text-sm font-bold mb-4 uppercase tracking-wide">
                    {{ t('pages') }}
                </h3>
                <ul class="space-y-1 text-sm">
                    <li>
                        <Link :href="route('home')" :title="t('home')"
                              class="flex items-center h-7
                                     text-sm font-semibold
                                     text-slate-300 hover:text-yellow-300
                                     dark:text-slate-300 dark:hover:text-yellow-300">
                            {{ t('home') }}
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('login')" :title="t('login')"
                              class="flex items-center h-7
                                     text-sm font-semibold
                                     text-slate-300 hover:text-yellow-300
                                     dark:text-slate-300 dark:hover:text-yellow-300">
                            {{ t('login') }}
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('register')" :title="t('register')"
                              class="flex items-center h-7
                                     text-sm font-semibold
                                     text-slate-300 hover:text-yellow-300
                                     dark:text-slate-300 dark:hover:text-yellow-300">
                            {{ t('register') }}
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('profile.show')" :title="t('profile')"
                              class="flex items-center h-7
                                     text-sm font-semibold
                                     text-slate-300 hover:text-yellow-300
                                     dark:text-slate-300 dark:hover:text-yellow-300">
                            {{ t('profile') }}
                        </Link>
                    </li>
                </ul>
            </div>

            <FooterMenuRubrics />

            <!-- Соцсети -->
            <div>
                <h3 class="text-white text-sm font-bold mb-4 uppercase tracking-wide">
                    {{ t('social') }}
                </h3>
                <ul class="space-y-1 text-sm">
                    <li>
                        <a href="https://www.instagram.com/ringkz_com/?utm_medium=copy_link"
                           class="flex items-center h-7
                                  text-sm font-semibold
                                  text-slate-300 hover:text-yellow-300
                                  dark:text-slate-300 dark:hover:text-yellow-300" target="_blank">
                            Instagram
                        </a>
                    </li>
                    <li>
                        <a href="https://twitter.com/ringkz_com?s=11"
                           class="flex items-center h-7
                                  text-sm font-semibold
                                  text-slate-300 hover:text-yellow-300
                                  dark:text-slate-300 dark:hover:text-yellow-300" target="_blank">
                            X - Twitter
                        </a>
                    </li>
                    <li>
                        <a href="https://www.facebook.com/diamondringboxing"
                           class="flex items-center h-7
                                  text-sm font-semibold
                                  text-slate-300 hover:text-yellow-300
                                  dark:text-slate-300 dark:hover:text-yellow-300" target="_blank">
                            Facebook
                        </a>
                    </li>
                    <li>
                        <a href="https://vk.com/diamondring"
                           class="flex items-center h-7
                                  text-sm font-semibold
                                  text-slate-300 hover:text-yellow-300
                                  dark:text-slate-300 dark:hover:text-yellow-300" target="_blank">
                            VK
                        </a>
                    </li>
                </ul>
            </div>

        </div>

    </footer>
</template>
