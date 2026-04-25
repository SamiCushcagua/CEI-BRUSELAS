{{-- PWA mínimo: instalación en pantalla de inicio (Safari iOS, Chrome) — sin Service Worker --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#2563eb">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'CEI Bruselas') }}">
<link rel="apple-touch-icon" href="{{ asset('images/logo-cei.svg') }}">
