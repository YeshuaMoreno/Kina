<x-app-layout>
    @php
        $lookingForLabels = [
            'amistad' => 'Amistad',
            'pareja_formal' => 'Pareja formal',
            'algo_casual' => 'Algo casual',
            'comunidad' => 'Comunidad',
        ];
        $batteryLabels = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta'];

        $nombre = $profile->display_name ?? $me->name;
        $intencion = $profile?->looking_for ? ($lookingForLabels[$profile->looking_for] ?? null) : null;
        $bateria = $profile?->social_battery ? ($batteryLabels[$profile->social_battery] ?? null) : null;
        $ciudad = $profile?->city;
        $intereses = $profile?->interests ?? collect();
        $etiquetas = $profile?->identityTags ?? collect();

        $acciones = [
            ['descubrir.index', 'Descubrir', 'Personas afines a tu ritmo', 'M15.5 15.5 19 19M5 11a6 6 0 1 0 12 0 6 6 0 0 0-12 0Z', null],
            ['conexiones.index', 'Conexiones', 'Tus conversaciones', 'M8 10h.01M12 10h.01M16 10h.01M21 12a9 9 0 0 1-13 8l-5 1 1-4A9 9 0 1 1 21 12Z', $connectionsCount ?: null],
            ['solicitudes.index', 'Solicitudes', 'Quién quiere conectar', 'M16 11V7a4 4 0 1 0-8 0v4M5 11h14l-1 9H6l-1-9Z', $pendingRequests ?: null],
            ['profile.edit', 'Mi perfil', 'Edita quién eres en Kina', 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM6 21a6 6 0 0 1 12 0', null],
            ['profile.edit', 'Privacidad y seguridad', 'Tú decides qué se ve', 'M12 3l7 4v5c0 4.4-3 8.3-7 9-4-.7-7-4.6-7-9V7l7-4Z', null],
        ];
        if ($me->is_admin) {
            $acciones[] = ['admin.dashboard', 'Administración', 'Moderación y reportes', 'M9 12l2 2 4-4M12 3l7 4v5c0 4.4-3 8.3-7 9-4-.7-7-4.6-7-9V7l7-4Z', null];
        }
    @endphp

    <div class="py-8 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ===================== HERO ===================== --}}
            <section class="relative overflow-hidden rounded-3xl border border-lavanda/25 bg-gradient-to-br from-white/80 via-tiza to-rosa/20 px-6 py-8 sm:px-10 sm:py-11 shadow-sm">
                {{-- Órbita decorativa --}}
                <div class="pointer-events-none absolute -top-16 -right-14 w-72 h-72 opacity-30 hidden sm:block" aria-hidden="true">
                    <svg viewBox="0 0 300 300" fill="none" class="w-full h-full">
                        <circle cx="150" cy="150" r="118" stroke="#9A8C98" stroke-width="2"/>
                        <circle cx="150" cy="150" r="74" stroke="#C9ADA7" stroke-width="2"/>
                        <circle cx="150" cy="32" r="11" fill="#C9ADA7"/>
                        <circle cx="224" cy="150" r="7" fill="#9A8C98"/>
                    </svg>
                </div>

                <div class="relative max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-widest text-lavanda mb-2">Tu espacio en Kina</p>
                    <h1 class="font-serif text-4xl sm:text-5xl font-semibold text-dark leading-[1.1]">
                        Hola, {{ $nombre }}
                    </h1>
                    <p class="mt-4 text-lg text-malva/80 leading-relaxed">
                        Qué bueno tenerte por aquí. Conecta cuando quieras, a tu propio ritmo —
                        sin prisas y sin fingir otra versión de ti.
                    </p>

                    {{-- Resumen del usuario --}}
                    @if ($intencion || $bateria || $ciudad)
                        <div class="mt-6 flex flex-wrap gap-2">
                            @if ($intencion)<x-badge variant="rosa">Busca {{ strtolower($intencion) }}</x-badge>@endif
                            @if ($bateria)<x-badge variant="malva">Batería {{ strtolower($bateria) }}</x-badge>@endif
                            @if ($ciudad)<x-badge variant="neutral">{{ $ciudad }}</x-badge>@endif
                        </div>
                    @endif

                    {{-- CTAs --}}
                    <div class="mt-7 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('descubrir.index') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-malva text-tiza font-semibold hover:bg-dark transition shadow-sm">
                            Descubrir personas
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <a href="{{ route('solicitudes.index') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white/70 border border-lavanda/40 text-malva font-semibold hover:bg-lavanda/10 transition">
                            Ver solicitudes
                            @if ($pendingRequests)
                                <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-rosa text-dark text-xs font-bold">{{ $pendingRequests }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </section>

            {{-- ===================== ACCIONES ===================== --}}
            <section>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($acciones as [$route, $titulo, $texto, $icon, $count])
                        <a href="{{ route($route) }}" class="group">
                            <div class="h-full rounded-2xl border border-lavanda/20 bg-white/70 p-5 shadow-sm transition duration-200 group-hover:-translate-y-0.5 group-hover:border-lavanda/50 group-hover:shadow-md">
                                <div class="flex items-start justify-between">
                                    <div class="w-11 h-11 rounded-xl bg-lavanda/15 flex items-center justify-center transition group-hover:bg-rosa/25">
                                        <svg class="w-6 h-6 text-malva" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                        </svg>
                                    </div>
                                    @if ($count)
                                        <span class="inline-flex items-center justify-center min-w-6 h-6 px-2 rounded-full bg-rosa/25 text-dark text-xs font-bold">{{ $count }}</span>
                                    @endif
                                </div>
                                <h3 class="mt-4 font-serif text-lg font-semibold text-dark">{{ $titulo }}</h3>
                                <p class="mt-1 text-sm text-malva/70">{{ $texto }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- ===================== TU ESPACIO HOY + ACTIVIDAD ===================== --}}
            <section class="grid gap-6 lg:grid-cols-3">

                {{-- Tu espacio hoy --}}
                <x-card class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="font-serif text-xl font-semibold text-dark">Tu espacio hoy</h2>
                        <a href="{{ route('profile.edit') }}" class="text-sm font-semibold text-malva/70 hover:text-malva transition">Editar</a>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-lavanda">Intención</p>
                            <p class="mt-1 text-malva font-semibold">{{ $intencion ?? 'Sin definir' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-lavanda">Batería social</p>
                            <p class="mt-1 text-malva font-semibold">{{ $bateria ?? 'Sin definir' }}</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-lavanda mb-2">Intereses destacados</p>
                        @if ($intereses->count())
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($intereses->take(6) as $interest)
                                    <x-badge variant="neutral">{{ $interest->name }}</x-badge>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-malva/60">Aún no eliges intereses.
                                <a href="{{ route('profile.edit') }}" class="text-malva font-semibold hover:underline">Añádelos</a>.
                            </p>
                        @endif
                    </div>

                    @if ($etiquetas->count())
                        <div class="mt-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-lavanda mb-2">Tus etiquetas</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($etiquetas as $tag)
                                    <x-badge variant="malva">
                                        {{ $tag->name }}
                                        @if ($tag->is_sensitive)<span class="w-1.5 h-1.5 rounded-full bg-rosa"></span>@endif
                                    </x-badge>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-card>

                {{-- Actividad --}}
                <div class="space-y-4">
                    @php
                        $stats = [
                            ['Solicitudes pendientes', $pendingRequests, 'solicitudes.index',
                                $pendingRequests ? 'Alguien quiere conectar contigo.' : 'Nada pendiente por ahora.'],
                            ['Conexiones', $connectionsCount, 'conexiones.index',
                                $connectionsCount ? 'Tu círculo en Kina.' : 'Cuando conecten, aparecerán aquí.'],
                            ['Mensajes sin leer', $unreadMessages, 'conexiones.index',
                                $unreadMessages ? 'Te escribieron. Responde con calma.' : 'Al día. Sin prisa.'],
                        ];
                    @endphp

                    @foreach ($stats as [$label, $value, $route, $hint])
                        <a href="{{ route($route) }}" class="block group">
                            <div class="rounded-2xl border border-lavanda/20 bg-white/70 p-5 shadow-sm transition group-hover:border-lavanda/50">
                                <div class="flex items-baseline justify-between">
                                    <p class="text-sm font-semibold text-malva/70">{{ $label }}</p>
                                    <span class="font-serif text-3xl font-semibold {{ $value ? 'text-malva' : 'text-lavanda/60' }}">{{ $value }}</span>
                                </div>
                                <p class="mt-1 text-xs text-malva/60">{{ $hint }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
