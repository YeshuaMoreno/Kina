<x-onboarding.shell :step="6" title="Tu privacidad, tus reglas" subtitle="Tú decides qué se muestra y a quién.">
    <form method="POST" action="{{ route('onboarding.privacidad.store') }}" class="space-y-8">
        @csrf

        {{-- Visibilidad del perfil --}}
        <div>
            <p class="font-semibold text-malva mb-2">¿Quién puede ver tu perfil?</p>
            <div class="space-y-2.5">
                @foreach ([
                    'publico' => ['Público', 'Cualquier persona en Kina puede encontrarte.'],
                    'solo_conexiones' => ['Solo mis conexiones', 'Únicamente quienes ya conectaron contigo.'],
                    'nunca' => ['Oculto', 'No apareces en descubrir. Tú das el primer paso.'],
                ] as $value => [$titulo, $texto])
                    <label class="flex items-start gap-3 rounded-xl border border-lavanda/30 bg-tiza/40 px-4 py-3 cursor-pointer has-[:checked]:border-malva has-[:checked]:bg-lavanda/10 transition">
                        <input type="radio" name="profile_visibility" value="{{ $value }}"
                               @checked(old('profile_visibility', $profile->profile_visibility ?? 'publico') === $value)
                               class="mt-0.5 border-lavanda/50 text-malva focus:ring-lavanda">
                        <span>
                            <span class="block font-semibold text-malva">{{ $titulo }}</span>
                            <span class="block text-sm text-malva/70">{{ $texto }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('profile_visibility')" class="mt-1.5" />
        </div>

        {{-- Etiquetas sensibles --}}
        <div>
            <p class="font-semibold text-malva mb-2">Tus etiquetas sensibles</p>
            <label class="flex items-center gap-3 rounded-xl border border-lavanda/30 bg-tiza/40 px-4 py-3 cursor-pointer has-[:checked]:border-malva has-[:checked]:bg-lavanda/10 transition mb-3">
                <input type="checkbox" name="show_sensitive_tags" value="1"
                       @checked(old('show_sensitive_tags', $profile->show_sensitive_tags))
                       class="rounded border-lavanda/50 text-malva focus:ring-lavanda">
                <span class="text-sm font-semibold text-malva">Permitir mostrar mis etiquetas sensibles</span>
            </label>

            <p class="text-sm text-malva/70 mb-2">Si las muestras, ¿hasta dónde?</p>
            <div class="grid grid-cols-3 gap-3">
                @foreach ([
                    'nunca' => 'Nunca',
                    'solo_conexiones' => 'Conexiones',
                    'publico' => 'Público',
                ] as $value => $label)
                    <label class="block cursor-pointer">
                        <input type="radio" name="sensitive_tags_visibility" value="{{ $value }}" class="peer sr-only"
                               @checked(old('sensitive_tags_visibility', $profile->sensitive_tags_visibility ?? 'nunca') === $value)>
                        <div class="text-center rounded-xl border border-lavanda/30 bg-tiza/40 py-2.5 text-sm font-semibold text-malva transition
                                    peer-checked:border-malva peer-checked:bg-lavanda/10 peer-focus:ring-2 peer-focus:ring-lavanda">
                            {{ $label }}
                        </div>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('sensitive_tags_visibility')" class="mt-1.5" />
        </div>

        {{-- Consentimiento de privacidad --}}
        <div class="rounded-xl border border-lavanda/30 bg-lavanda/10 p-4 text-sm text-malva space-y-3">
            <p>
                Kina protege tu información con controles de privacidad, acceso restringido y buenas
                prácticas de seguridad. Tus etiquetas sensibles solo se muestran si tú lo decides.
            </p>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="consent_privacy" value="1" @checked(old('consent_privacy'))
                       class="mt-0.5 rounded border-lavanda/50 text-malva focus:ring-lavanda">
                <span>He leído y acepto el <strong>aviso de privacidad</strong> de Kina.</span>
            </label>
            <x-input-error :messages="$errors->get('consent_privacy')" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('onboarding.etiquetas') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Atrás</a>
            <x-primary-button>Continuar</x-primary-button>
        </div>
    </form>
</x-onboarding.shell>
