@props([
    'step' => 1,
    'total' => 7,
    'title' => '',
    'subtitle' => '',
])

@php
    $percent = (int) round(($step / $total) * 100);
@endphp

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · Kina</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|nunito:400,500,600,700|inter:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-tiza text-malva antialiased min-h-screen flex flex-col">

    {{-- Barra superior --}}
    <header class="border-b border-lavanda/20">
        <div class="max-w-2xl mx-auto px-5 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="w-7 h-7 text-malva" />
                <span class="font-serif text-xl font-semibold text-malva">Kina</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-malva/60 hover:text-malva transition">Salir</button>
            </form>
        </div>
    </header>

    {{-- Progreso --}}
    <div class="max-w-2xl w-full mx-auto px-5 sm:px-6 pt-8">
        <div class="flex items-center justify-between mb-2 text-sm">
            <span class="font-semibold text-malva">Paso {{ $step }} de {{ $total }}</span>
            <span class="text-lavanda">{{ $percent }}%</span>
        </div>
        <div class="h-2 rounded-full bg-lavanda/20 overflow-hidden">
            <div class="h-full rounded-full bg-rosa transition-all duration-500" style="width: {{ $percent }}%"></div>
        </div>
    </div>

    {{-- Contenido --}}
    <main class="flex-1 max-w-2xl w-full mx-auto px-5 sm:px-6 py-8 sm:py-10">
        <div class="mb-7">
            <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-dark">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-2 text-malva/75 text-lg">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($errors->any())
            <x-alert type="error" title="Revisa estos puntos:" class="mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <x-card>
            {{ $slot }}
        </x-card>

        <p class="mt-6 text-center text-xs text-lavanda">
            Puedes editar todo esto más tarde desde tu perfil.
        </p>
    </main>

</body>
</html>
