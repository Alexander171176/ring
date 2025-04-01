<script setup>
import {ref, onMounted, onUnmounted, computed, nextTick} from "vue"; // Добавили хуки и nextTick
import {Link, router, usePage} from "@inertiajs/vue3";
import TopMenuRubrics from "@/Components/Public/Default/Rubric/TopMenuRubrics.vue";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import ThemeToggle from "@/Components/User/ThemeToggle/ThemeToggle.vue";
import LogoutButton from "@/Components/User/Button/LogoutButton.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import {useI18n} from 'vue-i18n';

const {t} = useI18n();

// Оставляем ваши существующие defineProps и defineEmits
const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
const emits = defineEmits(['toggleNavigationDropdown']); // Если он используется где-то еще

// --- Новые ref для DOM-элементов ---
const topBarRef = ref(null);
const mainNavRef = ref(null);
const navPlaceholderRef = ref(null);

// --- Новые ref для управления состоянием и высотами ---
const isNavFixed = ref(false);
const topBarHeight = ref(0);
const navHeight = ref(0);

// --- Ваши существующие ref и логика ---
const showingNavigationDropdown = ref(false);
const {auth} = usePage().props;

const logout = () => {
    router.post(route('logout'));
};

// --- Функция для обновления высот ---
const updateHeights = () => {
    // Ждем следующего тика, чтобы DOM обновился после возможного снятия фиксации
    nextTick(() => {
        if (topBarRef.value) {
            topBarHeight.value = topBarRef.value.offsetHeight;
        }
        if (mainNavRef.value) {
            navHeight.value = mainNavRef.value.offsetHeight;
        }
        // Обновляем высоту плейсхолдера, если навигация зафиксирована
        if (isNavFixed.value && navPlaceholderRef.value) {
            navPlaceholderRef.value.style.height = `${navHeight.value}px`;
        }
    });
};


// --- Основная функция для обработки скролла ---
const handleScroll = () => {
    if (!topBarRef.value || !mainNavRef.value || !navPlaceholderRef.value) return; // Защита

    const scrollY = window.scrollY;

    if (scrollY >= topBarHeight.value) {
        if (!isNavFixed.value) {
            // Фиксируем
            isNavFixed.value = true;
            // Устанавливаем высоту плейсхолдера
            navPlaceholderRef.value.style.height = `${navHeight.value}px`;

        }
    } else {
        if (isNavFixed.value) {
            // Открепляем
            isNavFixed.value = false;
            // Сбрасываем высоту плейсхолдера
            navPlaceholderRef.value.style.height = '0px';
        }
    }
};

// --- Обработчик Resize с debounce ---
let resizeTimeout;
const handleResize = () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        // Сначала временно снимаем фиксацию, чтобы получить правильный offsetHeight навигации
        const wasFixed = isNavFixed.value;
        if (wasFixed) {
            isNavFixed.value = false; // Это изменит классы в шаблоне
            navPlaceholderRef.value.style.height = '0px';
        }
        // Обновляем высоты и сразу же проверяем скролл
        updateHeights();
        handleScroll(); // Проверяем, нужно ли снова фиксировать с новыми высотами
    }, 150); // Немного увеличил задержку
};

// --- Хуки жизненного цикла ---
onMounted(() => {
    // Вычисляем начальные высоты после монтирования компонента
    updateHeights();

    // Добавляем слушатели
    window.addEventListener('scroll', handleScroll, {passive: true});
    window.addEventListener('resize', handleResize);

    // Первоначальный вызов handleScroll на случай, если страница загрузилась уже прокрученной
    handleScroll();
});

onUnmounted(() => {
    // Удаляем слушатели при размонтировании
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('resize', handleResize);
    clearTimeout(resizeTimeout); // Очищаем таймер при уходе со страницы
});

// Вычисляемое свойство для классов навигации
const navClasses = computed(() => {
    // Определяем начальные и конечные цвета для градиента
    const initialGradientFrom = 'from-blue-700'; // Пример начального (слева)
    const initialGradientTo = 'to-sky-700';     // Пример начального (справа)
    const fixedGradientFrom = 'from-blue-800'; // Пример фиксированного (слева)
    const fixedGradientTo = 'to-sky-800';   // Пример фиксированного (справа)

    return {
        'nav-fixed': isNavFixed.value, // Добавляем класс для JS-логики фиксации

        // --- Классы градиента ---
        // ИЗМЕНЕНО: Заменяем 'bg-gradient-to-b' на 'bg-gradient-to-r'
        'bg-gradient-to-r': true, // Всегда горизонтальный градиент (слева направо)
                                  // Используйте 'bg-gradient-to-l' для градиента справа налево

        // Условное применение ЦВЕТОВ градиента
        [initialGradientFrom]: !isNavFixed.value,
        [initialGradientTo]:   !isNavFixed.value,
        [fixedGradientFrom]:   isNavFixed.value,
        [fixedGradientTo]:     isNavFixed.value,

        // --- Классы тени ---
        'shadow-md': !isNavFixed.value, // Начальная тень
        'shadow-lg': isNavFixed.value  // Тень при фиксации
    };
});

// Вычисляемое свойство для классов плейсхолдера (если нужно больше логики)
const placeholderClasses = computed(() => {
    return {
        'header-placeholder': true,
        'active': isNavFixed.value // Можно использовать для доп. стилей, если нужно
    }
});

</script>

<template>
    <!-- Верхний блок (используем ref) -->
    <div ref="topBarRef" class="bg-slate-100 dark:bg-blue-950">
        <div class="max-w-12xl mx-auto px-4 sm:px-3 md:px-2 xl:px-6 py-2">
            <div class="flex items-center justify-between h-10">

                <div></div>

                <div class="ml-2 flex items-center">
                    <!-- Меню пользователя -->
                    <div v-if="canLogin" class="flex items-center space-x-2 mr-8">

                        <Link v-if="auth.user" :href="route('dashboard')" :title="t('profile')"
                              class="flex items-center px-3 pb-0.5
                                     text-sm font-semibold
                                     text-slate-900 hover:text-orange-500
                                     dark:text-slate-100 dark:hover:text-yellow-200
                                     focus:outline focus:outline-2 focus:rounded-sm
                                     focus:outline-orange-500 dark:focus:outline-yellow-200">
                            <svg class="shrink-0 h-5 w-5 mr-2" viewBox="0 0 24 24">
                                <path class="fill-current text-slate-400 dark:text-slate-400"
                                      d="M18.974 8H22a2 2 0 012 2v6h-2v5a1 1 0 01-1 1h-2a1 1 0 01-1-1v-5h-2v-6a2 2 0 012-2h.974zM20 7a2 2 0 11-.001-3.999A2 2 0 0120 7zM2.974 8H6a2 2 0 012 2v6H6v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5H0v-6a2 2 0 012-2h.974zM4 7a2 2 0 11-.001-3.999A2 2 0 014 7z"></path>
                                <path class="fill-current text-blue-500 dark:text-slate-200"
                                      d="M12 6a3 3 0 110-6 3 3 0 010 6zm2 18h-4a1 1 0 01-1-1v-6H6v-6a3 3 0 013-3h6a3 3 0 013 3v6h-3v6a1 1 0 01-1 1z"></path>
                            </svg>
                            {{ t('profile') }}
                        </Link>

                        <form v-if="auth.user" @submit.prevent="logout">
                            <LogoutButton>{{ t('logout') }}</LogoutButton> <!-- Убрал стрелку -->
                        </form>

                        <template v-else>
                            <Link v-if="canRegister" :href="route('register')"
                                  class="px-3 pb-0.5
                                     text-sm font-semibold
                                     text-slate-900 hover:text-orange-500
                                     dark:text-slate-100 dark:hover:text-yellow-200
                                     focus:outline focus:outline-2 focus:rounded-sm
                                     focus:outline-orange-500 dark:focus:outline-yellow-200">
                                {{ t('register') }}
                            </Link>
                            <Link :href="route('login')"
                                  class="px-3 pb-0.5
                                     text-sm font-semibold
                                     text-slate-900 hover:text-orange-500
                                     dark:text-slate-100 dark:hover:text-yellow-200
                                     focus:outline focus:outline-2 focus:rounded-sm
                                     focus:outline-orange-500 dark:focus:outline-yellow-200">
                                {{ t('login') }}
                            </Link>
                        </template>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Плейсхолдер (используем ref и вычисляемый класс) -->
    <div ref="navPlaceholderRef" :class="placeholderClasses"></div>

    <!-- Навигационная панель (используем ref и вычисляемый класс) -->
    <nav ref="mainNavRef" :class="navClasses"
         class="py-1 text-white relative z-10 transition-all duration-300 ease-in-out">
        <div class="max-w-12xl mx-auto px-4 sm:px-3 md:px-2 xl:px-6">
            <div class="flex items-center justify-between h-10">

                <!-- Логотип -->
                <div class="flex items-center">
                    <ApplicationMark class="block h-6 w-auto"/>
                </div>

                <!-- Меню рубрик -->
                <TopMenuRubrics :isOpen="showingNavigationDropdown"
                                class="hidden md:flex flex-grow justify-center space-x-2 px-1"/>

                <!-- Переключатель темы -->
                <ThemeToggle/>

                <!-- Меню для мобильных устройств -->
                <div class="-me-2 flex items-center md:hidden">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md
                                   text-gray-400 hover:text-gray-500 hover:bg-gray-100
                                   dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out">
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

        <!-- Мобильное меню (остается без изменений) -->
        <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="md:hidden">
            <TopMenuRubrics :isOpen="showingNavigationDropdown" class="px-4 py-2 border-t dark:border-gray-700"/>

            <div v-if="canLogin" class="pt-2 pb-3 space-y-1">
                <ResponsiveNavLink v-if="auth.user" :href="route('dashboard')">{{ t('profile') }}</ResponsiveNavLink>
                <form v-if="auth.user" @submit.prevent="logout" class="w-fit ml-4">
                    <LogoutButton>{{ t('logout') }}</LogoutButton>
                </form>
                <template v-else>
                    <ResponsiveNavLink :href="route('login')">{{ t('login') }}</ResponsiveNavLink>
                    <ResponsiveNavLink v-if="canRegister" :href="route('register')">{{
                            t('register')
                        }}
                    </ResponsiveNavLink>
                </template>
            </div>
        </div>
    </nav>
</template>

<style scoped>
/* Класс для фиксированного состояния навигации */
.nav-fixed {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    z-index: 50 !important; /* Убедитесь, что z-index высокий */
    /* transition: transform 0.3s ease-in-out; /* Плавность для появления/исчезновения (если нужно) */
}

/* Плейсхолдер для компенсации высоты */
.header-placeholder {
    height: 0;
    transition: height 0.1s ease-out; /* Можно настроить плавность */
}

/* Стили могут быть и в <style> секции без scoped, если нужно переопределить Tailwind */
</style>
