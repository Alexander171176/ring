import {usePage} from "@inertiajs/vue3";

export function useGuide() {
    const hasTutorial = (title) => usePage().props.tutorials.includes(title);
    const hasGuides = (title) => usePage().props.guides.includes(title);

    return {hasTutorial,hasGuides}
}
