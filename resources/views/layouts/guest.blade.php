<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sign in - XAdmin') }}</title>
        <script>!function () { try { var t = localStorage.getItem("dash26-theme"), e = window.matchMedia("(prefers-color-scheme: dark)").matches; document.documentElement.setAttribute("data-theme", t || (e ? "dark" : "light")) } catch (t) { document.documentElement.setAttribute("data-theme", "light") } }()</script>

        @vite([
            // CSS
            'resources/css/app.css', 
            'resources/css/style.css',
            // JS
            'resources/js/app.js',
            'resources/js/vendor-fullcalendar.js',
            'resources/js/vendor-chartjs.js',
            'resources/js/vendors.js',
            'resources/js/2026.js',
            'resources/js/runtime.js'
        ])
    </head>
    <body>
        <div class="auth-shell">
            <aside class="auth-aside">
                <div class="auth-brand">
                    <div class="logo"><svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#fff"
                                d="M14.747 9.125c.527-1.426 1.736-2.573 3.317-2.573c1.643 0 2.792 1.085 3.318 2.573l6.077 16.867c.186.496.248.931.248 1.147c0 1.209-.992 2.046-2.139 2.046c-1.303 0-1.954-.682-2.264-1.611l-.931-2.915h-8.62l-.93 2.884c-.31.961-.961 1.642-2.232 1.642c-1.24 0-2.294-.93-2.294-2.17c0-.496.155-.868.217-1.023l6.233-16.867zm.34 11.256h5.891l-2.883-8.992h-.062l-2.946 8.992z" />
                        </svg></div>
                    <div class="name">{{ config('app.name', 'XAdmin') }}</div>
                </div>
                <div class="auth-aside-body">
                    <span class="auth-aside-eyebrow">Admin Console · Secure Access</span>
                    <h1>Manage your store from one admin workspace.</h1>
                    <p>Track orders, update inventory, manage products, and monitor sales performance without switching between tools.</p>
                    <div class="auth-quote">
                        <div><strong>Core modules</strong></div>
                        <div>Orders & fulfillment</div>
                        <div>Product catalog</div>
                        <div>Sales & revenue reports</div>
                        <div class="auth-quote-author">
                            <div class="av">RB</div>
                            <div>Role-based access · Session protected · Activity logged</div>
                        </div>
                    </div>
                </div>
                <div class="auth-aside-footer"><span>&copy; {{ date('Y') }} {{ config('app.name', 'NTBook') }}</span> <span>Internal use only</span></div>
            </aside>
            <main class="auth-main">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
