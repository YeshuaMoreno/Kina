<x-onboarding.shell :step="7" title="Una foto (si quieres)" subtitle="Es totalmente opcional. Puedes añadirla o cambiarla después.">
    <form method="POST" action="{{ route('onboarding.foto.store') }}" enctype="multipart/form-data" class="space-y-6"
          x-data="{ preview: null, name: null,
                    pick(e){ const f = e.target.files[0]; if(!f){ this.preview=null; this.name=null; return; }
                             this.name = f.name; this.preview = URL.createObjectURL(f); } }">
        @csrf

        <label class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-lavanda/40 bg-tiza/40 px-6 py-10 cursor-pointer hover:bg-lavanda/5 transition text-center">
            <template x-if="!preview">
                <div class="flex flex-col items-center gap-2">
                    <svg class="w-10 h-10 text-lavanda" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                    </svg>
                    <span class="font-semibold text-malva">Toca para elegir una imagen</span>
                    <span class="text-xs text-lavanda">JPG, PNG o WEBP · máx. 4 MB</span>
                </div>
            </template>
            <template x-if="preview">
                <div class="flex flex-col items-center gap-2">
                    <img :src="preview" alt="Vista previa" class="w-32 h-32 object-cover rounded-2xl border border-lavanda/30">
                    <span class="text-xs text-malva/70" x-text="name"></span>
                    <span class="text-xs text-lavanda">Toca para cambiarla</span>
                </div>
            </template>
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="pick($event)">
        </label>
        <x-input-error :messages="$errors->get('photo')" />

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('onboarding.privacidad') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Atrás</a>
            <div class="flex items-center gap-4">
                <button type="submit" class="text-sm font-semibold text-malva/60 hover:text-malva">Omitir por ahora</button>
                <x-primary-button>Finalizar</x-primary-button>
            </div>
        </div>
    </form>
</x-onboarding.shell>
