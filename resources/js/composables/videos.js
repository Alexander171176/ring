import {usePage} from "@inertiajs/vue3";

export function useVideo() {
    const hasSection = (title) => usePage().props.sections.includes(title);
    const hasVideos = (title) => usePage().props.articles.includes(title);

    return {hasSection,hasVideos}
}
