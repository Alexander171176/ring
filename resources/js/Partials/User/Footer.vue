<script setup>
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t, locale } = useI18n();

// Загружаем сохранённую локаль из localStorage или используем текущую
const savedLocale = localStorage.getItem('locale') || locale.value;
const selectedLocale = ref(savedLocale);

// Устанавливаем локаль из localStorage при монтировании
onMounted(() => {
    locale.value = savedLocale;
});

// Функция для обновления языка в базе данных и перезагрузки страницы
const updateLanguage = async (newLocale) => {
    try {
        await axios.post('/settings/locale', { locale: newLocale });
        console.log('Язык успешно обновлен в базе данных');

        localStorage.setItem('locale', newLocale); // Сохраняем выбранный язык в localStorage
        window.location.reload(); // Перезагружаем страницу для применения изменений
    } catch (error) {
        console.error('Ошибка при обновлении языка в базе данных:', error);
    }
};

// Следим за изменением выбранного языка
watch(selectedLocale, async (newLocale) => {
    if (newLocale !== locale.value) {
        await updateLanguage(newLocale); // Обновляем язык и перезагружаем страницу
    }
});
</script>

<template>
    <footer class="sticky px-4 py-2 bottom-0 bg-gradient-to-b from-slate-100 to-slate-300 dark:bg-gradient-to-b dark:from-slate-700 dark:to-slate-900 border-t border-slate-200 dark:border-slate-700 z-20">
        <div class="flex items-center justify-between flex-wrap">
            <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-2 sm:mb-0">
                © {{ new Date().getFullYear() }}
                <a href="/" target="_blank" class="font-semibold text-red-400 hover:text-rose-300">DigitalPro.</a>
                {{ t('allRightsReserved') }}
            </div>
            <div class="flex items-center space-x-2">
                <a href="https://t.me/k_a_v_www" target="_blank"
                   class="flex items-center space-x-2 text-blue-500 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"
                         class="w-5 h-5 sm:w-6 sm:h-6">
                        <path
                            d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.175 8.89l-1.4 6.63c-.105.467-.405.578-.82.36l-2.27-1.67-1.093 1.054c-.12.12-.222.222-.45.222l.168-2.39 4.35-3.923c.19-.168-.04-.263-.29-.095L8.78 11.167l-2.42-.76c-.464-.14-.474-.464.096-.684l9.452-3.65c.44-.16.82.108.66.717z"/>
                    </svg>
                    <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ t('supportService') }}</span>
                </a>
                <select v-model="selectedLocale"
                        class="bg-slate-100 dark:bg-slate-300 form-select text-gray-900 dark:text-gray-700 px-3 py-0.5">
                    <option value="ru">{{ t('russian') }}</option>
                    <option value="en">{{ t('english') }}</option>
                    <option value="kz">{{ t('kazakh') }}</option>
                </select>
            </div>
        </div>
    </footer>
</template>
