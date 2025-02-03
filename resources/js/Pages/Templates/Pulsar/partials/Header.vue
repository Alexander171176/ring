<script setup>
import { ref, defineEmits } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import ApplicationMark from '@/Pages/Templates/Default/components/ApplicationMark.vue';
import ResponsiveNavLinks from '@/Pages/Templates/Default/components/ResponsiveNavLinks.vue';
import LogoutButton from '@/Pages/Templates/Default/components/LogoutButton.vue';
import ThemeToggle from '@/Pages/Templates/Default/components/ThemeToggle.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const emits = defineEmits(['toggleNavigationDropdown']);

const props = defineProps({
    title: String,
    currentTime: String,
    showingNavigationDropdown: Boolean,
    canLogin: Boolean,
    canRegister: Boolean
});

const switchToTeam = (team) => {
    router.put(
        route('current-team.update'),
        {
            team_id: team.id,
        },
        {
            preserveState: false,
        }
    )
};

const logout = () => {
    router.post(route('logout'))
};

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="sticky top-0
                bg-gradient-to-b from-slate-100 to-slate-300
                dark:bg-gradient-to-b dark:from-slate-700 dark:to-slate-900
                border-b border-slate-200 dark:border-slate-700 z-20">

        <nav class="border-b border-gray-100">
            <div class="max-w-full mx-auto px-4 sm:px-0">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center justify-center ml-4">
                        <div class="shrink-0 flex items-center">
                            <Link href="/">
                                <ApplicationMark class="block h-9 w-auto"/>
                            </Link>
                        </div>
                    </div>
                    <div v-if="canLogin" class="hidden md:flex w-full flex-row flex-wrap items-center justify-end">

                        <!-- Navigation Links -->
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="font-semibold
                                           text-gray-600
                                           text-lg
                                           px-3 py-1
                                           hover:text-gray-900
                                           dark:text-gray-400
                                           dark:hover:text-white
                                           focus:outline
                                           focus:outline-2
                                           focus:rounded-sm
                                           focus:outline-red-500">
                            Личный кабинет
                        </Link>

                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('admin')"
                            class="font-semibold
                                           text-gray-600
                                           text-lg
                                           mr-2 px-3 py-1
                                           hover:text-gray-900
                                           dark:text-gray-400
                                           dark:hover:text-white
                                           focus:outline
                                           focus:outline-2
                                           focus:rounded-sm
                                           focus:outline-red-500">
                            Панель администратора
                        </Link>

                        <!-- logout -->
                        <form v-if="$page.props.auth.user" @submit.prevent="logout">
                            <LogoutButton>
                                Выход
                            </LogoutButton>
                        </form>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="font-semibold
                                           text-gray-600
                                           text-lg
                                           px-3 py-1
                                           hover:text-gray-900
                                           dark:text-gray-400
                                           dark:hover:text-white
                                           focus:outline
                                           focus:outline-2
                                           focus:rounded-sm
                                           focus:outline-red-500">
                                Вход
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="font-semibold
                                           text-gray-600
                                           text-lg
                                           px-3 py-1
                                           hover:text-gray-900
                                           dark:text-gray-400
                                           dark:hover:text-white
                                           focus:outline
                                           focus:outline-2
                                           focus:rounded-sm
                                           focus:outline-red-500">
                                Регистрация
                            </Link>
                        </template>

                    </div>
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <div class="mx-2 flex items-center">
                            <ThemeToggle class="relative z-10"/>
                        </div>
                    </div>
                    <div class="-me-2 flex items-center sm:hidden">
                        <button class="inline-flex items-center justify-center
                                        p-2 rounded-md
                                        text-slate-400 hover:text-slate-500
                                        hover:bg-gray-100
                                        focus:outline-none focus:bg-gray-100 focus:text-slate-500
                                        transition duration-150 ease-in-out"
                                @click="showingNavigationDropdown = !showingNavigationDropdown">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                <ResponsiveNavLinks />
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-slate-100 dark:bg-sky-900 shadow">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-4 lg:px-8">
                <slot name="header"/>
            </div>
        </header>
    </div>
</template>
