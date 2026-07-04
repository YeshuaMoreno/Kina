<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-dark">Conexiones</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            @forelse ($items as $item)
                @php $other = $item['other']; $p = $item['profile']; $last = $item['last']; @endphp
                <a href="{{ route('conversaciones.show', $item['conversation']) }}" class="block mb-4 group">
                    <x-card class="transition group-hover:border-lavanda/50">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-lavanda/20 flex items-center justify-center shrink-0">
                                <span class="font-serif text-xl text-malva">{{ mb_substr($p->display_name ?? $other->name, 0, 1) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-serif text-lg font-semibold text-dark truncate">{{ $p->display_name ?? $other->name }}</h3>
                                    @if ($last)
                                        <span class="text-xs text-lavanda shrink-0">{{ $last->created_at->diffForHumans(short: true) }}</span>
                                    @endif
                                </div>
                                @if ($last)
                                    <p class="text-sm text-malva/70 truncate">
                                        @if ($last->sender_id === auth()->id())<span class="text-lavanda">Tú: </span>@endif{{ $last->body }}
                                    </p>
                                @else
                                    <p class="text-sm text-lavanda italic">Aún no se han escrito. Salúdense sin prisa.</p>
                                @endif
                            </div>
                        </div>
                    </x-card>
                </a>
            @empty
                <x-card class="text-center py-12">
                    <p class="font-serif text-xl text-dark">Todavía no tienes conexiones</p>
                    <p class="mt-2 text-sm text-malva/70">Cuando alguien acepte tu solicitud, aparecerá aquí para conversar.</p>
                    <a href="{{ route('descubrir.index') }}" class="inline-block mt-5">
                        <x-primary-button>Descubrir personas</x-primary-button>
                    </a>
                </x-card>
            @endforelse
        </div>
    </div>
</x-app-layout>
