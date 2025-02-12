<script setup>
import { ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import ThemeToggle from "@/Components/User/ThemeToggle/ThemeToggle.vue";
import LogoutButton from "@/Components/User/Button/LogoutButton.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { useI18n } from 'vue-i18n';

const {t} = useI18n();

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
    <div class="min-h-screen w-full sm:fixed sm:top-0 sm:end-0 text-end z-10 shadow">
        <nav class="bg-white border-b border-gray-100 dark:bg-slate-900">
            <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="w-full flex justify-between">
                        <div class="shrink-0 flex items-center">
                            <ApplicationMark class="block h-9 w-auto" />
                        </div>

                        <div v-if="canLogin" class="hidden md:flex w-full flex-row flex-wrap items-center justify-end">
                            <Link v-if="auth.user" :href="route('admin')" :title="t('adminPanel')"
                                  class="px-3 py-1 pr-2 font-semibold text-gray-600 text-lg
                                         hover:text-gray-900 dark:text-gray-400 dark:hover:text-white
                                         focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                                <svg class="shrink-0 h-4 w-4" viewBox="0 0 24 24">
                                    <path class="fill-current text-blue-400" d="M15.9,18.45C17.25,18.45 18.35,17.35 18.35,16C18.35,14.65 17.25,13.55 15.9,13.55C14.54,13.55 13.45,14.65 13.45,16C13.45,17.35 14.54,18.45 15.9,18.45M21.1,16.68L22.58,17.84C22.71,17.95 22.75,18.13 22.66,18.29L21.26,20.71C21.17,20.86 21,20.92 20.83,20.86L19.09,20.16C18.73,20.44 18.33,20.67 17.91,20.85L17.64,22.7C17.62,22.87 17.47,23 17.3,23H14.5C14.32,23 14.18,22.87 14.15,22.7L13.89,20.85C13.46,20.67 13.07,20.44 12.71,20.16L10.96,20.86C10.81,20.92 10.62,20.86 10.54,20.71L9.14,18.29C9.05,18.13 9.09,17.95 9.22,17.84L10.7,16.68L10.65,16L10.7,15.31L9.22,14.16C9.09,14.05 9.05,13.86 9.14,13.71L10.54,11.29C10.62,11.13 10.81,11.07 10.96,11.13L12.71,11.84C13.07,11.56 13.46,11.32 13.89,11.15L14.15,9.29C14.18,9.13 14.32,9 14.5,9H17.3C17.47,9 17.62,9.13 17.64,9.29L17.91,11.15C18.33,11.32 18.73,11.56 19.09,11.84L20.83,11.13C21,11.07 21.17,11.13 21.26,11.29L22.66,13.71C22.75,13.86 22.71,14.05 22.58,14.16L21.1,15.31L21.15,16L21.1,16.68M6.69,8.07C7.56,8.07 8.26,7.37 8.26,6.5C8.26,5.63 7.56,4.92 6.69,4.92A1.58,1.58 0 0,0 5.11,6.5C5.11,7.37 5.82,8.07 6.69,8.07M10.03,6.94L11,7.68C11.07,7.75 11.09,7.87 11.03,7.97L10.13,9.53C10.08,9.63 9.96,9.67 9.86,9.63L8.74,9.18L8,9.62L7.81,10.81C7.79,10.92 7.7,11 7.59,11H5.79C5.67,11 5.58,10.92 5.56,10.81L5.4,9.62L4.64,9.18L3.5,9.63C3.41,9.67 3.3,9.63 3.24,9.53L2.34,7.97C2.28,7.87 2.31,7.75 2.39,7.68L3.34,6.94L3.31,6.5L3.34,6.06L2.39,5.32C2.31,5.25 2.28,5.13 2.34,5.03L3.24,3.47C3.3,3.37 3.41,3.33 3.5,3.37L4.63,3.82L5.4,3.38L5.56,2.19C5.58,2.08 5.67,2 5.79,2H7.59C7.7,2 7.79,2.08 7.81,2.19L8,3.38L8.74,3.82L9.86,3.37C9.96,3.33 10.08,3.37 10.13,3.47L11.03,5.03C11.09,5.13 11.07,5.25 11,5.32L10.03,6.06L10.06,6.5L10.03,6.94Z"></path>
                                </svg>
                            </Link>
                            <Link v-if="auth.user" :href="route('dashboard')" :title="t('dashboard')"
                                  class="px-3 py-1 pr-2 font-semibold text-gray-600 text-lg
                                         hover:text-gray-900 dark:text-gray-400 dark:hover:text-white
                                         focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                                <svg class="shrink-0 h-4 w-4" viewBox="0 0 24 24">
                                    <path class="fill-current text-blue-600" d="M18.974 8H22a2 2 0 012 2v6h-2v5a1 1 0 01-1 1h-2a1 1 0 01-1-1v-5h-2v-6a2 2 0 012-2h.974zM20 7a2 2 0 11-.001-3.999A2 2 0 0120 7zM2.974 8H6a2 2 0 012 2v6H6v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5H0v-6a2 2 0 012-2h.974zM4 7a2 2 0 11-.001-3.999A2 2 0 014 7z"></path>
                                    <path class="fill-current text-blue-400" d="M12 6a3 3 0 110-6 3 3 0 010 6zm2 18h-4a1 1 0 01-1-1v-6H6v-6a3 3 0 013-3h6a3 3 0 013 3v6h-3v6a1 1 0 01-1 1z"></path>
                                </svg>
                            </Link>
                            <form v-if="auth.user" @submit.prevent="logout">
                                <LogoutButton> {{ t('logout') }} </LogoutButton>
                            </form>

                            <template v-else>
                                <Link :href="route('login')"
                                      class="px-3 py-1 font-semibold text-gray-600 text-lg
                                             hover:text-gray-900 dark:text-gray-400 dark:hover:text-white
                                             focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                                    {{ t('login') }}
                                </Link>
                                <Link v-if="canRegister" :href="route('register')"
                                      class="px-3 py-1 font-semibold text-gray-600 text-lg
                                             hover:text-gray-900 dark:text-gray-400 dark:hover:text-white
                                             focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                                    {{ t('register') }}
                                </Link>
                            </template>
                        </div>
                    </div>

                    <div class="ml-2 flex items-center">
                        <ThemeToggle />
                    </div>

                    <div class="-me-2 flex items-center md:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="md:hidden">
                <div v-if="canLogin" class="pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink v-if="auth.user" :href="route('admin')">{{ t('adminPanel') }}</ResponsiveNavLink>
                    <ResponsiveNavLink v-if="auth.user" :href="route('dashboard')">{{ t('dashboard') }}</ResponsiveNavLink>

                    <form v-if="auth.user" @submit.prevent="logout" class="w-fit ml-4">
                        <LogoutButton> {{ t('logout') }} </LogoutButton>
                    </form>

                    <template v-else>
                        <ResponsiveNavLink :href="route('login')" class="font-semibold text-gray-600 text-lg px-3 py-1 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                            {{ t('login') }}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canRegister" :href="route('register')" class="font-semibold text-gray-600 text-lg px-3 py-1 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                            {{ t('register') }}
                        </ResponsiveNavLink>
                    </template>
                </div>
            </div>
        </nav>
    </div>
</template>
