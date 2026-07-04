<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('conexiones.index') }}" class="text-malva/70 hover:text-malva shrink-0" aria-label="Volver">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="w-9 h-9 rounded-xl bg-lavanda/20 flex items-center justify-center shrink-0">
                    <span class="font-serif text-malva">{{ mb_substr($other->profile->display_name ?? $other->name, 0, 1) }}</span>
                </div>
                <h2 class="font-serif text-xl font-semibold text-dark truncate">{{ $other->profile->display_name ?? $other->name }}</h2>
            </div>
            <a href="{{ route('perfiles.show', $other) }}" class="text-sm font-semibold text-malva/70 hover:text-malva shrink-0">Ver perfil</a>
        </div>
    </x-slot>

    <div class="py-6"
         x-data="{
            init() {
                this.$refs.msgs.scrollTop = this.$refs.msgs.scrollHeight;
                // Refresh controlado (sin WebSockets): recarga si no estás escribiendo.
                setInterval(() => {
                    if (!document.hidden && this.$refs.body.value.trim() === '') {
                        window.location.reload();
                    }
                }, 20000);
            }
         }">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            <x-card padding="p-0" class="overflow-hidden">
                {{-- Mensajes --}}
                <div x-ref="msgs" class="h-[60vh] overflow-y-auto px-4 sm:px-5 py-5 space-y-3">
                    @forelse ($messages as $message)
                        @php $mine = $message->sender_id === auth()->id(); @endphp
                        <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%]">
                                <div class="px-4 py-2.5 text-sm leading-relaxed
                                            {{ $mine
                                                ? 'bg-malva text-tiza rounded-2xl rounded-br-md'
                                                : 'bg-white/80 border border-lavanda/20 text-malva rounded-2xl rounded-bl-md' }}">
                                    {{ $message->body }}
                                </div>
                                <div class="mt-1 px-1 flex items-center gap-1 text-[11px] text-lavanda {{ $mine ? 'justify-end' : '' }}">
                                    <span>{{ $message->created_at->format('H:i') }}</span>
                                    @if ($mine && $message->read_at)
                                        <span>· Leído</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex items-center justify-center text-center">
                            <p class="text-sm text-lavanda">Aún no hay mensajes.<br>Rompe el hielo cuando te sientas cómodo.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Composer --}}
                <div class="border-t border-lavanda/20 p-3 bg-tiza/60">
                    @error('body')
                        <p class="text-xs text-error px-1 pb-2">{{ $message }}</p>
                    @enderror
                    <form method="POST" action="{{ route('conversaciones.mensajes.store', $conversation) }}" class="flex items-end gap-2">
                        @csrf
                        <textarea x-ref="body" name="body" rows="1" maxlength="2000" required
                                  placeholder="Escribe con calma…"
                                  @keydown.enter.prevent="$event.target.form.requestSubmit()"
                                  class="flex-1 resize-none bg-white/80 border-lavanda/40 text-malva placeholder-lavanda/70 focus:border-malva focus:ring-lavanda rounded-2xl max-h-32"></textarea>
                        <button type="submit" class="shrink-0 inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-malva text-tiza hover:bg-dark transition" aria-label="Enviar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </form>
                </div>
            </x-card>

            <p class="mt-4 text-center text-xs text-lavanda">Se actualiza solo cada poco. También puedes recargar cuando quieras.</p>
        </div>
    </div>
</x-app-layout>
