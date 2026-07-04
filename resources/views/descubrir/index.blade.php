<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-dark">Descubrir</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            <p class="text-malva/75 mb-6">Personas que podrían sintonizar contigo. Tómate tu tiempo.</p>

            @forelse ($people as $person)
                @php $u = $person['user']; $p = $u->profile; @endphp
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-5">
                    <x-card>
                        <div class="flex items-start gap-4">
                            {{-- Avatar --}}
                            @if ($person['photo'])
                                <img src="{{ asset('storage/' . $person['photo']->path) }}" alt="{{ $p->display_name }}"
                                     class="w-16 h-16 rounded-2xl object-cover border border-lavanda/30 shrink-0">
                            @else
                                <div class="w-16 h-16 rounded-2xl bg-lavanda/20 flex items-center justify-center shrink-0">
                                    <span class="font-serif text-2xl text-malva">{{ mb_substr($p->display_name ?? $u->name, 0, 1) }}</span>
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-serif text-xl font-semibold text-dark truncate">{{ $p->display_name ?? $u->name }}</h3>
                                    <button @click="show = false" class="text-sm text-lavanda hover:text-malva shrink-0">Pasar</button>
                                </div>
                                <p class="text-sm text-malva/70">
                                    @if ($p->city){{ $p->city }}@endif
                                    @if ($p->looking_for)
                                        <span class="mx-1 text-lavanda">·</span>Busca {{ strtolower($lookingForLabels[$p->looking_for] ?? '') }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Intereses visibles --}}
                        @if ($person['interests']->count())
                            <div class="mt-4 flex flex-wrap gap-1.5">
                                @foreach ($person['interests'] as $interest)
                                    <x-badge variant="neutral">{{ $interest->name }}</x-badge>
                                @endforeach
                            </div>
                        @endif

                        {{-- Áreas de sintonía --}}
                        @if (count($person['sintonias']))
                            <div class="mt-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-lavanda mb-2">Áreas de Sintonía</p>
                                <x-sintonias :items="$person['sintonias']" />
                            </div>
                        @endif

                        {{-- Acciones --}}
                        <div class="mt-5 flex items-center gap-3">
                            <a href="{{ route('perfiles.show', $u) }}" class="flex-1">
                                <x-secondary-button type="button" class="w-full justify-center">Ver perfil</x-secondary-button>
                            </a>

                            @if ($person['connected'])
                                <x-badge variant="success" class="px-4 py-2.5">Conectados</x-badge>
                            @elseif ($person['request_status'] === 'pending')
                                <x-badge variant="malva" class="px-4 py-2.5">Solicitud enviada</x-badge>
                            @else
                                <form method="POST" action="{{ route('perfiles.conectar', $u) }}" class="flex-1">
                                    @csrf
                                    <x-primary-button class="w-full justify-center">Conectar</x-primary-button>
                                </form>
                            @endif
                        </div>
                    </x-card>
                </div>
            @empty
                <x-card class="text-center py-12">
                    <p class="font-serif text-xl text-dark">Por ahora no hay nadie nuevo por aquí</p>
                    <p class="mt-2 text-sm text-malva/70">Kina está creciendo poco a poco. Vuelve más tarde con calma.</p>
                </x-card>
            @endforelse
        </div>
    </div>
</x-app-layout>
