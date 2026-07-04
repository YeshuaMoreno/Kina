@php
    $statusMap = [
        'pending' => ['Pendiente', 'rosa'],
        'reviewed' => ['Revisado', 'neutral'],
        'resolved' => ['Resuelto', 'success'],
        'dismissed' => ['Descartado', 'malva'],
    ];
    [$label, $variant] = $statusMap[$report->status] ?? ['—', 'neutral'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-dark">Reporte #{{ $report->id }}</h2>
            <a href="{{ route('admin.reports.index') }}" class="text-sm font-semibold text-malva/70 hover:text-malva">← Reportes</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 space-y-5">

            {{-- Detalle --}}
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold uppercase tracking-wide text-lavanda">Estado actual</span>
                    <x-badge :variant="$variant">{{ $label }}</x-badge>
                </div>

                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-lavanda">Reportador</dt>
                        <dd class="text-malva">{{ optional($report->reporter)->name ?? '—' }}
                            <span class="text-lavanda text-sm">({{ optional($report->reporter)->email }})</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-lavanda">Reportado</dt>
                        <dd class="text-malva">
                            {{ optional($report->reported)->name ?? '—' }}
                            <span class="text-lavanda text-sm">({{ optional($report->reported)->email }})</span>
                            @if ($report->reported)
                                @if ($report->reported->is_suspended)
                                    <x-badge variant="neutral" class="!bg-error/10 !text-error !border-error/30 ml-1">Suspendido</x-badge>
                                @endif
                                <a href="{{ route('perfiles.show', $report->reported) }}" class="text-sm text-malva/70 hover:text-malva underline ml-1">ver perfil</a>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-lavanda">Motivo</dt>
                        <dd class="text-malva">{{ $reasons[$report->reason] ?? $report->reason }}</dd>
                    </div>
                    @if ($report->description)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-lavanda">Descripción</dt>
                            <dd class="text-malva/85 whitespace-pre-line">{{ $report->description }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-lavanda">Creado</dt>
                        <dd class="text-malva/85">{{ $report->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if ($report->reviewed_at)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-lavanda">Revisado</dt>
                            <dd class="text-malva/85">
                                {{ $report->reviewed_at->format('d/m/Y H:i') }}
                                @if ($report->reviewer) por {{ $report->reviewer->name }} @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            {{-- Acciones --}}
            <x-card>
                <h3 class="font-serif text-lg font-semibold text-dark mb-4">Resolver reporte</h3>

                @if ($errors->any())
                    <x-alert type="error" class="mb-4">
                        <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </x-alert>
                @endif

                <form method="POST" action="{{ route('admin.reports.review', $report) }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-3 gap-3">
                        @foreach (['reviewed' => 'Revisado', 'resolved' => 'Resuelto', 'dismissed' => 'Descartado'] as $value => $text)
                            <label class="block cursor-pointer">
                                <input type="radio" name="status" value="{{ $value }}" class="peer sr-only" @checked($report->status === $value)>
                                <div class="text-center rounded-xl border border-lavanda/30 bg-tiza/40 py-2.5 text-sm font-semibold text-malva transition
                                            peer-checked:border-malva peer-checked:bg-lavanda/10 peer-focus:ring-2 peer-focus:ring-lavanda">
                                    {{ $text }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('status')" />

                    @if ($report->reported && ! $report->reported->is_suspended && $report->reported_id !== auth()->id())
                        <label class="flex items-start gap-3 rounded-xl border border-error/30 bg-error/5 px-4 py-3 cursor-pointer">
                            <input type="checkbox" name="suspend_reported" value="1" class="mt-0.5 rounded border-lavanda/50 text-error focus:ring-error">
                            <span class="text-sm text-malva">También <strong>suspender</strong> a {{ $report->reported->name }} (no podrá usar la app).</span>
                        </label>
                    @endif

                    <div class="flex justify-end">
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </x-card>

        </div>
    </div>
</x-app-layout>
