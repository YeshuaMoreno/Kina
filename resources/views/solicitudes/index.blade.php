<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-dark">Solicitudes</h2>
            <a href="{{ route('conexiones.index') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">Ver conexiones →</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 space-y-8">

            {{-- Recibidas --}}
            <section>
                <h3 class="font-serif text-xl font-semibold text-dark mb-4">Recibidas</h3>

                @forelse ($received as $req)
                    @php $s = $req->sender; $sp = $s->profile; @endphp
                    <x-card class="mb-4">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-lavanda/20 flex items-center justify-center shrink-0">
                                <span class="font-serif text-xl text-malva">{{ mb_substr($sp->display_name ?? $s->name, 0, 1) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('perfiles.show', $s) }}" class="font-serif text-lg font-semibold text-dark hover:underline">
                                    {{ $sp->display_name ?? $s->name }}
                                </a>
                                @if ($req->message)
                                    <p class="mt-1 text-sm text-malva/80 bg-tiza/60 border border-lavanda/20 rounded-xl px-3 py-2">"{{ $req->message }}"</p>
                                @endif
                                <div class="mt-3 flex gap-3">
                                    <form method="POST" action="{{ route('solicitudes.aceptar', $req) }}" class="flex-1">
                                        @csrf
                                        <x-primary-button class="w-full justify-center">Aceptar</x-primary-button>
                                    </form>
                                    <form method="POST" action="{{ route('solicitudes.rechazar', $req) }}" class="flex-1">
                                        @csrf
                                        <x-secondary-button type="submit" class="w-full justify-center">Rechazar</x-secondary-button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </x-card>
                @empty
                    <x-card class="text-center py-8">
                        <p class="text-malva/70">No tienes solicitudes pendientes.</p>
                    </x-card>
                @endforelse
            </section>

            {{-- Enviadas --}}
            @if ($sent->count())
                <section>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-4">Enviadas</h3>
                    @php
                        $statusLabels = [
                            'pending' => ['Pendiente', 'neutral'],
                            'accepted' => ['Aceptada', 'success'],
                            'rejected' => ['Rechazada', 'malva'],
                            'cancelled' => ['Cancelada', 'malva'],
                        ];
                    @endphp
                    <x-card>
                        <ul class="divide-y divide-lavanda/20">
                            @foreach ($sent as $req)
                                @php [$label, $variant] = $statusLabels[$req->status] ?? ['—', 'neutral']; @endphp
                                <li class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                                    <span class="text-malva">{{ $req->receiver->profile->display_name ?? $req->receiver->name }}</span>
                                    <x-badge :variant="$variant">{{ $label }}</x-badge>
                                </li>
                            @endforeach
                        </ul>
                    </x-card>
                </section>
            @endif

        </div>
    </div>
</x-app-layout>
