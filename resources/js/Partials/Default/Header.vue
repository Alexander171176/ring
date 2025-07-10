<script setup>
import {ref, onMounted, onUnmounted, computed, nextTick} from "vue";
import {Link, router, usePage} from "@inertiajs/vue3";
import useLanguageSwitcher from '@/composables/useLanguageSwitcher';
import SocialIcons from "@/Components/User/Links/SocialIcons.vue";
import TopMenuRubrics from "@/Components/Public/Default/Rubric/TopMenuRubrics.vue";
import MobileTopMenuRubrics from "@/Components/Public/Default/Rubric/MobileTopMenuRubrics.vue";
import ThemeToggle from "@/Components/User/ThemeToggle/ThemeToggle.vue";
import LogoutIcon from "@/Components/Public/Default/Component/LogoutIcon.vue";
import LogoutButton from "@/Components/User/Button/LogoutButton.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import LocaleSelectOption from '@/Components/Admin/Select/LocaleSelectOption.vue'; // уже используется в футере
import {useI18n} from 'vue-i18n';

const { selectedLocale } = useLanguageSwitcher();

const {t} = useI18n();
const {siteSettings, auth} = usePage().props;

const isDarkMode = ref(false);
let observer;

const checkDarkMode = () => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
};

onMounted(() => {
    checkDarkMode();
    observer = new MutationObserver(checkDarkMode);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const emits = defineEmits(['toggleNavigationDropdown']);

const mainNavRef = ref(null);
const navPlaceholderRef = ref(null);

const isNavFixed = ref(false);
const navHeight = ref(0);
const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const recalcLayout = () => {
    nextTick(() => {
        if (mainNavRef.value) {
            navHeight.value = mainNavRef.value.offsetHeight;
        }

        const scrollY = window.scrollY;
        isNavFixed.value = scrollY > 0;

        if (navPlaceholderRef.value) {
            navPlaceholderRef.value.style.height = isNavFixed.value ? `${navHeight.value}px` : '0px';
        }
    });
};

let resizeTimeout;
const handleResize = () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        if (isNavFixed.value && navPlaceholderRef.value) {
            isNavFixed.value = false;
            navPlaceholderRef.value.style.height = '0px';
        }
        recalcLayout();
    }, 150);
};

onMounted(() => {
    recalcLayout();
    window.addEventListener('scroll', recalcLayout, {passive: true});
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('scroll', recalcLayout);
    window.removeEventListener('resize', handleResize);
    clearTimeout(resizeTimeout);
});

const navClasses = computed(() => ({
    'nav-fixed': isNavFixed.value,
    'shadow-md': isNavFixed.value,
}));

const placeholderClasses = computed(() => ({
    'header-placeholder': true,
    'active': isNavFixed.value,
}));

const showUserModal = ref(false);

onMounted(() => {
    document.addEventListener('click', onClickOutside);
});
onUnmounted(() => {
    document.removeEventListener('click', onClickOutside);
});

const onClickOutside = (e) => {
    nextTick(() => {
        const modal = document.querySelector('.user-modal-dropdown');
        const button = document.querySelector('.user-profile-button');
        if (
            modal &&
            !modal.contains(e.target) &&
            button &&
            !button.contains(e.target)
        ) {
            showUserModal.value = false;
        }
    });
};

</script>

<template>
    <div ref="navPlaceholderRef" :class="placeholderClasses"></div>

    <nav ref="mainNavRef" :class="navClasses"
         class="py-0 bg-blue-950
                border-t border-b border-dashed border-slate-100 dark:border-slate-100
                relative z-10">

        <div class="max-w-12xl mx-auto px-4 sm:px-3 md:px-2 xl:px-6">
            <div class="flex items-center justify-between h-8">

                <Link :href="route('home')" :title="t('home')"
                      class="flex items-center border-r border-slate-400 px-4 h-8
                             text-md font-semibold
                             text-slate-50 hover:text-red-400
                             dark:text-slate-50 dark:hover:text-red-400
                             focus:outline focus:outline-2 focus:rounded-sm
                             focus:outline-red dark:focus:outline-red-400">
                    RING.COM.KZ
                </Link>

                <div class="ml-2 flex items-center">
                    <SocialIcons class="hidden md:flex"/>
                </div>

                <div class="ml-2 flex items-center">

                    <LocaleSelectOption v-model="selectedLocale" class="mr-1" />

                    <div v-if="canLogin" class="relative flex items-center mr-0">

                        <!-- Иконка профиля -->
                        <button @click="showUserModal = !showUserModal"
                                class="user-profile-button text-white hover:text-blue-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M12 3.75a3.75 3.75 0 1 0 0 7.5a3.75 3.75 0 0 0 0-7.5m-4 9.5A3.75 3.75 0 0 0 4.25 17v1.188c0 .754.546 1.396 1.29 1.517c4.278.699 8.642.699 12.92 0a1.537 1.537 0 0 0 1.29-1.517V17A3.75 3.75 0 0 0 16 13.25h-.34c-.185 0-.369.03-.544.086l-.866.283a7.251 7.251 0 0 1-4.5 0l-.866-.283a1.752 1.752 0 0 0-.543-.086z"/>
                            </svg>
                        </button>

                        <!-- Модалка со ссылками -->
                        <div v-if="showUserModal"
                             class="user-modal-dropdown absolute right-0 top-8 w-36
                                    bg-gray-50 dark:bg-gray-600
                                    border border-gray-300 dark:border-gray-700 rounded
                                    shadow-md shadow-gray-400 dark:shadow-gray-800 z-50 text-sm">

                            <div class="flex flex-col p-2 space-y-1">
                                <template v-if="auth.user">
                                    <Link :href="route('profile.show')"
                                          class="text-slate-900 dark:text-slate-100
                                                 hover:text-blue-600 dark:hover:text-blue-400 font-semibold">
                                        {{ t('profile') }}
                                    </Link>
                                    <form @submit.prevent="logout">
                                        <button type="submit"
                                                class="text-left w-full
                                                       text-slate-900 dark:text-slate-100
                                                       hover:text-blue-600 dark:hover:text-blue-400 font-semibold">
                                            {{ t('logout') }}
                                        </button>
                                    </form>
                                </template>
                                <template v-else>
                                    <Link :href="route('login')"
                                          class="text-slate-900 dark:text-slate-100
                                                 hover:text-blue-600 dark:hover:text-blue-400 font-semibold">
                                        {{ t('login') }}
                                    </Link>
                                    <Link v-if="canRegister" :href="route('register')"
                                          class="text-slate-900 dark:text-slate-100
                                                 hover:text-blue-600 dark:hover:text-blue-400 font-semibold">
                                        {{ t('register') }}
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>

                    <ThemeToggle/>

                </div>

                <div class="-me-2 flex items-center md:hidden">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-0.5 rounded-xs
                                    text-white focus:outline-none transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path
                                :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"/>
                            <path
                                :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="md:hidden">

            <MobileTopMenuRubrics :isOpen="showingNavigationDropdown" />

            <div v-if="canLogin" class="pb-1 space-y-1 bg-gray-200 dark:bg-gray-700">

                <ResponsiveNavLink v-if="auth.user" :href="route('dashboard')">
                    {{ t('profile') }}
                </ResponsiveNavLink>

                <form v-if="auth.user" @submit.prevent="logout" class="w-full">
                    <LogoutButton>{{ t('logout') }}</LogoutButton>
                </form>

                <template v-else>
                    <ResponsiveNavLink :href="route('login')">{{ t('login') }}</ResponsiveNavLink>
                    <ResponsiveNavLink v-if="canRegister" :href="route('register')">
                        {{ t('register') }}
                    </ResponsiveNavLink>
                </template>

            </div>
        </div>

        <!-- Нижний блок шапки -->
        <div class="bg-slate-100 text-sm py-0 shadow-sm">
            <div class="max-w-12xl mx-auto px-4 sm:px-3 md:px-2 xl:px-6 flex justify-between items-center">
                <TopMenuRubrics :isOpen="showingNavigationDropdown"
                                class="hidden md:flex flex-grow justify-center space-x-2 px-1"/>
            </div>
        </div>

    </nav>

</template>

<style scoped>
.nav-fixed {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    z-index: 50 !important;
}

.header-placeholder {
    height: 0;
}
</style>
