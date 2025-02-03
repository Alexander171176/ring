<template>
    <div ref="editor" class="code-editor"></div>
</template>

<script setup>
import {onMounted, ref, watch} from 'vue';
import {EditorState} from '@codemirror/state';
import {EditorView, keymap, lineNumbers, highlightActiveLineGutter} from '@codemirror/view';
import {defaultKeymap, history, historyKeymap} from '@codemirror/commands';
import {foldGutter, foldKeymap, indentOnInput, syntaxHighlighting, defaultHighlightStyle} from '@codemirror/language';
import {javascript} from '@codemirror/lang-javascript';
import {oneDark} from '@codemirror/theme-one-dark';

const props = defineProps({
    modelValue: String,
    theme: {
        type: String,
        default: 'light', // Можно переключать на 'dark' для темной темы
    },
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
let editorView;

onMounted(() => {
    const myTheme = EditorView.theme({
        ".cm-content": {
            "font-size": "16px" // Устанавливаем размер шрифта
        }
    });

    const extensions = [
        lineNumbers(),
        highlightActiveLineGutter(),
        history(),
        foldGutter(),
        indentOnInput(),
        syntaxHighlighting(defaultHighlightStyle),
        keymap.of([...defaultKeymap, ...historyKeymap, ...foldKeymap]),
        javascript(),
        myTheme, // Добавляем пользовательскую тему
        EditorView.updateListener.of((update) => {
            if (update.docChanged) {
                emit('update:modelValue', update.state.doc.toString());
            }
        }),
    ];

    if (props.theme === 'dark') {
        extensions.push(oneDark);
    }

    editorView = new EditorView({
        state: EditorState.create({
            doc: props.modelValue,
            extensions,
        }),
        parent: editor.value,
    });
});

watch(
    () => props.modelValue,
    (newValue) => {
        if (editorView) {
            const currentValue = editorView.state.doc.toString();
            if (newValue !== currentValue) {
                editorView.dispatch({
                    changes: {from: 0, to: currentValue.length, insert: newValue},
                });
            }
        }
    }
);
</script>

<style scoped>
.code-editor {
    height: 100%;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>
