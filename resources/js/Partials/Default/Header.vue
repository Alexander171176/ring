<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import SocialIcons from "@/Components/User/Links/SocialIcons.vue";
import TopMenuRubrics from "@/Components/Public/Default/Rubric/TopMenuRubrics.vue";
import ThemeToggle from "@/Components/User/ThemeToggle/ThemeToggle.vue";
import LogoutButton from "@/Components/User/Button/LogoutButton.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { siteSettings, auth } = usePage().props;

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
    window.addEventListener('scroll', recalcLayout, { passive: true });
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
                      class="flex items-center border-r border-slate-400 px-6 h-8
                             text-sm font-semibold
                             text-slate-50 hover:text-red-400
                             dark:text-slate-50 dark:hover:text-red-400
                             focus:outline focus:outline-2 focus:rounded-sm
                             focus:outline-red dark:focus:outline-red-400">
                    RING.COM.KZ
                </Link>

                <SocialIcons />

                <div class="ml-2 flex items-center">
                    <div v-if="canLogin" class="flex items-center space-x-2 mr-8">

                        <Link v-if="auth.user" :href="route('profile.show')" :title="t('profile')"
                              class="flex items-center px-3 pb-0.5
                                     text-sm font-semibold
                                     text-slate-100 hover:text-yellow-200
                                     dark:text-slate-100 dark:hover:text-yellow-200
                                     focus:outline focus:outline-2 focus:rounded-sm
                                     focus:outline-yellow 2ark:focus:outline-yellow-200">
                            {{ t('profile') }}
                        </Link>

                        <form v-if="auth.user" @submit.prevent="logout">
                            <LogoutButton>{{ t('logout') }}</LogoutButton>
                        </form>

                        <template v-else>
                            <Link v-if="canRegister" :href="route('register')"
                                  class="px-3 pb-0.5 text-sm font-semibold
                                         text-slate-100 hover:text-yellow-200
                                         dark:text-slate-100 dark:hover:text-yellow-200
                                         focus:outline focus:outline-2 focus:rounded-sm
                                         focus:outline-yellow-200 dark:focus:outline-yellow-200">
                                {{ t('register') }}
                            </Link>
                            <Link :href="route('login')"
                                  class="px-3 pb-0.5 text-sm font-semibold
                                         text-slate-100 hover:text-yellow-200
                                         dark:text-slate-100 dark:hover:text-yellow-200
                                         focus:outline focus:outline-2 focus:rounded-sm
                                         focus:outline-yellow-200 dark:focus:outline-yellow-200">
                                {{ t('login') }}
                            </Link>
                        </template>

                    </div>
                </div>

                <ThemeToggle />

                <div class="-me-2 flex items-center md:hidden">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out">
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

        <!-- Нижний блок шапки -->
        <div class="bg-slate-100 text-sm py-0 shadow-sm">
            <div class="max-w-12xl mx-auto px-4 sm:px-3 md:px-2 xl:px-6 flex justify-between items-center">
                <TopMenuRubrics :isOpen="showingNavigationDropdown"
                                class="hidden md:flex flex-grow justify-center space-x-2 px-1" />
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
