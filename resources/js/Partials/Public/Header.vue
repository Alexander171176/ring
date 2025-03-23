<script setup>
import {defineEmits, ref} from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import Rubrics from "@/Components/Public/Default/Rubrics.vue";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import ThemeToggle from "@/Components/User/ThemeToggle/ThemeToggle.vue";
import LogoutButton from "@/Components/User/Button/LogoutButton.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { useI18n } from 'vue-i18n';

const {t} = useI18n();

const emits = defineEmits(['toggleNavigationDropdown']);

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const showingNavigationDropdown = ref(false);
const { auth } = usePage().props;

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <header class="w-full h-fit shadow z-10 font-sans">
        <nav class="bg-cyan-700 dark:bg-blue-950 border-b border-gray-100">
            <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    <!-- Логотип -->
                    <div class="flex items-center">
                        <ApplicationMark class="block h-9 w-auto" />
                    </div>

                    <!-- Меню рубрик -->
                    <Rubrics :isOpen="showingNavigationDropdown"
                              class="hidden md:flex flex-grow justify-center space-x-2 px-4"/>

                    <!-- Меню пользователя -->
                    <div v-if="canLogin" class="hidden md:flex items-center space-x-2">

                        <Link v-if="auth.user" :href="route('dashboard')" :title="t('profile')"
                              class="flex items-center px-3 py-1 text-md
                                     font-semibold text-slate-100 hover:text-orange-400
                                     focus:text-slate-100 active:text-orange-400
                                     focus:outline focus:outline-2 focus:rounded-sm focus:outline-orange-400">
                            <svg class="shrink-0 h-5 w-5 mr-2" viewBox="0 0 24 24">
                                <path class="fill-current text-slate-400" d="M18.974 8H22a2 2 0 012 2v6h-2v5a1 1 0 01-1 1h-2a1 1 0 01-1-1v-5h-2v-6a2 2 0 012-2h.974zM20 7a2 2 0 11-.001-3.999A2 2 0 0120 7zM2.974 8H6a2 2 0 012 2v6H6v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5H0v-6a2 2 0 012-2h.974zM4 7a2 2 0 11-.001-3.999A2 2 0 014 7z"></path>
                                <path class="fill-current text-slate-200" d="M12 6a3 3 0 110-6 3 3 0 010 6zm2 18h-4a1 1 0 01-1-1v-6H6v-6a3 3 0 013-3h6a3 3 0 013 3v6h-3v6a1 1 0 01-1 1z"></path>
                            </svg>
                            {{ t('profile') }}
                        </Link>

                        <form v-if="auth.user" @submit.prevent="logout">
                            <LogoutButton>{{ t('logout') }} -> </LogoutButton>
                        </form>

                        <template v-else>
                            <Link :href="route('login')"
                                  class="px-3 py-1
                                         text-md font-semibold
                                         text-slate-100 hover:text-yellow-200
                                         dark:text-slate-100 dark:hover:text-yellow-200
                                         focus:outline focus:outline-2 focus:rounded-sm focus:outline-yellow-200">
                                {{ t('login') }}
                            </Link>
                            <Link v-if="canRegister" :href="route('register')"
                                  class="px-3 py-1
                                         text-md font-semibold
                                         text-slate-100 hover:text-yellow-200
                                         dark:text-slate-100 dark:hover:text-yellow-200
                                         focus:outline focus:outline-2 focus:rounded-sm focus:outline-yellow-200">
                                {{ t('register') }}
                            </Link>
                        </template>

                    </div>

                    <!-- Переключатель темы -->
                    <div class="ml-2 flex items-center">
                        <ThemeToggle />
                    </div>

                    <!-- Меню для мобильных устройств -->
                    <div class="-me-2 flex items-center md:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md
                                       text-gray-400 hover:text-gray-500 hover:bg-gray-100
                                       dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                      stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 6h16M4 12h16M4 18h16"/>
                                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                      stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Мобильное меню -->
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="md:hidden">
                <Rubrics :isOpen="showingNavigationDropdown" class="px-4 py-2 border-t dark:border-gray-700"/>

                <div v-if="canLogin" class="pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink v-if="auth.user" :href="route('dashboard')">{{ t('profile') }}</ResponsiveNavLink>
                    <form v-if="auth.user" @submit.prevent="logout" class="w-fit ml-4">
                        <LogoutButton>{{ t('logout') }}</LogoutButton>
                    </form>
                    <template v-else>
                        <ResponsiveNavLink :href="route('login')">{{ t('login') }}</ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canRegister" :href="route('register')">{{ t('register') }}</ResponsiveNavLink>
                    </template>
                </div>
            </div>
        </nav>
    </header>
</template>
