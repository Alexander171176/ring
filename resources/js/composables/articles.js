import {usePage} from "@inertiajs/vue3";

export function useArticle() {
    const hasRubric = (title) => usePage().props.rubrics.includes(title);
    const hasArticles = (title) => usePage().props.articles.includes(title);

    return {hasRubric,hasArticles}
}
