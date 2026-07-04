<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-dark">
            Hola, {{ Auth::user()->profile->display_name ?? Auth::user()->name }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-malva/75 mb-8">¿Con qué ganas de conectar llegas hoy? Sin prisa.</p>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $accesos = [
                        ['descubrir.index', 'Descubrir', 'Encuentra personas afines a tu ritmo.', 'M12 4v16m8-8H4'],
                        ['solicitudes.index', 'Solicitudes', 'Revisa quién quiere conectar contigo.', 'M4 6h16M4 12h16M4 18h7'],
                        ['profile.edit', 'Mi perfil', 'Edita tu información y tus etiquetas.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        ['profile.edit', 'Configuración', 'Privacidad, seguridad y cuenta.', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ];
                @endphp

                @foreach ($accesos as [$route, $titulo, $texto, $icon])
                    <a href="{{ route($route) }}" class="group">
                        <x-card class="h-full transition group-hover:border-lavanda/50 group-hover:-translate-y-0.5">
                            <div class="w-11 h-11 rounded-xl bg-lavanda/15 flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-malva" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                </svg>
                            </div>
                            <h3 class="font-serif text-lg font-semibold text-dark">{{ $titulo }}</h3>
                            <p class="mt-1 text-sm text-malva/70">{{ $texto }}</p>
                        </x-card>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
