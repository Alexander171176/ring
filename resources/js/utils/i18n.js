import { createI18n } from 'vue-i18n';
import en from '../locales/en.js';
import ru from '../locales/ru.js';
import kz from '../locales/kz.js'; // Импортируем казахский язык
import axios from 'axios';

const messages = {
    en,
    ru,
    kz, // Добавляем казахский язык в объект сообщений
};

// Функция для загрузки сохраненного языка из базы данных
const loadSavedLocale = () => {
    return axios.get('/settings/locale')
        .then(response => response.data.locale || 'ru')
        .catch(error => {
            console.error('Ошибка при загрузке языка:', error);
            return 'ru'; // Язык по умолчанию, если произошла ошибка
        });
};

// Инициализация i18n с загруженным языком
const createI18nInstance = async () => {
    const savedLocale = await loadSavedLocale();

    return createI18n({
        locale: savedLocale, // Установите язык по умолчанию
        fallbackLocale: 'en',
        messages,
    });
};

// Создаем экземпляр i18n и экспортируем его через Promise
let i18n;

export const initI18n = async () => {
    if (!i18n) {
        i18n = await createI18nInstance();
    }
    return i18n;
};

// Функция для обновления языка в базе данных
export const updateLanguage = async (newLocale) => {
    try {
        await axios.post('/settings/locale', { locale: newLocale });
        console.log('Язык успешно обновлен');
    } catch (error) {
        console.error('Ошибка при обновлении языка:', error);
    }
};
