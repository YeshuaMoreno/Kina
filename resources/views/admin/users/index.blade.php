<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-dark">Usuarios</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Panel</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">

            {{-- Filtros --}}
            <div class="flex gap-2 mb-6">
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold border transition {{ ! $filter ? 'bg-malva text-tiza border-malva' : 'border-lavanda/40 text-malva hover:bg-lavanda/10' }}">
                    Todos
                </a>
                <a href="{{ route('admin.users.index', ['filtro' => 'suspendidos']) }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold border transition {{ $filter === 'suspendidos' ? 'bg-malva text-tiza border-malva' : 'border-lavanda/40 text-malva hover:bg-lavanda/10' }}">
                    Suspendidos
                </a>
            </div>

            <x-card padding="p-0">
                <ul class="divide-y divide-lavanda/20">
                    @forelse ($users as $user)
                        <li class="flex flex-wrap items-center justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-dark">{{ $user->profile->display_name ?? $user->name }}</span>
                                    @if ($user->is_admin)<x-badge variant="malva">Admin</x-badge>@endif
                                    @if ($user->is_suspended)
                                        <x-badge variant="neutral" class="!bg-error/10 !text-error !border-error/30">Suspendido</x-badge>
                                    @else
                                        <x-badge variant="success">Activo</x-badge>
                                    @endif
                                    @if ($user->profile?->onboarding_completed)
                                        <span class="text-xs text-lavanda">· onboarding ✓</span>
                                    @else
                                        <span class="text-xs text-lavanda">· sin onboarding</span>
                                    @endif
                                </div>
                                <p class="text-sm text-malva/70 truncate">{{ $user->email }}</p>
                                <p class="text-xs text-lavanda">Registrado {{ $user->created_at->format('d/m/Y') }}</p>
                            </div>

                            <div class="shrink-0">
                                @if ($user->id === auth()->id())
                                    <span class="text-xs text-lavanda">Eres tú</span>
                                @elseif ($user->is_suspended)
                                    <form method="POST" action="{{ route('admin.users.reactivate', $user) }}">
                                        @csrf
                                        <x-secondary-button type="submit">Reactivar</x-secondary-button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.suspend', $user) }}"
                                          onsubmit="return confirm('¿Suspender a {{ $user->name }}? No podrá usar la app.')">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold border border-error/40 text-error hover:bg-error/10 transition">
                                            Suspender
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="p-8 text-center text-malva/70">No hay usuarios que mostrar.</li>
                    @endforelse
                </ul>
            </x-card>

            <div class="mt-6">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
