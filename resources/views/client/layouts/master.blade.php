<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MNM Bookstore')</title>

    <!-- Google Fonts: Lora (Display) & Nunito (UI) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Styles and Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-bg text-brand-text font-ui antialiased min-h-screen flex flex-col selection:bg-brand-accent selection:text-white">

    <!-- Header Partial -->
    @include('client.partials.header')

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 md:px-6 lg:px-8 max-w-7xl pt-28">
        @yield('content')
    </main>

    <!-- Footer Partial -->
    @include('client.partials.footer')

    @stack('scripts')
</body>
</html>
