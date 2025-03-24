import {usePage} from "@inertiajs/vue3";

export function useRubric() {
    const hasRubric = (title) => usePage().props.rubrics.includes(title);
    const hasSection = (title) => usePage().props.sections.includes(title);

    return {hasRubric,hasSection}
}
