import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { initI18n } from './utils/i18n.js'; // Обновленный импорт i18n
import { createHead } from '@vueuse/head';

// --- Toastification ИМПОРТЫ ---
import Toast, { POSITION } from "vue-toastification";
import "vue-toastification/dist/index.css";
// --- Конец импорта ---

// --- FontAwesome (если используете) ---
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons'; //Импорт solid иконок
library.add(fas); // Добавление solid иконок в библиотеку
// --- Конец FontAwesome ---

import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';
import '@vue-flow/controls/dist/style.css';
import '@vue-flow/minimap/dist/style.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Асинхронно инициализируем i18n перед созданием приложения
        initI18n().then((i18n) => {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue)
                .use(i18n) // Подключаем i18n после его инициализации
                .use(Toast, toastOptions) // <--- ДОБАВЛЯЕМ РЕГИСТРАЦИЮ TOAST
                .use(createHead())
                .mount(el);
        });
        // --- Настройки Toastification ---
        const toastOptions = {
            position: POSITION.TOP_RIGHT,
            timeout: 3000,
            closeOnClick: true,
            pauseOnFocusLoss: true,
            pauseOnHover: true,
            draggable: true,
            draggablePercent: 0.6,
            showCloseButtonOnHover: false,
            hideProgressBar: false,
            closeButton: "button",
            icon: true,
            rtl: false
        };
        // --- Конец настроек ---
    },
    progress: {
        color: '#4B5563',
        delay: 0,
    },
});
