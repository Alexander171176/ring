<template>
    <div class="liveinternet-counter" v-if="mounted">
        <a href="https://www.liveinternet.ru/click" target="_blank">
            <img
                id="licnt15C5"
                width="88"
                height="31"
                style="border:0"
                :src="counterSrc"
                alt=""
                title="LiveInternet: показано число просмотров за 24 часа, посетителей за 24 часа и за сегодня"
            />
        </a>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const mounted = ref(false)
const counterSrc = ref('')

onMounted(() => {
    const screen = window.screen
    const referrer = escape(document.referrer)
    const screenInfo = screen
        ? `;s${screen.width}*${screen.height}*${screen.colorDepth || screen.pixelDepth}`
        : ''
    const currentUrl = escape(document.URL)
    const title = escape(document.title.substring(0, 150))

    counterSrc.value =
        'https://counter.yadro.ru/hit?t14.6;r' +
        referrer +
        screenInfo +
        ';u' + currentUrl +
        ';h' + title +
        ';' + Math.random()

    mounted.value = true
})
</script>
