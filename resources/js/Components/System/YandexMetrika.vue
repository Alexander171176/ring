<!-- resources/js/Components/YandexMetrika.vue -->
<template>
    <div class="flex items-center justify-center">
        <!-- Информер -->
        <a href="https://metrika.yandex.ru/stat/?id=102895527&amp;from=informer"
           target="_blank" rel="nofollow">
            <img src="https://informer.yandex.ru/informer/102895527/2_1_FFFFFFFF_EFEFEFFF_0_pageviews"
                 style="width:80px; height:31px; border:0;"
                 alt="Яндекс.Метрика"
                 title="Яндекс.Метрика: данные за сегодня (просмотры)"
                 class="ym-advanced-informer"
                 data-cid="102895527" data-lang="ru"/>
        </a>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';

onMounted(() => {
    // Подключаем скрипт Яндекс.Метрики
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){ (m[i].a=m[i].a||[]).push(arguments) };
        m[i].l=1*new Date();
        for (let j=0; j<document.scripts.length; j++) {
            if (document.scripts[j].src === r) return;
        }
        k = e.createElement(t), a = e.getElementsByTagName(t)[0];
        k.async = 1; k.src = r; a.parentNode.insertBefore(k,a);
    })(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

    window.ym && window.ym(102895527, "init", {
        clickmap: true,
        trackLinks: true,
        accurateTrackBounce: true
    });
});
</script>
