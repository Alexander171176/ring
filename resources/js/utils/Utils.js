import resolveConfig from 'tailwindcss/resolveConfig';
import tailwindConfigFile from '../../css/tailwind.config.js'; // Убедитесь, что путь правильный

export const tailwindConfig = () => {
    // Tailwind config
    return resolveConfig(tailwindConfigFile);
};

export const hexToRGB = (h) => {
    let r = 0;
    let g = 0;
    let b = 0;
    if (h.length === 4) {
        r = `0x${h[1]}${h[1]}`;
        g = `0x${h[2]}${h[2]}`;
        b = `0x${h[3]}${h[3]}`;
    } else if (h.length === 7) {
        r = `0x${h[1]}${h[2]}`;
        g = `0x${h[3]}${h[4]}`;
        b = `0x${h[5]}${h[6]}`;
    }
    return `${+r},${+g},${+b}`;
};

export const formatValue = (value) => Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    maximumSignificantDigits: 3,
    notation: 'compact',
}).format(value);

export const formatThousands = (value) => Intl.NumberFormat('ru-RU', {
    maximumSignificantDigits: 3,
    notation: 'compact',
}).format(value);
