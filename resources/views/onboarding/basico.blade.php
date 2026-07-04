<x-onboarding.shell :step="1" title="Cuéntanos lo básico" subtitle="Así sabremos cómo llamarte y dónde estás.">
    <form method="POST" action="{{ route('onboarding.basico.store') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="display_name" value="¿Cómo quieres que te llamen?" />
            <x-text-input id="display_name" name="display_name" type="text" class="mt-1 block w-full"
                          :value="old('display_name', $profile->display_name)" required autofocus placeholder="Tu nombre o apodo" />
            <x-input-error :messages="$errors->get('display_name')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="birthdate" value="Fecha de nacimiento" />
            <x-text-input id="birthdate" name="birthdate" type="date" class="mt-1 block w-full"
                          :value="old('birthdate', optional($user->birthdate)->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('birthdate')" class="mt-1.5" />
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <x-input-label for="city" value="Ciudad" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                              :value="old('city', $profile->city)" placeholder="Opcional" />
            </div>
            <div>
                <x-input-label for="state" value="Estado" />
                <x-text-input id="state" name="state" type="text" class="mt-1 block w-full"
                              :value="old('state', $profile->state)" placeholder="Opcional" />
            </div>
            <div>
                <x-input-label for="country" value="País" />
                <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                              :value="old('country', $profile->country)" placeholder="Opcional" />
            </div>
        </div>

        <label class="flex items-start gap-3 rounded-xl border border-lavanda/30 bg-tiza/50 p-4 cursor-pointer">
            <input type="checkbox" name="confirm_adult" value="1" @checked(old('confirm_adult'))
                   class="mt-0.5 rounded border-lavanda/50 text-malva focus:ring-lavanda">
            <span class="text-sm text-malva">
                Confirmo que soy <strong>mayor de 18 años</strong>. Kina es un espacio solo para personas adultas.
            </span>
        </label>
        <x-input-error :messages="$errors->get('confirm_adult')" class="-mt-3" />

        <div class="flex items-center justify-end pt-2">
            <x-primary-button>Continuar</x-primary-button>
        </div>
    </form>
</x-onboarding.shell>
