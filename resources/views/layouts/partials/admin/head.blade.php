<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'PurrfectCare - Админ-панель')</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/air-datepicker@3.4.0/air-datepicker.css">
@stack('styles')
@vite(['resources/sass/app.scss', 'resources/js/app.js'])
