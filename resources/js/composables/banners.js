import {usePage} from "@inertiajs/vue3";

export function useBanner() {
    const hasSection = (title) => usePage().props.sections.includes(title);
    const hasBanners = (title) => usePage().props.banners.includes(title);

    return {hasSection,hasBanners}
}
