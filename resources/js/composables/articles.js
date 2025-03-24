import {usePage} from "@inertiajs/vue3";

export function useArticle() {
    const hasSection = (title) => usePage().props.sections.includes(title);
    const hasArticles = (title) => usePage().props.articles.includes(title);

    return {hasSection,hasArticles}
}
