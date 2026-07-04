@php
    $statusMap = [
        'pending' => ['Pendiente', 'rosa'],
        'reviewed' => ['Revisado', 'neutral'],
        'resolved' => ['Resuelto', 'success'],
        'dismissed' => ['Descartado', 'malva'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-dark">Reportes</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Panel</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <x-card padding="p-0">
                <ul class="divide-y divide-lavanda/20">
                    @forelse ($reports as $report)
                        @php [$label, $variant] = $statusMap[$report->status] ?? ['—', 'neutral']; @endphp
                        <li class="flex flex-wrap items-center justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <p class="text-sm text-malva">
                                    <span class="font-semibold text-dark">{{ optional($report->reporter)->name ?? '—' }}</span>
                                    <span class="text-lavanda">reportó a</span>
                                    <span class="font-semibold text-dark">{{ optional($report->reported)->name ?? '—' }}</span>
                                </p>
                                <p class="text-sm text-malva/70">{{ $reasons[$report->reason] ?? $report->reason }}</p>
                                <p class="text-xs text-lavanda">{{ $report->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <x-badge :variant="$variant">{{ $label }}</x-badge>
                                <a href="{{ route('admin.reports.show', $report) }}">
                                    <x-secondary-button type="button">Ver</x-secondary-button>
                                </a>
                            </div>
                        </li>
                    @empty
                        <li class="p-8 text-center text-malva/70">No hay reportes por ahora. Buena señal.</li>
                    @endforelse
                </ul>
            </x-card>

            <div class="mt-6">{{ $reports->links() }}</div>
        </div>
    </div>
</x-app-layout>
