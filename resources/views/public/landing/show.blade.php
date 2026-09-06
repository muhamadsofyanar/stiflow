<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $landingPage->title }} — {{ config('app.name') }}</title>
    @if($meta['description'] ?? null)
        <meta name="description" content="{{ $meta['description'] }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white min-h-screen">
    {!! $blocksHtml !!}
</body>
</html>
