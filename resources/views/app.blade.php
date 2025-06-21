@php
    use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
@endphp
<!DOCTYPE html>
{{-- Отладка локали --}}
{{-- dd@(LaravelLocalization::getCurrentLocale(), app()->getLocale()) --}}
<html lang="{{ LaravelLocalization::getCurrentLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- 🛡 Запрет Google Translate --}}
        <meta name="google" content="notranslate">
        <meta http-equiv="Content-Language" content="{{ LaravelLocalization::getCurrentLocale() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- public/tinymce/tinymce.min.js -->
        <script src="/tinymce/tinymce.min.js"></script>

        <!-- public/tinymce/skins/ui/oxide/skin.min.css -->
        <link rel="stylesheet" href="/tinymce/skins/ui/oxide/skin.min.css">

        <!-- public/tinymce/skins/content/default/content.min.css -->
        <link rel="stylesheet" href="/tinymce/skins/content/default/content.min.css">

        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-D2PN0MWT5Q"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-D2PN0MWT5Q');
        </script>

        <!-- Yandex.Metrika counter -->
        <script type="text/javascript" >
            (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                m[i].l=1*new Date();
                for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
                k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
            (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

            ym(102895527, "init", {
                clickmap:true,
                trackLinks:true,
                accurateTrackBounce:true
            });
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/102895527" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
