<x-onboarding.shell :step="2" title="¿Qué buscas en Kina?" subtitle="Puedes cambiarlo cuando quieras. No hay respuestas correctas.">
    <form method="POST" action="{{ route('onboarding.intencion.store') }}" class="space-y-6">
        @csrf

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ([
                'amistad' => ['Amistad', 'Personas con quienes compartir y acompañarse.'],
                'pareja_formal' => ['Pareja formal', 'Una relación seria, sin prisa.'],
                'algo_casual' => ['Algo casual', 'Conocer gente sin grandes expectativas.'],
                'comunidad' => ['Comunidad', 'Encontrar tu gente y espacios afines.'],
            ] as $value => [$titulo, $texto])
                <label class="block cursor-pointer">
                    <input type="radio" name="looking_for" value="{{ $value }}" class="peer sr-only"
                           @checked(old('looking_for', $profile->looking_for) === $value)>
                    <div class="h-full rounded-xl border border-lavanda/30 bg-tiza/40 p-4 transition
                                peer-checked:border-malva peer-checked:bg-lavanda/10 peer-focus:ring-2 peer-focus:ring-lavanda">
                        <p class="font-serif text-lg font-semibold text-dark">{{ $titulo }}</p>
                        <p class="mt-1 text-sm text-malva/70">{{ $texto }}</p>
                    </div>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('looking_for')" />

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('onboarding.basico') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Atrás</a>
            <x-primary-button>Continuar</x-primary-button>
        </div>
    </form>
</x-onboarding.shell>
