<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' · ' . config('app.name', 'XAdmin') : config('app.name', 'XAdmin') }}</title>
    <script>
        ! function() {
            try {
                var t = localStorage.getItem("dash26-theme"),
                    e = window.matchMedia("(prefers-color-scheme: dark)").matches;
                document.documentElement.setAttribute("data-theme", t || (e ? "dark" : "light"))
            } catch (t) {
                document.documentElement.setAttribute("data-theme", "light")
            }
        }()
    </script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        @vite(array_merge(['resources/css/style.css', 'resources/css/admin.css', 'resources/js/admin.js'], $vite))
    @stack('styles')
</head>

<body data-active="{{ $active }}" data-crumbs="{{ $crumbs }}">
    <div @class(['shell'])>
        @include('partials.admin.sidebar', ['active' => $active])
        <div @class(['main'])>
            @include('partials.admin.topbar', ['crumbs' => $crumbs])
            <main @class(['content'])>
                {{ $slot }}
            </main>
            @include('partials.admin.footer')
        </div>
    </div>
    @stack('scripts')
</body>

</html>
