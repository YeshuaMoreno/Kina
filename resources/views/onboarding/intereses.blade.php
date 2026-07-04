<x-onboarding.shell :step="3" title="¿Qué te interesa?" subtitle="Elige al menos uno. Nos ayuda a encontrar personas afines.">
    <form method="POST" action="{{ route('onboarding.intereses.store') }}" class="space-y-6">
        @csrf

        @php $chosen = collect(old('interests', $selected))->map(fn ($i) => (int) $i)->all(); @endphp

        <div class="space-y-6">
            @foreach ($interestsByCategory as $category => $interests)
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-lavanda mb-2">{{ $category ?: 'Otros' }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($interests as $interest)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="interests[]" value="{{ $interest->id }}" class="peer sr-only"
                                       @checked(in_array($interest->id, $chosen, true))>
                                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-sm font-semibold border border-lavanda/30 bg-tiza/50 text-malva transition
                                             peer-checked:bg-malva peer-checked:text-tiza peer-checked:border-malva peer-focus:ring-2 peer-focus:ring-lavanda">
                                    {{ $interest->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('interests')" />

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('onboarding.intencion') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Atrás</a>
            <x-primary-button>Continuar</x-primary-button>
        </div>
    </form>
</x-onboarding.shell>
