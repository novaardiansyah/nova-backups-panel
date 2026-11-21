@php
    $authUser = auth()->user();
    $userTheme = $authUser?->preferredTheme() ?? ($appearance ?? 'system');
    $userLocale = $authUser?->preferredLocale() ?? app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $userLocale) }}" @class(['dark' => $userTheme === 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script>
            (function () {
                var theme = @json($userTheme);
                if (theme === 'system') {
                    var stored = null;
                    try { stored = localStorage.getItem('nova-theme'); } catch (e) {}
                    if (stored === 'dark' || (stored !== 'light' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                        document.documentElement.classList.add('dark');
                    }
                } else {
                    document.documentElement.classList.toggle('dark', theme === 'dark');
                    try { localStorage.setItem('nova-theme', theme); } catch (e) {}
                }
            })();
        </script>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
