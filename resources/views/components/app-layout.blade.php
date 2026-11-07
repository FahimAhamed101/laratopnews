<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>
            body { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #111827; }
            .min-h-screen { min-height: 100vh; }
            .bg-gray-100 { background: #f3f4f6; }
            .bg-white { background: #fff; }
            .border-b { border-bottom: 1px solid #f3f4f6; }
            .shadow { box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
            .shadow-sm { box-shadow: 0 1px 2px rgba(15, 23, 42, .08); }
            .rounded-md, .sm\:rounded-lg { border-radius: 8px; }
            .max-w-7xl { max-width: 80rem; }
            .max-w-xl { max-width: 36rem; }
            .mx-auto { margin-left: auto; margin-right: auto; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
            .py-12 { padding-top: 3rem; padding-bottom: 3rem; }
            .p-4 { padding: 1rem; }
            .p-6 { padding: 1.5rem; }
            .mt-1 { margin-top: .25rem; }
            .mt-2 { margin-top: .5rem; }
            .mt-6 { margin-top: 1.5rem; }
            .space-y-6 > * + * { margin-top: 1.5rem; }
            .flex { display: flex; }
            .items-center { align-items: center; }
            .justify-between { justify-content: space-between; }
            .gap-4 { gap: 1rem; }
            .gap-6 { gap: 1.5rem; }
            .h-16 { height: 4rem; }
            .block { display: block; }
            .w-full { width: 100%; }
            .text-sm { font-size: .875rem; }
            .text-lg { font-size: 1.125rem; }
            .text-xl { font-size: 1.25rem; }
            .font-medium { font-weight: 500; }
            .font-semibold { font-weight: 600; }
            .text-gray-600 { color: #4b5563; }
            .text-gray-700 { color: #374151; }
            .text-gray-800 { color: #1f2937; }
            .text-gray-900 { color: #111827; }
            .text-red-600 { color: #dc2626; }
            .leading-tight { line-height: 1.25; }
            a, button { color: inherit; }
            a { text-decoration: none; }
            button { font: inherit; cursor: pointer; }
            input { box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 8px; padding: .55rem .75rem; }
            @media (min-width: 640px) { .sm\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; } .sm\:p-8 { padding: 2rem; } }
            @media (min-width: 1024px) { .lg\:px-8 { padding-left: 2rem; padding-right: 2rem; } }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen">
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center gap-6">
                            <a href="{{ route('home') }}" class="font-semibold text-gray-900">
                                {{ $settings['site_name'] ?? 'Extremis News' }}
                            </a>
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Dashboard') }}
                            </a>
                            <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Profile') }}
                            </a>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                            @csrf
                            <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
