<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-dark">Reportar</h2>
            <a href="{{ route('perfiles.show', $user) }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Volver al perfil</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6">
            <p class="text-malva/75 mb-6">
                Vas a reportar a <strong>{{ $user->profile->display_name ?? $user->name }}</strong>.
                Tu reporte es confidencial y lo revisará nuestro equipo.
            </p>

            @if ($errors->any())
                <x-alert type="error" class="mb-6">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </x-alert>
            @endif

            <x-card>
                <form method="POST" action="{{ route('perfiles.reportar.store', $user) }}" class="space-y-6">
                    @csrf

                    <div>
                        <p class="font-semibold text-malva mb-2">Motivo</p>
                        <div class="space-y-2.5">
                            @foreach ($reasons as $value => $label)
                                <label class="flex items-center gap-3 rounded-xl border border-lavanda/30 bg-tiza/40 px-4 py-3 cursor-pointer has-[:checked]:border-malva has-[:checked]:bg-lavanda/10 transition">
                                    <input type="radio" name="reason" value="{{ $value }}" @checked(old('reason') === $value)
                                           class="border-lavanda/50 text-malva focus:ring-lavanda">
                                    <span class="text-sm font-semibold text-malva">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('reason')" class="mt-1.5" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Descripción (opcional)" />
                        <textarea id="description" name="description" rows="4" maxlength="1000"
                                  class="mt-1 block w-full bg-white/70 border-lavanda/40 text-malva placeholder-lavanda/70 focus:border-malva focus:ring-lavanda rounded-xl"
                                  placeholder="Cuéntanos qué pasó, si quieres darnos más contexto.">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('perfiles.show', $user) }}" class="text-sm font-semibold text-malva/70 hover:text-malva">Cancelar</a>
                        <x-primary-button>Enviar reporte</x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
