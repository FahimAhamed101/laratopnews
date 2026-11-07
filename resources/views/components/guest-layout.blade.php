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
            .flex { display: flex; }
            .flex-col { flex-direction: column; }
            .items-center { align-items: center; }
            .bg-gray-100 { background: #f3f4f6; }
            .bg-white { background: #fff; }
            .w-full { width: 100%; }
            .mt-1 { margin-top: .25rem; }
            .mt-2 { margin-top: .5rem; }
            .mt-4 { margin-top: 1rem; }
            .mt-6 { margin-top: 1.5rem; }
            .mb-4 { margin-bottom: 1rem; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
            .py-2 { padding-top: .5rem; padding-bottom: .5rem; }
            .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
            .pt-6 { padding-top: 1.5rem; }
            .overflow-hidden { overflow: hidden; }
            .shadow-md { box-shadow: 0 4px 6px rgba(15, 23, 42, .1); }
            .rounded-md, .sm\:rounded-lg { border-radius: 8px; }
            .block { display: block; }
            .text-sm { font-size: .875rem; }
            .font-medium { font-weight: 500; }
            .text-gray-600 { color: #4b5563; }
            .text-gray-700 { color: #374151; }
            .text-gray-900 { color: #111827; }
            .text-red-600 { color: #dc2626; }
            .space-y-1 > * + * { margin-top: .25rem; }
            input { box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 8px; padding: .55rem .75rem; }
            button { font: inherit; cursor: pointer; }
            @media (min-width: 640px) { .sm\:justify-center { justify-content: center; } .sm\:pt-0 { padding-top: 0; } .sm\:max-w-md { max-width: 28rem; } }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
