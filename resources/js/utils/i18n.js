import { createI18n } from 'vue-i18n';
import en from '../locales/en.js';
import ru from '../locales/ru.js';
import kz from '../locales/kz.js';
import axios from 'axios';

const messages = {
    en,
    ru,
    kz,
};

const loadSavedLocale = () => {
    return new Promise((resolve) => {
        if (window.initialLocale) {
            resolve(window.initialLocale);
        } else {
            axios.get('/settings/locale')
                .then(response => resolve(response.data.locale || 'ru'))
                .catch(error => {
                    console.error('Ошибка при загрузке языка:', error);
                    resolve('ru');
                });
        }
    });
};

const createI18nInstance = async () => {
    const savedLocale = await loadSavedLocale();
    return createI18n({
        locale: savedLocale,
        fallbackLocale: 'en',
        messages,
    });
};

let i18n;

export const initI18n = async () => {
    if (!i18n) {
        i18n = await createI18nInstance();
    }
    return i18n;
};

export const updateLanguage = async (newLocale) => {
    try {
        await axios.post('/settings/locale', { locale: newLocale });
        console.log('Язык успешно обновлен');
    } catch (error) {
        console.error('Ошибка при обновлении языка:', error);
    }
};
