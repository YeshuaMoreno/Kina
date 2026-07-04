<x-onboarding.shell :step="4" title="¿Cómo te gusta comunicarte?" subtitle="Esto ayuda a que otras personas sepan tu ritmo desde el inicio.">
    <form method="POST" action="{{ route('onboarding.comunicacion.store') }}" class="space-y-8">
        @csrf

        {{-- Batería social --}}
        <div>
            <p class="font-semibold text-malva mb-2">Tu batería social suele estar…</p>
            <div class="grid grid-cols-3 gap-3">
                @foreach (['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta'] as $value => $label)
                    <label class="block cursor-pointer">
                        <input type="radio" name="social_battery" value="{{ $value }}" class="peer sr-only"
                               @checked(old('social_battery', $profile->social_battery) === $value)>
                        <div class="text-center rounded-xl border border-lavanda/30 bg-tiza/40 py-3 font-semibold text-malva transition
                                    peer-checked:border-malva peer-checked:bg-lavanda/10 peer-focus:ring-2 peer-focus:ring-lavanda">
                            {{ $label }}
                        </div>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('social_battery')" class="mt-1.5" />
        </div>

        {{-- Preferencias --}}
        <div>
            <p class="font-semibold text-malva mb-2">Marca lo que va contigo</p>
            <div class="space-y-2.5">
                @foreach ([
                    'prefers_text' => 'Prefiero texto antes que llamada',
                    'direct_communication' => 'Me gusta la comunicación directa',
                    'slow_responder' => 'A veces me tardo en responder',
                    'prefers_quiet_plans' => 'Prefiero planes tranquilos',
                    'chat_before_meeting' => 'Prefiero conocer primero por chat',
                    'no_surprise_calls' => 'No me gustan las llamadas sorpresa',
                ] as $field => $label)
                    <label class="flex items-center gap-3 rounded-xl border border-lavanda/30 bg-tiza/40 px-4 py-3 cursor-pointer has-[:checked]:border-malva has-[:checked]:bg-lavanda/10 transition">
                        <input type="checkbox" name="{{ $field }}" value="1"
                               @checked(old($field, optional($preferences)->{$field}))
                               class="rounded border-lavanda/50 text-malva focus:ring-lavanda">
                        <span class="text-sm font-semibold text-malva">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('onboarding.intereses') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Atrás</a>
            <x-primary-button>Continuar</x-primary-button>
        </div>
    </form>
</x-onboarding.shell>
