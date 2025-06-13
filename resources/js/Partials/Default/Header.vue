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
                    <div v-if="canLogin" class="flex items-center mr-0">

                        <Link v-if="auth.user" :href="route('profile.show')" :title="t('profile')"
                              class="transition duration-300 text-white hover:text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M12 3.75a3.75 3.75 0 1 0 0 7.5a3.75 3.75 0 0 0 0-7.5m-4 9.5A3.75 3.75 0 0 0 4.25 17v1.188c0 .754.546 1.396 1.29 1.517c4.278.699 8.642.699 12.92 0a1.537 1.537 0 0 0 1.29-1.517V17A3.75 3.75 0 0 0 16 13.25h-.34c-.185 0-.369.03-.544.086l-.866.283a7.251 7.251 0 0 1-4.5 0l-.866-.283a1.752 1.752 0 0 0-.543-.086z"/>
                            </svg>
                        </Link>

                        <form v-if="auth.user" @submit.prevent="logout">
                            <LogoutIcon :title="t('logout')" />
                        </form>

                        <template v-else>
                            <Link v-if="canRegister" :href="route('register')" :title="t('register')"
                                  class="flex justify-center items-center p-0 mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 24 24"
                                     class="text-white hover:text-blue-600">
                                    <path fill="currentColor" d="M23,1H1A1,1,0,0,0,0,2V22a1,1,0,0,0,1,1H23a1,1,0,0,0,1-1V2A1,1,0,0,0,23,1ZM7,3A1,1,0,1,1,6,4,1,1,0,0,1,7,3ZM3,3A1,1,0,1,1,2,4,1,1,0,0,1,3,3ZM22,21H2V7H22Z"></path>
                                    <path fill="currentColor" d="M20.851,12.475A1,1,0,0,0,20,12H11.445a4,4,0,1,0,0,4H14l1.5,1L17,16h2a1,1,0,0,0,.895-.553l1-2A1,1,0,0,0,20.851,12.475ZM7,15a1,1,0,1,1,1-1A1,1,0,0,1,7,15Z"></path>
                                </svg>
                            </Link>
                            <Link :href="route('login')" :title="t('login')"
                                  class="flex justify-center items-center p-0 mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" height="18" width="18" viewBox="0 0 24 24"
                                     class="text-white hover:text-blue-600">
                                    <path fill="currentColor" d="M20,10H4c-1.105,0-2,.895-2,2v10c0,1.105,.895,2,2,2H20c1.105,0,2-.895,2-2V12c0-1.105-.895-2-2-2Zm-8,9c-1.105,0-2-.895-2-2s.895-2,2-2,2,.895,2,2-.895,2-2,2Z"></path>
                                    <path fill="currentColor" d="M18,8h-2v-2c.023-2.184-1.727-3.974-3.911-4h-.042c-2.197-.038-4.009,1.711-4.047,3.908,0,.001,0,.002,0,.003v2.089h-2v-2.1C6.033,2.636,8.685,.006,11.949,0h.061c3.302-.006,5.984,2.666,5.99,5.968,0,.014,0,.028,0,.042v1.99Z"></path>
                                </svg>
                            </Link>
                        </template>

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
