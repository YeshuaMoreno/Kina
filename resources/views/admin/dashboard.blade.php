<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-dark">Panel de administración</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-malva/75 mb-8">Modera con cuidado. Detrás de cada cuenta hay una persona.</p>

            {{-- Resumen --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-10">
                @foreach ([
                    ['Usuarios', $usersTotal, 'text-malva'],
                    ['Suspendidos', $usersSuspended, 'text-error'],
                    ['Reportes pendientes', $reportsPending, 'text-rosa'],
                    ['Reportes revisados', $reportsReviewed, 'text-success'],
                ] as [$label, $value, $color])
                    <x-card>
                        <p class="text-sm font-semibold text-malva/70">{{ $label }}</p>
                        <p class="mt-2 font-serif text-4xl font-semibold {{ $color }}">{{ $value }}</p>
                    </x-card>
                @endforeach
            </div>

            {{-- Accesos --}}
            <div class="grid gap-5 sm:grid-cols-2">
                <a href="{{ route('admin.users.index') }}" class="group">
                    <x-card class="h-full transition group-hover:border-lavanda/50">
                        <h3 class="font-serif text-xl font-semibold text-dark">Usuarios</h3>
                        <p class="mt-1 text-sm text-malva/70">Ver, suspender y reactivar cuentas.</p>
                    </x-card>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="group">
                    <x-card class="h-full transition group-hover:border-lavanda/50">
                        <h3 class="font-serif text-xl font-semibold text-dark">Reportes</h3>
                        <p class="mt-1 text-sm text-malva/70">Revisar y resolver reportes de la comunidad.</p>
                    </x-card>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
