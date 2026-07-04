<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-dark">Perfil</h2>
            <a href="{{ route('descubrir.index') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Descubrir</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 space-y-5">

            {{-- Cabecera --}}
            <x-card>
                <div class="flex items-start gap-5">
                    @if ($photo)
                        <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $profile->display_name }}"
                             class="w-24 h-24 rounded-2xl object-cover border border-lavanda/30 shrink-0">
                    @else
                        <div class="w-24 h-24 rounded-2xl bg-lavanda/20 flex items-center justify-center shrink-0">
                            <span class="font-serif text-4xl text-malva">{{ mb_substr($profile->display_name ?? $user->name, 0, 1) }}</span>
                        </div>
                    @endif

                    <div class="min-w-0">
                        <h1 class="font-serif text-2xl font-semibold text-dark">{{ $profile->display_name ?? $user->name }}</h1>
                        <p class="text-sm text-malva/70 mt-0.5">
                            {{ collect([$profile->city, $profile->state, $profile->country])->filter()->join(', ') ?: 'Ubicación no compartida' }}
                        </p>
                        @if ($profile->looking_for)
                            <div class="mt-2">
                                <x-badge variant="rosa">Busca {{ strtolower($lookingForLabels[$profile->looking_for] ?? '') }}</x-badge>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($profile->bio)
                    <p class="mt-5 text-malva/85 leading-relaxed whitespace-pre-line">{{ $profile->bio }}</p>
                @endif
            </x-card>

            {{-- Áreas de sintonía --}}
            @if (count($sintonias))
                <x-card>
                    <p class="text-xs font-semibold uppercase tracking-wide text-lavanda mb-3">Áreas de Sintonía</p>
                    <x-sintonias :items="$sintonias" />
                </x-card>
            @endif

            {{-- Intereses --}}
            @if ($profile->interests->count())
                <x-card>
                    <h3 class="font-serif text-lg font-semibold text-dark mb-3">Intereses</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($profile->interests as $interest)
                            <x-badge variant="neutral">{{ $interest->name }}</x-badge>
                        @endforeach
                    </div>
                </x-card>
            @endif

            {{-- Estilo de comunicación --}}
            @php $pref = $profile->communicationPreference; @endphp
            @if ($pref)
                @php $activePrefs = collect($commLabels)->filter(fn ($l, $f) => (bool) $pref->{$f}); @endphp
                @if ($activePrefs->count() || $profile->social_battery)
                    <x-card>
                        <h3 class="font-serif text-lg font-semibold text-dark mb-3">Cómo se comunica</h3>
                        @if ($profile->social_battery)
                            <p class="text-sm text-malva/80 mb-3">Batería social: <strong>{{ ucfirst($profile->social_battery) }}</strong></p>
                        @endif
                        <ul class="space-y-1.5">
                            @foreach ($activePrefs as $label)
                                <li class="flex items-center gap-2 text-sm text-malva/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-lavanda"></span>{{ $label }}
                                </li>
                            @endforeach
                        </ul>
                    </x-card>
                @endif
            @endif

            {{-- Etiquetas visibles --}}
            @if ($visibleTags->count())
                <x-card>
                    <h3 class="font-serif text-lg font-semibold text-dark mb-3">Etiquetas</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($visibleTags as $tag)
                            <x-badge variant="malva">
                                {{ $tag->name }}
                                @if ($tag->is_sensitive)<span class="w-1.5 h-1.5 rounded-full bg-rosa"></span>@endif
                            </x-badge>
                        @endforeach
                    </div>
                </x-card>
            @endif

            {{-- Acciones --}}
            <x-card>
                <div class="flex flex-col gap-3">
                    @if ($connected)
                        <x-badge variant="success" class="justify-center py-2.5">Ya están conectados</x-badge>
                    @elseif ($incoming && $incoming->status === 'pending')
                        <form method="POST" action="{{ route('solicitudes.aceptar', $incoming) }}">
                            @csrf
                            <x-primary-button class="w-full justify-center">Aceptar su solicitud</x-primary-button>
                        </form>
                    @elseif ($outgoing && $outgoing->status === 'pending')
                        <x-badge variant="malva" class="justify-center py-2.5">Solicitud enviada</x-badge>
                    @else
                        <form method="POST" action="{{ route('perfiles.conectar', $user) }}" x-data="{ open: false }">
                            @csrf
                            <div x-show="open" x-cloak class="mb-3">
                                <x-input-label for="message" value="Mensaje (opcional)" />
                                <textarea id="message" name="message" rows="3" maxlength="500"
                                          class="mt-1 block w-full bg-white/70 border-lavanda/40 text-malva placeholder-lavanda/70 focus:border-malva focus:ring-lavanda rounded-xl"
                                          placeholder="Un saludo breve, si quieres."></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" @click="open = !open" class="text-sm font-semibold text-malva/70 hover:text-malva px-2">
                                    <span x-show="!open">+ Añadir mensaje</span>
                                    <span x-show="open" x-cloak>Quitar mensaje</span>
                                </button>
                                <x-primary-button class="flex-1 justify-center">Conectar</x-primary-button>
                            </div>
                        </form>
                    @endif

                    <div class="flex items-center justify-center gap-6 pt-1 text-sm">
                        <form method="POST" action="{{ route('perfiles.bloquear', $user) }}"
                              onsubmit="return confirm('¿Bloquear a esta persona? No volverá a aparecer ni podrá contactarte.')">
                            @csrf
                            <button type="submit" class="text-malva/60 hover:text-error transition">Bloquear</button>
                        </form>
                        <a href="{{ route('perfiles.reportar', $user) }}" class="text-malva/60 hover:text-error transition">Reportar</a>
                    </div>
                </div>
            </x-card>

        </div>
    </div>
</x-app-layout>
