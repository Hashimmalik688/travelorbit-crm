<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">

<!-- Phosphor Icons (primary) - beautiful, consistent, free -->
<script type="module" src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>

<!-- Boxicons (fallback) -->
@vite(['resources/assets/vendor/fonts/iconify/iconify.css'])

<!-- Core CSS -->
@vite(['resources/assets/vendor/scss/core.scss', 'resources/assets/css/demo.css'])

<!-- Vendor Styles -->
@vite('resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss')
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')

<!-- app CSS -->
@vite(['resources/css/app.css'])
<!-- END: app CSS-->

{{-- Design System v5 — minimal base. Must stay AFTER the app.css @vite
     link so it wins on cascade order. Delete this line to revert. --}}
@include('layouts.sections.design-v5')

<!-- Livewire Styles -->
@livewireStyles
