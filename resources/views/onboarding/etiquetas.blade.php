<x-onboarding.shell :step="5" title="Etiquetas opcionales" subtitle="Solo si tú quieres. Nada aquí es obligatorio ni requiere diagnóstico.">
    <form method="POST" action="{{ route('onboarding.etiquetas.store') }}" class="space-y-6">
        @csrf

        @php $chosen = collect(old('tags', $selected))->map(fn ($i) => (int) $i)->all(); @endphp

        <div class="flex flex-wrap gap-2">
            @foreach ($tags as $tag)
                <label class="cursor-pointer">
                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="peer sr-only"
                           @checked(in_array($tag->id, $chosen, true))>
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-semibold border border-lavanda/30 bg-tiza/50 text-malva transition
                                 peer-checked:bg-malva peer-checked:text-tiza peer-checked:border-malva peer-focus:ring-2 peer-focus:ring-lavanda">
                        {{ $tag->name }}
                        @if ($tag->is_sensitive)
                            <span class="w-1.5 h-1.5 rounded-full bg-rosa" title="Etiqueta sensible"></span>
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('tags')" />

        <div class="rounded-xl border border-rosa/40 bg-rosa/10 p-4 text-sm text-malva space-y-3">
            <p>
                Las etiquetas con
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-rosa align-middle"></span>
                son <strong>sensibles</strong>. Quedan <strong>ocultas por defecto</strong>: solo se mostrarán si tú lo decides en el siguiente paso.
            </p>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="consent_sensitive" value="1" @checked(old('consent_sensitive'))
                       class="mt-0.5 rounded border-lavanda/50 text-malva focus:ring-lavanda">
                <span>Doy mi <strong>consentimiento explícito</strong> para guardar las etiquetas sensibles que haya seleccionado.</span>
            </label>
            <x-input-error :messages="$errors->get('consent_sensitive')" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('onboarding.comunicacion') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Atrás</a>
            <x-primary-button>Continuar</x-primary-button>
        </div>
    </form>
</x-onboarding.shell>
